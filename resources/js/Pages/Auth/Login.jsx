import React from 'react';
import { useForm, Head } from '@inertiajs/react';
import { Lock, User, ShieldCheck, ArrowRight } from 'lucide-react';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        username: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();
        post('/login');
    };

    const quickFill = (username) => {
        setData({
            username: username,
            password: 'password123',
            remember: true,
        });
    };

    return (
        <div className="min-h-screen bg-slate-950 flex flex-col justify-center items-center px-4 py-12 relative overflow-hidden font-sans">
            <Head title="Sign In - GPF Final Payment Portal" />

            {/* Glowing background gradients */}
            <div className="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[300px] bg-indigo-500/10 blur-[120px] rounded-full pointer-events-none"></div>

            <div className="w-full max-w-md relative z-10">
                {/* Header branding */}
                <div className="text-center mb-8">
                    <div className="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-400 text-white shadow-xl shadow-indigo-500/25 mb-4">
                        <ShieldCheck className="w-8 h-8" />
                    </div>
                    <h1 className="text-2xl font-extrabold tracking-tight text-white">
                        GPF Final Payment Portal
                    </h1>
                    <p className="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                        Office of the Accountant General (A & E), Tripura ::: Agartala
                    </p>
                </div>

                {/* Login card */}
                <div className="glass-panel p-8 rounded-2xl shadow-2xl shadow-black/50 border border-slate-800">
                    <form onSubmit={submit} className="space-y-5">
                        <div>
                            <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                                Username / Institutional Email
                            </label>
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                    <User className="w-4 h-4" />
                                </div>
                                <input
                                    type="text"
                                    value={data.username}
                                    onChange={(e) => setData('username', e.target.value)}
                                    placeholder="e.g. admin, srao, aao, deo"
                                    className="w-full pl-10 pr-4 py-2.5 bg-slate-900/80 border border-slate-700/80 rounded-xl text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                    required
                                />
                            </div>
                            {errors.username && (
                                <p className="mt-1.5 text-xs text-rose-400">{errors.username}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                                Password
                            </label>
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                    <Lock className="w-4 h-4" />
                                </div>
                                <input
                                    type="password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    placeholder="••••••••"
                                    className="w-full pl-10 pr-4 py-2.5 bg-slate-900/80 border border-slate-700/80 rounded-xl text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                    required
                                />
                            </div>
                            {errors.password && (
                                <p className="mt-1.5 text-xs text-rose-400">{errors.password}</p>
                            )}
                        </div>

                        <div className="flex items-center justify-between text-xs">
                            <label className="flex items-center gap-2 cursor-pointer text-slate-400 hover:text-slate-300">
                                <input
                                    type="checkbox"
                                    checked={data.remember}
                                    onChange={(e) => setData('remember', e.target.checked)}
                                    className="rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-950"
                                />
                                <span>Remember my terminal</span>
                            </label>
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white text-sm font-semibold shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 transition disabled:opacity-50"
                        >
                            <span>Authenticate & Proceed</span>
                            <ArrowRight className="w-4 h-4" />
                        </button>
                    </form>

                    {/* Quick Demo Switcher */}
                    <div className="mt-8 pt-6 border-t border-slate-800/80">
                        <p className="text-[11px] font-semibold text-slate-400 uppercase tracking-wider text-center mb-3">
                            Quick Demo Credentials
                        </p>
                        <div className="grid grid-cols-2 gap-2 text-xs">
                            <button
                                type="button"
                                onClick={() => quickFill('admin')}
                                className="px-2.5 py-1.5 rounded-lg bg-slate-800/60 hover:bg-slate-800 border border-slate-700 text-slate-300 text-left transition"
                            >
                                <div className="font-semibold text-indigo-400">admin</div>
                                <div className="text-[10px] text-slate-500">Super Admin</div>
                            </button>
                            <button
                                type="button"
                                onClick={() => quickFill('srao')}
                                className="px-2.5 py-1.5 rounded-lg bg-slate-800/60 hover:bg-slate-800 border border-slate-700 text-slate-300 text-left transition"
                            >
                                <div className="font-semibold text-emerald-400">srao</div>
                                <div className="text-[10px] text-slate-500">Sr. AO (Approver)</div>
                            </button>
                            <button
                                type="button"
                                onClick={() => quickFill('aao')}
                                className="px-2.5 py-1.5 rounded-lg bg-slate-800/60 hover:bg-slate-800 border border-slate-700 text-slate-300 text-left transition"
                            >
                                <div className="font-semibold text-blue-400">aao</div>
                                <div className="text-[10px] text-slate-500">AAO (Checker)</div>
                            </button>
                            <button
                                type="button"
                                onClick={() => quickFill('deo_inward')}
                                className="px-2.5 py-1.5 rounded-lg bg-slate-800/60 hover:bg-slate-800 border border-slate-700 text-slate-300 text-left transition"
                            >
                                <div className="font-semibold text-amber-400">deo_inward</div>
                                <div className="text-[10px] text-slate-500">DEO (Inward)</div>
                            </button>
                        </div>
                    </div>
                </div>

                <p className="text-center text-xs text-slate-600 mt-6">
                    Statutory Institutional System • Restricted to Authorized Officers
                </p>
            </div>
        </div>
    );
}
