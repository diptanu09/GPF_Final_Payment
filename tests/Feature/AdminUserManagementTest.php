<?php

namespace Tests\Feature;

use App\Models\AdminSecurityToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_non_admin_cannot_access_admin_user_console(): void
    {
        $deo = User::where('username', 'deeksha')->first();

        $response = $this->actingAs($deo)->get('/admin/users');
        $response->assertForbidden();
    }

    public function test_admin_can_access_user_governance_console(): void
    {
        $admin = User::where('username', 'dir')->first();

        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertOk();
    }

    public function test_admin_can_approve_pending_user(): void
    {
        $admin = User::where('username', 'dir')->first();

        $pendingUser = User::create([
            'name' => 'Pending Officer',
            'username' => 'pending_off',
            'email' => 'pending.off@tripura.gov.in',
            'password' => Hash::make('password123'),
            'role' => 'deo',
            'approval_status' => 'pending',
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->post("/admin/users/{$pendingUser->id}/approve");

        $response->assertSessionHas('success');

        $pendingUser->refresh();
        $this->assertEquals('approved', $pendingUser->approval_status);
        $this->assertTrue($pendingUser->is_active);
        $this->assertEquals($admin->id, $pendingUser->approved_by);
        $this->assertNotNull($pendingUser->approved_at);
    }

    public function test_admin_can_reject_user(): void
    {
        $admin = User::where('username', 'dir')->first();

        $user = User::create([
            'name' => 'Rejected Officer',
            'username' => 'rejected_off',
            'email' => 'rejected.off@tripura.gov.in',
            'password' => Hash::make('password123'),
            'role' => 'deo',
            'approval_status' => 'pending',
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->post("/admin/users/{$user->id}/reject", [
            'notes' => 'Invalid government identity documents provided.',
        ]);

        $response->assertSessionHas('info');

        $user->refresh();
        $this->assertEquals('rejected', $user->approval_status);
        $this->assertFalse($user->is_active);
    }

    public function test_admin_can_update_user_role_and_attributes(): void
    {
        $admin = User::where('username', 'dir')->first();
        $targetUser = User::where('username', 'kalipada')->first();

        $response = $this->actingAs($admin)->put("/admin/users/{$targetUser->id}", [
            'name' => 'Kalipada Deb (Sr. DEO)',
            'email' => 'kalipada.deb@tripura.gov.in',
            'role' => 'checker',
            'designation' => 'Assistant Accounts Officer',
            'section' => 'Fund Section I',
            'phone_number' => '9876543210',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        $response->assertSessionHas('success');

        $targetUser->refresh();
        $this->assertEquals('Kalipada Deb (Sr. DEO)', $targetUser->name);
        $this->assertEquals('checker', $targetUser->role);
        $this->assertEquals('Assistant Accounts Officer', $targetUser->designation);
        $this->assertEquals('Fund Section I', $targetUser->section);
        $this->assertEquals('9876543210', $targetUser->phone_number);
    }

    public function test_admin_can_reset_user_password_directly(): void
    {
        $admin = User::where('username', 'dir')->first();
        $targetUser = User::where('username', 'anjana')->first();

        $response = $this->actingAs($admin)->post("/admin/users/{$targetUser->id}/reset-password", [
            'password' => 'NewAdminAssignedPass@2026',
        ]);

        $response->assertSessionHas('success');

        $targetUser->refresh();
        $this->assertTrue(Hash::check('NewAdminAssignedPass@2026', $targetUser->password));
    }

    public function test_admin_can_generate_and_revoke_security_token(): void
    {
        $admin = User::where('username', 'dir')->first();

        // 1. Generate Token
        $response = $this->actingAs($admin)->post('/admin/tokens/generate', [
            'token_type' => 'registration',
            'role' => 'approver',
            'issued_for_email' => 'new.approver@tripura.gov.in',
            'expiry_days' => 5,
            'notes' => 'Token issued for joining Sr. AO.',
        ]);

        $response->assertSessionHas('success');

        $token = AdminSecurityToken::where('issued_for_email', 'new.approver@tripura.gov.in')->first();
        $this->assertNotNull($token);
        $this->assertStringStartsWith('ADM-REG-', $token->token);
        $this->assertEquals('registration', $token->token_type);
        $this->assertEquals('approver', $token->role);
        $this->assertTrue($token->isValid());

        // 2. Revoke Token
        $revokeRes = $this->actingAs($admin)->delete("/admin/tokens/{$token->id}");
        $revokeRes->assertSessionHas('info');

        $this->assertDatabaseMissing('admin_security_tokens', ['id' => $token->id]);
    }

    public function test_user_can_view_and_update_profile(): void
    {
        $user = User::where('username', 'rkdb')->first();

        // 1. View Profile
        $this->actingAs($user)->get('/profile')->assertOk();

        // 2. Update Profile
        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'R. K. Debbarma (Sr. AO Special)',
            'email' => 'rkdb.special@tripura.gov.in',
            'designation' => 'Senior Accounts Officer',
            'section' => 'Fund Special Cell',
            'phone_number' => '9436123456',
        ]);

        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('R. K. Debbarma (Sr. AO Special)', $user->name);
        $this->assertEquals('rkdb.special@tripura.gov.in', $user->email);
        $this->assertEquals('Senior Accounts Officer', $user->designation);
        $this->assertEquals('Fund Special Cell', $user->section);
        $this->assertEquals('9436123456', $user->phone_number);
    }

    public function test_user_can_update_password_with_current_password_verification(): void
    {
        $user = User::where('username', 'rkdb')->first();

        // Failed attempt with wrong current password
        $failResponse = $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'wrong_password',
            'password' => 'BrandNewPassword@2026',
            'password_confirmation' => 'BrandNewPassword@2026',
        ]);
        $failResponse->assertSessionHasErrors('current_password');

        // Successful attempt with correct current password ('rbsr123')
        $successResponse = $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'rbsr123',
            'password' => 'BrandNewPassword@2026',
            'password_confirmation' => 'BrandNewPassword@2026',
        ]);
        $successResponse->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('BrandNewPassword@2026', $user->password));
    }
}
