import React, { useState, useMemo } from 'react';
import { useForm, Head, Link } from '@inertiajs/react';
import { Lock, User, ArrowRight, Eye, EyeOff, ShieldAlert, KeyRound, UserPlus } from 'lucide-react';
import AuthBackground from '@/Components/Auth/AuthBackground';
import InteractiveTiltCard from '@/Components/Auth/InteractiveTiltCard';
import SecurityShieldAvatar from '@/Components/Auth/SecurityShieldAvatar';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        username: '',
        password: '',
        remember: false,
    });

    const [showPassword, setShowPassword] = useState(false);
    const [focusedField, setFocusedField] = useState(null); // 'username' | 'password' | null

    // Calculate the dynamic state of the security shield hologram avatar
    const avatarState = useMemo(() => {
        if (processing) return 'processing';
        if (errors.username || errors.password) return 'error';
        if (showPassword) return 'peek';
        if (focusedField === 'password') return 'password';
        if (focusedField === 'username') return 'username';
        return 'idle';
    }, [processing, errors, showPassword, focusedField]);

    const submit = (e) => {
        e.preventDefault();
        post('/login');
    };

    return (
        <AuthBackground className="flex flex-col items-center justify-center">
            <Head title="Official Secure Sign In - GPF Final Payment Portal" />

            <div className="w-full max-w-md mx-auto space-y-3.5 sm:space-y-4 my-auto">
                {/* Government Header Branding with Interactive Reactive Avatar */}
                <SecurityShieldAvatar
                    state={avatarState}
                    title="Government of India • CAG"
                    heading="GPF Final Payment Portal"
                    subtitle="Office of the Accountant General (A & E), Tripura ::: Agartala"
                />

                {/* Interactive 3D Tilt Card Container */}
                <InteractiveTiltCard className="p-5 sm:p-7">
                    {/* Header Bar */}
                    <div className="flex items-center justify-between pb-3 mb-4 border-b border-slate-800/80 text-xs font-semibold text-slate-300">
                        <div className="flex items-center gap-2">
                            <KeyRound className="w-4 h-4 text-indigo-400" />
                            <span>Institutional Terminal Access</span>
                        </div>
                        <span className="text-[10px] font-mono px-2 py-0.5 rounded bg-indigo-950/60 border border-indigo-500/30 text-indigo-300">
                            SEC-TLS v1.3
                        </span>
                    </div>

                    <form onSubmit={submit} className="space-y-3.5">
                        {/* Username / Email Field */}
                        <div className="space-y-1.5">
                            <div className="flex items-center justify-between">
                                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                                    Officer Username / Email ID
                                </label>
                                <Link
                                    href="/forgot-username"
                                    className="text-[11px] text-indigo-400 hover:text-indigo-300 transition hover:underline"
                                >
                                    Forgot username?
                                </Link>
                            </div>
                            <div className="relative group">
                                <div
                                    className={`absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-all duration-200 ${
                                        focusedField === 'username'
                                            ? 'text-cyan-400 scale-110'
                                            : 'text-slate-500'
                                    }`}
                                >
                                    <User className="w-4 h-4" />
                                </div>
                                <input
                                    type="text"
                                    value={data.username}
                                    onFocus={() => setFocusedField('username')}
                                    onBlur={() => setFocusedField(null)}
                                    onChange={(e) => setData('username', e.target.value)}
                                    placeholder="Enter registered officer username"
                                    className={`w-full pl-10 pr-4 py-2.5 bg-slate-950/80 border rounded-xl text-sm text-slate-100 placeholder-slate-500 focus:outline-none transition-all duration-200 ${
                                        focusedField === 'username'
                                            ? 'border-cyan-500/70 ring-2 ring-cyan-500/20 shadow-[0_0_15px_rgba(6,182,212,0.15)]'
                                            : 'border-slate-800 hover:border-slate-700'
                                    }`}
                                    autoComplete="username"
                                    autoFocus
                                    required
                                />
                            </div>
                            {errors.username && (
                                <div className="p-2.5 rounded-lg bg-rose-500/10 border border-rose-500/20 flex items-start gap-2 text-rose-300 text-xs animate-micro-shake">
                                    <ShieldAlert className="w-4 h-4 flex-shrink-0 text-rose-400 mt-0.5" />
                                    <span>{errors.username}</span>
                                </div>
                            )}
                        </div>

                        {/* Password Field */}
                        <div className="space-y-1.5">
                            <div className="flex items-center justify-between">
                                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                                    Institutional Password
                                </label>
                                <Link
                                    href="/forgot-password"
                                    className="text-[11px] text-indigo-400 hover:text-indigo-300 transition hover:underline"
                                >
                                    Forgot password?
                                </Link>
                            </div>
                            <div className="relative group">
                                <div
                                    className={`absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-all duration-200 ${
                                        focusedField === 'password'
                                            ? 'text-indigo-400 scale-110'
                                            : 'text-slate-500'
                                    }`}
                                >
                                    <Lock className="w-4 h-4" />
                                </div>
                                <input
                                    type={showPassword ? 'text' : 'password'}
                                    value={data.password}
                                    onFocus={() => setFocusedField('password')}
                                    onBlur={() => setFocusedField(null)}
                                    onChange={(e) => setData('password', e.target.value)}
                                    placeholder="Enter institutional password"
                                    className={`w-full pl-10 pr-11 py-2.5 bg-slate-950/80 border rounded-xl text-sm text-slate-100 placeholder-slate-500 focus:outline-none transition-all duration-200 font-mono ${
                                        focusedField === 'password'
                                            ? 'border-indigo-500/70 ring-2 ring-indigo-500/20 shadow-[0_0_15px_rgba(99,102,241,0.15)]'
                                            : 'border-slate-800 hover:border-slate-700'
                                    }`}
                                    autoComplete="current-password"
                                    required
                                />
                                <button
                                    type="button"
                                    onClick={() => setShowPassword(!showPassword)}
                                    tabIndex={-1}
                                    title={showPassword ? 'Mask password' : 'Peek password'}
                                    className="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300 transition active:scale-90"
                                >
                                    {showPassword ? (
                                        <EyeOff className="w-4 h-4 text-amber-400" />
                                    ) : (
                                        <Eye className="w-4 h-4 hover:text-slate-300" />
                                    )}
                                </button>
                            </div>
                            {errors.password && (
                                <p className="text-xs text-rose-400 animate-micro-shake">{errors.password}</p>
                            )}
                        </div>

                        {/* Remember Terminal */}
                        <div className="flex items-center justify-between text-xs pt-0.5">
                            <label className="flex items-center gap-2 cursor-pointer text-slate-400 hover:text-slate-300 transition">
                                <input
                                    type="checkbox"
                                    checked={data.remember}
                                    onChange={(e) => setData('remember', e.target.checked)}
                                    className="rounded bg-slate-950 border-slate-700 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-950 transition"
                                />
                                <span>Remember authentication terminal</span>
                            </label>
                        </div>

                        {/* Submit Button with Dynamic Lighting and Loading Ring */}
                        <button
                            type="submit"
                            disabled={processing}
                            className="relative group w-full py-2.5 px-4 rounded-xl text-white text-sm font-semibold shadow-xl shadow-indigo-600/25 overflow-hidden transition-all duration-200 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed mt-2 bg-gradient-to-r from-indigo-600 via-indigo-500 to-indigo-600 hover:from-indigo-500 hover:to-indigo-500 cursor-pointer"
                        >
                            {/* Animated Glowing Light Sweep */}
                            <div className="absolute inset-0 -translate-x-full group-hover:translate-x-full transition-transform duration-1000 bg-gradient-to-r from-transparent via-white/15 to-transparent pointer-events-none" />

                            <div className="relative flex items-center justify-center gap-2">
                                {processing ? (
                                    <>
                                        <span className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                                        <span>Verifying Credentials...</span>
                                    </>
                                ) : (
                                    <>
                                        <span>Authenticate & Enter Portal</span>
                                        <ArrowRight className="w-4 h-4 transition-transform group-hover:translate-x-1" />
                                    </>
                                )}
                            </div>
                        </button>
                    </form>

                    {/* Registration Option Link */}
                    <div className="mt-4 pt-3.5 border-t border-slate-800/80 text-center">
                        <p className="text-xs text-slate-400">
                            Need an institutional officer account?{' '}
                            <Link
                                href="/register"
                                className="text-indigo-400 hover:text-indigo-300 font-semibold inline-flex items-center gap-1 hover:underline transition"
                            >
                                <UserPlus className="w-3.5 h-3.5" />
                                <span>Register Officer</span>
                            </Link>
                        </p>
                    </div>

                    {/* Government Portal Security Badges */}
                    <div className="mt-3.5 pt-3 border-t border-slate-800/60 flex flex-col gap-1 text-[11px] text-slate-400 text-center">
                        <div className="flex items-center justify-center gap-4 text-[10px] text-slate-500 font-medium">
                            <span className="flex items-center gap-1.5">
                                <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" />
                                256-Bit TLS Encryption
                            </span>
                            <span>•</span>
                            <span className="flex items-center gap-1.5">
                                <span className="w-1.5 h-1.5 rounded-full bg-indigo-500" />
                                Digital Audit Trail
                            </span>
                        </div>
                        <p className="text-[10px] text-slate-500/80">
                            Authorized personnel only. All access attempts are timestamped and logged.
                        </p>
                    </div>
                </InteractiveTiltCard>
            </div>
        </AuthBackground>
    );
}
