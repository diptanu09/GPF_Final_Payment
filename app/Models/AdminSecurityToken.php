<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminSecurityToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'token_type',
        'role',
        'issued_for_email',
        'notes',
        'created_by',
        'is_used',
        'used_by',
        'used_at',
        'expires_at',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function usedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    public function isValid(): bool
    {
        if ($this->is_used) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }
}
