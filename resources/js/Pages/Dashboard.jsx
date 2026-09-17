import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import ThreeDashboardGlobe from '@/Components/Visuals/ThreeDashboardGlobe';
import AnimatedCounter from '@/Components/UI/AnimatedCounter';
import {
    FilePlus,
    FileText,
    Clock,
    IndianRupee,
    AlertCircle,
    ArrowUpRight,
    TrendingUp,
    ShieldAlert,
    CheckCircle2,
    Calendar,
    ChevronRight,
    Search,
    Layers,
    Sparkles
} from 'lucide-react';
import audioFeedback from '@/Services/AudioFeedbackService';

export default function Dashboard({ metrics = {}, aging = {}, recent_cases = [], recent_histories = [] }) {
    const totalRegistered = metrics?.registered ?? 0;
    const totalSettled = metrics?.settled ?? 0;
    const totalPending = metrics?.pending ?? 0;
    const totalAuthorizedAmount = metrics?.authorized_amount ?? 0;

    return (
        <AuthenticatedLayout title="Executive Dashboard">
            <Head title="Executive Dashboard - GPF Final Payment Portal" />

            <div className="space-y-4 sm:space-y-5">
                {/* 3D Executive Compact Hero Card */}
                <div className="rounded-2xl relative overflow-hidden bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-indigo-500/20 p-4 sm:p-5 shadow-sm dark:shadow-2xl">
                    {/* Top Accent Gradient Stripe */}
                    <div className="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-600 via-blue-600 to-cyan-500" />
                    
                    {/* Ambient Radial Lights */}
                    <div className="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-indigo-500/5 dark:bg-indigo-500/10 blur-3xl pointer-events-none" />
                    <div className="absolute -left-16 -bottom-16 w-64 h-64 rounded-full bg-cyan-500/5 dark:bg-cyan-500/10 blur-3xl pointer-events-none" />

                    <div className="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">
                        <div className="lg:col-span-8 space-y-2.5">
                            <div className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/20 text-indigo-700 dark:text-indigo-300 text-[11px] font-semibold">
                                <Sparkles className="w-3 h-3 text-indigo-600 dark:text-indigo-400" />
                                <span>CAG Tripura Settlement Ledger &bull; Real-Time</span>
                            </div>

                            <h2 className="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white leading-tight">
                                GPF Final Payment <span className="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-blue-600 dark:from-indigo-400 dark:to-cyan-400">Executive Console</span>
                            </h2>

                            <p className="text-xs text-slate-600 dark:text-slate-300 max-w-xl leading-relaxed font-normal">
                                Live statutory workflow pipeline, Central GPF Rule 11(4) delay interest monitoring, and PKI hardware token authorization.
                            </p>

                            <div className="flex flex-wrap items-center gap-2 pt-1">
                                <Link
                                    href="/inward/create"
                                    onClick={() => audioFeedback.playClick()}
                                    className="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white text-xs font-semibold shadow-sm shadow-indigo-600/30 transition transform hover:-translate-y-0.5"
                                >
                                    <FilePlus className="w-3.5 h-3.5" />
                                    <span>Register New Inward</span>
                                </Link>

                                <Link
                                    href="/search"
                                    onClick={() => audioFeedback.playClick()}
                                    className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-700 transition shadow-2xs hover:shadow-xs transform hover:-translate-y-0.5"
                                >
                                    <Search className="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" />
                                    <span>Search Docket</span>
                                </Link>

                                <Link
                                    href="/letters"
                                    onClick={() => audioFeedback.playClick()}
                                    className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-700 transition shadow-2xs hover:shadow-xs transform hover:-translate-y-0.5"
                                >
                                    <Layers className="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" />
                                    <span>Statutory Letters Hub</span>
                                </Link>
                            </div>
                        </div>

                        {/* Interactive 3D Three.js Compact Widget */}
                        <div className="lg:col-span-4 flex items-center justify-center relative">
                            <div className="w-full max-w-[180px] relative">
                                <div className="absolute inset-0 bg-gradient-to-tr from-indigo-500/15 to-cyan-500/15 rounded-full blur-xl pointer-events-none"></div>
                                <ThreeDashboardGlobe height={130} className="w-full" />
                                <div className="text-center mt-0.5">
                                    <span className="text-[9px] font-mono uppercase tracking-widest text-indigo-600 dark:text-indigo-400 font-semibold opacity-90">
                                        &bull; 3D Settlement Nexus &bull;
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* KPI Metrics Cards: Crisp & Elevated */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <div className="p-4 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md hover:border-blue-400/80 dark:hover:border-blue-500/40 transition-all duration-200 group">
                        <div className="flex items-center justify-between">
                            <span className="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Registered</span>
                            <div className="p-2 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-500/20 group-hover:scale-105 transition-transform shadow-2xs">
                                <FileText className="w-4 h-4" />
                            </div>
                        </div>
                        <div className="text-2xl font-bold text-slate-900 dark:text-white mt-2 font-mono tracking-tight">
                            <AnimatedCounter value={totalRegistered} duration={900} />
                        </div>
                        <div className="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                            All-time inward dockets registered
                        </div>
                    </div>

                    <div className="p-4 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md hover:border-emerald-400/80 dark:hover:border-emerald-500/40 transition-all duration-200 group">
                        <div className="flex items-center justify-between">
                            <span className="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Settled & Authorized</span>
                            <div className="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 group-hover:scale-105 transition-transform shadow-2xs">
                                <CheckCircle2 className="w-4 h-4" />
                            </div>
                        </div>
                        <div className="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-2 font-mono tracking-tight">
                            <AnimatedCounter value={totalSettled} duration={900} />
                        </div>
                        <div className="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                            Final payment orders dispatched
                        </div>
                    </div>

                    <div className="p-4 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md hover:border-amber-400/80 dark:hover:border-amber-500/40 transition-all duration-200 group">
                        <div className="flex items-center justify-between">
                            <span className="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Active Pending</span>
                            <div className="p-2 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20 group-hover:scale-105 transition-transform shadow-2xs">
                                <Clock className="w-4 h-4" />
                            </div>
                        </div>
                        <div className="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-2 font-mono tracking-tight">
                            <AnimatedCounter value={totalPending} duration={900} />
                        </div>
                        <div className="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                            Under active audit / verification
                        </div>
                    </div>

                    <div className="p-4 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md hover:border-indigo-400/80 dark:hover:border-indigo-500/40 transition-all duration-200 group">
                        <div className="flex items-center justify-between">
                            <span className="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Authorized Sum</span>
                            <div className="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20 group-hover:scale-105 transition-transform shadow-2xs">
                                <IndianRupee className="w-4 h-4" />
                            </div>
                        </div>
                        <div className="text-xl sm:text-2xl font-bold text-indigo-600 dark:text-indigo-300 mt-2 font-mono truncate tracking-tight">
                            <AnimatedCounter value={totalAuthorizedAmount} prefix="₹ " duration={1200} />
                        </div>
                        <div className="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                            Digitally sanctioned final payments
                        </div>
                    </div>
                </div>

                {/* Statutory Pending Case Aging Breakdown */}
                <div className="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 shadow-sm">
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 mb-3.5">
                        <div>
                            <h3 className="text-xs sm:text-sm font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                <Clock className="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" />
                                <span>Statutory Pending Case Aging Breakdown</span>
                            </h3>
                            <p className="text-[10px] text-slate-500 dark:text-slate-400">
                                Case duration calculated from official inward registration diary date.
                            </p>
                        </div>
                        <span className="text-[11px] font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-xl border border-slate-200 dark:border-slate-700 self-start sm:self-auto shadow-2xs">
                            Total Pending: {totalPending} Cases
                        </span>
                    </div>

                    <div className="grid grid-cols-2 sm:grid-cols-5 gap-2.5">
                        <div className="p-3 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-500/30 text-center hover:bg-emerald-100/70 dark:hover:bg-emerald-900/40 hover:border-emerald-300 dark:hover:border-emerald-400/60 shadow-2xs dark:shadow-none transition-all duration-200">
                            <div className="text-[11px] font-semibold text-emerald-800 dark:text-emerald-300">&lt; 15 Days</div>
                            <div className="text-xl font-extrabold text-emerald-700 dark:text-emerald-100 mt-0.5 font-mono tracking-tight">
                                <AnimatedCounter value={aging?.less_15 ?? 0} />
                            </div>
                            <div className="text-[10px] text-emerald-700 dark:text-emerald-400 font-medium">On track</div>
                        </div>

                        <div className="p-3 rounded-xl bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-500/30 text-center hover:bg-blue-100/70 dark:hover:bg-blue-900/40 hover:border-blue-300 dark:hover:border-blue-400/60 shadow-2xs dark:shadow-none transition-all duration-200">
                            <div className="text-[11px] font-semibold text-blue-800 dark:text-blue-300">15 – 30 Days</div>
                            <div className="text-xl font-extrabold text-blue-700 dark:text-blue-100 mt-0.5 font-mono tracking-tight">
                                <AnimatedCounter value={aging?.['15_to_30'] ?? 0} />
                            </div>
                            <div className="text-[10px] text-blue-700 dark:text-blue-400 font-medium">Normal review</div>
                        </div>

                        <div className="p-3 rounded-xl bg-amber-50/80 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-500/30 text-center hover:bg-amber-100/70 dark:hover:bg-amber-900/40 hover:border-amber-300 dark:hover:border-amber-400/60 shadow-2xs dark:shadow-none transition-all duration-200">
                            <div className="text-[11px] font-semibold text-amber-800 dark:text-amber-300">31 – 45 Days</div>
                            <div className="text-xl font-extrabold text-amber-700 dark:text-amber-100 mt-0.5 font-mono tracking-tight">
                                <AnimatedCounter value={aging?.['31_to_45'] ?? 0} />
                            </div>
                            <div className="text-[10px] text-amber-700 dark:text-amber-400 font-medium">Attention needed</div>
                        </div>

                        <div className="p-3 rounded-xl bg-orange-50/80 dark:bg-orange-950/40 border border-orange-200 dark:border-orange-500/30 text-center hover:bg-orange-100/70 dark:hover:bg-orange-900/40 hover:border-orange-300 dark:hover:border-orange-400/60 shadow-2xs dark:shadow-none transition-all duration-200">
                            <div className="text-[11px] font-semibold text-orange-800 dark:text-orange-300">46 – 60 Days</div>
                            <div className="text-xl font-extrabold text-orange-700 dark:text-orange-100 mt-0.5 font-mono tracking-tight">
                                <AnimatedCounter value={aging?.['46_to_60'] ?? 0} />
                            </div>
                            <div className="text-[10px] text-orange-700 dark:text-orange-400 font-medium">Priority review</div>
                        </div>

                        <div className="p-3 rounded-xl bg-rose-50/80 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-500/30 text-center col-span-2 sm:col-span-1 hover:bg-rose-100/70 dark:hover:bg-rose-900/40 hover:border-rose-300 dark:hover:border-rose-400/60 shadow-2xs dark:shadow-none transition-all duration-200">
                            <div className="text-[11px] font-semibold text-rose-800 dark:text-rose-300">&gt; 60 Days</div>
                            <div className="text-xl font-extrabold text-rose-700 dark:text-rose-100 mt-0.5 font-mono tracking-tight">
                                <AnimatedCounter value={aging?.more_60 ?? 0} />
                            </div>
                            <div className="text-[10px] text-rose-700 dark:text-rose-400 font-medium">Urgent escalation</div>
                        </div>
                    </div>
                </div>

                {/* Two Columns: Recent Cases & Audit Feed */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-5">
                    {/* Recent Cases (2 columns) */}
                    <div className="lg:col-span-2 p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 shadow-sm">
                        <div className="flex items-center justify-between mb-3">
                            <h3 className="text-xs sm:text-sm font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                <FileText className="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" />
                                <span>Recent Case Movements</span>
                            </h3>
                            <Link
                                href="/inward"
                                onClick={() => audioFeedback.playClick()}
                                className="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 flex items-center gap-0.5 font-semibold"
                            >
                                <span>View All Cases</span>
                                <ChevronRight className="w-3 h-3" />
                            </Link>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-xs">
                                <thead>
                                    <tr className="border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-400 font-semibold bg-slate-50 dark:bg-slate-950/40">
                                        <th className="py-2.5 px-3">Registration No</th>
                                        <th className="py-2.5 px-3">Subscriber / GPF No</th>
                                        <th className="py-2.5 px-3">Current Status</th>
                                        <th className="py-2.5 px-3 text-right">Net Amount</th>
                                        <th className="py-2.5 px-3 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100 dark:divide-slate-800/60">
                                    {recent_cases.length === 0 ? (
                                        <tr>
                                            <td colSpan={5} className="py-6 text-center text-slate-400 text-xs">
                                                No recent inward dockets found.
                                            </td>
                                        </tr>
                                    ) : (
                                        recent_cases.map((c) => (
                                            <tr key={c.id} className="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                                <td className="py-2.5 px-3 font-mono font-bold text-indigo-700 dark:text-indigo-300">
                                                    {c.registration_no}
                                                </td>
                                                <td className="py-2.5 px-3">
                                                    <div className="font-semibold text-slate-900 dark:text-slate-200">{c.subscriber_name}</div>
                                                    <div className="text-[10px] text-slate-500 dark:text-slate-400 font-mono">{c.gpf_account}</div>
                                                </td>
                                                <td className="py-2.5 px-3">
                                                    <span className={`px-2 py-0.5 text-[9px] font-semibold rounded-full border ${c.status_badge}`}>
                                                        {c.status_label}
                                                    </span>
                                                </td>
                                                <td className="py-2.5 px-3 text-right font-mono text-slate-800 dark:text-slate-200 font-semibold">
                                                    {c.amount ? `₹ ${Number(c.amount).toLocaleString('en-IN')}` : '—'}
                                                </td>
                                                <td className="py-2.5 px-3 text-right">
                                                    <Link
                                                        href={`/inward/${c.id}`}
                                                        onClick={() => audioFeedback.playClick()}
                                                        className="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-700 dark:text-indigo-400 hover:text-indigo-800 bg-indigo-50 dark:bg-indigo-500/10 px-2 py-0.8 rounded-lg border border-indigo-200 dark:border-indigo-500/20 shadow-2xs hover:shadow-xs transition"
                                                    >
                                                        <span>Open</span>
                                                        <ArrowUpRight className="w-2.5 h-2.5" />
                                                    </Link>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {/* Audit Timeline (1 column) */}
                    <div className="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 shadow-sm">
                        <h3 className="text-xs sm:text-sm font-bold text-slate-900 dark:text-white flex items-center gap-1.5 mb-3">
                            <ShieldAlert className="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" />
                            <span>Workflow Audit Feed</span>
                        </h3>

                        <div className="space-y-3">
                            {recent_histories.length === 0 ? (
                                <div className="py-6 text-center text-slate-400 text-xs">
                                    No audit entries logged yet.
                                </div>
                            ) : (
                                recent_histories.map((h) => (
                                    <div key={h.id} className="relative pl-5 pb-1 border-l-2 border-slate-200 dark:border-slate-800 last:border-l-0">
                                        <div className="absolute -left-[5px] top-1 w-2.5 h-2.5 rounded-full bg-indigo-600 ring-4 ring-indigo-50 dark:ring-slate-900"></div>
                                        <div className="text-xs font-semibold text-slate-900 dark:text-slate-200">
                                            {h.performed_by}
                                        </div>
                                        <div className="text-[10px] text-indigo-600 dark:text-indigo-400 font-medium mt-0.2">
                                            {h.action_type} &bull; Case {h.case_reg}
                                        </div>
                                        <p className="text-[10px] text-slate-600 dark:text-slate-400 mt-0.5 line-clamp-2 leading-relaxed">
                                            {h.remarks}
                                        </p>
                                        <span className="text-[9px] text-slate-400 dark:text-slate-500 mt-0.5 block font-mono">
                                            {h.created_at}
                                        </span>
                                    </div>
                                ))
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
