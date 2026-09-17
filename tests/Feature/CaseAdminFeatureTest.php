<?php

namespace Tests\Feature;

use App\Enums\CaseType;
use App\Enums\CaseWorkflowStatus;
use App\Models\Authority;
use App\Models\DigitalSignature;
use App\Models\InwardCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseAdminFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $approver;
    protected User $deo;
    protected User $otherDeo;
    protected InwardCase $case;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::firstOrCreate(['username' => 'test_admin'], [
            'name' => 'Test Admin',
            'email' => 'test_admin@tripura.gov.in',
            'password' => 'secret123',
            'role' => 'admin',
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

        $this->deo = User::firstOrCreate(['username' => 'test_deo'], [
            'name' => 'Test DEO',
            'email' => 'test_deo@tripura.gov.in',
            'password' => 'secret123',
            'role' => 'deo',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $this->otherDeo = User::firstOrCreate(['username' => 'test_deo2'], [
            'name' => 'Second DEO',
            'email' => 'test_deo2@tripura.gov.in',
            'password' => 'secret123',
            'role' => 'deo',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);

        $this->case = InwardCase::create([
            'registration_no' => '202611007777',
            'diary_number' => 'INW/2026/7777',
            'diary_date' => now(),
            'series_code' => '11',
            'series_name' => 'EDN',
            'account_no' => '77777',
            'subscriber_name_cache' => 'Nikhil Bhowmik',
            'name_title' => 'Shri',
            'designation_title' => 'Inspector',
            'designation' => 'Inspector',
            'case_type' => CaseType::NORMAL_SUPERANNUATION,
            'pension_type_id' => '1',
            'pension_type_name' => 'Superannuation (SUP)',
            'section' => 'Fund Section I',
            'ddo_code' => 'EDN001',
            'treasury_code' => 'AGT',
            'current_status' => CaseWorkflowStatus::APPROVED,
            'approved_at' => now(),
            'created_by' => $this->deo->id,
            'assigned_user_id' => $this->deo->id,
        ]);
    }

    public function test_deo_cannot_access_case_admin_console(): void
    {
        $response = $this->actingAs($this->deo)->get('/admin/cases');
        $response->assertStatus(403);
    }

    public function test_approver_and_admin_can_access_case_admin(): void
    {
        $response = $this->actingAs($this->approver)->get('/admin/cases');
        $response->assertStatus(200);

        $adminResponse = $this->actingAs($this->admin)->get('/admin/cases');
        $adminResponse->assertStatus(200);
    }

    public function test_case_can_be_unapproved_back_to_calculated(): void
    {
        $response = $this->actingAs($this->approver)->post("/admin/cases/{$this->case->id}/unapprove", [
            'remarks' => 'Reverted for correction of Base Financial Year balance discrepancy.',
        ]);

        $response->assertSessionHas('success');

        $this->case->refresh();
        $this->assertEquals(CaseWorkflowStatus::CALCULATED, $this->case->current_status);
        $this->assertNull($this->case->approved_at);
        $this->assertStringContainsString('Reverted for correction', $this->case->unapproved_remarks);

        $this->assertDatabaseHas('workflow_histories', [
            'inward_case_id' => $this->case->id,
            'action_type' => 'UNAPPROVE',
        ]);
    }

    public function test_case_can_be_cancelled_with_justification(): void
    {
        $response = $this->actingAs($this->approver)->post("/admin/cases/{$this->case->id}/cancel", [
            'remarks' => 'Court stay order received vide High Court WP(C) No. 998/2026.',
        ]);

        $response->assertSessionHas('success');

        $this->case->refresh();
        $this->assertEquals(CaseWorkflowStatus::CANCELLED, $this->case->current_status);
        $this->assertNotNull($this->case->cancelled_at);
        $this->assertStringContainsString('Court stay order', $this->case->cancelled_remarks);
    }

    public function test_digital_signature_can_be_reset(): void
    {
        $this->case->update([
            'current_status' => CaseWorkflowStatus::AUTHORIZED,
            'authorized_at' => now(),
        ]);

        $authority = Authority::create([
            'inward_case_id' => $this->case->id,
            'authority_number' => 'No. Fund Section I / FP / SUP / 2023-2024 / 202611007777 /',
            'authority_date' => now(),
            'gross_amount' => 500000.00,
            'net_amount' => 500000.00,
            'is_signed' => true,
            'verification_hash' => hash('sha256', 'dummy_signature'),
            'signed_at' => now(),
        ]);

        DigitalSignature::create([
            'authority_id' => $authority->id,
            'signatory_user_id' => $this->approver->id,
            'signatory_name' => 'R. K. Debbarma',
            'signatory_role' => 'Sr. Accounts Officer',
            'certificate_serial' => '12345678',
            'certificate_issuer' => 'e-Mudhra Sub-CA',
            'signed_hash' => hash('sha256', 'dummy_signature'),
            'signed_at' => now(),
        ]);

        $response = $this->actingAs($this->approver)->post("/admin/cases/{$authority->id}/reset-signature", [
            'remarks' => 'Typographical error in endorsement address, reset required.',
        ]);

        $response->assertSessionHas('success');

        $authority->refresh();
        $this->assertFalse($authority->is_signed);
        $this->assertNull($authority->verification_hash);

        $this->case->refresh();
        $this->assertEquals(CaseWorkflowStatus::APPROVED, $this->case->current_status);
        $this->assertNull($this->case->authorized_at);
    }

    public function test_sectional_receipt_docket_can_be_transferred_between_staff(): void
    {
        $response = $this->actingAs($this->deo)->post("/inward/{$this->case->id}/transfer", [
            'to_user_id' => $this->otherDeo->id,
            'remarks' => 'Reassigned to Section II Dealing Assistant Kalipada.',
        ]);

        $response->assertSessionHas('success');

        $this->case->refresh();
        $this->assertEquals($this->otherDeo->id, $this->case->assigned_user_id);
        $this->assertEquals($this->otherDeo->id, $this->case->transferred_to_user_id);
        $this->assertStringContainsString('Reassigned to Section II', $this->case->transfer_remarks);

        $this->assertDatabaseHas('workflow_histories', [
            'inward_case_id' => $this->case->id,
            'action_type' => 'TRANSFER_CASE',
        ]);
    }
}
