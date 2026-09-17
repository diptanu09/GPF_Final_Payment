import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    Users,
    ArrowLeft,
    CheckCircle2,
    FileText,
    Calculator,
    Award,
    Clock,
    TrendingUp
} from 'lucide-react';

export default function UserProductivity({ productivity }) {
    return (
        <AuthenticatedLayout title="Officer Productivity Matrix">
            <Head title="Officer Productivity Matrix - GPF Final Payment Portal" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <div className="flex items-center gap-2 mb-1">
                            <Link
                                href="/reports"
                                className="text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white text-xs flex items-center gap-1 transition"
                            >
                                <ArrowLeft className="w-3.5 h-3.5" />
                                <span>Back to MIS Hub</span>
                            </Link>
                        </div>
                        <h2 className="text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                            <Users className="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                            <span>Staff Productivity & Activity Metrics</span>
                        </h2>
                        <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Institutional output breakdown: Case inward registration, calculation executions, audit verifications, approvals, and digital signatures.
                        </p>
                    </div>
                </div>

                <div className="glass-panel app-card rounded-2xl overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-slate-50 dark:bg-slate-900/80 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 font-semibold uppercase tracking-wider">
                                <tr>
                                    <th className="py-3 px-4">Officer Name & Role</th>
                                    <th className="py-3 px-4">Designation & Section</th>
                                    <th className="py-3 px-4 text-center">Inward Reg</th>
                                    <th className="py-3 px-4 text-center">Calculated</th>
                                    <th className="py-3 px-4 text-center">AAO Checked</th>
                                    <th className="py-3 px-4 text-center">AO Approved</th>
                                    <th className="py-3 px-4 text-center">DSC Signed</th>
                                    <th className="py-3 px-4 text-center">Active Queue</th>
                                    <th className="py-3 px-4 text-right">Total Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 dark:divide-slate-800/60 font-mono">
                                {productivity?.map((u) => (
                                    <tr key={u.id} className="hover:bg-slate-50/80 dark:hover:bg-slate-900/40 transition">
                                        <td className="py-3 px-4 font-sans">
                                            <div className="font-bold text-slate-900 dark:text-slate-200">{u.name}</div>
                                            <div className="text-[10px] text-indigo-600 dark:text-indigo-400 font-mono">@{u.username} • {u.role}</div>
                                        </td>

                                        <td className="py-3 px-4 font-sans text-slate-700 dark:text-slate-300">
                                            <div>{u.designation}</div>
                                            <div className="text-[10px] text-slate-400 dark:text-slate-500">{u.section}</div>
                                        </td>

                                        <td className="py-3 px-4 text-center font-bold text-slate-900 dark:text-slate-200">
                                            {u.inward_count}
                                        </td>

                                        <td className="py-3 px-4 text-center font-bold text-blue-600 dark:text-blue-400">
                                            {u.calculated_count}
                                        </td>

                                        <td className="py-3 px-4 text-center font-bold text-indigo-600 dark:text-indigo-400">
                                            {u.checked_count}
                                        </td>

                                        <td className="py-3 px-4 text-center font-bold text-emerald-600 dark:text-emerald-400">
                                            {u.approved_count}
                                        </td>

                                        <td className="py-3 px-4 text-center font-bold text-teal-600 dark:text-teal-400">
                                            {u.signed_count}
                                        </td>

                                        <td className="py-3 px-4 text-center font-bold text-amber-600 dark:text-amber-400">
                                            {u.assigned_active}
                                        </td>

                                        <td className="py-3 px-4 text-right font-bold text-slate-900 dark:text-white text-sm">
                                            {u.total_actions}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
