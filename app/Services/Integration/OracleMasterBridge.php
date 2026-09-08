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
    protected function getConnection(): mixed
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
                ORDER BY SERIES_DESCR ASC
            ");
            
            if (@oci_execute($stmt)) {
                $series = [];
                while ($row = oci_fetch_assoc($stmt)) {
                    $series[] = [
                        'id' => trim($row['ID']),
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
     * Retrieve list of DDOs from Oracle 11g (VLCS.MM_DDO)
     */
    public function getDdoList(): Collection
    {
        $conn = $this->getConnection();
        if (!$conn) {
            return $this->fallbackDdos();
        }

        try {
            $this->validateTableAccess('MM_DDO');
            $stmt = oci_parse($conn, "
                SELECT DDO_CODE AS ID, DDO_DESIGNATION AS NAME, DDO_ADDRESS AS ADDRESS 
                FROM VLCS.MM_DDO 
                WHERE DDO_DESIGNATION IS NOT NULL 
                ORDER BY DDO_DESIGNATION ASC
            ");
            
            if (@oci_execute($stmt)) {
                $ddos = [];
                while ($row = oci_fetch_assoc($stmt)) {
                    $ddos[] = [
                        'id' => trim($row['ID']),
                        'name' => trim($row['NAME']),
                        'address' => isset($row['ADDRESS']) ? trim($row['ADDRESS']) : '',
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
                SELECT TRES_CODE AS ID, TRES_NAME AS NAME 
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
     * Look up subscriber service profile by Series ID and Account Number (VLCS.MM_EMPLOYEE & gpffp.GPF_APPLICATION)
     */
    public function lookupSubscriber(string $seriesCode, string $accountNo): array
    {
        $conn = $this->getConnection();
        $cleanAccount = preg_replace('/[^0-9]/', '', $accountNo);
        $cleanSeries = trim($seriesCode);

        if ($conn) {
            try {
                $this->validateTableAccess('MM_EMPLOYEE');
                
                // Query employee master from VLCS
                $query = "
                    SELECT 
                        EMP_CODE, SERIES_ID, EMP_NAME, EMP_DESIGNATION, 
                        DATE_OF_BIRTH, DATE_OF_JOIN, FATHER_HUSBANT_NAME, 
                        EMP_MAIL_ADDRESS, OLD_EMP_CODE, DEPT_CODE
                    FROM VLCS.MM_EMPLOYEE
                    WHERE (EMP_CODE = :acct1 OR OLD_EMP_CODE = :acct2)
                      AND (SERIES_ID = :series OR :series_null IS NULL)
                      AND ROWNUM = 1
                ";

                $stmt = oci_parse($conn, $query);
                oci_bind_by_name($stmt, ':acct1', $cleanAccount);
                oci_bind_by_name($stmt, ':acct2', $cleanAccount);
                oci_bind_by_name($stmt, ':series', $cleanSeries);
                oci_bind_by_name($stmt, ':series_null', $cleanSeries);

                if (@oci_execute($stmt)) {
                    $emp = oci_fetch_assoc($stmt);
                    oci_free_statement($stmt);

                    if ($emp) {
                        // Check if previous application exists in GPFFP
                        $appQuery = "
                            SELECT PERSONAL_ADDRESS, DDO_CODE, TREASURY_CODE, SPOUSE_NAME, RELATION, LAST_FUND_DEDUCTION
                            FROM gpffp.GPF_APPLICATION
                            WHERE ACCOUNT_NO = :acct AND (SERIES_ID = :series OR :series_null IS NULL) AND ROWNUM = 1
                        ";
                        $appStmt = oci_parse($conn, $appQuery);
                        oci_bind_by_name($appStmt, ':acct', $cleanAccount);
                        oci_bind_by_name($appStmt, ':series', $cleanSeries);
                        oci_bind_by_name($appStmt, ':series_null', $cleanSeries);
                        
                        $appData = null;
                        if (@oci_execute($appStmt)) {
                            $appData = oci_fetch_assoc($appStmt);
                            oci_free_statement($appStmt);
                        }

                        // Clean raw names (strip legacy leading 0/prefixes if present)
                        $rawName = trim($emp['EMP_NAME'] ?? '');
                        $cleanedName = preg_replace('/^[0-9\s]+/', '', $rawName);

                        return [
                            'found_in_oracle' => true,
                            'series_code' => $emp['SERIES_ID'] ?: $cleanSeries,
                            'account_no' => $emp['EMP_CODE'] ?: $cleanAccount,
                            'subscriber_name' => $cleanedName ?: $rawName,
                            'name_title' => 'Shri',
                            'designation_title' => 'Mr',
                            'designation' => ($emp['EMP_DESIGNATION'] && $emp['EMP_DESIGNATION'] !== 'n/a') ? trim($emp['EMP_DESIGNATION']) : 'Government Employee',
                            'employee_code' => 'EMP' . str_pad($cleanAccount, 6, '0', STR_PAD_LEFT),
                            'beneficiary_code' => 'BEN' . str_pad($cleanAccount, 6, '0', STR_PAD_LEFT),
                            'mobile_no' => '',
                            'personal_address' => $appData['PERSONAL_ADDRESS'] ?? ($emp['EMP_MAIL_ADDRESS'] ?: 'Agartala, Tripura'),
                            'ddo_code' => $appData['DDO_CODE'] ?? '1001',
                            'treasury_code' => $appData['TREASURY_CODE'] ?? '01',
                            'spouse_name' => $appData['SPOUSE_NAME'] ?? ($emp['FATHER_HUSBANT_NAME'] ?: ''),
                            'spouse_relation' => $appData['RELATION'] ?? 'Spouse',
                            'dob' => $emp['DATE_OF_BIRTH'] ?? null,
                            'doj' => $emp['DATE_OF_JOIN'] ?? null,
                            'last_fund_deduction' => $appData['LAST_FUND_DEDUCTION'] ?? null,
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
            'series_code' => $cleanSeries,
            'account_no' => $cleanAccount,
            'subscriber_name' => 'Sri Subscriber ' . $cleanAccount,
            'name_title' => 'Shri',
            'designation_title' => 'Mr',
            'designation' => 'Government Employee',
            'employee_code' => 'EMP' . str_pad($cleanAccount, 6, '0', STR_PAD_LEFT),
            'beneficiary_code' => 'BEN' . str_pad($cleanAccount, 6, '0', STR_PAD_LEFT),
            'mobile_no' => '',
            'personal_address' => 'Agartala, West Tripura, PIN: 799001',
            'ddo_code' => '1001',
            'treasury_code' => '01',
            'spouse_name' => '',
            'spouse_relation' => 'Spouse',
        ];
    }

    /**
     * Retrieve historic monthly subscriptions from Oracle 11g (gpffp.GPF_SUBSCRIPTION)
     */
    public function getSubscriptions(string $seriesCode, string $accountNo): array
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
                    SUBSCRIPTION_AMT, REFUND_AMT, WITHDRAWAL_AMT, ADVANCE_AMT, 
                    INT_ALLOW
                FROM gpffp.GPF_SUBSCRIPTION
                WHERE ACCOUNT_NO = :acct AND (SERIES_ID = :series OR :series_null IS NULL)
                ORDER BY PAY_SLIP_DATE ASC
            ";

            $stmt = oci_parse($conn, $query);
            oci_bind_by_name($stmt, ':acct', $cleanAccount);
            oci_bind_by_name($stmt, ':series', $cleanSeries);
            oci_bind_by_name($stmt, ':series_null', $cleanSeries);

            if (@oci_execute($stmt)) {
                $rows = [];
                while ($row = oci_fetch_assoc($stmt)) {
                    $rows[] = [
                        'financial_year' => $row['FIN_YEAR_CODE'],
                        'pay_slip_date' => $row['PAY_SLIP_DATE'],
                        'interest_date' => $row['INTEREST_DATE'],
                        'deposit' => (float) ($row['SUBSCRIPTION_AMT'] ?? 0) + (float) ($row['REFUND_AMT'] ?? 0),
                        'subscription' => (float) ($row['SUBSCRIPTION_AMT'] ?? 0),
                        'refund' => (float) ($row['REFUND_AMT'] ?? 0),
                        'withdrawal' => (float) ($row['WITHDRAWAL_AMT'] ?? 0),
                        'advance' => (float) ($row['ADVANCE_AMT'] ?? 0),
                        'voucher_no' => $row['VOUCHER_NO'],
                        'abstract_no' => $row['ABSTRACT_NO'],
                        'interest_allowed' => ($row['INT_ALLOW'] === 'Y'),
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
     * Fallback Series
     */
    protected function fallbackSeries(): Collection
    {
        return collect([
            ['id' => '16', 'name' => 'GRP - General Provident Fund (Series 16)'],
            ['id' => '18', 'name' => 'ADC - Autonomous District Council (Series 18)'],
            ['id' => '19', 'name' => 'EGRP - Education Department (Series 19)'],
            ['id' => '20', 'name' => 'AGRP - Agriculture Department (Series 20)'],
            ['id' => '21', 'name' => 'OGRP - Other Departments (Series 21)'],
        ]);
    }

    /**
     * Fallback DDOs
     */
    protected function fallbackDdos(): Collection
    {
        return collect([
            ['id' => '14768', 'name' => 'SPORTS OFFICER, TRIPURA SPORTS SCHOOL, KABIRAJTILLA'],
            ['id' => '14769', 'name' => 'ASSTT. DIRECTOR OF ARDD(BL) , BISHALGARH'],
            ['id' => '14770', 'name' => 'H.M.,MURABARI HIGH SCHOOL,BISHALGARH'],
            ['id' => '14771', 'name' => 'SUB-DIVISIONAL JUDICIAL MAGISTRATE, BISHALGARH'],
            ['id' => '14772', 'name' => 'DY. COMMANDANT, 1ST BN. TSR, GAKULNAGAR, BISHALGARH'],
        ]);
    }

    /**
     * Fallback Treasuries
     */
    protected function fallbackTreasuries(): Collection
    {
        return collect([
            ['id' => 'TPA01', 'name' => 'Kanchanpur Sub Treasury'],
            ['id' => 'TPA02', 'name' => 'Dharmanagar Treasury'],
            ['id' => 'TPA08', 'name' => 'Agartala Treasury No. I'],
            ['id' => 'TPA23', 'name' => 'Jampuijala Sub Treasury'],
            ['id' => 'TPA24', 'name' => 'Karbook Sub Treasury'],
            ['id' => 'TPAAC', 'name' => 'AC(ISS) Agartala'],
        ]);
    }
}
