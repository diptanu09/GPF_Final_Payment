import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { FileCheck, ArrowLeft, ArrowUpRight } from 'lucide-react';

export default function SettledCases({ cases }) {
    return (
        <AuthenticatedLayout title="Settled Cases Report">
            <Head title="Settled Cases Report - GPF Final Payment Portal" />

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
                            <FileCheck className="w-5 h-5 text-emerald-400" />
                            <span>Settled & Authorized GPF Final Payment Cases</span>
                        </h2>
                        <p className="text-xs text-slate-400 mt-0.5">
                            Official register of digitally authorized payment orders.
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
                                    <th className="py-3 px-4">Authority Order No</th>
                                    <th className="py-3 px-4 text-right">Settled Amount</th>
                                    <th className="py-3 px-4">Authorized Date</th>
                                    <th className="py-3 px-4">Status</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/60 font-mono">
                                {cases.data.length === 0 ? (
                                    <tr>
                                        <td colSpan="6" className="py-10 text-center text-slate-500 font-sans">
                                            No settled cases recorded yet.
                                        </td>
                                    </tr>
                                ) : (
                                    cases.data.map((c, idx) => (
                                        <tr key={idx} className="hover:bg-slate-900/40 transition">
                                            <td className="py-3 px-4 font-bold text-indigo-300">
                                                {c.registration_no}
                                            </td>
                                            <td className="py-3 px-4 font-sans">
                                                <div className="font-semibold text-slate-200">{c.subscriber_name}</div>
                                                <div className="text-[11px] text-slate-400 font-mono">{c.gpf_account}</div>
                                            </td>
                                            <td className="py-3 px-4 text-slate-300">
                                                {c.authority_no || '—'}
                                            </td>
                                            <td className="py-3 px-4 text-right font-bold text-emerald-400 text-sm">
                                                ₹ {Number(c.net_amount).toLocaleString('en-IN')}
                                            </td>
                                            <td className="py-3 px-4 text-slate-400 font-sans">
                                                {c.authorized_at || '—'}
                                            </td>
                                            <td className="py-3 px-4 font-sans">
                                                <span className={`px-2.5 py-0.5 text-[10px] font-semibold rounded-full border ${c.status_badge}`}>
                                                    {c.status_label}
                                                </span>
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
