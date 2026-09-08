<?php

namespace App\Services\Integration;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OracleMasterBridge
{
    protected mixed $connection = null;

    /**
     * Excluded tables according to institutional migration security policy:
     * Exclude all FP_* tables and report upload / signed report tables.
     */
    protected const EXCLUDED_PREFIXES = ['FP_'];
    protected const EXCLUDED_TABLES = [
        'CORR_AUTHORITY_REPORTS_UPLOAD',
        'CORR_SIGNED_REPORTS_UPLOAD',
        'DLIS_AUTHORITY_REPORTS_UPLOAD',
        'DLIS_REV_REPORTS_UPLOAD',
        'DLIS_SIGNED_AUTH_REPORT',
        'DLIS_SIGNED_REV_REPORT',
    ];

    /**
     * Get or create active Oracle 11g OCI connection
     */
    public function getConnection(): mixed
    {
        if ($this->connection) {
            return $this->connection;
        }

        $host = config('database.connections.oracle_legacy.host', '192.168.100.247');
        $port = config('database.connections.oracle_legacy.port', '1521');
        $sid = config('database.connections.oracle_legacy.database', 'db11g');
        $user = config('database.connections.oracle_legacy.username', 'gpffp');
        $pass = config('database.connections.oracle_legacy.password', 'gpffp');

        $tns = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST={$host})(PORT={$port}))(CONNECT_DATA=(SID={$sid})))";

        if (!function_exists('oci_pconnect')) {
            Log::warning('OracleMasterBridge: OCI8 extension is not loaded.');
            return null;
        }

        $conn = @oci_pconnect($user, $pass, $tns, 'AL32UTF8');

        if (!$conn) {
            $e = oci_error();
            Log::error('OracleMasterBridge: Oracle connection failed.', ['error' => $e['message'] ?? 'Unknown error']);
            return null;
        }

        $this->connection = $conn;
        return $this->connection;
    }

    /**
     * Verify table access against exclusion security policy
     */
    protected function validateTableAccess(string $tableName): void
    {
        $upper = strtoupper(trim($tableName));
        
        foreach (self::EXCLUDED_PREFIXES as $prefix) {
            if (str_starts_with($upper, $prefix)) {
                throw new RuntimeException("Access denied: Table '{$tableName}' is excluded by institutional security policy.");
            }
        }

        if (in_array($upper, self::EXCLUDED_TABLES, true)) {
            throw new RuntimeException("Access denied: Table '{$tableName}' is excluded by institutional security policy.");
        }
    }

    /**
     * Retrieve list of GPF Series from Oracle 11g (VLCS.MM_GPF_SERIES)
     */
    public function getSeriesList(): Collection
    {
        $conn = $this->getConnection();
        if (!$conn) {
            return $this->fallbackSeries();
        }

        try {
            $this->validateTableAccess('MM_GPF_SERIES');
            $stmt = oci_parse($conn, "
                SELECT SERIES_ID AS ID, SERIES_DESCR AS NAME 
                FROM VLCS.MM_GPF_SERIES 
                WHERE SERIES_DESCR IS NOT NULL 
                ORDER BY TO_NUMBER(SERIES_ID) ASC
            ");
            
            if (@oci_execute($stmt)) {
                $series = [];
                while ($row = oci_fetch_assoc($stmt)) {
                    $series[] = [
                        'id' => trim($row['ID']),
                        'code' => trim($row['NAME']),
                        'name' => trim($row['NAME']) . ' (Series ' . trim($row['ID']) . ')',
                    ];
                }
                oci_free_statement($stmt);
                if (!empty($series)) {
                    return collect($series);
                }
            }
        } catch (Exception $e) {
            Log::warning('OracleMasterBridge::getSeriesList error: ' . $e->getMessage());
        }

        return $this->fallbackSeries();
    }

    /**
     * Retrieve list of DDOs from Oracle 11g (VLCS.STATE_DDO / VLCS.MM_DDO)
     */
    public function getDdoList(): Collection
    {
        $conn = $this->getConnection();
        if (!$conn) {
            return $this->fallbackDdos();
        }

        try {
            $this->validateTableAccess('STATE_DDO');
            $stmt = oci_parse($conn, "
                SELECT DDO_CODE AS ID, DDO_DESG AS NAME, DDO_TREASURY_CODE AS TREASURY_CODE, PHONE_NO, DDO_EMAIL_ID 
                FROM VLCS.STATE_DDO 
                WHERE DDO_DESG IS NOT NULL 
                ORDER BY DDO_DESG ASC
            ");
            
            if (@oci_execute($stmt)) {
                $ddos = [];
                while ($row = oci_fetch_assoc($stmt)) {
                    $ddos[] = [
                        'id' => trim($row['ID']),
                        'name' => trim($row['NAME']),
                        'treasury_code' => isset($row['TREASURY_CODE']) ? trim($row['TREASURY_CODE']) : '',
                        'phone' => isset($row['PHONE_NO']) ? trim($row['PHONE_NO']) : '',
                        'email' => isset($row['DDO_EMAIL_ID']) ? trim($row['DDO_EMAIL_ID']) : '',
                    ];
                }
                oci_free_statement($stmt);
                if (!empty($ddos)) {
                    return collect($ddos);
                }
            }
        } catch (Exception $e) {
            Log::warning('OracleMasterBridge::getDdoList error: ' . $e->getMessage());
        }

        return $this->fallbackDdos();
    }

    /**
     * Retrieve Treasuries from Oracle 11g (VLCS.STATE_TREASURY)
     */
    public function getTreasuries(): Collection
    {
        $conn = $this->getConnection();
        if (!$conn) {
            return $this->fallbackTreasuries();
        }

        try {
            $this->validateTableAccess('STATE_TREASURY');
            $stmt = oci_parse($conn, "
                SELECT TRES_CODE AS ID, TRES_NAME AS NAME, EMAIL_ID 
                FROM VLCS.STATE_TREASURY 
                WHERE TRES_NAME IS NOT NULL 
                ORDER BY TRES_NAME ASC
            ");
            
            if (@oci_execute($stmt)) {
                $treasuries = [];
                while ($row = oci_fetch_assoc($stmt)) {
                    $treasuries[] = [
                        'id' => trim($row['ID']),
                        'name' => trim($row['NAME']),
                        'email' => isset($row['EMAIL_ID']) ? trim($row['EMAIL_ID']) : '',
                    ];
                }
                oci_free_statement($stmt);
                if (!empty($treasuries)) {
                    return collect($treasuries);
                }
            }
        } catch (Exception $e) {
            Log::warning('OracleMasterBridge::getTreasuries error: ' . $e->getMessage());
        }

        return $this->fallbackTreasuries();
    }

    /**
     * Look up subscriber details from VLCS.GP_ACCOUNTS, VLCS.MM_EMPLOYEE, and gpffp.GPF_APPLICATION
     */
    public function lookupSubscriber(string $seriesCode, string $accountNo): array
    {
        $conn = $this->getConnection();
        $cleanAccount = preg_replace('/[^0-9]/', '', $accountNo);
        $cleanSeries = trim($seriesCode);

        if ($conn) {
            try {
                $this->validateTableAccess('GP_ACCOUNTS');
                
                // 1. Check VLCS.GP_ACCOUNTS (the primary master used in legacy subscriber_details_by_account_no.php)
                $query = "
                    SELECT 
                        SERIES_ID, ACCOUNT_NO, ACC_HOLDER_NAME, EMP_CODE, BENE_CODE, 
                        MOBILE, ACCOUNT_CLOSED_TAG, DATE_OF_CLOSURE, OP_BALANCE_WITHDRAWL,
                        CL_BAL_WITHDRAWL, FIN_YEAR_CODE
                    FROM VLCS.GP_ACCOUNTS
                    WHERE SERIES_ID = :series AND ACCOUNT_NO = :acct
                      AND ROWNUM = 1
                ";

                $stmt = oci_parse($conn, $query);
                oci_bind_by_name($stmt, ':series', $cleanSeries);
                oci_bind_by_name($stmt, ':acct', $cleanAccount);

                $acc = null;
                if (@oci_execute($stmt)) {
                    $acc = oci_fetch_assoc($stmt);
                    oci_free_statement($stmt);
                }

                if ($acc) {
                    $isClosed = (strtoupper(trim($acc['ACCOUNT_CLOSED_TAG'] ?? '')) === 'Y');
                    $closureDate = $acc['DATE_OF_CLOSURE'] ? date('d-m-Y', strtotime($acc['DATE_OF_CLOSURE'])) : null;
                    $warningMessage = $isClosed ? "Notice: GPF account was closed on {$closureDate}." : null;

                    // Fetch employee master / application data for designation and address
                    $empCode = trim($acc['EMP_CODE'] ?? '');
                    $empData = null;
                    if ($empCode) {
                        $empStmt = oci_parse($conn, "
                            SELECT EMP_NAME, EMP_DESIGNATION, DATE_OF_BIRTH, DATE_OF_JOIN, 
                                   FATHER_HUSBANT_NAME, EMP_MAIL_ADDRESS 
                            FROM VLCS.MM_EMPLOYEE 
                            WHERE EMP_CODE = :empcode AND ROWNUM = 1
                        ");
                        oci_bind_by_name($empStmt, ':empcode', $empCode);
                        if (@oci_execute($empStmt)) {
                            $empData = oci_fetch_assoc($empStmt);
                            oci_free_statement($empStmt);
                        }
                    }

                    // Check previous application in gpffp.GPF_APPLICATION
                    $appStmt = oci_parse($conn, "
                        SELECT TITLE, DESG_TITLE, DESIGNATION, SPOUSE_NAME, RELATION, 
                               PERSONAL_ADDRESS, DDO_CODE, TREASURY_CODE, DATE_OF_EFFECT, LAST_FUND_DEDUCTION
                        FROM gpffp.GPF_APPLICATION
                        WHERE ACCOUNT_NO = :acct AND (SERIES_ID = :series OR :series_null IS NULL) AND ROWNUM = 1
                    ");
                    oci_bind_by_name($appStmt, ':acct', $cleanAccount);
                    oci_bind_by_name($appStmt, ':series', $cleanSeries);
                    oci_bind_by_name($appStmt, ':series_null', $cleanSeries);
                    $appData = null;
                    if (@oci_execute($appStmt)) {
                        $appData = oci_fetch_assoc($appStmt);
                        oci_free_statement($appStmt);
                    }

                    $rawName = trim($acc['ACC_HOLDER_NAME'] ?? ($empData['EMP_NAME'] ?? ''));
                    $designation = $appData['DESIGNATION'] ?? ($empData['EMP_DESIGNATION'] ?? 'Government Employee');
                    $personalAddress = $appData['PERSONAL_ADDRESS'] ?? ($empData['EMP_MAIL_ADDRESS'] ?? 'Agartala, Tripura');
                    $ddoCode = $appData['DDO_CODE'] ?? '';
                    $treasuryCode = $appData['TREASURY_CODE'] ?? '';

                    // If DDO exists but Treasury is empty, lookup Treasury from VLCS.STATE_DDO
                    if ($ddoCode && !$treasuryCode) {
                        $ddoStmt = oci_parse($conn, "SELECT DDO_TREASURY_CODE FROM VLCS.STATE_DDO WHERE DDO_CODE = :ddocode AND ROWNUM = 1");
                        oci_bind_by_name($ddoStmt, ':ddocode', $ddoCode);
                        if (@oci_execute($ddoStmt)) {
                            $ddoRow = oci_fetch_assoc($ddoStmt);
                            $treasuryCode = $ddoRow['DDO_TREASURY_CODE'] ?? '';
                            oci_free_statement($ddoStmt);
                        }
                    }

                    return [
                        'found_in_oracle' => true,
                        'is_closed' => $isClosed,
                        'closure_date' => $closureDate,
                        'warning' => $warningMessage,
                        'series_code' => $cleanSeries,
                        'account_no' => $cleanAccount,
                        'subscriber_name' => $rawName,
                        'name_title' => $appData['TITLE'] ?? 'Shri',
                        'designation_title' => $appData['DESG_TITLE'] ?? 'Mr',
                        'designation' => $designation ?: 'Government Employee',
                        'employee_code' => $acc['EMP_CODE'] ?: '',
                        'beneficiary_code' => $acc['BENE_CODE'] ?: '',
                        'mobile_no' => $acc['MOBILE'] ?: '',
                        'opening_balance' => (float) ($acc['OP_BALANCE_WITHDRAWL'] ?? 0),
                        'closing_balance' => (float) ($acc['CL_BAL_WITHDRAWL'] ?? 0),
                        'closing_fin_year' => $acc['FIN_YEAR_CODE'] ?? null,
                        'personal_address' => $personalAddress ?: 'Agartala, Tripura',
                        'ddo_code' => $ddoCode,
                        'treasury_code' => $treasuryCode,
                        'spouse_name' => $appData['SPOUSE_NAME'] ?? ($empData['FATHER_HUSBANT_NAME'] ?? ''),
                        'spouse_relation' => $appData['RELATION'] ?? 'Spouse',
                        'dob' => $empData['DATE_OF_BIRTH'] ?? null,
                        'doj' => $empData['DATE_OF_JOIN'] ?? null,
                        'last_fund_deduction' => $appData['LAST_FUND_DEDUCTION'] ?? null,
                    ];
                }

                // 2. Fallback check on VLCS.MM_EMPLOYEE
                $empQuery = "
                    SELECT 
                        EMP_CODE, SERIES_ID, EMP_NAME, EMP_DESIGNATION, 
                        DATE_OF_BIRTH, DATE_OF_JOIN, FATHER_HUSBANT_NAME, 
                        EMP_MAIL_ADDRESS, OLD_EMP_CODE
                    FROM VLCS.MM_EMPLOYEE
                    WHERE (EMP_CODE = :acct1 OR OLD_EMP_CODE = :acct2)
                      AND (SERIES_ID = :series OR :series_null IS NULL)
                      AND ROWNUM = 1
                ";

                $stmt2 = oci_parse($conn, $empQuery);
                oci_bind_by_name($stmt2, ':acct1', $cleanAccount);
                oci_bind_by_name($stmt2, ':acct2', $cleanAccount);
                oci_bind_by_name($stmt2, ':series', $cleanSeries);
                oci_bind_by_name($stmt2, ':series_null', $cleanSeries);

                if (@oci_execute($stmt2)) {
                    $emp = oci_fetch_assoc($stmt2);
                    oci_free_statement($stmt2);

                    if ($emp) {
                        return [
                            'found_in_oracle' => true,
                            'is_closed' => false,
                            'closure_date' => null,
                            'warning' => null,
                            'series_code' => $emp['SERIES_ID'] ?: $cleanSeries,
                            'account_no' => $emp['EMP_CODE'] ?: $cleanAccount,
                            'subscriber_name' => trim($emp['EMP_NAME'] ?? ''),
                            'name_title' => 'Shri',
                            'designation_title' => 'Mr',
                            'designation' => trim($emp['EMP_DESIGNATION'] ?? '') ?: 'Government Employee',
                            'employee_code' => $emp['EMP_CODE'] ?: '',
                            'beneficiary_code' => '',
                            'mobile_no' => '',
                            'opening_balance' => 0.00,
                            'closing_balance' => 0.00,
                            'closing_fin_year' => null,
                            'personal_address' => $emp['EMP_MAIL_ADDRESS'] ?: 'Agartala, Tripura',
                            'ddo_code' => '',
                            'treasury_code' => '',
                            'spouse_name' => $emp['FATHER_HUSBANT_NAME'] ?: '',
                            'spouse_relation' => 'Spouse',
                            'dob' => $emp['DATE_OF_BIRTH'] ?? null,
                            'doj' => $emp['DATE_OF_JOIN'] ?? null,
                            'last_fund_deduction' => null,
                        ];
                    }
                }
            } catch (Exception $e) {
                Log::warning('OracleMasterBridge::lookupSubscriber error: ' . $e->getMessage());
            }
        }

        // Fallback default structure
        return [
            'found_in_oracle' => false,
            'is_closed' => false,
            'closure_date' => null,
            'warning' => null,
            'series_code' => $cleanSeries,
            'account_no' => $cleanAccount,
            'subscriber_name' => '',
            'name_title' => 'Shri',
            'designation_title' => 'Mr',
            'designation' => '',
            'employee_code' => '',
            'beneficiary_code' => '',
            'mobile_no' => '',
            'opening_balance' => 0.00,
            'closing_balance' => 0.00,
            'closing_fin_year' => null,
            'personal_address' => '',
            'ddo_code' => '',
            'treasury_code' => '',
            'spouse_name' => '',
            'spouse_relation' => 'Spouse',
        ];
    }

    /**
     * Retrieve historic monthly subscriptions from Oracle 11g (gpffp.GPF_SUBSCRIPTION & VLCS tables)
     */
    public function getSubscriptions(string $seriesCode, string $accountNo, ?string $regdNo = null): array
    {
        $conn = $this->getConnection();
        if (!$conn) {
            return [];
        }

        try {
            $this->validateTableAccess('GPF_SUBSCRIPTION');
            $cleanAccount = preg_replace('/[^0-9]/', '', $accountNo);
            $cleanSeries = trim($seriesCode);

            $query = "
                SELECT 
                    REGD_NO, SERIES_ID, ACCOUNT_NO, FIN_YEAR_CODE, 
                    ABSTRACT_NO, VOUCHER_NO, PAY_SLIP_DATE, INTEREST_DATE, 
                    NVL(SUBSCRIPTION_AMT, 0) AS SUBSCRIPTION_AMT, 
                    NVL(REFUND_AMT, 0) AS REFUND_AMT, 
                    NVL(WITHDRAWAL_AMT, 0) AS WITHDRAWAL_AMT, 
                    NVL(ADVANCE_AMT, 0) AS ADVANCE_AMT, 
                    NVL(OTHERS_AMT, 0) AS OTHERS_AMT,
                    NVL(ADJUSTMENT_NO, '0') AS ADJUSTMENT_NO,
                    INT_ALLOW
                FROM gpffp.GPF_SUBSCRIPTION
                WHERE (REGD_NO = :regd OR (ACCOUNT_NO = :acct AND (SERIES_ID = :series OR :series_null IS NULL)))
                ORDER BY PAY_SLIP_DATE ASC, INTEREST_DATE ASC
            ";

            $stmt = oci_parse($conn, $query);
            $regdParam = $regdNo ?: '0';
            oci_bind_by_name($stmt, ':regd', $regdParam);
            oci_bind_by_name($stmt, ':acct', $cleanAccount);
            oci_bind_by_name($stmt, ':series', $cleanSeries);
            oci_bind_by_name($stmt, ':series_null', $cleanSeries);

            if (@oci_execute($stmt)) {
                $rows = [];
                while ($row = oci_fetch_assoc($stmt)) {
                    $subAmt = (float) ($row['SUBSCRIPTION_AMT'] ?? 0);
                    $refAmt = (float) ($row['REFUND_AMT'] ?? 0);
                    $othAmt = (float) ($row['OTHERS_AMT'] ?? 0);
                    $wthAmt = (float) ($row['WITHDRAWAL_AMT'] ?? 0);
                    $advAmt = (float) ($row['ADVANCE_AMT'] ?? 0);

                    $rows[] = [
                        'financial_year' => $row['FIN_YEAR_CODE'],
                        'pay_slip_date' => $row['PAY_SLIP_DATE'],
                        'interest_date' => $row['INTEREST_DATE'] ?? $row['PAY_SLIP_DATE'],
                        'deposit' => $subAmt + $refAmt + $othAmt,
                        'subscription' => $subAmt,
                        'refund' => $refAmt,
                        'others' => $othAmt,
                        'withdrawal' => $wthAmt + $advAmt,
                        'advance' => $advAmt,
                        'voucher_no' => $row['VOUCHER_NO'] ?? '',
                        'abstract_no' => $row['ABSTRACT_NO'] ?? '',
                        'adjustment_no' => ($row['ADJUSTMENT_NO'] !== '0') ? $row['ADJUSTMENT_NO'] : null,
                        'interest_allowed' => ($row['INT_ALLOW'] !== 'N'),
                    ];
                }
                oci_free_statement($stmt);
                return $rows;
            }
        } catch (Exception $e) {
            Log::warning('OracleMasterBridge::getSubscriptions error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Retrieve missing credits from VLCS.GP_MISSING_CREDIT
     */
    public function getMissingCredits(string $seriesCode, string $accountNo): array
    {
        $conn = $this->getConnection();
        if (!$conn) {
            return [];
        }

        try {
            $this->validateTableAccess('GP_MISSING_CREDIT');
            $cleanAccount = preg_replace('/[^0-9]/', '', $accountNo);
            $cleanSeries = trim($seriesCode);

            $query = "
                SELECT SLIP_DATE, FIN_YEAR_CODE, CLEAR_TAG, CLEAR_FIN_YEAR_CODE 
                FROM VLCS.GP_MISSING_CREDIT 
                WHERE SERIES_ID = :series AND ACCOUNT_NO = :acct 
                  AND NVL(CLEAR_TAG, 'N') != 'Y'
                ORDER BY SLIP_DATE ASC
            ";

            $stmt = oci_parse($conn, $query);
            oci_bind_by_name($stmt, ':series', $cleanSeries);
            oci_bind_by_name($stmt, ':acct', $cleanAccount);

            if (@oci_execute($stmt)) {
                $rows = [];
                while ($row = oci_fetch_assoc($stmt)) {
                    $rows[] = [
                        'slip_date' => $row['SLIP_DATE'],
                        'fin_year_code' => $row['FIN_YEAR_CODE'],
                        'clear_tag' => $row['CLEAR_TAG'],
                    ];
                }
                oci_free_statement($stmt);
                return $rows;
            }
        } catch (Exception $e) {
            Log::warning('OracleMasterBridge::getMissingCredits error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Fallback Series
     */
    protected function fallbackSeries(): Collection
    {
        return collect([
            ['id' => '1', 'code' => 'AIS', 'name' => 'AIS - All India Service (Series 1)'],
            ['id' => '2', 'code' => 'AGR', 'name' => 'AGR - Agriculture (Series 2)'],
            ['id' => '3', 'code' => 'COOP', 'name' => 'COOP - Cooperation (Series 3)'],
            ['id' => '10', 'code' => 'EDN', 'name' => 'EDN - Education (Series 10)'],
            ['id' => '16', 'code' => 'GRP', 'name' => 'GRP - General Provident Fund (Series 16)'],
            ['id' => '18', 'code' => 'ADC', 'name' => 'ADC - Autonomous District Council (Series 18)'],
            ['id' => '19', 'code' => 'EGRP', 'name' => 'EGRP - Education Dept (Series 19)'],
            ['id' => '20', 'code' => 'AGRP', 'name' => 'AGRP - Agriculture Dept (Series 20)'],
            ['id' => '21', 'code' => 'OGRP', 'name' => 'OGRP - Other Depts (Series 21)'],
        ]);
    }

    /**
     * Fallback DDOs
     */
    protected function fallbackDdos(): Collection
    {
        return collect([
            ['id' => '6016', 'name' => 'Head Master, K. C. Girls Class-XII School, Kamalpur', 'treasury_code' => 'TPA06'],
            ['id' => '6017', 'name' => 'Headmaster, Kalacheri HS School, Kamalpur', 'treasury_code' => 'TPA06'],
            ['id' => '6018', 'name' => 'Head Master, Kamalpur Boys HS School', 'treasury_code' => 'TPA06'],
            ['id' => '14768', 'name' => 'SPORTS OFFICER, TRIPURA SPORTS SCHOOL, KABIRAJTILLA', 'treasury_code' => 'TPA08'],
        ]);
    }

    /**
     * Fallback Treasuries
     */
    protected function fallbackTreasuries(): Collection
    {
        return collect([
            ['id' => 'TPA01', 'name' => 'Kanchanpur Sub Treasury', 'email' => ''],
            ['id' => 'TPA02', 'name' => 'Dharmanagar Treasury', 'email' => ''],
            ['id' => 'TPA06', 'name' => 'Kamalpur Sub Treasury', 'email' => 'stokmnp@yahoo.com'],
            ['id' => 'TPA08', 'name' => 'Agartala Treasury No. I', 'email' => ''],
            ['id' => 'TPA23', 'name' => 'Jampuijala Sub Treasury', 'email' => ''],
            ['id' => 'TPA24', 'name' => 'Karbook Sub Treasury', 'email' => ''],
        ]);
    }
}
