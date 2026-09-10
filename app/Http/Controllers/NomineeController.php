<?php

namespace App\Http\Controllers;

use App\Models\CaseNominee;
use App\Models\InwardCase;
use App\Services\Calculation\GpfCalculationEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NomineeController extends Controller
{
    public function __construct(
        protected GpfCalculationEngine $calculationEngine,
    ) {}

    public function index(string $caseId): Response
    {
        $case = InwardCase::with(['nominees', 'latestCalculationRun'])->findOrFail($caseId);

        return Inertia::render('Nominees/NomineeMatrix', [
            'case_data' => $case,
            'nominees' => $case->nominees,
            'final_amount' => (float) ($case->latestCalculationRun?->final_closing_balance ?? 0.00),
        ]);
    }

    public function sync(Request $request, string $caseId): RedirectResponse
    {
        $case = InwardCase::findOrFail($caseId);

        $validated = $request->validate([
            'nominees' => ['required', 'array', 'min:1'],
            'nominees.*.nominee_name' => ['required', 'string', 'max:255'],
            'nominees.*.beneficiary_code' => ['nullable', 'string', 'max:50'],
            'nominees.*.relationship' => ['required', 'string'],
            'nominees.*.share_percentage' => ['required', 'numeric', 'min:0.01', 'max:100.00'],
            'nominees.*.bank_account_no' => ['nullable', 'string'],
            'nominees.*.bank_ifsc' => ['nullable', 'string'],
            'nominees.*.bank_name' => ['nullable', 'string'],
            'nominees.*.guardian_name' => ['nullable', 'string'],
            'nominees.*.is_minor' => ['nullable', 'boolean'],
            'nominees.*.address' => ['nullable', 'string'],
        ]);

        // Enforce 100.00% total sum
        $totalPercentage = collect($validated['nominees'])->sum('share_percentage');
        if (abs($totalPercentage - 100.00) > 0.01) {
            return back()->withErrors([
                'nominees' => "The sum of all nominee shares must equal exactly 100.00%. Current sum: {$totalPercentage}%",
            ]);
        }

        // Delete old nominees and recreate
        $case->nominees()->delete();

        foreach ($validated['nominees'] as $nomineeData) {
            $case->nominees()->create($nomineeData);
        }

        // Re-partition amounts if calculation run exists
        $finalPayable = (float) ($case->latestCalculationRun?->final_closing_balance ?? 0.00);
        if ($finalPayable > 0) {
            $this->calculationEngine->partitionNomineeShares($case, $finalPayable);
        }

        return back()->with('success', 'Nominee share matrix updated successfully!');
    }
}
