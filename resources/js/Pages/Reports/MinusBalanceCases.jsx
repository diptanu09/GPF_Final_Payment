import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    AlertOctagon,
    ArrowLeft,
    ShieldAlert,
    CheckCircle2,
    FileText
} from 'lucide-react';

export default function MinusBalanceCases({ cases }) {
    return (
        <AuthenticatedLayout title="Minus Balance Recovery Register">
            <Head title="Minus Balance Cases - GPF Final Payment Portal" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <div className="flex items-center gap-2 mb-1">
                            <Link
                                href="/reports"
                                className="text-slate-400 hover:text-white text-xs flex items-center gap-1 transition"
                            >
                                <ArrowLeft className="w-3.5 h-3.5" />
                                <span>Back to MIS Hub</span>
                            </Link>
                        </div>
                        <h2 className="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                            <AlertOctagon className="w-6 h-6 text-rose-400" />
                            <span>Minus Balance & Overdrawal Recovery Monitor</span>
                        </h2>
                        <p className="text-xs text-slate-400 mt-1">
                            Tracking overdrawn subscriber ledgers subject to Rule 11(7) recovery under Major Head '8009' and interest under '0049'.
                        </p>
                    </div>
                </div>

                <div className="glass-panel rounded-2xl overflow-hidden border border-slate-800/80">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-slate-900/80 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider">
                                <tr>
                                    <th className="py-3 px-4">Registration & Account</th>
                                    <th className="py-3 px-4">Subscriber Details</th>
                                    <th className="py-3 px-4">DDO Code & Department</th>
                                    <th className="py-3 px-4 text-right">Overdrawn Figure</th>
                                    <th className="py-3 px-4 text-right">Recovered Amount</th>
                                    <th className="py-3 px-4">Status & Remarks</th>
                                    <th className="py-3 px-4 text-center">Notice (R11)</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/60 font-mono">
                                {cases.data?.length > 0 ? (
                                    cases.data.map((c) => (
                                        <tr key={c.id} className="hover:bg-slate-900/40 transition">
                                            <td className="py-3 px-4 font-sans">
                                                <div className="font-bold text-slate-100 font-mono text-sm">{c.registration_no}</div>
                                                <div className="text-[11px] text-indigo-400 font-mono">{c.gpf_account}</div>
                                            </td>

                                            <td className="py-3 px-4 font-sans font-semibold text-slate-200">
                                                {c.subscriber_name}
                                                <div className="text-[10px] text-slate-400 font-normal">{c.designation}</div>
                                            </td>

                                            <td className="py-3 px-4 font-sans text-slate-300">
                                                <div className="font-semibold">{c.ddo_code}</div>
                                                <div className="text-[10px] text-slate-500">{c.ddo_designation}</div>
                                            </td>

                                            <td className="py-3 px-4 text-right font-bold text-rose-400 text-sm">
                                                ₹ {Math.abs(c.overdrawn_balance).toFixed(2)}
                                            </td>

                                            <td className="py-3 px-4 text-right font-bold text-emerald-400 text-sm">
                                                ₹ {c.amount_recovered?.toFixed(2)}
                                            </td>

                                            <td className="py-3 px-4 font-sans">
                                                {c.is_closed ? (
                                                    <span className="px-2 py-0.5 text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full inline-flex items-center gap-1">
                                                        <CheckCircle2 className="w-3 h-3" />
                                                        <span>Closed on {c.closed_at}</span>
                                                    </span>
                                                ) : (
                                                    <span className="px-2 py-0.5 text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20 rounded-full inline-flex items-center gap-1">
                                                        <ShieldAlert className="w-3 h-3" />
                                                        <span>Recovery Outstanding</span>
                                                    </span>
                                                )}
                                                {c.minus_balance_remarks && (
                                                    <div className="text-[10px] text-slate-400 mt-1 truncate max-w-xs" title={c.minus_balance_remarks}>
                                                        {c.minus_balance_remarks}
                                                    </div>
                                                )}
                                            </td>

                                            <td className="py-3 px-4 text-center font-sans">
                                                <a
                                                    href={`/letters/minus-balance/${c.id}`}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="px-2.5 py-1 bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 border border-rose-500/30 rounded-lg text-[11px] font-medium transition inline-flex items-center gap-1"
                                                >
                                                    <FileText className="w-3 h-3" />
                                                    <span>Print Notice</span>
                                                </a>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="7" className="py-12 text-center text-slate-500">
                                            No minus balance cases recorded in the system.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {cases.links && cases.links.length > 3 && (
                        <div className="p-4 border-t border-slate-800 flex justify-end gap-1">
                            {cases.links.map((l, i) => (
                                <Link
                                    key={i}
                                    href={l.url || '#'}
                                    dangerouslySetInnerHTML={{ __html: l.label }}
                                    className={`px-3 py-1 text-xs rounded-lg transition ${l.active ? 'bg-rose-600 text-white' : 'text-slate-400 hover:bg-slate-800'} ${!l.url && 'opacity-40 cursor-not-allowed'}`}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
