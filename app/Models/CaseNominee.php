<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseNominee extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'inward_case_id',
        'nominee_name',
        'beneficiary_code',
        'relationship',
        'share_percentage',
        'allocated_amount',
        'bank_account_no',
        'bank_ifsc',
        'bank_name',
        'marital_status',
        'guardian_name',
        'is_minor',
        'address',
    ];

    protected $casts = [
        'share_percentage' => 'decimal:2',
        'allocated_amount' => 'decimal:2',
        'is_minor' => 'boolean',
    ];

    public function inwardCase(): BelongsTo
    {
        return $this->belongsTo(InwardCase::class);
    }
}
