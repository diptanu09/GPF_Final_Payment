<?php

namespace App\Http\Controllers;

use App\Enums\CaseWorkflowStatus;
use App\Models\Authority;
use App\Models\DigitalSignature;
use App\Models\InwardCase;
use App\Models\User;
use App\Models\WorkflowHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(): Response
    {
        $totalRegistered = InwardCase::count();
        $totalSettled = InwardCase::whereIn('current_status', [
            CaseWorkflowStatus::AUTHORIZED,
            CaseWorkflowStatus::HRMS_SYNCED,
            CaseWorkflowStatus::DISPATCHED,
        ])->count();
        $totalPending = InwardCase::whereNotIn('current_status', [
            CaseWorkflowStatus::AUTHORIZED,
            CaseWorkflowStatus::HRMS_SYNCED,
            CaseWorkflowStatus::DISPATCHED,
            CaseWorkflowStatus::CANCELLED,
        ])->count();
        $totalMinusBalance = InwardCase::where('current_status', CaseWorkflowStatus::MINUS_BALANCE)
            ->orWhereHas('latestCalculationRun', fn($q) => $q->where('final_closing_balance', '<', 0))
            ->count();
        $totalCancelled = InwardCase::where('current_status', CaseWorkflowStatus::CANCELLED)->count();

        // User performance count
        $userStats = User::withCount(['inwardCases' => function ($q) {
            $q->whereIn('current_status', [CaseWorkflowStatus::AUTHORIZED, CaseWorkflowStatus::DISPATCHED]);
        }])->get()->map(fn ($u) => [
            'name' => $u->name,
            'role' => $u->roleLabel(),
            'settled_count' => $u->inward_cases_count,
        ]);

        return Inertia::render('Reports/Index', [
            'summary' => [
                'total_registered' => $totalRegistered,
                'total_settled' => $totalSettled,
                'total_pending' => $totalPending,
                'total_minus_balance' => $totalMinusBalance,
                'total_cancelled' => $totalCancelled,
            ],
            'user_stats' => $userStats,
        ]);
    }

    public function settledCases(Request $request): Response
    {
        $cases = InwardCase::with(['assignedUser', 'latestCalculationRun', 'latestAuthority'])
            ->whereIn('current_status', [
                CaseWorkflowStatus::AUTHORIZED,
                CaseWorkflowStatus::HRMS_SYNCED,
                CaseWorkflowStatus::DISPATCHED,
            ])
            ->latest('authorized_at')
            ->paginate(20)
            ->through(fn ($c) => [
                'registration_no' => $c->registration_no,
                'gpf_account' => $c->formatted_gpf_account,
                'subscriber_name' => $c->subscriber_name_cache,
                'designation' => $c->designation,
                'authority_no' => $c->latestAuthority?->authority_number,
                'net_amount' => (float) ($c->latestCalculationRun?->final_closing_balance ?? 0.00),
                'authorized_at' => $c->authorized_at?->format('d M Y'),
                'status_label' => $c->current_status->label(),
                'status_badge' => $c->current_status->badgeClasses(),
            ]);

        return Inertia::render('Reports/SettledCases', [
            'cases' => $cases,
        ]);
    }

    public function pendingCases(Request $request): Response
    {
        $cases = InwardCase::with(['assignedUser'])
            ->whereNotIn('current_status', [
                CaseWorkflowStatus::AUTHORIZED,
                CaseWorkflowStatus::HRMS_SYNCED,
                CaseWorkflowStatus::DISPATCHED,
                CaseWorkflowStatus::CANCELLED,
            ])
            ->latest()
            ->paginate(20)
            ->through(fn ($c) => [
                'id' => $c->id,
                'registration_no' => $c->registration_no,
                'gpf_account' => $c->formatted_gpf_account,
                'subscriber_name' => $c->subscriber_name_cache,
                'designation' => $c->designation,
                'status_label' => $c->current_status->label(),
                'status_badge' => $c->current_status->badgeClasses(),
                'assigned_user' => $c->assignedUser?->name ?? 'Unassigned',
                'days_pending' => $c->created_at->diffInDays(now()),
                'created_at' => $c->created_at->format('d M Y'),
            ]);

        return Inertia::render('Reports/PendingCases', [
            'cases' => $cases,
        ]);
    }

    /**
     * Officer Productivity & Performance Matrix (Legacy: mis_user_report.php)
     */
    public function userProductivity(Request $request): Response
    {
        $users = User::where('is_active', true)->get()->map(function ($u) {
            $inwardCount = InwardCase::where('created_by', $u->id)->count();
            $calculatedCount = WorkflowHistory::where('performed_by', $u->id)->where('action_type', 'CALCULATE')->count();
            $checkedCount = WorkflowHistory::where('performed_by', $u->id)->where('action_type', 'CHECK')->count();
            $approvedCount = WorkflowHistory::where('performed_by', $u->id)->where('action_type', 'APPROVE')->count();
            $signedCount = WorkflowHistory::where('performed_by', $u->id)->where('action_type', 'SIGN_AUTHORITY')->count();
            $assignedActive = InwardCase::where('assigned_user_id', $u->id)->whereNotIn('current_status', [
                CaseWorkflowStatus::DISPATCHED,
                CaseWorkflowStatus::CANCELLED,
            ])->count();

            return [
                'id' => $u->id,
                'name' => $u->name,
                'username' => $u->username,
                'role' => $u->roleLabel(),
                'designation' => $u->designation ?: 'Officer',
                'section' => $u->section ?: 'Fund Section',
                'inward_count' => $inwardCount,
                'calculated_count' => $calculatedCount,
                'checked_count' => $checkedCount,
                'approved_count' => $approvedCount,
                'signed_count' => $signedCount,
                'assigned_active' => $assignedActive,
                'total_actions' => $inwardCount + $calculatedCount + $checkedCount + $approvedCount + $signedCount,
            ];
        });

        return Inertia::render('Reports/UserProductivity', [
            'productivity' => $users,
        ]);
    }

    /**
     * PKI Digital Signatures Audit Log (Legacy: mis_digital_signature.php)
     */
    public function digitalSignatures(Request $request): Response
    {
        $signatures = DigitalSignature::with(['authority.inwardCase', 'signer'])
            ->latest()
            ->paginate(20)
            ->through(fn ($s) => [
                'id' => $s->id,
                'registration_no' => $s->authority?->inwardCase?->registration_no,
                'gpf_account' => $s->authority?->inwardCase?->formatted_gpf_account,
                'subscriber_name' => $s->authority?->inwardCase?->subscriber_name_cache,
                'authority_number' => $s->authority?->authority_number,
                'signer_name' => $s->signer?->name ?? 'Authorized Signer',
                'certificate_dn' => $s->certificate_subject_dn ?: 'CN=Sr. Accounts Officer, OU=Tripura AG, O=CAG India',
                'signature_hash' => $s->signature_hash,
                'is_verified' => (bool)$s->is_verified,
                'signed_at' => $s->signed_at ? $s->signed_at->format('d M Y, h:i A') : $s->created_at->format('d M Y, h:i A'),
            ]);

        return Inertia::render('Reports/DigitalSignatures', [
            'signatures' => $signatures,
        ]);
    }

    /**
     * Minus Balance Monitoring & Recovery Dashboard (Legacy: minus_balance_remarks.php)
     */
    public function minusBalanceCases(Request $request): Response
    {
        $cases = InwardCase::with(['latestCalculationRun', 'assignedUser'])
            ->where(function ($q) {
                $q->where('current_status', CaseWorkflowStatus::MINUS_BALANCE)
                  ->orWhereHas('latestCalculationRun', fn($qr) => $qr->where('final_closing_balance', '<', 0))
                  ->orWhereNotNull('minus_balance_remarks');
            })
            ->latest()
            ->paginate(20)
            ->through(fn ($c) => [
                'id' => $c->id,
                'registration_no' => $c->registration_no,
                'gpf_account' => $c->formatted_gpf_account,
                'subscriber_name' => $c->subscriber_name_cache,
                'designation' => $c->designation,
                'ddo_code' => $c->ddo_code,
                'ddo_designation' => $c->ddo_designation,
                'overdrawn_balance' => (float)($c->latestCalculationRun?->final_closing_balance ?? 0),
                'amount_recovered' => (float)($c->amount_recovered ?? 0),
                'minus_balance_remarks' => $c->minus_balance_remarks,
                'is_closed' => (bool)$c->minus_balance_closed_at,
                'closed_at' => $c->minus_balance_closed_at?->format('d M Y'),
                'created_at' => $c->created_at->format('d M Y'),
            ]);

        return Inertia::render('Reports/MinusBalanceCases', [
            'cases' => $cases,
        ]);
    }

    /**
     * Canceled Cases Audit Report (Legacy: mis_cancel_cases.php)
     */
    public function cancelledCases(Request $request): Response
    {
        $cases = InwardCase::with(['assignedUser', 'creator'])
            ->where('current_status', CaseWorkflowStatus::CANCELLED)
            ->latest('cancelled_at')
            ->paginate(20)
            ->through(fn ($c) => [
                'id' => $c->id,
                'registration_no' => $c->registration_no,
                'gpf_account' => $c->formatted_gpf_account,
                'subscriber_name' => $c->subscriber_name_cache,
                'designation' => $c->designation,
                'ddo_code' => $c->ddo_code,
                'ddo_designation' => $c->ddo_designation,
                'cancelled_remarks' => $c->cancelled_remarks ?: 'Cancelled by Directorate order',
                'cancelled_at' => $c->cancelled_at ? $c->cancelled_at->format('d M Y, h:i A') : $c->updated_at->format('d M Y'),
                'created_at' => $c->created_at->format('d M Y'),
            ]);

        return Inertia::render('Reports/CancelledCases', [
            'cases' => $cases,
        ]);
    }
}
