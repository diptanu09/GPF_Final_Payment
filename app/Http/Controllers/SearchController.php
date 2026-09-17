<?php

namespace App\Http\Controllers;

use App\Enums\CaseWorkflowStatus;
use App\Models\InwardCase;
use App\Services\Integration\OracleMasterBridge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function __construct(
        protected OracleMasterBridge $oracleBridge
    ) {}

    /**
     * Advanced Multi-Criteria Search View
     */
    public function index(Request $request): Response
    {
        $searchType = $request->query('search_type', 'code'); // 'code', 'account', 'name', 'all'
        $term = trim($request->query('query', ''));
        $seriesId = $request->query('series_id');
        $accountNo = trim($request->query('account_no', ''));
        $status = $request->query('status');
        $pensionTypeId = $request->query('pension_type_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $query = InwardCase::with(['latestCalculationRun', 'latestAuthority', 'assignedUser']);
        $likeOp = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        if ($searchType === 'code' && !empty($term)) {
            $query->where(function ($q) use ($term, $likeOp) {
                $q->where('registration_no', $likeOp, "%{$term}%")
                  ->orWhere('employee_code', $likeOp, "%{$term}%")
                  ->orWhere('beneficiary_code', $likeOp, "%{$term}%");
            });
        } elseif ($searchType === 'account' && (!empty($accountNo) || !empty($seriesId))) {
            if (!empty($seriesId)) {
                $query->where('series_code', $seriesId);
            }
            if (!empty($accountNo)) {
                $query->where('account_no', $likeOp, "%{$accountNo}%");
            }
        } elseif ($searchType === 'name' && !empty($term)) {
            $query->where('subscriber_name_cache', $likeOp, "%{$term}%");
        } elseif (!empty($term)) {
            $query->where(function ($q) use ($term, $likeOp) {
                $q->where('registration_no', $likeOp, "%{$term}%")
                  ->orWhere('subscriber_name_cache', $likeOp, "%{$term}%")
                  ->orWhere('account_no', $likeOp, "%{$term}%")
                  ->orWhere('employee_code', $likeOp, "%{$term}%")
                  ->orWhere('beneficiary_code', $likeOp, "%{$term}%");
            });
        }

        if (!empty($status)) {
            $query->where('current_status', (int)$status);
        }

        if (!empty($pensionTypeId)) {
            $query->where('pension_type_id', $pensionTypeId);
        }

        if (!empty($dateFrom)) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $cases = $query->latest()->paginate(20)->withQueryString()->through(fn ($c) => [
            'id' => $c->id,
            'registration_no' => $c->registration_no,
            'gpf_account' => $c->formatted_gpf_account,
            'subscriber_name' => $c->subscriber_name_cache,
            'designation' => $c->designation,
            'employee_code' => $c->employee_code,
            'beneficiary_code' => $c->beneficiary_code,
            'pension_type' => $c->pension_type_name ?: ($c->case_type?->label() ?? 'Superannuation'),
            'current_status' => $c->current_status->value,
            'status_label' => $c->current_status->label(),
            'status_badge' => $c->current_status->badgeClasses(),
            'net_amount' => (float)($c->latestCalculationRun?->final_closing_balance ?? 0),
            'authority_no' => $c->latestAuthority?->authority_number,
            'assigned_user' => $c->assignedUser?->name ?? 'Unassigned',
            'created_at' => $c->created_at->format('d M Y'),
        ]);

        $seriesList = $this->oracleBridge->getGpfSeries();
        $pensionTypes = $this->oracleBridge->getPensionTypes();

        $statuses = array_map(fn($s) => [
            'id' => $s->value,
            'name' => $s->label(),
        ], CaseWorkflowStatus::cases());

        return Inertia::render('Search/Index', [
            'cases' => $cases,
            'series_list' => $seriesList,
            'pension_types' => $pensionTypes,
            'statuses' => $statuses,
            'filters' => [
                'search_type' => $searchType,
                'query' => $term,
                'series_id' => $seriesId,
                'account_no' => $accountNo,
                'status' => $status,
                'pension_type_id' => $pensionTypeId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    /**
     * Deep-Dive Docket Inspection API (Provides data for all 6 tabs)
     */
    public function inspect(string $caseId): JsonResponse
    {
        $case = InwardCase::with([
            'latestCalculationRun.computedBy',
            'latestCalculationRun.checkedBy',
            'latestCalculationRun.approvedBy',
            'latestAuthority.signer',
            'latestAuthority.digitalSignatures.signer',
            'nominees',
            'assignedUser',
            'transferredToUser',
            'creator',
            'workflowHistories.user',
        ])->findOrFail($caseId);

        $run = $case->latestCalculationRun;
        $auth = $case->latestAuthority;

        // 1. Basic Info
        $basicInfo = [
            'registration_no' => $case->registration_no,
            'gpf_account' => $case->formatted_gpf_account,
            'series_code' => $case->series_code,
            'series_name' => $case->series_name,
            'account_no' => $case->account_no,
            'subscriber_name' => $case->subscriber_name_cache,
            'name_title' => $case->name_title,
            'designation_title' => $case->designation_title,
            'designation' => $case->designation,
            'case_type' => $case->case_type?->label() ?? $case->case_type?->value,
            'pension_type' => $case->pension_type_name ?: 'Superannuation',
            'section' => $case->section,
            'ddo_code' => $case->ddo_code,
            'ddo_designation' => $case->ddo_designation,
            'treasury_code' => $case->treasury_code,
            'treasury_name' => $case->treasury_name,
            'sub_treasury_name' => $case->sub_treasury_name,
            'personal_address' => $case->personal_address,
            'mobile_no' => $case->mobile_no,
            'employee_code' => $case->employee_code,
            'beneficiary_code' => $case->beneficiary_code,
            'spouse_name' => $case->spouse_name,
            'spouse_relation' => $case->spouse_relation,
            'lta_to_whom' => $case->lta_to_whom,
            'date_of_lta' => $case->date_of_lta?->format('d/m/Y'),
            'debit_during_year' => (float)$case->debit_during_year,
            'status_label' => $case->current_status->label(),
            'status_badge' => $case->current_status->badgeClasses(),
            'assigned_user' => $case->assignedUser?->name,
            'creator' => $case->creator?->name,
        ];

        // 2. Nominees
        $nominees = $case->nominees->map(fn($n) => [
            'id' => $n->id,
            'name' => $n->nominee_name,
            'beneficiary_code' => $n->beneficiary_code,
            'relationship' => $n->relationship,
            'share_percentage' => (float)$n->share_percentage,
            'allocated_amount' => (float)$n->allocated_amount,
            'bank_account' => $n->bank_account_no,
            'ifsc_code' => $n->ifsc_code,
            'is_minor' => (bool)$n->is_minor,
            'guardian_name' => $n->guardian_name,
        ]);

        // 3. Date Timeline
        $timeline = [
            'diary_date' => $case->diary_date?->format('d M Y'),
            'event_date' => $case->event_date?->format('d M Y'),
            'last_fund_deduction' => $case->last_fund_deduction?->format('F Y'),
            'date_of_lta' => $case->date_of_lta?->format('d M Y'),
            'created_at' => $case->created_at->format('d M Y, h:i A'),
            'verified_at' => $case->verified_at?->format('d M Y, h:i A'),
            'pre_calculated_at' => $case->pre_calculated_at?->format('d M Y, h:i A'),
            'calculated_at' => $case->calculated_at?->format('d M Y, h:i A'),
            'checked_at' => $case->checked_at?->format('d M Y, h:i A'),
            'approved_at' => $case->approved_at?->format('d M Y, h:i A'),
            'authorized_at' => $case->authorized_at?->format('d M Y, h:i A'),
            'hrms_uploaded_at' => $case->hrms_uploaded_at?->format('d M Y, h:i A'),
            'dispatched_at' => $case->dispatched_at?->format('d M Y, h:i A'),
            'cancelled_at' => $case->cancelled_at?->format('d M Y, h:i A'),
            'minus_balance_closed_at' => $case->minus_balance_closed_at?->format('d M Y, h:i A'),
        ];

        // 4. Financial Breakdown
        $financials = $run ? [
            'opening_fin_year' => $run->opening_fin_year,
            'opening_balance' => (float)$run->opening_balance_amount,
            'total_subscriptions' => (float)$run->total_subscriptions,
            'total_refunds' => (float)$run->total_refunds,
            'excess_deposits' => (float)$run->excess_deposits,
            'total_withdrawals' => (float)$run->total_withdrawals,
            'actual_interest' => (float)$run->actual_interest_computed,
            'delayed_interest' => (float)$run->delayed_interest_computed,
            'total_interest' => (float)$run->total_interest_computed,
            'dlis_admissible' => (bool)$run->dlis_admissible,
            'dlis_amount' => (float)$run->dlis_amount,
            'final_closing_balance' => (float)$run->final_closing_balance,
            'cutoff_date' => $run->cutoff_date?->format('d/m/Y'),
            'interest_allowed_upto' => $run->interest_allowed_upto?->format('F Y'),
            'computed_by' => $run->computedBy?->name,
            'checked_by' => $run->checkedBy?->name,
            'approved_by' => $run->approvedBy?->name,
            'has_authority' => (bool)$auth,
            'authority_no' => $auth?->authority_number,
            'authority_date' => $auth?->authority_date?->format('d M Y'),
        ] : null;

        // 5. Remarks & Governance
        $remarks = [
            'delay_justification' => $case->delay_justification,
            'delay_approved_by' => $case->delayApprover?->name,
            'delay_approved_at' => $case->delay_approved_at?->format('d M Y, h:i A'),
            'minus_balance_remarks' => $case->minus_balance_remarks,
            'amount_recovered' => (float)$case->amount_recovered,
            'minus_balance_closed_at' => $case->minus_balance_closed_at?->format('d M Y, h:i A'),
            'cancelled_remarks' => $case->cancelled_remarks,
            'unapproved_remarks' => $case->unapproved_remarks,
            'transfer_remarks' => $case->transfer_remarks,
            'transferred_to' => $case->transferredToUser?->name,
        ];

        // 6. Workflow Logs & Reverts
        $workflowLogs = $case->workflowHistories->map(fn($w) => [
            'id' => $w->id,
            'action_type' => $w->action_type,
            'from_status' => $w->from_status?->label() ?? 'Initial',
            'to_status' => $w->to_status?->label() ?? 'Updated',
            'remarks' => $w->remarks,
            'officer_name' => $w->user?->name ?? 'System',
            'officer_role' => $w->user?->roleLabel() ?? 'System',
            'ip_address' => $w->ip_address,
            'created_at' => $w->created_at->format('d M Y, h:i A'),
        ]);

        return response()->json([
            'basic_info' => $basicInfo,
            'nominees' => $nominees,
            'timeline' => $timeline,
            'financials' => $financials,
            'remarks' => $remarks,
            'workflow_logs' => $workflowLogs,
        ]);
    }
}
