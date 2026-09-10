<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Integration\OracleMasterBridge;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
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

        return Inertia::render('Auth/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string'],
        ]);

        $inputUser = strtolower(trim($credentials['username']));
        $inputPassword = trim($credentials['password']);
        $remember = $request->boolean('remember');

        // 1. Rate Limiting: Max 5 failed attempts per minute per username + IP
        $throttleKey = Str::transliterate($inputUser . '|' . $request->ip());
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            Log::warning("GPF Auth: Rate limit lockout for user '{$inputUser}' from IP {$request->ip()}. Lockout time: {$seconds}s.");

            return back()->withErrors([
                'username' => "Too many failed login attempts. For security, please try again in {$seconds} seconds.",
            ])->onlyInput('username');
        }

        // 2. Find user in PostgreSQL users table (case-insensitive username or email)
        $user = User::whereRaw('LOWER(username) = ?', [$inputUser])
            ->orWhereRaw('LOWER(email) = ?', [$inputUser])
            ->first();

        // 3. If not found in PostgreSQL, check live Oracle gpffp.USER_ACCOUNTS / local user_accounts table
        if (!$user) {
            $user = $this->lookupAndSyncOracleUser($inputUser);
        }

        if ($user) {
            // 4. Verify account active status
            if (!$user->is_active) {
                RateLimiter::hit($throttleKey, 60);
                Log::warning("GPF Auth: Deactivated account login attempt: '{$inputUser}' from IP {$request->ip()}.");

                return back()->withErrors([
                    'username' => 'This institutional account has been deactivated. Please contact the System Administrator.',
                ])->onlyInput('username');
            }

            // 5. Verify password strictly via Hash::check or live Oracle credential sync
            $passwordMatches = Hash::check($inputPassword, $user->password);

            if (!$passwordMatches) {
                $conn = $this->oracleBridge->getConnection();
                if ($conn) {
                    $stmt = oci_parse($conn, "SELECT PASSWORD FROM gpffp.USER_ACCOUNTS WHERE LOWER(TRIM(USERNAME)) = :usr AND ROWNUM = 1");
                    $targetUsername = $user->username;
                    oci_bind_by_name($stmt, ':usr', $targetUsername);
                    if (@oci_execute($stmt)) {
                        $row = oci_fetch_assoc($stmt);
                        oci_free_statement($stmt);
                        if ($row && !empty($row['PASSWORD']) && trim($row['PASSWORD']) === $inputPassword) {
                            $user->password = Hash::make($inputPassword);
                            $user->save();
                            $passwordMatches = true;
                        }
                    }
                }
            }

            if ($passwordMatches) {
                RateLimiter::clear($throttleKey);
                Auth::login($user, $remember);
                $request->session()->regenerate();

                Log::info("GPF Auth: User '{$user->username}' ({$user->role}) authenticated successfully from IP {$request->ip()}.");

                return redirect()->intended(route('dashboard'))->with('success', "Welcome back, {$user->name} ({$user->roleLabel()})!");
            }
        }

        // 6. Record failed attempt and apply rate limit penalty
        RateLimiter::hit($throttleKey, 60);
        Log::warning("GPF Auth: Invalid login credentials for '{$inputUser}' from IP {$request->ip()}.");

        return back()->withErrors([
            'username' => 'Invalid username or password. Please verify your institutional credentials.',
        ])->onlyInput('username');
    }

    public function logout(Request $request): RedirectResponse
    {
        $username = Auth::user()?->username ?? 'Unknown';
        Log::info("GPF Auth: User '{$username}' logged out from IP {$request->ip()}.");

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Logged out securely.');
    }

    public function showRegister(): Response|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/Register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', 'in:admin,approver,checker,deo,dispatch,dealing_assistant'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => trim($validated['name']),
            'username' => strtolower(trim($validated['username'])),
            'email' => strtolower(trim($validated['email'])),
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        Log::info("GPF Auth: New institutional user registered: '{$user->username}' ({$user->role}) by IP {$request->ip()}.");

        return redirect()->route('login')->with('success', "Account registered successfully for {$user->name} ({$user->username}). You may now log in.");
    }

    public function showForgotPassword(): Response|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/ForgotPassword');
    }

    public function sendPasswordResetToken(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string'],
        ]);

        $identifier = strtolower(trim($validated['identifier']));

        $user = User::whereRaw('LOWER(email) = ?', [$identifier])
            ->orWhereRaw('LOWER(username) = ?', [$identifier])
            ->first();

        if (!$user) {
            return back()->withErrors([
                'identifier' => 'No account found matching this email or username.',
            ])->onlyInput('identifier');
        }

        // Generate a secure random token
        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        Log::info("GPF Auth: Password reset initiated for '{$user->username}' ({$user->email}) from IP {$request->ip()}.");

        return redirect()->route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ])->with('success', "Security verification token generated for {$user->email}. Please set your new password below.");
    }

    public function showResetPassword(Request $request): Response|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/ResetPassword', [
            'email' => $request->query('email', ''),
            'token' => $request->query('token', ''),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $reset = DB::table('password_reset_tokens')
            ->where('email', strtolower(trim($validated['email'])))
            ->first();

        if (!$reset || !Hash::check($validated['token'], $reset->token)) {
            return back()->withErrors([
                'token' => 'Invalid or expired password reset security token.',
            ]);
        }

        // Check if token expired (1 hour limit)
        if (Carbon::parse($reset->created_at)->addHour()->isPast()) {
            DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();
            return redirect()->route('password.request')->withErrors([
                'identifier' => 'Password reset token has expired. Please request a new one.',
            ]);
        }

        $user = User::where('email', strtolower(trim($validated['email'])))->first();
        if (!$user) {
            return back()->withErrors(['email' => 'User account not found.']);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

        Log::info("GPF Auth: Password successfully reset for user '{$user->username}' from IP {$request->ip()}.");

        return redirect()->route('login')->with('success', "Password successfully updated for {$user->username}! Please log in with your new password.");
    }

    public function showForgotUsername(): Response|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/ForgotUsername');
    }

    public function recoverUsername(Request $request): Response|RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'No account found associated with this email address.',
            ])->onlyInput('email');
        }

        Log::info("GPF Auth: Username recovery requested for '{$email}' from IP {$request->ip()}.");

        return Inertia::render('Auth/ForgotUsername', [
            'recovered_username' => $user->username,
            'recovered_name' => $user->name,
            'recovered_role' => $user->roleLabel(),
            'searched_email' => $email,
        ]);
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

                    $isActive = strtoupper(trim($row['USER_STATUS'] ?? 'Y')) === 'Y';
                    $plainPass = trim($row['PASSWORD'] ?? '');

                    return User::create([
                        'username' => strtolower(trim($row['USERNAME'])),
                        'name' => trim($row['FULL_NAME']) ?: ucfirst($username),
                        'email' => strtolower(trim($row['USERNAME'])) . '@tripura.gov.in',
                        'role' => $role,
                        'password' => Hash::make($plainPass),
                        'is_active' => $isActive,
                    ]);
                }
            }
        }

        return null;
    }
}
