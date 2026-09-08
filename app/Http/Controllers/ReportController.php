<?php

namespace App\Http\Controllers;

use App\Enums\CaseWorkflowStatus;
use App\Models\InwardCase;
use App\Models\User;
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
        $totalMinusBalance = InwardCase::where('current_status', CaseWorkflowStatus::MINUS_BALANCE)->count();

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
}
