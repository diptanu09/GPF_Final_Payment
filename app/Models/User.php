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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isApprover(): bool
    {
        return in_array($this->role, ['super_admin', 'approver']);
    }

    public function isChecker(): bool
    {
        return in_array($this->role, ['super_admin', 'approver', 'checker']);
    }

    public function isDealingAssistant(): bool
    {
        return in_array($this->role, ['super_admin', 'approver', 'checker', 'dealing_assistant']);
    }

    public function inwardCases(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InwardCase::class, 'assigned_user_id');
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super Admin',
            'approver' => 'Senior Accounts Officer (Sr. AO)',
            'checker' => 'Assistant Accounts Officer (AAO)',
            'dealing_assistant' => 'Dealing Assistant (DA)',
            default => 'Data Entry Operator (DEO)',
        };
    }
}
