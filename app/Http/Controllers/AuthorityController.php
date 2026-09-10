<?php

namespace App\Http\Controllers;

use App\Enums\CaseWorkflowStatus;
use App\Models\Authority;
use App\Models\InwardCase;
use App\Services\DigitalSignature\PkiSignatureVerifier;
use App\Services\Workflow\GpfWorkflowService;
use App\Services\Format\IndianCurrencyFormatter;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuthorityController extends Controller
{
    public function __construct(
        protected PkiSignatureVerifier $pkiVerifier,
        protected GpfWorkflowService $workflowService,
    ) {}

    public function index(): Response
    {
        $authorities = Authority::with(['inwardCase.assignedUser', 'digitalSignature'])
            ->latest()
            ->paginate(15)
            ->through(fn ($a) => [
                'id' => $a->id,
                'authority_number' => $a->authority_number,
                'authority_type' => $a->authority_type,
                'authority_date' => $a->authority_date->format('d M Y'),
                'registration_no' => $a->inwardCase->registration_no,
                'gpf_account' => $a->inwardCase->formatted_gpf_account,
                'subscriber_name' => $a->inwardCase->subscriber_name_cache,
                'net_amount' => (float) $a->net_amount,
                'dlis_amount' => (float) $a->dlis_amount,
                'is_signed' => $a->is_signed,
                'signed_by' => $a->digitalSignature?->signatory_name,
                'signed_at' => $a->signed_at?->format('d M Y, h:i A'),
                'is_uploaded_hrms' => $a->is_uploaded_hrms,
                'is_dispatched' => $a->is_dispatched,
            ]);

        return Inertia::render('Authority/Index', [
            'authorities' => $authorities,
        ]);
    }

    public function generate(string $caseId): RedirectResponse
    {
        $case = InwardCase::with(['latestCalculationRun'])->findOrFail($caseId);
        $run = $case->latestCalculationRun;

        if (!$run) {
            return back()->withErrors(['error' => 'Cannot generate authority without a verified calculation run.']);
        }

        $section = $case->section ?: 'FUND-I';
        $pensionCode = ($case->case_type?->value === 'FAM' || (string)$case->pension_type_id === '2') ? 'FAM' : ($case->case_type?->value === 'L' ? 'LTA' : 'SUP');
        $openingFinYear = $run->opening_fin_year ?: '2023-2024';
        $authType = $case->case_type?->value === 'L' ? 'LTA' : 'FP';
        
        // Exact AG Tripura statutory memo format: No. SECTION / TYPE / PENSION_TYPE / OPENING_FIN_YEAR / REGD_NO /
        $authNo = "No. {$section} / {$authType} / {$pensionCode} / {$openingFinYear} / {$case->registration_no} /";

        $authority = Authority::updateOrCreate(
            ['inward_case_id' => $case->id],
            [
                'calculation_run_id' => $run->id,
                'authority_number' => $authNo,
                'authority_type' => $authType,
                'authority_date' => now(),
                'gross_amount' => $run->final_closing_balance,
                'deductions_amount' => 0.00,
                'net_amount' => $run->final_closing_balance,
                'dlis_amount' => (float) ($run->dlis_amount ?? 0),
            ]
        );

        return redirect()->route('authority.show', $authority->id)->with('success', "Authority certificate generated: {$authNo}");
    }

    public function show(string $id): Response
    {
        $authority = Authority::with([
            'inwardCase.nominees',
            'calculationRun.monthlyBreakdowns',
            'digitalSignature',
        ])->findOrFail($id);

        $netAmount = (float) $authority->net_amount;
        $dlisAmount = (float) $authority->dlis_amount;

        return Inertia::render('Authority/Show', [
            'authority' => $authority,
            'case_data' => $authority->inwardCase,
            'calculation' => $authority->calculationRun,
            'nominees' => $authority->inwardCase->nominees,
            'amount_in_words' => IndianCurrencyFormatter::toWords($netAmount),
            'dlis_in_words' => $dlisAmount > 0 ? IndianCurrencyFormatter::toWords($dlisAmount) : null,
        ]);
    }

    public function sign(Request $request, string $id): JsonResponse
    {
        $authority = Authority::with(['inwardCase'])->findOrFail($id);

        $validated = $request->validate([
            'signed_hash' => ['required', 'string'],
            'certificate_serial' => ['nullable', 'string'],
            'certificate_issuer' => ['nullable', 'string'],
            'certificate_valid_to' => ['nullable', 'date'],
        ]);

        $sig = $this->pkiVerifier->verifyAndRecord($authority, $validated, $request->user(), $request->ip());

        // Advance workflow status to AUTHORIZED (State 7)
        $this->workflowService->transition(
            $authority->inward_case_id,
            CaseWorkflowStatus::AUTHORIZED,
            'DIGITAL_SIGN',
            "Payment Authority digitally signed by {$sig->signatory_name} (Serial: {$sig->certificate_serial}).",
            $request->user(),
            $request->ip()
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Authority signed and authorized successfully!',
            'signature_id' => $sig->id,
            'signed_at' => $sig->signed_at->format('d M Y, h:i A'),
        ]);
    }

    public function print(string $id)
    {
        $authority = Authority::with([
            'inwardCase.nominees',
            'calculationRun.monthlyBreakdowns',
            'digitalSignature',
        ])->findOrFail($id);

        $viewName = $authority->inwardCase->case_type?->value === 'L' ? 'pdf.lta_authority_letter' : 'pdf.authority_letter';

        return view($viewName, [
            'authority' => $authority,
            'case' => $authority->inwardCase,
            'run' => $authority->calculationRun,
            'nominees' => $authority->inwardCase->nominees,
            'missing_credits_text' => 'nil',
        ]);
    }

    public function printDlis(string $id)
    {
        $authority = Authority::with([
            'inwardCase.nominees',
            'calculationRun.monthlyBreakdowns',
            'digitalSignature',
        ])->findOrFail($id);

        return view('pdf.dlis_letter', [
            'authority' => $authority,
            'case' => $authority->inwardCase,
            'run' => $authority->calculationRun,
            'nominees' => $authority->inwardCase->nominees,
        ]);
    }
}
