<?php

namespace App\Models;

use App\Enums\CaseWorkflowStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'inward_case_id',
        'performed_by',
        'from_status',
        'to_status',
        'action_type',
        'remarks',
        'ip_address',
    ];

    protected $casts = [
        'from_status' => CaseWorkflowStatus::class,
        'to_status' => CaseWorkflowStatus::class,
    ];

    public function inwardCase(): BelongsTo
    {
        return $this->belongsTo(InwardCase::class);
    }

    public function performedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
