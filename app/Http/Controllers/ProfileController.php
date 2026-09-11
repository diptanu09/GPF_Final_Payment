<?php

namespace App\Http\Controllers;

use App\Enums\CaseWorkflowStatus;
use App\Models\Authority;
use App\Models\DigitalSignature;
use App\Models\InwardCase;
use App\Models\User;
use App\Models\WorkflowHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(): Response
    {
        /** @var User $user */
        $user = Auth::user();

        // Calculate officer activity statistics based on role
        $stats = [
            'total_dockets_created' => InwardCase::where('created_by', $user->id)->count(),
            'total_checks_performed' => WorkflowHistory::where('performed_by', $user->id)
                ->whereIn('to_status', [CaseWorkflowStatus::CHECKED->value, CaseWorkflowStatus::LTA_CHECKED->value])
                ->count(),
            'total_approvals_given' => WorkflowHistory::where('performed_by', $user->id)
                ->whereIn('to_status', [CaseWorkflowStatus::APPROVED->value, CaseWorkflowStatus::LTA_APPROVED->value])
                ->count(),
            'total_signed_authorities' => DigitalSignature::where('signatory_user_id', $user->id)->count(),
        ];

        return Inertia::render('Profile/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'role_label' => $user->roleLabel(),
                'designation' => $user->designation,
                'section' => $user->section,
                'phone_number' => $user->phone_number,
                'approval_status' => $user->approval_status ?? 'approved',
                'is_active' => $user->is_active ?? true,
                'created_at' => $user->created_at?->format('d M Y, h:i A'),
                'approved_at' => $user->approved_at?->format('d M Y, h:i A'),
                'approved_by_name' => $user->approverUser?->name,
            ],
            'stats' => $stats,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'designation' => ['nullable', 'string', 'max:150'],
            'section' => ['nullable', 'string', 'max:100'],
            'phone_number' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'designation' => !empty($validated['designation']) ? trim($validated['designation']) : null,
            'section' => !empty($validated['section']) ? trim($validated['section']) : null,
            'phone_number' => !empty($validated['phone_number']) ? trim($validated['phone_number']) : null,
        ]);

        return back()->with('success', 'Profile details updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Your password has been changed securely.');
    }
}
