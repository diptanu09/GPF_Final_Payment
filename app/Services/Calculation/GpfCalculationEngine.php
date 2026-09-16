<?php

namespace App\Services\Calculation;

use App\Enums\CaseType;
use App\Models\CalculationMonthlyBreakdown;
use App\Models\CalculationRun;
use App\Models\InwardCase;
use App\Models\InterestRateSlab;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GpfCalculationEngine
{
    protected CutoffRuleResolver $cutoffResolver;
    protected int $scale = 4;

    public function __construct(CutoffRuleResolver $cutoffResolver)
    {
        $this->cutoffResolver = $cutoffResolver;
        bcscale($this->scale);
    }

    /**
     * Execute full calculation run for an InwardCase
     */
    public function calculate(InwardCase $case, array $ledgerEntries, ?int $userId = null): CalculationRun
    {
        return DB::transaction(function () use ($case, $ledgerEntries, $userId) {
            $cutoffDate = $this->cutoffResolver->resolveCutoffDate($case);
            $openingFinYear = $case->created_at->format('Y') . '-' . ($case->created_at->format('Y') + 1);

            // Extract opening balance from parameters or first ledger item
            $openingBal = '0.0000';
            if (isset($ledgerEntries['opening_balance'])) {
                $openingBal = (string) $ledgerEntries['opening_balance'];
                $openingFinYear = $ledgerEntries['opening_fin_year'] ?? $openingFinYear;
            }

            // Create CalculationRun record
            $run = CalculationRun::create([
                'inward_case_id' => $case->id,
                'opening_fin_year' => $openingFinYear,
                'opening_balance_amount' => round((float) $openingBal, 2),
                'cutoff_date' => $cutoffDate->toDateString(),
                'interest_allowed_upto' => $cutoffDate->toDateString(),
                'computed_by' => $userId,
                'dlis_admissible' => in_array((int) $case->pension_type_id, config('gpf.dlis.eligible_pension_types', [2, 7])) 
                    || $case->case_type === \App\Enums\CaseType::FAMILY_PENSION 
                    || $case->case_type === \App\Enums\CaseType::DEATH_IN_SERVICE,
            ]);

            $delayJustification = $ledgerEntries['delay_justification'] ?? $case->delay_justification ?? null;
            $months = collect($ledgerEntries['monthly_entries'] ?? []);
            $ledgerResult = $this->processMonthlyLedger($run, $months, $openingBal, $cutoffDate, $case, $delayJustification);
            $processedBreakdowns = $ledgerResult['breakdowns'];
            $delayPeriodStarted = $ledgerResult['delay_period_started'];
            $delayOpeningBal = $ledgerResult['delay_opening_bal'];
            $cutMonthYearMonth = $ledgerResult['cut_month_year_month'];
            $delayMonthsCount = $ledgerResult['delay_months_count'];
            $hasExceededDelayCap = $ledgerResult['has_exceeded_delay_cap'];

            // Compute cumulative totals across all months
            $totalSub = '0.0000';
            $totalRef = '0.0000';
            $totalWith = '0.0000';
            $actualInt = '0.0000';
            $delayInt = '0.0000';
            $excessDep = '0.0000';

            foreach ($processedBreakdowns as $row) {
                if ($row->interest_on_deposit) {
                    $totalSub = bcadd($totalSub, (string) $row->deposit, $this->scale);
                } else {
                    $excessDep = bcadd($excessDep, (string) $row->deposit, $this->scale);
                }
                $totalWith = bcadd($totalWith, (string) $row->withdrawal, $this->scale);
                $actualInt = bcadd($actualInt, (string) $row->actual_interest, $this->scale);
                $delayInt = bcadd($delayInt, (string) $row->delay_interest, $this->scale);
            }

            $totalInt = bcadd($actualInt, $delayInt, $this->scale);
            
            // Final balance computation (aligned with legacy calculate.php lines 220-265):
            // When a delay period exists, closing balance equals:
            // delayOpeningBal + delayDeposits + excessDep - delayWithdrawals + delayInterest
            // Otherwise, openingBal + totalSub + excessDep - totalWith + actualInt
            if ($delayPeriodStarted) {
                $delayWithdrawals = '0.0000';
                $delayDeposits = '0.0000';
                foreach ($processedBreakdowns as $row) {
                    if (Carbon::parse($row->pay_slip_date)->format('Y-m') > $cutMonthYearMonth) {
                        if ($row->interest_on_deposit) {
                            $delayDeposits = bcadd($delayDeposits, (string) $row->deposit, $this->scale);
                        }
                        $delayWithdrawals = bcadd($delayWithdrawals, (string) $row->withdrawal, $this->scale);
                    }
                }
                $finalBal = bcadd($delayOpeningBal, $delayDeposits, $this->scale);
                $finalBal = bcadd($finalBal, $excessDep, $this->scale);
                $finalBal = bcsub($finalBal, $delayWithdrawals, $this->scale);
                $finalBal = bcadd($finalBal, $delayInt, $this->scale);
            } else {
                $finalBal = bcadd($openingBal, $totalSub, $this->scale);
                $finalBal = bcadd($finalBal, $excessDep, $this->scale);
                $finalBal = bcsub($finalBal, $totalWith, $this->scale);
                $finalBal = bcadd($finalBal, $actualInt, $this->scale);
            }

            // DLIS Calculation
            $dlisAmount = 0.00;
            if ($run->dlis_admissible) {
                $dlisAmount = $this->calculateDlisAmount($processedBreakdowns, $cutoffDate);
            }

            // Update CalculationRun summary
            $run->update([
                'total_subscriptions' => round((float) $totalSub, 2),
                'total_refunds' => round((float) $totalRef, 2),
                'total_withdrawals' => round((float) $totalWith, 2),
                'excess_deposits' => round((float) $excessDep, 2),
                'actual_interest_computed' => round((float) $actualInt, 2),
                'delayed_interest_computed' => round((float) $delayInt, 2),
                'total_interest_computed' => round((float) $totalInt, 2),
                'dlis_amount' => $dlisAmount,
                'final_closing_balance' => round((float) $finalBal, 0), // Statutory nearest whole rupee
                'delay_justification' => $delayJustification,
                'delay_months_count' => $delayMonthsCount,
                'has_exceeded_delay_cap' => $hasExceededDelayCap,
            ]);

            // Persist delay justification to case if provided and changed
            if (!empty($delayJustification) && $case->delay_justification !== $delayJustification) {
                $case->update([
                    'delay_justification' => $delayJustification,
                ]);
            }

            // If nominees exist, partition the pro-rata shares
            $this->partitionNomineeShares($case, (float) $run->final_closing_balance);

            return $run->fresh(['monthlyBreakdowns']);
        });
    }

    /**
     * Process month-by-month compounding and annual capitalization
     * following statutory AG Tripura GPF progressive compounding rules.
     * Aligned with legacy calculate.php and calculation_sheet.php.
     *
     * @return array{breakdowns: Collection, delay_period_started: bool, delay_opening_bal: string, cut_month_year_month: string, delay_months_count: int, has_exceeded_delay_cap: bool}
     */
    protected function processMonthlyLedger(CalculationRun $run, Collection $entries, string $openingBal, Carbon $cutoffDate, ?InwardCase $case = null, ?string $delayJustification = null): array
    {
        $breakdowns = collect();
        $currentOpening = $openingBal;
        $runningProgressive = '0.0000';
        $yearlyAccruedInterest = '0.0000';
        $yearlyDeposits = '0.0000';
        $yearlyWithdrawals = '0.0000';
        $currentFinYear = null;
        $delayPeriodStarted = false;
        $delayOpeningBal = '0.0000';
        $delayMonthCount = 0;

        // Statutory Rule: If case type is superannuation and subscriber retired on the month-end,
        // subscriber completed full service for that month and is entitled to full interest.
        $isSuperannuation = false;
        $isMonthEndRetirement = false;
        $eventYM = null;
        if ($case) {
            $isSuperannuation = ($case->case_type === CaseType::NORMAL_SUPERANNUATION || $case->pension_type_id === '1');
            if ($case->event_date) {
                $eDate = Carbon::parse($case->event_date);
                $isMonthEndRetirement = ($eDate->day === $eDate->daysInMonth);
                $eventYM = $eDate->format('Y-m');
            }
        }

        // Detect explicit Cut Month in the ledger (matching legacy GPF_ACCOUNT_CALCULATION WHERE CUT_MONTH='Y')
        $cutMonthEntry = $entries->first(fn ($item) => !empty($item['is_cut_month']));
        $cutMonthYearMonth = $cutMonthEntry 
            ? Carbon::parse($cutMonthEntry['pay_slip_date'])->format('Y-m') 
            : $cutoffDate->format('Y-m');

        // Group entries by financial year to ensure correct annual capitalization
        foreach ($entries as $index => $item) {
            $paySlipDate = Carbon::parse($item['pay_slip_date'] ?? Carbon::now());
            $calMonth = $paySlipDate->format('Y-m');
            $accountingMonth = (int) ($item['accounting_month'] ?? (($paySlipDate->month >= 4) ? $paySlipDate->month - 3 : $paySlipDate->month + 9));
            $finYear = $item['financial_year'] ?? (($paySlipDate->month >= 4) ? $paySlipDate->year . '-' . ($paySlipDate->year + 1) : ($paySlipDate->year - 1) . '-' . $paySlipDate->year);

            $deposit = (string) ($item['deposit'] ?? 0.00);
            $withdrawal = (string) ($item['withdrawal'] ?? 0.00);
            $intOnDeposit = (bool) ($item['interest_on_deposit'] ?? true);
            $isCutMonth = $cutMonthEntry 
                ? ($calMonth === $cutMonthYearMonth && !empty($item['is_cut_month']))
                : ($calMonth === $cutMonthYearMonth);
            $isAdj = (bool) ($item['is_adjustment'] ?? false);
            $isDelayed = ($calMonth > $cutMonthYearMonth);

            // In superannuation, if subscriber retired on the month-end, they served the full month
            // and are entitled to interest for that retirement month.
            $isEventMonth = ($eventYM !== null && $calMonth === $eventYM);
            $earnsInterestInCutMonth = ($isCutMonth && $isSuperannuation && $isMonthEndRetirement && $isEventMonth);

            // Determine rate of interest for this month
            $rate = (string) ($item['rate_of_interest'] ?? InterestRateSlab::getRateForDate($paySlipDate->toDateString()) ?? config('gpf.interest.default_rate', 7.1000));

            // If transitioning to a new Financial Year (normal period), capitalize previous year's interest & net transactions
            if (!$isDelayed && $currentFinYear !== null && $finYear !== $currentFinYear) {
                $annualInterest = (string) round((float) $yearlyAccruedInterest);
                $currentOpening = bcadd($currentOpening, $yearlyDeposits, $this->scale);
                $currentOpening = bcsub($currentOpening, $yearlyWithdrawals, $this->scale);
                $currentOpening = bcadd($currentOpening, $annualInterest, $this->scale);

                // Reset yearly accumulators
                $yearlyAccruedInterest = '0.0000';
                $yearlyDeposits = '0.0000';
                $yearlyWithdrawals = '0.0000';
                $runningProgressive = '0.0000';
            }
            $currentFinYear = $finYear;

            // When entering delay period, capture closing balance up to cut month as delay opening balance
            if ($isDelayed && !$delayPeriodStarted) {
                $delayPeriodStarted = true;
                $annualInterest = (string) round((float) $yearlyAccruedInterest);
                $delayOpeningBal = bcadd($currentOpening, $yearlyDeposits, $this->scale);
                $delayOpeningBal = bcsub($delayOpeningBal, $yearlyWithdrawals, $this->scale);
                $delayOpeningBal = bcadd($delayOpeningBal, $annualInterest, $this->scale);
                $currentOpening = $delayOpeningBal;
                $runningProgressive = '0.0000';
                $delayMonthCount = 0;
            }

            $effectiveDeposit = $intOnDeposit ? $deposit : '0.0000';

            // Calculate Progressive Balance & Row-Level Opening Balance
            $rowOpeningBalance = '0.0000';

            if ($isCutMonth && !$earnsInterestInCutMonth) {
                // Cut month with interest suppression (e.g. mid-month retirement or standard cut month):
                // progressive = 0, interest = 0.
                // However, following legacy calculate.php line 120-126, transactions in cut month
                // are credited/debited into the accumulated principal carried into the delay period.
                $progressive = '0.0000';
                $actualInt = '0.0000';
                $delayInt = '0.0000';
                $runningProgressive = '0.0000';

                $yearlyDeposits = bcadd($yearlyDeposits, $effectiveDeposit, $this->scale);
                $yearlyWithdrawals = bcadd($yearlyWithdrawals, $withdrawal, $this->scale);

                $rowOpeningBalance = ($accountingMonth === 1) ? $currentOpening : '0.0000';
            } elseif ($isDelayed) {
                // Delayed period (after cutoff): simple monthly interest without annual compounding
                $delayMonthCount++;
                if ($delayMonthCount === 1) {
                    $runningProgressive = $delayOpeningBal;
                    $rowOpeningBalance = $delayOpeningBal;
                } else {
                    $rowOpeningBalance = '0.0000';
                }

                $monthStep = bcsub($effectiveDeposit, $withdrawal, $this->scale);
                $runningProgressive = bcadd($runningProgressive, $monthStep, $this->scale);
                $progressive = $runningProgressive;

                // Statutory 6-Month Cap Rule (Central GPF Rule 11(4)):
                // Delay interest is capped at maximum 6 months.
                // If delay reaches Month 7 or beyond (7+), it requires an official Delay Justification / Remarks
                // approved by the Sr. Accounts Officer. Without justification, interest is suppressed to 0.00.
                $hasValidJustification = !empty(trim((string) $delayJustification));
                if ($delayMonthCount > 6 && !$hasValidJustification) {
                    $actualInt = '0.0000';
                    $delayInt = '0.0000';
                } else {
                    $numerator = bcmul($progressive, $rate, $this->scale);
                    $monthlyInt = bcdiv($numerator, '1200', $this->scale);
                    $actualInt = '0.0000';
                    $delayInt = (string) round((float) $monthlyInt, 2);
                }
            } else {
                // Normal month within active FY OR Superannuation retirement month where subscriber retired on month-end
                $rowOpeningBalance = ($accountingMonth === 1) ? $currentOpening : '0.0000';

                $monthStep = bcsub($effectiveDeposit, $withdrawal, $this->scale);
                if ($accountingMonth === 1 || bccomp($runningProgressive, '0.0000', $this->scale) === 0) {
                    $runningProgressive = bcadd($currentOpening, $monthStep, $this->scale);
                } else {
                    $runningProgressive = bcadd($runningProgressive, $monthStep, $this->scale);
                }
                $progressive = $runningProgressive;

                $monthlyInt = '0.0000';
                if (bccomp($progressive, '0.0000', $this->scale) > 0) {
                    $numerator = bcmul($progressive, $rate, $this->scale);
                    $monthlyInt = bcdiv($numerator, '1200', $this->scale);
                }

                $actualInt = (string) round((float) $monthlyInt, 2);
                $delayInt = '0.0000';

                $yearlyAccruedInterest = bcadd($yearlyAccruedInterest, $actualInt, $this->scale);
                $yearlyDeposits = bcadd($yearlyDeposits, $effectiveDeposit, $this->scale);
                $yearlyWithdrawals = bcadd($yearlyWithdrawals, $withdrawal, $this->scale);
            }

            $breakdown = CalculationMonthlyBreakdown::create([
                'calculation_run_id' => $run->id,
                'financial_year' => $finYear,
                'calendar_month' => $calMonth,
                'pay_slip_date' => $paySlipDate->toDateString(),
                'interest_date' => isset($item['interest_date']) ? Carbon::parse($item['interest_date'])->toDateString() : null,
                'accounting_month' => $accountingMonth,
                'opening_balance' => round((float) $rowOpeningBalance, 2),
                'deposit' => round((float) $deposit, 2),
                'withdrawal' => round((float) $withdrawal, 2),
                'rate_of_interest' => round((float) $rate, 4),
                'interest_on_deposit' => $intOnDeposit,
                'progressive_balance' => round((float) $progressive, 2),
                'actual_interest' => round((float) $actualInt, 2),
                'delay_interest' => round((float) $delayInt, 2),
                'is_cut_month' => $isCutMonth,
                'is_adjustment' => $isAdj,
                'voucher_no' => $item['voucher_no'] ?? null,
                'abstract_no' => $item['abstract_no'] ?? null,
            ]);

            $breakdowns->push($breakdown);
        }

        return [
            'breakdowns' => $breakdowns,
            'delay_period_started' => $delayPeriodStarted,
            'delay_opening_bal' => $delayOpeningBal,
            'cut_month_year_month' => $cutMonthYearMonth,
            'delay_months_count' => $delayMonthCount,
            'has_exceeded_delay_cap' => ($delayMonthCount > 6),
        ];
    }

    /**
     * Compute DLIS (Deposit Linked Insurance Scheme) 36-month average up to statutory cap
     * Matching legacy formula: (Sum(Progressive in 36m) + Sum(Interest in 36m)) / 36
     * Rounded to nearest whole integer rupee per statutory order.
     */
    protected function calculateDlisAmount(Collection $breakdowns, Carbon $cutoffDate): float
    {
        $cap = (float) config('gpf.dlis.max_amount', 60000);
        $eligibleMonths = $breakdowns->filter(function ($row) use ($cutoffDate) {
            return Carbon::parse($row->pay_slip_date)->lessThanOrEqualTo($cutoffDate) && !$row->is_cut_month;
        })->take(-36);

        if ($eligibleMonths->isEmpty()) {
            return 0.00;
        }

        $sumProgressive = $eligibleMonths->sum(fn ($r) => (float) $r->progressive_balance);
        $sumInterest = $eligibleMonths->sum(fn ($r) => (float) $r->actual_interest);
        $average = ($sumProgressive + $sumInterest) / min(36, max(1, $eligibleMonths->count()));

        return min($cap, (float) round($average, 0));
    }

    /**
     * Allocate final payment according to exact nominee share percentages,
     * ensuring that remaining rounding paisa is reconciled onto the primary nominee.
     */
    public function partitionNomineeShares(InwardCase $case, float $totalPayable): void
    {
        $nominees = $case->nominees()->get();
        if ($nominees->isEmpty()) {
            return;
        }

        $allocatedSum = 0.00;
        $primaryNominee = $nominees->first();

        foreach ($nominees as $nominee) {
            $percentage = (float) $nominee->share_percentage;
            $share = round(($totalPayable * $percentage) / 100.0, 2);
            $nominee->update(['allocated_amount' => $share]);
            $allocatedSum += $share;
        }

        // Reconcile rounding remainder
        $remainder = round($totalPayable - $allocatedSum, 2);
        if (abs($remainder) > 0.001 && $primaryNominee) {
            $primaryNominee->update([
                'allocated_amount' => $primaryNominee->allocated_amount + $remainder
            ]);
        }
    }
}
