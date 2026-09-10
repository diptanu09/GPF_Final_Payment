<?php

namespace App\Services\Integration;

use App\Models\InwardCase;
use App\Models\InterestRateSlab;
use Carbon\Carbon;
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

        if (!function_exists('oci_pconnect') && !function_exists('oci_connect')) {
            Log::warning('OracleMasterBridge: OCI8 extension is not loaded.');
            return null;
        }

        $host = config('database.connections.oracle_legacy.host', '192.168.100.247');
        $port = (int) config('database.connections.oracle_legacy.port', 1521);
        $sid = config('database.connections.oracle_legacy.database', 'db11g');
        $user = config('database.connections.oracle_legacy.username', 'gpffp');
        $pass = config('database.connections.oracle_legacy.password', 'gpffp');

        // Fast probe to avoid locking the PHP worker thread if the host is down or unreachable
        $socket = @fsockopen($host, $port, $errno, $errstr, 0.4);
        if (!$socket) {
            Log::info("OracleMasterBridge: Oracle server {$host}:{$port} is currently unreachable ({$errstr}). Operating in PostgreSQL replica fallback mode.");
            return null;
        }
        fclose($socket);

        $tns = "(DESCRIPTION=(CONNECT_TIMEOUT=2)(TRANSPORT_CONNECT_TIMEOUT=2)(RETRY_COUNT=0)(ADDRESS=(PROTOCOL=TCP)(HOST={$host})(PORT={$port}))(CONNECT_DATA=(SID={$sid})))";

        $conn = @oci_pconnect($user, $pass, $tns, 'AL32UTF8');
        if (!$conn) {
            $conn = @oci_connect($user, $pass, $tns, 'AL32UTF8');
        }

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
     * Retrieve Pension Types from Oracle 11g (MAS_PENSION_TYPE)
     */
    public function getPensionTypes(): Collection
    {
        $conn = $this->getConnection();
        if (!$conn) {
            return $this->fallbackPensionTypes();
        }

        try {
            $this->validateTableAccess('MAS_PENSION_TYPE');
            $stmt = oci_parse($conn, "
                SELECT PENSION_ID AS ID, PENSION_SHORT_DESCR AS CODE, PENSION_LONG_DESCR AS NAME 
                FROM MAS_PENSION_TYPE 
                ORDER BY TO_NUMBER(PENSION_ID) ASC
            ");
            
            if (@oci_execute($stmt)) {
                $types = [];
                while ($row = oci_fetch_assoc($stmt)) {
                    $types[] = [
                        'id' => trim((string) $row['ID']),
                        'code' => trim($row['CODE']),
                        'name' => trim($row['NAME']) . ' (' . trim($row['CODE']) . ')',
                        'short_descr' => trim($row['CODE']),
                        'long_descr' => trim($row['NAME']),
                    ];
                }
                oci_free_statement($stmt);
                if (!empty($types)) {
                    return collect($types);
                }
            }
        } catch (Exception $e) {
            Log::warning('OracleMasterBridge::getPensionTypes error: ' . $e->getMessage());
        }

        return $this->fallbackPensionTypes();
    }

    /**
     * Look up subscriber details from VLCS.GP_ACCOUNTS, VLCS.GP_APPLICATIONS, gpffp.GPF_APPLICATION, gpffp.LTA_APPLICATION, and VLCS.MM_EMPLOYEE
     */
    public function lookupSubscriber(string $seriesCode, string $accountNo): array
    {
        $conn = $this->getConnection();
        $cleanAccount = preg_replace('/[^0-9]/', '', $accountNo);
        $cleanSeries = trim($seriesCode);

        if ($conn) {
            try {
                $this->validateTableAccess('GP_ACCOUNTS');
                $this->validateTableAccess('GP_APPLICATIONS');
                $this->validateTableAccess('STATE_DDO');

                // 1. Check VLCS.GP_ACCOUNTS
                $accQuery = "
                    SELECT 
                        SERIES_ID, ACCOUNT_NO, ACC_HOLDER_NAME, EMP_CODE, BENE_CODE, 
                        MOBILE, ACCOUNT_CLOSED_TAG, DATE_OF_CLOSURE, OP_BALANCE_WITHDRAWL,
                        CL_BAL_WITHDRAWL, FIN_YEAR_CODE, APPLICATION_NO
                    FROM VLCS.GP_ACCOUNTS
                    WHERE SERIES_ID = :series AND ACCOUNT_NO = :acct
                      AND ROWNUM = 1
                ";

                $stmt = oci_parse($conn, $accQuery);
                oci_bind_by_name($stmt, ':series', $cleanSeries);
                oci_bind_by_name($stmt, ':acct', $cleanAccount);

                $acc = null;
                if (@oci_execute($stmt)) {
                    $acc = oci_fetch_assoc($stmt);
                    oci_free_statement($stmt);
                }

                $appNo = $acc['APPLICATION_NO'] ?? null;
                $empCode = trim($acc['EMP_CODE'] ?? '');

                // 2. Query VLCS.GP_APPLICATIONS (primary source for 105,000+ subscriber designations & demographics)
                $gpApp = null;
                if ($appNo) {
                    $appStmt = oci_parse($conn, "
                        SELECT 
                            APPLICATION_NO, APPLICANT_NAME, DESIGNATION, APPLICANT_ADDRESS,
                            APPLICANT_PIN_CODE, APPLICANT_STATE, DDO_CODE, GUARDIAN_NAME,
                            SPOUCE_NAME, DATE_OF_BIRTH, DATE_OF_JOINING, SEX
                        FROM VLCS.GP_APPLICATIONS
                        WHERE APPLICATION_NO = :app_no
                          AND ROWNUM = 1
                    ");
                    oci_bind_by_name($appStmt, ':app_no', $appNo);
                    if (@oci_execute($appStmt)) {
                        $gpApp = oci_fetch_assoc($appStmt);
                        oci_free_statement($appStmt);
                    }
                }

                if (!$gpApp && $cleanSeries && $cleanAccount) {
                    $appStmt2 = oci_parse($conn, "
                        SELECT 
                            APPLICATION_NO, APPLICANT_NAME, DESIGNATION, APPLICANT_ADDRESS,
                            APPLICANT_PIN_CODE, APPLICANT_STATE, DDO_CODE, GUARDIAN_NAME,
                            SPOUCE_NAME, DATE_OF_BIRTH, DATE_OF_JOINING, SEX
                        FROM VLCS.GP_APPLICATIONS
                        WHERE SERIES_ID = :series AND ACCOUNT_NO = :acct
                          AND ROWNUM = 1
                    ");
                    oci_bind_by_name($appStmt2, ':series', $cleanSeries);
                    oci_bind_by_name($appStmt2, ':acct', $cleanAccount);
                    if (@oci_execute($appStmt2)) {
                        $gpApp = oci_fetch_assoc($appStmt2);
                        oci_free_statement($appStmt2);
                    }
                }

                // 3. Query gpffp.GPF_APPLICATION for any recorded final payment details
                $gpfApp = null;
                $gpfStmt = oci_parse($conn, "
                    SELECT 
                        TITLE, DESG_TITLE, DESIGNATION, SPOUSE_NAME, RELATION, 
                        PERSONAL_ADDRESS, DDO_CODE, TREASURY_CODE, DATE_OF_EFFECT, LAST_FUND_DEDUCTION
                    FROM gpffp.GPF_APPLICATION
                    WHERE ACCOUNT_NO = :acct AND SERIES_ID = :series
                      AND ROWNUM = 1
                ");
                oci_bind_by_name($gpfStmt, ':acct', $cleanAccount);
                oci_bind_by_name($gpfStmt, ':series', $cleanSeries);
                if (@oci_execute($gpfStmt)) {
                    $gpfApp = oci_fetch_assoc($gpfStmt);
                    oci_free_statement($gpfStmt);
                }

                // 4. Query gpffp.LTA_APPLICATION
                $ltaApp = null;
                $ltaStmt = oci_parse($conn, "
                    SELECT ACC_HOLDER_NAME, DESIGNATION, ADDRESS, TREASURY, DOR, DODR
                    FROM gpffp.LTA_APPLICATION
                    WHERE ACCOUNT_NO = :acct AND SERIES_ID = :series
                      AND ROWNUM = 1
                ");
                oci_bind_by_name($ltaStmt, ':acct', $cleanAccount);
                oci_bind_by_name($ltaStmt, ':series', $cleanSeries);
                if (@oci_execute($ltaStmt)) {
                    $ltaApp = oci_fetch_assoc($ltaStmt);
                    oci_free_statement($ltaStmt);
                }

                // 5. Query VLCS.MM_EMPLOYEE (only if empCode is known)
                $empData = null;
                if ($empCode) {
                    $empStmt = oci_parse($conn, "
                        SELECT EMP_CODE, SERIES_ID, EMP_NAME, EMP_DESIGNATION, 
                               DATE_OF_BIRTH, DATE_OF_JOIN, FATHER_HUSBANT_NAME, 
                               EMP_MAIL_ADDRESS, OLD_EMP_CODE
                        FROM VLCS.MM_EMPLOYEE 
                        WHERE EMP_CODE = :emp AND ROWNUM = 1
                    ");
                    oci_bind_by_name($empStmt, ':emp', $empCode);
                    if (@oci_execute($empStmt)) {
                        $empData = oci_fetch_assoc($empStmt);
                        oci_free_statement($empStmt);
                    }
                }

                if ($acc || $gpApp || $gpfApp || $empData || $ltaApp) {
                    $isClosed = (strtoupper(trim($acc['ACCOUNT_CLOSED_TAG'] ?? '')) === 'Y');
                    $closureDate = ($acc['DATE_OF_CLOSURE'] ?? null) ? date('d-m-Y', strtotime($acc['DATE_OF_CLOSURE'])) : null;
                    $warningMessage = $isClosed ? "Notice: GPF account was closed on {$closureDate}." : null;

                    $rawName = trim($acc['ACC_HOLDER_NAME'] ?? ($gpApp['APPLICANT_NAME'] ?? ($gpfApp['SUBSCRIBER_NAME'] ?? ($empData['EMP_NAME'] ?? ''))));

                    // Designation Resolution Hierarchy
                    $rawDesg = trim($gpApp['DESIGNATION'] ?? '')
                        ?: trim($gpfApp['DESIGNATION'] ?? '')
                        ?: trim($ltaApp['DESIGNATION'] ?? '')
                        ?: trim($empData['EMP_DESIGNATION'] ?? '');

                    if (in_array(strtolower($rawDesg), ['n/a', 'na', 'null', 'none', '-', '.'], true)) {
                        $rawDesg = '';
                    }
                    $designation = $rawDesg ?: 'Government Employee';

                    // Personal Address Resolution Hierarchy
                    $personalAddress = trim($gpApp['APPLICANT_ADDRESS'] ?? '');
                    if ($personalAddress && !empty($gpApp['APPLICANT_PIN_CODE'])) {
                        if (!str_contains($personalAddress, trim($gpApp['APPLICANT_PIN_CODE']))) {
                            $personalAddress .= ', PIN - ' . trim($gpApp['APPLICANT_PIN_CODE']);
                        }
                    }
                    if (!$personalAddress) {
                        $personalAddress = trim($gpfApp['PERSONAL_ADDRESS'] ?? ($ltaApp['ADDRESS'] ?? ($empData['EMP_MAIL_ADDRESS'] ?? '')));
                    }

                    // DDO and Treasury Resolution via VLCS.STATE_DDO
                    $rawDdo = trim($gpApp['DDO_CODE'] ?? ($gpfApp['DDO_CODE'] ?? ''));
                    $ddoCode = $rawDdo;
                    $treasuryCode = trim($gpfApp['TREASURY_CODE'] ?? ($ltaApp['TREASURY'] ?? ''));

                    if ($rawDdo) {
                        $ddoStmt = oci_parse($conn, "SELECT DDO_CODE, DDO_TREASURY_CODE FROM VLCS.STATE_DDO WHERE DDO_CODE = :d AND ROWNUM = 1");
                        oci_bind_by_name($ddoStmt, ':d', $rawDdo);
                        $ddoRow = null;
                        if (@oci_execute($ddoStmt)) {
                            $ddoRow = oci_fetch_assoc($ddoStmt);
                            oci_free_statement($ddoStmt);
                        }

                        if (!$ddoRow) {
                            $ddoStmt2 = oci_parse($conn, "SELECT DDO_CODE, DDO_TREASURY_CODE FROM VLCS.STATE_DDO WHERE VLC_DDO = :d AND ROWNUM = 1");
                            oci_bind_by_name($ddoStmt2, ':d', $rawDdo);
                            if (@oci_execute($ddoStmt2)) {
                                $ddoRow = oci_fetch_assoc($ddoStmt2);
                                oci_free_statement($ddoStmt2);
                            }
                        }

                        if ($ddoRow) {
                            $ddoCode = trim($ddoRow['DDO_CODE']);
                            if (!$treasuryCode && !empty($ddoRow['DDO_TREASURY_CODE'])) {
                                $treasuryCode = trim($ddoRow['DDO_TREASURY_CODE']);
                            }
                        }
                    }

                    // Spouse / Guardian
                    $spouse = trim($gpApp['SPOUCE_NAME'] ?? '')
                        ?: trim($gpfApp['SPOUSE_NAME'] ?? '')
                        ?: trim($gpApp['GUARDIAN_NAME'] ?? '')
                        ?: trim($empData['FATHER_HUSBANT_NAME'] ?? '');
                    if (in_array(strtolower($spouse), ['n/a', 'na', 'null', 'none', '-'], true)) {
                        $spouse = '';
                    }

                    $dob = $gpApp['DATE_OF_BIRTH'] ?? ($empData['DATE_OF_BIRTH'] ?? null);
                    $doj = $gpApp['DATE_OF_JOINING'] ?? ($empData['DATE_OF_JOIN'] ?? null);

                    return [
                        'found_in_oracle' => true,
                        'is_closed' => $isClosed,
                        'closure_date' => $closureDate,
                        'warning' => $warningMessage,
                        'series_code' => $cleanSeries,
                        'account_no' => $cleanAccount,
                        'subscriber_name' => $rawName,
                        'name_title' => $gpfApp['TITLE'] ?? 'Shri',
                        'designation_title' => $gpfApp['DESG_TITLE'] ?? 'Mr',
                        'designation' => $designation,
                        'employee_code' => $acc['EMP_CODE'] ?? ($empData['EMP_CODE'] ?? ''),
                        'beneficiary_code' => $acc['BENE_CODE'] ?? '',
                        'mobile_no' => $acc['MOBILE'] ?? '',
                        'opening_balance' => (float) ($acc['OP_BALANCE_WITHDRAWL'] ?? 0),
                        'closing_balance' => (float) ($acc['CL_BAL_WITHDRAWL'] ?? 0),
                        'closing_fin_year' => $acc['FIN_YEAR_CODE'] ?? null,
                        'personal_address' => $personalAddress ?: 'Agartala, Tripura',
                        'ddo_code' => $ddoCode,
                        'treasury_code' => $treasuryCode,
                        'spouse_name' => $spouse,
                        'spouse_relation' => $gpfApp['RELATION'] ?? 'Spouse',
                        'dob' => $dob ? date('Y-m-d', strtotime($dob)) : null,
                        'doj' => $doj ? date('Y-m-d', strtotime($doj)) : null,
                        'last_fund_deduction' => $gpfApp['LAST_FUND_DEDUCTION'] ?? null,
                    ];
                }
            } catch (Exception $e) {
                Log::warning('OracleMasterBridge::lookupSubscriber error: ' . $e->getMessage());
            }
        }

        // Fallback to PostgreSQL 18 replica tables
        return $this->lookupSubscriberFromPgsql($cleanSeries, $cleanAccount);
    }

    /**
     * Fallback subscriber lookup from PostgreSQL 18 replica tables
     */
    protected function lookupSubscriberFromPgsql(string $seriesCode, string $accountNo): array
    {
        $cleanAccount = preg_replace('/[^0-9]/', '', $accountNo);
        $cleanSeries = trim($seriesCode);

        try {
            // 1. vlcs_gp_accounts
            $acc = null;
            if (\Illuminate\Support\Facades\Schema::hasTable('vlcs_gp_accounts')) {
                $acc = \Illuminate\Support\Facades\DB::table('vlcs_gp_accounts')
                    ->where('series_id', $cleanSeries)
                    ->where('account_no', $cleanAccount)
                    ->first();
            }

            $appNo = $acc->application_no ?? null;
            $empCode = trim($acc->emp_code ?? '');

            // 2. vlcs_gp_applications
            $gpApp = null;
            if (\Illuminate\Support\Facades\Schema::hasTable('vlcs_gp_applications')) {
                if ($appNo) {
                    $gpApp = \Illuminate\Support\Facades\DB::table('vlcs_gp_applications')->where('application_no', $appNo)->first();
                }
                if (!$gpApp && $cleanSeries && $cleanAccount) {
                    $gpApp = \Illuminate\Support\Facades\DB::table('vlcs_gp_applications')
                        ->where('series_id', $cleanSeries)
                        ->where('account_no', $cleanAccount)
                        ->first();
                }
            }

            // 3. gpffp_gpf_application
            $gpfApp = null;
            if (\Illuminate\Support\Facades\Schema::hasTable('gpffp_gpf_application')) {
                $gpfApp = \Illuminate\Support\Facades\DB::table('gpffp_gpf_application')
                    ->where('account_no', $cleanAccount)
                    ->where('series_id', $cleanSeries)
                    ->first();
            }

            // 4. vlcs_mm_employee
            $empData = null;
            if ($empCode && \Illuminate\Support\Facades\Schema::hasTable('vlcs_mm_employee')) {
                $empData = \Illuminate\Support\Facades\DB::table('vlcs_mm_employee')->where('emp_code', $empCode)->first();
            }

            if ($acc || $gpApp || $gpfApp || $empData) {
                $isClosed = (strtoupper(trim($acc->account_closed_tag ?? '')) === 'Y');
                $closureDate = ($acc->date_of_closure ?? null) ? date('d-m-Y', strtotime($acc->date_of_closure)) : null;
                $warningMessage = $isClosed ? "Notice: GPF account was closed on {$closureDate}." : null;

                $rawName = trim($acc->acc_holder_name ?? ($gpApp->applicant_name ?? ($gpfApp->subscriber_name ?? ($empData->emp_name ?? ''))));

                $rawDesg = trim($gpApp->designation ?? '')
                    ?: trim($gpfApp->designation ?? '')
                    ?: trim($empData->emp_designation ?? '');

                if (in_array(strtolower($rawDesg), ['n/a', 'na', 'null', 'none', '-', '.'], true)) {
                    $rawDesg = '';
                }
                $designation = $rawDesg ?: 'Government Employee';

                $personalAddress = trim($gpApp->applicant_address ?? '');
                if ($personalAddress && !empty($gpApp->applicant_pin_code)) {
                    if (!str_contains($personalAddress, trim($gpApp->applicant_pin_code))) {
                        $personalAddress .= ', PIN - ' . trim($gpApp->applicant_pin_code);
                    }
                }
                if (!$personalAddress) {
                    $personalAddress = trim($gpfApp->personal_address ?? ($empData->emp_mail_address ?? ''));
                }

                $rawDdo = trim($gpApp->ddo_code ?? ($gpfApp->ddo_code ?? ''));
                $ddoCode = $rawDdo;
                $treasuryCode = trim($gpfApp->treasury_code ?? '');

                if ($rawDdo && \Illuminate\Support\Facades\Schema::hasTable('vlcs_state_ddo')) {
                    $ddoRow = \Illuminate\Support\Facades\DB::table('vlcs_state_ddo')
                        ->where('ddo_code', $rawDdo)
                        ->orWhere('vlc_ddo', $rawDdo)
                        ->first();
                    if ($ddoRow) {
                        $ddoCode = trim($ddoRow->ddo_code);
                        if (!$treasuryCode && !empty($ddoRow->ddo_treasury_code)) {
                            $treasuryCode = trim($ddoRow->ddo_treasury_code);
                        }
                    }
                }

                $spouse = trim($gpApp->spouce_name ?? '')
                    ?: trim($gpfApp->spouse_name ?? '')
                    ?: trim($gpApp->guardian_name ?? '')
                    ?: trim($empData->father_husbant_name ?? '');

                if (in_array(strtolower($spouse), ['n/a', 'na', 'null', 'none', '-'], true)) {
                    $spouse = '';
                }

                $dob = $gpApp->date_of_birth ?? ($empData->date_of_birth ?? null);
                $doj = $gpApp->date_of_joining ?? ($empData->date_of_join ?? null);

                return [
                    'found_in_oracle' => true,
                    'is_closed' => $isClosed,
                    'closure_date' => $closureDate,
                    'warning' => $warningMessage,
                    'series_code' => $cleanSeries,
                    'account_no' => $cleanAccount,
                    'subscriber_name' => $rawName,
                    'name_title' => $gpfApp->title ?? 'Shri',
                    'designation_title' => $gpfApp->desg_title ?? 'Mr',
                    'designation' => $designation,
                    'employee_code' => $acc->emp_code ?? ($empData->emp_code ?? ''),
                    'beneficiary_code' => $acc->bene_code ?? '',
                    'mobile_no' => $acc->mobile ?? '',
                    'opening_balance' => (float) ($acc->op_balance_withdrawl ?? 0),
                    'closing_balance' => (float) ($acc->cl_bal_withdrawl ?? 0),
                    'closing_fin_year' => $acc->fin_year_code ?? null,
                    'personal_address' => $personalAddress ?: 'Agartala, Tripura',
                    'ddo_code' => $ddoCode,
                    'treasury_code' => $treasuryCode,
                    'spouse_name' => $spouse,
                    'spouse_relation' => $gpfApp->relation ?? 'Spouse',
                    'dob' => $dob ? date('Y-m-d', strtotime($dob)) : null,
                    'doj' => $doj ? date('Y-m-d', strtotime($doj)) : null,
                    'last_fund_deduction' => $gpfApp->last_fund_deduction ?? null,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('lookupSubscriberFromPgsql error: ' . $e->getMessage());
        }

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
     * Retrieve all available yearly balances and Base Financial Years for a subscriber from VLCS.GP_YEARLY_BALANCES
     */
    public function getAvailableClosingBalances(string $seriesCode, string $accountNo): Collection
    {
        $conn = $this->getConnection();
        $cleanAccount = preg_replace('/[^0-9]/', '', $accountNo);
        $cleanAccountInt = (int) $cleanAccount;

        if ($conn) {
            try {
                $this->validateTableAccess('GP_YEARLY_BALANCES');
                $stmt = oci_parse($conn, "
                    SELECT a.SERIES_ID, a.ACCOUNT_NO, a.FIN_YEAR_CODE, b.FIN_YEAR,
                           NVL(a.OP_BALANCE_WITHDRAWL, 0) AS OP_BALANCE_WITHDRAWL,
                           NVL(a.CL_BAL_WITHDRAWL, 0) AS CL_BAL_WITHDRAWL,
                           NVL(a.INTR_WITHDRAWL, 0) AS INTR_WITHDRAWL,
                           a.DATE_OF_CLOSURE, a.ACCOUNT_CLOSED_TAG
                    FROM VLCS.GP_YEARLY_BALANCES a
                    LEFT JOIN VLCS.MM_FINANCIAL_YEAR b ON TO_CHAR(a.FIN_YEAR_CODE) = TO_CHAR(b.FIN_YEAR_CODE)
                    WHERE (a.ACCOUNT_NO = :acct_num OR TO_CHAR(a.ACCOUNT_NO) = :acct_str)
                      AND a.FIN_YEAR_CODE IS NOT NULL
                      AND a.CL_BAL_WITHDRAWL IS NOT NULL
                    ORDER BY TO_NUMBER(REGEXP_SUBSTR(a.FIN_YEAR_CODE, '^[0-9]+')) DESC
                ");
                oci_bind_by_name($stmt, ':acct_num', $cleanAccountInt);
                oci_bind_by_name($stmt, ':acct_str', $cleanAccount);

                if (@oci_execute($stmt)) {
                    $balances = [];
                    while ($row = oci_fetch_assoc($stmt)) {
                        $finYearLabel = trim($row['FIN_YEAR'] ?? '');
                        if (empty($finYearLabel)) {
                            $code = (int) $row['FIN_YEAR_CODE'];
                            $startYr = 1998 + $code;
                            $finYearLabel = "$startYr-" . ($startYr + 1);
                        }

                        $balances[] = [
                            'fin_year_code' => trim($row['FIN_YEAR_CODE']),
                            'financial_year' => $finYearLabel,
                            'closing_balance' => (float) $row['CL_BAL_WITHDRAWL'],
                            'opening_balance' => (float) $row['OP_BALANCE_WITHDRAWL'],
                            'interest' => (float) $row['INTR_WITHDRAWL'],
                            'is_closed' => strtoupper(trim($row['ACCOUNT_CLOSED_TAG'] ?? '')) === 'Y',
                        ];
                    }
                    oci_free_statement($stmt);
                    if (!empty($balances)) {
                        return collect($balances);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('OracleMasterBridge::getAvailableClosingBalances error: ' . $e->getMessage());
            }
        }

        // Fallback list of financial years
        return collect([
            ['fin_year_code' => '26', 'financial_year' => '2024-2025', 'closing_balance' => 0.0, 'opening_balance' => 0.0, 'interest' => 0.0, 'is_closed' => false],
            ['fin_year_code' => '25', 'financial_year' => '2023-2024', 'closing_balance' => 0.0, 'opening_balance' => 0.0, 'interest' => 0.0, 'is_closed' => false],
            ['fin_year_code' => '24', 'financial_year' => '2022-2023', 'closing_balance' => 0.0, 'opening_balance' => 0.0, 'interest' => 0.0, 'is_closed' => false],
        ]);
    }

    /**
     * Retrieve raw monthly vouchers from VLCS.GP_VOUCHER_ACC_DETAILS
     */
    public function getVouchers(string $seriesCode, string $accountNo): array
    {
        $conn = $this->getConnection();
        $cleanAccount = preg_replace('/[^0-9]/', '', $accountNo);
        $cleanAccountInt = (int) $cleanAccount;
        $vouchersByMonth = [];

        if ($conn) {
            try {
                $this->validateTableAccess('GP_VOUCHER_ACC_DETAILS');
                $stmt = oci_parse($conn, "
                    SELECT TO_CHAR(a.PAY_SLIP_DATE, 'YYYY-MM') as CAL_MONTH,
                           TO_CHAR(a.PAY_SLIP_DATE, 'YYYY-MM-DD') as PAY_SLIP_DATE,
                           NVL(a.SUBSCRIPTION_AMT, 0) as SUBSCRIPTION_AMT,
                           NVL(a.REFUND_AMT, 0) as REFUND_AMT,
                           NVL(a.WITHDRAWAL_AMT, 0) as WITHDRAWAL_AMT,
                           a.VOUCHER_NO, a.ABSTRACT_NO
                    FROM VLCS.GP_VOUCHER_ACC_DETAILS a
                    WHERE (a.ACCOUNT_NO = :acct_num OR TO_CHAR(a.ACCOUNT_NO) = :acct_str)
                      AND a.POSTING_TYPE != 'F' AND a.TAG = 'Y'
                    ORDER BY a.PAY_SLIP_DATE ASC
                ");
                oci_bind_by_name($stmt, ':acct_num', $cleanAccountInt);
                oci_bind_by_name($stmt, ':acct_str', $cleanAccount);

                if (@oci_execute($stmt)) {
                    while ($row = oci_fetch_assoc($stmt)) {
                        $m = $row['CAL_MONTH'];
                        $vouchersByMonth[$m] = [
                            'deposit' => (float) ($row['SUBSCRIPTION_AMT'] + $row['REFUND_AMT']),
                            'withdrawal' => (float) $row['WITHDRAWAL_AMT'],
                            'subscription' => (float) $row['SUBSCRIPTION_AMT'],
                            'refund' => (float) $row['REFUND_AMT'],
                            'voucher_no' => $row['VOUCHER_NO'] ?? '',
                            'abstract_no' => $row['ABSTRACT_NO'] ?? '',
                            'pay_slip_date' => $row['PAY_SLIP_DATE'],
                        ];
                    }
                    oci_free_statement($stmt);
                }
            } catch (\Throwable $e) {
                Log::warning('OracleMasterBridge::getVouchers error: ' . $e->getMessage());
            }
        }

        return $vouchersByMonth;
    }

    /**
     * Build multi-year monthly calculation ledger starting from Base Financial Year up to cutoff date
     */
    public function buildMultiYearLedger(InwardCase $case, ?string $baseFinYear = null, ?float $baseOpeningBal = null): array
    {
        $availableBalances = $this->getAvailableClosingBalances($case->series_code, $case->account_no);
        $vouchersByMonth = $this->getVouchers($case->series_code, $case->account_no);

        // 1. Resolve Base Financial Year & Base Opening Balance
        if ($baseFinYear) {
            $matched = $availableBalances->firstWhere('financial_year', $baseFinYear);
            if ($matched && $baseOpeningBal === null) {
                $baseOpeningBal = (float) $matched['closing_balance'];
            }
        }

        if (!$baseFinYear || $baseOpeningBal === null) {
            $latestClosed = $availableBalances->first(fn ($b) => $b['closing_balance'] > 0) ?? $availableBalances->first();
            if ($latestClosed && $latestClosed['closing_balance'] > 0) {
                $baseFinYear = $latestClosed['financial_year'];
                $baseOpeningBal = (float) $latestClosed['closing_balance'];
            } else {
                $baseFinYear = '2023-2024';
                $baseOpeningBal = 0.00;
            }
        }

        // 2. Resolve calculation end date (interest allowed upto)
        $cutoffDate = app(\App\Services\Calculation\CutoffRuleResolver::class)->resolveCutoffDate($case);
        
        // Start date = April 1st of the year immediately following baseFinYear
        $startYear = (int) substr($baseFinYear, 0, 4) + 1;
        $startDate = Carbon::create($startYear, 4, 1);

        // Ensure calculation spans up to cutoff date or at least 1 full year
        $calcEndDate = $cutoffDate->greaterThan($startDate) ? $cutoffDate : (clone $startDate)->addMonths(11);
        if ($calcEndDate->diffInMonths($startDate) < 11) {
            $calcEndDate = (clone $startDate)->addMonths(11);
        }

        $currentDate = clone $startDate;
        $runningOpening = $baseOpeningBal;
        $runningProgressive = 0.00;
        $yearlyInterest = 0.00;
        $yearlyDeposits = 0.00;
        $yearlyWithdrawals = 0.00;
        $currentFY = null;
        $monthlyLedger = [];
        $delayPeriodStarted = false;
        $delayOpeningBal = 0.00;

        while ($currentDate->lessThanOrEqualTo($calcEndDate)) {
            $calMonth = $currentDate->format('Y-m');
            $m = $currentDate->month;
            $y = $currentDate->year;
            $finYear = ($m >= 4) ? "$y-" . ($y + 1) : ($y - 1) . "-$y";
            $accountingMonth = ($m >= 4) ? $m - 3 : $m + 9;
            $isCutMonth = $currentDate->isSameMonth($cutoffDate);
            $isDelayed = $currentDate->greaterThan($cutoffDate);

            // Transition to new FY (normal period): capitalize prior year's interest & net transactions
            if (!$isDelayed && $currentFY !== null && $finYear !== $currentFY) {
                $runningOpening = $runningOpening + $yearlyDeposits - $yearlyWithdrawals + round($yearlyInterest);
                $yearlyInterest = 0.00;
                $yearlyDeposits = 0.00;
                $yearlyWithdrawals = 0.00;
                $runningProgressive = 0.00;
            }
            $currentFY = $finYear;

            // When entering delay period, capture closing balance up to cut month as delay opening balance
            if ($isDelayed && !$delayPeriodStarted) {
                $delayPeriodStarted = true;
                $delayOpeningBal = $runningOpening + $yearlyDeposits - $yearlyWithdrawals + round($yearlyInterest);
                $runningOpening = $delayOpeningBal;
                $runningProgressive = 0.00;
            }

            $voucher = $vouchersByMonth[$calMonth] ?? null;
            $deposit = $voucher ? (float) $voucher['deposit'] : 0.00;
            $withdrawal = $voucher ? (float) $voucher['withdrawal'] : 0.00;
            $rate = InterestRateSlab::getRateForDate($currentDate->toDateString()) ?? 7.1000;

            if ($isCutMonth) {
                // Cut Month: Values suppressed for interest calculation
                $runningProgressive = 0.00;
                $monthlyInt = 0.00;
                $delayInt = 0.00;
            } elseif ($isDelayed) {
                // Delayed Period: interest computed into delay_interest
                if ($runningProgressive == 0) {
                    $runningProgressive = $delayOpeningBal + $deposit - $withdrawal;
                } else {
                    $runningProgressive += ($deposit - $withdrawal);
                }
                $monthlyInt = 0.00;
                $delayInt = round(($runningProgressive * $rate) / 1200, 2);
            } else {
                // Normal Active Period
                if ($accountingMonth === 1 || $runningProgressive == 0) {
                    $runningProgressive = $runningOpening + $deposit - $withdrawal;
                } else {
                    $runningProgressive += ($deposit - $withdrawal);
                }
                $monthlyInt = round(($runningProgressive * $rate) / 1200, 2);
                $delayInt = 0.00;
                $yearlyInterest += $monthlyInt;
                $yearlyDeposits += $deposit;
                $yearlyWithdrawals += $withdrawal;
            }

            $monthlyLedger[] = [
                'financial_year' => $finYear,
                'calendar_month' => $calMonth,
                'pay_slip_date' => $currentDate->format('Y-m-d'),
                'interest_date' => $currentDate->format('Y-m-d'),
                'accounting_month' => $accountingMonth,
                'opening_balance' => round($runningOpening, 2),
                'deposit' => $deposit,
                'withdrawal' => $withdrawal,
                'rate_of_interest' => $rate,
                'interest_on_deposit' => !$isDelayed,
                'progressive_balance' => round($runningProgressive, 2),
                'actual_interest' => $monthlyInt,
                'delay_interest' => $delayInt,
                'is_cut_month' => $isCutMonth,
                'is_adjustment' => false,
                'voucher_no' => $voucher['voucher_no'] ?? null,
                'abstract_no' => $voucher['abstract_no'] ?? null,
            ];

            $currentDate->addMonth();
        }

        return [
            'base_fin_year' => $baseFinYear,
            'opening_balance' => $baseOpeningBal,
            'available_base_years' => $availableBalances,
            'monthly_ledger' => $monthlyLedger,
        ];
    }

    /**
     * Retrieve historic monthly subscriptions from Oracle 11g (gpffp.GPF_SUBSCRIPTION & VLCS tables)
     */
    public function getSubscriptions(string $seriesCode, string $accountNo, ?string $regdNo = null): array
    {
        $conn = $this->getConnection();
        $cleanAccount = preg_replace('/[^0-9]/', '', $accountNo);
        $cleanSeries = trim($seriesCode);

        if ($conn) {
            try {
                $this->validateTableAccess('GPF_SUBSCRIPTION');

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
                    WHERE (REGD_NO = :regd OR (TRIM(ACCOUNT_NO) = :acct AND SERIES_ID = :series))
                    ORDER BY PAY_SLIP_DATE ASC, INTEREST_DATE ASC
                ";

                $stmt = oci_parse($conn, $query);
                $regdParam = $regdNo ?: '0';
                oci_bind_by_name($stmt, ':regd', $regdParam);
                oci_bind_by_name($stmt, ':acct', $cleanAccount);
                oci_bind_by_name($stmt, ':series', $cleanSeries);

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
                    if (!empty($rows)) {
                        return $rows;
                    }
                }
            } catch (Exception $e) {
                Log::warning('OracleMasterBridge::getSubscriptions error: ' . $e->getMessage());
            }
        }

        // Fallback to PostgreSQL 18 replica table
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('gpffp_gpf_subscription')) {
                $q = \Illuminate\Support\Facades\DB::table('gpffp_gpf_subscription');
                if ($regdNo) {
                    $q->where('regd_no', $regdNo);
                } else {
                    $q->where('account_no', $cleanAccount)->where('series_id', $cleanSeries);
                }
                $pgSubs = $q->orderBy('pay_slip_date', 'asc')->get();

                return $pgSubs->map(function ($row) {
                    $subAmt = (float) ($row->subscription_amt ?? 0);
                    $refAmt = (float) ($row->refund_amt ?? 0);
                    $othAmt = (float) ($row->others_amt ?? 0);
                    $wthAmt = (float) ($row->withdrawal_amt ?? 0);
                    $advAmt = (float) ($row->advance_amt ?? 0);

                    return [
                        'financial_year' => $row->fin_year_code,
                        'pay_slip_date' => $row->pay_slip_date,
                        'interest_date' => $row->interest_date ?? $row->pay_slip_date,
                        'deposit' => $subAmt + $refAmt + $othAmt,
                        'subscription' => $subAmt,
                        'refund' => $refAmt,
                        'others' => $othAmt,
                        'withdrawal' => $wthAmt + $advAmt,
                        'advance' => $advAmt,
                        'voucher_no' => $row->voucher_no ?? '',
                        'abstract_no' => $row->abstract_no ?? '',
                        'adjustment_no' => ($row->adjustment_no !== '0') ? $row->adjustment_no : null,
                        'interest_allowed' => ($row->int_allow !== 'N'),
                    ];
                })->all();
            }
        } catch (\Throwable $e) {
            Log::warning('getSubscriptions pgsql error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Retrieve missing credits from VLCS.GP_MISSING_CREDIT
     */
    public function getMissingCredits(string $seriesCode, string $accountNo): array
    {
        $conn = $this->getConnection();
        $cleanAccount = preg_replace('/[^0-9]/', '', $accountNo);
        $cleanSeries = trim($seriesCode);

        if ($conn) {
            try {
                $this->validateTableAccess('GP_MISSING_CREDIT');

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
                    if (!empty($rows)) {
                        return $rows;
                    }
                }
            } catch (Exception $e) {
                Log::warning('OracleMasterBridge::getMissingCredits error: ' . $e->getMessage());
            }
        }

        // Fallback to PostgreSQL 18 replica table
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('vlcs_gp_missing_credit')) {
                return \Illuminate\Support\Facades\DB::table('vlcs_gp_missing_credit')
                    ->where('series_id', $cleanSeries)
                    ->where('account_no', $cleanAccount)
                    ->where(function ($q) {
                        $q->whereNull('clear_tag')->orWhere('clear_tag', '!=', 'Y');
                    })
                    ->orderBy('slip_date', 'asc')
                    ->get()
                    ->map(fn ($r) => [
                        'slip_date' => $r->slip_date,
                        'fin_year_code' => $r->fin_year_code,
                        'clear_tag' => $r->clear_tag,
                    ])
                    ->all();
            }
        } catch (\Throwable $e) {
            Log::warning('getMissingCredits pgsql error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Fallback Series
     */
    protected function fallbackSeries(): Collection
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('vlcs_mm_gpf_series')) {
                $rows = \Illuminate\Support\Facades\DB::table('vlcs_mm_gpf_series')
                    ->whereNotNull('series_descr')
                    ->orderByRaw('series_id::numeric ASC')
                    ->get();
                if ($rows->isNotEmpty()) {
                    return $rows->map(fn ($r) => [
                        'id' => trim((string) $r->series_id),
                        'code' => trim($r->series_descr),
                        'name' => trim($r->series_descr) . ' (Series ' . trim((string) $r->series_id) . ')',
                    ]);
                }
            }
        } catch (\Throwable) {}

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
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('vlcs_state_ddo')) {
                $rows = \Illuminate\Support\Facades\DB::table('vlcs_state_ddo')
                    ->whereNotNull('ddo_desg')
                    ->orderBy('ddo_desg', 'asc')
                    ->get();
                if ($rows->isNotEmpty()) {
                    return $rows->map(fn ($r) => [
                        'id' => trim((string) $r->ddo_code),
                        'name' => trim($r->ddo_desg),
                        'treasury_code' => trim($r->ddo_treasury_code ?? ''),
                        'phone' => trim((string) ($r->phone_no ?? '')),
                        'email' => trim($r->ddo_email_id ?? ''),
                    ]);
                }
            }
        } catch (\Throwable) {}

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
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('vlcs_state_treasury')) {
                $rows = \Illuminate\Support\Facades\DB::table('vlcs_state_treasury')
                    ->whereNotNull('tres_name')
                    ->orderBy('tres_name', 'asc')
                    ->get();
                if ($rows->isNotEmpty()) {
                    return $rows->map(fn ($r) => [
                        'id' => trim((string) $r->tres_code),
                        'name' => trim($r->tres_name),
                        'email' => trim($r->email_id ?? ''),
                    ]);
                }
            }
        } catch (\Throwable) {}

        return collect([
            ['id' => 'TPA01', 'name' => 'Kanchanpur Sub Treasury', 'email' => ''],
            ['id' => 'TPA02', 'name' => 'Dharmanagar Treasury', 'email' => ''],
            ['id' => 'TPA06', 'name' => 'Kamalpur Sub Treasury', 'email' => 'stokmnp@yahoo.com'],
            ['id' => 'TPA08', 'name' => 'Agartala Treasury No. I', 'email' => ''],
            ['id' => 'TPA23', 'name' => 'Jampuijala Sub Treasury', 'email' => ''],
            ['id' => 'TPA24', 'name' => 'Karbook Sub Treasury', 'email' => ''],
        ]);
    }

    /**
     * Fallback Pension Types
     */
    protected function fallbackPensionTypes(): Collection
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('mas_pension_type')) {
                $rows = \Illuminate\Support\Facades\DB::table('mas_pension_type')
                    ->orderByRaw('pension_id::numeric ASC')
                    ->get();
                if ($rows->isNotEmpty()) {
                    return $rows->map(fn ($r) => [
                        'id' => trim((string) $r->pension_id),
                        'code' => trim($r->pension_short_descr),
                        'name' => trim($r->pension_long_descr) . ' (' . trim($r->pension_short_descr) . ')',
                        'short_descr' => trim($r->pension_short_descr),
                        'long_descr' => trim($r->pension_long_descr),
                    ]);
                }
            }
        } catch (\Throwable) {}

        return collect([
            ['id' => '1', 'code' => 'SUP', 'name' => 'Superannuation (SUP)', 'short_descr' => 'SUP', 'long_descr' => 'Superannuation'],
            ['id' => '2', 'code' => 'FAM', 'name' => 'Family Pension (FAM)', 'short_descr' => 'FAM', 'long_descr' => 'Family'],
            ['id' => '3', 'code' => 'VOL', 'name' => 'Voluntary Retirement (VOL)', 'short_descr' => 'VOL', 'long_descr' => 'Voluntary'],
            ['id' => '4', 'code' => 'DISM', 'name' => 'Dismissal (DISM)', 'short_descr' => 'DISM', 'long_descr' => 'Dismissal'],
            ['id' => '5', 'code' => 'SUSP', 'name' => 'Suspension (SUSP)', 'short_descr' => 'SUSP', 'long_descr' => 'Suspension'],
            ['id' => '6', 'code' => 'BLTR', 'name' => 'Balance Transfer (BLTR)', 'short_descr' => 'BLTR', 'long_descr' => 'Balance Transfer'],
            ['id' => '7', 'code' => 'MISN', 'name' => 'Missing (MISN)', 'short_descr' => 'MISN', 'long_descr' => 'Missing'],
        ]);
    }
}
