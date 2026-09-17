import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    KeyRound,
    ArrowLeft,
    ShieldCheck,
    Lock,
    Clock,
    FileCheck2
} from 'lucide-react';

export default function DigitalSignatures({ signatures }) {
    return (
        <AuthenticatedLayout title="PKI Digital Signature Audit Log">
            <Head title="Digital Signature Audit Log - GPF Final Payment Portal" />

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
                            <KeyRound className="w-6 h-6 text-purple-400" />
                            <span>PKI Digital Signature Cryptographic Log</span>
                        </h2>
                        <p className="text-xs text-slate-400 mt-1">
                            Statutory hardware USB DSC signing events with SHA-256 verification hashes and certificate subject identifiers.
                        </p>
                    </div>
                </div>

                <div className="glass-panel rounded-2xl overflow-hidden border border-slate-800/80">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-slate-900/80 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider">
                                <tr>
                                    <th className="py-3 px-4">Registration & Account</th>
                                    <th className="py-3 px-4">Subscriber Name</th>
                                    <th className="py-3 px-4">Authority Order</th>
                                    <th className="py-3 px-4">Signatory & Certificate DN</th>
                                    <th className="py-3 px-4">SHA-256 Verification Hash</th>
                                    <th className="py-3 px-4 text-right">Signed Timestamp</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/60 font-mono">
                                {signatures.data?.length > 0 ? (
                                    signatures.data.map((s) => (
                                        <tr key={s.id} className="hover:bg-slate-900/40 transition">
                                            <td className="py-3 px-4 font-sans">
                                                <div className="font-bold text-slate-100 font-mono text-sm">{s.registration_no}</div>
                                                <div className="text-[11px] text-indigo-400 font-mono">{s.gpf_account}</div>
                                            </td>

                                            <td className="py-3 px-4 font-sans font-semibold text-slate-200">
                                                {s.subscriber_name}
                                            </td>

                                            <td className="py-3 px-4 font-mono text-slate-300">
                                                {s.authority_number}
                                            </td>

                                            <td className="py-3 px-4 font-sans">
                                                <div className="font-bold text-emerald-400 flex items-center gap-1">
                                                    <ShieldCheck className="w-3.5 h-3.5 text-emerald-400" />
                                                    <span>{s.signer_name}</span>
                                                </div>
                                                <div className="text-[10px] text-slate-500 font-mono truncate max-w-xs" title={s.certificate_dn}>
                                                    {s.certificate_dn}
                                                </div>
                                            </td>

                                            <td className="py-3 px-4 font-mono text-[10px] text-indigo-300">
                                                <div className="bg-slate-950 p-1.5 rounded border border-slate-800 truncate max-w-xs" title={s.signature_hash}>
                                                    {s.signature_hash}
                                                </div>
                                            </td>

                                            <td className="py-3 px-4 text-right text-slate-300 font-sans">
                                                {s.signed_at}
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="6" className="py-12 text-center text-slate-500">
                                            No digital signatures recorded yet.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {signatures.links && signatures.links.length > 3 && (
                        <div className="p-4 border-t border-slate-800 flex justify-end gap-1">
                            {signatures.links.map((l, i) => (
                                <Link
                                    key={i}
                                    href={l.url || '#'}
                                    dangerouslySetInnerHTML={{ __html: l.label }}
                                    className={`px-3 py-1 text-xs rounded-lg transition ${l.active ? 'bg-purple-600 text-white' : 'text-slate-400 hover:bg-slate-800'} ${!l.url && 'opacity-40 cursor-not-allowed'}`}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
