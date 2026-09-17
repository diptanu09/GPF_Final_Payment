import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    Send,
    UploadCloud,
    Barcode,
    CheckCircle2,
    Building2,
    ArrowUpRight
} from 'lucide-react';

export default function Index({ authorities }) {
    const [selectedAuthority, setSelectedAuthority] = useState(null);
    const [barcodeModalOpen, setBarcodeModalOpen] = useState(false);
    const [barcode, setBarcode] = useState('');

    const { post, processing } = useForm();

    const handleUploadHrms = (id) => {
        if (confirm('Synchronize this digitally signed payment authority with State eHRMS portal?')) {
            post(`/dispatch/${id}/hrms`);
        }
    };

    const handleDispatchSubmit = (e) => {
        e.preventDefault();
        if (!selectedAuthority) return;
        post(`/dispatch/${selectedAuthority.id}/dispatch`, {
            data: { dispatch_barcode: barcode },
            onSuccess: () => {
                setBarcodeModalOpen(false);
                setSelectedAuthority(null);
                setBarcode('');
            },
        });
    };

    return (
        <AuthenticatedLayout title="Outward Dispatch & eHRMS Sync">
            <Head title="Outward Dispatch & eHRMS Sync - GPF Final Payment Portal" />

            <div className="space-y-6">
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                            <Send className="w-6 h-6 text-cyan-600 dark:text-cyan-400" />
                            <span>Outward Dispatch & State eHRMS Integration</span>
                        </h2>
                        <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Push signed authorities to the state employee portal and record postal speed-post barcodes.
                        </p>
                    </div>
                </div>

                <div className="glass-panel app-card rounded-2xl overflow-hidden shadow-sm">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-slate-50 dark:bg-slate-900/80 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 font-semibold uppercase tracking-wider">
                                <tr>
                                    <th className="py-3.5 px-4">Authority No</th>
                                    <th className="py-3.5 px-4">Subscriber & DDO</th>
                                    <th className="py-3.5 px-4 text-right">Net Amount</th>
                                    <th className="py-3.5 px-4 text-center">eHRMS Sync Status</th>
                                    <th className="py-3.5 px-4 text-center">Postal Dispatch</th>
                                    <th className="py-3.5 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 dark:divide-slate-800/60 font-mono">
                                {authorities.data.length === 0 ? (
                                    <tr>
                                        <td colSpan="6" className="py-12 text-center text-slate-500 text-sm">
                                            No signed authorities ready for dispatch.
                                        </td>
                                    </tr>
                                ) : (
                                    authorities.data.map((a) => (
                                        <tr key={a.id} className="hover:bg-slate-50/80 dark:hover:bg-slate-900/40 transition">
                                            <td className="py-3.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                                {a.authority_number}
                                                <div className="text-[10px] text-slate-500 dark:text-slate-400 font-normal">
                                                    Dated: {a.authority_date}
                                                </div>
                                            </td>
                                            <td className="py-3.5 px-4">
                                                <div className="font-semibold text-slate-900 dark:text-slate-100 font-sans">{a.subscriber_name}</div>
                                                <div className="text-[11px] text-slate-500 dark:text-slate-400 font-mono">
                                                    GPF: {a.gpf_account} &bull; DDO: {a.ddo_code}
                                                </div>
                                            </td>
                                            <td className="py-3.5 px-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400 text-sm">
                                                ₹ {Number(a.net_amount).toLocaleString('en-IN')}
                                            </td>
                                            <td className="py-3.5 px-4 text-center font-sans">
                                                {a.is_uploaded_hrms ? (
                                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-purple-50 dark:bg-purple-500/10 text-purple-800 dark:text-purple-300 border border-purple-200 dark:border-purple-500/20 text-[10px] font-semibold">
                                                        <CheckCircle2 className="w-3 h-3 text-purple-600 dark:text-purple-400" />
                                                        <span>Synced ({a.hrms_uploaded_at})</span>
                                                    </span>
                                                ) : (
                                                    <button
                                                        onClick={() => handleUploadHrms(a.id)}
                                                        disabled={processing}
                                                        className="inline-flex items-center gap-1 px-3 py-1 bg-purple-600 hover:bg-purple-500 text-white rounded-lg text-[10px] font-semibold transition shadow-2xs"
                                                    >
                                                        <UploadCloud className="w-3 h-3" />
                                                        <span>Push to eHRMS</span>
                                                    </button>
                                                )}
                                            </td>
                                            <td className="py-3.5 px-4 text-center font-sans">
                                                {a.is_dispatched ? (
                                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-cyan-50 dark:bg-cyan-500/10 text-cyan-800 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-500/20 text-[10px] font-semibold font-mono">
                                                        <span>📦 {a.dispatch_barcode}</span>
                                                    </span>
                                                ) : (
                                                    <button
                                                        onClick={() => {
                                                            setSelectedAuthority(a);
                                                            setBarcodeModalOpen(true);
                                                        }}
                                                        className="inline-flex items-center gap-1 px-3 py-1 bg-cyan-600 hover:bg-cyan-500 text-white rounded-lg text-[10px] font-semibold transition shadow-2xs"
                                                    >
                                                        <Barcode className="w-3 h-3" />
                                                        <span>Log Dispatch</span>
                                                    </button>
                                                )}
                                            </td>
                                            <td className="py-3.5 px-4 text-right font-sans">
                                                <Link
                                                    href={`/authority/${a.id}`}
                                                    className="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 shadow-2xs transition"
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
                </div>

                {/* Postal Dispatch Modal */}
                {barcodeModalOpen && selectedAuthority && (
                    <div className="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                        <div className="glass-panel app-card max-w-md w-full p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 space-y-4 shadow-2xl">
                            <h3 className="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <Barcode className="w-4 h-4 text-cyan-600 dark:text-cyan-400" />
                                <span>Log Postal Speed-Post Outward</span>
                            </h3>

                            <p className="text-xs text-slate-600 dark:text-slate-400">
                                Enter India Post speed post / registered parcel barcode for dispatch tracking.
                            </p>

                            <form onSubmit={handleDispatchSubmit} className="space-y-4">
                                <div>
                                    <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                        Postal Tracking Barcode <span className="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={barcode}
                                        onChange={(e) => setBarcode(e.target.value.toUpperCase())}
                                        placeholder="e.g. ED123456789IN"
                                        className="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 font-mono uppercase tracking-wider shadow-2xs focus:outline-none focus:ring-2 focus:ring-cyan-500"
                                        required
                                    />
                                </div>

                                <div className="flex items-center justify-end gap-3 pt-2 border-t border-slate-200 dark:border-slate-800">
                                    <button
                                        type="button"
                                        onClick={() => setBarcodeModalOpen(false)}
                                        className="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 shadow-2xs transition"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={processing || barcode.length < 5}
                                        className="px-5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold shadow-md shadow-cyan-600/25 disabled:opacity-50 transition"
                                    >
                                        Save Dispatch Log
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
