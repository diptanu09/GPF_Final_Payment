import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    FileText,
    Printer,
    Search,
    AlertTriangle,
    ShieldAlert,
    FileCheck2,
    RotateCcw,
    Send,
    Edit3,
    FileX,
    ExternalLink,
    Filter,
    Layers,
    DollarSign,
    CheckCircle2
} from 'lucide-react';

export default function LettersIndex({ cases, filters }) {
    const [search, setSearch] = useState(filters?.search || '');
    const [activeType, setActiveType] = useState(filters?.type || 'all');
    const [recoveryModalCase, setRecoveryModalCase] = useState(null);
    const [recoveryForm, setRecoveryForm] = useState({
        amount_recovered: '',
        remarks: '',
        close_minus_balance: false,
    });
    const [corrigendumModalCase, setCorrigendumModalCase] = useState(null);
    const [corrigendumForm, setCorrigendumForm] = useState({
        matter: '',
        copy_to: '',
        signature: 'Accounts Officer / Fund (FP)',
    });
    const [objectionModalCase, setObjectionModalCase] = useState(null);
    const [objectionForm, setObjectionForm] = useState({
        points: [
            "The application should be submitted in statutory Form 10-A / 10-B / 10-C duly filled in all respects.",
            "Exact Date of Retirement / Demise / Resignation is not authenticated or missing from records.",
            "Attested Death Certificate from Registrar of Births & Deaths not enclosed.",
            "Survival Certificate and Legal Succession Certificate / Nominee affidavit required.",
            "Last Fund Deduction statement showing Subscription, Refund, DA with Treasury Bill No. and Date.",
            "Certificate of Advance / Withdrawal drawn, if any, during the last 12 (twelve) months immediately preceding retirement/demise with Bill No. and Date.",
            "Statement showing monthly G.P.F. Subscription / Refund / DA details for last 12 months.",
            "The application form must be stamped and counter-signed by the Head of Office / Department."
        ],
        selectedPoints: [],
        custom_remarks: '',
        signature: 'Accounts Officer / Fund (FP)',
    });

    const handleSearch = (e) => {
        e.preventDefault();
        router.get('/letters', { search, type: activeType }, { preserveState: true });
    };

    const handleTypeChange = (type) => {
        setActiveType(type);
        router.get('/letters', { search, type }, { preserveState: true });
    };

    const openRecoveryModal = (c) => {
        setRecoveryModalCase(c);
        setRecoveryForm({
            amount_recovered: c.amount_recovered || '',
            remarks: '',
            close_minus_balance: false,
        });
    };

    const submitRecovery = (e) => {
        e.preventDefault();
        if (!recoveryModalCase) return;
        router.post(`/letters/minus-balance/${recoveryModalCase.id}/recovery`, recoveryForm, {
            onSuccess: () => setRecoveryModalCase(null),
        });
    };

    const openCorrigendumModal = (c) => {
        setCorrigendumModalCase(c);
        setCorrigendumForm({
            matter: `Please read the name of Treasury / DDO as [Corrected Treasury/DDO] instead of [Old Treasury/DDO] wherever appeared in the GPF Final Payment Authority for Rs. ${c.net_amount?.toFixed(2)} issued in favour of ${c.subscriber_name}, ${c.designation}, GPF A/c No. ${c.gpf_account} from this office vide Authority No. ${c.authority_no || '[Authority No]'} dated [Date].\n\nAll other terms and conditions will remain unchanged.`,
            copy_to: `1. The Treasury Officer for information and necessary action.\n2. The Drawing & Disbursing Officer for making necessary payment.\n3. ${c.subscriber_name}, for information.`,
            signature: 'Accounts Officer / Fund (FP)',
        });
    };

    const submitCorrigendum = (e) => {
        e.preventDefault();
        if (!corrigendumModalCase) return;
        const url = `/letters/corrigendum/${corrigendumModalCase.id}?matter=${encodeURIComponent(corrigendumForm.matter)}&copy_to=${encodeURIComponent(corrigendumForm.copy_to)}&signature=${encodeURIComponent(corrigendumForm.signature)}`;
        window.open(url, '_blank');
        setCorrigendumModalCase(null);
    };

    const openObjectionModal = (c) => {
        setObjectionModalCase(c);
        setObjectionForm({
            ...objectionForm,
            selectedPoints: [...objectionForm.points.slice(0, 3)],
            custom_remarks: `In inviting a reference to the GPF Final Payment application submitted in respect of ${c.subscriber_name}, ${c.designation}, GPF A/c No. ${c.gpf_account}, I am to return herewith the original application along with enclosures due to the discrepancies noted below.`,
        });
    };

    const submitObjection = (e) => {
        e.preventDefault();
        if (!objectionModalCase) return;
        const formEl = document.createElement('form');
        formEl.method = 'POST';
        formEl.action = `/letters/objection/${objectionModalCase.id}`;
        formEl.target = '_blank';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            formEl.appendChild(csrfInput);
        }

        objectionForm.selectedPoints.forEach(p => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'points[]';
            input.value = p;
            formEl.appendChild(input);
        });

        const remInput = document.createElement('input');
        remInput.type = 'hidden';
        remInput.name = 'custom_remarks';
        remInput.value = objectionForm.custom_remarks;
        formEl.appendChild(remInput);

        const sigInput = document.createElement('input');
        sigInput.type = 'hidden';
        sigInput.name = 'signature';
        sigInput.value = objectionForm.signature;
        formEl.appendChild(sigInput);

        document.body.appendChild(formEl);
        formEl.submit();
        document.body.removeChild(formEl);
        setObjectionModalCase(null);
    };

    return (
        <AuthenticatedLayout title="Statutory Letters & Notices">
            <Head title="Letters & Notices Hub - GPF Final Payment Portal" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                            <Layers className="w-6 h-6 text-indigo-400" />
                            <span>Statutory Letters & Notices Hub</span>
                        </h2>
                        <p className="text-xs text-slate-400 mt-1">
                            Official AG Tripura letter generations: Input Sheets, Annexure 5.24 Intimations, Corrigendums, Revalidations, Objections, and Rule 11(7) Minus Balance Notices.
                        </p>
                    </div>

                    {/* Filter Tabs */}
                    <div className="flex items-center gap-1.5 p-1 bg-slate-900/80 border border-slate-800 rounded-xl text-xs">
                        <button
                            onClick={() => handleTypeChange('all')}
                            className={`px-3 py-1.5 rounded-lg font-medium transition ${activeType === 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white'}`}
                        >
                            All Cases
                        </button>
                        <button
                            onClick={() => handleTypeChange('authorized')}
                            className={`px-3 py-1.5 rounded-lg font-medium transition ${activeType === 'authorized' ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-400 hover:text-white'}`}
                        >
                            Authorized (DSC)
                        </button>
                        <button
                            onClick={() => handleTypeChange('minus_balance')}
                            className={`px-3 py-1.5 rounded-lg font-medium transition ${activeType === 'minus_balance' ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-400 hover:text-white'}`}
                        >
                            Minus Balance (Rule 11)
                        </button>
                    </div>
                </div>

                {/* Search Bar */}
                <div className="glass-panel p-4 rounded-2xl flex flex-col sm:flex-row gap-3">
                    <form onSubmit={handleSearch} className="flex-1 flex gap-2">
                        <div className="relative flex-1">
                            <Search className="w-4 h-4 text-slate-500 absolute left-3 top-1/2 -translate-y-1/2" />
                            <input
                                type="text"
                                placeholder="Search by Registration No, GPF Account, Subscriber Name, Employee Code..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="w-full pl-9 pr-4 py-2 bg-slate-900/60 border border-slate-700/60 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition"
                            />
                        </div>
                        <button
                            type="submit"
                            className="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs rounded-xl transition flex items-center gap-1.5"
                        >
                            <Filter className="w-3.5 h-3.5" />
                            <span>Filter</span>
                        </button>
                    </form>
                </div>

                {/* Cases Table with Statutory Action Buttons */}
                <div className="glass-panel rounded-2xl overflow-hidden border border-slate-800/80">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-slate-900/80 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider">
                                <tr>
                                    <th className="py-3 px-4">Registration & Account</th>
                                    <th className="py-3 px-4">Subscriber Details</th>
                                    <th className="py-3 px-4">Status & Balance</th>
                                    <th className="py-3 px-4">Statutory Letters & Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/60 font-mono">
                                {cases.data?.length > 0 ? (
                                    cases.data.map((c) => (
                                        <tr key={c.id} className="hover:bg-slate-900/40 transition">
                                            <td className="py-3 px-4 font-sans">
                                                <div className="font-bold text-slate-100 font-mono text-sm">{c.registration_no}</div>
                                                <div className="text-[11px] text-indigo-400 font-mono">{c.gpf_account}</div>
                                                {c.authority_no && (
                                                    <div className="text-[10px] text-slate-500 font-mono mt-0.5">Auth: {c.authority_no}</div>
                                                )}
                                            </td>

                                            <td className="py-3 px-4 font-sans">
                                                <div className="font-semibold text-slate-200">{c.subscriber_name}</div>
                                                <div className="text-[11px] text-slate-400">{c.designation || 'N/A'}</div>
                                            </td>

                                            <td className="py-3 px-4">
                                                <span className={`inline-block px-2 py-0.5 text-[10px] font-semibold border rounded-full ${c.status_badge}`}>
                                                    {c.status_label}
                                                </span>
                                                <div className={`mt-1 font-bold text-xs ${c.is_minus_balance ? 'text-rose-400' : 'text-slate-200'}`}>
                                                    {c.is_minus_balance ? `Minus: ₹ ${Math.abs(c.net_amount).toFixed(2)}` : `Net: ₹ ${c.net_amount?.toFixed(2)}`}
                                                </div>
                                                {c.amount_recovered > 0 && (
                                                    <div className="text-[10px] text-emerald-400 font-sans">
                                                        Recovered: ₹ {c.amount_recovered.toFixed(2)}
                                                    </div>
                                                )}
                                            </td>

                                            <td className="py-3 px-4 font-sans">
                                                <div className="flex flex-wrap items-center gap-1.5">
                                                    {/* 1. Input Sheet */}
                                                    <a
                                                        href={`/letters/input-sheet/${c.id}`}
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        className="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700/80 rounded-lg text-[11px] font-medium transition flex items-center gap-1"
                                                        title="View & Print Official Input Sheet (DEO, AAO, Sr. AO signatures)"
                                                    >
                                                        <FileText className="w-3 h-3 text-indigo-400" />
                                                        <span>Input Sheet</span>
                                                    </a>

                                                    {/* 2. Intimation Letter (Annexure 5.24) */}
                                                    {c.has_authority && (
                                                        <a
                                                            href={`/letters/intimation/${c.id}`}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="px-2.5 py-1 bg-teal-500/10 hover:bg-teal-500/20 text-teal-300 border border-teal-500/30 rounded-lg text-[11px] font-medium transition flex items-center gap-1"
                                                            title="Annexure 5.24: Intimation to Subscriber on Authorization"
                                                        >
                                                            <Send className="w-3 h-3 text-teal-400" />
                                                            <span>Intimation</span>
                                                        </a>
                                                    )}

                                                    {/* 3. Corrigendum */}
                                                    <button
                                                        onClick={() => openCorrigendumModal(c)}
                                                        className="px-2.5 py-1 bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 border border-amber-500/30 rounded-lg text-[11px] font-medium transition flex items-center gap-1"
                                                        title="Generate official Corrigendum Amendment Order"
                                                    >
                                                        <Edit3 className="w-3 h-3 text-amber-400" />
                                                        <span>Corrigendum</span>
                                                    </button>

                                                    {/* 4. Revalidation */}
                                                    <a
                                                        href={`/letters/revalidation/${c.id}`}
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        className="px-2.5 py-1 bg-blue-500/10 hover:bg-blue-500/20 text-blue-300 border border-blue-500/30 rounded-lg text-[11px] font-medium transition flex items-center gap-1"
                                                        title="Revalidate lapsed authority to Treasury Officer"
                                                    >
                                                        <RotateCcw className="w-3 h-3 text-blue-400" />
                                                        <span>Revalidate</span>
                                                    </a>

                                                    {/* 5. Objection / Return Memo */}
                                                    <button
                                                        onClick={() => openObjectionModal(c)}
                                                        className="px-2.5 py-1 bg-orange-500/10 hover:bg-orange-500/20 text-orange-300 border border-orange-500/30 rounded-lg text-[11px] font-medium transition flex items-center gap-1"
                                                        title="Return Docket to DDO with statutory defect checklist"
                                                    >
                                                        <FileX className="w-3 h-3 text-orange-400" />
                                                        <span>Objection Memo</span>
                                                    </button>

                                                    {/* 6. Minus Balance Notice & Recovery */}
                                                    {c.is_minus_balance && (
                                                        <div className="flex items-center gap-1">
                                                            <a
                                                                href={`/letters/minus-balance/${c.id}`}
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                className="px-2.5 py-1 bg-rose-500/15 hover:bg-rose-500/25 text-rose-300 border border-rose-500/30 rounded-lg text-[11px] font-medium transition flex items-center gap-1"
                                                                title="Rule 11(7) Minus Balance Notice"
                                                            >
                                                                <ShieldAlert className="w-3 h-3 text-rose-400" />
                                                                <span>Notice (R11)</span>
                                                            </a>
                                                            <button
                                                                onClick={() => openRecoveryModal(c)}
                                                                className="px-2 py-1 bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-300 border border-emerald-500/30 rounded-lg text-[11px] font-medium transition flex items-center gap-1"
                                                                title="Record Minus Balance Recovery"
                                                            >
                                                                <DollarSign className="w-3 h-3 text-emerald-400" />
                                                                <span>Recovery</span>
                                                            </button>
                                                        </div>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="4" className="py-12 text-center text-slate-500">
                                            No dockets found matching the selected criteria.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {cases.links && cases.links.length > 3 && (
                        <div className="p-4 border-t border-slate-800 flex justify-end gap-1">
                            {cases.links.map((l, i) => (
                                <Link
                                    key={i}
                                    href={l.url || '#'}
                                    dangerouslySetInnerHTML={{ __html: l.label }}
                                    className={`px-3 py-1 text-xs rounded-lg transition ${l.active ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800'} ${!l.url && 'opacity-40 cursor-not-allowed'}`}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>

            {/* Minus Balance Recovery Modal */}
            {recoveryModalCase && (
                <div className="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl">
                        <div className="flex items-center justify-between border-b border-slate-800 pb-3">
                            <h3 className="text-base font-bold text-white flex items-center gap-2">
                                <DollarSign className="w-5 h-5 text-emerald-400" />
                                <span>Record Minus Balance Recovery</span>
                            </h3>
                            <button
                                onClick={() => setRecoveryModalCase(null)}
                                className="text-slate-400 hover:text-white"
                            >
                                ✕
                            </button>
                        </div>

                        <div className="text-xs text-slate-300 space-y-1 bg-slate-950/50 p-3 rounded-xl border border-slate-800">
                            <div><strong>Registration No:</strong> <span className="font-mono text-indigo-300">{recoveryModalCase.registration_no}</span></div>
                            <div><strong>Subscriber:</strong> {recoveryModalCase.subscriber_name} ({recoveryModalCase.gpf_account})</div>
                            <div><strong>Total Overdrawn:</strong> <span className="text-rose-400 font-bold">₹ {Math.abs(recoveryModalCase.net_amount).toFixed(2)}</span></div>
                        </div>

                        <form onSubmit={submitRecovery} className="space-y-4">
                            <div>
                                <label className="block text-xs font-semibold text-slate-300 mb-1">
                                    Amount Recovered (₹) *
                                </label>
                                <input
                                    type="number"
                                    step="0.01"
                                    required
                                    value={recoveryForm.amount_recovered}
                                    onChange={(e) => setRecoveryForm({ ...recoveryForm, amount_recovered: e.target.value })}
                                    className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:border-indigo-500 focus:outline-none"
                                    placeholder="Enter recovered figure"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-slate-300 mb-1">
                                    Recovery Remarks & Challan Details *
                                </label>
                                <textarea
                                    required
                                    rows="3"
                                    value={recoveryForm.remarks}
                                    onChange={(e) => setRecoveryForm({ ...recoveryForm, remarks: e.target.value })}
                                    className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:border-indigo-500 focus:outline-none"
                                    placeholder="Enter Treasury Challan No, date, DCRG recovery voucher particulars..."
                                />
                            </div>

                            <label className="flex items-center gap-2 cursor-pointer text-xs text-slate-300">
                                <input
                                    type="checkbox"
                                    checked={recoveryForm.close_minus_balance}
                                    onChange={(e) => setRecoveryForm({ ...recoveryForm, close_minus_balance: e.target.checked })}
                                    className="rounded border-slate-700 bg-slate-950 text-indigo-600 focus:ring-indigo-500"
                                />
                                <span>Mark Minus Balance as Fully Settled / Closed</span>
                            </label>

                            <div className="flex justify-end gap-2 pt-3 border-t border-slate-800">
                                <button
                                    type="button"
                                    onClick={() => setRecoveryModalCase(null)}
                                    className="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-xl transition"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    className="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs rounded-xl transition flex items-center gap-1.5"
                                >
                                    <CheckCircle2 className="w-4 h-4" />
                                    <span>Save Recovery</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Corrigendum Builder Modal */}
            {corrigendumModalCase && (
                <div className="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="bg-slate-900 border border-slate-800 rounded-2xl max-w-xl w-full p-6 space-y-4 shadow-2xl">
                        <div className="flex items-center justify-between border-b border-slate-800 pb-3">
                            <h3 className="text-base font-bold text-white flex items-center gap-2">
                                <Edit3 className="w-5 h-5 text-amber-400" />
                                <span>Generate Corrigendum Order</span>
                            </h3>
                            <button
                                onClick={() => setCorrigendumModalCase(null)}
                                className="text-slate-400 hover:text-white"
                            >
                                ✕
                            </button>
                        </div>

                        <form onSubmit={submitCorrigendum} className="space-y-4">
                            <div>
                                <label className="block text-xs font-semibold text-slate-300 mb-1">
                                    Amendment Matter *
                                </label>
                                <textarea
                                    required
                                    rows="5"
                                    value={corrigendumForm.matter}
                                    onChange={(e) => setCorrigendumForm({ ...corrigendumForm, matter: e.target.value })}
                                    className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:border-indigo-500 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-slate-300 mb-1">
                                    Copy Forwarded (Endorsement)
                                </label>
                                <textarea
                                    rows="3"
                                    value={corrigendumForm.copy_to}
                                    onChange={(e) => setCorrigendumForm({ ...corrigendumForm, copy_to: e.target.value })}
                                    className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:border-indigo-500 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-slate-300 mb-1">
                                    Signatory Title
                                </label>
                                <input
                                    type="text"
                                    value={corrigendumForm.signature}
                                    onChange={(e) => setCorrigendumForm({ ...corrigendumForm, signature: e.target.value })}
                                    className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:border-indigo-500 focus:outline-none"
                                />
                            </div>

                            <div className="flex justify-end gap-2 pt-3 border-t border-slate-800">
                                <button
                                    type="button"
                                    onClick={() => setCorrigendumModalCase(null)}
                                    className="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-xl transition"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    className="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white font-semibold text-xs rounded-xl transition flex items-center gap-1.5"
                                >
                                    <Printer className="w-4 h-4" />
                                    <span>Print Corrigendum</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Objection / Return Memo Builder Modal */}
            {objectionModalCase && (
                <div className="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="bg-slate-900 border border-slate-800 rounded-2xl max-w-xl w-full p-6 space-y-4 shadow-2xl max-h-[90vh] overflow-y-auto">
                        <div className="flex items-center justify-between border-b border-slate-800 pb-3">
                            <h3 className="text-base font-bold text-white flex items-center gap-2">
                                <FileX className="w-5 h-5 text-orange-400" />
                                <span>Objection / Return Memorandum to DDO</span>
                            </h3>
                            <button
                                onClick={() => setObjectionModalCase(null)}
                                className="text-slate-400 hover:text-white"
                            >
                                ✕
                            </button>
                        </div>

                        <form onSubmit={submitObjection} className="space-y-4">
                            <div>
                                <label className="block text-xs font-semibold text-slate-300 mb-1">
                                    Opening Statement
                                </label>
                                <textarea
                                    rows="2"
                                    value={objectionForm.custom_remarks}
                                    onChange={(e) => setObjectionForm({ ...objectionForm, custom_remarks: e.target.value })}
                                    className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:border-indigo-500 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-slate-300 mb-2">
                                    Select Objection / Discrepancy Points:
                                </label>
                                <div className="space-y-2 bg-slate-950 p-3 rounded-xl border border-slate-800 max-h-48 overflow-y-auto text-xs">
                                    {objectionForm.points.map((pt, idx) => {
                                        const isChecked = objectionForm.selectedPoints.includes(pt);
                                        return (
                                            <label key={idx} className="flex items-start gap-2 cursor-pointer text-slate-300 hover:text-white">
                                                <input
                                                    type="checkbox"
                                                    checked={isChecked}
                                                    onChange={(e) => {
                                                        const newPts = e.target.checked
                                                            ? [...objectionForm.selectedPoints, pt]
                                                            : objectionForm.selectedPoints.filter(p => p !== pt);
                                                        setObjectionForm({ ...objectionForm, selectedPoints: newPts });
                                                    }}
                                                    className="rounded border-slate-700 bg-slate-900 text-orange-600 focus:ring-orange-500 mt-0.5"
                                                />
                                                <span>{pt}</span>
                                            </label>
                                        );
                                    })}
                                </div>
                            </div>

                            <div className="flex justify-end gap-2 pt-3 border-t border-slate-800">
                                <button
                                    type="button"
                                    onClick={() => setObjectionModalCase(null)}
                                    className="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-xl transition"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    className="px-4 py-2 bg-orange-600 hover:bg-orange-500 text-white font-semibold text-xs rounded-xl transition flex items-center gap-1.5"
                                >
                                    <Printer className="w-4 h-4" />
                                    <span>Print Objection Memo</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
