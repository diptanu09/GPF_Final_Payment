import React from 'react';
import { useForm, Head, Link } from '@inertiajs/react';
import { KeyRound, Mail, ArrowRight, ArrowLeft, ShieldAlert, ShieldCheck } from 'lucide-react';

export default function ForgotPassword() {
    const { data, setData, post, processing, errors } = useForm({
        identifier: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post('/forgot-password');
    };

    return (
        <div className="min-h-screen bg-slate-950 flex flex-col justify-center items-center px-4 py-12 relative overflow-hidden font-sans select-none">
            <Head title="Forgot Password - GPF Final Payment Portal" />

            {/* Ambient background glows */}
            <div className="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[650px] h-[350px] bg-amber-600/10 blur-[130px] rounded-full pointer-events-none"></div>
            <div className="absolute bottom-1/4 left-1/3 w-[450px] h-[250px] bg-indigo-600/5 blur-[120px] rounded-full pointer-events-none"></div>

            <div className="w-full max-w-md relative z-10 space-y-6">
                {/* Government Header Branding */}
                <div className="text-center space-y-2">
                    <div className="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-amber-600 via-amber-500 to-amber-400 text-white shadow-xl shadow-amber-600/20 mb-2 border border-amber-400/30">
                        <KeyRound className="w-7 h-7" />
                    </div>
                    <div className="space-y-0.5">
                        <p className="text-[11px] font-bold tracking-widest text-amber-400 uppercase">
                            Government of India • CAG
                        </p>
                        <h1 className="text-xl font-extrabold tracking-tight text-white sm:text-2xl">
                            Password Recovery
                        </h1>
                        <p className="text-xs text-slate-400 font-medium">
                            Office of the Accountant General (A & E), Tripura ::: Agartala
                        </p>
                    </div>
                </div>

                {/* Form Card */}
                <div className="glass-panel p-7 sm:p-8 rounded-2xl shadow-2xl shadow-black/60 border border-slate-800/80 backdrop-blur-xl bg-slate-900/70">
                    <div className="flex items-center justify-between pb-4 mb-4 border-b border-slate-800/70 text-xs font-semibold text-slate-300">
                        <span className="flex items-center gap-2">
                            <ShieldCheck className="w-4 h-4 text-amber-400" />
                            <span>Security Verification</span>
                        </span>
                        <Link
                            href="/login"
                            className="text-indigo-400 hover:text-indigo-300 transition flex items-center gap-1 text-[11px]"
                        >
                            <ArrowLeft className="w-3.5 h-3.5" />
                            <span>Back to Sign In</span>
                        </Link>
                    </div>

                    <p className="text-xs text-slate-400 mb-5 leading-relaxed">
                        Enter your registered official <strong>Email Address</strong> or <strong>Officer Username</strong>. We will verify your credentials and guide you through setting a new secure password.
                    </p>

                    <form onSubmit={submit} className="space-y-4">
                        <div>
                            <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Username or Email Address <span className="text-rose-400">*</span>
                            </label>
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                    <Mail className="w-4 h-4" />
                                </div>
                                <input
                                    type="text"
                                    value={data.identifier}
                                    onChange={(e) => setData('identifier', e.target.value)}
                                    placeholder="e.g. rkdb or officer@tripura.gov.in"
                                    className="w-full pl-10 pr-4 py-2.5 bg-slate-950/70 border border-slate-700/80 rounded-xl text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
                                    required
                                    autoFocus
                                />
                            </div>
                            {errors.identifier && (
                                <div className="mt-2 p-2.5 rounded-lg bg-rose-500/10 border border-rose-500/20 flex items-start gap-2 text-rose-300 text-xs">
                                    <ShieldAlert className="w-4 h-4 flex-shrink-0 text-rose-400 mt-0.5" />
                                    <span>{errors.identifier}</span>
                                </div>
                            )}
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full py-2.5 px-4 rounded-xl bg-amber-600 hover:bg-amber-500 active:bg-amber-700 text-white text-sm font-semibold shadow-lg shadow-amber-600/30 flex items-center justify-center gap-2 transition disabled:opacity-50 mt-2"
                        >
                            {processing ? (
                                <span className="flex items-center gap-2">
                                    <span className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                    <span>Verifying Account...</span>
                                </span>
                            ) : (
                                <>
                                    <span>Generate Security Reset Token</span>
                                    <ArrowRight className="w-4 h-4" />
                                </>
                            )}
                        </button>
                    </form>

                    {/* Additional Links */}
                    <div className="mt-6 pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                        <Link href="/forgot-username" className="text-slate-400 hover:text-white transition">
                            Forgot your username?
                        </Link>
                        <Link href="/register" className="text-indigo-400 hover:text-indigo-300 font-semibold">
                            Register new user
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
}
