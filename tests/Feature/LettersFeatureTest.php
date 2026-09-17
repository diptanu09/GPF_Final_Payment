<?php

namespace Tests\Feature;

use App\Enums\CaseType;
use App\Enums\CaseWorkflowStatus;
use App\Models\Authority;
use App\Models\CalculationRun;
use App\Models\InwardCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LettersFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $deo;
    protected User $approver;
    protected InwardCase $case;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->deo = User::firstOrCreate(['username' => 'test_deo'], [
            'name' => 'Test DEO',
            'email' => 'test_deo@tripura.gov.in',
            'password' => 'secret123',
            'role' => 'deo',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $this->approver = User::firstOrCreate(['username' => 'test_approver'], [
            'name' => 'Test Approver',
            'email' => 'test_approver@tripura.gov.in',
            'password' => 'secret123',
            'role' => 'approver',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $this->case = InwardCase::create([
            'registration_no' => '202611009999',
            'diary_number' => 'INW/2026/9999',
            'diary_date' => now(),
            'series_code' => '11',
            'series_name' => 'EDN',
            'account_no' => '99999',
            'subscriber_name_cache' => 'Pranab Sen',
            'name_title' => 'Shri',
            'designation_title' => 'Headmaster',
            'designation' => 'Graduate Teacher',
            'case_type' => CaseType::NORMAL_SUPERANNUATION,
            'pension_type_id' => '1',
            'pension_type_name' => 'Superannuation (SUP)',
            'section' => 'Fund Section I',
            'ddo_code' => 'EDN001',
            'ddo_designation' => 'HM Umakanta Academy',
            'treasury_code' => 'AGT',
            'treasury_name' => 'Agartala Treasury I',
            'event_date' => '2026-03-31',
            'last_fund_deduction' => '2026-02-28',
            'debit_during_year' => 0.00,
            'personal_address' => 'Ramnagar Road No 2, Agartala',
            'current_status' => CaseWorkflowStatus::CALCULATED,
            'created_by' => $this->deo->id,
            'assigned_user_id' => $this->deo->id,
        ]);

        CalculationRun::create([
            'inward_case_id' => $this->case->id,
            'opening_fin_year' => '2023-2024',
            'opening_balance_amount' => 450000.00,
            'total_subscriptions' => 120000.00,
            'total_refunds' => 0.00,
            'total_withdrawals' => 50000.00,
            'excess_deposits' => 0.00,
            'actual_interest_computed' => 32000.00,
            'delayed_interest_computed' => 0.00,
            'total_interest_computed' => 32000.00,
            'final_closing_balance' => 552000.00,
            'computed_by' => $this->deo->id,
        ]);
    }

    public function test_letters_index_page_renders(): void
    {
        $response = $this->actingAs($this->deo)->get('/letters');
        $response->assertStatus(200);
    }

    public function test_input_sheet_report_renders_printable_view(): void
    {
        $response = $this->actingAs($this->deo)->get("/letters/input-sheet/{$this->case->id}");
        $response->assertStatus(200);
        $response->assertSee('OFFICE OF THE ACCOUNTANT GENERAL');
        $response->assertSee('Input sheet');
        $response->assertSee('Pranab Sen');
        $response->assertSee('552,000.00');
    }

    public function test_intimation_letter_renders_annexure_5_24(): void
    {
        Authority::create([
            'inward_case_id' => $this->case->id,
            'authority_number' => 'No. Fund Section I / FP / SUP / 2023-2024 / 202611009999 /',
            'authority_date' => now(),
            'gross_amount' => 552000.00,
            'net_amount' => 552000.00,
            'is_signed' => true,
            'signed_by' => $this->approver->id,
            'signed_at' => now(),
        ]);

        $response = $this->actingAs($this->deo)->get("/letters/intimation/{$this->case->id}");
        $response->assertStatus(200);
        $response->assertSee('Annexure - 5.24');
        $response->assertSee('Intimation to Subscriber on Issue of Authorization');
        $response->assertSee('six (6) months');
    }

    public function test_corrigendum_and_revalidation_orders_render(): void
    {
        $corrResponse = $this->actingAs($this->deo)->get("/letters/corrigendum/{$this->case->id}");
        $corrResponse->assertStatus(200);
        $corrResponse->assertSee('CORRIGENDUM');

        $revalResponse = $this->actingAs($this->deo)->get("/letters/revalidation/{$this->case->id}");
        $revalResponse->assertStatus(200);
        $revalResponse->assertSee('REVALIDATION ORDER');
    }

    public function test_objection_memo_and_minus_balance_notice_render(): void
    {
        $objResponse = $this->actingAs($this->deo)->get("/letters/objection/{$this->case->id}");
        $objResponse->assertStatus(200);
        $objResponse->assertSee('OBJECTION / DEFECT RETURN MEMORANDUM');

        $mbResponse = $this->actingAs($this->deo)->get("/letters/minus-balance/{$this->case->id}");
        $mbResponse->assertStatus(200);
        $mbResponse->assertSee('Rule 11(7)');
    }

    public function test_minus_balance_recovery_can_be_recorded(): void
    {
        $response = $this->actingAs($this->deo)->post("/letters/minus-balance/{$this->case->id}/recovery", [
            'amount_recovered' => 45000.00,
            'remarks' => 'Recovered through Treasury Challan No. TR-9982 dated 15/09/2026',
            'close_minus_balance' => true,
        ]);

        $response->assertSessionHas('success');

        $this->case->refresh();
        $this->assertEquals(45000.00, (float)$this->case->amount_recovered);
        $this->assertStringContainsString('TR-9982', $this->case->minus_balance_remarks);
        $this->assertNotNull($this->case->minus_balance_closed_at);

        $this->assertDatabaseHas('workflow_histories', [
            'inward_case_id' => $this->case->id,
            'action_type' => 'MINUS_BALANCE_RECOVERY',
        ]);
    }
}
