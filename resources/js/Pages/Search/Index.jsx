import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    Search,
    Filter,
    Layers,
    Eye,
    Calendar,
    User,
    Users,
    DollarSign,
    ClipboardList,
    Clock,
    FileText,
    Shield,
    CheckCircle2,
    X,
    Building,
    CreditCard,
    AlertCircle,
    ArrowRight
} from 'lucide-react';

export default function SearchIndex({ cases, series_list, pension_types, statuses, filters }) {
    const [searchType, setSearchType] = useState(filters?.search_type || 'code');
    const [queryTerm, setQueryTerm] = useState(filters?.query || '');
    const [seriesId, setSeriesId] = useState(filters?.series_id || '');
    const [accountNo, setAccountNo] = useState(filters?.account_no || '');
    const [selectedStatus, setSelectedStatus] = useState(filters?.status || '');
    const [selectedPension, setSelectedPension] = useState(filters?.pension_type_id || '');
    const [dateFrom, setDateFrom] = useState(filters?.date_from || '');
    const [dateTo, setDateTo] = useState(filters?.date_to || '');

    // Inspection Drawer State
    const [inspectCaseId, setInspectCaseId] = useState(null);
    const [inspectData, setInspectData] = useState(null);
    const [inspectLoading, setInspectLoading] = useState(false);
    const [activeTab, setActiveTab] = useState('basic');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get('/search', {
            search_type: searchType,
            query: queryTerm,
            series_id: seriesId,
            account_no: accountNo,
            status: selectedStatus,
            pension_type_id: selectedPension,
            date_from: dateFrom,
            date_to: dateTo,
        }, { preserveState: true });
    };

    const handleReset = () => {
        setSearchType('code');
        setQueryTerm('');
        setSeriesId('');
        setAccountNo('');
        setSelectedStatus('');
        setSelectedPension('');
        setDateFrom('');
        setDateTo('');
        router.get('/search');
    };

    const openInspectDrawer = async (caseId) => {
        setInspectCaseId(caseId);
        setInspectLoading(true);
        setActiveTab('basic');
        try {
            const res = await fetch(`/search/${caseId}/inspect`);
            const data = await res.json();
            setInspectData(data);
        } catch (err) {
            console.error('Failed to load inspection data', err);
        } finally {
            setInspectLoading(false);
        }
    };

    return (
        <AuthenticatedLayout title="Global Case Search & Deep Docket Inspector">
            <Head title="Case Search & Audit Inspector - GPF Final Payment Portal" />

            <div className="space-y-6">
                {/* Header */}
                <div>
                    <h2 className="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                        <Search className="w-6 h-6 text-indigo-400" />
                        <span>Advanced Global Case Search</span>
                    </h2>
                    <p className="text-xs text-slate-400 mt-1">
                        Comprehensive inquiry across all historical and active final payment dockets with multi-attribute filtering and 6-tab deep inspection.
                    </p>
                </div>

                {/* Filter Console */}
                <form onSubmit={handleSearch} className="glass-panel p-5 rounded-2xl space-y-4 border border-slate-800">
                    <div className="flex flex-wrap items-center gap-3">
                        <span className="text-xs font-semibold text-slate-400 uppercase tracking-wider">Search By:</span>
                        <div className="flex items-center gap-1.5 p-1 bg-slate-900/80 border border-slate-800 rounded-xl text-xs">
                            <button
                                type="button"
                                onClick={() => setSearchType('code')}
                                className={`px-3 py-1.5 rounded-lg font-medium transition ${searchType === 'code' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white'}`}
                            >
                                Reg / Emp / Beneficiary Code
                            </button>
                            <button
                                type="button"
                                onClick={() => setSearchType('account')}
                                className={`px-3 py-1.5 rounded-lg font-medium transition ${searchType === 'account' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white'}`}
                            >
                                GPF Account No (Series / Number)
                            </button>
                            <button
                                type="button"
                                onClick={() => setSearchType('name')}
                                className={`px-3 py-1.5 rounded-lg font-medium transition ${searchType === 'name' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white'}`}
                            >
                                Subscriber Name
                            </button>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-3 text-xs">
                        {searchType === 'account' ? (
                            <>
                                <div>
                                    <label className="block text-slate-400 font-semibold mb-1">GPF Series</label>
                                    <select
                                        value={seriesId}
                                        onChange={(e) => setSeriesId(e.target.value)}
                                        className="w-full px-3 py-2 bg-slate-900/80 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:border-indigo-500"
                                    >
                                        <option value="">-- All Series --</option>
                                        {series_list?.map((s) => (
                                            <option key={s.id} value={s.id}>
                                                {s.series_name} ({s.id})
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-slate-400 font-semibold mb-1">Account Number</label>
                                    <input
                                        type="text"
                                        placeholder="e.g. 12345"
                                        value={accountNo}
                                        onChange={(e) => setAccountNo(e.target.value)}
                                        className="w-full px-3 py-2 bg-slate-900/80 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:border-indigo-500"
                                    />
                                </div>
                            </>
                        ) : (
                            <div className="md:col-span-2">
                                <label className="block text-slate-400 font-semibold mb-1">
                                    {searchType === 'code' ? 'Registration / Employee / Beneficiary Number' : 'Subscriber Full Name'}
                                </label>
                                <input
                                    type="text"
                                    placeholder={searchType === 'code' ? 'Enter exact or partial code (e.g. 2026...)' : 'Enter subscriber name...'}
                                    value={queryTerm}
                                    onChange={(e) => setQueryTerm(e.target.value)}
                                    className="w-full px-3 py-2 bg-slate-900/80 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:border-indigo-500"
                                />
                            </div>
                        )}

                        <div>
                            <label className="block text-slate-400 font-semibold mb-1">Workflow Status</label>
                            <select
                                value={selectedStatus}
                                onChange={(e) => setSelectedStatus(e.target.value)}
                                className="w-full px-3 py-2 bg-slate-900/80 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:border-indigo-500"
                            >
                                <option value="">-- All Statuses --</option>
                                {statuses?.map((st) => (
                                    <option key={st.id} value={st.id}>
                                        {st.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-slate-400 font-semibold mb-1">Pension Type</label>
                            <select
                                value={selectedPension}
                                onChange={(e) => setSelectedPension(e.target.value)}
                                className="w-full px-3 py-2 bg-slate-900/80 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:border-indigo-500"
                            >
                                <option value="">-- All Categories --</option>
                                {pension_types?.map((pt) => (
                                    <option key={pt.id} value={pt.id}>
                                        {pt.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-slate-400 font-semibold mb-1">Inward Date From</label>
                            <input
                                type="date"
                                value={dateFrom}
                                onChange={(e) => setDateFrom(e.target.value)}
                                className="w-full px-3 py-2 bg-slate-900/80 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:border-indigo-500"
                            />
                        </div>

                        <div>
                            <label className="block text-slate-400 font-semibold mb-1">Inward Date To</label>
                            <input
                                type="date"
                                value={dateTo}
                                onChange={(e) => setDateTo(e.target.value)}
                                className="w-full px-3 py-2 bg-slate-900/80 border border-slate-700/80 rounded-xl text-white focus:outline-none focus:border-indigo-500"
                            />
                        </div>

                        <div className="md:col-span-2 flex items-end gap-2">
                            <button
                                type="submit"
                                className="flex-1 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl transition flex items-center justify-center gap-1.5"
                            >
                                <Search className="w-4 h-4" />
                                <span>Execute Search</span>
                            </button>
                            <button
                                type="button"
                                onClick={handleReset}
                                className="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-xl transition"
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                </form>

                {/* Results Table */}
                <div className="glass-panel rounded-2xl overflow-hidden border border-slate-800/80">
                    <div className="p-4 border-b border-slate-800 flex items-center justify-between">
                        <span className="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Found {cases.total || 0} Docket Record(s)
                        </span>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-slate-900/80 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider">
                                <tr>
                                    <th className="py-3 px-4">Registration No</th>
                                    <th className="py-3 px-4">GPF Account</th>
                                    <th className="py-3 px-4">Subscriber Name</th>
                                    <th className="py-3 px-4">Codes</th>
                                    <th className="py-3 px-4">Category</th>
                                    <th className="py-3 px-4">Status</th>
                                    <th className="py-3 px-4 text-right">Net Payable</th>
                                    <th className="py-3 px-4 text-center">Inspect</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/60 font-mono">
                                {cases.data?.length > 0 ? (
                                    cases.data.map((c) => (
                                        <tr key={c.id} className="hover:bg-slate-900/40 transition">
                                            <td className="py-3 px-4 font-bold text-slate-100 font-mono">
                                                {c.registration_no}
                                            </td>

                                            <td className="py-3 px-4 text-indigo-400 font-bold">
                                                {c.gpf_account}
                                            </td>

                                            <td className="py-3 px-4 font-sans font-semibold text-slate-200">
                                                {c.subscriber_name}
                                                <div className="text-[10px] text-slate-400 font-normal">{c.designation}</div>
                                            </td>

                                            <td className="py-3 px-4 text-[11px] text-slate-400">
                                                <div>Emp: {c.employee_code || '-'}</div>
                                                <div>Ben: {c.beneficiary_code || '-'}</div>
                                            </td>

                                            <td className="py-3 px-4 font-sans text-slate-300">
                                                {c.pension_type}
                                            </td>

                                            <td className="py-3 px-4">
                                                <span className={`inline-block px-2 py-0.5 text-[10px] font-semibold border rounded-full ${c.status_badge}`}>
                                                    {c.status_label}
                                                </span>
                                            </td>

                                            <td className="py-3 px-4 text-right font-bold text-slate-100">
                                                ₹ {c.net_amount?.toFixed(2)}
                                            </td>

                                            <td className="py-3 px-4 text-center font-sans">
                                                <button
                                                    onClick={() => openInspectDrawer(c.id)}
                                                    className="px-2.5 py-1 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 rounded-lg text-[11px] font-medium transition inline-flex items-center gap-1"
                                                >
                                                    <Eye className="w-3 h-3 text-indigo-400" />
                                                    <span>Inspect</span>
                                                </button>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="8" className="py-12 text-center text-slate-500">
                                            No matching records found.
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

            {/* 6-Tab Deep Docket Inspector Modal / Drawer */}
            {inspectCaseId && (
                <div className="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="bg-slate-900 border border-slate-800 rounded-2xl max-w-4xl w-full p-6 space-y-4 shadow-2xl max-h-[92vh] flex flex-col">
                        {/* Drawer Header */}
                        <div className="flex items-center justify-between border-b border-slate-800 pb-3">
                            <div className="flex items-center gap-3">
                                <div className="p-2 rounded-xl bg-indigo-600/20 text-indigo-400 border border-indigo-500/30">
                                    <Layers className="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 className="text-base font-bold text-white flex items-center gap-2">
                                        <span>Docket Deep-Audit Inspector</span>
                                        {inspectData && (
                                            <span className="font-mono text-sm text-indigo-300">
                                                [{inspectData.basic_info?.registration_no}]
                                            </span>
                                        )}
                                    </h3>
                                    <p className="text-[11px] text-slate-400">
                                        Comprehensive 360° record examination across demographic, nominee, ledger, and workflow history.
                                    </p>
                                </div>
                            </div>
                            <button
                                onClick={() => setInspectCaseId(null)}
                                className="text-slate-400 hover:text-white p-1"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>

                        {inspectLoading ? (
                            <div className="py-24 text-center text-slate-400 space-y-2">
                                <div className="w-8 h-8 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin mx-auto" />
                                <div className="text-xs">Loading complete docket details...</div>
                            </div>
                        ) : inspectData ? (
                            <>
                                {/* 6 Tabs Navigation */}
                                <div className="flex items-center gap-1 border-b border-slate-800 overflow-x-auto text-xs">
                                    <button
                                        onClick={() => setActiveTab('basic')}
                                        className={`px-3 py-2 font-medium border-b-2 transition flex items-center gap-1.5 whitespace-nowrap ${activeTab === 'basic' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-slate-400 hover:text-slate-200'}`}
                                    >
                                        <User className="w-3.5 h-3.5" />
                                        <span>1. Basic Info</span>
                                    </button>
                                    <button
                                        onClick={() => setActiveTab('nominees')}
                                        className={`px-3 py-2 font-medium border-b-2 transition flex items-center gap-1.5 whitespace-nowrap ${activeTab === 'nominees' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-slate-400 hover:text-slate-200'}`}
                                    >
                                        <Users className="w-3.5 h-3.5" />
                                        <span>2. Nominees ({inspectData.nominees?.length || 0})</span>
                                    </button>
                                    <button
                                        onClick={() => setActiveTab('timeline')}
                                        className={`px-3 py-2 font-medium border-b-2 transition flex items-center gap-1.5 whitespace-nowrap ${activeTab === 'timeline' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-slate-400 hover:text-slate-200'}`}
                                    >
                                        <Clock className="w-3.5 h-3.5" />
                                        <span>3. Date Timeline</span>
                                    </button>
                                    <button
                                        onClick={() => setActiveTab('financials')}
                                        className={`px-3 py-2 font-medium border-b-2 transition flex items-center gap-1.5 whitespace-nowrap ${activeTab === 'financials' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-slate-400 hover:text-slate-200'}`}
                                    >
                                        <DollarSign className="w-3.5 h-3.5" />
                                        <span>4. Financial Ledger</span>
                                    </button>
                                    <button
                                        onClick={() => setActiveTab('remarks')}
                                        className={`px-3 py-2 font-medium border-b-2 transition flex items-center gap-1.5 whitespace-nowrap ${activeTab === 'remarks' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-slate-400 hover:text-slate-200'}`}
                                    >
                                        <ClipboardList className="w-3.5 h-3.5" />
                                        <span>5. Remarks & Recovery</span>
                                    </button>
                                    <button
                                        onClick={() => setActiveTab('workflow')}
                                        className={`px-3 py-2 font-medium border-b-2 transition flex items-center gap-1.5 whitespace-nowrap ${activeTab === 'workflow' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-slate-400 hover:text-slate-200'}`}
                                    >
                                        <Shield className="w-3.5 h-3.5" />
                                        <span>6. Workflow Audit ({inspectData.workflow_logs?.length || 0})</span>
                                    </button>
                                </div>

                                {/* Tab Body */}
                                <div className="flex-1 overflow-y-auto p-2 text-xs space-y-4">
                                    {/* Tab 1: Basic Info */}
                                    {activeTab === 'basic' && (
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-3 bg-slate-950/60 p-4 rounded-xl border border-slate-800">
                                            <div><span className="text-slate-500">Subscriber Name:</span> <strong className="text-slate-100">{inspectData.basic_info.subscriber_name}</strong></div>
                                            <div><span className="text-slate-500">GPF Account No:</span> <strong className="text-indigo-400 font-mono">{inspectData.basic_info.gpf_account}</strong></div>
                                            <div><span className="text-slate-500">Designation:</span> <span className="text-slate-200">{inspectData.basic_info.designation}</span></div>
                                            <div><span className="text-slate-500">Pension Category:</span> <span className="text-slate-200">{inspectData.basic_info.pension_type}</span></div>
                                            <div><span className="text-slate-500">Employee Code:</span> <span className="text-slate-200 font-mono">{inspectData.basic_info.employee_code || '-'}</span></div>
                                            <div><span className="text-slate-500">Beneficiary Code:</span> <span className="text-slate-200 font-mono">{inspectData.basic_info.beneficiary_code || '-'}</span></div>
                                            <div><span className="text-slate-500">DDO Code & Desg:</span> <span className="text-slate-200">{inspectData.basic_info.ddo_code} - {inspectData.basic_info.ddo_designation}</span></div>
                                            <div><span className="text-slate-500">Treasury:</span> <span className="text-slate-200">{inspectData.basic_info.treasury_name} ({inspectData.basic_info.treasury_code})</span></div>
                                            <div className="md:col-span-2"><span className="text-slate-500">Personal Address:</span> <span className="text-slate-200">{inspectData.basic_info.personal_address || 'N/A'}</span></div>
                                            <div><span className="text-slate-500">Mobile No:</span> <span className="text-slate-200">{inspectData.basic_info.mobile_no || 'N/A'}</span></div>
                                            <div><span className="text-slate-500">Assigned Officer:</span> <span className="text-slate-200 font-semibold">{inspectData.basic_info.assigned_user || 'Unassigned'}</span></div>
                                            {inspectData.basic_info.spouse_name && (
                                                <div className="md:col-span-2"><span className="text-slate-500">Spouse / Relation:</span> <span className="text-slate-200">{inspectData.basic_info.spouse_name} ({inspectData.basic_info.spouse_relation})</span></div>
                                            )}
                                        </div>
                                    )}

                                    {/* Tab 2: Nominees */}
                                    {activeTab === 'nominees' && (
                                        <div className="space-y-3">
                                            {inspectData.nominees?.length > 0 ? (
                                                <div className="border border-slate-800 rounded-xl overflow-hidden">
                                                    <table className="w-full text-left text-xs">
                                                        <thead className="bg-slate-950 text-slate-400 font-semibold uppercase">
                                                            <tr>
                                                                <th className="py-2.5 px-3">Nominee Name</th>
                                                                <th className="py-2.5 px-3">Relationship</th>
                                                                <th className="py-2.5 px-3">Beneficiary Code</th>
                                                                <th className="py-2.5 px-3 text-right">Share %</th>
                                                                <th className="py-2.5 px-3 text-right">Allocated Amount</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody className="divide-y divide-slate-800">
                                                            {inspectData.nominees.map((n) => (
                                                                <tr key={n.id} className="hover:bg-slate-950/40">
                                                                    <td className="py-2 px-3 font-semibold text-slate-100">{n.name}</td>
                                                                    <td className="py-2 px-3 text-slate-300">{n.relationship}</td>
                                                                    <td className="py-2 px-3 font-mono text-indigo-300">{n.beneficiary_code || '-'}</td>
                                                                    <td className="py-2 px-3 text-right font-mono">{n.share_percentage}%</td>
                                                                    <td className="py-2 px-3 text-right font-mono font-bold text-emerald-400">₹ {n.allocated_amount?.toFixed(2)}</td>
                                                                </tr>
                                                            ))}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            ) : (
                                                <div className="p-8 text-center text-slate-500 bg-slate-950/40 rounded-xl">
                                                    No nominees recorded for this docket.
                                                </div>
                                            )}
                                        </div>
                                    )}

                                    {/* Tab 3: Timeline */}
                                    {activeTab === 'timeline' && (
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-3 bg-slate-950/60 p-4 rounded-xl border border-slate-800">
                                            <div><span className="text-slate-500">Diary Date:</span> <strong className="text-slate-200">{inspectData.timeline.diary_date || '-'}</strong></div>
                                            <div><span className="text-slate-500">Date of Effect (Retirement/Demise):</span> <strong className="text-slate-200">{inspectData.timeline.event_date || '-'}</strong></div>
                                            <div><span className="text-slate-500">Last Fund Deduction:</span> <strong className="text-slate-200">{inspectData.timeline.last_fund_deduction || '-'}</strong></div>
                                            <div><span className="text-slate-500">Inward Registered At:</span> <span className="text-slate-200">{inspectData.timeline.created_at}</span></div>
                                            <div><span className="text-slate-500">Calculated At:</span> <span className="text-slate-200">{inspectData.timeline.calculated_at || 'Pending'}</span></div>
                                            <div><span className="text-slate-500">Checked by AAO:</span> <span className="text-slate-200">{inspectData.timeline.checked_at || 'Pending'}</span></div>
                                            <div><span className="text-slate-500">Approved by Sr. AO:</span> <span className="text-slate-200">{inspectData.timeline.approved_at || 'Pending'}</span></div>
                                            <div><span className="text-slate-500">Digitally Authorized (DSC):</span> <span className="text-slate-200">{inspectData.timeline.authorized_at || 'Pending'}</span></div>
                                            <div><span className="text-slate-500">eHRMS Synced:</span> <span className="text-slate-200">{inspectData.timeline.hrms_uploaded_at || 'Pending'}</span></div>
                                            <div><span className="text-slate-500">Dispatched:</span> <span className="text-slate-200">{inspectData.timeline.dispatched_at || 'Pending'}</span></div>
                                        </div>
                                    )}

                                    {/* Tab 4: Financial Ledger */}
                                    {activeTab === 'financials' && (
                                        inspectData.financials ? (
                                            <div className="space-y-4">
                                                <div className="grid grid-cols-2 sm:grid-cols-3 gap-3 bg-slate-950/60 p-4 rounded-xl border border-slate-800">
                                                    <div><span className="text-slate-500">Base Financial Year:</span> <div className="font-bold text-slate-100">{inspectData.financials.opening_fin_year}</div></div>
                                                    <div><span className="text-slate-500">Opening Balance:</span> <div className="font-bold text-slate-100">₹ {inspectData.financials.opening_balance.toFixed(2)}</div></div>
                                                    <div><span className="text-slate-500">Total Deposits:</span> <div className="font-bold text-emerald-400">₹ {inspectData.financials.total_subscriptions.toFixed(2)}</div></div>
                                                    <div><span className="text-slate-500">Total Withdrawals:</span> <div className="font-bold text-rose-400">₹ {inspectData.financials.total_withdrawals.toFixed(2)}</div></div>
                                                    <div><span className="text-slate-500">Actual Interest:</span> <div className="font-bold text-indigo-400">₹ {inspectData.financials.actual_interest.toFixed(2)}</div></div>
                                                    <div><span className="text-slate-500">Delayed Interest:</span> <div className="font-bold text-amber-400">₹ {inspectData.financials.delayed_interest.toFixed(2)}</div></div>
                                                    {inspectData.financials.dlis_admissible && (
                                                        <div><span className="text-slate-500">DLIS Coverage:</span> <div className="font-bold text-teal-400">₹ {inspectData.financials.dlis_amount.toFixed(2)}</div></div>
                                                    )}
                                                    <div className="col-span-2 sm:col-span-3 pt-2 border-t border-slate-800 flex justify-between items-center">
                                                        <span className="text-slate-300 font-bold uppercase">Final Closing Balance Net Payable:</span>
                                                        <span className="text-base font-bold text-emerald-400 font-mono">₹ {inspectData.financials.final_closing_balance.toFixed(2)}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        ) : (
                                            <div className="p-8 text-center text-slate-500 bg-slate-950/40 rounded-xl">
                                                Calculation has not yet been executed for this docket.
                                            </div>
                                        )
                                    )}

                                    {/* Tab 5: Remarks & Recovery */}
                                    {activeTab === 'remarks' && (
                                        <div className="space-y-3 bg-slate-950/60 p-4 rounded-xl border border-slate-800">
                                            <div>
                                                <span className="text-slate-500 font-semibold block mb-0.5">Delay Justification (Rule 11(4)):</span>
                                                <div className="text-slate-200 bg-slate-900 p-2.5 rounded-lg">
                                                    {inspectData.remarks.delay_justification || 'No delay remarks recorded.'}
                                                </div>
                                            </div>
                                            {inspectData.remarks.transfer_remarks && (
                                                <div>
                                                    <span className="text-slate-500 font-semibold block mb-0.5">Sectional Receipt Transfer Remarks:</span>
                                                    <div className="text-slate-200 bg-slate-900 p-2.5 rounded-lg">
                                                        {inspectData.remarks.transfer_remarks}
                                                    </div>
                                                </div>
                                            )}
                                            {inspectData.remarks.minus_balance_remarks && (
                                                <div>
                                                    <span className="text-rose-400 font-semibold block mb-0.5">Minus Balance Recovery Remarks:</span>
                                                    <div className="text-slate-200 bg-slate-900 p-2.5 rounded-lg border border-rose-900/30">
                                                        {inspectData.remarks.minus_balance_remarks}
                                                        <div className="text-[10px] text-emerald-400 mt-1">Recovered: ₹ {inspectData.remarks.amount_recovered?.toFixed(2)}</div>
                                                    </div>
                                                </div>
                                            )}
                                            {inspectData.remarks.unapproved_remarks && (
                                                <div>
                                                    <span className="text-amber-400 font-semibold block mb-0.5">Un-Approval Remarks:</span>
                                                    <div className="text-slate-200 bg-slate-900 p-2.5 rounded-lg border border-amber-900/30">
                                                        {inspectData.remarks.unapproved_remarks}
                                                    </div>
                                                </div>
                                            )}
                                            {inspectData.remarks.cancelled_remarks && (
                                                <div>
                                                    <span className="text-rose-400 font-semibold block mb-0.5">Cancellation Justification:</span>
                                                    <div className="text-slate-200 bg-slate-900 p-2.5 rounded-lg border border-rose-900/30">
                                                        {inspectData.remarks.cancelled_remarks}
                                                    </div>
                                                </div>
                                            )}
                                        </div>
                                    )}

                                    {/* Tab 6: Workflow Audit Logs */}
                                    {activeTab === 'workflow' && (
                                        <div className="space-y-2">
                                            {inspectData.workflow_logs?.map((w, i) => (
                                                <div key={i} className="p-3 rounded-xl bg-slate-950 border border-slate-800 flex items-start gap-3">
                                                    <div className="w-6 h-6 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-[10px] mt-0.5 shrink-0">
                                                        {i + 1}
                                                    </div>
                                                    <div className="flex-1">
                                                        <div className="flex items-center justify-between">
                                                            <span className="font-bold text-slate-200">{w.action_type}</span>
                                                            <span className="text-[10px] text-slate-500">{w.created_at}</span>
                                                        </div>
                                                        <div className="text-[11px] text-slate-400 mt-0.5">
                                                            By: <strong className="text-slate-300">{w.officer_name}</strong> ({w.officer_role}) | IP: {w.ip_address || 'Local'}
                                                        </div>
                                                        {w.remarks && (
                                                            <div className="text-[11px] text-slate-300 bg-slate-900/80 p-2 rounded-lg mt-1 border border-slate-800/80">
                                                                {w.remarks}
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            </>
                        ) : null}

                        {/* Drawer Footer Actions */}
                        <div className="border-t border-slate-800 pt-3 flex items-center justify-between">
                            <div className="text-xs text-slate-500">
                                Institutional Audit Record • AG Tripura
                            </div>
                            <div className="flex items-center gap-2">
                                {inspectData && (
                                    <a
                                        href={`/letters/input-sheet/${inspectCaseId}`}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold transition flex items-center gap-1"
                                    >
                                        <FileText className="w-3.5 h-3.5 text-indigo-400" />
                                        <span>Print Input Sheet</span>
                                    </a>
                                )}
                                <button
                                    onClick={() => setInspectCaseId(null)}
                                    className="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold transition"
                                >
                                    Close Inspector
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
