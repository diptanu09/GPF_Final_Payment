<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalculationRun extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'inward_case_id',
        'opening_fin_year',
        'opening_balance_amount',
        'total_subscriptions',
        'total_refunds',
        'total_withdrawals',
        'excess_deposits',
        'missing_credits_total',
        'missing_debits_total',
        'actual_interest_computed',
        'delayed_interest_computed',
        'total_interest_computed',
        'dlis_admissible',
        'dlis_amount',
        'final_closing_balance',
        'cutoff_date',
        'interest_allowed_upto',
        'computed_by',
        'checked_by',
        'approved_by',
        'is_locked',
        'remarks',
    ];

    protected $casts = [
        'opening_balance_amount' => 'decimal:2',
        'total_subscriptions' => 'decimal:2',
        'total_refunds' => 'decimal:2',
        'total_withdrawals' => 'decimal:2',
        'excess_deposits' => 'decimal:2',
        'missing_credits_total' => 'decimal:2',
        'missing_debits_total' => 'decimal:2',
        'actual_interest_computed' => 'decimal:2',
        'delayed_interest_computed' => 'decimal:2',
        'total_interest_computed' => 'decimal:2',
        'dlis_admissible' => 'boolean',
        'dlis_amount' => 'decimal:2',
        'final_closing_balance' => 'decimal:2',
        'cutoff_date' => 'date',
        'interest_allowed_upto' => 'date',
        'is_locked' => 'boolean',
    ];

    public function inwardCase(): BelongsTo
    {
        return $this->belongsTo(InwardCase::class);
    }

    public function monthlyBreakdowns(): HasMany
    {
        return $this->hasMany(CalculationMonthlyBreakdown::class)->orderBy('pay_slip_date', 'asc');
    }

    public function computedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'computed_by');
    }

    public function checkedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
