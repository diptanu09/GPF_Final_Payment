import React, { useState, useMemo } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    Calculator,
    Save,
    RotateCcw,
    Plus,
    Trash2,
    CheckCircle2,
    ArrowLeft,
    TrendingUp,
    Lock,
    Sparkles
} from 'lucide-react';

export default function CalculationSheet({ case_data, calculation_run, opening_balance, opening_fin_year, monthly_ledger }) {
    const [openingBal, setOpeningBal] = useState(opening_balance || 0);
    const [finYear, setFinYear] = useState(opening_fin_year || '2023-2024');
    const [rows, setRows] = useState(monthly_ledger || []);

    const { data, setData, post, processing } = useForm({
        opening_balance: openingBal,
        opening_fin_year: finYear,
        monthly_entries: rows,
    });

    // Update row cell
    const updateRow = (index, field, value) => {
        const updated = [...rows];
        updated[index] = { ...updated[index], [field]: value };
        setRows(updated);
        setData('monthly_entries', updated);
    };

    // Add a new monthly entry
    const addRow = () => {
        const lastRow = rows[rows.length - 1];
        let nextDate = new Date();
        if (lastRow) {
            const d = new Date(lastRow.pay_slip_date);
            d.setMonth(d.getMonth() + 1);
            nextDate = d;
        }

        const newRow = {
            financial_year: finYear,
            calendar_month: nextDate.toISOString().slice(0, 7),
            pay_slip_date: nextDate.toISOString().slice(0, 10),
            accounting_month: (rows.length % 12) + 1,
            opening_balance: 0,
            deposit: 0,
            withdrawal: 0,
            rate_of_interest: 7.1000,
            interest_on_deposit: true,
            progressive_balance: 0,
            actual_interest: 0,
            delay_interest: 0,
            is_cut_month: false,
            is_adjustment: false,
        };

        const updated = [...rows, newRow];
        setRows(updated);
        setData('monthly_entries', updated);
    };

    // Remove row
    const removeRow = (index) => {
        const updated = rows.filter((_, i) => i !== index);
        setRows(updated);
        setData('monthly_entries', updated);
    };

    // Real-time live client preview of progressive balance & interest
    const liveCalculations = useMemo(() => {
        let currentOpening = parseFloat(openingBal) || 0;
        let cumulativeInterest = 0;
        let totalSub = 0;
        let totalWith = 0;
        let totalExcess = 0;

        const calculatedRows = rows.map((r, idx) => {
            const dep = parseFloat(r.deposit) || 0;
            const withdr = parseFloat(r.withdrawal) || 0;
            const rate = parseFloat(r.rate_of_interest) || 7.1;
            const intOnDep = r.interest_on_deposit;

            if (intOnDep) {
                totalSub += dep;
            } else {
                totalExcess += dep;
            }
            totalWith += withdr;

            // April month (Month 1): Capitalize previous year's interest
            if (r.accounting_month === 1 && idx > 0) {
                currentOpening += cumulativeInterest;
                cumulativeInterest = 0;
            }

            const effectiveDep = intOnDep ? dep : 0;
            const prog = currentOpening + effectiveDep - withdr;

            let mInterest = 0;
            if (!r.is_cut_month && prog > 0) {
                mInterest = Math.round(((prog * rate) / 1200) * 100) / 100;
            }

            cumulativeInterest += mInterest;

            return {
                ...r,
                progressive_balance: Math.round(prog * 100) / 100,
                actual_interest: mInterest,
            };
        });

        const finalAmount = Math.round(
            (parseFloat(openingBal) || 0) + totalSub + totalExcess - totalWith + cumulativeInterest
        );

        return {
            rows: calculatedRows,
            total_subscriptions: totalSub,
            total_withdrawals: totalWith,
            total_interest: cumulativeInterest,
            final_closing_balance: finalAmount,
        };
    }, [openingBal, rows]);

    const submit = (e) => {
        e.preventDefault();
        setData('opening_balance', openingBal);
        setData('opening_fin_year', finYear);
        setData('monthly_entries', rows);
        post(`/calculation/${case_data.id}`);
    };

    return (
        <AuthenticatedLayout title={`Calculation - ${case_data.registration_no}`}>
            <Head title={`Calculation Sheet - Case ${case_data.registration_no}`} />

            <div className="space-y-6 max-w-7xl mx-auto">
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
                                    <Calculator className="w-5 h-5 text-indigo-400" />
                                    <span>Interactive GPF Calculation Ledger</span>
                                </h2>
                                <span className="text-xs px-2.5 py-0.5 rounded-full bg-indigo-500/10 text-indigo-300 border border-indigo-500/20 font-mono">
                                    {case_data.registration_no}
                                </span>
                            </div>
                            <p className="text-xs text-slate-400">
                                Subscriber: <strong className="text-slate-200">{case_data.subscriber_name_cache}</strong> ({case_data.formatted_gpf_account})
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        <button
                            type="button"
                            onClick={submit}
                            disabled={processing}
                            className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/25 transition disabled:opacity-50"
                        >
                            <Save className="w-4 h-4" />
                            <span>{processing ? 'Processing...' : 'Save & Execute Calculation'}</span>
                        </button>
                    </div>
                </div>

                {/* Calculation Summary Bar */}
                <div className="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    <div className="glass-panel p-3.5 rounded-xl">
                        <div className="text-[11px] text-slate-400">Opening Balance</div>
                        <div className="text-base font-bold text-slate-100 mt-0.5 font-mono">
                            ₹ {Number(openingBal).toLocaleString('en-IN')}
                        </div>
                    </div>
                    <div className="glass-panel p-3.5 rounded-xl">
                        <div className="text-[11px] text-slate-400">Total Deposits</div>
                        <div className="text-base font-bold text-emerald-400 mt-0.5 font-mono">
                            + ₹ {Number(liveCalculations.total_subscriptions).toLocaleString('en-IN')}
                        </div>
                    </div>
                    <div className="glass-panel p-3.5 rounded-xl">
                        <div className="text-[11px] text-slate-400">Total Withdrawals</div>
                        <div className="text-base font-bold text-rose-400 mt-0.5 font-mono">
                            - ₹ {Number(liveCalculations.total_withdrawals).toLocaleString('en-IN')}
                        </div>
                    </div>
                    <div className="glass-panel p-3.5 rounded-xl">
                        <div className="text-[11px] text-slate-400">Accrued Interest</div>
                        <div className="text-base font-bold text-indigo-400 mt-0.5 font-mono">
                            + ₹ {Number(liveCalculations.total_interest).toLocaleString('en-IN')}
                        </div>
                    </div>
                    <div className="glass-panel p-3.5 rounded-xl bg-gradient-to-r from-emerald-950/40 to-indigo-950/40 border border-emerald-500/30 col-span-2 sm:col-span-1">
                        <div className="text-[11px] text-emerald-300 font-semibold">Net Certified Balance</div>
                        <div className="text-lg font-extrabold text-emerald-400 mt-0.5 font-mono">
                            ₹ {Number(liveCalculations.final_closing_balance).toLocaleString('en-IN')}
                        </div>
                    </div>
                </div>

                {/* Opening Balance Settings */}
                <div className="glass-panel p-4 rounded-2xl flex flex-col sm:flex-row items-center gap-4 text-xs">
                    <div className="flex-1 w-full sm:w-auto">
                        <label className="block text-slate-300 font-medium mb-1">Base Financial Year</label>
                        <input
                            type="text"
                            value={finYear}
                            onChange={(e) => setFinYear(e.target.value)}
                            placeholder="e.g. 2023-2024"
                            className="w-full px-3 py-1.5 bg-slate-900 border border-slate-700 rounded-lg text-slate-100 font-mono"
                        />
                    </div>
                    <div className="flex-1 w-full sm:w-auto">
                        <label className="block text-slate-300 font-medium mb-1">Opening Principal Balance (₹)</label>
                        <input
                            type="number"
                            step="0.01"
                            value={openingBal}
                            onChange={(e) => setOpeningBal(e.target.value)}
                            className="w-full px-3 py-1.5 bg-slate-900 border border-slate-700 rounded-lg text-slate-100 font-mono font-bold"
                        />
                    </div>
                    <div className="flex items-end self-end pt-5">
                        <button
                            type="button"
                            onClick={addRow}
                            className="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-indigo-400 font-semibold flex items-center gap-1.5 transition"
                        >
                            <Plus className="w-3.5 h-3.5" />
                            <span>Add Ledger Month</span>
                        </button>
                    </div>
                </div>

                {/* Ledger Spreadsheet Grid */}
                <div className="glass-panel rounded-2xl overflow-hidden shadow-2xl">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs">
                            <thead className="bg-slate-900/80 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider">
                                <tr>
                                    <th className="py-3 px-3">Month / Date</th>
                                    <th className="py-3 px-3 text-right">Deposit (₹)</th>
                                    <th className="py-3 px-3 text-right">Withdrawal (₹)</th>
                                    <th className="py-3 px-3 text-right">Rate %</th>
                                    <th className="py-3 px-3 text-center">Int Eligible</th>
                                    <th className="py-3 px-3 text-right">Progressive (₹)</th>
                                    <th className="py-3 px-3 text-right">Monthly Int (₹)</th>
                                    <th className="py-3 px-3 text-center">Cut Month</th>
                                    <th className="py-3 px-2 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/60 font-mono">
                                {liveCalculations.rows.map((row, idx) => (
                                    <tr key={idx} className="hover:bg-slate-900/40 transition">
                                        <td className="py-2.5 px-3">
                                            <input
                                                type="date"
                                                value={row.pay_slip_date}
                                                onChange={(e) => updateRow(idx, 'pay_slip_date', e.target.value)}
                                                className="px-2 py-1 bg-slate-950/60 border border-slate-800 rounded text-[11px] text-slate-200"
                                            />
                                        </td>
                                        <td className="py-2.5 px-3 text-right">
                                            <input
                                                type="number"
                                                step="0.01"
                                                value={row.deposit}
                                                onChange={(e) => updateRow(idx, 'deposit', parseFloat(e.target.value) || 0)}
                                                className="w-24 px-2 py-1 bg-slate-950/60 border border-slate-800 rounded text-right text-emerald-400 font-semibold"
                                            />
                                        </td>
                                        <td className="py-2.5 px-3 text-right">
                                            <input
                                                type="number"
                                                step="0.01"
                                                value={row.withdrawal}
                                                onChange={(e) => updateRow(idx, 'withdrawal', parseFloat(e.target.value) || 0)}
                                                className="w-24 px-2 py-1 bg-slate-950/60 border border-slate-800 rounded text-right text-rose-400 font-semibold"
                                            />
                                        </td>
                                        <td className="py-2.5 px-3 text-right">
                                            <input
                                                type="number"
                                                step="0.01"
                                                value={row.rate_of_interest}
                                                onChange={(e) => updateRow(idx, 'rate_of_interest', parseFloat(e.target.value) || 0)}
                                                className="w-16 px-1.5 py-1 bg-slate-950/60 border border-slate-800 rounded text-right text-slate-300"
                                            />
                                        </td>
                                        <td className="py-2.5 px-3 text-center">
                                            <input
                                                type="checkbox"
                                                checked={row.interest_on_deposit}
                                                onChange={(e) => updateRow(idx, 'interest_on_deposit', e.target.checked)}
                                                className="rounded bg-slate-900 border-slate-700 text-indigo-600"
                                            />
                                        </td>
                                        <td className="py-2.5 px-3 text-right text-slate-200 font-semibold">
                                            ₹ {Number(row.progressive_balance).toLocaleString('en-IN')}
                                        </td>
                                        <td className="py-2.5 px-3 text-right text-indigo-400 font-semibold">
                                            ₹ {Number(row.actual_interest).toLocaleString('en-IN')}
                                        </td>
                                        <td className="py-2.5 px-3 text-center">
                                            <input
                                                type="checkbox"
                                                checked={row.is_cut_month}
                                                onChange={(e) => updateRow(idx, 'is_cut_month', e.target.checked)}
                                                className="rounded bg-slate-900 border-slate-700 text-rose-600"
                                            />
                                        </td>
                                        <td className="py-2.5 px-2 text-center">
                                            <button
                                                type="button"
                                                onClick={() => removeRow(idx)}
                                                className="p-1 rounded text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 transition"
                                                title="Delete Row"
                                            >
                                                <Trash2 className="w-3.5 h-3.5" />
                                            </button>
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
