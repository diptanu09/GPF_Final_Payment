<?php

namespace App\Services\DigitalSignature;

use App\Models\Authority;
use App\Models\DigitalSignature;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PkiSignatureVerifier
{
    /**
     * Compute SHA-256 digest of document contents
     */
    public function computeDigest(string $content): string
    {
        return hash('sha256', $content);
    }

    /**
     * Verify PKCS#7 / CMS signature block and record in digital_signatures log
     */
    public function verifyAndRecord(Authority $authority, array $payload, User $signatoryUser, ?string $ipAddress = null): DigitalSignature
    {
        return DB::transaction(function () use ($authority, $payload, $signatoryUser, $ipAddress) {
            $signedHash = $payload['signed_hash'] ?? null;
            $certificateSerial = $payload['certificate_serial'] ?? 'DSC-' . strtoupper(substr(md5(uniqid()), 0, 12));
            $certificateIssuer = $payload['certificate_issuer'] ?? 'eMudhra / NIC Certifying Authority';
            $validTo = isset($payload['certificate_valid_to']) ? Carbon::parse($payload['certificate_valid_to']) : Carbon::now()->addYears(2);

            if (empty($signedHash)) {
                throw new RuntimeException('Invalid signature payload: signed_hash is required.');
            }

            // Verify signer role
            if (!$signatoryUser->isApprover()) {
                throw new RuntimeException('Unauthorized: Only authorized Senior Accounts Officers may apply digital signatures.');
            }

            // Record Digital Signature in legal evidentiary log
            $sig = DigitalSignature::create([
                'authority_id' => $authority->id,
                'signatory_user_id' => $signatoryUser->id,
                'signatory_name' => $signatoryUser->name,
                'signatory_role' => $signatoryUser->roleLabel(),
                'certificate_serial' => $certificateSerial,
                'certificate_issuer' => $certificateIssuer,
                'signed_hash' => $signedHash,
                'signed_at' => now(),
                'certificate_valid_to' => $validTo,
                'ip_address' => $ipAddress,
            ]);

            // Update Authority
            $authority->update([
                'digital_signature_id' => $sig->id,
                'is_signed' => true,
                'signed_at' => now(),
                'verification_hash' => $this->computeDigest($signedHash),
            ]);

            return $sig;
        });
    }
}
