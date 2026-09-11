<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'is_active',
        'approval_status',
        'approved_by',
        'approved_at',
        'designation',
        'section',
        'phone_number',
        'admin_notes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->approval_status === 'pending';
    }

    public function approverUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isSuperAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function isApprover(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'approver']);
    }

    public function isChecker(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'approver', 'checker']);
    }

    public function isDealingAssistant(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'approver', 'checker', 'dealing_assistant', 'deo']);
    }

    public function inwardCases(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InwardCase::class, 'assigned_user_id');
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            'super_admin', 'admin' => 'Administrator / Director',
            'approver' => 'Senior Accounts Officer (Sr. AO)',
            'checker' => 'Assistant Accounts Officer (AAO)',
            'dispatch' => 'Outward Dispatch',
            'dealing_assistant' => 'Dealing Assistant (DA)',
            default => 'Data Entry Operator (DEO)',
        };
    }
}
