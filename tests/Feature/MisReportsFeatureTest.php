<?php

namespace Tests\Feature;

use App\Enums\CaseType;
use App\Enums\CaseWorkflowStatus;
use App\Models\Authority;
use App\Models\CalculationRun;
use App\Models\DigitalSignature;
use App\Models\InwardCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MisReportsFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

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
    }

    public function test_user_productivity_report_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/reports/user-productivity');
        $response->assertStatus(200);
    }

    public function test_digital_signatures_report_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/reports/digital-signatures');
        $response->assertStatus(200);
    }

    public function test_minus_balance_report_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/reports/minus-balance');
        $response->assertStatus(200);
    }

    public function test_cancelled_cases_report_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/reports/cancelled');
        $response->assertStatus(200);
    }
}
