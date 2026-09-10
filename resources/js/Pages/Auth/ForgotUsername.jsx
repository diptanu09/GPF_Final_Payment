import React from 'react';
import { useForm, Head, Link } from '@inertiajs/react';
import { UserCheck, Mail, ArrowRight, ArrowLeft, ShieldAlert, ShieldCheck, Copy, Check } from 'lucide-react';

export default function ForgotUsername({ recovered_username, recovered_name, recovered_role, searched_email }) {
    const { data, setData, post, processing, errors } = useForm({
        email: searched_email || '',
    });

    const [copied, setCopied] = React.useState(false);

    const submit = (e) => {
        e.preventDefault();
        post('/forgot-username');
    };

    const copyUsername = () => {
        if (recovered_username) {
            navigator.clipboard.writeText(recovered_username);
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        }
    };

    return (
        <div className="min-h-screen bg-slate-950 flex flex-col justify-center items-center px-4 py-12 relative overflow-hidden font-sans select-none">
            <Head title="Recover Username - GPF Final Payment Portal" />

            {/* Ambient background glows */}
            <div className="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[650px] h-[350px] bg-cyan-600/10 blur-[130px] rounded-full pointer-events-none"></div>

            <div className="w-full max-w-md relative z-10 space-y-6">
                {/* Government Header Branding */}
                <div className="text-center space-y-2">
                    <div className="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-cyan-700 via-cyan-600 to-teal-500 text-white shadow-xl shadow-cyan-600/20 mb-2 border border-cyan-400/30">
                        <UserCheck className="w-7 h-7" />
                    </div>
                    <div className="space-y-0.5">
                        <p className="text-[11px] font-bold tracking-widest text-cyan-400 uppercase">
                            Government of India • CAG
                        </p>
                        <h1 className="text-xl font-extrabold tracking-tight text-white sm:text-2xl">
                            Username Lookup
                        </h1>
                        <p className="text-xs text-slate-400 font-medium">
                            Office of the Accountant General (A & E), Tripura ::: Agartala
                        </p>
                    </div>
                </div>

                {/* Main Card */}
                <div className="glass-panel p-7 sm:p-8 rounded-2xl shadow-2xl shadow-black/60 border border-slate-800/80 backdrop-blur-xl bg-slate-900/70">
                    <div className="flex items-center justify-between pb-4 mb-4 border-b border-slate-800/70 text-xs font-semibold text-slate-300">
                        <span className="flex items-center gap-2">
                            <ShieldCheck className="w-4 h-4 text-cyan-400" />
                            <span>Official Username Retrieval</span>
                        </span>
                        <Link
                            href="/login"
                            className="text-indigo-400 hover:text-indigo-300 transition flex items-center gap-1 text-[11px]"
                        >
                            <ArrowLeft className="w-3.5 h-3.5" />
                            <span>Back to Sign In</span>
                        </Link>
                    </div>

                    {recovered_username ? (
                        /* Recovered Username Box */
                        <div className="space-y-4">
                            <div className="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-slate-200 text-xs space-y-2">
                                <div className="flex items-center gap-2 text-emerald-400 font-semibold">
                                    <ShieldCheck className="w-4 h-4" />
                                    <span>Account Located Successfully</span>
                                </div>
                                <div className="text-slate-300">
                                    Officer Name: <strong className="text-white">{recovered_name}</strong>
                                </div>
                                <div className="text-slate-300">
                                    Designation / Role: <strong className="text-slate-200">{recovered_role}</strong>
                                </div>
                                <div className="pt-2 border-t border-emerald-500/20 flex items-center justify-between">
                                    <div>
                                        <span className="text-[11px] text-slate-400">Registered Username:</span>
                                        <div className="text-base font-bold font-mono text-emerald-300">
                                            {recovered_username}
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={copyUsername}
                                        className="p-2 rounded-lg bg-emerald-950/80 border border-emerald-700/60 text-emerald-300 hover:text-white transition flex items-center gap-1 text-xs"
                                    >
                                        {copied ? <Check className="w-3.5 h-3.5" /> : <Copy className="w-3.5 h-3.5" />}
                                        <span>{copied ? 'Copied' : 'Copy'}</span>
                                    </button>
                                </div>
                            </div>

                            <Link
                                href={`/login`}
                                className="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white text-sm font-semibold shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 transition"
                            >
                                <span>Proceed to Sign In</span>
                                <ArrowRight className="w-4 h-4" />
                            </Link>
                        </div>
                    ) : (
                        /* Recovery Lookup Form */
                        <form onSubmit={submit} className="space-y-4">
                            <p className="text-xs text-slate-400 leading-relaxed">
                                Enter your official institutional <strong>Email Address</strong> to find your registered login username.
                            </p>

                            <div>
                                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                    Registered Email Address <span className="text-rose-400">*</span>
                                </label>
                                <div className="relative">
                                    <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                        <Mail className="w-4 h-4" />
                                    </div>
                                    <input
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        placeholder="officer@tripura.gov.in"
                                        className="w-full pl-10 pr-4 py-2.5 bg-slate-950/70 border border-slate-700/80 rounded-xl text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition font-mono"
                                        required
                                        autoFocus
                                    />
                                </div>
                                {errors.email && (
                                    <div className="mt-2 p-2.5 rounded-lg bg-rose-500/10 border border-rose-500/20 flex items-start gap-2 text-rose-300 text-xs">
                                        <ShieldAlert className="w-4 h-4 flex-shrink-0 text-rose-400 mt-0.5" />
                                        <span>{errors.email}</span>
                                    </div>
                                )}
                            </div>

                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full py-2.5 px-4 rounded-xl bg-cyan-600 hover:bg-cyan-500 active:bg-cyan-700 text-white text-sm font-semibold shadow-lg shadow-cyan-600/30 flex items-center justify-center gap-2 transition disabled:opacity-50 mt-2"
                            >
                                {processing ? (
                                    <span className="flex items-center gap-2">
                                        <span className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                        <span>Searching Account...</span>
                                    </span>
                                ) : (
                                    <>
                                        <span>Find My Username</span>
                                        <ArrowRight className="w-4 h-4" />
                                    </>
                                )}
                            </button>
                        </form>
                    )}

                    {/* Footer */}
                    <div className="mt-6 pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                        <Link href="/forgot-password" className="text-slate-400 hover:text-white transition">
                            Forgot your password?
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
