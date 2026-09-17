<?php

namespace App\Http\Controllers;

use App\Enums\CaseWorkflowStatus;
use App\Models\Authority;
use App\Models\InwardCase;
use App\Models\WorkflowHistory;
use App\Services\Integration\OracleMasterBridge;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LettersController extends Controller
{
    public function __construct(
        protected OracleMasterBridge $oracleBridge
    ) {}

    /**
     * Dashboard / Index for all Statutory Letters & Notices
     */
    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $type = $request->query('type'); // 'all', 'authorized', 'minus_balance', 'draft'

        $query = InwardCase::with(['latestCalculationRun', 'latestAuthority', 'assignedUser']);
        $likeOp = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        if ($search) {
            $query->where(function ($q) use ($search, $likeOp) {
                $q->where('registration_no', $likeOp, "%{$search}%")
                  ->orWhere('subscriber_name_cache', $likeOp, "%{$search}%")
                  ->orWhere('account_no', $likeOp, "%{$search}%")
                  ->orWhere('employee_code', $likeOp, "%{$search}%");
            });
        }

        if ($type === 'minus_balance') {
            $query->where('current_status', CaseWorkflowStatus::MINUS_BALANCE)
                  ->orWhereHas('latestCalculationRun', fn($q) => $q->where('final_closing_balance', '<', 0));
        } elseif ($type === 'authorized') {
            $query->whereIn('current_status', [
                CaseWorkflowStatus::AUTHORIZED,
                CaseWorkflowStatus::HRMS_SYNCED,
                CaseWorkflowStatus::DISPATCHED,
            ]);
        }

        $cases = $query->latest()->paginate(15)->withQueryString()->through(fn ($c) => [
            'id' => $c->id,
            'registration_no' => $c->registration_no,
            'gpf_account' => $c->formatted_gpf_account,
            'subscriber_name' => $c->subscriber_name_cache,
            'designation' => $c->designation,
            'status_label' => $c->current_status->label(),
            'status_badge' => $c->current_status->badgeClasses(),
            'current_status' => $c->current_status->value,
            'is_minus_balance' => (float)($c->latestCalculationRun?->final_closing_balance ?? 0) < 0,
            'net_amount' => (float)($c->latestCalculationRun?->final_closing_balance ?? 0),
            'has_authority' => (bool)$c->latestAuthority,
            'authority_no' => $c->latestAuthority?->authority_number,
            'amount_recovered' => (float)($c->amount_recovered ?? 0),
            'created_at' => $c->created_at->format('d M Y'),
        ]);

        return Inertia::render('Letters/Index', [
            'cases' => $cases,
            'filters' => [
                'search' => $search,
                'type' => $type,
            ],
        ]);
    }

    /**
     * Official Input Sheet Report
     */
    public function inputSheet(Request $request, string $caseId)
    {
        $case = InwardCase::findOrFail($caseId);
        $run = $case->latestCalculationRun;

        // Fetch missing credits text
        $missingCredits = $this->oracleBridge->getMissingCredits($case->series_code, $case->account_no);
        $missingCreditsText = count($missingCredits) > 0 
            ? implode(', ', array_map(fn($m) => date('M-Y', strtotime($m['slip_date'] ?? $m['SLIP_DATE'] ?? '')), $missingCredits))
            : 'Nil';

        $sigFirst = $request->query('sig_f', 'Dealing Assistant');
        $sigSecond = $request->query('sig_s', 'Asstt. Accounts Officer');
        $sigThird = $request->query('sig_t', 'Sr. Accounts Officer / Fund');

        return view('pdf.input_sheet', [
            'case' => $case,
            'run' => $run,
            'missing_credits_text' => $missingCreditsText,
            'sig_first' => $sigFirst,
            'sig_second' => $sigSecond,
            'sig_third' => $sigThird,
        ]);
    }

    /**
     * Annexure 5.24 Intimation Letter to Subscriber
     */
    public function intimationLetter(Request $request, string $caseId)
    {
        $case = InwardCase::findOrFail($caseId);
        $run = $case->latestCalculationRun;
        $authority = $case->latestAuthority;

        $missingCredits = $this->oracleBridge->getMissingCredits($case->series_code, $case->account_no);
        $missingCreditsText = count($missingCredits) > 0 
            ? implode(', ', array_map(fn($m) => date('M-Y', strtotime($m['slip_date'] ?? $m['SLIP_DATE'] ?? '')), $missingCredits))
            : 'Nil';

        $signatureTitle = $request->query('signature', 'Accounts Officer / Fund (FP)');

        return view('pdf.intimation_letter', [
            'case' => $case,
            'run' => $run,
            'authority' => $authority,
            'missing_credits_text' => $missingCreditsText,
            'signature_title' => $signatureTitle,
        ]);
    }

    /**
     * Corrigendum Order Generator & Print
     */
    public function corrigendum(Request $request, string $caseId)
    {
        $case = InwardCase::findOrFail($caseId);
        $run = $case->latestCalculationRun;
        $authority = $case->latestAuthority;

        $matter = $request->input('matter');
        $copyTo = $request->input('copy_to');
        $signature = $request->input('signature', 'Accounts Officer / Fund (FP)');

        return view('pdf.corrigendum_letter', [
            'case' => $case,
            'run' => $run,
            'authority' => $authority,
            'corrigendum_matter' => $matter,
            'copy_to' => $copyTo,
            'signature_title' => $signature,
        ]);
    }

    /**
     * Revalidation Order Generator & Print
     */
    public function revalidation(Request $request, string $caseId)
    {
        $case = InwardCase::findOrFail($caseId);
        $run = $case->latestCalculationRun;
        $authority = $case->latestAuthority;

        $matter = $request->input('matter');
        $copyTo = $request->input('copy_to');
        $signature = $request->input('signature', 'Accounts Officer / Fund (FP)');

        return view('pdf.revalidation_letter', [
            'case' => $case,
            'run' => $run,
            'authority' => $authority,
            'revalidation_matter' => $matter,
            'copy_to' => $copyTo,
            'signature_title' => $signature,
        ]);
    }

    /**
     * Objection / Defect Return Memorandum to DDO
     */
    public function objection(Request $request, string $caseId)
    {
        $case = InwardCase::findOrFail($caseId);
        $run = $case->latestCalculationRun;

        $selectedPoints = $request->input('points');
        $customRemarks = $request->input('custom_remarks');
        $signature = $request->input('signature', 'Accounts Officer / Fund (FP)');

        return view('pdf.objection_letter', [
            'case' => $case,
            'run' => $run,
            'objection_points' => is_array($selectedPoints) && count($selectedPoints) > 0 ? $selectedPoints : null,
            'custom_remarks' => $customRemarks,
            'signature_title' => $signature,
        ]);
    }

    /**
     * Rule 11(7) Minus Balance Notice
     */
    public function minusBalance(Request $request, string $caseId)
    {
        $case = InwardCase::findOrFail($caseId);
        $run = $case->latestCalculationRun;
        $signature = $request->query('signature', 'Accounts Officer / Fund (FP)');

        return view('pdf.minus_balance_letter', [
            'case' => $case,
            'run' => $run,
            'signature_title' => $signature,
        ]);
    }

    /**
     * Record Minus Balance Recovery (Amount + Remarks)
     */
    public function storeRecovery(Request $request, string $caseId)
    {
        $validated = $request->validate([
            'amount_recovered' => 'required|numeric|min:0',
            'remarks' => 'required|string|max:1000',
            'close_minus_balance' => 'nullable|boolean',
        ]);

        $case = InwardCase::findOrFail($caseId);
        $user = $request->user();

        $case->amount_recovered = $validated['amount_recovered'];
        $case->minus_balance_remarks = $validated['remarks'];

        if (!empty($validated['close_minus_balance'])) {
            $case->minus_balance_closed_at = now();
        }

        $case->save();

        WorkflowHistory::create([
            'inward_case_id' => $case->id,
            'performed_by' => $user->id,
            'from_status' => $case->current_status,
            'to_status' => $case->current_status,
            'action_type' => 'MINUS_BALANCE_RECOVERY',
            'remarks' => "Recovered ₹ {$validated['amount_recovered']}. Remarks: {$validated['remarks']}" . (!empty($validated['close_minus_balance']) ? ' (Minus balance closed)' : ''),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', 'Minus balance recovery updated successfully.');
    }
}
