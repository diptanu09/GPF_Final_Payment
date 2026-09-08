<?php

namespace App\Http\Controllers;

use App\Enums\CaseWorkflowStatus;
use App\Models\InwardCase;
use App\Services\Workflow\GpfWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApprovalController extends Controller
{
    public function __construct(
        protected GpfWorkflowService $workflowService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        // Queue filter based on user role
        $query = InwardCase::with(['assignedUser', 'latestCalculationRun', 'nominees']);

        if ($user->isApprover()) {
            // Sr. AO sees Checked cases (State 5) and Approved cases awaiting Signature (State 6)
            $query->whereIn('current_status', [
                CaseWorkflowStatus::CALCULATED,
                CaseWorkflowStatus::CHECKED,
                CaseWorkflowStatus::APPROVED,
                CaseWorkflowStatus::LTA_CHECKED,
                CaseWorkflowStatus::LTA_APPROVED,
            ]);
        } elseif ($user->isChecker()) {
            // AAO sees Calculated cases (State 4) and Under Verification cases (State 2)
            $query->whereIn('current_status', [
                CaseWorkflowStatus::UNDER_VERIFICATION,
                CaseWorkflowStatus::PRE_CALCULATED,
                CaseWorkflowStatus::CALCULATED,
                CaseWorkflowStatus::CHECKED,
            ]);
        } else {
            $query->whereIn('current_status', [
                CaseWorkflowStatus::DRAFT,
                CaseWorkflowStatus::UNDER_VERIFICATION,
                CaseWorkflowStatus::CALCULATED,
            ]);
        }

        $cases = $query->latest()->paginate(15)->through(fn ($c) => [
            'id' => $c->id,
            'registration_no' => $c->registration_no,
            'gpf_account' => $c->formatted_gpf_account,
            'subscriber_name' => $c->subscriber_name_cache,
            'designation' => $c->designation,
            'status_id' => $c->current_status->value,
            'status_label' => $c->current_status->label(),
            'status_badge' => $c->current_status->badgeClasses(),
            'final_amount' => $c->latestCalculationRun?->final_closing_balance ?? 0.00,
            'dlis_amount' => $c->latestCalculationRun?->dlis_amount ?? 0.00,
            'nominee_count' => $c->nominees->count(),
            'created_at' => $c->created_at->format('d M Y'),
        ]);

        return Inertia::render('Approval/Index', [
            'cases' => $cases,
            'user_role' => $user->role,
        ]);
    }

    public function check(Request $request, string $caseId): RedirectResponse
    {
        $request->validate(['remarks' => ['nullable', 'string']]);

        $this->workflowService->transition(
            $caseId,
            CaseWorkflowStatus::CHECKED,
            'CHECK_AND_VERIFY',
            $request->input('remarks', 'Calculation ledger verified and checked by AAO.'),
            $request->user(),
            $request->ip()
        );

        return back()->with('success', 'Case verified and passed to Senior Accounts Officer for approval.');
    }

    public function approve(Request $request, string $caseId): RedirectResponse
    {
        $request->validate(['remarks' => ['nullable', 'string']]);

        $this->workflowService->transition(
            $caseId,
            CaseWorkflowStatus::APPROVED,
            'APPROVE',
            $request->input('remarks', 'Statutory payment approved by Senior Accounts Officer.'),
            $request->user(),
            $request->ip()
        );

        return back()->with('success', 'Case approved successfully! Authority is ready for digital signature.');
    }

    public function revert(Request $request, string $caseId): RedirectResponse
    {
        $validated = $request->validate([
            'target_status' => ['required', 'integer'],
            'remarks' => ['required', 'string', 'min:5'],
        ]);

        $targetEnum = CaseWorkflowStatus::from($validated['target_status']);

        $this->workflowService->transition(
            $caseId,
            $targetEnum,
            'REVERT',
            "Case reverted. Reason: " . $validated['remarks'],
            $request->user(),
            $request->ip()
        );

        return back()->with('info', 'Case has been reverted with audit remarks.');
    }
}
