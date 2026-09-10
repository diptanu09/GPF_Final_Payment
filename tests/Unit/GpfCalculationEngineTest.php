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
        $this->assertEquals(10000.00, (float) $run->dlis_amount);
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
}
