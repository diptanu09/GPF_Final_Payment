<?php

namespace App\Services\Workflow;

use App\Enums\CaseWorkflowStatus;
use App\Models\InwardCase;
use App\Models\User;
use App\Models\WorkflowHistory;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class GpfWorkflowService
{
    /**
     * Advance workflow status with pessimistic locking and immutable audit trail.
     */
    public function transition(string $caseId, CaseWorkflowStatus $targetStatus, string $actionType, ?string $remarks, User $user, ?string $ipAddress = null): InwardCase
    {
        return DB::transaction(function () use ($caseId, $targetStatus, $actionType, $remarks, $user, $ipAddress) {
            // Pessimistic lock on the case row
            $case = InwardCase::where('id', $caseId)->lockForUpdate()->firstOrFail();

            $fromStatus = $case->current_status;

            // Validate transition rules
            $this->validateTransition($fromStatus, $targetStatus, $user);

            // Update Case record timestamps
            $updates = [
                'current_status' => $targetStatus,
            ];

            match ($targetStatus) {
                CaseWorkflowStatus::UNDER_VERIFICATION => $updates['verified_at'] = now(),
                CaseWorkflowStatus::PRE_CALCULATED => $updates['pre_calculated_at'] = now(),
                CaseWorkflowStatus::CALCULATED => $updates['calculated_at'] = now(),
                CaseWorkflowStatus::CHECKED, CaseWorkflowStatus::LTA_CHECKED => $updates['checked_at'] = now(),
                CaseWorkflowStatus::APPROVED, CaseWorkflowStatus::LTA_APPROVED => $updates['approved_at'] = now(),
                CaseWorkflowStatus::AUTHORIZED, CaseWorkflowStatus::LTA_AUTHORIZED => $updates['authorized_at'] = now(),
                CaseWorkflowStatus::HRMS_SYNCED => $updates['hrms_uploaded_at'] = now(),
                CaseWorkflowStatus::DISPATCHED => $updates['dispatched_at'] = now(),
                CaseWorkflowStatus::CANCELLED => $updates['cancelled_at'] = now(),
                default => null,
            };

            $case->update($updates);

            // Record immutable WorkflowHistory
            WorkflowHistory::create([
                'inward_case_id' => $case->id,
                'performed_by' => $user->id,
                'from_status' => $fromStatus,
                'to_status' => $targetStatus,
                'action_type' => $actionType,
                'remarks' => $remarks,
                'ip_address' => $ipAddress,
            ]);

            return $case->fresh();
        });
    }

    /**
     * Validate role permissions for the given state transition
     */
    protected function validateTransition(CaseWorkflowStatus $from, CaseWorkflowStatus $to, User $user): void
    {
        if ($user->isSuperAdmin()) {
            return;
        }

        // Approval requires Approver / Sr. AO role
        if (in_array($to, [CaseWorkflowStatus::APPROVED, CaseWorkflowStatus::LTA_APPROVED, CaseWorkflowStatus::AUTHORIZED, CaseWorkflowStatus::LTA_AUTHORIZED])) {
            if (!$user->isApprover()) {
                throw new RuntimeException('Unauthorized: Only Accounts Officers / Approvers can approve or authorize cases.');
            }
        }

        // Checking requires Checker / AAO role
        if (in_array($to, [CaseWorkflowStatus::CHECKED, CaseWorkflowStatus::LTA_CHECKED])) {
            if (!$user->isChecker()) {
                throw new RuntimeException('Unauthorized: Only Assistant Accounts Officers (AAO) can verify/check cases.');
            }
        }
    }
}
