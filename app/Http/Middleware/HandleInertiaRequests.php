<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'designation' => $user->designation,
                    'section' => $user->section,
                    'phone_number' => $user->phone_number,
                    'approval_status' => $user->approval_status,
                    'role' => $user->role,
                    'role_label' => $user->roleLabel(),
                    'is_super_admin' => $user->isSuperAdmin(),
                    'is_approver' => $user->isApprover(),
                    'is_checker' => $user->isChecker(),
                    'is_da' => $user->isDealingAssistant(),
                ] : null,
                'pending_users_count' => fn () => ($user && $user->isSuperAdmin()) 
                    ? \App\Models\User::where('approval_status', 'pending')->count() 
                    : 0,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
            ],
            'app_name' => config('app.name', 'GPF Final Payment Portal'),
            'office_name' => config('gpf.authority.office_name_en'),
        ];
    }
}
