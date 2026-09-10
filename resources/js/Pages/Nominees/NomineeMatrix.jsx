import React, { useState, useMemo } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    Users,
    Plus,
    Trash2,
    Save,
    ArrowLeft,
    CheckCircle2,
    AlertCircle,
    Building2
} from 'lucide-react';

export default function NomineeMatrix({ case_data, nominees, final_amount }) {
    const [nomineeList, setNomineeList] = useState(
        nominees.length > 0
            ? nominees
            : [
                  {
                      nominee_name: case_data.spouse_name || case_data.subscriber_name_cache || '',
                      beneficiary_code: case_data.beneficiary_code || '',
                      relationship: case_data.spouse_relation || 'Spouse',
                      share_percentage: 100.0,
                      bank_account_no: '',
                      bank_ifsc: '',
                      bank_name: 'State Bank of India',
                      guardian_name: '',
                      is_minor: false,
                      address: case_data.personal_address || '',
                  },
              ]
    );

    const { setData, post, processing, errors } = useForm({
        nominees: nomineeList,
    });

    const updateNominee = (index, field, value) => {
        const updated = [...nomineeList];
        updated[index] = { ...updated[index], [field]: value };
        setNomineeList(updated);
        setData('nominees', updated);
    };

    const addNominee = () => {
        const remainingPercentage = Math.max(0, 100 - totalPercentage);
        const newNominee = {
            nominee_name: '',
            beneficiary_code: '',
            relationship: 'Son',
            share_percentage: remainingPercentage,
            bank_account_no: '',
            bank_ifsc: '',
            bank_name: '',
            guardian_name: '',
            is_minor: false,
            address: '',
        };
        const updated = [...nomineeList, newNominee];
        setNomineeList(updated);
        setData('nominees', updated);
    };

    const removeNominee = (index) => {
        const updated = nomineeList.filter((_, i) => i !== index);
        setNomineeList(updated);
        setData('nominees', updated);
    };

    const totalPercentage = useMemo(() => {
        return nomineeList.reduce((acc, n) => acc + (parseFloat(n.share_percentage) || 0), 0);
    }, [nomineeList]);

    const isSumValid = Math.abs(totalPercentage - 100.0) < 0.01;

    const submit = (e) => {
        e.preventDefault();
        setData('nominees', nomineeList);
        post(`/nominees/${case_data.id}`);
    };

    return (
        <AuthenticatedLayout title={`Nominees - ${case_data.registration_no}`}>
            <Head title={`Nominees Matrix - Case ${case_data.registration_no}`} />

            <div className="space-y-6 max-w-5xl mx-auto">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <Link
                            href={`/inward/${case_data.id}`}
                            className="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition"
                        >
                            <ArrowLeft className="w-4 h-4" />
                        </Link>
                        <div>
                            <div className="flex items-center gap-2">
                                <h2 className="text-xl font-bold tracking-tight text-white flex items-center gap-2">
                                    <Users className="w-5 h-5 text-purple-400" />
                                    <span>Nominee / Legal Heir Share Distribution</span>
                                </h2>
                                <span className="text-xs px-2.5 py-0.5 rounded-full bg-purple-500/10 text-purple-300 border border-purple-500/20 font-mono">
                                    {case_data.registration_no}
                                </span>
                            </div>
                            <p className="text-xs text-slate-400">
                                Total Settlement Sum: <strong className="text-emerald-400 font-mono">₹ {Number(final_amount).toLocaleString('en-IN')}</strong>
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        <button
                            type="button"
                            onClick={addNominee}
                            className="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-purple-400 text-xs font-semibold flex items-center gap-1.5 transition"
                        >
                            <Plus className="w-3.5 h-3.5" />
                            <span>Add Beneficiary</span>
                        </button>
                        <button
                            type="button"
                            onClick={submit}
                            disabled={!isSumValid || processing}
                            className="px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-semibold shadow-lg shadow-purple-600/25 flex items-center gap-1.5 transition disabled:opacity-50"
                        >
                            <Save className="w-3.5 h-3.5" />
                            <span>{processing ? 'Saving...' : 'Save Share Matrix'}</span>
                        </button>
                    </div>
                </div>

                {/* Percentage Validation Status Card */}
                <div className={`p-4 rounded-2xl border flex items-center justify-between transition ${
                    isSumValid
                        ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300'
                        : 'bg-rose-500/10 border-rose-500/30 text-rose-300'
                }`}>
                    <div className="flex items-center gap-2.5 text-xs font-semibold">
                        {isSumValid ? (
                            <CheckCircle2 className="w-5 h-5 text-emerald-400" />
                        ) : (
                            <AlertCircle className="w-5 h-5 text-rose-400" />
                        )}
                        <span>
                            {isSumValid
                                ? 'Nominee allocation verified: Total equals exactly 100.00%.'
                                : `Statutory Error: Total sum of shares must equal exactly 100.00%. Current: ${totalPercentage.toFixed(2)}%`}
                        </span>
                    </div>
                    <div className="text-sm font-bold font-mono">
                        {totalPercentage.toFixed(2)}%
                    </div>
                </div>

                {errors.nominees && (
                    <div className="p-3 bg-rose-500/10 border border-rose-500/20 text-rose-300 rounded-xl text-xs">
                        {errors.nominees}
                    </div>
                )}

                {/* Nominee Cards */}
                <div className="space-y-4">
                    {nomineeList.map((nominee, idx) => {
                        const shareRupees = Math.round(((final_amount * (parseFloat(nominee.share_percentage) || 0)) / 100.0) * 100) / 100;

                        return (
                            <div key={idx} className="glass-panel p-5 rounded-2xl relative space-y-4 border border-slate-800">
                                <div className="flex items-center justify-between border-b border-slate-800/80 pb-3">
                                    <div className="flex items-center gap-2">
                                        <span className="w-6 h-6 rounded-full bg-purple-500/20 text-purple-300 text-xs font-bold flex items-center justify-center">
                                            {idx + 1}
                                        </span>
                                        <h3 className="text-xs font-bold text-slate-200 uppercase tracking-wider">
                                            Beneficiary / Nominee #{idx + 1}
                                        </h3>
                                    </div>

                                    <div className="flex items-center gap-4">
                                        <div className="text-right">
                                            <span className="text-[10px] text-slate-500 uppercase tracking-wider block">Allocated Sum</span>
                                            <span className="text-xs font-bold font-mono text-emerald-400">
                                                ₹ {Number(shareRupees).toLocaleString('en-IN')}
                                            </span>
                                        </div>

                                        {nomineeList.length > 1 && (
                                            <button
                                                type="button"
                                                onClick={() => removeNominee(idx)}
                                                className="p-1.5 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 transition"
                                                title="Remove Nominee"
                                            >
                                                <Trash2 className="w-4 h-4" />
                                            </button>
                                        )}
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 sm:grid-cols-6 gap-4 text-xs">
                                    <div className="sm:col-span-2">
                                        <label className="block text-slate-300 font-medium mb-1">
                                            Nominee Legal Name <span className="text-rose-400">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            value={nominee.nominee_name}
                                            onChange={(e) => updateNominee(idx, 'nominee_name', e.target.value)}
                                            placeholder="Full name as per bank record"
                                            className="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-slate-100 font-semibold"
                                            required
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-slate-300 font-medium mb-1">
                                            Beneficiary Code
                                        </label>
                                        <input
                                            type="text"
                                            value={nominee.beneficiary_code || ''}
                                            onChange={(e) => updateNominee(idx, 'beneficiary_code', e.target.value)}
                                            placeholder="e.g. BEN-98421"
                                            className="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-slate-100 font-mono"
                                        />
                                    </div>

                                    <div className="sm:col-span-2">
                                        <label className="block text-slate-300 font-medium mb-1">
                                            Relationship <span className="text-rose-400">*</span>
                                        </label>
                                        <select
                                            value={nominee.relationship}
                                            onChange={(e) => updateNominee(idx, 'relationship', e.target.value)}
                                            className="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-slate-100"
                                        >
                                            <option value="Spouse">Spouse</option>
                                            <option value="Son">Son</option>
                                            <option value="Unmarried Daughter">Unmarried Daughter</option>
                                            <option value="Married Daughter">Married Daughter</option>
                                            <option value="Mother">Mother</option>
                                            <option value="Father">Father</option>
                                            <option value="Self">Self</option>
                                            <option value="Other">Other Legal Heir</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label className="block text-slate-300 font-medium mb-1">
                                            Share (%) <span className="text-rose-400">*</span>
                                        </label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            max="100.00"
                                            value={nominee.share_percentage}
                                            onChange={(e) => updateNominee(idx, 'share_percentage', parseFloat(e.target.value) || 0)}
                                            className="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-slate-100 font-mono font-bold text-right"
                                            required
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-slate-300 font-medium mb-1">Disbursement Bank Account</label>
                                        <input
                                            type="text"
                                            value={nominee.bank_account_no}
                                            onChange={(e) => updateNominee(idx, 'bank_account_no', e.target.value)}
                                            placeholder="Account number"
                                            className="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-slate-100 font-mono"
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-slate-300 font-medium mb-1">Bank IFSC Code</label>
                                        <input
                                            type="text"
                                            value={nominee.bank_ifsc}
                                            onChange={(e) => updateNominee(idx, 'bank_ifsc', e.target.value.toUpperCase())}
                                            placeholder="e.g. SBIN0000001"
                                            className="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-slate-100 font-mono uppercase"
                                        />
                                    </div>

                                    <div className="sm:col-span-2">
                                        <label className="block text-slate-300 font-medium mb-1">Bank Name & Branch</label>
                                        <input
                                            type="text"
                                            value={nominee.bank_name}
                                            onChange={(e) => updateNominee(idx, 'bank_name', e.target.value)}
                                            placeholder="e.g. State Bank of India, Agartala Branch"
                                            className="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-slate-100"
                                        />
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
