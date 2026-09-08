<?php

namespace App\Services\Calculation;

use App\Enums\CaseType;
use App\Models\InwardCase;
use Carbon\Carbon;

class CutoffRuleResolver
{
    /**
     * Determine the statutory cut-off date beyond which normal GPF interest ceases.
     */
    public function resolveCutoffDate(InwardCase $case): Carbon
    {
        $eventDate = $case->event_date ? Carbon::parse($case->event_date) : Carbon::now();

        return match ($case->case_type) {
            CaseType::NORMAL_SUPERANNUATION => $eventDate->copy()->endOfMonth(),
            CaseType::DEATH_IN_SERVICE => $this->resolveDeathCutoff($case, $eventDate),
            CaseType::RESIGNATION => $eventDate->copy()->endOfMonth(),
            CaseType::LTA_SPECIAL => $case->date_of_lta ? Carbon::parse($case->date_of_lta)->endOfMonth() : $eventDate->copy()->endOfMonth(),
            default => $eventDate->copy()->endOfMonth(),
        };
    }

    /**
     * In death cases, interest is allowed up to the date of demise + authorized window (e.g. 6 months).
     */
    protected function resolveDeathCutoff(InwardCase $case, Carbon $demiseDate): Carbon
    {
        $graceMonths = config('gpf.interest.default_post_demise_grace_months', 6);
        return $demiseDate->copy()->addMonths($graceMonths)->endOfMonth();
    }

    /**
     * Determine if a given pay slip date falls in the post-retirement / delayed interest window.
     */
    public function isDelayedInterestPeriod(Carbon $paySlipDate, Carbon $cutoffDate): bool
    {
        return $paySlipDate->greaterThan($cutoffDate);
    }
}
