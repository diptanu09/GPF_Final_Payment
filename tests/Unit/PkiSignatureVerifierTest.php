<?php

namespace Tests\Unit;

use App\Enums\CaseType;
use App\Enums\CaseWorkflowStatus;
use App\Models\Authority;
use App\Models\InwardCase;
use App\Models\User;
use App\Services\DigitalSignature\PkiSignatureVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PkiSignatureVerifierTest extends TestCase
{
    use RefreshDatabase;

    protected PkiSignatureVerifier $verifier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->verifier = new PkiSignatureVerifier();
    }

    public function test_compute_sha256_digest(): void
    {
        $content = "GPF/FINAL/2024/001-PAYMENT-AUTHORITY";
        $hash = $this->verifier->computeDigest($content);

        $this->assertEquals(hash('sha256', $content), $hash);
        $this->assertEquals(64, strlen($hash));
    }

    public function test_signature_verification_and_recording_by_approver(): void
    {
        $approver = User::where('role', 'approver')->first();
        $deo = User::where('role', 'deo')->first();

        $case = InwardCase::create([
            'registration_no' => '20240188888',
            'series_code' => '01',
            'account_no' => '88888',
            'subscriber_name_cache' => 'Shri Anjan Deb',
            'name_title' => 'Shri',
            'designation_title' => 'Mr',
            'designation' => 'Superintendent',
            'case_type' => CaseType::NORMAL_SUPERANNUATION,
            'pension_type_id' => '1',
            'ddo_code' => '1001',
            'treasury_code' => '01',
            'event_date' => '2024-03-31',
            'personal_address' => 'Agartala',
            'current_status' => CaseWorkflowStatus::APPROVED,
            'created_by' => $deo->id,
        ]);

        $authority = Authority::create([
            'inward_case_id' => $case->id,
            'authority_number' => 'AG-TR/GPF/AUTH/2024/001',
            'authority_date' => now(),
            'gross_amount' => 540000.00,
            'deductions_amount' => 0.00,
            'net_amount' => 540000.00,
            'dlis_amount' => 0.00,
            'is_signed' => false,
        ]);

        $payload = [
            'signed_hash' => hash('sha256', 'AG-TR/GPF/AUTH/2024/001-TEST-HASH'),
            'certificate_serial' => 'DSC-CERT-884920412399',
            'certificate_issuer' => 'NIC Certifying Authority',
            'certificate_valid_to' => '2026-12-31',
        ];

        $sig = $this->verifier->verifyAndRecord($authority, $payload, $approver);

        $this->assertNotNull($sig);
        $this->assertTrue($authority->fresh()->is_signed);
        $this->assertEquals($sig->id, $authority->fresh()->digital_signature_id);
        $this->assertEquals('NIC Certifying Authority', $sig->certificate_issuer);
    }
}
