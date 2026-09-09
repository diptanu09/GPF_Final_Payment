import React, { useState } from 'react';
import { useForm, Head } from '@inertiajs/react';
import { Lock, User, ShieldCheck, ArrowRight, Eye, EyeOff, ShieldAlert, KeyRound } from 'lucide-react';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        username: '',
        password: '',
        remember: false,
    });

    const [showPassword, setShowPassword] = useState(false);

    const submit = (e) => {
        e.preventDefault();
        post('/login');
    };

    return (
        <div className="min-h-screen bg-slate-950 flex flex-col justify-center items-center px-4 py-12 relative overflow-hidden font-sans select-none">
            <Head title="Official Secure Sign In - GPF Final Payment Portal" />

            {/* Ambient background glows */}
            <div className="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[650px] h-[350px] bg-indigo-600/10 blur-[130px] rounded-full pointer-events-none"></div>
            <div className="absolute bottom-1/4 left-1/3 w-[450px] h-[250px] bg-emerald-600/5 blur-[120px] rounded-full pointer-events-none"></div>

            <div className="w-full max-w-md relative z-10 space-y-6">
                {/* Government Header Branding */}
                <div className="text-center space-y-2">
                    <div className="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-700 via-indigo-600 to-indigo-500 text-white shadow-xl shadow-indigo-600/20 mb-2 border border-indigo-400/30">
                        <ShieldCheck className="w-8 h-8" />
                    </div>
                    <div className="space-y-0.5">
                        <p className="text-[11px] font-bold tracking-widest text-indigo-400 uppercase">
                            Government of India • CAG
                        </p>
                        <h1 className="text-xl font-extrabold tracking-tight text-white sm:text-2xl">
                            GPF Final Payment Portal
                        </h1>
                        <p className="text-xs text-slate-400 font-medium">
                            Office of the Accountant General (A & E), Tripura ::: Agartala
                        </p>
                    </div>
                </div>

                {/* Login Card */}
                <div className="glass-panel p-7 sm:p-8 rounded-2xl shadow-2xl shadow-black/60 border border-slate-800/80 backdrop-blur-xl bg-slate-900/70">
                    <div className="flex items-center gap-2 pb-4 mb-5 border-b border-slate-800/70 text-xs font-semibold text-slate-300">
                        <KeyRound className="w-4 h-4 text-indigo-400" />
                        <span>Institutional Authentication</span>
                    </div>

                    <form onSubmit={submit} className="space-y-4">
                        {/* Username / Email */}
                        <div>
                            <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Officer Username / Email ID
                            </label>
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                    <User className="w-4 h-4" />
                                </div>
                                <input
                                    type="text"
                                    value={data.username}
                                    onChange={(e) => setData('username', e.target.value)}
                                    placeholder="Enter your registered username (e.g. rkdb, anjana, dir)"
                                    className="w-full pl-10 pr-4 py-2.5 bg-slate-950/70 border border-slate-700/80 rounded-xl text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                    autoComplete="username"
                                    autoFocus
                                    required
                                />
                            </div>
                            {errors.username && (
                                <div className="mt-2 p-2.5 rounded-lg bg-rose-500/10 border border-rose-500/20 flex items-start gap-2 text-rose-300 text-xs">
                                    <ShieldAlert className="w-4 h-4 flex-shrink-0 text-rose-400 mt-0.5" />
                                    <span>{errors.username}</span>
                                </div>
                            )}
                        </div>

                        {/* Password */}
                        <div>
                            <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Institutional Password
                            </label>
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                    <Lock className="w-4 h-4" />
                                </div>
                                <input
                                    type={showPassword ? 'text' : 'password'}
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    placeholder="••••••••"
                                    className="w-full pl-10 pr-11 py-2.5 bg-slate-950/70 border border-slate-700/80 rounded-xl text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition font-mono"
                                    autoComplete="current-password"
                                    required
                                />
                                <button
                                    type="button"
                                    onClick={() => setShowPassword(!showPassword)}
                                    tabIndex={-1}
                                    className="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300 transition"
                                >
                                    {showPassword ? (
                                        <EyeOff className="w-4 h-4" />
                                    ) : (
                                        <Eye className="w-4 h-4" />
                                    )}
                                </button>
                            </div>
                            {errors.password && (
                                <p className="mt-1.5 text-xs text-rose-400">{errors.password}</p>
                            )}
                        </div>

                        {/* Remember Me */}
                        <div className="flex items-center justify-between text-xs pt-1">
                            <label className="flex items-center gap-2 cursor-pointer text-slate-400 hover:text-slate-300 transition">
                                <input
                                    type="checkbox"
                                    checked={data.remember}
                                    onChange={(e) => setData('remember', e.target.checked)}
                                    className="rounded bg-slate-950 border-slate-700 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-950"
                                />
                                <span>Remember authentication terminal</span>
                            </label>
                        </div>

                        {/* Submit Button */}
                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white text-sm font-semibold shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 transition disabled:opacity-50 mt-2"
                        >
                            {processing ? (
                                <span className="flex items-center gap-2">
                                    <span className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                    <span>Verifying Credentials...</span>
                                </span>
                            ) : (
                                <>
                                    <span>Authenticate & Enter Portal</span>
                                    <ArrowRight className="w-4 h-4" />
                                </>
                            )}
                        </button>
                    </form>

                    {/* Government Portal Security Badges */}
                    <div className="mt-6 pt-5 border-t border-slate-800/80 flex flex-col gap-2 text-[11px] text-slate-400 text-center">
                        <div className="flex items-center justify-center gap-4 text-[10px] text-slate-500 font-medium">
                            <span className="flex items-center gap-1">
                                <span className="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                256-Bit TLS Encryption
                            </span>
                            <span>•</span>
                            <span className="flex items-center gap-1">
                                <span className="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                Rate-Limited Protection
                            </span>
                        </div>
                        <p className="text-[10px] text-slate-500/80 mt-1">
                            Authorized personnel only. All access attempts and transaction logs are digitally audited.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}
