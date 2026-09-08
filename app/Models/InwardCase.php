<?php

namespace App\Models;

use App\Enums\CaseType;
use App\Enums\CaseWorkflowStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class InwardCase extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'registration_no',
        'diary_number',
        'diary_date',
        'series_code',
        'series_name',
        'account_no',
        'subscriber_name_cache',
        'name_title',
        'designation_title',
        'designation',
        'case_type',
        'pension_type_id',
        'pension_type_name',
        'section',
        'ddo_code',
        'ddo_designation',
        'treasury_code',
        'treasury_name',
        'sub_treasury_name',
        'event_date',
        'last_fund_deduction',
        'debit_during_year',
        'personal_address',
        'mobile_no',
        'employee_code',
        'beneficiary_code',
        'spouse_name',
        'spouse_relation',
        'lta_to_whom',
        'date_of_lta',
        'current_status',
        'assigned_user_id',
        'created_by',
        'verified_at',
        'pre_calculated_at',
        'calculated_at',
        'checked_at',
        'approved_at',
        'authorized_at',
        'hrms_uploaded_at',
        'dispatched_at',
        'cancelled_at',
    ];

    protected $casts = [
        'diary_date' => 'date',
        'event_date' => 'date',
        'last_fund_deduction' => 'date',
        'date_of_lta' => 'date',
        'debit_during_year' => 'decimal:2',
        'current_status' => CaseWorkflowStatus::class,
        'case_type' => CaseType::class,
        'verified_at' => 'datetime',
        'pre_calculated_at' => 'datetime',
        'calculated_at' => 'datetime',
        'checked_at' => 'datetime',
        'approved_at' => 'datetime',
        'authorized_at' => 'datetime',
        'hrms_uploaded_at' => 'datetime',
        'dispatched_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function calculationRuns(): HasMany
    {
        return $this->hasMany(CalculationRun::class)->latest();
    }

    public function latestCalculationRun(): HasOne
    {
        return $this->hasOne(CalculationRun::class)->latest('created_at');
    }

    public function nominees(): HasMany
    {
        return $this->hasMany(CaseNominee::class);
    }

    public function authorities(): HasMany
    {
        return $this->hasMany(Authority::class)->latest();
    }

    public function latestAuthority(): HasOne
    {
        return $this->hasOne(Authority::class)->latest('created_at');
    }

    public function workflowHistories(): HasMany
    {
        return $this->hasMany(WorkflowHistory::class)->orderBy('created_at', 'asc');
    }

    public function getFormattedGpfAccountAttribute(): string
    {
        return 'T/' . ($this->series_name ?: $this->series_code) . '/' . $this->account_no;
    }
}
