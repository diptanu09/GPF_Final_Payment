<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Integration\OracleMasterBridge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InwardCaseLookupFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_inward_create_page_renders_with_master_dropdowns(): void
    {
        $deo = User::where('role', 'deo')->first();

        $response = $this->actingAs($deo)->get('/inward/create');
        $response->assertOk();
    }

    public function test_subscriber_lookup_endpoint_returns_structured_subscriber_data(): void
    {
        $deo = User::where('role', 'deo')->first();

        $response = $this->actingAs($deo)->getJson('/inward/lookup?series_code=01&account_no=14240');
        $response->assertOk();
        $response->assertJsonStructure([
            'found_in_oracle',
            'is_closed',
            'closure_date',
            'warning',
            'series_code',
            'account_no',
            'subscriber_name',
            'name_title',
            'designation_title',
            'designation',
            'employee_code',
            'beneficiary_code',
            'mobile_no',
            'opening_balance',
            'closing_balance',
            'closing_fin_year',
            'personal_address',
            'ddo_code',
            'treasury_code',
            'spouse_name',
            'spouse_relation',
        ]);
    }

    public function test_deo_can_create_family_pension_inward_case(): void
    {
        $deo = User::where('role', 'deo')->first();

        $payload = [
            'series_code' => '01',
            'series_name' => 'AIS',
            'account_no' => '54321',
            'subscriber_name' => 'Manish Paul',
            'name_title' => 'Late',
            'designation_title' => 'Mr',
            'designation' => 'Assistant Teacher',
            'case_type' => 'FAM',
            'pension_type_id' => '2',
            'ddo_code' => '6016',
            'treasury_code' => 'TPA06',
            'event_date' => '2023-11-20',
            'personal_address' => 'Kamalpur, Dhalai, Tripura',
            'mobile_no' => '9876543210',
            'spouse_name' => 'Rita Paul',
            'spouse_relation' => 'Wife',
        ];

        $response = $this->actingAs($deo)->post('/inward', $payload);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('inward_cases', [
            'account_no' => '54321',
            'case_type' => 'FAM',
            'pension_type_id' => '2',
            'pension_type_name' => 'Family (FAM)',
            'name_title' => 'Late',
            'spouse_name' => 'Rita Paul',
        ]);
    }
}
