<?php

namespace App\Http\Controllers;

use App\Enums\CaseWorkflowStatus;
use App\Models\CalculationRun;
use App\Models\InwardCase;
use App\Models\InterestRateSlab;
use App\Services\Calculation\GpfCalculationEngine;
use App\Services\Workflow\GpfWorkflowService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalculationController extends Controller
{
    public function __construct(
        protected GpfCalculationEngine $calculationEngine,
        protected GpfWorkflowService $workflowService,
    ) {}

    public function show(string $caseId): Response
    {
        $case = InwardCase::with([
            'latestCalculationRun.monthlyBreakdowns',
            'nominees',
        ])->findOrFail($caseId);

        $latestRun = $case->latestCalculationRun;

        // If no calculation run yet, prepare default sample entries from event date / opening balance
        $monthlyLedger = [];
        $openingBalance = 0.00;
        $openingFinYear = '2023-2024';

        if ($latestRun) {
            $openingBalance = (float) $latestRun->opening_balance_amount;
            $openingFinYear = $latestRun->opening_fin_year;
            $monthlyLedger = $latestRun->monthlyBreakdowns->map(fn ($r) => [
                'id' => $r->id,
                'financial_year' => $r->financial_year,
                'calendar_month' => $r->calendar_month,
                'pay_slip_date' => $r->pay_slip_date->format('Y-m-d'),
                'accounting_month' => $r->accounting_month,
                'opening_balance' => (float) $r->opening_balance,
                'deposit' => (float) $r->deposit,
                'withdrawal' => (float) $r->withdrawal,
                'rate_of_interest' => (float) $r->rate_of_interest,
                'interest_on_deposit' => $r->interest_on_deposit,
                'progressive_balance' => (float) $r->progressive_balance,
                'actual_interest' => (float) $r->actual_interest,
                'delay_interest' => (float) $r->delay_interest,
                'is_cut_month' => $r->is_cut_month,
                'is_adjustment' => $r->is_adjustment,
            ]);
        } else {
            // Seed a default 12-month ledger for interactive preview
            $openingBalance = 450000.00;
            $startDate = Carbon::create(2023, 4, 1);
            for ($i = 0; $i < 12; $i++) {
                $mDate = (clone $startDate)->addMonths($i);
                $rate = InterestRateSlab::getRateForDate($mDate->toDateString()) ?? 7.1000;
                $monthlyLedger[] = [
                    'financial_year' => '2023-2024',
                    'calendar_month' => $mDate->format('Y-m'),
                    'pay_slip_date' => $mDate->format('Y-m-d'),
                    'accounting_month' => $i + 1,
                    'opening_balance' => $openingBalance,
                    'deposit' => 15000.00,
                    'withdrawal' => 0.00,
                    'rate_of_interest' => $rate,
                    'interest_on_deposit' => true,
                    'progressive_balance' => $openingBalance + 15000,
                    'actual_interest' => round((($openingBalance + 15000) * $rate) / 1200, 2),
                    'delay_interest' => 0.00,
                    'is_cut_month' => false,
                    'is_adjustment' => false,
                ];
            }
        }

        return Inertia::render('Calculation/CalculationSheet', [
            'case_data' => $case,
            'calculation_run' => $latestRun,
            'opening_balance' => $openingBalance,
            'opening_fin_year' => $openingFinYear,
            'monthly_ledger' => $monthlyLedger,
        ]);
    }

    public function store(Request $request, string $caseId): RedirectResponse
    {
        $case = InwardCase::findOrFail($caseId);

        $validated = $request->validate([
            'opening_balance' => ['required', 'numeric'],
            'opening_fin_year' => ['required', 'string'],
            'monthly_entries' => ['required', 'array', 'min:1'],
            'monthly_entries.*.pay_slip_date' => ['required', 'date'],
            'monthly_entries.*.deposit' => ['required', 'numeric'],
            'monthly_entries.*.withdrawal' => ['required', 'numeric'],
            'monthly_entries.*.rate_of_interest' => ['nullable', 'numeric'],
            'monthly_entries.*.interest_on_deposit' => ['nullable', 'boolean'],
            'monthly_entries.*.is_cut_month' => ['nullable', 'boolean'],
            'monthly_entries.*.is_adjustment' => ['nullable', 'boolean'],
        ]);

        $run = $this->calculationEngine->calculate($case, $validated, $request->user()->id);

        // Advance status to CALCULATED (State 4)
        if ($case->current_status->value < CaseWorkflowStatus::CALCULATED->value) {
            $this->workflowService->transition(
                $case->id,
                CaseWorkflowStatus::CALCULATED,
                'CALCULATE',
                "Calculation executed with net payable closing balance ₹" . number_format($run->final_closing_balance, 2),
                $request->user(),
                $request->ip()
            );
        }

        return back()->with('success', "Calculation completed successfully! Net Payable: ₹" . number_format($run->final_closing_balance));
    }
}
