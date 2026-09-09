import React, { useState } from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    FilePlus,
    Search,
    User,
    Building2,
    Calendar,
    Phone,
    MapPin,
    ArrowLeft,
    CheckCircle2,
    AlertTriangle,
    Sparkles,
    ShieldAlert,
    Coins
} from 'lucide-react';

export default function Create({ series_list, ddo_list, treasuries, case_types, pension_types = [] }) {
    const { data, setData, post, processing, errors } = useForm({
        series_code: '',
        series_name: '',
        account_no: '',
        subscriber_name: '',
        name_title: 'Shri',
        designation_title: 'Dr/Mr/Mrs',
        designation: '',
        case_type: 'F',
        pension_type_id: '1',
        section: 'Fund Section I',
        ddo_code: '',
        treasury_code: '',
        event_date: '',
        diary_number: '',
        diary_date: new Date().toISOString().split('T')[0],
        last_fund_deduction: '',
        debit_during_year: 0,
        personal_address: '',
        mobile_no: '',
        employee_code: '',
        beneficiary_code: '',
        spouse_name: '',
        spouse_relation: 'Spouse',
    });

    const handleCaseTypeChange = (newCaseType) => {
        setData((prev) => {
            const updates = { ...prev, case_type: newCaseType };
            if (newCaseType === 'FAM' || newCaseType === 'D') {
                updates.pension_type_id = '2'; // Family Pension (FAM)
                if (prev.name_title === 'Shri' || prev.name_title === 'Smt') {
                    updates.name_title = 'Late';
                }
            } else if (prev.pension_type_id === '2' && newCaseType === 'F') {
                updates.pension_type_id = '1'; // Superannuation
                if (prev.name_title === 'Late') {
                    updates.name_title = 'Shri';
                }
            }
            return updates;
        });
    };

    const [isLookingUp, setIsLookingUp] = useState(false);
    const [lookupFound, setLookupFound] = useState(false);
    const [closureWarning, setClosureWarning] = useState(null);
    const [balanceInfo, setBalanceInfo] = useState(null);

    const handleLookup = async () => {
        if (!data.series_code || !data.account_no) {
            alert('Please select Series and enter GPF Account Number first.');
            return;
        }

        setIsLookingUp(true);
        setClosureWarning(null);
        setBalanceInfo(null);
        try {
            const res = await fetch(`/inward/lookup?series_code=${encodeURIComponent(data.series_code)}&account_no=${encodeURIComponent(data.account_no)}`);
            if (res.ok) {
                const sub = await res.json();
                if (sub.found_in_oracle) {
                    setData((prev) => ({
                        ...prev,
                        subscriber_name: sub.subscriber_name || prev.subscriber_name,
                        name_title: sub.name_title || prev.name_title,
                        designation: sub.designation || prev.designation,
                        personal_address: sub.personal_address || prev.personal_address,
                        employee_code: sub.employee_code || prev.employee_code,
                        beneficiary_code: sub.beneficiary_code || prev.beneficiary_code,
                        mobile_no: sub.mobile_no || prev.mobile_no,
                        ddo_code: sub.ddo_code || prev.ddo_code,
                        treasury_code: sub.treasury_code || prev.treasury_code,
                        spouse_name: sub.spouse_name || prev.spouse_name,
                        spouse_relation: sub.spouse_relation || prev.spouse_relation,
                        last_fund_deduction: sub.last_fund_deduction || prev.last_fund_deduction,
                    }));
                    setLookupFound(true);

                    if (sub.warning || sub.is_closed) {
                        setClosureWarning(sub.warning || `Account was closed on ${sub.closure_date}`);
                    }

                    if (sub.opening_balance > 0 || sub.closing_balance > 0) {
                        setBalanceInfo({
                            opBalance: sub.opening_balance,
                            clBalance: sub.closing_balance,
                            finYear: sub.closing_fin_year,
                        });
                    }
                } else {
                    alert('No matching subscriber found in Oracle 11g VLC master. You can enter details manually.');
                }
            }
        } catch (err) {
            console.error('Lookup failed', err);
        } finally {
            setIsLookingUp(false);
        }
    };

    const handleDdoChange = (ddoCode) => {
        const selectedDdo = ddo_list.find((d) => String(d.id) === String(ddoCode));
        setData((prev) => ({
            ...prev,
            ddo_code: ddoCode,
            treasury_code: selectedDdo?.treasury_code || prev.treasury_code,
        }));
    };

    const submit = (e) => {
        e.preventDefault();
        post('/inward');
    };

    return (
        <AuthenticatedLayout title="Register Inward Case">
            <Head title="Register Inward Case - GPF Final Payment Portal" />

            <div className="max-w-4xl mx-auto space-y-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <Link
                            href="/inward"
                            className="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition"
                        >
                            <ArrowLeft className="w-4 h-4" />
                        </Link>
                        <div>
                            <h2 className="text-xl font-bold tracking-tight text-white">
                                Register New GPF Inward Docket
                            </h2>
                            <p className="text-xs text-slate-400">
                                Enter subscriber details or fetch directly from Oracle 11g VLC (VLCS.GP_ACCOUNTS / VLCS.STATE_DDO).
                            </p>
                        </div>
                    </div>
                </div>

                {closureWarning && (
                    <div className="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-start gap-3 text-amber-300 animate-fadeIn">
                        <AlertTriangle className="w-5 h-5 flex-shrink-0 mt-0.5 text-amber-400" />
                        <div>
                            <p className="text-sm font-semibold">Account Closure Alert</p>
                            <p className="text-xs text-amber-200/90">{closureWarning}</p>
                            <p className="text-[11px] text-amber-400/70 mt-1">If this is a residual, revised or corrigendum case, please set the appropriate Case Settlement Type below.</p>
                        </div>
                    </div>
                )}

                {balanceInfo && (
                    <div className="p-4 rounded-2xl bg-indigo-500/10 border border-indigo-500/30 flex items-center justify-between text-indigo-300">
                        <div className="flex items-center gap-2">
                            <Coins className="w-4 h-4 text-indigo-400" />
                            <span className="text-xs font-semibold">Legacy VLC Master Ledger Record:</span>
                        </div>
                        <div className="flex items-center gap-4 text-xs">
                            <span>Opening Balance: <strong className="text-white font-mono">₹{balanceInfo.opBalance.toLocaleString('en-IN')}</strong></span>
                            <span>Closing Balance: <strong className="text-emerald-400 font-mono">₹{balanceInfo.clBalance.toLocaleString('en-IN')}</strong></span>
                        </div>
                    </div>
                )}

                <form onSubmit={submit} className="space-y-6">
                    {/* Step 1: GPF Series & Account Lookup */}
                    <div className="glass-panel p-6 rounded-2xl space-y-4">
                        <div className="flex items-center justify-between border-b border-slate-800 pb-3">
                            <h3 className="text-sm font-bold text-indigo-400 flex items-center gap-2">
                                <Sparkles className="w-4 h-4" />
                                <span>1. Legacy Oracle 11g Account Lookup (VLCS.GP_ACCOUNTS)</span>
                            </h3>
                            {lookupFound && (
                                <span className="text-[11px] font-semibold text-emerald-400 flex items-center gap-1">
                                    <CheckCircle2 className="w-3.5 h-3.5" />
                                    <span>Profile Matched</span>
                                </span>
                            )}
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label className="block text-xs font-medium text-slate-300 mb-1.5">
                                    GPF Series <span className="text-rose-400">*</span>
                                </label>
                                <select
                                    value={data.series_code}
                                    onChange={(e) => {
                                        const selected = series_list.find((s) => String(s.id) === String(e.target.value));
                                        setData((prev) => ({
                                            ...prev,
                                            series_code: e.target.value,
                                            series_name: selected?.code || selected?.name || '',
                                        }));
                                    }}
                                    className="w-full px-3 py-2 bg-slate-900/80 border border-slate-700/80 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="">Select GPF Series</option>
                                    {series_list.map((s) => (
                                        <option key={s.id} value={s.id}>
                                            {s.name}
                                        </option>
                                    ))}
                                </select>
                                {errors.series_code && <p className="text-rose-400 text-[10px] mt-1">{errors.series_code}</p>}
                            </div>

                            <div>
                                <label className="block text-xs font-medium text-slate-300 mb-1.5">
                                    Account Number <span className="text-rose-400">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={data.account_no}
                                    onChange={(e) => setData('account_no', e.target.value)}
                                    placeholder="e.g. 5937"
                                    className="w-full px-3 py-2 bg-slate-900/80 border border-slate-700/80 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono font-semibold"
                                    required
                                />
                                {errors.account_no && <p className="text-rose-400 text-[10px] mt-1">{errors.account_no}</p>}
                            </div>

                            <div className="flex items-end">
                                <button
                                    type="button"
                                    onClick={handleLookup}
                                    disabled={isLookingUp}
                                    className="w-full py-2 px-3 rounded-xl bg-indigo-600/80 hover:bg-indigo-600 text-white text-xs font-semibold flex items-center justify-center gap-1.5 transition disabled:opacity-50"
                                >
                                    <Search className="w-3.5 h-3.5" />
                                    <span>{isLookingUp ? 'Searching Oracle...' : 'Fetch Subscriber'}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {/* Step 2: Subscriber Demographics */}
                    <div className="glass-panel p-6 rounded-2xl space-y-4">
                        <div className="border-b border-slate-800 pb-3">
                            <h3 className="text-sm font-bold text-slate-200 flex items-center gap-2">
                                <User className="w-4 h-4 text-indigo-400" />
                                <span>2. Subscriber Profile & Demographics</span>
                            </h3>
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label className="block text-xs font-medium text-slate-300 mb-1.5">Salutation</label>
                                <select
                                    value={data.name_title}
                                    onChange={(e) => setData('name_title', e.target.value)}
                                    className="w-full px-3 py-2 bg-slate-900/80 border border-slate-700/80 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                    <option value="Shri">Shri</option>
                                    <option value="Smt">Smt</option>
                                    <option value="Late">Late (Deceased)</option>
                                    <option value="Dr">Dr</option>
                                </select>
                            </div>

                            <div className="sm:col-span-2">
                                <label className="block text-xs font-medium text-slate-300 mb-1.5">
                                    Full Name <span className="text-rose-400">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={data.subscriber_name}
                                    onChange={(e) => setData('subscriber_name', e.target.value)}
                                    className="w-full px-3 py-2 bg-slate-900/80 border border-slate-700/80 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-semibold"
                                    required
                                />
                                {errors.subscriber_name && <p className="text-rose-400 text-[10px] mt-1">{errors.subscriber_name}</p>}
                            </div>

                            <div>
                                <label className="block text-xs font-medium text-slate-300 mb-1.5">
                                    Designation <span className="text-rose-400">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={data.designation}
                                    onChange={(e) => setData('designation', e.target.value)}
                                    placeholder="e.g. Senior Teacher"
                                    className="w-full px-3 py-2 bg-slate-900/80 border border-slate-700/80 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    required
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-medium text-slate-300 mb-1.5">
                                    Case Settlement Type <span className="text-rose-400">*</span>
                                </label>
                                <select
                                    value={data.case_type}
                                    onChange={(e) => handleCaseTypeChange(e.target.value)}
                                    className="w-full px-3 py-2 bg-slate-900/80 border border-slate-700/80 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium"
                                >
                                    {case_types.map((t) => (
                                        <option key={t.id} value={t.id}>{t.name}</option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="block text-xs font-medium text-slate-300 mb-1.5">
                                    Event / Retirement Date <span className="text-rose-400">*</span>
                                </label>
                                <input
                                    type="date"
                                    value={data.event_date}
                                    onChange={(e) => setData('event_date', e.target.value)}
                                    className="w-full px-3 py-2 bg-slate-900/80 border border-slate-700/80 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    required
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-medium text-slate-300 mb-1.5">
                                    Pension / Closure Category <span className="text-rose-400">*</span>
                                </label>
                                <select
                                    value={data.pension_type_id}
                                    onChange={(e) => setData('pension_type_id', e.target.value)}
                                    className="w-full px-3 py-2 bg-slate-900/80 border border-slate-700/80 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium"
                                    required
                                >
                                    {pension_types && pension_types.length > 0 ? (
                                        pension_types.map((p) => (
                                            <option key={p.id} value={p.id}>
                                                {p.id} - {p.name}
                                            </option>
                                        ))
                                    ) : (
                                        <>
                                            <option value="1">1 - Superannuation (SUP)</option>
                                            <option value="2">2 - Family Pension (FAM) [DLIS Admissible]</option>
                                            <option value="3">3 - Voluntary Retirement (VOL)</option>
                                            <option value="4">4 - Dismissal (DISM)</option>
                                            <option value="5">5 - Suspension (SUSP)</option>
                                            <option value="6">6 - Balance Transfer (BLTR)</option>
                                            <option value="7">7 - Missing Employee (MISN) [DLIS Admissible]</option>
                                        </>
                                    )}
                                </select>
                            </div>

                            {(data.case_type === 'FAM' || data.pension_type_id === '2') && (
                                <div className="sm:col-span-3 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs flex items-start gap-2.5">
                                    <ShieldAlert className="w-4 h-4 text-rose-400 flex-shrink-0 mt-0.5" />
                                    <div>
                                        <p className="font-semibold text-rose-200">Family Pension (FAM) Settlement Active</p>
                                        <p className="text-[11px] text-rose-300/80 mt-0.5">
                                            Admissible for Deposit-Linked Insurance Scheme (DLIS up to ₹60,000) and 6-month statutory interest window post-demise. Final authority and payment will be addressed to the designated nominee/spouse.
                                        </p>
                                    </div>
                                </div>
                            )}

                            <div>
                                <label className="block text-xs font-medium text-slate-300 mb-1.5">HRMS Employee Code</label>
                                <input
                                    type="text"
                                    value={data.employee_code}
                                    onChange={(e) => setData('employee_code', e.target.value)}
                                    placeholder="e.g. 106356"
                                    className="w-full px-3 py-2 bg-slate-900/80 border border-slate-700/80 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-medium text-slate-300 mb-1.5">Beneficiary Code</label>
                                <input
                                    type="text"
                                    value={data.beneficiary_code}
                                    onChange={(e) => setData('beneficiary_code', e.target.value)}
                                    placeholder="e.g. 216984"
                                    className="w-full px-3 py-2 bg-slate-900/80 border border-slate-700/80 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-medium text-slate-300 mb-1.5">Mobile No (SMS Alerts)</label>
                                <input
                                    type="text"
                                    value={data.mobile_no}
                                    onChange={(e) => setData('mobile_no', e.target.value)}
                                    placeholder="e.g. 9862523603"
                                    className="w-full px-3 py-2 bg-slate-900/80 border border-slate-700/80 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-medium text-slate-300 mb-1.5">Spouse / Father Name</label>
                                <input
                                    type="text"
                                    value={data.spouse_name}
                                    onChange={(e) => setData('spouse_name', e.target.value)}
                                    placeholder="Spouse or Legal Next of Kin"
                                    className="w-full px-3 py-2 bg-slate-900/80 border border-slate-700/80 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-medium text-slate-300 mb-1.5">Relation</label>
                                <select
                                    value={data.spouse_relation}
                                    onChange={(e) => setData('spouse_relation', e.target.value)}
                                    className="w-full px-3 py-2 bg-slate-900/80 border border-slate-700/80 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                    <option value="Spouse">Spouse (Husband / Wife)</option>
                                    <option value="Father">Father</option>
                                    <option value="Mother">Mother</option>
                                    <option value="Son">Son</option>
                                    <option value="Daughter">Daughter</option>
                                    <option value="Other">Other Legal Heir</option>
                                </select>
                            </div>

                            <div className="sm:col-span-3">
                                <label className="block text-xs font-medium text-slate-300 mb-1.5">
                                    Personal Residential Address <span className="text-rose-400">*</span>
                                </label>
                                <textarea
                                    value={data.personal_address}
                                    onChange={(e) => setData('personal_address', e.target.value)}
                                    rows={2}
                                    placeholder="Full mailing address for authority dispatch"
                                    className="w-full px-3 py-2 bg-slate-900/80 border border-slate-700/80 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    required
                                />
                            </div>
                        </div>
                    </div>

                    {/* Step 3: DDO & Treasury Linkage */}
                    <div className="glass-panel p-6 rounded-2xl space-y-4">
                        <div className="border-b border-slate-800 pb-3">
                            <h3 className="text-sm font-bold text-slate-200 flex items-center gap-2">
                                <Building2 className="w-4 h-4 text-indigo-400" />
                                <span>3. Drawing & Disbursing Officer (DDO) & Treasury (VLCS.STATE_DDO)</span>
                            </h3>
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label className="block text-xs font-medium text-slate-300 mb-1.5">
                                    DDO Code & Designation <span className="text-rose-400">*</span>
                                </label>
                                <select
                                    value={data.ddo_code}
                                    onChange={(e) => handleDdoChange(e.target.value)}
                                    className="w-full px-3 py-2 bg-slate-900/80 border border-slate-700/80 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="">Select DDO</option>
                                    {ddo_list.map((d) => (
                                        <option key={d.id} value={d.id}>{d.id} - {d.name}</option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="block text-xs font-medium text-slate-300 mb-1.5">
                                    Treasury / Sub-Treasury <span className="text-rose-400">*</span>
                                </label>
                                <select
                                    value={data.treasury_code}
                                    onChange={(e) => setData('treasury_code', e.target.value)}
                                    className="w-full px-3 py-2 bg-slate-900/80 border border-slate-700/80 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="">Select Treasury</option>
                                    {treasuries.map((t) => (
                                        <option key={t.id} value={t.id}>{t.id} - {t.name}</option>
                                    ))}
                                </select>
                            </div>
                        </div>
                    </div>

                    <div className="flex items-center justify-end gap-3">
                        <Link
                            href="/inward"
                            className="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300 transition"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            disabled={processing}
                            className="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white text-xs font-semibold shadow-lg shadow-indigo-600/25 transition disabled:opacity-50"
                        >
                            {processing ? 'Registering...' : 'Register Inward Case'}
                        </button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
