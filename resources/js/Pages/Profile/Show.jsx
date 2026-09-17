import React, { useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    User,
    Mail,
    Lock,
    Shield,
    ShieldCheck,
    CheckCircle2,
    KeyRound,
    Building2,
    Phone,
    Briefcase,
    Calendar,
    Activity,
    FileText,
    Award,
    Eye,
    EyeOff,
    Save,
    AlertCircle,
    UserCheck
} from 'lucide-react';

export default function ProfileShow({ user = {}, stats = {}, recent_activity = [] }) {
    const { flash = {} } = usePage().props;
    const [activeTab, setActiveTab] = useState('profile'); // 'profile', 'security', 'audit'

    // Form for profile updates
    const profileForm = useForm({
        name: user?.name || '',
        email: user?.email || '',
        designation: user?.designation || '',
        section: user?.section || '',
        phone_number: user?.phone_number || '',
    });

    // Form for password update
    const passwordForm = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const [showCurrentPassword, setShowCurrentPassword] = useState(false);
    const [showNewPassword, setShowNewPassword] = useState(false);

    const handleProfileSubmit = (e) => {
        e.preventDefault();
        profileForm.put('/profile', {
            preserveScroll: true,
        });
    };

    const handlePasswordSubmit = (e) => {
        e.preventDefault();
        passwordForm.put('/profile/password', {
            preserveScroll: true,
            onSuccess: () => {
                passwordForm.reset();
            },
        });
    };

    return (
        <AuthenticatedLayout title="Officer Profile & Security">
            <Head title="Officer Profile & Security - GPF Final Payment Portal" />

            <div className="max-w-6xl mx-auto space-y-6 pb-12">
                {/* Top Banner / Header Card */}
                <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950/80 to-slate-900 border border-slate-800 p-6 sm:p-8 shadow-xl text-white">
                    <div className="absolute top-0 right-0 w-96 h-96 bg-indigo-500/15 rounded-full blur-3xl pointer-events-none -mr-20 -mt-20"></div>

                    <div className="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                        <div className="flex items-center gap-5">
                            <div className="w-20 h-20 rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-400 flex items-center justify-center text-3xl font-extrabold text-white shadow-xl shadow-indigo-600/30 border-2 border-indigo-300/30">
                                {user.name?.charAt(0) || 'U'}
                            </div>
                            <div className="space-y-1">
                                <div className="flex flex-wrap items-center gap-2.5">
                                    <h1 className="text-2xl font-bold text-white tracking-tight">
                                        {user.name}
                                    </h1>
                                    <span className="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                        {user.role_label}
                                    </span>
                                    {user.is_active ? (
                                        <span className="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 flex items-center gap-1">
                                            <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                            Active
                                        </span>
                                    ) : (
                                        <span className="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                            Disabled
                                        </span>
                                    )}
                                </div>
                                <p className="text-xs text-indigo-200 font-mono">
                                    @{user.username} • {user.email}
                                </p>
                                <div className="text-xs text-slate-300 flex flex-wrap items-center gap-4 pt-1">
                                    {user.designation && (
                                        <span className="flex items-center gap-1 text-slate-200">
                                            <Briefcase className="w-3.5 h-3.5 text-indigo-400" />
                                            {user.designation}
                                        </span>
                                    )}
                                    {user.section && (
                                        <span className="flex items-center gap-1 text-slate-200">
                                            <Building2 className="w-3.5 h-3.5 text-indigo-400" />
                                            {user.section}
                                        </span>
                                    )}
                                    <span className="flex items-center gap-1 text-slate-400 text-[11px]">
                                        <Calendar className="w-3.5 h-3.5 text-slate-400" />
                                        Joined: {user.created_at || 'Registered Officer'}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {/* Approval status banner */}
                        <div className="bg-slate-950/80 border border-slate-800 rounded-xl p-3.5 text-xs text-slate-300 space-y-1.5 min-w-[220px]">
                            <div className="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1">
                                <ShieldCheck className="w-3.5 h-3.5 text-emerald-400" />
                                <span>Institutional Governance</span>
                            </div>
                            <div className="flex items-center justify-between text-xs">
                                <span className="text-slate-400">Approval Status:</span>
                                <span className="font-semibold text-emerald-400 capitalize">{user.approval_status}</span>
                            </div>
                            {user.approved_by_name && (
                                <div className="flex items-center justify-between text-[11px]">
                                    <span className="text-slate-400">Approved By:</span>
                                    <span className="font-mono text-indigo-300">{user.approved_by_name}</span>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Navigation Tabs */}
                <div className="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-1">
                    <button
                        type="button"
                        onClick={() => setActiveTab('profile')}
                        className={`px-4 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 transition ${
                            activeTab === 'profile'
                                ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25'
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-900'
                        }`}
                    >
                        <User className="w-4 h-4" />
                        <span>Profile Information</span>
                    </button>
                    <button
                        type="button"
                        onClick={() => setActiveTab('security')}
                        className={`px-4 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 transition ${
                            activeTab === 'security'
                                ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25'
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-900'
                        }`}
                    >
                        <KeyRound className="w-4 h-4" />
                        <span>Security & Password</span>
                    </button>
                    <button
                        type="button"
                        onClick={() => setActiveTab('audit')}
                        className={`px-4 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 transition ${
                            activeTab === 'audit'
                                ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25'
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-900'
                        }`}
                    >
                        <Activity className="w-4 h-4" />
                        <span>Activity & Governance</span>
                    </button>
                </div>

                {/* Tab 1: Profile Information */}
                {activeTab === 'profile' && (
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div className="lg:col-span-2 glass-panel app-card bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm backdrop-blur-sm">
                            <h2 className="text-base font-bold text-slate-900 dark:text-white mb-1 flex items-center gap-2">
                                <User className="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                                <span>Official Profile Details</span>
                            </h2>
                            <p className="text-xs text-slate-500 dark:text-slate-400 mb-6">
                                Update your contact info, official branch, and designation.
                            </p>

                            <form onSubmit={handleProfileSubmit} className="space-y-4">
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                            Full Name
                                        </label>
                                        <div className="relative">
                                            <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                                <User className="w-4 h-4" />
                                            </div>
                                            <input
                                                type="text"
                                                value={profileForm.data.name}
                                                onChange={(e) => profileForm.setData('name', e.target.value)}
                                                className="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700/80 rounded-xl text-sm text-slate-900 dark:text-slate-100 shadow-2xs focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                                                required
                                            />
                                        </div>
                                        {profileForm.errors.name && <p className="mt-1 text-xs text-rose-500">{profileForm.errors.name}</p>}
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                            Officer Username
                                        </label>
                                        <input
                                            type="text"
                                            value={user.username}
                                            readOnly
                                            className="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-500 dark:text-slate-400 font-mono cursor-not-allowed shadow-2xs"
                                        />
                                        <span className="text-[10px] text-slate-400 dark:text-slate-500 mt-1 block">Username cannot be changed directly.</span>
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                            Official Email Address
                                        </label>
                                        <div className="relative">
                                            <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                                <Mail className="w-4 h-4" />
                                            </div>
                                            <input
                                                type="email"
                                                value={profileForm.data.email}
                                                onChange={(e) => profileForm.setData('email', e.target.value)}
                                                className="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700/80 rounded-xl text-sm text-slate-900 dark:text-slate-100 font-mono shadow-2xs focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                                                required
                                            />
                                        </div>
                                        {profileForm.errors.email && <p className="mt-1 text-xs text-rose-500">{profileForm.errors.email}</p>}
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                            Contact Phone / Mobile
                                        </label>
                                        <div className="relative">
                                            <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                                <Phone className="w-4 h-4" />
                                            </div>
                                            <input
                                                type="text"
                                                value={profileForm.data.phone_number}
                                                onChange={(e) => profileForm.setData('phone_number', e.target.value)}
                                                placeholder="98XXXXXXXX"
                                                className="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700/80 rounded-xl text-sm text-slate-900 dark:text-slate-100 font-mono shadow-2xs focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                                            />
                                        </div>
                                        {profileForm.errors.phone_number && <p className="mt-1 text-xs text-rose-500">{profileForm.errors.phone_number}</p>}
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                            Designation
                                        </label>
                                        <div className="relative">
                                            <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                                <Briefcase className="w-4 h-4" />
                                            </div>
                                            <input
                                                type="text"
                                                value={profileForm.data.designation}
                                                onChange={(e) => profileForm.setData('designation', e.target.value)}
                                                placeholder="e.g. Senior Accounts Officer"
                                                className="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700/80 rounded-xl text-sm text-slate-900 dark:text-slate-100 shadow-2xs focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                            Section / Branch
                                        </label>
                                        <div className="relative">
                                            <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                                <Building2 className="w-4 h-4" />
                                            </div>
                                            <input
                                                type="text"
                                                value={profileForm.data.section}
                                                onChange={(e) => profileForm.setData('section', e.target.value)}
                                                placeholder="e.g. Fund Section I"
                                                className="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700/80 rounded-xl text-sm text-slate-900 dark:text-slate-100 shadow-2xs focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div className="pt-2 flex justify-end">
                                    <button
                                        type="submit"
                                        disabled={profileForm.processing}
                                        className="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/30 flex items-center gap-2 transition disabled:opacity-50"
                                    >
                                        <Save className="w-4 h-4" />
                                        <span>{profileForm.processing ? 'Saving Changes...' : 'Save Profile Changes'}</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        {/* Right Info Box */}
                        <div className="space-y-6">
                            <div className="glass-panel app-card bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
                                <h3 className="text-xs font-bold text-slate-900 dark:text-slate-300 uppercase tracking-wider flex items-center gap-2">
                                    <Shield className="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                                    <span>Role Capabilities</span>
                                </h3>
                                <div className="space-y-2.5 text-xs text-slate-600 dark:text-slate-400">
                                    <div className="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 space-y-1 shadow-2xs">
                                        <div className="font-semibold text-slate-900 dark:text-slate-200">System Role: {user.role_label}</div>
                                        <p className="text-[11.5px] leading-relaxed text-slate-600 dark:text-slate-400">
                                            {user.role === 'admin' && 'Full system administration, user approval governance, security token issuance, case reassignment, and audit reports.'}
                                            {user.role === 'approver' && 'Final payment settlement approvals, digital token (DSC) signing, statutory memo authorization, and HRMS outward dispatch.'}
                                            {user.role === 'checker' && 'Audit checking of calculations, monthly subscription verification, missing credit clearance, and reverting dockets.'}
                                            {user.role === 'deo' && 'Inward docket registration, subscriber master lookups, automated interest calculation runs, and nominee splits.'}
                                            {user.role === 'dispatch' && 'Speed Post outward dispatching, postal barcode tracking, and eHRMS transmission.'}
                                        </p>
                                    </div>
                                    <div className="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs shadow-2xs">
                                        <span className="text-slate-500 dark:text-slate-400 font-medium">Database Role:</span>
                                        <span className="font-mono text-indigo-600 dark:text-indigo-400 font-semibold">{user.role}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* Tab 2: Security & Password */}
                {activeTab === 'security' && (
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div className="lg:col-span-2 glass-panel app-card bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm backdrop-blur-sm">
                            <h2 className="text-base font-bold text-slate-900 dark:text-white mb-1 flex items-center gap-2">
                                <KeyRound className="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                                <span>Change Officer Password</span>
                            </h2>
                            <p className="text-xs text-slate-500 dark:text-slate-400 mb-6">
                                Ensure your password is strong and contains at least 8 characters.
                            </p>

                            <form onSubmit={handlePasswordSubmit} className="space-y-4">
                                <div>
                                    <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                        Current Password <span className="text-rose-500">*</span>
                                    </label>
                                    <div className="relative">
                                        <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                            <Lock className="w-4 h-4" />
                                        </div>
                                        <input
                                            type="password"
                                            value={passwordForm.data.current_password}
                                            onChange={(e) => passwordForm.setData('current_password', e.target.value)}
                                            className="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700/80 rounded-xl text-sm text-slate-900 dark:text-slate-100 shadow-2xs focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                                            required
                                        />
                                    </div>
                                    {passwordForm.errors.current_password && <p className="mt-1 text-xs text-rose-500">{passwordForm.errors.current_password}</p>}
                                </div>

                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                            New Password <span className="text-rose-500">*</span>
                                        </label>
                                        <input
                                            type="password"
                                            value={passwordForm.data.password}
                                            onChange={(e) => passwordForm.setData('password', e.target.value)}
                                            className="w-full px-4 py-2.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700/80 rounded-xl text-sm text-slate-900 dark:text-slate-100 shadow-2xs focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                                            required
                                        />
                                        {passwordForm.errors.password && <p className="mt-1 text-xs text-rose-500">{passwordForm.errors.password}</p>}
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                            Confirm New Password <span className="text-rose-500">*</span>
                                        </label>
                                        <input
                                            type="password"
                                            value={passwordForm.data.password_confirmation}
                                            onChange={(e) => passwordForm.setData('password_confirmation', e.target.value)}
                                            className="w-full px-4 py-2.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700/80 rounded-xl text-sm text-slate-900 dark:text-slate-100 shadow-2xs focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                                            required
                                        />
                                    </div>
                                </div>

                                <div className="pt-2">
                                    <button
                                        type="submit"
                                        disabled={passwordForm.processing}
                                        className="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/30 flex items-center gap-2 transition disabled:opacity-50"
                                    >
                                        <Lock className="w-4 h-4" />
                                        <span>{passwordForm.processing ? 'Updating Password...' : 'Update Password'}</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        {/* Security Guidelines */}
                        <div className="glass-panel app-card bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
                            <h3 className="text-xs font-bold text-slate-900 dark:text-slate-300 uppercase tracking-wider flex items-center gap-2">
                                <ShieldCheck className="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                <span>Security Recommendations</span>
                            </h3>
                            <ul className="space-y-2.5 text-xs text-slate-600 dark:text-slate-400">
                                <li className="flex items-start gap-2">
                                    <CheckCircle2 className="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" />
                                    <span>Use at least 8 alphanumeric characters and special symbols.</span>
                                </li>
                                <li className="flex items-start gap-2">
                                    <CheckCircle2 className="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" />
                                    <span>Do not share credentials or DSC USB tokens with unauthorized staff.</span>
                                </li>
                                <li className="flex items-start gap-2">
                                    <CheckCircle2 className="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" />
                                    <span>All workflow transitions are logged with timestamps and officer ID.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                )}

                {/* Tab 3: Activity & Governance */}
                {activeTab === 'audit' && (
                    <div className="glass-panel app-card bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm backdrop-blur-sm space-y-6">
                        <h2 className="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <Activity className="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                            <span>Audit & Workflow Performance</span>
                        </h2>

                        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            <div className="p-4 rounded-xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 shadow-2xs">
                                <div className="text-xs text-slate-500 dark:text-slate-400 font-medium">Inward Registered</div>
                                <div className="text-2xl font-bold text-slate-900 dark:text-white mt-1">{stats.total_dockets_created ?? 0}</div>
                            </div>
                            <div className="p-4 rounded-xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 shadow-2xs">
                                <div className="text-xs text-slate-500 dark:text-slate-400 font-medium">Audits Checked</div>
                                <div className="text-2xl font-bold text-indigo-600 dark:text-indigo-300 mt-1">{stats.total_checks_performed ?? 0}</div>
                            </div>
                            <div className="p-4 rounded-xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 shadow-2xs">
                                <div className="text-xs text-slate-500 dark:text-slate-400 font-medium">Settlements Sanctioned</div>
                                <div className="text-2xl font-bold text-emerald-600 dark:text-emerald-300 mt-1">{stats.total_approvals_given ?? 0}</div>
                            </div>
                            <div className="p-4 rounded-xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 shadow-2xs">
                                <div className="text-xs text-slate-500 dark:text-slate-400 font-medium">DSC Signed Authorities</div>
                                <div className="text-2xl font-bold text-amber-600 dark:text-amber-300 mt-1">{stats.total_signed_authorities ?? 0}</div>
                            </div>
                        </div>

                        <div className="p-4 rounded-xl bg-slate-50/70 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-400 space-y-2">
                            <div className="font-semibold text-slate-900 dark:text-slate-300">Statutory Compliance Note:</div>
                            <p className="leading-relaxed">
                                Under Section 12 of the GPF Statutory Rules (Tripura A&E), all actions executed by registered officers are recorded immutably in the PostgreSQL transaction log and dual-replicated.
                            </p>
                        </div>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
