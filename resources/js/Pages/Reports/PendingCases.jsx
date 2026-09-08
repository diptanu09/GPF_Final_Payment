import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Clock, ArrowLeft, ArrowUpRight } from 'lucide-react';

export default function PendingCases({ cases }) {
    return (
        <AuthenticatedLayout title="Pending Cases Report">
            <Head title="Pending Cases Report - GPF Final Payment Portal" />

            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <Link
                        href="/reports"
                        className="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition"
                    >
                        <ArrowLeft className="w-4 h-4" />
                    </Link>
                    <div>
                        <h2 className="text-xl font-bold tracking-tight text-white flex items-center gap-2">
                            <Clock className="w-5 h-5 text-amber-400" />
                            <span>Pending GPF Final Payment Dockets & Aging Register</span>
                        </h2>
                        <p className="text-xs text-slate-400 mt-0.5">
                            Audit register of all open cases awaiting verification, calculation, or approval.
                        </p>
                    </div>
                </div>

                <div className="glass-panel rounded-2xl overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-slate-900/80 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider">
                                <tr>
                                    <th className="py-3 px-4">Registration No</th>
                                    <th className="py-3 px-4">GPF Account & Subscriber</th>
                                    <th className="py-3 px-4">Current Workflow Stage</th>
                                    <th className="py-3 px-4">Handling Staff</th>
                                    <th className="py-3 px-4 text-center">Days Pending</th>
                                    <th className="py-3 px-4">Registered Date</th>
                                    <th className="py-3 px-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/60">
                                {cases.data.length === 0 ? (
                                    <tr>
                                        <td colSpan="7" className="py-10 text-center text-slate-500">
                                            No pending cases in the system.
                                        </td>
                                    </tr>
                                ) : (
                                    cases.data.map((c, idx) => (
                                        <tr key={idx} className="hover:bg-slate-900/40 transition">
                                            <td className="py-3 px-4 font-mono font-bold text-indigo-300">
                                                {c.registration_no}
                                            </td>
                                            <td className="py-3 px-4">
                                                <div className="font-semibold text-slate-200">{c.subscriber_name}</div>
                                                <div className="text-[11px] text-slate-400 font-mono">{c.gpf_account}</div>
                                            </td>
                                            <td className="py-3 px-4">
                                                <span className={`px-2.5 py-0.5 text-[10px] font-semibold rounded-full border ${c.status_badge}`}>
                                                    {c.status_label}
                                                </span>
                                            </td>
                                            <td className="py-3 px-4 text-slate-300">
                                                {c.assigned_user}
                                            </td>
                                            <td className="py-3 px-4 text-center">
                                                <span className={`px-2 py-0.5 rounded-md font-mono text-[11px] font-bold ${
                                                    c.days_pending > 60
                                                        ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30'
                                                        : c.days_pending > 30
                                                        ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30'
                                                        : 'bg-slate-800 text-slate-300'
                                                }`}>
                                                    {c.days_pending} Days
                                                </span>
                                            </td>
                                            <td className="py-3 px-4 text-slate-400">
                                                {c.created_at}
                                            </td>
                                            <td className="py-3 px-4 text-right">
                                                <Link
                                                    href={`/inward/${c.id}`}
                                                    className="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-400 hover:text-indigo-300 bg-indigo-500/10 px-2.5 py-1 rounded-lg border border-indigo-500/20"
                                                >
                                                    <span>Open Docket</span>
                                                    <ArrowUpRight className="w-3 h-3" />
                                                </Link>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
