import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    FileText,
    Calculator,
    Users,
    CheckCircle2,
    Award,
    Clock,
    UserCheck,
    ArrowLeft,
    Shield,
    FileCheck,
    Send,
    ExternalLink
} from 'lucide-react';

export default function Show({ case_data, staff_users }) {
    const { data, setData, post, processing } = useForm({
        assigned_user_id: case_data.assigned_user_id || '',
    });

    const [isAssigning, setIsAssigning] = useState(false);

    const handleAssign = (e) => {
        e.preventDefault();
        post(`/inward/${case_data.id}/assign`, {
            onSuccess: () => setIsAssigning(false),
        });
    };

    const run = case_data.latest_calculation_run;
    const authority = case_data.authorities?.[0];

    return (
        <AuthenticatedLayout title={`Case ${case_data.registration_no}`}>
            <Head title={`Case ${case_data.registration_no} - GPF Final Payment Portal`} />

            <div className="space-y-6 max-w-6xl mx-auto">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <Link
                            href="/inward"
                            className="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition"
                        >
                            <ArrowLeft className="w-4 h-4" />
                        </Link>
                        <div>
                            <div className="flex items-center gap-2">
                                <h2 className="text-xl font-bold tracking-tight text-white font-mono">
                                    {case_data.registration_no}
                                </h2>
                                <span className={`px-2.5 py-0.5 text-[10px] font-semibold rounded-full border ${case_data.current_status?.badge_classes || 'bg-slate-800 text-slate-300'}`}>
                                    {case_data.current_status?.name || 'In Progress'}
                                </span>
                            </div>
                            <p className="text-xs text-slate-400">
                                GPF Account: <strong className="text-slate-200 font-mono">{case_data.formatted_gpf_account}</strong> &bull; Diary: {case_data.diary_number}
                            </p>
                        </div>
                    </div>

                    {/* Action Bar */}
                    <div className="flex flex-wrap items-center gap-2">
                        <Link
                            href={`/calculation/${case_data.id}`}
                            className="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold shadow-lg shadow-blue-600/20 transition"
                        >
                            <Calculator className="w-3.5 h-3.5" />
                            <span>Calculation Sheet</span>
                        </Link>

                        <Link
                            href={`/nominees/${case_data.id}`}
                            className="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-semibold shadow-lg shadow-purple-600/20 transition"
                        >
                            <Users className="w-3.5 h-3.5" />
                            <span>Nominees ({case_data.nominees?.length || 0})</span>
                        </Link>

                        {authority ? (
                            <Link
                                href={`/authority/${authority.id}`}
                                className="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-lg shadow-emerald-600/20 transition"
                            >
                                <Award className="w-3.5 h-3.5" />
                                <span>Authority Order</span>
                            </Link>
                        ) : (
                            <Link
                                href={`/approval`}
                                className="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/20 transition"
                            >
                                <CheckCircle2 className="w-3.5 h-3.5" />
                                <span>Approval Queue</span>
                            </Link>
                        )}
                    </div>
                </div>

                {/* Grid Overview */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Left 2 Cols: Details & Financial Summary */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Subscriber Profile */}
                        <div className="glass-panel p-6 rounded-2xl space-y-4">
                            <h3 className="text-sm font-bold text-white border-b border-slate-800 pb-3 flex items-center justify-between">
                                <span>Subscriber Profile & Department</span>
                                <span className="text-xs text-indigo-400 font-mono">
                                    Emp Code: {case_data.employee_code || 'N/A'}
                                </span>
                            </h3>

                            <div className="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                                <div>
                                    <div className="text-slate-500">Full Name</div>
                                    <div className="font-semibold text-slate-200 mt-0.5">
                                        {case_data.name_title} {case_data.subscriber_name_cache}
                                    </div>
                                </div>
                                <div>
                                    <div className="text-slate-500">Designation</div>
                                    <div className="font-semibold text-slate-200 mt-0.5">{case_data.designation}</div>
                                </div>
                                <div>
                                    <div className="text-slate-500">Event Date (Retirement)</div>
                                    <div className="font-semibold text-slate-200 mt-0.5">{case_data.event_date || 'N/A'}</div>
                                </div>
                                <div>
                                    <div className="text-slate-500">DDO Code & Station</div>
                                    <div className="font-semibold text-slate-200 mt-0.5">{case_data.ddo_code}</div>
                                </div>
                                <div>
                                    <div className="text-slate-500">District Treasury</div>
                                    <div className="font-semibold text-slate-200 mt-0.5">{case_data.treasury_code}</div>
                                </div>
                                <div>
                                    <div className="text-slate-500">Mobile / SMS Alerts</div>
                                    <div className="font-semibold text-slate-200 mt-0.5 font-mono">{case_data.mobile_no || 'None'}</div>
                                </div>
                                <div className="sm:col-span-3">
                                    <div className="text-slate-500">Personal Residential Address</div>
                                    <div className="text-slate-300 mt-0.5">{case_data.personal_address}</div>
                                </div>
                            </div>
                        </div>

                        {/* Financial Ledger Snapshot */}
                        <div className="glass-panel p-6 rounded-2xl space-y-4">
                            <h3 className="text-sm font-bold text-white border-b border-slate-800 pb-3 flex items-center justify-between">
                                <span>Certified Settlement Summary</span>
                                {run ? (
                                    <span className="text-xs text-emerald-400 font-semibold flex items-center gap-1">
                                        <CheckCircle2 className="w-3.5 h-3.5" />
                                        <span>Calculation Run Available</span>
                                    </span>
                                ) : (
                                    <span className="text-xs text-amber-400">Calculation Pending</span>
                                )}
                            </h3>

                            {run ? (
                                <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                                    <div className="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                                        <div className="text-slate-500">Opening Balance</div>
                                        <div className="text-sm font-bold text-slate-200 mt-1 font-mono">
                                            ₹ {Number(run.opening_balance_amount).toLocaleString('en-IN')}
                                        </div>
                                    </div>
                                    <div className="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                                        <div className="text-slate-500">Subscriptions</div>
                                        <div className="text-sm font-bold text-slate-200 mt-1 font-mono">
                                            ₹ {Number(run.total_subscriptions).toLocaleString('en-IN')}
                                        </div>
                                    </div>
                                    <div className="p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                                        <div className="text-slate-500">Computed Interest</div>
                                        <div className="text-sm font-bold text-indigo-400 mt-1 font-mono">
                                            ₹ {Number(run.total_interest_computed).toLocaleString('en-IN')}
                                        </div>
                                    </div>
                                    <div className="p-3 rounded-xl bg-emerald-950/20 border border-emerald-800/30">
                                        <div className="text-emerald-400 font-semibold">Net Final Payment</div>
                                        <div className="text-base font-extrabold text-emerald-300 mt-1 font-mono">
                                            ₹ {Number(run.final_closing_balance).toLocaleString('en-IN')}
                                        </div>
                                    </div>
                                </div>
                            ) : (
                                <div className="p-6 text-center text-slate-500 text-xs">
                                    No ledger calculation executed yet. Click "Calculation Sheet" above to compute progressive interest.
                                </div>
                            )}
                        </div>

                        {/* Nominees & Beneficiaries */}
                        {case_data.nominees && case_data.nominees.length > 0 && (
                            <div className="glass-panel p-6 rounded-2xl space-y-4">
                                <h3 className="text-sm font-bold text-white border-b border-slate-800 pb-3 flex items-center justify-between">
                                    <span className="flex items-center gap-2">
                                        <Users className="w-4 h-4 text-purple-400" />
                                        <span>Nominee & Beneficiary Distribution</span>
                                    </span>
                                    <Link
                                        href={`/nominees/${case_data.id}`}
                                        className="text-xs text-purple-400 hover:text-purple-300 font-semibold"
                                    >
                                        Edit Share Matrix →
                                    </Link>
                                </h3>

                                <div className="rounded-xl overflow-hidden border border-slate-800">
                                    <table className="w-full text-left text-xs">
                                        <thead className="bg-slate-900/60 text-slate-400 font-semibold">
                                            <tr>
                                                <th className="py-2.5 px-3">Nominee Name</th>
                                                <th className="py-2.5 px-3">Beneficiary Code</th>
                                                <th className="py-2.5 px-3">Relation</th>
                                                <th className="py-2.5 px-3">Share %</th>
                                                <th className="py-2.5 px-3 text-right">Allocated Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-slate-800/60">
                                            {case_data.nominees.map((n) => (
                                                <tr key={n.id}>
                                                    <td className="py-2.5 px-3 font-semibold text-slate-200">{n.nominee_name}</td>
                                                    <td className="py-2.5 px-3 font-mono text-purple-300">{n.beneficiary_code || 'N/A'}</td>
                                                    <td className="py-2.5 px-3 text-slate-400">{n.relationship}</td>
                                                    <td className="py-2.5 px-3 font-mono">{n.share_percentage}%</td>
                                                    <td className="py-2.5 px-3 text-right font-mono font-bold text-emerald-400">
                                                        ₹ {Number(n.allocated_amount || 0).toLocaleString('en-IN')}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Right Col: Staff Assignment & Workflow Timeline */}
                    <div className="space-y-6">
                        {/* Staff Assignment Card */}
                        <div className="glass-panel p-5 rounded-2xl space-y-3">
                            <h4 className="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-2">
                                <UserCheck className="w-4 h-4 text-indigo-400" />
                                <span>Assigned Officer / Clerk</span>
                            </h4>

                            <div className="text-xs">
                                <div className="font-semibold text-slate-100">
                                    {case_data.assigned_user?.name || 'Unassigned'}
                                </div>
                                <div className="text-[11px] text-slate-400">
                                    {case_data.assigned_user?.role ? `Role: ${case_data.assigned_user.role}` : 'Not allocated'}
                                </div>
                            </div>

                            {isAssigning ? (
                                <form onSubmit={handleAssign} className="pt-2 border-t border-slate-800 space-y-2">
                                    <select
                                        value={data.assigned_user_id}
                                        onChange={(e) => setData('assigned_user_id', e.target.value)}
                                        className="w-full px-2.5 py-1.5 bg-slate-900 border border-slate-700 rounded-lg text-xs text-slate-200"
                                        required
                                    >
                                        <option value="">Select Staff</option>
                                        {staff_users.map((u) => (
                                            <option key={u.id} value={u.id}>{u.name} ({u.role})</option>
                                        ))}
                                    </select>
                                    <div className="flex gap-2">
                                        <button
                                            type="submit"
                                            disabled={processing}
                                            className="px-3 py-1 bg-indigo-600 hover:bg-indigo-500 rounded-lg text-[11px] font-semibold text-white transition"
                                        >
                                            Save
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => setIsAssigning(false)}
                                            className="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 rounded-lg text-[11px] text-slate-400 transition"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            ) : (
                                <button
                                    type="button"
                                    onClick={() => setIsAssigning(true)}
                                    className="text-[11px] font-semibold text-indigo-400 hover:text-indigo-300 block transition"
                                >
                                    + Reassign Docket
                                </button>
                            )}
                        </div>

                        {/* Audit Trail Timeline */}
                        <div className="glass-panel p-5 rounded-2xl space-y-4">
                            <h4 className="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-2">
                                <Clock className="w-4 h-4 text-indigo-400" />
                                <span>Workflow Audit Timeline</span>
                            </h4>

                            <div className="space-y-3">
                                {case_data.workflow_histories?.map((h) => (
                                    <div key={h.id} className="relative pl-5 pb-2 border-l border-slate-800 last:border-l-0 text-xs">
                                        <div className="absolute -left-1 top-0.5 w-2 h-2 rounded-full bg-indigo-400"></div>
                                        <div className="font-semibold text-slate-200">
                                            {h.performed_by_user?.name || 'Officer'}
                                        </div>
                                        <div className="text-[10px] text-indigo-400 font-medium">
                                            {h.action_type}
                                        </div>
                                        <p className="text-[11px] text-slate-400 mt-0.5">{h.remarks}</p>
                                        <span className="text-[9px] text-slate-500 mt-0.5 block">
                                            {new Date(h.created_at).toLocaleString('en-IN')}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
