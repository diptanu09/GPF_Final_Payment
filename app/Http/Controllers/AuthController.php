<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Integration\OracleMasterBridge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function __construct(protected OracleMasterBridge $oracleBridge) {}

    public function showLogin(): Response|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        // Fetch real active users to display in the login panel quick selector
        $realUsers = User::where('is_active', true)
            ->select('username', 'name', 'role')
            ->orderBy('name', 'asc')
            ->get();

        return Inertia::render('Auth/Login', [
            'real_users' => $realUsers,
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $inputUser = strtolower(trim($credentials['username']));
        $inputPassword = trim($credentials['password']);
        $remember = $request->boolean('remember');

        // 1. Find user in PostgreSQL users table (case-insensitive)
        $user = User::whereRaw('LOWER(username) = ?', [$inputUser])
            ->orWhereRaw('LOWER(email) = ?', [$inputUser])
            ->first();

        // 2. If not found in PostgreSQL, check live Oracle gpffp.USER_ACCOUNTS / local user_accounts table
        if (!$user) {
            $user = $this->lookupAndSyncOracleUser($inputUser);
        }

        if ($user) {
            // Verify password via standard Hash::check
            $passwordMatches = Hash::check($inputPassword, $user->password);

            // If not matched, check if password matches default 'password' or raw password in user_accounts table
            if (!$passwordMatches) {
                $rawOracleAcc = DB::table('user_accounts')
                    ->whereRaw('LOWER(username) = ?', [$user->username])
                    ->first();

                if ($inputPassword === 'password' || ($rawOracleAcc && $rawOracleAcc->password === $inputPassword)) {
                    // Update user's password to bcrypt hash
                    $user->password = Hash::make($inputPassword);
                    $user->save();
                    $passwordMatches = true;
                }
            }

            if ($passwordMatches) {
                Auth::login($user, $remember);
                $request->session()->regenerate();
                return redirect()->intended(route('dashboard'))->with('success', "Welcome back, {$user->name} ({$user->roleLabel()})!");
            }
        }

        return back()->withErrors([
            'username' => 'Invalid username or password. Please verify your Oracle credentials.',
        ])->onlyInput('username');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Logged out successfully.');
    }

    /**
     * Fallback lookup in Oracle gpffp.USER_ACCOUNTS
     */
    protected function lookupAndSyncOracleUser(string $username): ?User
    {
        $conn = $this->oracleBridge->getConnection();
        if ($conn) {
            $stmt = oci_parse($conn, "SELECT * FROM gpffp.USER_ACCOUNTS WHERE LOWER(TRIM(USERNAME)) = :usr AND ROWNUM = 1");
            oci_bind_by_name($stmt, ':usr', $username);
            if (@oci_execute($stmt)) {
                $row = oci_fetch_assoc($stmt);
                oci_free_statement($stmt);
                if ($row) {
                    $oracleRole = (int) ($row['USER_ROLE'] ?? 1);
                    $role = match ($oracleRole) {
                        1 => 'admin',
                        2 => 'deo',
                        3 => 'checker',
                        4 => 'approver',
                        5 => 'dispatch',
                        default => 'deo',
                    };

                    return User::create([
                        'username' => strtolower(trim($row['USERNAME'])),
                        'name' => trim($row['FULL_NAME']) ?: ucfirst($username),
                        'email' => strtolower(trim($row['USERNAME'])) . '@tripura.gov.in',
                        'role' => $role,
                        'password' => Hash::make(trim($row['PASSWORD']) ?: 'password'),
                        'is_active' => true,
                    ]);
                }
            }
        }

        return null;
    }
}
