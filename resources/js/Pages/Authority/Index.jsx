import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    Award,
    CheckCircle2,
    Clock,
    Printer,
    ArrowUpRight,
    ShieldCheck,
    Send
} from 'lucide-react';

export default function Index({ authorities }) {
    return (
        <AuthenticatedLayout title="Payment Authorities & DSC">
            <Head title="Payment Authorities & DSC - GPF Final Payment Portal" />

            <div className="space-y-6">
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                            <Award className="w-6 h-6 text-emerald-400" />
                            <span>Payment Authorities & Digital Signature Register</span>
                        </h2>
                        <p className="text-xs text-slate-400 mt-1">
                            Official statutory payment orders, PKI USB token signatures, and outward authorizations.
                        </p>
                    </div>
                </div>

                <div className="glass-panel rounded-2xl overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-slate-900/80 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider">
                                <tr>
                                    <th className="py-3.5 px-4">Authority No</th>
                                    <th className="py-3.5 px-4">Date</th>
                                    <th className="py-3.5 px-4">Subscriber & GPF No</th>
                                    <th className="py-3.5 px-4 text-right">Net Payable Sum</th>
                                    <th className="py-3.5 px-4 text-center">DSC Signature</th>
                                    <th className="py-3.5 px-4 text-center">eHRMS Sync</th>
                                    <th className="py-3.5 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/60">
                                {authorities.data.length === 0 ? (
                                    <tr>
                                        <td colSpan="7" className="py-12 text-center text-slate-500 text-sm">
                                            No payment authorities generated yet. Approve cases in the queue to generate authority orders.
                                        </td>
                                    </tr>
                                ) : (
                                    authorities.data.map((a) => (
                                        <tr key={a.id} className="hover:bg-slate-900/40 transition">
                                            <td className="py-3.5 px-4 font-mono font-bold text-indigo-300">
                                                {a.authority_number}
                                                <span className="ml-2 px-2 py-0.5 rounded-full bg-slate-800 text-[10px] text-slate-400 border border-slate-700">
                                                    {a.authority_type}
                                                </span>
                                            </td>
                                            <td className="py-3.5 px-4 text-slate-300">
                                                {a.authority_date}
                                            </td>
                                            <td className="py-3.5 px-4">
                                                <div className="font-semibold text-slate-200">{a.subscriber_name}</div>
                                                <div className="text-[11px] text-slate-400 font-mono">{a.gpf_account}</div>
                                            </td>
                                            <td className="py-3.5 px-4 text-right font-mono font-bold text-emerald-400 text-sm">
                                                ₹ {Number(a.net_amount).toLocaleString('en-IN')}
                                            </td>
                                            <td className="py-3.5 px-4 text-center">
                                                {a.is_signed ? (
                                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-teal-500/10 text-teal-300 border border-teal-500/20 text-[10px] font-semibold">
                                                        <ShieldCheck className="w-3 h-3 text-teal-400" />
                                                        <span>Signed ({a.signed_by})</span>
                                                    </span>
                                                ) : (
                                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px] font-semibold">
                                                        <Clock className="w-3 h-3" />
                                                        <span>Pending Signature</span>
                                                    </span>
                                                )}
                                            </td>
                                            <td className="py-3.5 px-4 text-center">
                                                {a.is_uploaded_hrms ? (
                                                    <span className="px-2 py-0.5 rounded-full bg-purple-500/10 text-purple-300 border border-purple-500/20 text-[10px] font-semibold">
                                                        Synced
                                                    </span>
                                                ) : (
                                                    <span className="text-[11px] text-slate-500">Not uploaded</span>
                                                )}
                                            </td>
                                            <td className="py-3.5 px-4 text-right space-x-2">
                                                <Link
                                                    href={`/authority/${a.id}`}
                                                    className="inline-flex items-center gap-1 text-[11px] font-semibold text-white bg-indigo-600 hover:bg-indigo-500 px-3 py-1.5 rounded-lg shadow-sm"
                                                >
                                                    <span>Open Order</span>
                                                    <ArrowUpRight className="w-3 h-3" />
                                                </Link>
                                                <a
                                                    href={`/authority/${a.id}/print`}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    className="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-300 hover:text-white bg-slate-800 px-2.5 py-1.5 rounded-lg border border-slate-700"
                                                >
                                                    <Printer className="w-3 h-3" />
                                                    <span>Print FP</span>
                                                </a>
                                                {a.dlis_amount > 0 && (
                                                    <a
                                                        href={`/authority/${a.id}/print-dlis`}
                                                        target="_blank"
                                                        rel="noreferrer"
                                                        className="inline-flex items-center gap-1 text-[11px] font-semibold text-cyan-300 hover:text-white bg-cyan-950/60 px-2.5 py-1.5 rounded-lg border border-cyan-800/80"
                                                    >
                                                        <Printer className="w-3 h-3" />
                                                        <span>DLIS</span>
                                                    </a>
                                                )}
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
