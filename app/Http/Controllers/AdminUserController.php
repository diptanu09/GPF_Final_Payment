<?php

namespace App\Http\Controllers;

use App\Models\AdminSecurityToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    protected function authorizeAdmin(): void
    {
        abort_unless(Auth::user()?->isSuperAdmin(), 403, 'Unauthorized. System administration privileges required.');
    }

    public function index(Request $request): Response
    {
        $this->authorizeAdmin();

        $roleFilter = $request->query('role');
        $statusFilter = $request->query('status');
        $search = $request->query('search');

        $usersQuery = User::with('approverUser')->latest();

        if ($roleFilter) {
            $usersQuery->where('role', $roleFilter);
        }

        if ($statusFilter) {
            if ($statusFilter === 'pending') {
                $usersQuery->where('approval_status', 'pending');
            } elseif ($statusFilter === 'approved') {
                $usersQuery->where('approval_status', 'approved');
            } elseif ($statusFilter === 'rejected') {
                $usersQuery->where('approval_status', 'rejected');
            } elseif ($statusFilter === 'active') {
                $usersQuery->where('is_active', true);
            } elseif ($statusFilter === 'inactive') {
                $usersQuery->where('is_active', false);
            }
        }

        if ($search) {
            $search = strtolower($search);
            $usersQuery->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(username) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"]);
            });
        }

        $users = $usersQuery->paginate(20)->withQueryString()->through(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'username' => $u->username,
            'email' => $u->email,
            'role' => $u->role,
            'role_label' => $u->roleLabel(),
            'designation' => $u->designation,
            'section' => $u->section,
            'phone_number' => $u->phone_number,
            'approval_status' => $u->approval_status,
            'is_active' => $u->is_active,
            'approved_at' => $u->approved_at?->format('d M Y, h:i A'),
            'approved_by_name' => $u->approverUser?->name,
            'created_at' => $u->created_at?->format('d M Y, h:i A'),
        ]);

        $pendingUsers = User::where('approval_status', 'pending')
            ->latest()
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'username' => $u->username,
                'email' => $u->email,
                'role' => $u->role,
                'role_label' => $u->roleLabel(),
                'designation' => $u->designation,
                'section' => $u->section,
                'phone_number' => $u->phone_number,
                'created_at' => $u->created_at?->format('d M Y, h:i A'),
            ]);

        $activeTokens = AdminSecurityToken::with('creator')
            ->where('is_used', false)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'token' => $t->token,
                'token_type' => $t->token_type,
                'role' => $t->role,
                'issued_for_email' => $t->issued_for_email,
                'notes' => $t->notes,
                'created_by_name' => $t->creator?->name ?? 'Admin',
                'expires_at' => $t->expires_at?->format('d M Y, h:i A'),
                'created_at' => $t->created_at?->format('d M Y, h:i A'),
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'pending_users' => $pendingUsers,
            'active_tokens' => $activeTokens,
            'filters' => [
                'role' => $roleFilter,
                'status' => $statusFilter,
                'search' => $search,
            ],
            'stats' => [
                'total_users' => User::count(),
                'pending_approvals' => User::where('approval_status', 'pending')->count(),
                'active_users' => User::where('is_active', true)->count(),
                'total_tokens' => AdminSecurityToken::where('is_used', false)->where('expires_at', '>', now())->count(),
            ],
        ]);
    }

    public function approve(string $userId): RedirectResponse
    {
        $this->authorizeAdmin();

        $user = User::findOrFail($userId);
        $user->update([
            'approval_status' => 'approved',
            'is_active' => true,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        Log::info("GPF Admin: User '{$user->username}' approved and activated by Admin " . Auth::user()->username);

        return back()->with('success', "Officer account '{$user->username}' ({$user->name}) has been approved and activated.");
    }

    public function reject(Request $request, string $userId): RedirectResponse
    {
        $this->authorizeAdmin();

        $user = User::findOrFail($userId);
        $user->update([
            'approval_status' => 'rejected',
            'is_active' => false,
            'admin_notes' => $request->input('notes', 'Registration rejected by administrator.'),
        ]);

        Log::info("GPF Admin: User '{$user->username}' registration rejected by Admin " . Auth::user()->username);

        return back()->with('info', "Registration for '{$user->username}' has been marked as rejected.");
    }

    public function update(Request $request, string $userId): RedirectResponse
    {
        $this->authorizeAdmin();

        $user = User::findOrFail($userId);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', "unique:users,email,{$user->id}"],
            'role' => ['required', 'string', 'in:admin,approver,checker,deo,dispatch,dealing_assistant'],
            'designation' => ['nullable', 'string', 'max:150'],
            'section' => ['nullable', 'string', 'max:100'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'is_active' => ['required', 'boolean'],
            'approval_status' => ['required', 'string', 'in:approved,pending,rejected'],
        ]);

        $user->update($validated);

        return back()->with('success', "User account '{$user->username}' updated successfully.");
    }

    public function resetUserPassword(Request $request, string $userId): RedirectResponse
    {
        $this->authorizeAdmin();

        $user = User::findOrFail($userId);

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        Log::info("GPF Admin: Password reset for user '{$user->username}' by Admin " . Auth::user()->username);

        return back()->with('success', "Password for officer '{$user->username}' has been updated successfully.");
    }

    public function generateToken(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'token_type' => ['required', 'string', 'in:registration,password_reset,all'],
            'role' => ['nullable', 'string', 'in:admin,approver,checker,deo,dispatch,dealing_assistant'],
            'issued_for_email' => ['nullable', 'email'],
            'expiry_days' => ['required', 'integer', 'min:1', 'max:30'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $prefix = match ($validated['token_type']) {
            'registration' => 'ADM-REG-',
            'password_reset' => 'ADM-RST-',
            default => 'ADM-SEC-',
        };

        $tokenStr = $prefix . strtoupper(Str::random(8));

        $token = AdminSecurityToken::create([
            'token' => $tokenStr,
            'token_type' => $validated['token_type'],
            'role' => $validated['role'] ?? null,
            'issued_for_email' => $validated['issued_for_email'] ? strtolower(trim($validated['issued_for_email'])) : null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => Auth::id(),
            'is_used' => false,
            'expires_at' => now()->addDays((int) $validated['expiry_days']),
        ]);

        Log::info("GPF Admin: Generated security token '{$tokenStr}' for type {$validated['token_type']} by Admin " . Auth::user()->username);

        return back()->with('success', "Admin Security Token generated: {$tokenStr} (Valid for {$validated['expiry_days']} days)");
    }

    public function revokeToken(string $tokenId): RedirectResponse
    {
        $this->authorizeAdmin();

        $token = AdminSecurityToken::findOrFail($tokenId);
        $token->delete();

        return back()->with('info', "Security Token '{$token->token}' has been revoked.");
    }
}
