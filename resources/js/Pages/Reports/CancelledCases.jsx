import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    XCircle,
    ArrowLeft,
    AlertTriangle,
    Clock
} from 'lucide-react';

export default function CancelledCases({ cases }) {
    return (
        <AuthenticatedLayout title="Canceled Cases Audit Register">
            <Head title="Canceled Cases - GPF Final Payment Portal" />

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
                            <XCircle className="w-6 h-6 text-rose-400" />
                            <span>Directorate Canceled Cases Audit Register</span>
                        </h2>
                        <p className="text-xs text-slate-400 mt-1">
                            Permanent audit register of cases terminated or rejected prior to final settlement with formal justifications.
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
                                    <th className="py-3 px-4">Cancellation Remarks & Justification</th>
                                    <th className="py-3 px-4 text-right">Cancellation Date</th>
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

                                            <td className="py-3 px-4 font-sans">
                                                <div className="text-rose-300 font-medium bg-rose-950/40 border border-rose-900/30 p-2 rounded-lg max-w-md">
                                                    {c.cancelled_remarks}
                                                </div>
                                            </td>

                                            <td className="py-3 px-4 text-right text-slate-300 font-sans">
                                                {c.cancelled_at}
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="5" className="py-12 text-center text-slate-500">
                                            No canceled cases recorded.
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
