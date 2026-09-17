<?php

namespace App\Http\Controllers;

use App\Enums\CaseWorkflowStatus;
use App\Models\Authority;
use App\Models\DigitalSignature;
use App\Models\InwardCase;
use App\Models\WorkflowHistory;
use App\Services\Workflow\GpfWorkflowService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CaseAdminController extends Controller
{
    public function __construct(
        protected GpfWorkflowService $workflowService
    ) {}

    /**
     * Case Governance Console View
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        if (!$user->isSuperAdmin() && !$user->isApprover()) {
            abort(403, 'Unauthorized: Access restricted to Directorate Admins and Approvers.');
        }

        $search = $request->query('search');
        $tab = $request->query('tab', 'unapprove'); // 'unapprove', 'cancel', 'signatures', 'drafts'

        $query = InwardCase::with(['latestCalculationRun', 'latestAuthority.signer', 'assignedUser']);
        $likeOp = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        if (!empty($search)) {
            $query->where(function ($q) use ($search, $likeOp) {
                $q->where('registration_no', $likeOp, "%{$search}%")
                  ->orWhere('subscriber_name_cache', $likeOp, "%{$search}%")
                  ->orWhere('account_no', $likeOp, "%{$search}%");
            });
        }

        if ($tab === 'unapprove') {
            $query->whereIn('current_status', [
                CaseWorkflowStatus::CHECKED,
                CaseWorkflowStatus::APPROVED,
                CaseWorkflowStatus::LTA_CHECKED,
                CaseWorkflowStatus::LTA_APPROVED,
            ]);
        } elseif ($tab === 'signatures') {
            $query->whereIn('current_status', [
                CaseWorkflowStatus::AUTHORIZED,
                CaseWorkflowStatus::LTA_AUTHORIZED,
                CaseWorkflowStatus::HRMS_SYNCED,
            ]);
        } elseif ($tab === 'drafts') {
            $query->whereIn('current_status', [
                CaseWorkflowStatus::DRAFT,
                CaseWorkflowStatus::UNDER_VERIFICATION,
            ]);
        } else {
            // 'cancel' tab: all non-closed/non-dispatched cases
            $query->whereNotIn('current_status', [
                CaseWorkflowStatus::DISPATCHED,
                CaseWorkflowStatus::CANCELLED,
            ]);
        }

        $cases = $query->latest()->paginate(15)->withQueryString()->through(fn ($c) => [
            'id' => $c->id,
            'registration_no' => $c->registration_no,
            'gpf_account' => $c->formatted_gpf_account,
            'subscriber_name' => $c->subscriber_name_cache,
            'designation' => $c->designation,
            'current_status' => $c->current_status->value,
            'status_label' => $c->current_status->label(),
            'status_badge' => $c->current_status->badgeClasses(),
            'net_amount' => (float)($c->latestCalculationRun?->final_closing_balance ?? 0),
            'authority_id' => $c->latestAuthority?->id,
            'authority_no' => $c->latestAuthority?->authority_number,
            'is_signed' => (bool)$c->latestAuthority?->is_signed,
            'checked_at' => $c->checked_at?->format('d M Y'),
            'approved_at' => $c->approved_at?->format('d M Y'),
            'authorized_at' => $c->authorized_at?->format('d M Y'),
            'created_at' => $c->created_at->format('d M Y'),
        ]);

        return Inertia::render('Admin/CaseAdmin/Index', [
            'cases' => $cases,
            'current_tab' => $tab,
            'filters' => [
                'search' => $search,
                'tab' => $tab,
            ],
        ]);
    }

    /**
     * Un-approve an approved or checked docket back to CALCULATED
     */
    public function unapprove(Request $request, string $caseId)
    {
        $user = $request->user();
        if (!$user->isSuperAdmin() && !$user->isApprover()) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'remarks' => 'required|string|min:5|max:1000',
        ]);

        $case = InwardCase::findOrFail($caseId);
        $fromStatus = $case->current_status;

        $case->unapproved_remarks = $validated['remarks'];
        $case->current_status = CaseWorkflowStatus::CALCULATED;
        $case->approved_at = null;
        $case->checked_at = null;
        $case->save();

        WorkflowHistory::create([
            'inward_case_id' => $case->id,
            'performed_by' => $user->id,
            'from_status' => $fromStatus,
            'to_status' => CaseWorkflowStatus::CALCULATED,
            'action_type' => 'UNAPPROVE',
            'remarks' => "Case unapproved. Reason: {$validated['remarks']}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', 'Case successfully unapproved and reverted to Calculated status.');
    }

    /**
     * Cancel case with formal remarks
     */
    public function cancelCase(Request $request, string $caseId)
    {
        $user = $request->user();
        if (!$user->isSuperAdmin() && !$user->isApprover()) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'remarks' => 'required|string|min:5|max:1000',
        ]);

        $case = InwardCase::findOrFail($caseId);
        $fromStatus = $case->current_status;

        $case->cancelled_remarks = $validated['remarks'];
        $case->current_status = CaseWorkflowStatus::CANCELLED;
        $case->cancelled_at = now();
        $case->save();

        WorkflowHistory::create([
            'inward_case_id' => $case->id,
            'performed_by' => $user->id,
            'from_status' => $fromStatus,
            'to_status' => CaseWorkflowStatus::CANCELLED,
            'action_type' => 'CANCEL',
            'remarks' => "Case cancelled. Reason: {$validated['remarks']}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', 'Case successfully marked as Cancelled.');
    }

    /**
     * Reset Digital Signature for an Authority (allows re-signing)
     */
    public function resetSignature(Request $request, string $authorityId)
    {
        $user = $request->user();
        if (!$user->isSuperAdmin() && !$user->isApprover()) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'remarks' => 'required|string|min:5|max:1000',
        ]);

        $authority = Authority::findOrFail($authorityId);
        $case = $authority->inwardCase;

        // Delete signature records
        DigitalSignature::where('authority_id', $authority->id)->delete();

        $authority->is_signed = false;
        $authority->digital_signature_id = null;
        $authority->verification_hash = null;
        $authority->signed_at = null;
        $authority->save();

        $fromStatus = $case->current_status;
        $case->current_status = CaseWorkflowStatus::APPROVED;
        $case->authorized_at = null;
        $case->save();

        WorkflowHistory::create([
            'inward_case_id' => $case->id,
            'performed_by' => $user->id,
            'from_status' => $fromStatus,
            'to_status' => CaseWorkflowStatus::APPROVED,
            'action_type' => 'RESET_SIGNATURE',
            'remarks' => "Digital signature reset. Reason: {$validated['remarks']}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', 'Digital signature successfully reset. Case reverted to Approved for re-signing.');
    }

    /**
     * Cleanly Delete Draft Case (Super Admin only)
     */
    public function deleteDraft(Request $request, string $caseId)
    {
        $user = $request->user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Unauthorized: Only Directorate Admin can delete draft dockets.');
        }

        $case = InwardCase::findOrFail($caseId);

        if (!in_array($case->current_status, [CaseWorkflowStatus::DRAFT, CaseWorkflowStatus::UNDER_VERIFICATION])) {
            return redirect()->back()->with('error', 'Only uncalculated Draft dockets can be deleted.');
        }

        $regNo = $case->registration_no;
        $case->delete();

        return redirect()->back()->with('success', "Draft docket {$regNo} deleted successfully.");
    }
}
