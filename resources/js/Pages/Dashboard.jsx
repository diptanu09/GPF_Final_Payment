import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    FilePlus,
    FileCheck,
    FileText,
    Clock,
    IndianRupee,
    AlertCircle,
    ArrowUpRight,
    TrendingUp,
    ShieldAlert,
    CheckCircle2,
    Calendar,
    ChevronRight
} from 'lucide-react';

export default function Dashboard({ metrics = {}, aging = {}, recent_cases = [], recent_histories = [] }) {
    const totalRegistered = metrics?.registered ?? 0;
    const totalSettled = metrics?.settled ?? 0;
    const totalPending = metrics?.pending ?? 0;
    const totalAuthorizedAmount = metrics?.authorized_amount ?? 0;
    return (
        <AuthenticatedLayout title="Executive Dashboard">
            <Head title="Executive Dashboard - GPF Final Payment Portal" />

            <div className="space-y-8">
                {/* Page Title & Banner */}
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                            Executive Settlement Dashboard
                        </h2>
                        <p className="text-xs text-slate-400 mt-1">
                            Real-time workflow monitoring, statutory aging, and authorization ledger.
                        </p>
                    </div>

                    <div className="flex items-center gap-3">
                        <Link
                            href="/inward/create"
                            className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white text-xs font-semibold shadow-lg shadow-indigo-600/25 transition"
                        >
                            <FilePlus className="w-4 h-4" />
                            <span>Register New Inward Case</span>
                        </Link>
                    </div>
                </div>

                {/* KPI Metrics Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div className="glass-panel p-5 rounded-2xl relative overflow-hidden">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Registered</span>
                            <div className="p-2 rounded-xl bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                <FileText className="w-5 h-5" />
                            </div>
                        </div>
                        <div className="text-2xl font-bold text-white mt-3">{totalRegistered}</div>
                        <div className="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                            <span>All-time inward dockets</span>
                        </div>
                    </div>

                    <div className="glass-panel p-5 rounded-2xl relative overflow-hidden">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold text-slate-400 uppercase tracking-wider">Settled & Authorized</span>
                            <div className="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                <CheckCircle2 className="w-5 h-5" />
                            </div>
                        </div>
                        <div className="text-2xl font-bold text-emerald-400 mt-3">{totalSettled}</div>
                        <div className="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                            <span>Final payment orders issued</span>
                        </div>
                    </div>

                    <div className="glass-panel p-5 rounded-2xl relative overflow-hidden">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Pending</span>
                            <div className="p-2 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                <Clock className="w-5 h-5" />
                            </div>
                        </div>
                        <div className="text-2xl font-bold text-amber-400 mt-3">{totalPending}</div>
                        <div className="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                            <span>Under active audit / verification</span>
                        </div>
                    </div>

                    <div className="glass-panel p-5 rounded-2xl relative overflow-hidden">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Authorized Sum</span>
                            <div className="p-2 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                <IndianRupee className="w-5 h-5" />
                            </div>
                        </div>
                        <div className="text-2xl font-bold text-indigo-300 mt-3">
                            ₹ {Number(totalAuthorizedAmount).toLocaleString('en-IN')}
                        </div>
                        <div className="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                            <span>Digitally signed funds</span>
                        </div>
                    </div>
                </div>

                {/* Pending Case Aging Breakdown */}
                <div className="glass-panel p-6 rounded-2xl">
                    <div className="flex items-center justify-between mb-4">
                        <div>
                            <h3 className="text-sm font-bold text-white flex items-center gap-2">
                                <Clock className="w-4 h-4 text-indigo-400" />
                                <span>Statutory Pending Case Aging Breakdown</span>
                            </h3>
                            <p className="text-[11px] text-slate-400">
                                Case duration from official inward registration date.
                            </p>
                        </div>
                        <span className="text-xs text-slate-400 bg-slate-800/60 px-2.5 py-1 rounded-lg border border-slate-700">
                            Total Pending: {totalPending}
                        </span>
                    </div>

                    <div className="grid grid-cols-2 sm:grid-cols-5 gap-3">
                        <div className="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800 text-center">
                            <div className="text-xs text-slate-400">&lt; 15 Days</div>
                            <div className="text-xl font-bold text-emerald-400 mt-1">{aging?.less_15 ?? 0}</div>
                            <div className="text-[10px] text-emerald-500/80 mt-0.5">On track</div>
                        </div>

                        <div className="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800 text-center">
                            <div className="text-xs text-slate-400">15 – 30 Days</div>
                            <div className="text-xl font-bold text-blue-400 mt-1">{aging?.['15_to_30'] ?? 0}</div>
                            <div className="text-[10px] text-blue-500/80 mt-0.5">Normal review</div>
                        </div>

                        <div className="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800 text-center">
                            <div className="text-xs text-slate-400">31 – 45 Days</div>
                            <div className="text-xl font-bold text-amber-400 mt-1">{aging?.['31_to_45'] ?? 0}</div>
                            <div className="text-[10px] text-amber-500/80 mt-0.5">Attention needed</div>
                        </div>

                        <div className="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800 text-center">
                            <div className="text-xs text-slate-400">46 – 60 Days</div>
                            <div className="text-xl font-bold text-orange-400 mt-1">{aging?.['46_to_60'] ?? 0}</div>
                            <div className="text-[10px] text-orange-500/80 mt-0.5">Priority review</div>
                        </div>

                        <div className="p-3.5 rounded-xl bg-rose-950/20 border border-rose-900/30 text-center col-span-2 sm:col-span-1">
                            <div className="text-xs text-rose-300">&gt; 60 Days</div>
                            <div className="text-xl font-bold text-rose-400 mt-1">{aging?.more_60 ?? 0}</div>
                            <div className="text-[10px] text-rose-500 mt-0.5">Urgent escalation</div>
                        </div>
                    </div>
                </div>

                {/* Two Columns: Recent Cases & Audit Timeline */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {/* Recent Cases (2 columns) */}
                    <div className="lg:col-span-2 glass-panel p-6 rounded-2xl">
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="text-sm font-bold text-white flex items-center gap-2">
                                <FileText className="w-4 h-4 text-indigo-400" />
                                <span>Recent Case Registrations & Movements</span>
                            </h3>
                            <Link href="/inward" className="text-xs text-indigo-400 hover:text-indigo-300 flex items-center gap-1 font-medium">
                                <span>View All Cases</span>
                                <ChevronRight className="w-3.5 h-3.5" />
                            </Link>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-xs">
                                <thead>
                                    <tr className="border-b border-slate-800 text-slate-400 font-semibold">
                                        <th className="py-3 px-2">Registration No</th>
                                        <th className="py-3 px-2">Subscriber / GPF No</th>
                                        <th className="py-3 px-2">Current Status</th>
                                        <th className="py-3 px-2 text-right">Net Amount</th>
                                        <th className="py-3 px-2 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-800/60">
                                    {recent_cases.map((c) => (
                                        <tr key={c.id} className="hover:bg-slate-900/40 transition">
                                            <td className="py-3 px-2 font-mono font-bold text-indigo-300">
                                                {c.registration_no}
                                            </td>
                                            <td className="py-3 px-2">
                                                <div className="font-semibold text-slate-200">{c.subscriber_name}</div>
                                                <div className="text-[11px] text-slate-400 font-mono">{c.gpf_account}</div>
                                            </td>
                                            <td className="py-3 px-2">
                                                <span className={`px-2.5 py-1 text-[10px] font-semibold rounded-full border ${c.status_badge}`}>
                                                    {c.status_label}
                                                </span>
                                            </td>
                                            <td className="py-3 px-2 text-right font-mono text-slate-200">
                                                {c.amount ? `₹ ${Number(c.amount).toLocaleString('en-IN')}` : '—'}
                                            </td>
                                            <td className="py-3 px-2 text-right">
                                                <Link
                                                    href={`/inward/${c.id}`}
                                                    className="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-400 hover:text-indigo-300 bg-indigo-500/10 px-2.5 py-1 rounded-lg border border-indigo-500/20"
                                                >
                                                    <span>Open</span>
                                                    <ArrowUpRight className="w-3 h-3" />
                                                </Link>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {/* Audit Timeline (1 column) */}
                    <div className="glass-panel p-6 rounded-2xl">
                        <h3 className="text-sm font-bold text-white flex items-center gap-2 mb-4">
                            <ShieldAlert className="w-4 h-4 text-indigo-400" />
                            <span>Workflow Audit Feed</span>
                        </h3>

                        <div className="space-y-4">
                            {recent_histories.map((h) => (
                                <div key={h.id} className="relative pl-6 pb-2 border-l border-slate-800 last:border-l-0">
                                    <div className="absolute -left-1.5 top-0 w-3 h-3 rounded-full bg-indigo-500 border-2 border-slate-950"></div>
                                    <div className="text-xs font-semibold text-slate-200">
                                        {h.performed_by}
                                    </div>
                                    <div className="text-[11px] text-indigo-400 font-medium mt-0.5">
                                        {h.action_type} &bull; Case {h.case_reg}
                                    </div>
                                    <p className="text-[11px] text-slate-400 mt-1 line-clamp-2">
                                        {h.remarks}
                                    </p>
                                    <span className="text-[10px] text-slate-500 mt-1 block">
                                        {h.created_at}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
