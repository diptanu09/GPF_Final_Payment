<?php

namespace App\Console\Commands;

use App\Enums\CaseType;
use App\Enums\CaseWorkflowStatus;
use App\Models\Authority;
use App\Models\CalculationMonthlyBreakdown;
use App\Models\CalculationRun;
use App\Models\CaseNominee;
use App\Models\DigitalSignature;
use App\Models\InwardCase;
use App\Models\User;
use App\Models\WorkflowHistory;
use App\Services\Integration\OracleMasterBridge;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SyncRealDataFromOracleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gpf:sync-real-oracle-data {--clean : Purge all sample data before importing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up sample dummy records and fetch real case, subscriber, and ledger data from Oracle 11g';

    public function __construct(protected OracleMasterBridge $oracleBridge)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('===============================================================');
        $this->info('  GPF FINAL PAYMENT SYSTEM: ORACLE 11g REAL DATA SYNCHRONIZATION');
        $this->info('===============================================================');

        $conn = $this->oracleBridge->getConnection();
        if (!$conn) {
            $this->error('Failed to establish connection to Oracle 11g (192.168.100.247:1521/db11g).');
            return Command::FAILURE;
        }

        $this->info('✓ Connected successfully to Oracle 11g database (gpffp / VLCS schemas).');

        // Step 1: Clean up sample test entries in PostgreSQL
        $this->info(PHP_EOL . '1. Cleaning up sample dummy data from PostgreSQL 18...');
        
        DB::statement('TRUNCATE TABLE workflow_histories, authorities, digital_signatures, calculation_monthly_breakdowns, calculation_runs, case_nominees, inward_cases CASCADE');
        $this->info('✓ Purged all sample inward cases, calculations, breakdowns, and dummy nominees.');

        // Step 2: Import / Sync real institutional users from gpffp.USER_ACCOUNTS
        $this->info(PHP_EOL . '2. Synchronizing system users from gpffp.USER_ACCOUNTS...');
        $userMap = $this->syncUsers($conn);
        $this->info('✓ Institutional users mapped: ' . count($userMap));

        $usedRegNos = [];

        // Step 3: Import Real GPF Inward & Case Status Records
        $this->info(PHP_EOL . '3. Fetching real cases from gpffp.GPF_INWARD & gpffp.GPF_CASE_STATUS...');
        $importedCases = $this->importRealInwardCases($conn, $userMap, $usedRegNos);
        $this->info("✓ Imported {$importedCases} real primary GPF cases from Oracle 11g.");

        // Step 4: Import Real LTA Applications (85 records)
        $this->info(PHP_EOL . '4. Fetching real LTA cases from gpffp.LTA_APPLICATION...');
        $importedLta = $this->importLtaCases($conn, $userMap, $usedRegNos);
        $this->info("✓ Imported {$importedLta} real LTA cases from Oracle 11g.");

        $totalCases = InwardCase::count();
        $totalRuns = CalculationRun::count();
        $totalBreakdowns = CalculationMonthlyBreakdown::count();
        $totalNominees = CaseNominee::count();

        $this->info(PHP_EOL . '===============================================================');
        $this->info('  SYNCHRONIZATION COMPLETED SUCCESSFULLY');
        $this->info('===============================================================');
        $this->table(
            ['Entity', 'PostgreSQL Record Count'],
            [
                ['Total Real Inward Cases', $totalCases],
                ['Calculations / Ledgers', $totalRuns],
                ['Monthly Ledger Breakdowns', $totalBreakdowns],
                ['Beneficiary Nominees', $totalNominees],
                ['Active System Users', User::count()],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Sync users from gpffp.USER_ACCOUNTS
     */
    protected function syncUsers(mixed $conn): array
    {
        $userMap = [];
        
        // Default administrative role accounts
        $defaults = [
            ['username' => 'admin', 'name' => 'System Administrator', 'role' => 'admin', 'email' => 'admin@tripura.gov.in'],
            ['username' => 'deo', 'name' => 'Data Entry Officer', 'role' => 'deo', 'email' => 'deo@tripura.gov.in'],
            ['username' => 'checker', 'name' => 'Assistant Accounts Officer', 'role' => 'checker', 'email' => 'aao@tripura.gov.in'],
            ['username' => 'approver', 'name' => 'Senior Accounts Officer', 'role' => 'approver', 'email' => 'ao@tripura.gov.in'],
            ['username' => 'dispatchexec', 'name' => 'Outward Dispatch Executive', 'role' => 'dispatch', 'email' => 'dispatch@tripura.gov.in'],
        ];

        foreach ($defaults as $d) {
            $u = User::updateOrCreate(
                ['username' => $d['username']],
                [
                    'name' => $d['name'],
                    'role' => $d['role'],
                    'email' => $d['email'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );
            $userMap[$d['username']] = $u->id;
        }

        // Fetch from gpffp.USER_ACCOUNTS
        $stmt = oci_parse($conn, "SELECT FULL_NAME, USERNAME, USER_ROLE, USER_STATUS FROM gpffp.USER_ACCOUNTS");
        if (@oci_execute($stmt)) {
            while ($row = oci_fetch_assoc($stmt)) {
                $username = strtolower(trim($row['USERNAME']));
                $fullName = trim($row['FULL_NAME']) ?: ucfirst($username);
                $oracleRole = (int) ($row['USER_ROLE'] ?? 1);
                $isActive = (strtoupper(trim($row['USER_STATUS'] ?? 'Y')) === 'Y');

                $role = match ($oracleRole) {
                    1 => 'admin',
                    2 => 'deo',
                    3 => 'checker',
                    4 => 'approver',
                    5 => 'dispatch',
                    default => 'deo',
                };

                $u = User::updateOrCreate(
                    ['username' => $username],
                    [
                        'name' => $fullName,
                        'role' => $role,
                        'email' => "{$username}@tripura.gov.in",
                        'password' => Hash::make('password'),
                        'is_active' => $isActive,
                    ]
                );
                $userMap[$username] = $u->id;
            }
            oci_free_statement($stmt);
        }

        return $userMap;
    }

    /**
     * Import Real Inward & Case Status Records
     */
    protected function importRealInwardCases(mixed $conn, array $userMap, array &$usedRegNos): int
    {
        $count = 0;

        $query = "
            SELECT 
                i.SL_NO AS INWARD_SL, i.REGD_NO, i.SERIES_ID, i.ACCOUNT_NO, 
                i.LETTER_TYPE, i.LETTER_NO, i.APPLIED_DATE, i.FIN_YEAR_CODE, 
                i.CASE_TYPE, i.SECTION, i.RECORD_DAK_NO, i.RECORD_DAK_DATE, 
                i.MARK_TO, i.MARK_DATE, i.CREATE_USER, i.CREATE_DATE,
                s.SUBSCRIBER_NAME, s.EMPLOYEE_CODE, s.BENEFICIARY_CODE, 
                s.MOBILE_NO, s.PENSION_TYPE, s.CASE_STATUS, s.ENTERED_DATE,
                s.CALCULATION_DATE, s.CHECKED_DATE, s.APPROVED_DATE,
                a.TITLE, a.DESG_TITLE, a.DESIGNATION, a.SPOUSE_NAME, a.RELATION,
                a.PERSONAL_ADDRESS, a.DDO_CODE, a.TREASURY_CODE, a.DATE_OF_EFFECT,
                a.LAST_FUND_DEDUCTION, a.DEBIT_DURING_YEAR, a.DATE_OF_LTA, a.LTA_TO_WHOM
            FROM gpffp.GPF_INWARD i
            LEFT JOIN gpffp.GPF_CASE_STATUS s ON i.REGD_NO = s.REGD_NO
            LEFT JOIN gpffp.GPF_APPLICATION a ON i.REGD_NO = a.REGD_NO
            ORDER BY i.SL_NO ASC
        ";

        $stmt = oci_parse($conn, $query);
        if (!@oci_execute($stmt)) {
            $e = oci_error($stmt);
            $this->error('Failed to query GPF_INWARD: ' . ($e['message'] ?? ''));
            return 0;
        }

        $defaultUserId = $userMap['admin'] ?? User::first()?->id;

        while ($row = oci_fetch_assoc($stmt)) {
            $regdNo = trim($row['REGD_NO']);
            if (!$regdNo) continue;

            $seriesCode = trim($row['SERIES_ID']);
            $accountNo = trim($row['ACCOUNT_NO']);
            $rawStatus = (int) ($row['CASE_STATUS'] ?? 1);
            $statusEnum = CaseWorkflowStatus::tryFrom($rawStatus) ?? CaseWorkflowStatus::DRAFT;

            $rawCaseType = strtoupper(trim($row['CASE_TYPE'] ?? 'F'));
            $caseTypeEnum = match ($rawCaseType) {
                'F' => CaseType::NORMAL_SUPERANNUATION,
                'R' => CaseType::RESIGNATION,
                'L' => CaseType::LTA_SPECIAL,
                'C' => CaseType::CORRESPONDENCE,
                'B' => CaseType::BALANCE_TRANSFER,
                'D' => CaseType::DEATH_IN_SERVICE,
                default => CaseType::NORMAL_SUPERANNUATION,
            };

            // Disambiguate duplicate registration numbers
            $candidate = $regdNo;
            $idx = 1;
            while (isset($usedRegNos[$candidate]) || InwardCase::withTrashed()->where('registration_no', $candidate)->exists()) {
                $candidate = "{$regdNo}-{$rawCaseType}" . ($idx > 1 ? "-{$idx}" : "");
                $idx++;
            }
            $finalRegdNo = $candidate;
            $usedRegNos[$finalRegdNo] = true;

            // Subscriber Lookup from VLCS
            $subMaster = $this->oracleBridge->lookupSubscriber($seriesCode, $accountNo);

            $subscriberName = trim($row['SUBSCRIBER_NAME'] ?? '') ?: ($subMaster['subscriber_name'] ?? 'Subscriber ' . $accountNo);
            $designation = trim($row['DESIGNATION'] ?? '') ?: ($subMaster['designation'] ?? 'Government Employee');
            $address = trim($row['PERSONAL_ADDRESS'] ?? '') ?: ($subMaster['personal_address'] ?? 'Agartala, Tripura');
            $ddoCode = trim($row['DDO_CODE'] ?? '') ?: ($subMaster['ddo_code'] ?? '6016');
            $treasuryCode = trim($row['TREASURY_CODE'] ?? '') ?: ($subMaster['treasury_code'] ?? 'TPA06');
            $mobileNo = trim($row['MOBILE_NO'] ?? '') ?: ($subMaster['mobile_no'] ?? null);
            $empCode = trim($row['EMPLOYEE_CODE'] ?? '') ?: ($subMaster['employee_code'] ?? null);
            $beneCode = trim($row['BENEFICIARY_CODE'] ?? '') ?: ($subMaster['beneficiary_code'] ?? null);

            $appliedDate = $row['APPLIED_DATE'] ? Carbon::parse($row['APPLIED_DATE']) : now();
            $eventDate = $row['DATE_OF_EFFECT'] ? Carbon::parse($row['DATE_OF_EFFECT']) : $appliedDate;
            $lastDeduction = $row['LAST_FUND_DEDUCTION'] ? Carbon::parse($row['LAST_FUND_DEDUCTION']) : null;
            $createDate = $row['CREATE_DATE'] ? Carbon::parse($row['CREATE_DATE']) : now();

            $creatorUsername = strtolower(trim($row['CREATE_USER'] ?? ''));
            $creatorId = $userMap[$creatorUsername] ?? $defaultUserId;

            // Fetch series name
            $seriesObj = $this->oracleBridge->getSeriesList()->firstWhere('id', $seriesCode);
            $seriesName = $seriesObj['code'] ?? "Series {$seriesCode}";

            $inwardCase = InwardCase::create([
                'registration_no' => $finalRegdNo,
                'diary_number' => $row['LETTER_NO'] ?: ("DAK/" . ($row['RECORD_DAK_NO'] ?: rand(1000, 9999))),
                'diary_date' => $row['RECORD_DAK_DATE'] ? Carbon::parse($row['RECORD_DAK_DATE']) : $appliedDate,
                'series_code' => $seriesCode,
                'series_name' => $seriesName,
                'account_no' => $accountNo,
                'subscriber_name_cache' => $subscriberName,
                'name_title' => trim($row['TITLE'] ?? '') ?: 'Shri',
                'designation_title' => trim($row['DESG_TITLE'] ?? '') ?: 'Mr',
                'designation' => $designation,
                'case_type' => $caseTypeEnum,
                'pension_type_id' => (string) ($row['PENSION_TYPE'] ?? '1'),
                'section' => 'Fund Section ' . ($row['SECTION'] ?? 'I'),
                'ddo_code' => $ddoCode,
                'treasury_code' => $treasuryCode,
                'event_date' => $eventDate,
                'last_fund_deduction' => $lastDeduction,
                'debit_during_year' => (float) ($row['DEBIT_DURING_YEAR'] ?? 0.00),
                'personal_address' => $address,
                'mobile_no' => $mobileNo,
                'employee_code' => $empCode,
                'beneficiary_code' => $beneCode,
                'spouse_name' => trim($row['SPOUSE_NAME'] ?? ''),
                'spouse_relation' => trim($row['RELATION'] ?? 'Spouse'),
                'lta_to_whom' => trim($row['LTA_TO_WHOM'] ?? ''),
                'date_of_lta' => $row['DATE_OF_LTA'] ? Carbon::parse($row['DATE_OF_LTA']) : null,
                'current_status' => $statusEnum,
                'assigned_user_id' => $creatorId,
                'created_by' => $creatorId,
                'created_at' => $createDate,
                'updated_at' => now(),
            ]);

            // Import Calculation Run & Breakdowns if available in gpffp.GPF_AMOUNT_INFO
            $this->importCalculationRun($conn, $inwardCase, $regdNo, $userMap, $defaultUserId);

            // Import Workflow Audit Log from gpffp.GPF_CASES_LOG
            $this->importCaseLogs($conn, $inwardCase, $regdNo, $userMap, $defaultUserId);

            // Import Outward Dispatch from gpffp.GPF_OUTWARD
            $this->importOutward($conn, $inwardCase, $regdNo, $userMap, $defaultUserId);

            $count++;
        }
        oci_free_statement($stmt);

        return $count;
    }

    /**
     * Import Calculation Run & Monthly Breakdowns
     */
    protected function importCalculationRun(mixed $conn, InwardCase $case, string $regdNo, array $userMap, int $defaultUserId): void
    {
        $amtStmt = oci_parse($conn, "SELECT * FROM gpffp.GPF_AMOUNT_INFO WHERE REGD_NO = :regd");
        oci_bind_by_name($amtStmt, ':regd', $regdNo);
        if (!@oci_execute($amtStmt)) return;

        $amt = oci_fetch_assoc($amtStmt);
        oci_free_statement($amtStmt);

        if (!$amt) return;

        $computedBy = $userMap[strtolower(trim($amt['CALCULATION_DONE_BY'] ?? ''))] ?? $defaultUserId;
        $checkedBy = $userMap[strtolower(trim($amt['CHECKED_BY'] ?? ''))] ?? null;
        $approvedBy = $userMap[strtolower(trim($amt['APPROVED_BY'] ?? ''))] ?? null;

        $cutoffDate = $amt['INTEREST_ALLOWED_UPTO'] ? Carbon::parse($amt['INTEREST_ALLOWED_UPTO']) : null;

        $run = CalculationRun::create([
            'inward_case_id' => $case->id,
            'opening_fin_year' => (string) ($amt['OPENING_FIN_YEAR'] ?? $amt['CLOSING_FIN_YEAR'] ?? '2023-2024'),
            'opening_balance_amount' => (float) ($amt['OPENING_BAL_AMOUNT'] ?? $amt['CLOSING_BAL_AMOUNT'] ?? 0),
            'total_subscriptions' => (float) ($amt['ACTUAL_DEPOSIT'] ?? 0),
            'total_refunds' => 0.00,
            'total_withdrawals' => (float) ($amt['WITHDRAWAL'] ?? 0),
            'excess_deposits' => (float) ($amt['EXCESS_DEPOSIT'] ?? 0),
            'actual_interest_computed' => (float) ($amt['ACTUAL_INTEREST'] ?? 0),
            'delayed_interest_computed' => (float) ($amt['DELAYED_INTEREST'] ?? 0),
            'total_interest_computed' => (float) ($amt['ACTUAL_INTEREST'] ?? 0) + (float) ($amt['DELAYED_INTEREST'] ?? 0),
            'dlis_admissible' => (strtoupper(trim($amt['DLIS_ADMISSIBLE'] ?? 'N')) === 'Y'),
            'dlis_amount' => (float) ($amt['DLIS_AMOUNT'] ?? 0),
            'final_closing_balance' => (float) ($amt['FINAL_PAYMENT_AMOUNT'] ?? 0),
            'cutoff_date' => $cutoffDate,
            'interest_allowed_upto' => $cutoffDate,
            'computed_by' => $computedBy,
            'checked_by' => $checkedBy,
            'approved_by' => $approvedBy,
            'is_locked' => ($case->current_status->value >= CaseWorkflowStatus::APPROVED->value),
            'created_at' => $amt['CREATE_DATE'] ? Carbon::parse($amt['CREATE_DATE']) : now(),
        ]);

        // Import monthly breakdowns from gpffp.GPF_ACCOUNT_CALCULATION
        $calStmt = oci_parse($conn, "
            SELECT * FROM gpffp.GPF_ACCOUNT_CALCULATION 
            WHERE REGD_NO = :regd 
            ORDER BY PAY_SLIP_DATE ASC, INTEREST_DATE ASC
        ");
        oci_bind_by_name($calStmt, ':regd', $regdNo);

        if (@oci_execute($calStmt)) {
            while ($cRow = oci_fetch_assoc($calStmt)) {
                $paySlipDate = $cRow['PAY_SLIP_DATE'] ? Carbon::parse($cRow['PAY_SLIP_DATE']) : now();
                $intDate = $cRow['INTEREST_DATE'] ? Carbon::parse($cRow['INTEREST_DATE']) : $paySlipDate;

                CalculationMonthlyBreakdown::create([
                    'calculation_run_id' => $run->id,
                    'financial_year' => (string) ($cRow['FIN_YEAR_CODE'] ?? '2023-2024'),
                    'calendar_month' => $paySlipDate->format('Y-m'),
                    'pay_slip_date' => $paySlipDate->toDateString(),
                    'interest_date' => $intDate->toDateString(),
                    'accounting_month' => (int) ($cRow['ACCOUNTING_MONTH'] ?? 1),
                    'opening_balance' => (float) ($cRow['OPENING_BALANCE'] ?? 0),
                    'deposit' => (float) ($cRow['DEPOSIT'] ?? $cRow['SUBSCRIPTION_AMT'] ?? 0),
                    'withdrawal' => (float) ($cRow['WITHDRAWAL'] ?? $cRow['WITHDRAWAL_AMT'] ?? 0),
                    'rate_of_interest' => (float) ($cRow['RATE_OF_INTEREST'] ?? 7.1000),
                    'interest_on_deposit' => (strtoupper(trim($cRow['INTEREST_ON_DEPOSIT'] ?? 'Y')) === 'Y'),
                    'progressive_balance' => (float) ($cRow['PROGRESSIVE'] ?? 0),
                    'actual_interest' => (float) ($cRow['ACTUAL_INTEREST'] ?? 0),
                    'delay_interest' => (float) ($cRow['DELAY_INTEREST'] ?? 0),
                    'is_cut_month' => (strtoupper(trim($cRow['CUT_MONTH'] ?? 'N')) === 'Y'),
                    'is_adjustment' => (strtoupper(trim($cRow['ADJUSTMENT'] ?? 'N')) === 'Y'),
                ]);
            }
            oci_free_statement($calStmt);
        }
    }

    /**
     * Import Case Audit Log from gpffp.GPF_CASES_LOG
     */
    protected function importCaseLogs(mixed $conn, InwardCase $case, string $regdNo, array $userMap, int $defaultUserId): void
    {
        $stmt = oci_parse($conn, "SELECT * FROM gpffp.GPF_CASES_LOG WHERE REGD_NO = :regd ORDER BY SL_NO ASC");
        oci_bind_by_name($stmt, ':regd', $regdNo);

        if (@oci_execute($stmt)) {
            while ($log = oci_fetch_assoc($stmt)) {
                $statusVal = (int) ($log['CASE_STATUS'] ?? 1);
                $statusEnum = CaseWorkflowStatus::tryFrom($statusVal) ?? CaseWorkflowStatus::DRAFT;
                $actionBy = $userMap[strtolower(trim($log['ACTION_BY'] ?? ''))] ?? $defaultUserId;

                WorkflowHistory::create([
                    'inward_case_id' => $case->id,
                    'performed_by' => $actionBy,
                    'from_status' => null,
                    'to_status' => $statusEnum,
                    'action_type' => 'STATE_TRANSITION',
                    'remarks' => "Transitioned to status: {$statusEnum->label()} in legacy system.",
                    'created_at' => $log['ACTION_DATE'] ? Carbon::parse($log['ACTION_DATE']) : now(),
                ]);
            }
            oci_free_statement($stmt);
        }
    }

    /**
     * Import Outward dispatch records from gpffp.GPF_OUTWARD
     */
    protected function importOutward(mixed $conn, InwardCase $case, string $regdNo, array $userMap, int $defaultUserId): void
    {
        $stmt = oci_parse($conn, "SELECT * FROM gpffp.GPF_OUTWARD WHERE REGD_NO = :regd ORDER BY SL_NO ASC");
        oci_bind_by_name($stmt, ':regd', $regdNo);

        if (@oci_execute($stmt)) {
            while ($out = oci_fetch_assoc($stmt)) {
                $performedBy = $userMap[strtolower(trim($out['CREATE_USER'] ?? ''))] ?? $defaultUserId;
                WorkflowHistory::create([
                    'inward_case_id' => $case->id,
                    'performed_by' => $performedBy,
                    'from_status' => CaseWorkflowStatus::AUTHORIZED,
                    'to_status' => CaseWorkflowStatus::DISPATCHED,
                    'action_type' => 'OUTWARD_DISPATCH',
                    'remarks' => "Dispatched via {$out['SENT_BY']} to {$out['COPY_TO']} (Subject: {$out['SUBJECT']}). Letter No: {$out['LETTER_NO']}",
                    'created_at' => $out['CREATE_DATE'] ? Carbon::parse($out['CREATE_DATE']) : now(),
                ]);
            }
            oci_free_statement($stmt);
        }
    }

    /**
     * Import Real LTA cases from gpffp.LTA_APPLICATION (85 records)
     */
    protected function importLtaCases(mixed $conn, array $userMap, array &$usedRegNos): int
    {
        $count = 0;
        $defaultUserId = $userMap['admin'] ?? User::first()?->id;

        $stmt = oci_parse($conn, "SELECT * FROM gpffp.LTA_APPLICATION ORDER BY REGD_NO ASC");
        if (!@oci_execute($stmt)) return 0;

        while ($lta = oci_fetch_assoc($stmt)) {
            $regdNo = trim($lta['REGD_NO']);
            if (!$regdNo) continue;

            $seriesCode = trim($lta['SERIES_ID']);
            $accountNo = trim($lta['ACCOUNT_NO']);
            $subscriberName = trim($lta['ACC_HOLDER_NAME']) ?: "Subscriber {$accountNo}";
            $designation = trim($lta['DESIGNATION']) ?: 'Government Employee';
            $address = trim($lta['ADDRESS']) ?: 'Agartala, Tripura';
            $ltaWhom = trim($lta['LTA_WHOM'] ?? '');
            $dor = $lta['DOR'] ? Carbon::parse($lta['DOR']) : null;
            $dodr = $lta['DODR'] ? Carbon::parse($lta['DODR']) : $dor;
            $approvalDate = $lta['APPROVAL_DATE'] ? Carbon::parse($lta['APPROVAL_DATE']) : null;
            $createDate = $lta['CREATE_DATE'] ? Carbon::parse($lta['CREATE_DATE']) : now();

            $status = (strtoupper(trim($lta['STATUS'] ?? '')) === 'A') 
                ? CaseWorkflowStatus::LTA_APPROVED 
                : CaseWorkflowStatus::LTA_REGISTERED;

            $seriesObj = $this->oracleBridge->getSeriesList()->firstWhere('id', $seriesCode);
            $seriesName = $seriesObj['code'] ?? "Series {$seriesCode}";

            $creatorId = $userMap[strtolower(trim($lta['CREATE_USER'] ?? ''))] ?? $defaultUserId;

            // Disambiguate registration number for LTA
            $candidate = $regdNo;
            $idx = 1;
            while (isset($usedRegNos[$candidate]) || InwardCase::withTrashed()->where('registration_no', $candidate)->exists()) {
                $candidate = "{$regdNo}-LTA" . ($idx > 1 ? "-{$idx}" : "");
                $idx++;
            }
            $finalRegdNo = $candidate;
            $usedRegNos[$finalRegdNo] = true;

            $inwardCase = InwardCase::create([
                'registration_no' => $finalRegdNo,
                'diary_number' => "LTA/{$finalRegdNo}",
                'diary_date' => $createDate,
                'series_code' => $seriesCode,
                'series_name' => $seriesName,
                'account_no' => $accountNo,
                'subscriber_name_cache' => $subscriberName,
                'name_title' => 'Shri',
                'designation_title' => 'Mr',
                'designation' => $designation,
                'case_type' => CaseType::LTA_SPECIAL,
                'pension_type_id' => '1',
                'section' => 'LTA Fund Section',
                'ddo_code' => '6016',
                'treasury_code' => trim($lta['TREASURY'] ?? 'TPA06'),
                'treasury_name' => trim($lta['TREASURY'] ?? ''),
                'sub_treasury_name' => trim($lta['PAYABLE_TO'] ?? ''),
                'event_date' => $dor ?: $createDate,
                'personal_address' => $address,
                'lta_to_whom' => $ltaWhom,
                'date_of_lta' => $dodr,
                'current_status' => $status,
                'assigned_user_id' => $creatorId,
                'created_by' => $creatorId,
                'created_at' => $createDate,
                'updated_at' => now(),
            ]);

            // Add Nominee for LTA_WHOM if present
            if ($ltaWhom) {
                CaseNominee::create([
                    'inward_case_id' => $inwardCase->id,
                    'nominee_name' => $ltaWhom,
                    'relationship' => 'Spouse / Legal Heir',
                    'share_percentage' => 100.00,
                    'allocated_amount' => 0.00,
                    'is_minor' => false,
                    'address' => $address,
                ]);
            }

            // Create workflow history
            WorkflowHistory::create([
                'inward_case_id' => $inwardCase->id,
                'performed_by' => $creatorId,
                'from_status' => null,
                'to_status' => $status,
                'action_type' => 'LTA_IMPORT',
                'remarks' => "LTA Case registered in legacy system. Approved by: " . trim($lta['APPROVED_BY'] ?? 'Admin'),
                'created_at' => $approvalDate ?: $createDate,
            ]);

            $count++;
        }
        oci_free_statement($stmt);

        return $count;
    }
}
