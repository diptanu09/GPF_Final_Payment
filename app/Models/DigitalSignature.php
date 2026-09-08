<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalSignature extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'authority_id',
        'signatory_user_id',
        'signatory_name',
        'signatory_role',
        'certificate_serial',
        'certificate_issuer',
        'signed_hash',
        'signed_at',
        'certificate_valid_to',
        'ip_address',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'certificate_valid_to' => 'datetime',
    ];

    public function authority(): BelongsTo
    {
        return $this->belongsTo(Authority::class);
    }

    public function signatoryUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signatory_user_id');
    }
}
