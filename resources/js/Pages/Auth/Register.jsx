import React, { useState, useMemo } from 'react';
import { useForm, Head, Link } from '@inertiajs/react';
import {
    User,
    Mail,
    Lock,
    ShieldCheck,
    ArrowRight,
    Eye,
    EyeOff,
    Briefcase,
    ArrowLeft,
    KeyRound,
    Building2,
    Phone,
    Info,
    Sparkles,
    FileCheck,
    Send
} from 'lucide-react';
import AuthBackground from '@/Components/Auth/AuthBackground';
import InteractiveTiltCard from '@/Components/Auth/InteractiveTiltCard';
import SecurityShieldAvatar from '@/Components/Auth/SecurityShieldAvatar';
import PasswordStrengthMeter from '@/Components/Auth/PasswordStrengthMeter';

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
    const [focusedField, setFocusedField] = useState(null);

    // Available official roles
    const officialRoles = [
        {
            id: 'deo',
            title: 'Dealing Assistant',
            short: 'DEO / Maker',
            desc: 'Docket entry & calculation runs',
            icon: User,
            color: 'indigo',
        },
        {
            id: 'checker',
            title: 'AAO Officer',
            short: 'Checker',
            desc: 'Audit check & voucher ledger verify',
            icon: FileCheck,
            color: 'cyan',
        },
        {
            id: 'approver',
            title: 'Senior AO',
            short: 'Approver',
            desc: 'Final sanction & DSC signature',
            icon: ShieldCheck,
            color: 'emerald',
        },
        {
            id: 'dispatch',
            title: 'Dispatch Officer',
            short: 'Outward',
            desc: 'HRMS sync & speed post dispatch',
            icon: Send,
            color: 'purple',
        },
    ];

    const hasAnyError = Object.keys(errors).length > 0;
    const isTokenActive = Boolean(data.admin_token && data.admin_token.trim().startsWith('ADM-'));

    // Compute dynamic security avatar status
    const avatarState = useMemo(() => {
        if (processing) return 'processing';
        if (hasAnyError) return 'error';
        if (isTokenActive) return 'token';
        if (showPassword) return 'peek';
        if (focusedField === 'password' || focusedField === 'password_confirmation') return 'password';
        if (['username', 'name', 'email'].includes(focusedField)) return 'username';
        return 'idle';
    }, [processing, hasAnyError, isTokenActive, showPassword, focusedField]);

    const submit = (e) => {
        e.preventDefault();
        post('/register');
    };

    return (
        <AuthBackground className="flex flex-col justify-center items-center">
            <Head title="Institutional User Registration - GPF Final Payment Portal" />

            <div className="w-full max-w-xl mx-auto space-y-5">
                {/* Government Header Branding with Interactive Reactive Avatar */}
                <SecurityShieldAvatar
                    state={avatarState}
                    title="Government of India • CAG"
                    heading="Officer Registration Portal"
                    subtitle="Office of the Accountant General (A & E), Tripura ::: Agartala"
                />

                {/* Interactive 3D Tilt Card */}
                <InteractiveTiltCard className="p-6 sm:p-8">
                    {/* Top Navigation Bar */}
                    <div className="flex items-center justify-between pb-3.5 mb-4 border-b border-slate-800/80 text-xs font-semibold text-slate-300">
                        <div className="flex items-center gap-2">
                            <ShieldCheck className="w-4 h-4 text-emerald-400" />
                            <span>Create Institutional Account</span>
                        </div>
                        <Link
                            href="/login"
                            className="text-indigo-400 hover:text-indigo-300 transition flex items-center gap-1 text-[11.5px] hover:underline"
                        >
                            <ArrowLeft className="w-3.5 h-3.5" />
                            <span>Back to Sign In</span>
                        </Link>
                    </div>

                    {/* Notice on Governance Policy */}
                    <div className="mb-4 p-3 rounded-xl bg-slate-950/80 border border-slate-800/80 flex items-start gap-2.5 text-xs text-slate-400">
                        <Info className="w-4 h-4 text-indigo-400 flex-shrink-0 mt-0.5" />
                        <div className="space-y-0.5 text-[11px] leading-relaxed">
                            <span className="font-semibold text-slate-200">Governance Policy:</span> Registrations without an Admin Security Token are routed for verification and activation by the Directorate Administration. Entering a valid <strong>Admin Security Token</strong> grants immediate clearance.
                        </div>
                    </div>

                    <form onSubmit={submit} className="space-y-4">
                        {/* Admin Security Token Input with Shimmer Animation */}
                        <div
                            className={`p-3.5 rounded-xl transition-all duration-300 ${
                                isTokenActive
                                    ? 'bg-emerald-950/40 border border-emerald-500/50 shadow-[0_0_20px_rgba(16,185,129,0.15)]'
                                    : 'bg-indigo-950/30 border border-indigo-500/20'
                            }`}
                        >
                            <div className="flex items-center justify-between mb-1.5">
                                <label className="text-xs font-semibold text-indigo-300 flex items-center gap-1.5 uppercase tracking-wider">
                                    <KeyRound className="w-3.5 h-3.5 text-indigo-400" />
                                    <span>Admin Security Token (Optional)</span>
                                </label>
                                {isTokenActive ? (
                                    <span className="text-[10px] text-emerald-400 font-mono flex items-center gap-1 font-semibold animate-pulse">
                                        <Sparkles className="w-3 h-3" />
                                        Fast-Track Mode Active
                                    </span>
                                ) : (
                                    <span className="text-[10px] text-indigo-400/80 font-mono">
                                        Instant Auto-Approval
                                    </span>
                                )}
                            </div>
                            <input
                                type="text"
                                value={data.admin_token}
                                onChange={(e) => setData('admin_token', e.target.value.toUpperCase())}
                                placeholder="Enter admin security token (if issued)"
                                className={`w-full px-3.5 py-2 bg-slate-950 border rounded-lg text-xs font-mono text-indigo-200 placeholder-slate-600 focus:outline-none transition uppercase tracking-wider ${
                                    isTokenActive
                                        ? 'border-emerald-500/50 ring-2 ring-emerald-500/20'
                                        : 'border-indigo-500/30 focus:ring-2 focus:ring-indigo-500'
                                }`}
                            />
                            {errors.admin_token && (
                                <p className="text-xs text-rose-400 mt-1 animate-micro-shake">{errors.admin_token}</p>
                            )}
                        </div>

                        {/* Full Name & Username */}
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                                    Full Name <span className="text-rose-400">*</span>
                                </label>
                                <div className="relative">
                                    <div
                                        className={`absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-all duration-200 ${
                                            focusedField === 'name' ? 'text-indigo-400 scale-110' : 'text-slate-500'
                                        }`}
                                    >
                                        <User className="w-4 h-4" />
                                    </div>
                                    <input
                                        type="text"
                                        value={data.name}
                                        onFocus={() => setFocusedField('name')}
                                        onBlur={() => setFocusedField(null)}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="Enter official full name"
                                        className={`w-full pl-10 pr-3.5 py-2 bg-slate-950/80 border rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none transition-all duration-200 ${
                                            focusedField === 'name'
                                                ? 'border-indigo-500/70 ring-2 ring-indigo-500/20 shadow-[0_0_12px_rgba(99,102,241,0.15)]'
                                                : 'border-slate-800 hover:border-slate-700'
                                        }`}
                                        required
                                    />
                                </div>
                                {errors.name && <p className="mt-1 text-xs text-rose-400 animate-micro-shake">{errors.name}</p>}
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                                    Official Username <span className="text-rose-400">*</span>
                                </label>
                                <div className="relative">
                                    <div
                                        className={`absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none font-mono text-xs transition-all duration-200 ${
                                            focusedField === 'username' ? 'text-cyan-400 scale-110' : 'text-slate-500'
                                        }`}
                                    >
                                        @
                                    </div>
                                    <input
                                        type="text"
                                        value={data.username}
                                        onFocus={() => setFocusedField('username')}
                                        onBlur={() => setFocusedField(null)}
                                        onChange={(e) => setData('username', e.target.value)}
                                        placeholder="Enter desired official username"
                                        className={`w-full pl-8 pr-3.5 py-2 bg-slate-950/80 border rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none transition-all duration-200 font-mono ${
                                            focusedField === 'username'
                                                ? 'border-cyan-500/70 ring-2 ring-cyan-500/20 shadow-[0_0_12px_rgba(6,182,212,0.15)]'
                                                : 'border-slate-800 hover:border-slate-700'
                                        }`}
                                        required
                                    />
                                </div>
                                {errors.username && <p className="mt-1 text-xs text-rose-400 animate-micro-shake">{errors.username}</p>}
                            </div>
                        </div>

                        {/* Email Address & Designation */}
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                                    Official Email <span className="text-rose-400">*</span>
                                </label>
                                <div className="relative">
                                    <div
                                        className={`absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-all duration-200 ${
                                            focusedField === 'email' ? 'text-indigo-400 scale-110' : 'text-slate-500'
                                        }`}
                                    >
                                        <Mail className="w-4 h-4" />
                                    </div>
                                    <input
                                        type="email"
                                        value={data.email}
                                        onFocus={() => setFocusedField('email')}
                                        onBlur={() => setFocusedField(null)}
                                        onChange={(e) => setData('email', e.target.value)}
                                        placeholder="Enter official email address"
                                        className={`w-full pl-10 pr-3.5 py-2 bg-slate-950/80 border rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none transition-all duration-200 font-mono ${
                                            focusedField === 'email'
                                                ? 'border-indigo-500/70 ring-2 ring-indigo-500/20 shadow-[0_0_12px_rgba(99,102,241,0.15)]'
                                                : 'border-slate-800 hover:border-slate-700'
                                        }`}
                                        required
                                    />
                                </div>
                                {errors.email && <p className="mt-1 text-xs text-rose-400 animate-micro-shake">{errors.email}</p>}
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                                    Official Designation
                                </label>
                                <div className="relative">
                                    <div
                                        className={`absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-all duration-200 ${
                                            focusedField === 'designation' ? 'text-indigo-400 scale-110' : 'text-slate-500'
                                        }`}
                                    >
                                        <Briefcase className="w-3.5 h-3.5" />
                                    </div>
                                    <input
                                        type="text"
                                        value={data.designation}
                                        onFocus={() => setFocusedField('designation')}
                                        onBlur={() => setFocusedField(null)}
                                        onChange={(e) => setData('designation', e.target.value)}
                                        placeholder="Enter official designation"
                                        className={`w-full pl-9 pr-3 py-2 bg-slate-950/80 border rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none transition-all duration-200 ${
                                            focusedField === 'designation'
                                                ? 'border-indigo-500/70 ring-2 ring-indigo-500/20'
                                                : 'border-slate-800 hover:border-slate-700'
                                        }`}
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Interactive Official Role Selector Cards */}
                        <div className="space-y-1.5">
                            <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                                Requested Official Role <span className="text-rose-400">*</span>
                            </label>
                            <div className="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                {officialRoles.map((role) => {
                                    const isSelected = data.role === role.id;
                                    const RoleIcon = role.icon;
                                    return (
                                        <button
                                            key={role.id}
                                            type="button"
                                            onClick={() => setData('role', role.id)}
                                            className={`p-2.5 rounded-xl border text-left transition-all duration-200 active:scale-95 flex flex-col justify-between relative group cursor-pointer ${
                                                isSelected
                                                    ? 'bg-indigo-950/60 border-indigo-500 ring-2 ring-indigo-500/30 shadow-[0_0_15px_rgba(99,102,241,0.2)]'
                                                    : 'bg-slate-950/70 border-slate-800 hover:border-slate-700'
                                            }`}
                                        >
                                            <div className="flex items-center justify-between mb-1.5 w-full">
                                                <div
                                                    className={`p-1.5 rounded-lg transition-colors ${
                                                        isSelected
                                                            ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30'
                                                            : 'bg-slate-900 text-slate-400 group-hover:text-indigo-400'
                                                    }`}
                                                >
                                                    <RoleIcon className="w-3.5 h-3.5" />
                                                </div>
                                                {isSelected && (
                                                    <span className="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-ping" />
                                                )}
                                            </div>
                                            <div>
                                                <div
                                                    className={`text-xs font-bold leading-tight transition-colors ${
                                                        isSelected ? 'text-white' : 'text-slate-300'
                                                    }`}
                                                >
                                                    {role.short}
                                                </div>
                                                <div className="text-[10px] text-slate-400 truncate mt-0.5">
                                                    {role.desc}
                                                </div>
                                            </div>
                                        </button>
                                    );
                                })}
                            </div>
                            {/* Hidden native select for accessibility and automated fallback */}
                            <select
                                value={data.role}
                                onChange={(e) => setData('role', e.target.value)}
                                className="sr-only"
                                tabIndex={-1}
                            >
                                <option value="deo">Dealing Assistant / DEO</option>
                                <option value="checker">Assistant Accounts Officer (AAO)</option>
                                <option value="approver">Senior Accounts Officer (Sr. AO)</option>
                                <option value="dispatch">Outward Dispatch Officer</option>
                            </select>
                            {errors.role && <p className="mt-1 text-xs text-rose-400 animate-micro-shake">{errors.role}</p>}
                        </div>

                        {/* Section & Phone Grid */}
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                                    Section / Branch
                                </label>
                                <div className="relative">
                                    <div
                                        className={`absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-all duration-200 ${
                                            focusedField === 'section' ? 'text-indigo-400 scale-110' : 'text-slate-500'
                                        }`}
                                    >
                                        <Building2 className="w-3.5 h-3.5" />
                                    </div>
                                    <input
                                        type="text"
                                        value={data.section}
                                        onFocus={() => setFocusedField('section')}
                                        onBlur={() => setFocusedField(null)}
                                        onChange={(e) => setData('section', e.target.value)}
                                        placeholder="Enter branch / section"
                                        className={`w-full pl-9 pr-3 py-2 bg-slate-950/80 border rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none transition-all duration-200 ${
                                            focusedField === 'section'
                                                ? 'border-indigo-500/70 ring-2 ring-indigo-500/20'
                                                : 'border-slate-800 hover:border-slate-700'
                                        }`}
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                                    Mobile Number
                                </label>
                                <div className="relative">
                                    <div
                                        className={`absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-all duration-200 ${
                                            focusedField === 'phone' ? 'text-indigo-400 scale-110' : 'text-slate-500'
                                        }`}
                                    >
                                        <Phone className="w-3.5 h-3.5" />
                                    </div>
                                    <input
                                        type="text"
                                        value={data.phone_number}
                                        onFocus={() => setFocusedField('phone')}
                                        onBlur={() => setFocusedField(null)}
                                        onChange={(e) => setData('phone_number', e.target.value)}
                                        placeholder="Enter 10-digit mobile number"
                                        className={`w-full pl-9 pr-3 py-2 bg-slate-950/80 border rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none transition-all duration-200 font-mono ${
                                            focusedField === 'phone'
                                                ? 'border-indigo-500/70 ring-2 ring-indigo-500/20'
                                                : 'border-slate-800 hover:border-slate-700'
                                        }`}
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Password and Confirmation Grid */}
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-1">
                            <div>
                                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                                    Password <span className="text-rose-400">*</span>
                                </label>
                                <div className="relative">
                                    <div
                                        className={`absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-all duration-200 ${
                                            focusedField === 'password' ? 'text-indigo-400 scale-110' : 'text-slate-500'
                                        }`}
                                    >
                                        <Lock className="w-4 h-4" />
                                    </div>
                                    <input
                                        type={showPassword ? 'text' : 'password'}
                                        value={data.password}
                                        onFocus={() => setFocusedField('password')}
                                        onBlur={() => setFocusedField(null)}
                                        onChange={(e) => setData('password', e.target.value)}
                                        placeholder="Enter secure password (min 8 chars)"
                                        className={`w-full pl-10 pr-10 py-2 bg-slate-950/80 border rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none transition-all duration-200 font-mono ${
                                            focusedField === 'password'
                                                ? 'border-indigo-500/70 ring-2 ring-indigo-500/20 shadow-[0_0_12px_rgba(99,102,241,0.15)]'
                                                : 'border-slate-800 hover:border-slate-700'
                                        }`}
                                        required
                                    />
                                    <button
                                        type="button"
                                        onClick={() => setShowPassword(!showPassword)}
                                        tabIndex={-1}
                                        title={showPassword ? 'Mask password' : 'Peek password'}
                                        className="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-slate-300 transition active:scale-90"
                                    >
                                        {showPassword ? (
                                            <EyeOff className="w-4 h-4 text-amber-400" />
                                        ) : (
                                            <Eye className="w-4 h-4" />
                                        )}
                                    </button>
                                </div>
                                {errors.password && <p className="mt-1 text-xs text-rose-400 animate-micro-shake">{errors.password}</p>}
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                                    Confirm Password <span className="text-rose-400">*</span>
                                </label>
                                <div className="relative">
                                    <div
                                        className={`absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-all duration-200 ${
                                            focusedField === 'password_confirmation' ? 'text-indigo-400 scale-110' : 'text-slate-500'
                                        }`}
                                    >
                                        <Lock className="w-4 h-4" />
                                    </div>
                                    <input
                                        type={showPassword ? 'text' : 'password'}
                                        value={data.password_confirmation}
                                        onFocus={() => setFocusedField('password_confirmation')}
                                        onBlur={() => setFocusedField(null)}
                                        onChange={(e) => setData('password_confirmation', e.target.value)}
                                        placeholder="Re-enter password to confirm"
                                        className={`w-full pl-10 pr-4 py-2 bg-slate-950/80 border rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none transition-all duration-200 font-mono ${
                                            focusedField === 'password_confirmation'
                                                ? 'border-indigo-500/70 ring-2 ring-indigo-500/20 shadow-[0_0_12px_rgba(99,102,241,0.15)]'
                                                : 'border-slate-800 hover:border-slate-700'
                                        }`}
                                        required
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Interactive Dynamic Password Strength Gauge */}
                        <PasswordStrengthMeter password={data.password} />

                        {/* Submit Button */}
                        <button
                            type="submit"
                            disabled={processing}
                            className="relative group w-full py-2.5 px-4 rounded-xl text-white text-sm font-semibold shadow-xl shadow-indigo-600/25 overflow-hidden transition-all duration-200 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed mt-4 bg-gradient-to-r from-indigo-600 via-indigo-500 to-indigo-600 hover:from-indigo-500 hover:to-indigo-500 cursor-pointer"
                        >
                            <div className="absolute inset-0 -translate-x-full group-hover:translate-x-full transition-transform duration-1000 bg-gradient-to-r from-transparent via-white/15 to-transparent pointer-events-none" />

                            <div className="relative flex items-center justify-center gap-2">
                                {processing ? (
                                    <>
                                        <span className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                                        <span>Submitting Registration...</span>
                                    </>
                                ) : (
                                    <>
                                        <span>Submit Officer Registration</span>
                                        <ArrowRight className="w-4 h-4 transition-transform group-hover:translate-x-1" />
                                    </>
                                )}
                            </div>
                        </button>
                    </form>

                    {/* Footer note */}
                    <div className="mt-5 pt-3.5 border-t border-slate-800/80 text-center">
                        <p className="text-xs text-slate-400">
                            Already registered?{' '}
                            <Link href="/login" className="text-indigo-400 hover:text-indigo-300 font-semibold underline hover:text-indigo-200 transition">
                                Sign In to Portal
                            </Link>
                        </p>
                    </div>
                </InteractiveTiltCard>
            </div>
        </AuthBackground>
    );
}
