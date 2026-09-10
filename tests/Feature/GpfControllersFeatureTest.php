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

class GpfControllersFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_approval_controller_check_and_approve_endpoints(): void
    {
        $deo = User::where('role', 'deo')->first();
        $aao = User::where('role', 'checker')->first();
        $srao = User::where('role', 'approver')->first();

        $case = InwardCase::create([
            'registration_no' => '20240177777',
            'series_code' => '01',
            'account_no' => '77777',
            'subscriber_name_cache' => 'Shri Biplab Ghosh',
            'name_title' => 'Shri',
            'designation_title' => 'Mr',
            'designation' => 'Inspector',
            'case_type' => CaseType::NORMAL_SUPERANNUATION,
            'pension_type_id' => '1',
            'ddo_code' => '1001',
            'treasury_code' => '01',
            'event_date' => '2024-03-31',
            'personal_address' => 'Agartala',
            'current_status' => CaseWorkflowStatus::CALCULATED,
            'created_by' => $deo->id,
        ]);

        // AAO Check
        $response = $this->actingAs($aao)->post("/approval/{$case->id}/check", [
            'remarks' => 'Verified calculation sheets and ledger slips.',
        ]);
        $response->assertRedirect();
        $this->assertEquals(CaseWorkflowStatus::CHECKED, $case->fresh()->current_status);

        // Sr. AO Approve
        $response = $this->actingAs($srao)->post("/approval/{$case->id}/approve", [
            'remarks' => 'Sanctioned for final settlement.',
        ]);
        $response->assertRedirect();
        $this->assertEquals(CaseWorkflowStatus::APPROVED, $case->fresh()->current_status);
    }

    public function test_nominee_sync_controller_endpoint(): void
    {
        $deo = User::where('role', 'deo')->first();

        $case = InwardCase::create([
            'registration_no' => '20240166666',
            'series_code' => '01',
            'account_no' => '66666',
            'subscriber_name_cache' => 'Late Pradip Sen',
            'name_title' => 'Late',
            'designation_title' => 'Mr',
            'designation' => 'Assistant Teacher',
            'case_type' => CaseType::DEATH_IN_SERVICE,
            'pension_type_id' => '2',
            'ddo_code' => '1001',
            'treasury_code' => '01',
            'event_date' => '2023-11-20',
            'personal_address' => 'Udaipur',
            'current_status' => CaseWorkflowStatus::DRAFT,
            'created_by' => $deo->id,
        ]);

        $response = $this->actingAs($deo)->post("/nominees/{$case->id}", [
            'nominees' => [
                [
                    'nominee_name' => 'Smt Gita Sen',
                    'beneficiary_code' => 'BEN-66001',
                    'relationship' => 'Spouse',
                    'share_percentage' => 50.00,
                    'is_minor' => false,
                    'bank_account_no' => '123456789012',
                    'ifsc_code' => 'SBIN0001234',
                ],
                [
                    'nominee_name' => 'Master Rahul Sen',
                    'beneficiary_code' => 'BEN-66002',
                    'relationship' => 'Son',
                    'share_percentage' => 50.00,
                    'is_minor' => true,
                    'guardian_name' => 'Smt Gita Sen',
                    'guardian_relation' => 'Mother',
                    'bank_account_no' => '123456789012',
                    'ifsc_code' => 'SBIN0001234',
                ],
            ],
        ]);

        $response->assertRedirect();
        $freshNominees = $case->fresh()->nominees;
        $this->assertCount(2, $freshNominees);
        $this->assertEquals('BEN-66001', $freshNominees->first()->beneficiary_code);
    }

    public function test_authority_generation_and_signing(): void
    {
        $srao = User::where('role', 'approver')->first();
        $deo = User::where('role', 'deo')->first();

        $case = InwardCase::create([
            'registration_no' => '20240155555',
            'series_code' => '01',
            'account_no' => '55555',
            'subscriber_name_cache' => 'Shri Subhash Das',
            'name_title' => 'Shri',
            'designation_title' => 'Mr',
            'designation' => 'Head Clerk',
            'case_type' => CaseType::NORMAL_SUPERANNUATION,
            'pension_type_id' => '1',
            'ddo_code' => '1001',
            'treasury_code' => '01',
            'event_date' => '2024-03-31',
            'personal_address' => 'Dharmanagar',
            'current_status' => CaseWorkflowStatus::APPROVED,
            'created_by' => $deo->id,
        ]);

        $calcRun = CalculationRun::create([
            'inward_case_id' => $case->id,
            'run_by' => $deo->id,
            'run_date' => now(),
            'opening_balance_amount' => 150000.00,
            'opening_fin_year' => '2023-2024',
            'total_subscriptions' => 30000.00,
            'total_refunds' => 0.00,
            'total_withdrawals' => 0.00,
            'total_interest_computed' => 12500.00,
            'dlis_amount' => 0.00,
            'final_closing_balance' => 192500.00,
            'status' => 'FINAL',
        ]);

        // Generate Authority
        $response = $this->actingAs($srao)->post("/authority/generate/{$case->id}");
        $response->assertRedirect();

        $authority = Authority::where('inward_case_id', $case->id)->first();
        $this->assertNotNull($authority);
        $this->assertEquals(192500.00, (float) $authority->net_amount);

        // Sign Authority
        $signResponse = $this->actingAs($srao)->postJson("/authority/{$authority->id}/sign", [
            'signed_hash' => hash('sha256', 'TEST-AUTH-HASH-55555'),
            'certificate_serial' => 'DSC-CERT-TEST-12345',
            'certificate_issuer' => 'eMudhra CA',
        ]);
        $signResponse->assertOk()->assertJson(['status' => 'success']);
        $this->assertTrue($authority->fresh()->is_signed);
        $this->assertEquals(CaseWorkflowStatus::AUTHORIZED, $case->fresh()->current_status);
    }

    public function test_dispatch_and_hrms_workflow(): void
    {
        $srao = User::where('role', 'approver')->first();
        $deo = User::where('role', 'deo')->first();

        $case = InwardCase::create([
            'registration_no' => '20240144444',
            'series_code' => '01',
            'account_no' => '44444',
            'subscriber_name_cache' => 'Shri Dilip Roy',
            'name_title' => 'Shri',
            'designation_title' => 'Mr',
            'designation' => 'Principal',
            'case_type' => CaseType::NORMAL_SUPERANNUATION,
            'pension_type_id' => '1',
            'ddo_code' => '1001',
            'treasury_code' => '01',
            'event_date' => '2024-03-31',
            'personal_address' => 'Kailashahar',
            'current_status' => CaseWorkflowStatus::AUTHORIZED,
            'created_by' => $deo->id,
        ]);

        $authority = Authority::create([
            'inward_case_id' => $case->id,
            'authority_number' => 'AG-TR/GPF/AUTH/2024/44444',
            'authority_date' => now(),
            'gross_amount' => 350000.00,
            'deductions_amount' => 0.00,
            'net_amount' => 350000.00,
            'dlis_amount' => 0.00,
            'is_signed' => true,
            'signed_at' => now(),
        ]);

        // Upload to HRMS
        $hrmsResp = $this->actingAs($srao)->post("/dispatch/{$authority->id}/hrms");
        $hrmsResp->assertRedirect();
        $this->assertTrue($authority->fresh()->is_uploaded_hrms);
        $this->assertEquals(CaseWorkflowStatus::HRMS_SYNCED, $case->fresh()->current_status);

        // Dispatch Outward
        $dispResp = $this->actingAs($srao)->post("/dispatch/{$authority->id}/dispatch", [
            'dispatch_barcode' => 'TR9988776655IN',
            'remarks' => 'Dispatched via Registered Speed Post to DDO and Subscriber.',
        ]);
        $dispResp->assertRedirect();
        $this->assertTrue($authority->fresh()->is_dispatched);
        $this->assertEquals('TR9988776655IN', $authority->fresh()->dispatch_barcode);
        $this->assertEquals(CaseWorkflowStatus::DISPATCHED, $case->fresh()->current_status);
    }

    public function test_reports_endpoints_render(): void
    {
        $srao = User::where('role', 'approver')->first();

        $this->actingAs($srao)->get('/reports')->assertStatus(200);
        $this->actingAs($srao)->get('/reports/settled')->assertStatus(200);
        $this->actingAs($srao)->get('/reports/pending')->assertStatus(200);
    }
}
