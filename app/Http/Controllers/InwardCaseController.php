<?php

namespace App\Http\Controllers;

use App\Enums\CaseType;
use App\Enums\CaseWorkflowStatus;
use App\Models\InwardCase;
use App\Models\User;
use App\Models\WorkflowHistory;
use App\Services\Integration\OracleMasterBridge;
use App\Services\Workflow\GpfWorkflowService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InwardCaseController extends Controller
{
    public function __construct(
        protected OracleMasterBridge $oracleBridge,
        protected GpfWorkflowService $workflowService,
    ) {}

    public function index(Request $request): Response
    {
        $query = InwardCase::with(['assignedUser', 'latestCalculationRun']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('registration_no', 'like', "%{$search}%")
                  ->orWhere('account_no', 'like', "%{$search}%")
                  ->orWhere('subscriber_name_cache', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('current_status', $status);
        }

        if ($caseType = $request->input('case_type')) {
            $query->where('case_type', $caseType);
        }

        $cases = $query->latest()->paginate(15)->withQueryString()->through(fn ($c) => [
            'id' => $c->id,
            'registration_no' => $c->registration_no,
            'diary_number' => $c->diary_number,
            'diary_date' => $c->diary_date?->format('d M Y'),
            'gpf_account' => $c->formatted_gpf_account,
            'subscriber_name' => $c->subscriber_name_cache,
            'designation' => $c->designation,
            'case_type_label' => $c->case_type->label(),
            'case_type_badge' => $c->case_type->badgeColor(),
            'status_id' => $c->current_status->value,
            'status_label' => $c->current_status->label(),
            'status_badge' => $c->current_status->badgeClasses(),
            'assigned_user' => $c->assignedUser?->name,
            'final_amount' => $c->latestCalculationRun?->final_closing_balance,
            'created_at' => $c->created_at->format('d M Y'),
        ]);

        return Inertia::render('Inward/Index', [
            'cases' => $cases,
            'filters' => $request->only(['search', 'status', 'case_type']),
            'statuses' => collect(CaseWorkflowStatus::cases())->map(fn ($s) => [
                'id' => $s->value,
                'name' => $s->label(),
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Inward/Create', [
            'series_list' => $this->oracleBridge->getSeriesList(),
            'ddo_list' => $this->oracleBridge->getDdoList(),
            'treasuries' => $this->oracleBridge->getTreasuries(),
            'pension_types' => $this->oracleBridge->getPensionTypes(),
            'case_types' => collect(CaseType::cases())->map(fn ($t) => [
                'id' => $t->value,
                'name' => $t->label(),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Sanitize optional empty strings to null / default values
        $request->merge([
            'last_fund_deduction' => $request->last_fund_deduction ?: null,
            'diary_date' => $request->diary_date ?: now()->toDateString(),
            'debit_during_year' => $request->debit_during_year ?: 0,
            'name_title' => $request->name_title ?: 'Shri',
            'designation_title' => $request->designation_title ?: 'Mr',
            'designation' => $request->designation ?: 'Government Employee',
            'personal_address' => $request->personal_address ?: 'Tripura',
            'mobile_no' => $request->mobile_no ?: null,
            'employee_code' => $request->employee_code ?: null,
            'beneficiary_code' => $request->beneficiary_code ?: null,
            'spouse_name' => $request->spouse_name ?: null,
            'spouse_relation' => $request->spouse_relation ?: 'Spouse',
        ]);

        $validated = $request->validate([
            'series_code' => ['required', 'string'],
            'series_name' => ['nullable', 'string'],
            'account_no' => ['required', 'string'],
            'subscriber_name' => ['required', 'string', 'max:255'],
            'name_title' => ['required', 'string'],
            'designation_title' => ['required', 'string'],
            'designation' => ['required', 'string', 'max:255'],
            'case_type' => ['required', 'string'],
            'pension_type_id' => ['required', 'string'],
            'section' => ['nullable', 'string'],
            'ddo_code' => ['required', 'string'],
            'treasury_code' => ['required', 'string'],
            'event_date' => ['required', 'date'],
            'diary_number' => ['nullable', 'string'],
            'diary_date' => ['nullable', 'date'],
            'last_fund_deduction' => ['nullable', 'date'],
            'debit_during_year' => ['nullable', 'numeric'],
            'personal_address' => ['required', 'string'],
            'mobile_no' => ['nullable', 'string'],
            'employee_code' => ['nullable', 'string'],
            'beneficiary_code' => ['nullable', 'string'],
            'spouse_name' => ['nullable', 'string'],
            'spouse_relation' => ['nullable', 'string'],
        ], [
            'event_date.required' => 'Please enter the Retirement or Event Date.',
            'ddo_code.required' => 'Please select the DDO Code.',
            'treasury_code.required' => 'Please select the Treasury.',
            'subscriber_name.required' => 'Subscriber Name is required.',
        ]);

        $year = date('Y');
        $formatSeries = str_pad($validated['series_code'], 2, '0', STR_PAD_LEFT);
        $cleanAccount = preg_replace('/[^0-9]/', '', $validated['account_no']);
        $baseRegNo = $year . $formatSeries . $cleanAccount;
        $registrationNo = $baseRegNo;
        $counter = 1;
        while (InwardCase::withTrashed()->where('registration_no', $registrationNo)->exists()) {
            $registrationNo = $baseRegNo . '-' . $counter;
            $counter++;
        }

        $pensionTypes = $this->oracleBridge->getPensionTypes();
        $selectedPension = $pensionTypes->firstWhere('id', (string) $validated['pension_type_id']);
        $pensionTypeName = $selectedPension['name'] ?? ($validated['pension_type_id'] === '2' ? 'Family Pension (FAM)' : 'Superannuation (SUP)');

        $inwardCase = InwardCase::create([
            'registration_no' => $registrationNo,
            'diary_number' => $validated['diary_number'] ?? ('INW/' . $year . '/' . rand(1000, 9999)),
            'diary_date' => $validated['diary_date'] ?? now(),
            'series_code' => $validated['series_code'],
            'series_name' => $validated['series_name'] ?? null,
            'account_no' => $validated['account_no'],
            'subscriber_name_cache' => $validated['subscriber_name'],
            'name_title' => $validated['name_title'],
            'designation_title' => $validated['designation_title'],
            'designation' => $validated['designation'],
            'case_type' => CaseType::from($validated['case_type']),
            'pension_type_id' => $validated['pension_type_id'],
            'pension_type_name' => $pensionTypeName,
            'section' => $validated['section'] ?? 'Fund Section I',
            'ddo_code' => $validated['ddo_code'],
            'treasury_code' => $validated['treasury_code'],
            'event_date' => $validated['event_date'],
            'last_fund_deduction' => $validated['last_fund_deduction'] ?? null,
            'debit_during_year' => $validated['debit_during_year'] ?? 0.00,
            'personal_address' => $validated['personal_address'],
            'mobile_no' => $validated['mobile_no'] ?? null,
            'employee_code' => $validated['employee_code'] ?? null,
            'beneficiary_code' => $validated['beneficiary_code'] ?? null,
            'spouse_name' => $validated['spouse_name'] ?? null,
            'spouse_relation' => $validated['spouse_relation'] ?? null,
            'current_status' => CaseWorkflowStatus::DRAFT,
            'created_by' => $request->user()->id,
            'assigned_user_id' => $request->user()->id,
        ]);

        WorkflowHistory::create([
            'inward_case_id' => $inwardCase->id,
            'performed_by' => $request->user()->id,
            'from_status' => null,
            'to_status' => CaseWorkflowStatus::DRAFT,
            'action_type' => 'INWARD_REGISTER',
            'remarks' => "Inward case registered with Registration No. {$registrationNo}.",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('inward.show', $inwardCase->id)->with('success', "Case registered successfully. Registration No: {$registrationNo}");
    }

    public function show(string $id): Response
    {
        $case = InwardCase::with([
            'assignedUser',
            'creator',
            'latestCalculationRun.monthlyBreakdowns',
            'nominees',
            'authorities.digitalSignature',
            'workflowHistories.performedByUser',
        ])->findOrFail($id);

        $staffUsers = User::where('is_active', true)->select('id', 'name', 'role')->get();

        return Inertia::render('Inward/Show', [
            'case_data' => $case,
            'staff_users' => $staffUsers,
        ]);
    }

    public function lookup(Request $request): \Illuminate\Http\JsonResponse
    {
        $series = $request->query('series_code');
        $account = $request->query('account_no');

        if (!$series || !$account) {
            return response()->json(['error' => 'Series and Account No required'], 422);
        }

        $subscriber = $this->oracleBridge->lookupSubscriber($series, $account);
        return response()->json($subscriber);
    }

    public function assignStaff(Request $request, string $id): RedirectResponse
    {
        $request->validate(['assigned_user_id' => ['required', 'exists:users,id']]);
        
        $case = InwardCase::findOrFail($id);
        $user = User::findOrFail($request->assigned_user_id);

        $case->update(['assigned_user_id' => $user->id]);

        WorkflowHistory::create([
            'inward_case_id' => $case->id,
            'performed_by' => $request->user()->id,
            'from_status' => $case->current_status,
            'to_status' => $case->current_status,
            'action_type' => 'ASSIGN_STAFF',
            'remarks' => "Case reassigned to {$user->name} ({$user->roleLabel()})",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "Case successfully assigned to {$user->name}");
    }

    public function transfer(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'to_user_id' => ['required', 'exists:users,id'],
            'remarks' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $case = InwardCase::findOrFail($id);
        $toUser = User::findOrFail($validated['to_user_id']);
        $currentUser = $request->user();

        $case->transferred_to_user_id = $toUser->id;
        $case->transfer_remarks = $validated['remarks'];
        $case->assigned_user_id = $toUser->id;
        $case->save();

        WorkflowHistory::create([
            'inward_case_id' => $case->id,
            'performed_by' => $currentUser->id,
            'from_status' => $case->current_status,
            'to_status' => $case->current_status,
            'action_type' => 'TRANSFER_CASE',
            'remarks' => "Sectional Receipt: Transferred from {$currentUser->name} to {$toUser->name}. Remarks: {$validated['remarks']}",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "Case successfully transferred to {$toUser->name} ({$toUser->roleLabel()})");
    }
}

