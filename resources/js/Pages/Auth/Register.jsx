import React, { useState } from 'react';
import { useForm, Head, Link } from '@inertiajs/react';
import {
    UserPlus,
    User,
    Mail,
    Lock,
    ShieldCheck,
    ArrowRight,
    Eye,
    EyeOff,
    ShieldAlert,
    Briefcase,
    ArrowLeft,
    KeyRound,
    Building2,
    Phone,
    Info,
    CheckCircle2
} from 'lucide-react';

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        username: '',
        email: '',
        designation: '',
        section: '',
        phone_number: '',
        role: 'deo',
        admin_token: '',
        password: '',
        password_confirmation: '',
    });

    const [showPassword, setShowPassword] = useState(false);
    const [hasToken, setHasToken] = useState(false);

    const submit = (e) => {
        e.preventDefault();
        post('/register');
    };

    return (
        <div className="min-h-screen bg-slate-950 flex flex-col justify-center items-center px-4 py-10 relative overflow-hidden font-sans select-none">
            <Head title="Institutional User Registration - GPF Final Payment Portal" />

            {/* Ambient background glows */}
            <div className="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[380px] bg-indigo-600/10 blur-[130px] rounded-full pointer-events-none"></div>
            <div className="absolute bottom-1/4 right-1/3 w-[450px] h-[250px] bg-emerald-600/5 blur-[120px] rounded-full pointer-events-none"></div>

            <div className="w-full max-w-xl relative z-10 space-y-6">
                {/* Government Header Branding */}
                <div className="text-center space-y-2">
                    <div className="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-700 via-indigo-600 to-indigo-500 text-white shadow-xl shadow-indigo-600/20 mb-2 border border-indigo-400/30">
                        <UserPlus className="w-7 h-7" />
                    </div>
                    <div className="space-y-0.5">
                        <p className="text-[11px] font-bold tracking-widest text-indigo-400 uppercase">
                            Government of India • CAG
                        </p>
                        <h1 className="text-xl font-extrabold tracking-tight text-white sm:text-2xl">
                            Officer Registration Portal
                        </h1>
                        <p className="text-xs text-slate-400 font-medium">
                            Office of the Accountant General (A & E), Tripura ::: Agartala
                        </p>
                    </div>
                </div>

                {/* Registration Card */}
                <div className="glass-panel p-6 sm:p-8 rounded-2xl shadow-2xl shadow-black/60 border border-slate-800/80 backdrop-blur-xl bg-slate-900/70">
                    <div className="flex items-center justify-between pb-4 mb-5 border-b border-slate-800/70 text-xs font-semibold text-slate-300">
                        <span className="flex items-center gap-2">
                            <ShieldCheck className="w-4 h-4 text-emerald-400" />
                            <span>Create Institutional Account</span>
                        </span>
                        <Link
                            href="/login"
                            className="text-indigo-400 hover:text-indigo-300 transition flex items-center gap-1 text-[11px]"
                        >
                            <ArrowLeft className="w-3.5 h-3.5" />
                            <span>Back to Sign In</span>
                        </Link>
                    </div>

                    {/* Notice on Approval vs Token */}
                    <div className="mb-5 p-3 rounded-xl bg-slate-950/80 border border-slate-800 flex items-start gap-2.5 text-xs text-slate-400">
                        <Info className="w-4 h-4 text-indigo-400 flex-shrink-0 mt-0.5" />
                        <div className="space-y-1 text-[11.5px] leading-relaxed">
                            <span className="font-semibold text-slate-200">Security Governance Policy:</span> Registrations without an Admin Token will be queued for manual verification & approval by the System Administrator (`dir`/`jdg`). If you received an <strong>Admin Security Token</strong>, enter it below for instant activation.
                        </div>
                    </div>

                    <form onSubmit={submit} className="space-y-4">
                        {/* Admin Security Token Input (Optional Toggle) */}
                        <div className="p-3.5 rounded-xl bg-indigo-950/30 border border-indigo-500/20 space-y-2">
                            <div className="flex items-center justify-between">
                                <label className="text-xs font-semibold text-indigo-300 flex items-center gap-1.5 uppercase tracking-wider">
                                    <KeyRound className="w-3.5 h-3.5 text-indigo-400" />
                                    <span>Admin Security Token (Optional)</span>
                                </label>
                                <span className="text-[10px] text-indigo-400/80 font-mono">Instant Auto-Approval</span>
                            </div>
                            <input
                                type="text"
                                value={data.admin_token}
                                onChange={(e) => setData('admin_token', e.target.value.toUpperCase())}
                                placeholder="ADM-REG-XXXXXXXX"
                                className="w-full px-3.5 py-2 bg-slate-950 border border-indigo-500/30 rounded-lg text-xs font-mono text-indigo-200 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition uppercase tracking-wider"
                            />
                            {errors.admin_token && <p className="text-xs text-rose-400">{errors.admin_token}</p>}
                        </div>

                        {/* Full Name & Username */}
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                    Full Name <span className="text-rose-400">*</span>
                                </label>
                                <div className="relative">
                                    <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                        <User className="w-4 h-4" />
                                    </div>
                                    <input
                                        type="text"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="e.g. Ramesh Chandra Deb"
                                        className="w-full pl-10 pr-4 py-2.5 bg-slate-950/70 border border-slate-700/80 rounded-xl text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                        required
                                    />
                                </div>
                                {errors.name && <p className="mt-1 text-xs text-rose-400">{errors.name}</p>}
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                    Username <span className="text-rose-400">*</span>
                                </label>
                                <div className="relative">
                                    <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                        <span className="text-xs font-mono text-slate-500">@</span>
                                    </div>
                                    <input
                                        type="text"
                                        value={data.username}
                                        onChange={(e) => setData('username', e.target.value)}
                                        placeholder="e.g. ramesh_deb"
                                        className="w-full pl-8 pr-4 py-2.5 bg-slate-950/70 border border-slate-700/80 rounded-xl text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition font-mono"
                                        required
                                    />
                                </div>
                                {errors.username && <p className="mt-1 text-xs text-rose-400">{errors.username}</p>}
                            </div>
                        </div>

                        {/* Email Address & Official Role */}
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                    Official Email <span className="text-rose-400">*</span>
                                </label>
                                <div className="relative">
                                    <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                        <Mail className="w-4 h-4" />
                                    </div>
                                    <input
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        placeholder="officer@tripura.gov.in"
                                        className="w-full pl-10 pr-4 py-2.5 bg-slate-950/70 border border-slate-700/80 rounded-xl text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition font-mono"
                                        required
                                    />
                                </div>
                                {errors.email && <p className="mt-1 text-xs text-rose-400">{errors.email}</p>}
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                    Requested Role <span className="text-rose-400">*</span>
                                </label>
                                <div className="relative">
                                    <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                        <Briefcase className="w-4 h-4" />
                                    </div>
                                    <select
                                        value={data.role}
                                        onChange={(e) => setData('role', e.target.value)}
                                        className="w-full pl-10 pr-4 py-2.5 bg-slate-950/70 border border-slate-700/80 rounded-xl text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                        required
                                    >
                                        <option value="deo">Dealing Assistant / DEO</option>
                                        <option value="checker">Assistant Accounts Officer (AAO)</option>
                                        <option value="approver">Senior Accounts Officer (Sr. AO)</option>
                                        <option value="dispatch">Outward Dispatch Officer</option>
                                    </select>
                                </div>
                                {errors.role && <p className="mt-1 text-xs text-rose-400">{errors.role}</p>}
                            </div>
                        </div>

                        {/* Designation & Section / Phone */}
                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                    Designation
                                </label>
                                <div className="relative">
                                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                                        <Briefcase className="w-3.5 h-3.5" />
                                    </div>
                                    <input
                                        type="text"
                                        value={data.designation}
                                        onChange={(e) => setData('designation', e.target.value)}
                                        placeholder="e.g. AAO / DEO"
                                        className="w-full pl-9 pr-3 py-2 bg-slate-950/70 border border-slate-700/80 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                    Section / Branch
                                </label>
                                <div className="relative">
                                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                                        <Building2 className="w-3.5 h-3.5" />
                                    </div>
                                    <input
                                        type="text"
                                        value={data.section}
                                        onChange={(e) => setData('section', e.target.value)}
                                        placeholder="e.g. Fund Section I"
                                        className="w-full pl-9 pr-3 py-2 bg-slate-950/70 border border-slate-700/80 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                    Mobile Number
                                </label>
                                <div className="relative">
                                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                                        <Phone className="w-3.5 h-3.5" />
                                    </div>
                                    <input
                                        type="text"
                                        value={data.phone_number}
                                        onChange={(e) => setData('phone_number', e.target.value)}
                                        placeholder="98XXXXXXXX"
                                        className="w-full pl-9 pr-3 py-2 bg-slate-950/70 border border-slate-700/80 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition font-mono"
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Password and Confirmation Grid */}
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                            <div>
                                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                    Password <span className="text-rose-400">*</span>
                                </label>
                                <div className="relative">
                                    <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                        <Lock className="w-4 h-4" />
                                    </div>
                                    <input
                                        type={showPassword ? 'text' : 'password'}
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        placeholder="Min 8 characters"
                                        className="w-full pl-10 pr-10 py-2.5 bg-slate-950/70 border border-slate-700/80 rounded-xl text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition font-mono"
                                        required
                                    />
                                    <button
                                        type="button"
                                        onClick={() => setShowPassword(!showPassword)}
                                        tabIndex={-1}
                                        className="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-slate-300 transition"
                                    >
                                        {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                                    </button>
                                </div>
                                {errors.password && <p className="mt-1 text-xs text-rose-400">{errors.password}</p>}
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                    Confirm Password <span className="text-rose-400">*</span>
                                </label>
                                <div className="relative">
                                    <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                        <Lock className="w-4 h-4" />
                                    </div>
                                    <input
                                        type={showPassword ? 'text' : 'password'}
                                        value={data.password_confirmation}
                                        onChange={(e) => setData('password_confirmation', e.target.value)}
                                        placeholder="Repeat password"
                                        className="w-full pl-10 pr-4 py-2.5 bg-slate-950/70 border border-slate-700/80 rounded-xl text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition font-mono"
                                        required
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Submit Button */}
                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white text-sm font-semibold shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 transition disabled:opacity-50 mt-4"
                        >
                            {processing ? (
                                <span className="flex items-center gap-2">
                                    <span className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                    <span>Submitting Registration...</span>
                                </span>
                            ) : (
                                <>
                                    <span>Submit Officer Registration</span>
                                    <ArrowRight className="w-4 h-4" />
                                </>
                            )}
                        </button>
                    </form>

                    {/* Footer note */}
                    <div className="mt-6 pt-4 border-t border-slate-800/80 text-center">
                        <p className="text-xs text-slate-400">
                            Already registered?{' '}
                            <Link href="/login" className="text-indigo-400 hover:text-indigo-300 font-semibold underline">
                                Sign In to Portal
                            </Link>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}
