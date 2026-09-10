import React, { useState, useMemo, useEffect } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
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
    Sparkles,
    Building2,
    Calendar,
    ShieldCheck,
    Coins,
    AlertCircle,
    FileSpreadsheet,
    Layers,
    ChevronDown,
    ExternalLink,
    Clock,
    AlertTriangle
} from 'lucide-react';

export default function CalculationSheet({
    case_data,
    calculation_run,
    available_base_years = [],
    opening_balance = 0,
    opening_fin_year = '2023-2024',
    monthly_ledger = []
}) {
    const [openingBal, setOpeningBal] = useState(opening_balance || 0);
    const [finYear, setFinYear] = useState(opening_fin_year || '2023-2024');
    const [rows, setRows] = useState(monthly_ledger || []);
    const [dlisAdmissible, setDlisAdmissible] = useState(
        calculation_run ? Boolean(calculation_run.dlis_admissible) : (case_data.case_type === 'FAM' || case_data.case_type === 'D')
    );

    // Sync state when props change
    useEffect(() => {
        setOpeningBal(opening_balance || 0);
        setFinYear(opening_fin_year || '2023-2024');
        setRows(monthly_ledger || []);
    }, [opening_balance, opening_fin_year, monthly_ledger]);

    const { data, setData, post, processing } = useForm({
        opening_balance: openingBal,
        opening_fin_year: finYear,
        monthly_entries: rows,
    });

    // Handle changing the Base Financial Year from VLC list
    const handleBaseFinYearChange = (selectedYear) => {
        setFinYear(selectedYear);
        const matched = (available_base_years || []).find((b) => b.financial_year === selectedYear);
        const newBal = matched && matched.closing_balance ? matched.closing_balance : 0;
        setOpeningBal(newBal);

        // Reload data from backend with chosen base financial year
        router.get(
            `/calculation/${case_data.id}`,
            { base_fin_year: selectedYear, opening_balance: newBal },
            { preserveState: false, preserveScroll: true }
        );
    };

    // Update individual cell in ledger
    const updateRow = (index, field, value) => {
        const updated = [...rows];
        updated[index] = { ...updated[index], [field]: value };

        // If toggling cut month to true, ensure subsequent months default to delayed
        if (field === 'is_cut_month' && value === true) {
            for (let i = 0; i < updated.length; i++) {
                if (i !== index && updated[i].is_cut_month) {
                    updated[i].is_cut_month = false;
                }
            }
        }

        setRows(updated);
        setData('monthly_entries', updated);
    };

    // Add a new monthly entry
    const addRow = (isDelayMonth = false) => {
        const lastRow = rows[rows.length - 1];
        let nextDate = new Date();
        if (lastRow && lastRow.pay_slip_date) {
            const d = new Date(lastRow.pay_slip_date);
            d.setMonth(d.getMonth() + 1);
            nextDate = d;
        }

        const m = nextDate.getMonth() + 1;
        const y = nextDate.getFullYear();
        const calculatedFY = (m >= 4) ? `${y}-${y + 1}` : `${y - 1}-${y}`;
        const accountingMonth = (m >= 4) ? m - 3 : m + 9;

        const newRow = {
            financial_year: calculatedFY,
            calendar_month: nextDate.toISOString().slice(0, 7),
            pay_slip_date: nextDate.toISOString().slice(0, 10),
            interest_date: nextDate.toISOString().slice(0, 10),
            accounting_month: accountingMonth,
            opening_balance: 0,
            deposit: isDelayMonth ? 0 : 5000.0,
            withdrawal: 0.0,
            rate_of_interest: 7.1000,
            interest_on_deposit: !isDelayMonth,
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

    // Real-time live client preview of progressive balance, actual interest, & delay interest
    const liveCalculations = useMemo(() => {
        let currentOpening = parseFloat(openingBal) || 0;
        let cumulativeActualInt = 0;
        let cumulativeDelayInt = 0;
        let totalSub = 0;
        let totalWith = 0;
        let totalExcess = 0;
        let runningProgressive = 0;
        let yearlyAccruedInt = 0;
        let yearlyDeposits = 0;
        let yearlyWithdrawals = 0;
        let currentFY = null;
        let cutMonthPassed = false;
        let delayOpeningBal = 0;

        // Find index of Cut Month if explicitly marked
        const cutMonthIdx = rows.findIndex((r) => r.is_cut_month);

        const calculatedRows = rows.map((r, idx) => {
            const dep = parseFloat(r.deposit) || 0;
            const withdr = parseFloat(r.withdrawal) || 0;
            const rate = parseFloat(r.rate_of_interest) || 7.1;
            const intOnDep = r.interest_on_deposit !== false;
            const isCutMonth = Boolean(r.is_cut_month);
            const finY = r.financial_year;

            // Is this row in the delayed period (after Cut Month)?
            const isDelayRow = (cutMonthIdx !== -1 && idx > cutMonthIdx);

            // Transition between Normal Financial Years: Capitalize previous year's interest
            if (!isDelayRow && currentFY !== null && finY !== currentFY) {
                currentOpening = currentOpening + yearlyDeposits - yearlyWithdrawals + Math.round(yearlyAccruedInt);
                yearlyAccruedInt = 0;
                yearlyDeposits = 0;
                yearlyWithdrawals = 0;
                runningProgressive = 0;
            }
            currentFY = finY;

            // When crossing into the Delay Period, capture pre-delay closing balance as Delay Opening Balance
            if (isDelayRow && !cutMonthPassed) {
                cutMonthPassed = true;
                delayOpeningBal = currentOpening + yearlyDeposits - yearlyWithdrawals + Math.round(yearlyAccruedInt);
                currentOpening = delayOpeningBal;
                runningProgressive = 0;
            }

            const effectiveDep = intOnDep ? dep : 0;
            if (intOnDep) {
                totalSub += dep;
                if (!isDelayRow) {
                    yearlyDeposits += dep;
                }
            } else {
                totalExcess += dep;
            }
            totalWith += withdr;
            if (!isDelayRow) {
                yearlyWithdrawals += withdr;
            }

            let mActualInterest = 0;
            let mDelayInterest = 0;

            if (isCutMonth) {
                // Cut Month: Suppressed for interest
                runningProgressive = 0;
                mActualInterest = 0;
                mDelayInterest = 0;
            } else if (isDelayRow) {
                // Delayed Interest Month
                if (runningProgressive === 0) {
                    runningProgressive = delayOpeningBal + effectiveDep - withdr;
                } else {
                    runningProgressive += (effectiveDep - withdr);
                }

                if (runningProgressive > 0) {
                    mDelayInterest = Math.round(((runningProgressive * rate) / 1200) * 100) / 100;
                }
                cumulativeDelayInt += mDelayInterest;
            } else {
                // Normal Active Period Month
                if (r.accounting_month === 1 || runningProgressive === 0) {
                    runningProgressive = currentOpening + effectiveDep - withdr;
                } else {
                    runningProgressive += (effectiveDep - withdr);
                }

                if (runningProgressive > 0) {
                    mActualInterest = Math.round(((runningProgressive * rate) / 1200) * 100) / 100;
                }

                yearlyAccruedInt += mActualInterest;
                cumulativeActualInt += mActualInterest;
            }

            return {
                ...r,
                is_delayed: isDelayRow,
                opening_balance: (r.accounting_month === 1 || (isDelayRow && idx === cutMonthIdx + 1)) ? Math.round(currentOpening * 100) / 100 : 0,
                progressive_balance: Math.round(runningProgressive * 100) / 100,
                actual_interest: mActualInterest,
                delay_interest: mDelayInterest,
            };
        });

        const totalInterestCombined = cumulativeActualInt + cumulativeDelayInt;
        const finalAmount = Math.round(
            (parseFloat(openingBal) || 0) + totalSub + totalExcess - totalWith + totalInterestCombined
        );

        // Partition rows into Normal FY Groups vs Delay Period Group
        const normalRows = calculatedRows.filter((r) => !r.is_delayed);
        const delayRows = calculatedRows.filter((r) => r.is_delayed);

        // Group normal rows by Financial Year
        const groupedNormal = {};
        normalRows.forEach((r, originalIdx) => {
            const fy = r.financial_year || '2024-2025';
            if (!groupedNormal[fy]) {
                groupedNormal[fy] = {
                    financial_year: fy,
                    rows: [],
                    opening_balance: r.opening_balance || currentOpening,
                    total_deposit: 0,
                    total_withdrawal: 0,
                    total_interest: 0,
                    closing_balance: 0,
                };
            }
            groupedNormal[fy].rows.push({ ...r, _originalIdx: originalIdx });
            groupedNormal[fy].total_deposit += (parseFloat(r.deposit) || 0);
            groupedNormal[fy].total_withdrawal += (parseFloat(r.withdrawal) || 0);
            groupedNormal[fy].total_interest += (parseFloat(r.actual_interest) || 0);
        });

        Object.values(groupedNormal).forEach((group) => {
            const firstRow = group.rows[0];
            const op = firstRow ? (parseFloat(firstRow.opening_balance) || 0) : 0;
            group.opening_balance = op;
            group.total_interest_rounded = Math.round(group.total_interest);
            group.closing_balance = Math.round(op + group.total_deposit - group.total_withdrawal + group.total_interest_rounded);
        });

        // Delay Summary Calculations
        const delaySummary = {
            opening_balance: delayOpeningBal,
            total_deposit: delayRows.reduce((acc, r) => acc + (parseFloat(r.deposit) || 0), 0),
            total_withdrawal: delayRows.reduce((acc, r) => acc + (parseFloat(r.withdrawal) || 0), 0),
            total_delay_interest: Math.round(cumulativeDelayInt),
            final_closing_balance: Math.round(delayOpeningBal + delayRows.reduce((acc, r) => acc + (parseFloat(r.deposit) || 0), 0) - delayRows.reduce((acc, r) => acc + (parseFloat(r.withdrawal) || 0), 0) + cumulativeDelayInt),
        };

        // DLIS calculation (36 months progressive average up to ₹60,000)
        let dlisAmount = 0;
        if (dlisAdmissible) {
            const validRows = calculatedRows.filter((r) => !r.is_cut_month).slice(-36);
            if (validRows.length > 0) {
                const sumProg = validRows.reduce((acc, r) => acc + (parseFloat(r.progressive_balance) || 0), 0);
                const sumInt = validRows.reduce((acc, r) => acc + (parseFloat(r.actual_interest) || 0) + (parseFloat(r.delay_interest) || 0), 0);
                const avg = Math.round((sumProg + sumInt) / Math.min(36, validRows.length));
                dlisAmount = Math.min(60000, avg);
            }
        }

        return {
            rows: calculatedRows,
            grouped_normal_fy: Object.values(groupedNormal),
            delay_rows: delayRows,
            delay_summary: delaySummary,
            has_delay: delayRows.length > 0,
            total_subscriptions: totalSub,
            total_excess: totalExcess,
            total_withdrawals: totalWith,
            actual_interest: cumulativeActualInt,
            delay_interest: cumulativeDelayInt,
            total_interest: totalInterestCombined,
            final_closing_balance: finalAmount,
            dlis_amount: dlisAmount,
            grand_payable: finalAmount + dlisAmount,
        };
    }, [openingBal, rows, dlisAdmissible]);

    const submit = (e) => {
        e.preventDefault();
        setData('opening_balance', openingBal);
        setData('opening_fin_year', finYear);
        setData('monthly_entries', rows);
        post(`/calculation/${case_data.id}`);
    };

    return (
        <AuthenticatedLayout title={`Calculation - ${case_data.registration_no}`}>
            <Head title={`Calculation Sheet - ${case_data.registration_no}`} />

            <div className="space-y-6 max-w-7xl mx-auto pb-16">
                {/* Top Action Header */}
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <Link
                            href={`/inward/${case_data.id}`}
                            className="p-2.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:border-slate-700 transition"
                        >
                            <ArrowLeft className="w-4 h-4" />
                        </Link>
                        <div>
                            <div className="flex items-center gap-2.5">
                                <h2 className="text-xl font-bold tracking-tight text-white flex items-center gap-2">
                                    <Calculator className="w-5 h-5 text-indigo-400" />
                                    <span>GPF Final Payment Calculation Ledger</span>
                                </h2>
                                <span className="text-xs px-2.5 py-0.5 rounded-full bg-indigo-500/10 text-indigo-300 border border-indigo-500/20 font-mono font-semibold">
                                    {case_data.registration_no}
                                </span>
                            </div>
                            <p className="text-xs text-slate-400 mt-0.5">
                                Subscriber: <strong className="text-slate-200">{case_data.subscriber_name_cache}</strong> | GPF A/C:{' '}
                                <strong className="text-indigo-300 font-mono">{case_data.formatted_gpf_account}</strong> | Case Type:{' '}
                                <span className="text-slate-300 font-medium">{case_data.case_type}</span>
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        <button
                            type="button"
                            onClick={submit}
                            disabled={processing}
                            className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 text-white text-xs font-semibold shadow-lg shadow-indigo-600/25 transition disabled:opacity-50"
                        >
                            <Save className="w-4 h-4" />
                            <span>{processing ? 'Calculating & Saving...' : 'Save & Sanction Settlement'}</span>
                        </button>
                    </div>
                </div>

                {/* Calculation Summary Bar with Delayed Interest Metric */}
                <div className="grid grid-cols-2 sm:grid-cols-6 gap-3">
                    <div className="glass-panel p-3.5 rounded-xl border border-slate-800">
                        <div className="text-[11px] text-slate-400">Base Opening Balance</div>
                        <div className="text-sm font-bold text-slate-100 mt-1 font-mono">
                            ₹ {Number(openingBal).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                        </div>
                        <div className="text-[10px] text-indigo-400 mt-0.5">{finYear} (VLC)</div>
                    </div>
                    <div className="glass-panel p-3.5 rounded-xl border border-slate-800">
                        <div className="text-[11px] text-slate-400">Total Subscriptions</div>
                        <div className="text-sm font-bold text-emerald-400 mt-1 font-mono">
                            + ₹ {Number(liveCalculations.total_subscriptions).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                        </div>
                        <div className="text-[10px] text-slate-400 mt-0.5">Regular Deposits</div>
                    </div>
                    <div className="glass-panel p-3.5 rounded-xl border border-slate-800">
                        <div className="text-[11px] text-slate-400">Total Withdrawals</div>
                        <div className="text-sm font-bold text-rose-400 mt-1 font-mono">
                            - ₹ {Number(liveCalculations.total_withdrawals).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                        </div>
                        <div className="text-[10px] text-slate-400 mt-0.5">Debit Adjustments</div>
                    </div>
                    <div className="glass-panel p-3.5 rounded-xl border border-slate-800">
                        <div className="text-[11px] text-slate-400">Accrued Interest</div>
                        <div className="text-sm font-bold text-indigo-400 mt-1 font-mono">
                            + ₹ {Number(liveCalculations.actual_interest).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                        </div>
                        <div className="text-[10px] text-slate-400 mt-0.5">Normal Period</div>
                    </div>
                    <div className="glass-panel p-3.5 rounded-xl border border-amber-500/30 bg-amber-950/10">
                        <div className="text-[11px] text-amber-300 font-semibold flex items-center gap-1">
                            <Clock className="w-3 h-3" />
                            <span>Delayed Interest</span>
                        </div>
                        <div className="text-sm font-bold text-amber-400 mt-1 font-mono">
                            + ₹ {Number(liveCalculations.delay_interest).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                        </div>
                        <div className="text-[10px] text-amber-400/80 mt-0.5">
                            {liveCalculations.has_delay ? `${liveCalculations.delay_rows.length} Delay Month(s)` : 'No Delay'}
                        </div>
                    </div>
                    <div className="glass-panel p-3.5 rounded-xl bg-gradient-to-r from-emerald-950/40 to-indigo-950/40 border border-emerald-500/30 col-span-2 sm:col-span-1">
                        <div className="text-[11px] text-emerald-300 font-semibold">Net Final Settlement</div>
                        <div className="text-base font-extrabold text-emerald-400 mt-1 font-mono">
                            ₹ {Number(liveCalculations.grand_payable).toLocaleString('en-IN')}
                        </div>
                        <div className="text-[10px] text-emerald-400/80 mt-0.5">Statutory Amount</div>
                    </div>
                </div>

                {/* Base Financial Year Selection from VLC */}
                <div className="glass-panel p-4 rounded-2xl border border-slate-800">
                    <div className="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                        <div className="flex items-center gap-3">
                            <div className="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                                <Building2 className="w-5 h-5" />
                            </div>
                            <div>
                                <h3 className="text-sm font-bold text-white flex items-center gap-2">
                                    <span>Base Financial Year & VLC Closing Balance</span>
                                    <span className="text-[10px] px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono">
                                        Direct from VLCS.GP_YEARLY_BALANCES
                                    </span>
                                </h3>
                                <p className="text-xs text-slate-400">
                                    Select the base financial year to automatically fetch the audited closing balance from VLC.
                                </p>
                            </div>
                        </div>

                        <div className="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                            <div className="w-full sm:w-72">
                                <label className="block text-[11px] text-slate-400 mb-1 font-medium">
                                    Base Financial Year (VLC)
                                </label>
                                <select
                                    value={finYear}
                                    onChange={(e) => handleBaseFinYearChange(e.target.value)}
                                    className="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-slate-100 font-mono font-semibold focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                >
                                    {available_base_years && available_base_years.length > 0 ? (
                                        available_base_years.map((b) => (
                                            <option key={b.financial_year} value={b.financial_year}>
                                                {b.financial_year} (Closing Bal: ₹ {Number(b.closing_balance).toLocaleString('en-IN', { minimumFractionDigits: 2 })})
                                            </option>
                                        ))
                                    ) : (
                                        <option value={finYear}>{finYear}</option>
                                    )}
                                </select>
                            </div>

                            <div className="w-full sm:w-48">
                                <label className="block text-[11px] text-slate-400 mb-1 font-medium">
                                    Opening Balance (₹)
                                </label>
                                <input
                                    type="number"
                                    step="0.01"
                                    value={openingBal}
                                    onChange={(e) => setOpeningBal(parseFloat(e.target.value) || 0)}
                                    className="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-slate-100 font-mono font-bold focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                />
                            </div>

                            <div className="flex items-end self-end pt-5 gap-2">
                                <button
                                    type="button"
                                    onClick={() => addRow(false)}
                                    className="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-indigo-400 text-xs font-semibold flex items-center gap-1.5 transition border border-slate-700"
                                    title="Add Normal Ledger Month"
                                >
                                    <Plus className="w-3.5 h-3.5" />
                                    <span>Add Month</span>
                                </button>
                                <button
                                    type="button"
                                    onClick={() => addRow(true)}
                                    className="px-3.5 py-2 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 text-xs font-semibold flex items-center gap-1.5 transition border border-amber-500/30"
                                    title="Add Post-Retirement Delay Month"
                                >
                                    <Clock className="w-3.5 h-3.5" />
                                    <span>Add Delay Month</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Section 1: Normal Period Calculation Tables (Grouped by Financial Year) */}
                <div className="space-y-8">
                    {liveCalculations.grouped_normal_fy.map((group) => (
                        <div key={group.financial_year} className="space-y-4">
                            {/* FY Section Header */}
                            <div className="flex items-center justify-between px-1">
                                <div className="flex items-center gap-2.5">
                                    <div className="w-2.5 h-2.5 rounded-full bg-indigo-500"></div>
                                    <h3 className="text-sm font-bold text-white uppercase tracking-wider font-mono">
                                        Calculation for Financial Year: {group.financial_year}
                                    </h3>
                                    <span className="text-[11px] px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 font-mono">
                                        Opening: ₹ {Number(group.opening_balance).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                    </span>
                                </div>
                                <div className="text-xs text-slate-400 font-mono">
                                    Year-End Closing: <strong className="text-indigo-400">₹ {Number(group.closing_balance).toLocaleString('en-IN')}</strong>
                                </div>
                            </div>

                            {/* FY Monthly Table */}
                            <div className="glass-panel rounded-2xl overflow-hidden shadow-2xl border border-slate-800">
                                <div className="overflow-x-auto">
                                    <table className="w-full text-left text-xs">
                                        <thead className="bg-slate-900/90 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-[11px]">
                                            <tr>
                                                <th className="py-3 px-3">Pay Slip Month</th>
                                                <th className="py-3 px-3 text-right">Opening Bal (₹)</th>
                                                <th className="py-3 px-3 text-right">Deposit (₹)</th>
                                                <th className="py-3 px-3 text-right">Withdrawal (₹)</th>
                                                <th className="py-3 px-3 text-right">Rate %</th>
                                                <th className="py-3 px-3 text-center" title="Interest on Deposit Toggle">
                                                    Int On Dep
                                                </th>
                                                <th className="py-3 px-3 text-right">Progressive (₹)</th>
                                                <th className="py-3 px-3 text-right">Monthly Int (₹)</th>
                                                <th className="py-3 px-3 text-center" title="Cut Month Toggle">
                                                    Cut Month
                                                </th>
                                                <th className="py-3 px-2 text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-slate-800/60 font-mono">
                                            {group.rows.map((row) => {
                                                const originalIndex = row._originalIdx;
                                                const isCut = row.is_cut_month;
                                                const noInt = row.interest_on_deposit === false;

                                                let rowBg = 'hover:bg-slate-900/40';
                                                if (isCut) {
                                                    rowBg = 'bg-rose-950/40 hover:bg-rose-950/50 text-rose-200 border-l-4 border-rose-500';
                                                } else if (noInt) {
                                                    rowBg = 'bg-cyan-950/20 hover:bg-cyan-950/30 text-cyan-200';
                                                }

                                                return (
                                                    <tr key={originalIndex} className={`${rowBg} transition`}>
                                                        <td className="py-2 px-3">
                                                            <div className="flex items-center gap-1.5">
                                                                <input
                                                                    type="date"
                                                                    value={row.pay_slip_date}
                                                                    onChange={(e) => updateRow(originalIndex, 'pay_slip_date', e.target.value)}
                                                                    className="px-2 py-1 bg-slate-950/70 border border-slate-800 rounded text-[11px] text-slate-200 font-mono"
                                                                />
                                                                <span className="text-[10px] text-slate-500 font-sans">
                                                                    M{row.accounting_month}
                                                                </span>
                                                                {isCut && (
                                                                    <span className="px-1.5 py-0.5 rounded bg-rose-500/20 text-rose-300 text-[9px] font-sans font-bold uppercase">
                                                                        Cut Month
                                                                    </span>
                                                                )}
                                                            </div>
                                                        </td>
                                                        <td className="py-2 px-3 text-right text-slate-400">
                                                            {row.opening_balance > 0 ? (
                                                                <span className="text-slate-200 font-semibold">
                                                                    ₹ {Number(row.opening_balance).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                                                </span>
                                                            ) : (
                                                                <span className="text-slate-600">-</span>
                                                            )}
                                                        </td>
                                                        <td className="py-2 px-3 text-right">
                                                            <input
                                                                type="number"
                                                                step="0.01"
                                                                value={row.deposit}
                                                                onChange={(e) => updateRow(originalIndex, 'deposit', parseFloat(e.target.value) || 0)}
                                                                className="w-24 px-2 py-1 bg-slate-950/70 border border-slate-800 rounded text-right text-emerald-400 font-semibold text-xs"
                                                            />
                                                        </td>
                                                        <td className="py-2 px-3 text-right">
                                                            <input
                                                                type="number"
                                                                step="0.01"
                                                                value={row.withdrawal}
                                                                onChange={(e) => updateRow(originalIndex, 'withdrawal', parseFloat(e.target.value) || 0)}
                                                                className="w-24 px-2 py-1 bg-slate-950/70 border border-slate-800 rounded text-right text-rose-400 font-semibold text-xs"
                                                            />
                                                        </td>
                                                        <td className="py-2 px-3 text-right">
                                                            <input
                                                                type="number"
                                                                step="0.01"
                                                                value={row.rate_of_interest}
                                                                onChange={(e) => updateRow(originalIndex, 'rate_of_interest', parseFloat(e.target.value) || 0)}
                                                                className="w-16 px-1.5 py-1 bg-slate-950/70 border border-slate-800 rounded text-right text-slate-300 text-xs"
                                                            />
                                                        </td>
                                                        <td className="py-2 px-3 text-center">
                                                            <input
                                                                type="checkbox"
                                                                checked={row.interest_on_deposit !== false}
                                                                onChange={(e) => updateRow(originalIndex, 'interest_on_deposit', e.target.checked)}
                                                                className="rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-0 cursor-pointer"
                                                                title="Toggle interest eligibility"
                                                            />
                                                        </td>
                                                        <td className="py-2 px-3 text-right text-slate-200 font-semibold">
                                                            ₹ {Number(row.progressive_balance).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                                        </td>
                                                        <td className="py-2 px-3 text-right text-indigo-400 font-semibold">
                                                            ₹ {Number(row.actual_interest).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                                        </td>
                                                        <td className="py-2 px-3 text-center">
                                                            <input
                                                                type="checkbox"
                                                                checked={Boolean(row.is_cut_month)}
                                                                onChange={(e) => updateRow(originalIndex, 'is_cut_month', e.target.checked)}
                                                                className="rounded bg-slate-900 border-slate-700 text-rose-600 focus:ring-0 cursor-pointer"
                                                                title="Toggle Cut Month (End of Normal Interest Period)"
                                                            />
                                                        </td>
                                                        <td className="py-2 px-2 text-center">
                                                            <button
                                                                type="button"
                                                                onClick={() => removeRow(originalIndex)}
                                                                className="p-1 rounded text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 transition"
                                                                title="Delete Month Entry"
                                                            >
                                                                <Trash2 className="w-3.5 h-3.5" />
                                                            </button>
                                                        </td>
                                                    </tr>
                                                );
                                            })}
                                        </tbody>
                                        {/* FY Totals Footer */}
                                        <tfoot className="bg-slate-900/90 font-mono font-bold border-t border-slate-700 text-slate-200">
                                            <tr>
                                                <td className="py-3 px-3 uppercase text-[11px] text-slate-400">
                                                    FY {group.financial_year} TOTALS
                                                </td>
                                                <td className="py-3 px-3 text-right text-indigo-300">
                                                    ₹ {Number(group.opening_balance).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                                </td>
                                                <td className="py-3 px-3 text-right text-emerald-400">
                                                    ₹ {Number(group.total_deposit).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                                </td>
                                                <td className="py-3 px-3 text-right text-rose-400">
                                                    ₹ {Number(group.total_withdrawal).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                                </td>
                                                <td colSpan={3} className="py-3 px-3 text-right text-slate-400 text-[11px]">
                                                    Interest Sum:
                                                </td>
                                                <td className="py-3 px-3 text-right text-indigo-400">
                                                    ₹ {Number(group.total_interest).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                                </td>
                                                <td colSpan={2} className="py-3 px-3 text-center text-emerald-400 text-xs">
                                                    Closing: ₹ {Number(group.closing_balance).toLocaleString('en-IN')}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>

                {/* Section 2: Dedicated DELAY INTEREST CALCULATION Table (Post Cut-Off Period) */}
                {liveCalculations.has_delay && (
                    <div className="space-y-4 pt-4">
                        <div className="flex items-center justify-between px-1">
                            <div className="flex items-center gap-2.5">
                                <div className="p-1.5 rounded-lg bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                    <AlertTriangle className="w-4 h-4" />
                                </div>
                                <h3 className="text-sm font-bold text-amber-300 uppercase tracking-wider font-mono">
                                    DELAY INTEREST CALCULATION (Months After Cut-Off)
                                </h3>
                                <span className="text-[11px] px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-300 border border-amber-500/20 font-mono">
                                    Delay Opening Bal: ₹ {Number(liveCalculations.delay_summary.opening_balance).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                </span>
                            </div>
                            <div className="text-xs text-amber-400 font-mono">
                                Total Delay Interest: <strong>+ ₹ {Number(liveCalculations.delay_interest).toLocaleString('en-IN', { minimumFractionDigits: 2 })}</strong>
                            </div>
                        </div>

                        <div className="glass-panel rounded-2xl overflow-hidden shadow-2xl border border-amber-500/30 bg-amber-950/10">
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-xs">
                                    <thead className="bg-amber-950/40 border-b border-amber-500/20 text-amber-200/90 font-semibold uppercase tracking-wider text-[11px]">
                                        <tr>
                                            <th className="py-3 px-3">Pay Slip Month (Delay)</th>
                                            <th className="py-3 px-3 text-right">Opening Bal (₹)</th>
                                            <th className="py-3 px-3 text-right">Excess Deposit (₹)</th>
                                            <th className="py-3 px-3 text-right">Withdrawal (₹)</th>
                                            <th className="py-3 px-3 text-right">Rate %</th>
                                            <th className="py-3 px-3 text-right">Progressive (₹)</th>
                                            <th className="py-3 px-3 text-right text-amber-300">Delay Int (₹)</th>
                                            <th className="py-3 px-2 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-amber-500/10 font-mono">
                                        {liveCalculations.delay_rows.map((row) => {
                                            const originalIndex = rows.findIndex((orig) => orig.pay_slip_date === row.pay_slip_date);

                                            return (
                                                <tr key={originalIndex} className="hover:bg-amber-950/20 transition">
                                                    <td className="py-2 px-3">
                                                        <div className="flex items-center gap-1.5">
                                                            <input
                                                                type="date"
                                                                value={row.pay_slip_date}
                                                                onChange={(e) => updateRow(originalIndex, 'pay_slip_date', e.target.value)}
                                                                className="px-2 py-1 bg-slate-950/70 border border-amber-500/30 rounded text-[11px] text-amber-200 font-mono"
                                                            />
                                                            <span className="text-[10px] text-amber-400/70 font-sans">
                                                                M{row.accounting_month}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td className="py-2 px-3 text-right text-amber-200/80">
                                                        {row.opening_balance > 0 ? (
                                                            <span className="font-semibold text-amber-300">
                                                                ₹ {Number(row.opening_balance).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                                            </span>
                                                        ) : (
                                                            <span className="text-slate-600">-</span>
                                                        )}
                                                    </td>
                                                    <td className="py-2 px-3 text-right">
                                                        <input
                                                            type="number"
                                                            step="0.01"
                                                            value={row.deposit}
                                                            onChange={(e) => updateRow(originalIndex, 'deposit', parseFloat(e.target.value) || 0)}
                                                            className="w-24 px-2 py-1 bg-slate-950/70 border border-amber-500/30 rounded text-right text-cyan-400 font-semibold text-xs"
                                                        />
                                                    </td>
                                                    <td className="py-2 px-3 text-right">
                                                        <input
                                                            type="number"
                                                            step="0.01"
                                                            value={row.withdrawal}
                                                            onChange={(e) => updateRow(originalIndex, 'withdrawal', parseFloat(e.target.value) || 0)}
                                                            className="w-24 px-2 py-1 bg-slate-950/70 border border-amber-500/30 rounded text-right text-rose-400 font-semibold text-xs"
                                                        />
                                                    </td>
                                                    <td className="py-2 px-3 text-right">
                                                        <input
                                                            type="number"
                                                            step="0.01"
                                                            value={row.rate_of_interest}
                                                            onChange={(e) => updateRow(originalIndex, 'rate_of_interest', parseFloat(e.target.value) || 0)}
                                                            className="w-16 px-1.5 py-1 bg-slate-950/70 border border-amber-500/30 rounded text-right text-slate-300 text-xs"
                                                        />
                                                    </td>
                                                    <td className="py-2 px-3 text-right text-amber-200 font-semibold">
                                                        ₹ {Number(row.progressive_balance).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                                    </td>
                                                    <td className="py-2 px-3 text-right text-amber-400 font-bold">
                                                        ₹ {Number(row.delay_interest).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                                    </td>
                                                    <td className="py-2 px-2 text-center">
                                                        <button
                                                            type="button"
                                                            onClick={() => removeRow(originalIndex)}
                                                            className="p-1 rounded text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 transition"
                                                            title="Delete Delay Month"
                                                        >
                                                            <Trash2 className="w-3.5 h-3.5" />
                                                        </button>
                                                    </td>
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                    {/* Delay Totals Footer */}
                                    <tfoot className="bg-amber-950/40 font-mono font-bold border-t border-amber-500/30 text-amber-200">
                                        <tr>
                                            <td className="py-3 px-3 uppercase text-[11px]">
                                                DELAY PERIOD TOTALS
                                            </td>
                                            <td className="py-3 px-3 text-right text-amber-300">
                                                ₹ {Number(liveCalculations.delay_summary.opening_balance).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                            </td>
                                            <td className="py-3 px-3 text-right text-cyan-400">
                                                ₹ {Number(liveCalculations.delay_summary.total_deposit).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                            </td>
                                            <td className="py-3 px-3 text-right text-rose-400">
                                                ₹ {Number(liveCalculations.delay_summary.total_withdrawal).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                            </td>
                                            <td className="py-3 px-3 text-right text-slate-400 text-[11px]">
                                                Delay Interest:
                                            </td>
                                            <td className="py-3 px-3 text-right text-amber-400">
                                                -
                                            </td>
                                            <td className="py-3 px-3 text-right text-amber-400 text-sm font-extrabold">
                                                ₹ {Number(liveCalculations.delay_summary.total_delay_interest).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                            </td>
                                            <td className="py-3 px-2 text-center text-emerald-400">
                                                ✓
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        {/* Delay Period Particulars Card (Matching Legacy `calculation_sheet.php`) */}
                        <div className="glass-panel p-4 rounded-2xl border border-amber-500/20 bg-amber-950/10">
                            <div className="text-xs font-bold text-amber-300 uppercase tracking-wider mb-2 font-mono flex items-center gap-1.5">
                                <Clock className="w-3.5 h-3.5" />
                                <span>Delay Period Audit Breakdown</span>
                            </div>
                            <div className="grid grid-cols-1 md:grid-cols-5 gap-3 text-xs font-mono">
                                <div className="p-2.5 rounded-xl bg-slate-900/60 border border-slate-800">
                                    <div className="text-[10px] text-slate-400">Opening (Pre-Delay)</div>
                                    <div className="text-sm font-bold text-white mt-0.5">
                                        ₹ {Number(liveCalculations.delay_summary.opening_balance).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                    </div>
                                </div>
                                <div className="p-2.5 rounded-xl bg-slate-900/60 border border-slate-800">
                                    <div className="text-[10px] text-slate-400">Excess Deposits</div>
                                    <div className="text-sm font-bold text-cyan-400 mt-0.5">
                                        ₹ {Number(liveCalculations.delay_summary.total_deposit).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                    </div>
                                </div>
                                <div className="p-2.5 rounded-xl bg-slate-900/60 border border-slate-800">
                                    <div className="text-[10px] text-slate-400">Delay Withdrawals</div>
                                    <div className="text-sm font-bold text-rose-400 mt-0.5">
                                        ₹ {Number(liveCalculations.delay_summary.total_withdrawal).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                    </div>
                                </div>
                                <div className="p-2.5 rounded-xl bg-slate-900/60 border border-slate-800">
                                    <div className="text-[10px] text-slate-400">Delayed Interest</div>
                                    <div className="text-sm font-bold text-amber-400 mt-0.5">
                                        ₹ {Number(liveCalculations.delay_summary.total_delay_interest).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                    </div>
                                </div>
                                <div className="p-2.5 rounded-xl bg-emerald-950/30 border border-emerald-500/30">
                                    <div className="text-[10px] text-emerald-300">Final Delay Balance</div>
                                    <div className="text-sm font-bold text-emerald-400 mt-0.5">
                                        ₹ {Number(liveCalculations.delay_summary.final_closing_balance).toLocaleString('en-IN')}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* DLIS & Sanction Approval Section */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* DLIS Sanction Scheme */}
                    <div className="glass-panel p-5 rounded-2xl border border-slate-800 space-y-4">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-2.5">
                                <ShieldCheck className="w-5 h-5 text-amber-400" />
                                <h4 className="text-sm font-bold text-white">Deposit-Linked Insurance Scheme (DLIS)</h4>
                            </div>
                            <label className="flex items-center gap-2 cursor-pointer text-xs text-slate-300 font-medium">
                                <input
                                    type="checkbox"
                                    checked={dlisAdmissible}
                                    onChange={(e) => setDlisAdmissible(e.target.checked)}
                                    className="rounded bg-slate-900 border-slate-700 text-amber-500 focus:ring-0"
                                />
                                <span>DLIS Admissible</span>
                            </label>
                        </div>
                        <p className="text-xs text-slate-400 leading-relaxed">
                            Under Tripura GPF Rules, for Death in Service cases, insurance coverage equal to the average balance
                            over the preceding 36 months is admissible up to a statutory ceiling of{' '}
                            <strong className="text-amber-300">₹ 60,000.00</strong>.
                        </p>
                        {dlisAdmissible && (
                            <div className="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-between">
                                <div>
                                    <div className="text-xs font-semibold text-amber-300">Sanctioned DLIS Amount</div>
                                    <div className="text-[11px] text-amber-400/80">36-month progressive balance average</div>
                                </div>
                                <div className="text-lg font-bold font-mono text-amber-400">
                                    ₹ {Number(liveCalculations.dlis_amount).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Final Settlement Breakdown Card */}
                    <div className="glass-panel p-5 rounded-2xl border border-slate-800 space-y-3 bg-gradient-to-br from-slate-900/90 to-indigo-950/20">
                        <div className="flex items-center gap-2 text-indigo-400 text-xs font-bold uppercase tracking-wider">
                            <Coins className="w-4 h-4" />
                            <span>Sanction Settlement Final Audit</span>
                        </div>
                        <div className="space-y-2 text-xs font-mono">
                            <div className="flex justify-between py-1 border-b border-slate-800 text-slate-300">
                                <span>Base Opening Principal Balance:</span>
                                <span className="text-white">
                                    ₹ {Number(openingBal).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                </span>
                            </div>
                            <div className="flex justify-between py-1 border-b border-slate-800 text-slate-300">
                                <span>Total Subscriptions & Credits:</span>
                                <span className="text-emerald-400">
                                    + ₹ {Number(liveCalculations.total_subscriptions).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                </span>
                            </div>
                            {liveCalculations.total_excess > 0 && (
                                <div className="flex justify-between py-1 border-b border-slate-800 text-slate-300">
                                <span>Total Excess Deposits (No Int):</span>
                                    <span className="text-cyan-400">
                                        + ₹ {Number(liveCalculations.total_excess).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                    </span>
                                </div>
                            )}
                            <div className="flex justify-between py-1 border-b border-slate-800 text-slate-300">
                                <span>Normal Compounded Interest:</span>
                                <span className="text-indigo-400">
                                    + ₹ {Number(liveCalculations.actual_interest).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                </span>
                            </div>
                            {liveCalculations.delay_interest > 0 && (
                                <div className="flex justify-between py-1 border-b border-slate-800 text-slate-300">
                                    <span>Delayed Period Interest:</span>
                                    <span className="text-amber-400 font-bold">
                                        + ₹ {Number(liveCalculations.delay_interest).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                    </span>
                                </div>
                            )}
                            <div className="flex justify-between py-1 border-b border-slate-800 text-slate-300">
                                <span>Less Withdrawals & Debits:</span>
                                <span className="text-rose-400">
                                    - ₹ {Number(liveCalculations.total_withdrawals).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                </span>
                            </div>
                            {dlisAdmissible && (
                                <div className="flex justify-between py-1 border-b border-slate-800 text-slate-300">
                                    <span>DLIS Insurance Coverage:</span>
                                    <span className="text-amber-400">
                                        + ₹ {Number(liveCalculations.dlis_amount).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                                    </span>
                                </div>
                            )}
                            <div className="flex justify-between pt-2 text-sm font-bold text-emerald-400">
                                <span className="font-sans">Grand Total Certified Payable:</span>
                                <span>₹ {Number(liveCalculations.grand_payable).toLocaleString('en-IN')}</span>
                            </div>
                        </div>

                        <div className="pt-2">
                            <button
                                type="button"
                                onClick={submit}
                                disabled={processing}
                                className="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2"
                            >
                                <Save className="w-4 h-4" />
                                <span>{processing ? 'Processing...' : 'Save & Sanction Settlement'}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
