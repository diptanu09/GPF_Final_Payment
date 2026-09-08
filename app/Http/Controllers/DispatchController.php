<?php

namespace App\Http\Controllers;

use App\Enums\CaseWorkflowStatus;
use App\Models\Authority;
use App\Models\InwardCase;
use App\Services\Workflow\GpfWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DispatchController extends Controller
{
    public function __construct(
        protected GpfWorkflowService $workflowService,
    ) {}

    public function index(): Response
    {
        $authorities = Authority::with(['inwardCase'])
            ->where('is_signed', true)
            ->latest()
            ->paginate(15)
            ->through(fn ($a) => [
                'id' => $a->id,
                'authority_number' => $a->authority_number,
                'authority_date' => $a->authority_date->format('d M Y'),
                'registration_no' => $a->inwardCase->registration_no,
                'subscriber_name' => $a->inwardCase->subscriber_name_cache,
                'gpf_account' => $a->inwardCase->formatted_gpf_account,
                'ddo_code' => $a->inwardCase->ddo_code,
                'treasury_code' => $a->inwardCase->treasury_code,
                'net_amount' => (float) $a->net_amount,
                'is_uploaded_hrms' => $a->is_uploaded_hrms,
                'hrms_uploaded_at' => $a->hrms_uploaded_at?->format('d M Y, h:i A'),
                'is_dispatched' => $a->is_dispatched,
                'dispatched_at' => $a->dispatched_at?->format('d M Y, h:i A'),
                'dispatch_barcode' => $a->dispatch_barcode,
            ]);

        return Inertia::render('Dispatch/Index', [
            'authorities' => $authorities,
        ]);
    }

    public function uploadHrms(Request $request, string $id): RedirectResponse
    {
        $authority = Authority::with(['inwardCase'])->findOrFail($id);

        $authority->update([
            'is_uploaded_hrms' => true,
            'hrms_uploaded_at' => now(),
        ]);

        $this->workflowService->transition(
            $authority->inward_case_id,
            CaseWorkflowStatus::HRMS_SYNCED,
            'HRMS_UPLOAD',
            "Authority order synchronized with State eHRMS portal.",
            $request->user(),
            $request->ip()
        );

        return back()->with('success', 'Authority successfully synchronized to State eHRMS.');
    }

    public function dispatch(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'dispatch_barcode' => ['required', 'string', 'max:50'],
        ]);

        $authority = Authority::with(['inwardCase'])->findOrFail($id);

        $authority->update([
            'is_dispatched' => true,
            'dispatched_at' => now(),
            'dispatch_barcode' => $validated['dispatch_barcode'],
        ]);

        $this->workflowService->transition(
            $authority->inward_case_id,
            CaseWorkflowStatus::DISPATCHED,
            'OUTWARD_DISPATCH',
            "Postal outward dispatch completed. Barcode: {$validated['dispatch_barcode']}.",
            $request->user(),
            $request->ip()
        );

        return back()->with('success', "Dispatched logged with tracking barcode: {$validated['dispatch_barcode']}");
    }
}
