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
                        <h2 className="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                            <Send className="w-6 h-6 text-cyan-400" />
                            <span>Outward Dispatch & State eHRMS Integration</span>
                        </h2>
                        <p className="text-xs text-slate-400 mt-1">
                            Push signed authorities to the state employee portal and record postal speed-post barcodes.
                        </p>
                    </div>
                </div>

                <div className="glass-panel rounded-2xl overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-slate-900/80 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider">
                                <tr>
                                    <th className="py-3.5 px-4">Authority No</th>
                                    <th className="py-3.5 px-4">Subscriber & DDO</th>
                                    <th className="py-3.5 px-4 text-right">Net Amount</th>
                                    <th className="py-3.5 px-4 text-center">eHRMS Sync Status</th>
                                    <th className="py-3.5 px-4 text-center">Postal Dispatch</th>
                                    <th className="py-3.5 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/60">
                                {authorities.data.length === 0 ? (
                                    <tr>
                                        <td colSpan="6" className="py-12 text-center text-slate-500 text-sm">
                                            No signed authorities ready for dispatch.
                                        </td>
                                    </tr>
                                ) : (
                                    authorities.data.map((a) => (
                                        <tr key={a.id} className="hover:bg-slate-900/40 transition">
                                            <td className="py-3.5 px-4 font-mono font-bold text-indigo-300">
                                                {a.authority_number}
                                                <div className="text-[10px] text-slate-500 font-normal">
                                                    Dated: {a.authority_date}
                                                </div>
                                            </td>
                                            <td className="py-3.5 px-4">
                                                <div className="font-semibold text-slate-200">{a.subscriber_name}</div>
                                                <div className="text-[11px] text-slate-400 font-mono">
                                                    GPF: {a.gpf_account} &bull; DDO: {a.ddo_code}
                                                </div>
                                            </td>
                                            <td className="py-3.5 px-4 text-right font-mono font-bold text-emerald-400 text-sm">
                                                ₹ {Number(a.net_amount).toLocaleString('en-IN')}
                                            </td>
                                            <td className="py-3.5 px-4 text-center">
                                                {a.is_uploaded_hrms ? (
                                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-purple-500/10 text-purple-300 border border-purple-500/20 text-[10px] font-semibold">
                                                        <CheckCircle2 className="w-3 h-3 text-purple-400" />
                                                        <span>Synced ({a.hrms_uploaded_at})</span>
                                                    </span>
                                                ) : (
                                                    <button
                                                        onClick={() => handleUploadHrms(a.id)}
                                                        disabled={processing}
                                                        className="inline-flex items-center gap-1 px-3 py-1 bg-purple-600 hover:bg-purple-500 text-white rounded-lg text-[10px] font-semibold transition"
                                                    >
                                                        <UploadCloud className="w-3 h-3" />
                                                        <span>Push to eHRMS</span>
                                                    </button>
                                                )}
                                            </td>
                                            <td className="py-3.5 px-4 text-center">
                                                {a.is_dispatched ? (
                                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-cyan-500/10 text-cyan-300 border border-cyan-500/20 text-[10px] font-semibold font-mono">
                                                        <span>📦 {a.dispatch_barcode}</span>
                                                    </span>
                                                ) : (
                                                    <button
                                                        onClick={() => {
                                                            setSelectedAuthority(a);
                                                            setBarcodeModalOpen(true);
                                                        }}
                                                        className="inline-flex items-center gap-1 px-3 py-1 bg-cyan-600/80 hover:bg-cyan-600 text-white rounded-lg text-[10px] font-semibold transition"
                                                    >
                                                        <Barcode className="w-3 h-3" />
                                                        <span>Log Dispatch</span>
                                                    </button>
                                                )}
                                            </td>
                                            <td className="py-3.5 px-4 text-right">
                                                <Link
                                                    href={`/authority/${a.id}`}
                                                    className="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-300 hover:text-white bg-slate-800 px-2.5 py-1.5 rounded-lg border border-slate-700"
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
                    <div className="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                        <div className="glass-panel max-w-md w-full p-6 rounded-2xl border border-slate-800 space-y-4">
                            <h3 className="text-sm font-bold text-white flex items-center gap-2">
                                <Barcode className="w-4 h-4 text-cyan-400" />
                                <span>Log Postal Speed-Post Outward</span>
                            </h3>

                            <p className="text-xs text-slate-400">
                                Enter India Post speed post / registered parcel barcode for dispatch tracking.
                            </p>

                            <form onSubmit={handleDispatchSubmit} className="space-y-4">
                                <div>
                                    <label className="block text-xs font-medium text-slate-300 mb-1">
                                        Postal Tracking Barcode <span className="text-rose-400">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={barcode}
                                        onChange={(e) => setBarcode(e.target.value.toUpperCase())}
                                        placeholder="e.g. ED123456789IN"
                                        className="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-slate-100 font-mono uppercase tracking-wider"
                                        required
                                    />
                                </div>

                                <div className="flex items-center justify-end gap-3 pt-2">
                                    <button
                                        type="button"
                                        onClick={() => setBarcodeModalOpen(false)}
                                        className="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={processing || barcode.length < 5}
                                        className="px-5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold shadow-lg shadow-cyan-600/25 disabled:opacity-50"
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
