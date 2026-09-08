<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterestRateSlab extends Model
{
    use HasFactory;

    protected $fillable = [
        'financial_year',
        'accounting_month',
        'effective_from',
        'effective_to',
        'rate_percentage',
        'year_desc',
        'notification_reference',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'accounting_month' => 'integer',
        'rate_percentage' => 'decimal:4',
    ];

    /**
     * Scope to find the applicable rate for a specific date or calendar month
     */
    public static function getRateForDate(string $date): ?float
    {
        $slab = self::where('effective_from', '<=', $date)
            ->where('effective_to', '>=', $date)
            ->first();

        return $slab ? (float) $slab->rate_percentage : null;
    }
}
