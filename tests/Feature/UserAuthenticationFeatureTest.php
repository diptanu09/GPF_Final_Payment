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
}
