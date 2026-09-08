<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Authority extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'inward_case_id',
        'calculation_run_id',
        'authority_number',
        'authority_type',
        'authority_date',
        'gross_amount',
        'deductions_amount',
        'net_amount',
        'dlis_amount',
        'pdf_storage_path',
        'verification_hash',
        'digital_signature_id',
        'is_signed',
        'signed_at',
        'is_dispatched',
        'dispatched_at',
        'dispatch_barcode',
        'is_uploaded_hrms',
        'hrms_uploaded_at',
    ];

    protected $casts = [
        'authority_date' => 'date',
        'gross_amount' => 'decimal:2',
        'deductions_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'dlis_amount' => 'decimal:2',
        'is_signed' => 'boolean',
        'signed_at' => 'datetime',
        'is_dispatched' => 'boolean',
        'dispatched_at' => 'datetime',
        'is_uploaded_hrms' => 'boolean',
        'hrms_uploaded_at' => 'datetime',
    ];

    public function inwardCase(): BelongsTo
    {
        return $this->belongsTo(InwardCase::class);
    }

    public function calculationRun(): BelongsTo
    {
        return $this->belongsTo(CalculationRun::class);
    }

    public function digitalSignature(): BelongsTo
    {
        return $this->belongsTo(DigitalSignature::class);
    }
}
