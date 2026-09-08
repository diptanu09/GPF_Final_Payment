import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    CheckCircle2,
    ShieldCheck,
    RotateCcw,
    Calculator,
    Award,
    Clock,
    AlertCircle,
    ArrowUpRight
} from 'lucide-react';

export default function Index({ cases, user_role }) {
    const [selectedCase, setSelectedCase] = useState(null);
    const [revertModalOpen, setRevertModalOpen] = useState(false);
    const [remarks, setRemarks] = useState('');

    const { post, processing } = useForm({
        remarks: '',
        target_status: 2, // Revert to Under Verification
    });

    const handleCheck = (caseId) => {
        if (confirm('Verify and pass this calculation sheet to the Accounts Officer?')) {
            post(`/approval/${caseId}/check`);
        }
    };

    const handleApprove = (caseId) => {
        if (confirm('Grant statutory approval for this GPF Final Settlement?')) {
            post(`/approval/${caseId}/approve`);
        }
    };

    const handleGenerateAuthority = (caseId) => {
        post(`/authority/generate/${caseId}`);
    };

    const handleRevert = (e) => {
        e.preventDefault();
        if (!selectedCase) return;
        post(`/approval/${selectedCase.id}/revert`, {
            onSuccess: () => {
                setRevertModalOpen(false);
                setSelectedCase(null);
            },
        });
    };

    return (
        <AuthenticatedLayout title="Supervisory Approvals Queue">
            <Head title="Approvals Queue - GPF Final Payment Portal" />

            <div className="space-y-6">
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                            <ShieldCheck className="w-6 h-6 text-indigo-400" />
                            <span>Supervisory Verification & Approval Queue</span>
                        </h2>
                        <p className="text-xs text-slate-400 mt-1">
                            Review statutory interest calculations, verify nominee splits, and authorize payment orders.
                        </p>
                    </div>
                </div>

                <div className="glass-panel rounded-2xl overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-slate-900/80 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider">
                                <tr>
                                    <th className="py-3.5 px-4">Registration No</th>
                                    <th className="py-3.5 px-4">GPF Account & Subscriber</th>
                                    <th className="py-3.5 px-4">Current Status</th>
                                    <th className="py-3.5 px-4 text-right">Payable Amount</th>
                                    <th className="py-3.5 px-4 text-center">Nominees</th>
                                    <th className="py-3.5 px-4 text-right">Approval Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/60">
                                {cases.data.length === 0 ? (
                                    <tr>
                                        <td colSpan="6" className="py-12 text-center text-slate-500 text-sm">
                                            No cases currently pending in your supervisory approval queue.
                                        </td>
                                    </tr>
                                ) : (
                                    cases.data.map((c) => (
                                        <tr key={c.id} className="hover:bg-slate-900/40 transition">
                                            <td className="py-3.5 px-4 font-mono font-bold text-indigo-300">
                                                {c.registration_no}
                                            </td>
                                            <td className="py-3.5 px-4">
                                                <div className="font-semibold text-slate-200">{c.subscriber_name}</div>
                                                <div className="text-[11px] text-slate-400 font-mono">{c.gpf_account}</div>
                                            </td>
                                            <td className="py-3.5 px-4">
                                                <span className={`px-2.5 py-1 text-[10px] font-semibold rounded-full border ${c.status_badge}`}>
                                                    {c.status_label}
                                                </span>
                                            </td>
                                            <td className="py-3.5 px-4 text-right font-mono font-bold text-emerald-400">
                                                ₹ {Number(c.final_amount).toLocaleString('en-IN')}
                                            </td>
                                            <td className="py-3.5 px-4 text-center">
                                                <span className="px-2 py-0.5 rounded-md bg-purple-500/10 text-purple-300 border border-purple-500/20 text-[10px] font-semibold">
                                                    {c.nominee_count} Claimant(s)
                                                </span>
                                            </td>
                                            <td className="py-3.5 px-4 text-right space-x-2">
                                                <Link
                                                    href={`/calculation/${c.id}`}
                                                    className="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-300 hover:text-white bg-slate-800 px-2.5 py-1.5 rounded-lg border border-slate-700"
                                                >
                                                    <Calculator className="w-3 h-3" />
                                                    <span>Audit Ledger</span>
                                                </Link>

                                                {/* AAO / Checker action */}
                                                {(c.status_id === 4 || c.status_id === 3) && (
                                                    <button
                                                        onClick={() => handleCheck(c.id)}
                                                        disabled={processing}
                                                        className="inline-flex items-center gap-1 text-[11px] font-semibold text-white bg-blue-600 hover:bg-blue-500 px-3 py-1.5 rounded-lg shadow-sm"
                                                    >
                                                        <CheckCircle2 className="w-3 h-3" />
                                                        <span>Check (AAO)</span>
                                                    </button>
                                                )}

                                                {/* Sr. AO / Approver action */}
                                                {(c.status_id === 5 || c.status_id === 14) && (
                                                    <button
                                                        onClick={() => handleApprove(c.id)}
                                                        disabled={processing}
                                                        className="inline-flex items-center gap-1 text-[11px] font-semibold text-white bg-emerald-600 hover:bg-emerald-500 px-3 py-1.5 rounded-lg shadow-sm"
                                                    >
                                                        <ShieldCheck className="w-3 h-3" />
                                                        <span>Approve</span>
                                                    </button>
                                                )}

                                                {/* Generate Authority when Approved */}
                                                {(c.status_id === 6 || c.status_id === 15) && (
                                                    <button
                                                        onClick={() => handleGenerateAuthority(c.id)}
                                                        disabled={processing}
                                                        className="inline-flex items-center gap-1 text-[11px] font-semibold text-white bg-indigo-600 hover:bg-indigo-500 px-3 py-1.5 rounded-lg shadow-sm"
                                                    >
                                                        <Award className="w-3 h-3" />
                                                        <span>Generate Authority</span>
                                                    </button>
                                                )}

                                                {/* Revert Button */}
                                                <button
                                                    onClick={() => {
                                                        setSelectedCase(c);
                                                        setRevertModalOpen(true);
                                                    }}
                                                    className="inline-flex items-center gap-1 text-[11px] font-semibold text-rose-400 hover:text-rose-300 bg-rose-500/10 px-2.5 py-1.5 rounded-lg border border-rose-500/20"
                                                    title="Revert case for correction"
                                                >
                                                    <RotateCcw className="w-3 h-3" />
                                                    <span>Revert</span>
                                                </button>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Revert Modal */}
                {revertModalOpen && selectedCase && (
                    <div className="fixed inset-0 bg-black/75 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                        <div className="glass-panel max-w-md w-full p-6 rounded-2xl border border-slate-800 space-y-4">
                            <h3 className="text-sm font-bold text-white flex items-center gap-2">
                                <RotateCcw className="w-4 h-4 text-rose-400" />
                                <span>Revert Case: {selectedCase.registration_no}</span>
                            </h3>

                            <p className="text-xs text-slate-400">
                                Reverting will return this case to the Dealing Assistant / DEO for ledger re-verification.
                            </p>

                            <form onSubmit={handleRevert} className="space-y-4">
                                <div>
                                    <label className="block text-xs font-medium text-slate-300 mb-1">
                                        Supervisory Rejection Remarks <span className="text-rose-400">*</span>
                                    </label>
                                    <textarea
                                        value={remarks}
                                        onChange={(e) => setRemarks(e.target.value)}
                                        rows={3}
                                        placeholder="State specific reasons (e.g. missing debit voucher not accounted for, subscriber name mismatch)..."
                                        className="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-2 focus:ring-rose-500"
                                        required
                                    />
                                </div>

                                <div className="flex items-center justify-end gap-3 pt-2 border-t border-slate-800">
                                    <button
                                        type="button"
                                        onClick={() => setRevertModalOpen(false)}
                                        className="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={processing || remarks.length < 5}
                                        className="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold disabled:opacity-50"
                                    >
                                        Confirm Revert
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
