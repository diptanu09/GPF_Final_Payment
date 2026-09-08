import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    FilePlus,
    Search,
    Filter,
    ArrowUpRight,
    Calculator,
    ChevronLeft,
    ChevronRight,
    FileText
} from 'lucide-react';

export default function Index({ cases, filters, statuses }) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get('/inward', { search, status }, { preserveState: true });
    };

    return (
        <AuthenticatedLayout title="Inward Cases Register">
            <Head title="Inward Cases Register - GPF Final Payment Portal" />

            <div className="space-y-6">
                {/* Header & New Button */}
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                            Inward Case Dockets & Processing
                        </h2>
                        <p className="text-xs text-slate-400 mt-1">
                            Search, track, and process statutory GPF final settlement dockets.
                        </p>
                    </div>

                    <Link
                        href="/inward/create"
                        className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/25 transition"
                    >
                        <FilePlus className="w-4 h-4" />
                        <span>Register New Inward Case</span>
                    </Link>
                </div>

                {/* Filter and Search Bar */}
                <form onSubmit={handleSearch} className="glass-panel p-4 rounded-2xl flex flex-col sm:flex-row items-center gap-3">
                    <div className="relative flex-1 w-full">
                        <Search className="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2" />
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Search by Registration No, Account No, Subscriber Name, or Employee Code..."
                            className="w-full pl-10 pr-4 py-2 bg-slate-900/80 border border-slate-700/80 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                    </div>

                    <div className="w-full sm:w-64">
                        <select
                            value={status}
                            onChange={(e) => {
                                setStatus(e.target.value);
                                router.get('/inward', { search, status: e.target.value }, { preserveState: true });
                            }}
                            className="w-full px-3 py-2 bg-slate-900/80 border border-slate-700/80 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="">All Workflow Statuses</option>
                            {statuses.map((s) => (
                                <option key={s.id} value={s.id}>{s.name}</option>
                            ))}
                        </select>
                    </div>

                    <button
                        type="submit"
                        className="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl text-xs font-semibold text-slate-200 transition"
                    >
                        Filter
                    </button>
                </form>

                {/* Table */}
                <div className="glass-panel rounded-2xl overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-slate-900/60 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider">
                                <tr>
                                    <th className="py-3.5 px-4">Registration No</th>
                                    <th className="py-3.5 px-4">GPF Account</th>
                                    <th className="py-3.5 px-4">Subscriber Details</th>
                                    <th className="py-3.5 px-4">Case Type</th>
                                    <th className="py-3.5 px-4">Workflow Status</th>
                                    <th className="py-3.5 px-4 text-right">Payable Balance</th>
                                    <th className="py-3.5 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/60">
                                {cases.data.length === 0 ? (
                                    <tr>
                                        <td colSpan="7" className="py-12 text-center text-slate-500 text-sm">
                                            No cases found matching the criteria. Click "Register New Inward Case" to start.
                                        </td>
                                    </tr>
                                ) : (
                                    cases.data.map((c) => (
                                        <tr key={c.id} className="hover:bg-slate-900/40 transition group">
                                            <td className="py-3.5 px-4 font-mono font-bold text-indigo-300">
                                                {c.registration_no}
                                                <div className="text-[10px] text-slate-500 font-normal">
                                                    {c.diary_number}
                                                </div>
                                            </td>
                                            <td className="py-3.5 px-4 font-mono font-semibold text-slate-200">
                                                {c.gpf_account}
                                            </td>
                                            <td className="py-3.5 px-4">
                                                <div className="font-semibold text-slate-200">{c.subscriber_name}</div>
                                                <div className="text-[11px] text-slate-400">{c.designation}</div>
                                            </td>
                                            <td className="py-3.5 px-4">
                                                <span className={`px-2.5 py-0.5 text-[10px] font-semibold rounded-full border ${c.case_type_badge}`}>
                                                    {c.case_type_label}
                                                </span>
                                            </td>
                                            <td className="py-3.5 px-4">
                                                <span className={`px-2.5 py-1 text-[10px] font-semibold rounded-full border ${c.status_badge}`}>
                                                    {c.status_label}
                                                </span>
                                                {c.assigned_user && (
                                                    <div className="text-[10px] text-slate-500 mt-0.5">
                                                        Assigned: {c.assigned_user}
                                                    </div>
                                                )}
                                            </td>
                                            <td className="py-3.5 px-4 text-right font-mono font-bold text-slate-100">
                                                {c.final_amount ? `₹ ${Number(c.final_amount).toLocaleString('en-IN')}` : '—'}
                                            </td>
                                            <td className="py-3.5 px-4 text-right space-x-2">
                                                <Link
                                                    href={`/calculation/${c.id}`}
                                                    className="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-400 hover:text-blue-300 bg-blue-500/10 px-2.5 py-1 rounded-lg border border-blue-500/20"
                                                    title="Open Calculation Sheet"
                                                >
                                                    <Calculator className="w-3 h-3" />
                                                    <span>Ledger</span>
                                                </Link>
                                                <Link
                                                    href={`/inward/${c.id}`}
                                                    className="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-400 hover:text-indigo-300 bg-indigo-500/10 px-2.5 py-1 rounded-lg border border-indigo-500/20"
                                                    title="View Full Docket"
                                                >
                                                    <span>View</span>
                                                    <ArrowUpRight className="w-3 h-3" />
                                                </Link>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {cases.links && cases.links.length > 3 && (
                        <div className="p-4 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
                            <div>
                                Showing <span className="font-semibold text-slate-200">{cases.from || 0}</span> to <span className="font-semibold text-slate-200">{cases.to || 0}</span> of <span className="font-semibold text-slate-200">{cases.total}</span> cases
                            </div>
                            <div className="flex items-center gap-1">
                                {cases.links.map((link, idx) => (
                                    <Link
                                        key={idx}
                                        href={link.url || '#'}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                        className={`px-3 py-1.5 rounded-lg border transition ${
                                            link.active
                                                ? 'bg-indigo-600 text-white border-indigo-500 font-semibold'
                                                : link.url
                                                ? 'bg-slate-900/60 border-slate-800 text-slate-400 hover:bg-slate-800 hover:text-white'
                                                : 'opacity-40 cursor-not-allowed border-slate-900 text-slate-600'
                                        }`}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
