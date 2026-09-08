import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    BarChart3,
    FileCheck,
    Clock,
    AlertOctagon,
    Users,
    ArrowUpRight,
    TrendingUp
} from 'lucide-react';

export default function Index({ summary, user_stats }) {
    return (
        <AuthenticatedLayout title="MIS Reports">
            <Head title="MIS Reports - GPF Final Payment Portal" />

            <div className="space-y-8">
                <div>
                    <h2 className="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                        <BarChart3 className="w-6 h-6 text-indigo-400" />
                        <span>Management Information System (MIS) Reports</span>
                    </h2>
                    <p className="text-xs text-slate-400 mt-1">
                        Institutional audit statistics, case settlement ratios, and staff performance indices.
                    </p>
                </div>

                {/* Summary Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Link
                        href="/reports/settled"
                        className="glass-panel p-5 rounded-2xl hover:border-emerald-500/30 transition group block"
                    >
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold text-slate-400 uppercase tracking-wider">Settled Cases</span>
                            <FileCheck className="w-5 h-5 text-emerald-400 group-hover:scale-110 transition" />
                        </div>
                        <div className="text-2xl font-bold text-emerald-400 mt-3">{summary.total_settled}</div>
                        <div className="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                            <span>Click to view detailed register</span>
                            <ArrowUpRight className="w-3 h-3 text-emerald-400" />
                        </div>
                    </Link>

                    <Link
                        href="/reports/pending"
                        className="glass-panel p-5 rounded-2xl hover:border-amber-500/30 transition group block"
                    >
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pending Dockets</span>
                            <Clock className="w-5 h-5 text-amber-400 group-hover:scale-110 transition" />
                        </div>
                        <div className="text-2xl font-bold text-amber-400 mt-3">{summary.total_pending}</div>
                        <div className="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                            <span>Click to view aging breakdown</span>
                            <ArrowUpRight className="w-3 h-3 text-amber-400" />
                        </div>
                    </Link>

                    <div className="glass-panel p-5 rounded-2xl">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold text-slate-400 uppercase tracking-wider">Minus Balances</span>
                            <AlertOctagon className="w-5 h-5 text-rose-400" />
                        </div>
                        <div className="text-2xl font-bold text-rose-400 mt-3">{summary.total_minus_balance}</div>
                        <div className="text-[11px] text-slate-500 mt-1">Recovery objection dockets</div>
                    </div>

                    <div className="glass-panel p-5 rounded-2xl">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Inward</span>
                            <TrendingUp className="w-5 h-5 text-indigo-400" />
                        </div>
                        <div className="text-2xl font-bold text-slate-100 mt-3">{summary.total_registered}</div>
                        <div className="text-[11px] text-slate-500 mt-1">Cumulative registered cases</div>
                    </div>
                </div>

                {/* Staff Settlement Counts */}
                <div className="glass-panel p-6 rounded-2xl space-y-4">
                    <h3 className="text-sm font-bold text-white flex items-center gap-2">
                        <Users className="w-4 h-4 text-indigo-400" />
                        <span>Dealing Assistant & Auditor Performance Metrics</span>
                    </h3>

                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-slate-900/80 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider">
                                <tr>
                                    <th className="py-3 px-4">Officer / Staff Name</th>
                                    <th className="py-3 px-4">Institutional Role</th>
                                    <th className="py-3 px-4 text-right">Settled & Authorized Cases</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/60 font-mono">
                                {user_stats.map((u, idx) => (
                                    <tr key={idx} className="hover:bg-slate-900/40 transition">
                                        <td className="py-3 px-4 font-sans font-semibold text-slate-200">
                                            {u.name}
                                        </td>
                                        <td className="py-3 px-4 text-slate-400 font-sans">
                                            {u.role}
                                        </td>
                                        <td className="py-3 px-4 text-right font-bold text-emerald-400">
                                            {u.settled_count}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
