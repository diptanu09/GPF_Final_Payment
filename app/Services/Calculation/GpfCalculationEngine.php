<?php

namespace App\Services\Calculation;

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

            $months = collect($ledgerEntries['monthly_entries'] ?? []);
            $processedBreakdowns = $this->processMonthlyLedger($run, $months, $openingBal, $cutoffDate);

            // Compute cumulative totals
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
            
            // Final balance = Opening + Subscriptions + Excess - Withdrawals + Interest
            $finalBal = bcadd($openingBal, $totalSub, $this->scale);
            $finalBal = bcadd($finalBal, $excessDep, $this->scale);
            $finalBal = bcsub($finalBal, $totalWith, $this->scale);
            $finalBal = bcadd($finalBal, $totalInt, $this->scale);

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
            ]);

            // If nominees exist, partition the pro-rata shares
            $this->partitionNomineeShares($case, (float) $run->final_closing_balance);

            return $run->fresh(['monthlyBreakdowns']);
        });
    }

    /**
     * Process month-by-month compounding and annual capitalization
     */
    protected function processMonthlyLedger(CalculationRun $run, Collection $entries, string $openingBal, Carbon $cutoffDate): Collection
    {
        $breakdowns = collect();
        $currentOpening = $openingBal;
        $runningProgressive = '0.0000';
        $yearlyAccruedInterest = '0.0000';

        foreach ($entries as $index => $item) {
            $paySlipDate = Carbon::parse($item['pay_slip_date'] ?? Carbon::now());
            $calMonth = $paySlipDate->format('Y-m');
            $accountingMonth = (int) ($item['accounting_month'] ?? (($paySlipDate->month >= 4) ? $paySlipDate->month - 3 : $paySlipDate->month + 9));
            $finYear = $item['financial_year'] ?? (($paySlipDate->month >= 4) ? $paySlipDate->year . '-' . ($paySlipDate->year + 1) : ($paySlipDate->year - 1) . '-' . $paySlipDate->year);

            $deposit = (string) ($item['deposit'] ?? 0.00);
            $withdrawal = (string) ($item['withdrawal'] ?? 0.00);
            $intOnDeposit = (bool) ($item['interest_on_deposit'] ?? true);
            $isCutMonth = (bool) ($item['is_cut_month'] ?? false);
            $isAdj = (bool) ($item['is_adjustment'] ?? false);

            // Determine rate of interest for this month
            $rate = (string) ($item['rate_of_interest'] ?? InterestRateSlab::getRateForDate($paySlipDate->toDateString()) ?? 7.1000);

            // April (Month 1): If starting a new FY and not first record, add previous year's interest to principal
            if ($accountingMonth === 1 && $index > 0) {
                $currentOpening = bcadd($currentOpening, $yearlyAccruedInterest, $this->scale);
                $yearlyAccruedInterest = '0.0000';
            }

            // Calculate Progressive balance
            // Month Progressive = Prior Opening + Deposit - Withdrawal
            $effectiveDeposit = $intOnDeposit ? $deposit : '0.0000';
            $monthStep = bcsub($effectiveDeposit, $withdrawal, $this->scale);
            $progressive = bcadd($currentOpening, $monthStep, $this->scale);

            // Calculate monthly interest = (Progressive * Rate) / 1200
            $monthlyInt = '0.0000';
            $isDelayed = $paySlipDate->greaterThan($cutoffDate);

            if (!$isCutMonth && bccomp($progressive, '0.0000', $this->scale) > 0) {
                $numerator = bcmul($progressive, $rate, $this->scale);
                $monthlyInt = bcdiv($numerator, '1200', $this->scale);
            }

            $actualInt = $isDelayed ? '0.0000' : $monthlyInt;
            $delayInt = $isDelayed ? $monthlyInt : '0.0000';

            $yearlyAccruedInterest = bcadd($yearlyAccruedInterest, $actualInt, $this->scale);

            $breakdown = CalculationMonthlyBreakdown::create([
                'calculation_run_id' => $run->id,
                'financial_year' => $finYear,
                'calendar_month' => $calMonth,
                'pay_slip_date' => $paySlipDate->toDateString(),
                'interest_date' => isset($item['interest_date']) ? Carbon::parse($item['interest_date'])->toDateString() : null,
                'accounting_month' => $accountingMonth,
                'opening_balance' => round((float) $currentOpening, 2),
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

        return $breakdowns;
    }

    /**
     * Compute DLIS (Deposit Linked Insurance Scheme) 36-month average up to statutory cap
     */
    protected function calculateDlisAmount(Collection $breakdowns, Carbon $cutoffDate): float
    {
        $cap = (float) config('gpf.dlis.max_amount', 10000);
        $eligibleMonths = $breakdowns->filter(function ($row) use ($cutoffDate) {
            return Carbon::parse($row->pay_slip_date)->lessThanOrEqualTo($cutoffDate);
        })->take(-36);

        if ($eligibleMonths->isEmpty()) {
            return 0.00;
        }

        $sumProgressive = $eligibleMonths->sum(fn ($r) => (float) $r->progressive_balance);
        $average = $sumProgressive / max(1, $eligibleMonths->count());

        return min($cap, round($average, 2));
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
