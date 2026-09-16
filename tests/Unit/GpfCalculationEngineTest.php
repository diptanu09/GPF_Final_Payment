<?php

namespace Tests\Unit;

use App\Enums\CaseType;
use App\Enums\CaseWorkflowStatus;
use App\Models\CaseNominee;
use App\Models\InwardCase;
use App\Models\User;
use App\Services\Calculation\CutoffRuleResolver;
use App\Services\Calculation\GpfCalculationEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GpfCalculationEngineTest extends TestCase
{
    use RefreshDatabase;

    protected GpfCalculationEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->engine = new GpfCalculationEngine(new CutoffRuleResolver());
    }

    public function test_calculation_engine_computes_correct_statutory_interest_and_balance(): void
    {
        $user = User::first();

        $case = InwardCase::create([
            'registration_no' => '20230112345',
            'series_code' => '01',
            'account_no' => '12345',
            'subscriber_name_cache' => 'Test Subscriber',
            'name_title' => 'Shri',
            'designation_title' => 'Mr',
            'designation' => 'Teacher',
            'case_type' => CaseType::NORMAL_SUPERANNUATION,
            'pension_type_id' => '1',
            'ddo_code' => '1001',
            'treasury_code' => '01',
            'event_date' => '2024-03-31',
            'personal_address' => 'Agartala',
            'current_status' => CaseWorkflowStatus::DRAFT,
            'created_by' => $user->id,
        ]);

        $ledgerEntries = [
            'opening_balance' => 100000.00,
            'opening_fin_year' => '2023-2024',
            'monthly_entries' => [
                [
                    'financial_year' => '2023-2024',
                    'calendar_month' => '2023-04',
                    'pay_slip_date' => '2023-04-01',
                    'accounting_month' => 1,
                    'deposit' => 10000.00,
                    'withdrawal' => 0.00,
                    'rate_of_interest' => 7.1000,
                    'interest_on_deposit' => true,
                ],
                [
                    'financial_year' => '2023-2024',
                    'calendar_month' => '2023-05',
                    'pay_slip_date' => '2023-05-01',
                    'accounting_month' => 2,
                    'deposit' => 10000.00,
                    'withdrawal' => 0.00,
                    'rate_of_interest' => 7.1000,
                    'interest_on_deposit' => true,
                ],
            ],
        ];

        $run = $this->engine->calculate($case, $ledgerEntries, $user->id);

        $this->assertNotNull($run);
        $this->assertEquals(100000.00, (float) $run->opening_balance_amount);
        $this->assertEquals(20000.00, (float) $run->total_subscriptions);
        $this->assertGreaterThan(0, (float) $run->total_interest_computed);
        $this->assertGreaterThan(120000.00, (float) $run->final_closing_balance);
    }

    public function test_nominee_share_partitioning_reconciles_odd_paisa_remainder(): void
    {
        $user = User::first();

        $case = InwardCase::create([
            'registration_no' => '20230299999',
            'series_code' => '02',
            'account_no' => '99999',
            'subscriber_name_cache' => 'Deceased Employee',
            'name_title' => 'Late',
            'designation_title' => 'Mr',
            'designation' => 'Inspector',
            'case_type' => CaseType::DEATH_IN_SERVICE,
            'pension_type_id' => '2',
            'ddo_code' => '1001',
            'treasury_code' => '01',
            'event_date' => '2023-08-15',
            'personal_address' => 'Agartala',
            'current_status' => CaseWorkflowStatus::DRAFT,
            'created_by' => $user->id,
        ]);

        $n1 = CaseNominee::create([
            'inward_case_id' => $case->id,
            'nominee_name' => 'Wife (Primary)',
            'relationship' => 'Spouse',
            'share_percentage' => 33.33,
        ]);

        $n2 = CaseNominee::create([
            'inward_case_id' => $case->id,
            'nominee_name' => 'Son 1',
            'relationship' => 'Son',
            'share_percentage' => 33.33,
        ]);

        $n3 = CaseNominee::create([
            'inward_case_id' => $case->id,
            'nominee_name' => 'Son 2',
            'relationship' => 'Son',
            'share_percentage' => 33.34,
        ]);

        $totalPayable = 100000.00;
        $this->engine->partitionNomineeShares($case, $totalPayable);

        $allocatedSum = CaseNominee::where('inward_case_id', $case->id)->sum('allocated_amount');
        $this->assertEquals($totalPayable, (float) $allocatedSum);
    }

    public function test_dlis_admissible_for_family_pension_case(): void
    {
        $user = User::first();

        $case = InwardCase::create([
            'registration_no' => '20230288888',
            'series_code' => '02',
            'account_no' => '88888',
            'subscriber_name_cache' => 'Late Sukhendu Bhowmik',
            'name_title' => 'Late',
            'designation_title' => 'Mr',
            'designation' => 'Inspector',
            'case_type' => CaseType::FAMILY_PENSION,
            'pension_type_id' => '2',
            'pension_type_name' => 'Family Pension (FAM)',
            'ddo_code' => '1001',
            'treasury_code' => '01',
            'event_date' => '2023-08-15',
            'personal_address' => 'Agartala',
            'current_status' => CaseWorkflowStatus::DRAFT,
            'created_by' => $user->id,
        ]);

        $ledgerEntries = [
            'opening_balance' => 80000.00,
            'opening_fin_year' => '2023-2024',
            'monthly_entries' => [
                [
                    'financial_year' => '2023-2024',
                    'calendar_month' => '2023-04',
                    'pay_slip_date' => '2023-04-01',
                    'accounting_month' => 1,
                    'deposit' => 5000.00,
                    'withdrawal' => 0.00,
                    'rate_of_interest' => 7.1000,
                    'interest_on_deposit' => true,
                ],
            ],
        ];

        $run = $this->engine->calculate($case, $ledgerEntries, $user->id);

        $this->assertNotNull($run);
        $this->assertTrue((bool) $run->dlis_admissible);
        $this->assertEquals((float) config('gpf.dlis.max_amount', 60000.00), (float) $run->dlis_amount);
        $this->assertGreaterThan(85000.00, (float) $run->final_closing_balance);
    }

    public function test_multi_year_progressive_compounding_and_beneficiary_code(): void
    {
        $user = User::first();

        $case = InwardCase::create([
            'registration_no' => '20230377777',
            'series_code' => '03',
            'account_no' => '77777',
            'subscriber_name_cache' => 'Multi-Year Employee',
            'name_title' => 'Shri',
            'designation_title' => 'Mr',
            'designation' => 'Officer',
            'case_type' => CaseType::NORMAL_SUPERANNUATION,
            'pension_type_id' => '1',
            'ddo_code' => '1001',
            'treasury_code' => '01',
            'event_date' => '2025-03-31',
            'personal_address' => 'Agartala',
            'current_status' => CaseWorkflowStatus::DRAFT,
            'created_by' => $user->id,
        ]);

        $n1 = CaseNominee::create([
            'inward_case_id' => $case->id,
            'nominee_name' => 'Wife Legal Heir',
            'beneficiary_code' => 'BEN-77001',
            'relationship' => 'Spouse',
            'share_percentage' => 100.00,
        ]);

        $this->assertEquals('BEN-77001', $n1->fresh()->beneficiary_code);

        // 2 financial years ledger
        $ledgerEntries = [
            'opening_balance' => 100000.00,
            'opening_fin_year' => '2023-2024',
            'monthly_entries' => [
                // FY 2023-2024 (April - March)
                [
                    'financial_year' => '2023-2024',
                    'calendar_month' => '2023-04',
                    'pay_slip_date' => '2023-04-01',
                    'accounting_month' => 1,
                    'deposit' => 10000.00,
                    'withdrawal' => 0.00,
                    'rate_of_interest' => 7.1000,
                    'interest_on_deposit' => true,
                ],
                [
                    'financial_year' => '2023-2024',
                    'calendar_month' => '2023-05',
                    'pay_slip_date' => '2023-05-01',
                    'accounting_month' => 2,
                    'deposit' => 10000.00,
                    'withdrawal' => 0.00,
                    'rate_of_interest' => 7.1000,
                    'interest_on_deposit' => true,
                ],
                // FY 2024-2025 (April)
                [
                    'financial_year' => '2024-2025',
                    'calendar_month' => '2024-04',
                    'pay_slip_date' => '2024-04-01',
                    'accounting_month' => 1,
                    'deposit' => 10000.00,
                    'withdrawal' => 0.00,
                    'rate_of_interest' => 7.1000,
                    'interest_on_deposit' => true,
                ],
            ],
        ];

        $run = $this->engine->calculate($case, $ledgerEntries, $user->id);

        $breakdowns = $run->monthlyBreakdowns;
        $this->assertCount(3, $breakdowns);

        // Month 1: Progressive = 100,000 + 10,000 = 110,000
        $this->assertEquals(110000.00, (float) $breakdowns[0]->progressive_balance);

        // Month 2: Progressive = 110,000 + 10,000 = 120,000
        $this->assertEquals(120000.00, (float) $breakdowns[1]->progressive_balance);

        // FY 2024-2025 Month 1 (April): Opening balance should capitalize prior FY's deposits and accrued interest
        $this->assertGreaterThan(120000.00, (float) $breakdowns[2]->opening_balance);
        $this->assertEquals((float) $run->final_closing_balance, (float) $n1->fresh()->allocated_amount);
    }

    public function test_delayed_interest_computation_after_cut_month(): void
    {
        $user = User::first();

        $case = InwardCase::create([
            'registration_no' => '20240455555',
            'series_code' => '04',
            'account_no' => '55555',
            'subscriber_name_cache' => 'Delayed Settlement Subscriber',
            'name_title' => 'Shri',
            'designation_title' => 'Mr',
            'designation' => 'Staff',
            'case_type' => CaseType::NORMAL_SUPERANNUATION,
            'pension_type_id' => '1',
            'ddo_code' => '1001',
            'treasury_code' => '01',
            'event_date' => '2024-03-15',
            'personal_address' => 'Agartala',
            'current_status' => CaseWorkflowStatus::DRAFT,
            'created_by' => $user->id,
        ]);

        $ledgerEntries = [
            'opening_balance' => 200000.00,
            'opening_fin_year' => '2023-2024',
            'monthly_entries' => [
                // Month 1: 2024-02 (Normal Active Month)
                [
                    'financial_year' => '2023-2024',
                    'calendar_month' => '2024-02',
                    'pay_slip_date' => '2024-02-01',
                    'accounting_month' => 11,
                    'deposit' => 10000.00,
                    'withdrawal' => 0.00,
                    'rate_of_interest' => 7.1000,
                    'interest_on_deposit' => true,
                    'is_cut_month' => false,
                ],
                // Month 2: 2024-03 (Cut Month - Interest Suppressed)
                [
                    'financial_year' => '2023-2024',
                    'calendar_month' => '2024-03',
                    'pay_slip_date' => '2024-03-01',
                    'accounting_month' => 12,
                    'deposit' => 0.00,
                    'withdrawal' => 0.00,
                    'rate_of_interest' => 7.1000,
                    'interest_on_deposit' => true,
                    'is_cut_month' => true,
                ],
                // Month 3: 2024-04 (Delayed Period Month 1)
                [
                    'financial_year' => '2024-2025',
                    'calendar_month' => '2024-04',
                    'pay_slip_date' => '2024-04-01',
                    'accounting_month' => 1,
                    'deposit' => 0.00,
                    'withdrawal' => 0.00,
                    'rate_of_interest' => 7.1000,
                    'interest_on_deposit' => false,
                    'is_cut_month' => false,
                ],
                // Month 4: 2024-05 (Delayed Period Month 2)
                [
                    'financial_year' => '2024-2025',
                    'calendar_month' => '2024-05',
                    'pay_slip_date' => '2024-05-01',
                    'accounting_month' => 2,
                    'deposit' => 0.00,
                    'withdrawal' => 0.00,
                    'rate_of_interest' => 7.1000,
                    'interest_on_deposit' => false,
                    'is_cut_month' => false,
                ],
            ],
        ];

        $run = $this->engine->calculate($case, $ledgerEntries, $user->id);

        $breakdowns = $run->monthlyBreakdowns;
        $this->assertCount(4, $breakdowns);

        // Month 1 (Normal): actual_interest > 0, delay_interest = 0
        $this->assertGreaterThan(0, (float) $breakdowns[0]->actual_interest);
        $this->assertEquals(0.00, (float) $breakdowns[0]->delay_interest);

        // Month 2 (Cut Month): actual_interest = 0, delay_interest = 0
        $this->assertEquals(0.00, (float) $breakdowns[1]->actual_interest);
        $this->assertEquals(0.00, (float) $breakdowns[1]->delay_interest);
        $this->assertTrue((bool) $breakdowns[1]->is_cut_month);

        // Month 3 (Delay Month 1): actual_interest = 0, delay_interest > 0, opening_balance = delayOpeningBal
        $this->assertEquals(0.00, (float) $breakdowns[2]->actual_interest);
        $this->assertGreaterThan(0, (float) $breakdowns[2]->delay_interest);
        $this->assertGreaterThan(200000.00, (float) $breakdowns[2]->opening_balance);

        // Month 4 (Delay Month 2): actual_interest = 0, delay_interest > 0, opening_balance = 0.00 (legacy rule)
        $this->assertEquals(0.00, (float) $breakdowns[3]->actual_interest);
        $this->assertGreaterThan(0, (float) $breakdowns[3]->delay_interest);
        $this->assertEquals(0.00, (float) $breakdowns[3]->opening_balance);

        // Run Summary Verification
        $this->assertGreaterThan(0, (float) $run->actual_interest_computed);
        $this->assertGreaterThan(0, (float) $run->delayed_interest_computed);
        $this->assertEquals(
            (float) ($run->actual_interest_computed + $run->delayed_interest_computed),
            (float) $run->total_interest_computed
        );
        $this->assertGreaterThan(210000.00, (float) $run->final_closing_balance);
    }

    public function test_cut_month_transactions_incorporated_in_delay_opening_balance_and_legacy_rules(): void
    {
        $user = User::first();

        $case = InwardCase::create([
            'registration_no' => '20240988776',
            'series_code' => '05',
            'account_no' => '88776',
            'subscriber_name_cache' => 'Legacy Parity Test Subscriber',
            'name_title' => 'Shri',
            'designation_title' => 'Mr',
            'designation' => 'Headmaster',
            'case_type' => CaseType::NORMAL_SUPERANNUATION,
            'pension_type_id' => '1',
            'ddo_code' => '1001',
            'treasury_code' => '01',
            'event_date' => '2024-09-15',
            'personal_address' => 'Agartala',
            'current_status' => CaseWorkflowStatus::DRAFT,
            'created_by' => $user->id,
        ]);

        $ledgerEntries = [
            'opening_balance' => 500000.00,
            'opening_fin_year' => '2024-2025',
            'monthly_entries' => [
                // Month 1: 2024-04 (Accounting Month 1)
                [
                    'financial_year' => '2024-2025',
                    'calendar_month' => '2024-04',
                    'pay_slip_date' => '2024-04-01',
                    'accounting_month' => 1,
                    'deposit' => 10000.00,
                    'withdrawal' => 0.00,
                    'rate_of_interest' => 7.1000,
                    'interest_on_deposit' => true,
                    'is_cut_month' => false,
                ],
                // Month 2: 2024-05 (Accounting Month 2)
                [
                    'financial_year' => '2024-2025',
                    'calendar_month' => '2024-05',
                    'pay_slip_date' => '2024-05-01',
                    'accounting_month' => 2,
                    'deposit' => 10000.00,
                    'withdrawal' => 0.00,
                    'rate_of_interest' => 7.1000,
                    'interest_on_deposit' => true,
                    'is_cut_month' => false,
                ],
                // Month 6: 2024-09 (Cut Month - with subscription and withdrawal)
                [
                    'financial_year' => '2024-2025',
                    'calendar_month' => '2024-09',
                    'pay_slip_date' => '2024-09-01',
                    'accounting_month' => 6,
                    'deposit' => 5000.00,
                    'withdrawal' => 2000.00,
                    'rate_of_interest' => 7.1000,
                    'interest_on_deposit' => true,
                    'is_cut_month' => true,
                ],
                // Month 7: 2024-10 (Delay Month 1)
                [
                    'financial_year' => '2024-2025',
                    'calendar_month' => '2024-10',
                    'pay_slip_date' => '2024-10-01',
                    'accounting_month' => 7,
                    'deposit' => 0.00,
                    'withdrawal' => 0.00,
                    'rate_of_interest' => 7.1000,
                    'interest_on_deposit' => false,
                    'is_cut_month' => false,
                ],
                // Month 8: 2024-11 (Delay Month 2)
                [
                    'financial_year' => '2024-2025',
                    'calendar_month' => '2024-11',
                    'pay_slip_date' => '2024-11-01',
                    'accounting_month' => 8,
                    'deposit' => 0.00,
                    'withdrawal' => 0.00,
                    'rate_of_interest' => 7.1000,
                    'interest_on_deposit' => false,
                    'is_cut_month' => false,
                ],
            ],
        ];

        $run = $this->engine->calculate($case, $ledgerEntries, $user->id);
        $breakdowns = $run->monthlyBreakdowns;

        // Verify row-level opening_balance:
        // Month 1 (accounting_month = 1) has opening_balance
        $this->assertEquals(500000.00, (float) $breakdowns[0]->opening_balance);

        // Month 2 (accounting_month = 2) must have opening_balance = 0.00
        $this->assertEquals(0.00, (float) $breakdowns[1]->opening_balance);

        // Cut Month (Month 3 in this list): interest is 0
        $this->assertEquals(0.00, (float) $breakdowns[2]->actual_interest);
        $this->assertEquals(0.00, (float) $breakdowns[2]->progressive_balance);
        $this->assertEquals(0.00, (float) $breakdowns[2]->opening_balance);

        // Delay Month 1 (Month 4 in list): opening_balance must include Cut Month's net deposit (5000 - 2000 = 3000)
        // Delay Opening Bal = 500,000 + 10,000 + 10,000 + 5,000 - 2,000 + round(actualInterest)
        $delayOpening = (float) $breakdowns[3]->opening_balance;
        $this->assertGreaterThan(523000.00, $delayOpening);

        // Delay Month 2 (Month 5 in list): opening_balance must be 0.00
        $this->assertEquals(0.00, (float) $breakdowns[4]->opening_balance);

        // Progressive in Delay Month 2 equals progressive in Delay Month 1 (since 0 deposits/withdrawals)
        $this->assertEquals(
            (float) $breakdowns[3]->progressive_balance,
            (float) $breakdowns[4]->progressive_balance
        );

        // Monthly delay interest in Month 2 equals Month 1
        $this->assertEquals(
            (float) $breakdowns[3]->delay_interest,
            (float) $breakdowns[4]->delay_interest
        );
    }

    public function test_full_legacy_calculation_pipeline_parity_with_excess_deposits_and_delayed_interest(): void
    {
        $user = User::first();

        $case = InwardCase::create([
            'registration_no' => '20240977112',
            'series_code' => '07',
            'account_no' => '77112',
            'subscriber_name_cache' => 'Strict Legacy Audit Subscriber',
            'name_title' => 'Shri',
            'designation_title' => 'Mr',
            'designation' => 'Supervisor',
            'case_type' => CaseType::NORMAL_SUPERANNUATION,
            'pension_type_id' => '1',
            'ddo_code' => '1001',
            'treasury_code' => '01',
            'event_date' => '2024-06-15',
            'personal_address' => 'Agartala',
            'current_status' => CaseWorkflowStatus::DRAFT,
            'created_by' => $user->id,
        ]);

        $ledgerEntries = [
            'opening_balance' => 100000.00,
            'opening_fin_year' => '2024-2025',
            'monthly_entries' => [
                // Month 1: 2024-04 (Normal deposit)
                [
                    'financial_year' => '2024-2025',
                    'calendar_month' => '2024-04',
                    'pay_slip_date' => '2024-04-01',
                    'accounting_month' => 1,
                    'deposit' => 10000.00,
                    'withdrawal' => 0.00,
                    'rate_of_interest' => 7.1000,
                    'interest_on_deposit' => true,
                    'is_cut_month' => false,
                ],
                // Month 2: 2024-05 (Excess deposit where interest_on_deposit = false)
                [
                    'financial_year' => '2024-2025',
                    'calendar_month' => '2024-05',
                    'pay_slip_date' => '2024-05-01',
                    'accounting_month' => 2,
                    'deposit' => 10000.00,
                    'withdrawal' => 0.00,
                    'rate_of_interest' => 7.1000,
                    'interest_on_deposit' => false,
                    'is_cut_month' => false,
                ],
                // Month 3: 2024-06 (Cut Month with deposit & withdrawal)
                [
                    'financial_year' => '2024-2025',
                    'calendar_month' => '2024-06',
                    'pay_slip_date' => '2024-06-01',
                    'accounting_month' => 3,
                    'deposit' => 5000.00,
                    'withdrawal' => 2000.00,
                    'rate_of_interest' => 7.1000,
                    'interest_on_deposit' => true,
                    'is_cut_month' => true,
                ],
                // Month 4: 2024-07 (Delay Month 1)
                [
                    'financial_year' => '2024-2025',
                    'calendar_month' => '2024-07',
                    'pay_slip_date' => '2024-07-01',
                    'accounting_month' => 4,
                    'deposit' => 0.00,
                    'withdrawal' => 0.00,
                    'rate_of_interest' => 7.1000,
                    'interest_on_deposit' => false,
                    'is_cut_month' => false,
                ],
                // Month 5: 2024-08 (Delay Month 2)
                [
                    'financial_year' => '2024-2025',
                    'calendar_month' => '2024-08',
                    'pay_slip_date' => '2024-08-01',
                    'accounting_month' => 5,
                    'deposit' => 0.00,
                    'withdrawal' => 0.00,
                    'rate_of_interest' => 7.1000,
                    'interest_on_deposit' => false,
                    'is_cut_month' => false,
                ],
            ],
        ];

        $run = $this->engine->calculate($case, $ledgerEntries, $user->id);
        $breakdowns = $run->monthlyBreakdowns;

        // Month 1 progressive: 100,000 + 10,000 = 110,000. Interest: 650.83
        $this->assertEquals(110000.00, (float) $breakdowns[0]->progressive_balance);
        $this->assertEquals(650.83, (float) $breakdowns[0]->actual_interest);

        // Month 2 progressive: 110,000 (deposit excluded from progressive since interest_on_deposit = false). Interest: 650.83
        $this->assertEquals(110000.00, (float) $breakdowns[1]->progressive_balance);
        $this->assertEquals(650.83, (float) $breakdowns[1]->actual_interest);

        // Month 3 (Cut Month): progressive = 0, actual_interest = 0
        $this->assertEquals(0.00, (float) $breakdowns[2]->progressive_balance);
        $this->assertEquals(0.00, (float) $breakdowns[2]->actual_interest);

        // Delay Opening Balance in Month 4:
        // 100,000 + 15,000 - 2,000 + round(650.83 + 650.83 = 1301.66 => 1302) = 114,302.00
        $this->assertEquals(114302.00, (float) $breakdowns[3]->opening_balance);
        $this->assertEquals(114302.00, (float) $breakdowns[3]->progressive_balance);
        // Delay interest = round(114302 * 7.1 / 1200, 2) = 676.29
        $this->assertEquals(676.29, (float) $breakdowns[3]->delay_interest);

        // Month 5 Delay Month 2:
        $this->assertEquals(0.00, (float) $breakdowns[4]->opening_balance);
        $this->assertEquals(114302.00, (float) $breakdowns[4]->progressive_balance);
        $this->assertEquals(676.29, (float) $breakdowns[4]->delay_interest);

        // Summary Checks:
        $this->assertEquals(15000.00, (float) $run->total_subscriptions); // 10000 + 5000
        $this->assertEquals(10000.00, (float) $run->excess_deposits);     // 10000
        $this->assertEquals(2000.00, (float) $run->total_withdrawals);    // 2000
        $this->assertEquals(1301.66, (float) $run->actual_interest_computed);
        $this->assertEquals(1352.58, (float) $run->delayed_interest_computed); // 676.29 * 2
        $this->assertEquals(2654.24, (float) $run->total_interest_computed);

        // Final closing balance matching legacy calculate.php line 264:
        // delayOpeningBal (114,302) + excess (10,000) - delayWithdrawals (0) + delayInterest (1352.58) = 125,654.58 => 125655
        $this->assertEquals(125655.00, (float) $run->final_closing_balance);
    }

    public function test_superannuation_month_end_retirement_earns_interest_in_retirement_month(): void
    {
        $user = User::first();

        $case = InwardCase::create([
            'registration_no' => '20240399887',
            'series_code' => '01',
            'account_no' => '99887',
            'subscriber_name_cache' => 'Superannuation Month End Retiring Employee',
            'name_title' => 'Shri',
            'designation_title' => 'Mr',
            'designation' => 'Principal',
            'case_type' => CaseType::NORMAL_SUPERANNUATION,
            'pension_type_id' => '1',
            'ddo_code' => '1001',
            'treasury_code' => '01',
            'event_date' => '2024-03-31', // Last day of March 2024
            'personal_address' => 'Agartala',
            'current_status' => CaseWorkflowStatus::DRAFT,
            'created_by' => $user->id,
        ]);

        $ledgerEntries = [
            'opening_balance' => 200000.00,
            'opening_fin_year' => '2023-2024',
            'monthly_entries' => [
                // Month 1: 2024-02 (Normal Month)
                [
                    'financial_year' => '2023-2024',
                    'calendar_month' => '2024-02',
                    'pay_slip_date' => '2024-02-01',
                    'accounting_month' => 11,
                    'deposit' => 10000.00,
                    'withdrawal' => 0.00,
                    'rate_of_interest' => 7.1000,
                    'interest_on_deposit' => true,
                    'is_cut_month' => false,
                ],
                // Month 2: 2024-03 (Retirement Month / Cut Month on 31st March)
                [
                    'financial_year' => '2023-2024',
                    'calendar_month' => '2024-03',
                    'pay_slip_date' => '2024-03-01',
                    'accounting_month' => 12,
                    'deposit' => 0.00,
                    'withdrawal' => 0.00,
                    'rate_of_interest' => 7.1000,
                    'interest_on_deposit' => true,
                    'is_cut_month' => true,
                ],
                // Month 3: 2024-04 (Delay Month 1)
                [
                    'financial_year' => '2024-2025',
                    'calendar_month' => '2024-04',
                    'pay_slip_date' => '2024-04-01',
                    'accounting_month' => 1,
                    'deposit' => 0.00,
                    'withdrawal' => 0.00,
                    'rate_of_interest' => 7.1000,
                    'interest_on_deposit' => false,
                    'is_cut_month' => false,
                ],
            ],
        ];

        $run = $this->engine->calculate($case, $ledgerEntries, $user->id);
        $breakdowns = $run->monthlyBreakdowns;

        // Month 1 (2024-02): Progressive = 210,000, actual_interest = 210,000 * 7.1 / 1200 = 1242.50
        $this->assertEquals(210000.00, (float) $breakdowns[0]->progressive_balance);
        $this->assertEquals(1242.50, (float) $breakdowns[0]->actual_interest);

        // Month 2 (2024-03 - Retirement Month):
        // Under statutory rule, because subscriber retired on month end (2024-03-31) in superannuation,
        // employee EARNS interest for that month:
        // Progressive = 210,000, actual_interest = 1242.50
        $this->assertTrue((bool) $breakdowns[1]->is_cut_month);
        $this->assertEquals(210000.00, (float) $breakdowns[1]->progressive_balance);
        $this->assertEquals(1242.50, (float) $breakdowns[1]->actual_interest);
        $this->assertEquals(0.00, (float) $breakdowns[1]->delay_interest);

        // Month 3 (2024-04 - Delay Month 1):
        // Yearly interest capitalized for 2023-2024 = round(1242.50 + 1242.50) = 2485
        // Delay opening balance = 200,000 + 10,000 + 2,485 = 212,485.00
        $this->assertEquals(212485.00, (float) $breakdowns[2]->opening_balance);
        $this->assertEquals(212485.00, (float) $breakdowns[2]->progressive_balance);
        $this->assertEquals(1257.20, (float) $breakdowns[2]->delay_interest);
        $this->assertEquals(0.00, (float) $breakdowns[2]->actual_interest);
    }

    public function test_delay_interest_capped_at_six_months_without_justification(): void
    {
        $user = User::first();

        $case = InwardCase::create([
            'registration_no' => '20240988888',
            'series_code' => '01',
            'account_no' => '88888',
            'subscriber_name_cache' => 'Capped Delay Subscriber',
            'name_title' => 'Shri',
            'designation_title' => 'Mr',
            'designation' => 'Officer',
            'case_type' => CaseType::NORMAL_SUPERANNUATION,
            'pension_type_id' => '1',
            'ddo_code' => '1001',
            'treasury_code' => '01',
            'event_date' => '2024-03-31',
            'personal_address' => 'Agartala',
            'current_status' => CaseWorkflowStatus::DRAFT,
            'created_by' => $user->id,
        ]);

        $monthlyEntries = [
            // Cut Month (2024-03)
            [
                'financial_year' => '2023-2024',
                'calendar_month' => '2024-03',
                'pay_slip_date' => '2024-03-01',
                'accounting_month' => 12,
                'deposit' => 0.00,
                'withdrawal' => 0.00,
                'rate_of_interest' => 7.1000,
                'interest_on_deposit' => true,
                'is_cut_month' => true,
            ],
        ];

        // Add 8 delay months (2024-04 to 2024-11)
        for ($i = 1; $i <= 8; $i++) {
            $monthNum = 3 + $i;
            $calMonth = sprintf('2024-%02d', $monthNum);
            $monthlyEntries[] = [
                'financial_year' => '2024-2025',
                'calendar_month' => $calMonth,
                'pay_slip_date' => "{$calMonth}-01",
                'accounting_month' => $i,
                'deposit' => 0.00,
                'withdrawal' => 0.00,
                'rate_of_interest' => 7.1000,
                'interest_on_deposit' => false,
                'is_cut_month' => false,
            ];
        }

        $ledgerEntries = [
            'opening_balance' => 100000.00,
            'opening_fin_year' => '2023-2024',
            'monthly_entries' => $monthlyEntries,
            // NO delay_justification provided
        ];

        $run = $this->engine->calculate($case, $ledgerEntries, $user->id);
        $breakdowns = $run->monthlyBreakdowns;

        $this->assertEquals(8, $run->delay_months_count);
        $this->assertTrue((bool) $run->has_exceeded_delay_cap);
        $this->assertNull($run->delay_justification);

        // Delay months 1 to 6 (index 1 to 6 in breakdowns): delay_interest > 0
        for ($m = 1; $m <= 6; $m++) {
            $this->assertGreaterThan(
                0,
                (float) $breakdowns[$m]->delay_interest,
                "Delay month {$m} should earn delay interest within statutory 6-month limit."
            );
        }

        // Delay months 7 and 8 (index 7 and 8 in breakdowns): delay_interest MUST be 0.00 (capped)
        $this->assertEquals(
            0.00,
            (float) $breakdowns[7]->delay_interest,
            'Delay month 7 MUST have 0.00 interest without justification under Rule 11(4).'
        );
        $this->assertEquals(
            0.00,
            (float) $breakdowns[8]->delay_interest,
            'Delay month 8 MUST have 0.00 interest without justification under Rule 11(4).'
        );
    }

    public function test_delay_interest_allowed_for_months_seven_plus_with_justification(): void
    {
        $user = User::first();

        $case = InwardCase::create([
            'registration_no' => '20240977777',
            'series_code' => '01',
            'account_no' => '77777',
            'subscriber_name_cache' => 'Justified Delay Subscriber',
            'name_title' => 'Shri',
            'designation_title' => 'Mr',
            'designation' => 'Officer',
            'case_type' => CaseType::NORMAL_SUPERANNUATION,
            'pension_type_id' => '1',
            'ddo_code' => '1001',
            'treasury_code' => '01',
            'event_date' => '2024-03-31',
            'personal_address' => 'Agartala',
            'current_status' => CaseWorkflowStatus::DRAFT,
            'created_by' => $user->id,
        ]);

        $monthlyEntries = [
            // Cut Month (2024-03)
            [
                'financial_year' => '2023-2024',
                'calendar_month' => '2024-03',
                'pay_slip_date' => '2024-03-01',
                'accounting_month' => 12,
                'deposit' => 0.00,
                'withdrawal' => 0.00,
                'rate_of_interest' => 7.1000,
                'interest_on_deposit' => true,
                'is_cut_month' => true,
            ],
        ];

        // Add 8 delay months (2024-04 to 2024-11)
        for ($i = 1; $i <= 8; $i++) {
            $monthNum = 3 + $i;
            $calMonth = sprintf('2024-%02d', $monthNum);
            $monthlyEntries[] = [
                'financial_year' => '2024-2025',
                'calendar_month' => $calMonth,
                'pay_slip_date' => "{$calMonth}-01",
                'accounting_month' => $i,
                'deposit' => 0.00,
                'withdrawal' => 0.00,
                'rate_of_interest' => 7.1000,
                'interest_on_deposit' => false,
                'is_cut_month' => false,
            ];
        }

        $justificationText = 'Late submission of LPC and Service Book from DDO due to departmental audit. Delay not attributable to subscriber.';

        $ledgerEntries = [
            'opening_balance' => 100000.00,
            'opening_fin_year' => '2023-2024',
            'delay_justification' => $justificationText,
            'monthly_entries' => $monthlyEntries,
        ];

        $run = $this->engine->calculate($case, $ledgerEntries, $user->id);
        $breakdowns = $run->monthlyBreakdowns;

        $this->assertEquals(8, $run->delay_months_count);
        $this->assertTrue((bool) $run->has_exceeded_delay_cap);
        $this->assertEquals($justificationText, $run->delay_justification);

        // Case should also have delay justification persisted
        $case->refresh();
        $this->assertEquals($justificationText, $case->delay_justification);

        // All 8 delay months MUST earn delay interest when justification is present
        for ($m = 1; $m <= 8; $m++) {
            $this->assertGreaterThan(
                0,
                (float) $breakdowns[$m]->delay_interest,
                "Delay month {$m} should earn delay interest because official delay justification is recorded."
            );
        }

        // Compare total delay interest with vs without justification
        // 8 months with interest > 6 months with interest
        $totalDelayInterest = (float) $breakdowns->sum('delay_interest');
        $delayOpeningBalance = (float) $breakdowns[1]->opening_balance;
        $expectedMonthlyDelay = round(($delayOpeningBalance * 7.1) / 1200, 2);
        $this->assertEquals(round($expectedMonthlyDelay * 8, 2), round($totalDelayInterest, 2));
    }
}

