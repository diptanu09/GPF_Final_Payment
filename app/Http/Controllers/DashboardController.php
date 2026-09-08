<?php

namespace App\Http\Controllers;

use App\Enums\CaseWorkflowStatus;
use App\Models\Authority;
use App\Models\InwardCase;
use App\Models\WorkflowHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $now = Carbon::now();

        // High-level KPI metrics
        $totalRegistered = InwardCase::count();
        $totalSettled = InwardCase::whereIn('current_status', [
            CaseWorkflowStatus::AUTHORIZED,
            CaseWorkflowStatus::HRMS_SYNCED,
            CaseWorkflowStatus::DISPATCHED,
            CaseWorkflowStatus::LTA_AUTHORIZED,
        ])->count();
        $totalPending = InwardCase::whereNotIn('current_status', [
            CaseWorkflowStatus::AUTHORIZED,
            CaseWorkflowStatus::HRMS_SYNCED,
            CaseWorkflowStatus::DISPATCHED,
            CaseWorkflowStatus::LTA_AUTHORIZED,
            CaseWorkflowStatus::CANCELLED,
        ])->count();

        $totalAuthorizedAmount = Authority::where('is_signed', true)->sum('gross_amount');
        $totalMinusBalanceCases = InwardCase::where('current_status', CaseWorkflowStatus::MINUS_BALANCE)->count();

        // Calculate Pending Case Aging
        $pendingCases = InwardCase::whereNotIn('current_status', [
            CaseWorkflowStatus::AUTHORIZED,
            CaseWorkflowStatus::HRMS_SYNCED,
            CaseWorkflowStatus::DISPATCHED,
            CaseWorkflowStatus::LTA_AUTHORIZED,
            CaseWorkflowStatus::CANCELLED,
        ])->get();

        $aging = [
            'less_15' => 0,
            '15_to_30' => 0,
            '31_to_45' => 0,
            '46_to_60' => 0,
            'more_60' => 0,
        ];

        foreach ($pendingCases as $case) {
            $days = $case->created_at->diffInDays($now);
            if ($days < 15) {
                $aging['less_15']++;
            } elseif ($days <= 30) {
                $aging['15_to_30']++;
            } elseif ($days <= 45) {
                $aging['31_to_45']++;
            } elseif ($days <= 60) {
                $aging['46_to_60']++;
            } else {
                $aging['more_60']++;
            }
        }

        // Recent case activity
        $recentCases = InwardCase::with(['assignedUser', 'latestCalculationRun'])
            ->latest()
            ->take(8)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'registration_no' => $c->registration_no,
                'subscriber_name' => $c->subscriber_name_cache,
                'gpf_account' => $c->formatted_gpf_account,
                'case_type_label' => $c->case_type->label(),
                'case_type_badge' => $c->case_type->badgeColor(),
                'status_id' => $c->current_status->value,
                'status_label' => $c->current_status->label(),
                'status_badge' => $c->current_status->badgeClasses(),
                'created_at' => $c->created_at->format('d M Y, h:i A'),
                'amount' => $c->latestCalculationRun?->final_closing_balance ?? null,
            ]);

        // Recent audit events
        $recentHistories = WorkflowHistory::with(['performedByUser', 'inwardCase'])
            ->latest()
            ->take(6)
            ->get()
            ->map(fn ($h) => [
                'id' => $h->id,
                'case_reg' => $h->inwardCase?->registration_no,
                'performed_by' => $h->performedByUser?->name ?? 'System',
                'action_type' => $h->action_type,
                'to_status' => $h->to_status?->label(),
                'remarks' => $h->remarks,
                'created_at' => $h->created_at->diffForHumans(),
            ]);

        return Inertia::render('Dashboard', [
            'metrics' => [
                'registered' => $totalRegistered,
                'settled' => $totalSettled,
                'pending' => $totalPending,
                'authorized_amount' => round($totalAuthorizedAmount),
                'minus_balance_cases' => $totalMinusBalanceCases,
            ],
            'aging' => $aging,
            'recent_cases' => $recentCases,
            'recent_histories' => $recentHistories,
        ]);
    }
}
