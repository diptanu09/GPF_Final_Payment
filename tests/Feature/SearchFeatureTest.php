<?php

namespace Tests\Feature;

use App\Enums\CaseType;
use App\Enums\CaseWorkflowStatus;
use App\Models\CalculationRun;
use App\Models\CaseNominee;
use App\Models\InwardCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $deo;
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

        $this->case = InwardCase::create([
            'registration_no' => '202611008888',
            'diary_number' => 'INW/2026/8888',
            'diary_date' => now(),
            'series_code' => '11',
            'series_name' => 'EDN',
            'account_no' => '88888',
            'subscriber_name_cache' => 'Subir Debnath',
            'name_title' => 'Shri',
            'designation_title' => 'Officer',
            'designation' => 'Inspector of Schools',
            'case_type' => CaseType::NORMAL_SUPERANNUATION,
            'pension_type_id' => '1',
            'pension_type_name' => 'Superannuation (SUP)',
            'section' => 'Fund Section I',
            'ddo_code' => 'EDN002',
            'ddo_designation' => 'Inspector of Schools Agartala',
            'treasury_code' => 'AGT',
            'treasury_name' => 'Agartala Treasury I',
            'event_date' => '2026-03-31',
            'last_fund_deduction' => '2026-02-28',
            'personal_address' => 'Banamalipur, Agartala',
            'current_status' => CaseWorkflowStatus::CALCULATED,
            'created_by' => $this->deo->id,
            'assigned_user_id' => $this->deo->id,
        ]);

        CalculationRun::create([
            'inward_case_id' => $this->case->id,
            'opening_fin_year' => '2023-2024',
            'opening_balance_amount' => 300000.00,
            'total_subscriptions' => 60000.00,
            'total_withdrawals' => 20000.00,
            'actual_interest_computed' => 22000.00,
            'delayed_interest_computed' => 0.00,
            'total_interest_computed' => 22000.00,
            'final_closing_balance' => 362000.00,
            'computed_by' => $this->deo->id,
        ]);

        CaseNominee::create([
            'inward_case_id' => $this->case->id,
            'nominee_name' => 'Sunita Debnath',
            'relationship' => 'Wife',
            'share_percentage' => 100.00,
            'allocated_amount' => 362000.00,
            'beneficiary_code' => 'BEN9981',
        ]);
    }

    public function test_search_index_page_renders(): void
    {
        $response = $this->actingAs($this->deo)->get('/search');
        $response->assertStatus(200);
    }

    public function test_search_filters_by_code_and_account(): void
    {
        $codeResponse = $this->actingAs($this->deo)->get('/search?search_type=code&query=202611008888');
        $codeResponse->assertStatus(200);

        $acctResponse = $this->actingAs($this->deo)->get('/search?search_type=account&series_id=11&account_no=88888');
        $acctResponse->assertStatus(200);
    }

    public function test_deep_docket_inspector_endpoint_returns_six_tabs(): void
    {
        $response = $this->actingAs($this->deo)->get("/search/{$this->case->id}/inspect");
        $response->assertStatus(200);

        $json = $response->json();
        $this->assertArrayHasKey('basic_info', $json);
        $this->assertArrayHasKey('nominees', $json);
        $this->assertArrayHasKey('timeline', $json);
        $this->assertArrayHasKey('financials', $json);
        $this->assertArrayHasKey('remarks', $json);
        $this->assertArrayHasKey('workflow_logs', $json);

        $this->assertEquals('202611008888', $json['basic_info']['registration_no']);
        $this->assertEquals('Subir Debnath', $json['basic_info']['subscriber_name']);
        $this->assertEquals(362000.00, $json['financials']['final_closing_balance']);
        $this->assertCount(1, $json['nominees']);
        $this->assertEquals('BEN9981', $json['nominees'][0]['beneficiary_code']);
    }
}
