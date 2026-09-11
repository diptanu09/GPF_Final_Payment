<?php

namespace Tests\Feature;

use App\Enums\CaseType;
use App\Enums\CaseWorkflowStatus;
use App\Models\CaseNominee;
use App\Models\InwardCase;
use App\Models\User;
use App\Services\Calculation\CutoffRuleResolver;
use App\Services\Calculation\GpfCalculationEngine;
use App\Services\Workflow\GpfWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GpfWorkflowFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        User::firstOrCreate(['username' => 'test_deo'], [
            'name' => 'Test DEO',
            'email' => 'test_deo@tripura.gov.in',
            'password' => 'secret123',
            'role' => 'deo',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        User::firstOrCreate(['username' => 'test_checker'], [
            'name' => 'Test Checker',
            'email' => 'test_checker@tripura.gov.in',
            'password' => 'secret123',
            'role' => 'checker',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        User::firstOrCreate(['username' => 'test_approver'], [
            'name' => 'Test Approver',
            'email' => 'test_approver@tripura.gov.in',
            'password' => 'secret123',
            'role' => 'approver',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
    }

    public function test_complete_gpf_settlement_workflow_lifecycle(): void
    {
        $deo = User::where('role', 'deo')->first();
        $da = User::where('username', 'kalipada')->orWhere('role', 'deo')->first() ?? $deo;
        $aao = User::where('role', 'checker')->first();
        $srao = User::where('role', 'approver')->first();

        // 1. DEO creates Inward Case
        $response = $this->actingAs($deo)->post('/inward', [
            'series_code' => '01',
            'account_no' => '99991',
            'subscriber_name' => 'Sri Debabrata Roy',
            'name_title' => 'Shri',
            'designation_title' => 'Mr',
            'designation' => 'Executive Engineer',
            'case_type' => CaseType::NORMAL_SUPERANNUATION->value,
            'pension_type_id' => '1',
            'ddo_code' => '1001',
            'treasury_code' => '01',
            'event_date' => '2024-03-31',
            'personal_address' => 'Agartala, West Tripura',
            'remarks' => 'Superannuation claim application received',
        ]);

        $response->assertRedirect();
        $case = InwardCase::where('account_no', '99991')->first();
        $this->assertNotNull($case);
        $this->assertEquals(CaseWorkflowStatus::DRAFT, $case->current_status);

        // 2. DA performs Calculation Run
        $calcEngine = new GpfCalculationEngine(new CutoffRuleResolver());
        $ledgerEntries = [
            'opening_balance' => 250000.00,
            'opening_fin_year' => '2023-2024',
            'monthly_entries' => [
                [
                    'financial_year' => '2023-2024',
                    'calendar_month' => '2023-04',
                    'pay_slip_date' => '2023-04-01',
                    'accounting_month' => 1,
                    'deposit' => 20000.00,
                    'withdrawal' => 0.00,
                    'rate_of_interest' => 7.1000,
                    'interest_on_deposit' => true,
                ],
                [
                    'financial_year' => '2023-2024',
                    'calendar_month' => '2023-05',
                    'pay_slip_date' => '2023-05-01',
                    'accounting_month' => 2,
                    'deposit' => 20000.00,
                    'withdrawal' => 0.00,
                    'rate_of_interest' => 7.1000,
                    'interest_on_deposit' => true,
                ],
            ],
        ];

        $run = $calcEngine->calculate($case, $ledgerEntries, $da->id);
        $this->assertNotNull($run);
        $this->assertGreaterThan(290000.00, (float) $run->final_closing_balance);

        // 3. Nominee matrix configuration (Self 100%)
        $nominee = CaseNominee::create([
            'inward_case_id' => $case->id,
            'nominee_name' => 'Sri Debabrata Roy',
            'relationship' => 'Self',
            'share_percentage' => 100.00,
        ]);
        $calcEngine->partitionNomineeShares($case, (float) $run->final_closing_balance);
        $this->assertEquals((float) $run->final_closing_balance, (float) $nominee->fresh()->allocated_amount);

        // 4. Workflow State Transitions
        $workflowService = app(GpfWorkflowService::class);

        // DA Submits calculation
        $workflowService->transition(
            $case->id,
            CaseWorkflowStatus::CALCULATED,
            'CALCULATION_SUBMIT',
            'Calculation finalized by Dealing Assistant',
            $da
        );
        $this->assertEquals(CaseWorkflowStatus::CALCULATED, $case->fresh()->current_status);

        // AAO Audits and Checks
        $workflowService->transition(
            $case->id,
            CaseWorkflowStatus::CHECKED,
            'AAO_VERIFY',
            'Audited and verified statutory calculations and interest rates by AAO',
            $aao
        );
        $this->assertEquals(CaseWorkflowStatus::CHECKED, $case->fresh()->current_status);

        // Sr. AO Approves
        $workflowService->transition(
            $case->id,
            CaseWorkflowStatus::APPROVED,
            'SRAO_APPROVE',
            'Final Payment Sanctioned and Approved by Senior Accounts Officer',
            $srao
        );
        $this->assertEquals(CaseWorkflowStatus::APPROVED, $case->fresh()->current_status);

        // Check workflow history audit trail (Initial draft registration + 3 transitions = 4)
        $this->assertCount(4, $case->workflowHistories);
    }
}
