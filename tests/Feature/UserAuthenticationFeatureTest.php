<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAuthenticationFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $accounts = [
            ['username' => 'dir', 'password' => 'dir', 'role' => 'admin', 'name' => 'Director', 'email' => 'dir@tripura.gov.in'],
            ['username' => 'jdg', 'password' => 'Juhi1234@', 'role' => 'admin', 'name' => 'JDG', 'email' => 'jdg@tripura.gov.in'],
            ['username' => 'rkdb', 'password' => 'rbsr123', 'role' => 'approver', 'name' => 'R.K. Debbarma', 'email' => 'rkdb@tripura.gov.in'],
            ['username' => 'anjana', 'password' => 'ad123', 'role' => 'checker', 'name' => 'Anjana', 'email' => 'anjana@tripura.gov.in'],
            ['username' => 'deeksha', 'password' => 'deeksha@123', 'role' => 'deo', 'name' => 'Deeksha', 'email' => 'deeksha@tripura.gov.in'],
            ['username' => 'kalipada', 'password' => 'Lp123', 'role' => 'deo', 'name' => 'Kalipada', 'email' => 'kalipada@tripura.gov.in'],
        ];

        foreach ($accounts as $acc) {
            User::firstOrCreate(
                ['username' => $acc['username']],
                [
                    'name' => $acc['name'],
                    'email' => $acc['email'],
                    'password' => \Illuminate\Support\Facades\Hash::make($acc['password']),
                    'role' => $acc['role'],
                    'approval_status' => 'approved',
                    'is_active' => true,
                ]
            );
        }
    }

    public function test_login_page_renders_with_real_institutional_users(): void
    {
        $response = $this->get('/login');
        $response->assertOk();
    }

    public function test_institutional_users_can_authenticate(): void
    {
        $institutionalAccounts = [
            ['username' => 'dir', 'password' => 'dir', 'expected_role' => 'admin'],
            ['username' => 'jdg', 'password' => 'Juhi1234@', 'expected_role' => 'admin'],
            ['username' => 'rkdb', 'password' => 'rbsr123', 'expected_role' => 'approver'],
            ['username' => 'anjana', 'password' => 'ad123', 'expected_role' => 'checker'],
            ['username' => 'deeksha', 'password' => 'deeksha@123', 'expected_role' => 'deo'],
            ['username' => 'kalipada', 'password' => 'Lp123', 'expected_role' => 'deo'],
        ];

        foreach ($institutionalAccounts as $acc) {
            $response = $this->post('/login', [
                'username' => $acc['username'],
                'password' => $acc['password'],
            ]);

            $response->assertSessionHasNoErrors();
            $response->assertRedirect(route('dashboard'));
            $this->assertAuthenticated();

            $user = auth()->user();
            $this->assertEquals($acc['username'], $user->username);

            $this->post('/logout');
            $this->assertGuest();
        }
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $response = $this->post('/login', [
            'username' => 'dir',
            'password' => 'invalid_pass_123',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_registration_without_token_creates_pending_user(): void
    {
        $this->get('/register')->assertOk();

        $response = $this->post('/register', [
            'name' => 'Shri Sanjit Deb',
            'username' => 'sanjit_deb',
            'email' => 'sanjit.deb@tripura.gov.in',
            'role' => 'checker',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('login'));

        $user = User::where('username', 'sanjit_deb')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Shri Sanjit Deb', $user->name);
        $this->assertEquals('checker', $user->role);
        $this->assertEquals('pending', $user->approval_status);
        $this->assertFalse($user->is_active);

        // Pending user cannot login
        $loginRes = $this->post('/login', [
            'username' => 'sanjit_deb',
            'password' => 'Password@123',
        ]);
        $loginRes->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_registration_with_admin_security_token_auto_approves(): void
    {
        $admin = User::where('username', 'dir')->first();
        $token = \App\Models\AdminSecurityToken::create([
            'token' => 'ADM-REG-TEST999',
            'token_type' => 'registration',
            'role' => 'approver',
            'created_by' => $admin->id,
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->post('/register', [
            'name' => 'Shri Biplab Roy',
            'username' => 'biplab_roy',
            'email' => 'biplab.roy@tripura.gov.in',
            'role' => 'deo', // token role override
            'admin_token' => 'ADM-REG-TEST999',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('login'));

        $user = User::where('username', 'biplab_roy')->first();
        $this->assertNotNull($user);
        $this->assertEquals('approved', $user->approval_status);
        $this->assertTrue($user->is_active);
        $this->assertEquals('approver', $user->role); // took role from token

        // Auto-approved user can login immediately
        $loginRes = $this->post('/login', [
            'username' => 'biplab_roy',
            'password' => 'Password@123',
        ]);
        $loginRes->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_validates_unique_username_and_email(): void
    {
        $response = $this->post('/register', [
            'name' => 'Duplicate Test',
            'username' => 'rkdb', // already exists
            'email' => 'rkdb@tripura.gov.in', // already exists
            'role' => 'deo',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        $response->assertSessionHasErrors(['username', 'email']);
    }

    public function test_forgot_password_flow_and_reset(): void
    {
        $this->get('/forgot-password')->assertOk();

        $user = User::where('username', 'rkdb')->first();

        // 1. Submit email/username to request token
        $response = $this->post('/forgot-password', [
            'identifier' => $user->email,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // Verify token in DB
        $resetEntry = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->first();

        $this->assertNotNull($resetEntry);

        // Extract token from redirect URL
        $targetUrl = $response->headers->get('Location');
        parse_str(parse_url($targetUrl, PHP_URL_QUERY), $queryParams);
        $token = $queryParams['token'];

        // 2. Render reset password page
        $this->get("/reset-password?token={$token}&email=" . urlencode($user->email))->assertOk();

        // 3. Submit new password
        $resetResponse = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewSecurePassword@2026',
            'password_confirmation' => 'NewSecurePassword@2026',
        ]);

        $resetResponse->assertSessionHasNoErrors();
        $resetResponse->assertRedirect(route('login'));

        // 4. Authenticate with new password
        $loginResponse = $this->post('/login', [
            'username' => 'rkdb',
            'password' => 'NewSecurePassword@2026',
        ]);

        $loginResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_forgot_username_lookup(): void
    {
        $this->get('/forgot-username')->assertOk();

        $user = User::where('username', 'anjana')->first();

        $response = $this->post('/forgot-username', [
            'email' => $user->email,
        ]);

        $response->assertOk();
    }
}
