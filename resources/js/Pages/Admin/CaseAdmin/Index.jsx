import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    ShieldAlert,
    RotateCcw,
    XCircle,
    KeyRound,
    Trash2,
    Search,
    AlertTriangle,
    CheckCircle2,
    Layers,
    Filter
} from 'lucide-react';

export default function CaseAdminIndex({ cases, current_tab, filters }) {
    const [search, setSearch] = useState(filters?.search || '');
    const [activeTab, setActiveTab] = useState(current_tab || 'unapprove');

    // Action Modals State
    const [modalConfig, setModalConfig] = useState(null); // { type: 'unapprove' | 'cancel' | 'reset-sig' | 'delete', case: {...}, remarks: '' }

    const handleSearch = (e) => {
        e.preventDefault();
        router.get('/admin/cases', { search, tab: activeTab }, { preserveState: true });
    };

    const handleTabChange = (tab) => {
        setActiveTab(tab);
        router.get('/admin/cases', { search, tab }, { preserveState: true });
    };

    const submitAction = (e) => {
        e.preventDefault();
        if (!modalConfig) return;

        const { type, caseItem, remarks } = modalConfig;

        if (type === 'unapprove') {
            router.post(`/admin/cases/${caseItem.id}/unapprove`, { remarks }, {
                onSuccess: () => setModalConfig(null),
            });
        } else if (type === 'cancel') {
            router.post(`/admin/cases/${caseItem.id}/cancel`, { remarks }, {
                onSuccess: () => setModalConfig(null),
            });
        } else if (type === 'reset-sig') {
            router.post(`/admin/cases/${caseItem.authority_id}/reset-signature`, { remarks }, {
                onSuccess: () => setModalConfig(null),
            });
        } else if (type === 'delete') {
            router.delete(`/admin/cases/${caseItem.id}/draft`, {
                onSuccess: () => setModalConfig(null),
            });
        }
    };

    return (
        <AuthenticatedLayout title="Case Governance & Administrative Overrides">
            <Head title="Case Governance Console - GPF Final Payment Portal" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                            <ShieldAlert className="w-6 h-6 text-amber-600 dark:text-amber-400" />
                            <span>Directorate Case Governance Console</span>
                        </h2>
                        <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Executive administrative controls: Case un-approvals, cancellations with audit justification, signature invalidation, and draft pruning.
                        </p>
                    </div>

                    {/* Action Tabs */}
                    <div className="flex items-center gap-1.5 p-1 bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl text-xs">
                        <button
                            onClick={() => handleTabChange('unapprove')}
                            className={`px-3 py-1.5 rounded-lg font-medium transition flex items-center gap-1.5 ${activeTab === 'unapprove' ? 'bg-amber-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'}`}
                        >
                            <RotateCcw className="w-3.5 h-3.5" />
                            <span>Un-Approve</span>
                        </button>
                        <button
                            onClick={() => handleTabChange('cancel')}
                            className={`px-3 py-1.5 rounded-lg font-medium transition flex items-center gap-1.5 ${activeTab === 'cancel' ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'}`}
                        >
                            <XCircle className="w-3.5 h-3.5" />
                            <span>Cancel Cases</span>
                        </button>
                        <button
                            onClick={() => handleTabChange('signatures')}
                            className={`px-3 py-1.5 rounded-lg font-medium transition flex items-center gap-1.5 ${activeTab === 'signatures' ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'}`}
                        >
                            <KeyRound className="w-3.5 h-3.5" />
                            <span>Reset Signature</span>
                        </button>
                        <button
                            onClick={() => handleTabChange('drafts')}
                            className={`px-3 py-1.5 rounded-lg font-medium transition flex items-center gap-1.5 ${activeTab === 'drafts' ? 'bg-slate-800 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'}`}
                        >
                            <Trash2 className="w-3.5 h-3.5" />
                            <span>Draft Pruning</span>
                        </button>
                    </div>
                </div>

                {/* Search Bar */}
                <div className="glass-panel app-card p-4 rounded-2xl flex gap-3">
                    <form onSubmit={handleSearch} className="flex-1 flex gap-2">
                        <div className="relative flex-1">
                            <Search className="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-1/2 -translate-y-1/2" />
                            <input
                                type="text"
                                placeholder="Search by Registration No, Account No, Subscriber Name..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-950/60 border border-slate-300 dark:border-slate-700/60 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-amber-500 shadow-2xs transition"
                            />
                        </div>
                        <button
                            type="submit"
                            className="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5"
                        >
                            <Filter className="w-3.5 h-3.5" />
                            <span>Filter</span>
                        </button>
                    </form>
                </div>

                {/* Cases Table with Administrative Action Buttons */}
                <div className="glass-panel app-card rounded-2xl overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-slate-50 dark:bg-slate-900/80 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 font-semibold uppercase tracking-wider">
                                <tr>
                                    <th className="py-3 px-4">Registration & Account</th>
                                    <th className="py-3 px-4">Subscriber Details</th>
                                    <th className="py-3 px-4">Current Status</th>
                                    <th className="py-3 px-4">Net Payable</th>
                                    <th className="py-3 px-4 text-right">Administrative Action</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 dark:divide-slate-800/60 font-mono">
                                {cases.data?.length > 0 ? (
                                    cases.data.map((c) => (
                                        <tr key={c.id} className="hover:bg-slate-50/80 dark:hover:bg-slate-900/40 transition">
                                            <td className="py-3 px-4 font-sans">
                                                <div className="font-bold text-slate-900 dark:text-slate-100 font-mono text-sm">{c.registration_no}</div>
                                                <div className="text-[11px] text-indigo-600 dark:text-indigo-400 font-mono font-medium">{c.gpf_account}</div>
                                            </td>

                                            <td className="py-3 px-4 font-sans">
                                                <div className="font-semibold text-slate-900 dark:text-slate-200">{c.subscriber_name}</div>
                                                <div className="text-[11px] text-slate-500 dark:text-slate-400">{c.designation}</div>
                                            </td>

                                            <td className="py-3 px-4">
                                                <span className={`inline-block px-2 py-0.5 text-[10px] font-semibold border rounded-full ${c.status_badge}`}>
                                                    {c.status_label}
                                                </span>
                                            </td>

                                            <td className="py-3 px-4 font-bold text-slate-900 dark:text-slate-100">
                                                ₹ {c.net_amount?.toFixed(2)}
                                            </td>

                                            <td className="py-3 px-4 text-right font-sans">
                                                {activeTab === 'unapprove' && (
                                                    <button
                                                        onClick={() => setModalConfig({ type: 'unapprove', caseItem: c, remarks: '' })}
                                                        className="px-3 py-1.5 bg-amber-50 dark:bg-amber-500/15 hover:bg-amber-100 dark:hover:bg-amber-500/25 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30 rounded-xl text-xs font-semibold shadow-2xs transition inline-flex items-center gap-1.5"
                                                    >
                                                        <RotateCcw className="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" />
                                                        <span>Un-Approve</span>
                                                    </button>
                                                )}

                                                {activeTab === 'cancel' && (
                                                    <button
                                                        onClick={() => setModalConfig({ type: 'cancel', caseItem: c, remarks: '' })}
                                                        className="px-3 py-1.5 bg-rose-50 dark:bg-rose-500/15 hover:bg-rose-100 dark:hover:bg-rose-500/25 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-500/30 rounded-xl text-xs font-semibold shadow-2xs transition inline-flex items-center gap-1.5"
                                                    >
                                                        <XCircle className="w-3.5 h-3.5 text-rose-600 dark:text-rose-400" />
                                                        <span>Cancel Case</span>
                                                    </button>
                                                )}

                                                {activeTab === 'signatures' && c.authority_id && (
                                                    <button
                                                        onClick={() => setModalConfig({ type: 'reset-sig', caseItem: c, remarks: '' })}
                                                        className="px-3 py-1.5 bg-purple-50 dark:bg-purple-500/15 hover:bg-purple-100 dark:hover:bg-purple-500/25 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-500/30 rounded-xl text-xs font-semibold shadow-2xs transition inline-flex items-center gap-1.5"
                                                    >
                                                        <KeyRound className="w-3.5 h-3.5 text-purple-600 dark:text-purple-400" />
                                                        <span>Reset Signature</span>
                                                    </button>
                                                )}

                                                {activeTab === 'drafts' && (
                                                    <button
                                                        onClick={() => setModalConfig({ type: 'delete', caseItem: c, remarks: '' })}
                                                        className="px-3 py-1.5 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 dark:hover:bg-rose-900/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/40 rounded-xl text-xs font-semibold shadow-2xs transition inline-flex items-center gap-1.5"
                                                    >
                                                        <Trash2 className="w-3.5 h-3.5 text-rose-600 dark:text-rose-400" />
                                                        <span>Delete Draft</span>
                                                    </button>
                                                )}
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="5" className="py-12 text-center text-slate-500">
                                            No cases found for the selected governance queue.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {cases.links && cases.links.length > 3 && (
                        <div className="p-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-1">
                            {cases.links.map((l, i) => (
                                <Link
                                    key={i}
                                    href={l.url || '#'}
                                    dangerouslySetInnerHTML={{ __html: l.label }}
                                    className={`px-3 py-1 text-xs rounded-lg transition ${l.active ? 'bg-amber-600 text-white shadow-2xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'} ${!l.url && 'opacity-40 cursor-not-allowed'}`}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>

            {/* Confirmation & Remarks Modal */}
            {modalConfig && (
                <div className="fixed inset-0 z-50 bg-slate-950/60 dark:bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl">
                        <div className="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                            <h3 className="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <AlertTriangle className="w-5 h-5 text-amber-600 dark:text-amber-400" />
                                <span>
                                    {modalConfig.type === 'unapprove' && 'Confirm Case Un-Approval'}
                                    {modalConfig.type === 'cancel' && 'Confirm Case Cancellation'}
                                    {modalConfig.type === 'reset-sig' && 'Confirm Signature Invalidation'}
                                    {modalConfig.type === 'delete' && 'Confirm Draft Deletion'}
                                </span>
                            </h3>
                            <button
                                onClick={() => setModalConfig(null)}
                                className="text-slate-400 hover:text-slate-600 dark:hover:text-white"
                            >
                                ✕
                            </button>
                        </div>

                        <div className="text-xs text-slate-700 dark:text-slate-300 space-y-1 bg-slate-50 dark:bg-slate-950/50 p-3 rounded-xl border border-slate-200 dark:border-slate-800">
                            <div><strong>Registration No:</strong> <span className="font-mono text-amber-600 dark:text-amber-300">{modalConfig.caseItem.registration_no}</span></div>
                            <div><strong>Subscriber:</strong> {modalConfig.caseItem.subscriber_name} ({modalConfig.caseItem.gpf_account})</div>
                        </div>

                        <form onSubmit={submitAction} className="space-y-4">
                            {modalConfig.type !== 'delete' ? (
                                <div>
                                    <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Directorate Audit Remarks / Justification *
                                    </label>
                                    <textarea
                                        required
                                        rows="3"
                                        value={modalConfig.remarks}
                                        onChange={(e) => setModalConfig({ ...modalConfig, remarks: e.target.value })}
                                        className="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none shadow-2xs"
                                        placeholder="Enter mandatory reason for this administrative intervention..."
                                    />
                                </div>
                            ) : (
                                <p className="text-xs text-rose-600 dark:text-rose-300">
                                    Warning: This will permanently purge the draft docket and remove all linked draft entries. This action is irreversible.
                                </p>
                            )}

                            <div className="flex justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                                <button
                                    type="button"
                                    onClick={() => setModalConfig(null)}
                                    className="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs rounded-xl transition"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    className={`px-4 py-2 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 ${modalConfig.type === 'delete' ? 'bg-rose-600 hover:bg-rose-500' : 'bg-amber-600 hover:bg-amber-500'}`}
                                >
                                    <CheckCircle2 className="w-4 h-4" />
                                    <span>Execute Override</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
