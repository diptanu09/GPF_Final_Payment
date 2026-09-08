<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalculationMonthlyBreakdown extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'calculation_run_id',
        'financial_year',
        'calendar_month',
        'pay_slip_date',
        'interest_date',
        'accounting_month',
        'opening_balance',
        'deposit',
        'withdrawal',
        'rate_of_interest',
        'interest_on_deposit',
        'progressive_balance',
        'actual_interest',
        'delay_interest',
        'is_cut_month',
        'is_adjustment',
        'voucher_no',
        'abstract_no',
    ];

    protected $casts = [
        'pay_slip_date' => 'date',
        'interest_date' => 'date',
        'accounting_month' => 'integer',
        'opening_balance' => 'decimal:2',
        'deposit' => 'decimal:2',
        'withdrawal' => 'decimal:2',
        'rate_of_interest' => 'decimal:4',
        'interest_on_deposit' => 'boolean',
        'progressive_balance' => 'decimal:2',
        'actual_interest' => 'decimal:2',
        'delay_interest' => 'decimal:2',
        'is_cut_month' => 'boolean',
        'is_adjustment' => 'boolean',
    ];

    public function calculationRun(): BelongsTo
    {
        return $this->belongsTo(CalculationRun::class);
    }
}
