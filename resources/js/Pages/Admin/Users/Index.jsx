import React, { useState } from 'react';
import { Head, useForm, router, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    Users,
    UserCheck,
    UserX,
    KeyRound,
    Shield,
    ShieldAlert,
    ShieldCheck,
    CheckCircle2,
    XCircle,
    Search,
    Filter,
    Plus,
    Copy,
    Check,
    Lock,
    Edit3,
    Trash2,
    Clock,
    Mail,
    User,
    Building2,
    Briefcase,
    Phone,
    RefreshCw,
    Key,
    AlertTriangle,
    Eye,
    EyeOff
} from 'lucide-react';

export default function AdminUsersIndex({
    users = { data: [] },
    pending_users = [],
    active_tokens = [],
    stats = {},
    filters = {}
}) {
    const [activeTab, setActiveTab] = useState(pending_users.length > 0 ? 'pending' : 'all_users'); // 'pending', 'all_users', 'tokens'
    const [search, setSearch] = useState(filters.search || '');
    const [selectedRole, setSelectedRole] = useState(filters.role || '');
    const [selectedStatus, setSelectedStatus] = useState(filters.status || '');
    const [copiedToken, setCopiedToken] = useState(null);

    // Edit User Modal State
    const [editingUser, setEditingUser] = useState(null);
    const editForm = useForm({
        name: '',
        email: '',
        role: 'deo',
        designation: '',
        section: '',
        phone_number: '',
        is_active: true,
        approval_status: 'approved',
    });

    // Reset Password Modal State
    const [resettingUser, setResettingUser] = useState(null);
    const [newPassword, setNewPassword] = useState('');
    const [showNewPassword, setShowNewPassword] = useState(false);
    const resetPasswordForm = useForm({
        password: '',
    });

    // Generate Token Modal State
    const [showTokenModal, setShowTokenModal] = useState(false);
    const tokenForm = useForm({
        token_type: 'registration',
        role: 'deo',
        issued_for_email: '',
        expiry_days: 7,
        notes: '',
    });

    // Handle Search & Filter Submission
    const handleFilterSubmit = (e) => {
        e?.preventDefault();
        router.get('/admin/users', {
            search: search || undefined,
            role: selectedRole || undefined,
            status: selectedStatus || undefined,
        }, { preserveState: true, preserveScroll: true });
    };

    // Open Edit Modal
    const handleOpenEdit = (user) => {
        setEditingUser(user);
        editForm.setData({
            name: user.name || '',
            email: user.email || '',
            role: user.role || 'deo',
            designation: user.designation || '',
            section: user.section || '',
            phone_number: user.phone_number || '',
            is_active: user.is_active ?? true,
            approval_status: user.approval_status || 'approved',
        });
    };

    const handleSaveUser = (e) => {
        e.preventDefault();
        if (!editingUser) return;
        editForm.put(`/admin/users/${editingUser.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setEditingUser(null);
            },
        });
    };

    // Open Reset Password Modal
    const handleOpenResetPassword = (user) => {
        setResettingUser(user);
        resetPasswordForm.setData('password', '');
    };

    const handleSaveResetPassword = (e) => {
        e.preventDefault();
        if (!resettingUser) return;
        resetPasswordForm.post(`/admin/users/${resettingUser.id}/reset-password`, {
            preserveScroll: true,
            onSuccess: () => {
                setResettingUser(null);
                resetPasswordForm.reset();
            },
        });
    };

    // Approve Registration
    const handleApprove = (userId) => {
        if (confirm('Are you sure you want to approve and activate this officer account?')) {
            router.post(`/admin/users/${userId}/approve`, {}, { preserveScroll: true });
        }
    };

    // Reject Registration
    const handleReject = (userId) => {
        const reason = prompt('Please enter the reason for rejection (optional):', 'Application rejected by Administrator.');
        if (reason !== null) {
            router.post(`/admin/users/${userId}/reject`, { notes: reason }, { preserveScroll: true });
        }
    };

    // Generate Token
    const handleGenerateToken = (e) => {
        e.preventDefault();
        tokenForm.post('/admin/tokens/generate', {
            preserveScroll: true,
            onSuccess: () => {
                setShowTokenModal(false);
                tokenForm.reset();
            },
        });
    };

    // Revoke Token
    const handleRevokeToken = (tokenId) => {
        if (confirm('Revoke this security token immediately?')) {
            router.delete(`/admin/tokens/${tokenId}`, { preserveScroll: true });
        }
    };

    // Copy to clipboard helper
    const handleCopy = (token) => {
        navigator.clipboard.writeText(token);
        setCopiedToken(token);
        setTimeout(() => setCopiedToken(null), 2500);
    };

    return (
        <AuthenticatedLayout title="Admin Governance & User Control">
            <Head title="Admin User Governance - GPF Final Payment Portal" />

            <div className="max-w-7xl mx-auto space-y-6 pb-12">
                {/* Header Title Banner */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900/80 border border-slate-800 p-6 rounded-2xl shadow-xl">
                    <div className="flex items-center gap-4">
                        <div className="w-12 h-12 rounded-xl bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 shadow-lg shadow-indigo-600/10">
                            <Shield className="w-6 h-6" />
                        </div>
                        <div>
                            <h1 className="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                                System User Governance Console
                                <span className="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                    Directorate Level
                                </span>
                            </h1>
                            <p className="text-xs text-slate-400">
                                Manage registered institutional accounts, approve pending staff, and issue security tokens.
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        <button
                            type="button"
                            onClick={() => setShowTokenModal(true)}
                            className="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white text-xs font-semibold shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition"
                        >
                            <Plus className="w-4 h-4" />
                            <span>Issue Admin Security Token</span>
                        </button>
                    </div>
                </div>

                {/* KPI Metrics Row */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div className="p-4 rounded-xl bg-slate-900/80 border border-slate-800 shadow-md">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-medium text-slate-400">Total Officers</span>
                            <Users className="w-4 h-4 text-indigo-400" />
                        </div>
                        <div className="text-2xl font-bold text-white mt-1">{stats.total_users ?? 0}</div>
                        <div className="text-[11px] text-slate-500 mt-0.5">Registered portal users</div>
                    </div>

                    <div className={`p-4 rounded-xl border shadow-md transition ${
                        (stats.pending_approvals || 0) > 0
                            ? 'bg-amber-950/30 border-amber-500/40 shadow-amber-500/10'
                            : 'bg-slate-900/80 border-slate-800'
                    }`}>
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-medium text-amber-300">Pending Approvals</span>
                            <Clock className="w-4 h-4 text-amber-400 animate-pulse" />
                        </div>
                        <div className="text-2xl font-bold text-amber-300 mt-1">{stats.pending_approvals ?? 0}</div>
                        <div className="text-[11px] text-amber-400/80 mt-0.5">Awaiting Directorate review</div>
                    </div>

                    <div className="p-4 rounded-xl bg-slate-900/80 border border-slate-800 shadow-md">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-medium text-slate-400">Active Accounts</span>
                            <CheckCircle2 className="w-4 h-4 text-emerald-400" />
                        </div>
                        <div className="text-2xl font-bold text-emerald-300 mt-1">{stats.active_users ?? 0}</div>
                        <div className="text-[11px] text-emerald-500/80 mt-0.5">Enabled login status</div>
                    </div>

                    <div className="p-4 rounded-xl bg-slate-900/80 border border-slate-800 shadow-md">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-medium text-slate-400">Active Security Tokens</span>
                            <KeyRound className="w-4 h-4 text-indigo-400" />
                        </div>
                        <div className="text-2xl font-bold text-indigo-300 mt-1">{stats.total_tokens ?? 0}</div>
                        <div className="text-[11px] text-indigo-400/80 mt-0.5">Valid unexpired tokens</div>
                    </div>
                </div>

                {/* Console Navigation Tabs */}
                <div className="flex items-center gap-2 border-b border-slate-800 pb-1">
                    <button
                        type="button"
                        onClick={() => setActiveTab('pending')}
                        className={`px-4 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 transition ${
                            activeTab === 'pending'
                                ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25'
                                : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900'
                        }`}
                    >
                        <UserCheck className="w-4 h-4" />
                        <span>Pending Approvals Queue</span>
                        {pending_users.length > 0 && (
                            <span className="px-1.5 py-0.2 rounded-full bg-amber-500 text-slate-950 font-bold text-[10px]">
                                {pending_users.length}
                            </span>
                        )}
                    </button>

                    <button
                        type="button"
                        onClick={() => setActiveTab('all_users')}
                        className={`px-4 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 transition ${
                            activeTab === 'all_users'
                                ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25'
                                : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900'
                        }`}
                    >
                        <Users className="w-4 h-4" />
                        <span>All Institutional Accounts ({stats.total_users ?? 0})</span>
                    </button>

                    <button
                        type="button"
                        onClick={() => setActiveTab('tokens')}
                        className={`px-4 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 transition ${
                            activeTab === 'tokens'
                                ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25'
                                : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900'
                        }`}
                    >
                        <KeyRound className="w-4 h-4" />
                        <span>Admin Security Tokens ({active_tokens.length})</span>
                    </button>
                </div>

                {/* Tab 1: Pending Approvals Queue */}
                {activeTab === 'pending' && (
                    <div className="space-y-4">
                        {pending_users.length === 0 ? (
                            <div className="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-12 text-center space-y-3">
                                <div className="w-12 h-12 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center mx-auto border border-emerald-500/20">
                                    <CheckCircle2 className="w-6 h-6" />
                                </div>
                                <h3 className="text-base font-semibold text-white">No Pending Registrations</h3>
                                <p className="text-xs text-slate-400 max-w-sm mx-auto">
                                    All submitted officer registration requests have been reviewed and approved.
                                </p>
                            </div>
                        ) : (
                            <div className="space-y-3">
                                {pending_users.map((pu) => (
                                    <div
                                        key={pu.id}
                                        className="p-5 rounded-2xl bg-slate-900/90 border border-amber-500/30 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4"
                                    >
                                        <div className="flex items-start gap-4">
                                            <div className="w-12 h-12 rounded-xl bg-amber-500/20 border border-amber-500/40 text-amber-300 font-bold flex items-center justify-center text-lg flex-shrink-0">
                                                {pu.name?.charAt(0) || 'U'}
                                            </div>
                                            <div className="space-y-1">
                                                <div className="flex items-center gap-2 flex-wrap">
                                                    <h3 className="text-base font-bold text-white">{pu.name}</h3>
                                                    <span className="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                                        Requested: {pu.role_label}
                                                    </span>
                                                    <span className="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-500/20 text-amber-300 border border-amber-500/30 animate-pulse">
                                                        Approval Pending
                                                    </span>
                                                </div>
                                                <div className="text-xs text-slate-400 font-mono">
                                                    @{pu.username} • {pu.email}
                                                </div>
                                                <div className="text-xs text-slate-400 flex flex-wrap items-center gap-3 pt-1">
                                                    {pu.designation && <span>Desg: {pu.designation}</span>}
                                                    {pu.section && <span>Section: {pu.section}</span>}
                                                    {pu.phone_number && <span>Phone: {pu.phone_number}</span>}
                                                    <span className="text-[11px] text-slate-500">Submitted: {pu.created_at}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="flex items-center gap-2">
                                            <button
                                                type="button"
                                                onClick={() => handleOpenEdit(pu)}
                                                className="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold flex items-center gap-1.5 transition border border-slate-700"
                                            >
                                                <Edit3 className="w-3.5 h-3.5" />
                                                <span>Modify Role/Info</span>
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => handleReject(pu.id)}
                                                className="px-3 py-2 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 border border-rose-500/30 text-rose-300 text-xs font-semibold flex items-center gap-1.5 transition"
                                            >
                                                <XCircle className="w-3.5 h-3.5 text-rose-400" />
                                                <span>Reject</span>
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => handleApprove(pu.id)}
                                                className="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white text-xs font-semibold flex items-center gap-1.5 shadow-lg shadow-emerald-600/20 transition"
                                            >
                                                <CheckCircle2 className="w-3.5 h-3.5" />
                                                <span>Approve & Activate</span>
                                            </button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                )}

                {/* Tab 2: All User Accounts */}
                {activeTab === 'all_users' && (
                    <div className="space-y-4">
                        {/* Filter and Search Bar */}
                        <form onSubmit={handleFilterSubmit} className="grid grid-cols-1 sm:grid-cols-12 gap-3 bg-slate-900/80 p-4 rounded-2xl border border-slate-800">
                            <div className="sm:col-span-5 relative">
                                <Search className="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500" />
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Search by name, username, or email..."
                                    className="w-full pl-10 pr-4 py-2 bg-slate-950 border border-slate-700/80 rounded-xl text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                />
                            </div>

                            <div className="sm:col-span-3">
                                <select
                                    value={selectedRole}
                                    onChange={(e) => setSelectedRole(e.target.value)}
                                    className="w-full px-3 py-2 bg-slate-950 border border-slate-700/80 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                    <option value="">All Roles</option>
                                    <option value="admin">Administrator / Director</option>
                                    <option value="approver">Approver (Sr. AO)</option>
                                    <option value="checker">Checker (AAO)</option>
                                    <option value="deo">Dealing Assistant / DEO</option>
                                    <option value="dispatch">Dispatch Section</option>
                                </select>
                            </div>

                            <div className="sm:col-span-2">
                                <select
                                    value={selectedStatus}
                                    onChange={(e) => setSelectedStatus(e.target.value)}
                                    className="w-full px-3 py-2 bg-slate-950 border border-slate-700/80 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                    <option value="">All Statuses</option>
                                    <option value="approved">Approved</option>
                                    <option value="pending">Pending</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Disabled</option>
                                </select>
                            </div>

                            <div className="sm:col-span-2 flex gap-2">
                                <button
                                    type="submit"
                                    className="flex-1 py-2 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold flex items-center justify-center gap-1.5 transition"
                                >
                                    <Filter className="w-3.5 h-3.5" />
                                    <span>Filter</span>
                                </button>
                                {(search || selectedRole || selectedStatus) && (
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setSearch('');
                                            setSelectedRole('');
                                            setSelectedStatus('');
                                            router.get('/admin/users');
                                        }}
                                        className="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition"
                                        title="Clear Filters"
                                    >
                                        <RefreshCw className="w-3.5 h-3.5" />
                                    </button>
                                )}
                            </div>
                        </form>

                        {/* Users Table */}
                        <div className="bg-slate-900/80 border border-slate-800/80 rounded-2xl overflow-hidden shadow-xl">
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-xs text-slate-300">
                                    <thead className="bg-slate-950/80 text-slate-400 uppercase tracking-wider text-[10px] border-b border-slate-800">
                                        <tr>
                                            <th className="py-3 px-4">Officer / Account</th>
                                            <th className="py-3 px-4">Role</th>
                                            <th className="py-3 px-4">Branch / Section</th>
                                            <th className="py-3 px-4">Approval Status</th>
                                            <th className="py-3 px-4">Login Status</th>
                                            <th className="py-3 px-4 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-800/60">
                                        {users.data.length === 0 ? (
                                            <tr>
                                                <td colSpan="6" className="py-8 text-center text-slate-500">
                                                    No user accounts found matching current criteria.
                                                </td>
                                            </tr>
                                        ) : (
                                            users.data.map((u) => (
                                                <tr key={u.id} className="hover:bg-slate-800/40 transition">
                                                    <td className="py-3 px-4">
                                                        <div className="font-semibold text-white">{u.name}</div>
                                                        <div className="text-[11px] font-mono text-slate-400">
                                                            @{u.username} • {u.email}
                                                        </div>
                                                        {u.phone_number && (
                                                            <div className="text-[10px] text-slate-500 font-mono">
                                                                Ph: {u.phone_number}
                                                            </div>
                                                        )}
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        <span className="px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-indigo-500/15 text-indigo-300 border border-indigo-500/25">
                                                            {u.role_label}
                                                        </span>
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        <div>{u.section || '—'}</div>
                                                        <div className="text-[10px] text-slate-500">{u.designation || '—'}</div>
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        {u.approval_status === 'approved' && (
                                                            <span className="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                                                                Approved
                                                            </span>
                                                        )}
                                                        {u.approval_status === 'pending' && (
                                                            <span className="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-500/15 text-amber-300 border border-amber-500/30 animate-pulse">
                                                                Pending
                                                            </span>
                                                        )}
                                                        {u.approval_status === 'rejected' && (
                                                            <span className="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-500/15 text-rose-300 border border-rose-500/30">
                                                                Rejected
                                                            </span>
                                                        )}
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        {u.is_active ? (
                                                            <span className="inline-flex items-center gap-1 text-emerald-400 font-medium">
                                                                <span className="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                                                Active
                                                            </span>
                                                        ) : (
                                                            <span className="inline-flex items-center gap-1 text-rose-400 font-medium">
                                                                <span className="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                                                Disabled
                                                            </span>
                                                        )}
                                                    </td>
                                                    <td className="py-3 px-4 text-right">
                                                        <div className="inline-flex items-center gap-1.5">
                                                            <button
                                                                type="button"
                                                                onClick={() => handleOpenEdit(u)}
                                                                className="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition"
                                                                title="Edit Officer Profile / Role / Status"
                                                            >
                                                                <Edit3 className="w-3.5 h-3.5" />
                                                            </button>
                                                            <button
                                                                type="button"
                                                                onClick={() => handleOpenResetPassword(u)}
                                                                className="p-1.5 rounded-lg bg-amber-500/15 hover:bg-amber-500/25 text-amber-300 transition"
                                                                title="Direct Admin Password Reset"
                                                            >
                                                                <Lock className="w-3.5 h-3.5" />
                                                            </button>
                                                            {u.approval_status === 'pending' && (
                                                                <button
                                                                    type="button"
                                                                    onClick={() => handleApprove(u.id)}
                                                                    className="p-1.5 rounded-lg bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 transition"
                                                                    title="Approve Registration"
                                                                >
                                                                    <CheckCircle2 className="w-3.5 h-3.5" />
                                                                </button>
                                                            )}
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            {/* Pagination Links */}
                            {users.links && users.links.length > 3 && (
                                <div className="p-3 border-t border-slate-800 flex items-center justify-center gap-1">
                                    {users.links.map((link, idx) => (
                                        <Link
                                            key={idx}
                                            href={link.url || '#'}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                            className={`px-3 py-1.5 rounded-lg text-xs font-medium transition ${
                                                link.active
                                                    ? 'bg-indigo-600 text-white'
                                                    : link.url
                                                    ? 'bg-slate-950 text-slate-400 hover:text-white hover:bg-slate-800'
                                                    : 'text-slate-600 cursor-not-allowed'
                                            }`}
                                        />
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                )}

                {/* Tab 3: Admin Security Tokens */}
                {activeTab === 'tokens' && (
                    <div className="space-y-4">
                        <div className="flex items-center justify-between">
                            <div>
                                <h3 className="text-sm font-bold text-white">Active Admin Authorization Tokens</h3>
                                <p className="text-xs text-slate-400">Tokens allow bypass of pending queue or authorized password resets.</p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setShowTokenModal(true)}
                                className="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-lg shadow-indigo-600/30 flex items-center gap-1.5 transition"
                            >
                                <Plus className="w-4 h-4" />
                                <span>Issue New Token</span>
                            </button>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {active_tokens.length === 0 ? (
                                <div className="col-span-2 bg-slate-900/60 border border-slate-800/80 rounded-2xl p-12 text-center text-slate-500 text-xs">
                                    No active admin tokens found. Click "Issue New Token" to create one.
                                </div>
                            ) : (
                                active_tokens.map((tk) => (
                                    <div
                                        key={tk.id}
                                        className="p-5 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-3 relative overflow-hidden"
                                    >
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center gap-2">
                                                <Key className="w-4 h-4 text-indigo-400" />
                                                <span className="font-mono text-sm font-bold text-indigo-300">
                                                    {tk.token}
                                                </span>
                                            </div>
                                            <button
                                                type="button"
                                                onClick={() => handleCopy(tk.token)}
                                                className="px-2.5 py-1 rounded-lg bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 text-[11px] font-medium flex items-center gap-1 transition"
                                            >
                                                {copiedToken === tk.token ? (
                                                    <>
                                                        <Check className="w-3.5 h-3.5 text-emerald-400" />
                                                        <span className="text-emerald-400">Copied!</span>
                                                    </>
                                                ) : (
                                                    <>
                                                        <Copy className="w-3.5 h-3.5" />
                                                        <span>Copy</span>
                                                    </>
                                                )}
                                            </button>
                                        </div>

                                        <div className="grid grid-cols-2 gap-2 text-xs pt-1">
                                            <div>
                                                <span className="text-slate-500 text-[11px] block">Purpose</span>
                                                <span className="font-semibold text-slate-200 capitalize">
                                                    {tk.token_type?.replace('_', ' ')}
                                                </span>
                                            </div>
                                            <div>
                                                <span className="text-slate-500 text-[11px] block">Assigned Role</span>
                                                <span className="font-semibold text-indigo-400">
                                                    {tk.role ? tk.role.toUpperCase() : 'Any Role'}
                                                </span>
                                            </div>
                                            <div>
                                                <span className="text-slate-500 text-[11px] block">Lock Email</span>
                                                <span className="text-slate-300 font-mono text-[11px]">
                                                    {tk.issued_for_email || 'Open to any email'}
                                                </span>
                                            </div>
                                            <div>
                                                <span className="text-slate-500 text-[11px] block">Expires</span>
                                                <span className="text-amber-400 font-medium text-[11px]">
                                                    {tk.expires_at}
                                                </span>
                                            </div>
                                        </div>

                                        {tk.notes && (
                                            <div className="text-[11px] text-slate-400 italic bg-slate-950/60 p-2 rounded-lg border border-slate-800/80">
                                                "{tk.notes}"
                                            </div>
                                        )}

                                        <div className="pt-2 border-t border-slate-800 flex items-center justify-between text-[11px] text-slate-500">
                                            <span>Issued by Admin: {tk.created_by_name}</span>
                                            <button
                                                type="button"
                                                onClick={() => handleRevokeToken(tk.id)}
                                                className="text-rose-400 hover:text-rose-300 font-medium flex items-center gap-1"
                                            >
                                                <Trash2 className="w-3 h-3" />
                                                <span>Revoke</span>
                                            </button>
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>
                    </div>
                )}
            </div>

            {/* Edit User Modal */}
            {editingUser && (
                <div className="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4">
                        <div className="flex items-center justify-between pb-3 border-b border-slate-800">
                            <h3 className="text-base font-bold text-white flex items-center gap-2">
                                <Edit3 className="w-4 h-4 text-indigo-400" />
                                <span>Edit Officer Account: {editingUser.username}</span>
                            </h3>
                            <button
                                type="button"
                                onClick={() => setEditingUser(null)}
                                className="p-1 rounded-lg text-slate-400 hover:text-white"
                            >
                                <XCircle className="w-5 h-5" />
                            </button>
                        </div>

                        <form onSubmit={handleSaveUser} className="space-y-4">
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 uppercase mb-1">Full Name</label>
                                    <input
                                        type="text"
                                        value={editForm.data.name}
                                        onChange={(e) => editForm.setData('name', e.target.value)}
                                        className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-100"
                                        required
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 uppercase mb-1">Official Email</label>
                                    <input
                                        type="email"
                                        value={editForm.data.email}
                                        onChange={(e) => editForm.setData('email', e.target.value)}
                                        className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-100 font-mono"
                                        required
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 uppercase mb-1">System Role</label>
                                    <select
                                        value={editForm.data.role}
                                        onChange={(e) => editForm.setData('role', e.target.value)}
                                        className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-100"
                                        required
                                    >
                                        <option value="deo">Dealing Assistant / DEO</option>
                                        <option value="checker">Assistant Accounts Officer (AAO)</option>
                                        <option value="approver">Senior Accounts Officer (Sr. AO)</option>
                                        <option value="dispatch">Outward Dispatch Section</option>
                                        <option value="admin">Director / Administrator</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 uppercase mb-1">Approval Status</label>
                                    <select
                                        value={editForm.data.approval_status}
                                        onChange={(e) => editForm.setData('approval_status', e.target.value)}
                                        className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-100"
                                        required
                                    >
                                        <option value="approved">Approved</option>
                                        <option value="pending">Pending</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 uppercase mb-1">Designation</label>
                                    <input
                                        type="text"
                                        value={editForm.data.designation}
                                        onChange={(e) => editForm.setData('designation', e.target.value)}
                                        className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-100"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 uppercase mb-1">Section</label>
                                    <input
                                        type="text"
                                        value={editForm.data.section}
                                        onChange={(e) => editForm.setData('section', e.target.value)}
                                        className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-100"
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 uppercase mb-1">Phone Number</label>
                                    <input
                                        type="text"
                                        value={editForm.data.phone_number}
                                        onChange={(e) => editForm.setData('phone_number', e.target.value)}
                                        className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-100 font-mono"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 uppercase mb-1">Account State</label>
                                    <select
                                        value={editForm.data.is_active ? '1' : '0'}
                                        onChange={(e) => editForm.setData('is_active', e.target.value === '1')}
                                        className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-100"
                                    >
                                        <option value="1">Active / Enabled</option>
                                        <option value="0">Disabled / Blocked</option>
                                    </select>
                                </div>
                            </div>

                            <div className="flex items-center justify-end gap-2 pt-3 border-t border-slate-800">
                                <button
                                    type="button"
                                    onClick={() => setEditingUser(null)}
                                    className="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    disabled={editForm.processing}
                                    className="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-lg shadow-indigo-600/30"
                                >
                                    {editForm.processing ? 'Saving...' : 'Save Officer Profile'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Direct Admin Reset Password Modal */}
            {resettingUser && (
                <div className="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4">
                        <div className="flex items-center justify-between pb-3 border-b border-slate-800">
                            <h3 className="text-base font-bold text-white flex items-center gap-2">
                                <Lock className="w-4 h-4 text-amber-400" />
                                <span>Reset Password: @{resettingUser.username}</span>
                            </h3>
                            <button
                                type="button"
                                onClick={() => setResettingUser(null)}
                                className="p-1 rounded-lg text-slate-400 hover:text-white"
                            >
                                <XCircle className="w-5 h-5" />
                            </button>
                        </div>

                        <p className="text-xs text-slate-400">
                            Enter the new secure password for officer <strong>{resettingUser.name}</strong>.
                        </p>

                        <form onSubmit={handleSaveResetPassword} className="space-y-4">
                            <div>
                                <label className="block text-xs font-semibold text-slate-300 uppercase mb-1">
                                    New Password <span className="text-rose-400">*</span>
                                </label>
                                <div className="relative">
                                    <input
                                        type={showNewPassword ? 'text' : 'password'}
                                        value={resetPasswordForm.data.password}
                                        onChange={(e) => resetPasswordForm.setData('password', e.target.value)}
                                        placeholder="Min 8 characters"
                                        className="w-full pl-3 pr-10 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-sm text-slate-100 font-mono focus:outline-none focus:ring-2 focus:ring-amber-500"
                                        required
                                        autoFocus
                                    />
                                    <button
                                        type="button"
                                        onClick={() => setShowNewPassword(!showNewPassword)}
                                        className="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-slate-300"
                                    >
                                        {showNewPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                                    </button>
                                </div>
                                {resetPasswordForm.errors.password && (
                                    <p className="mt-1 text-xs text-rose-400">{resetPasswordForm.errors.password}</p>
                                )}
                            </div>

                            <div className="flex items-center justify-end gap-2 pt-3 border-t border-slate-800">
                                <button
                                    type="button"
                                    onClick={() => setResettingUser(null)}
                                    className="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    disabled={resetPasswordForm.processing}
                                    className="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-xs font-semibold text-white shadow-lg shadow-amber-600/30"
                                >
                                    {resetPasswordForm.processing ? 'Updating...' : 'Set New Password'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Generate Token Modal */}
            {showTokenModal && (
                <div className="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4">
                        <div className="flex items-center justify-between pb-3 border-b border-slate-800">
                            <h3 className="text-base font-bold text-white flex items-center gap-2">
                                <KeyRound className="w-4 h-4 text-indigo-400" />
                                <span>Issue Admin Security Authorization Token</span>
                            </h3>
                            <button
                                type="button"
                                onClick={() => setShowTokenModal(false)}
                                className="p-1 rounded-lg text-slate-400 hover:text-white"
                            >
                                <XCircle className="w-5 h-5" />
                            </button>
                        </div>

                        <form onSubmit={handleGenerateToken} className="space-y-4">
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 uppercase mb-1">
                                        Token Purpose <span className="text-rose-400">*</span>
                                    </label>
                                    <select
                                        value={tokenForm.data.token_type}
                                        onChange={(e) => tokenForm.setData('token_type', e.target.value)}
                                        className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-100"
                                        required
                                    >
                                        <option value="registration">Instant User Registration</option>
                                        <option value="password_reset">Authorized Password Reset</option>
                                        <option value="all">Universal Purpose</option>
                                    </select>
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 uppercase mb-1">
                                        Designated Role
                                    </label>
                                    <select
                                        value={tokenForm.data.role}
                                        onChange={(e) => tokenForm.setData('role', e.target.value)}
                                        className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-100"
                                    >
                                        <option value="deo">Dealing Assistant / DEO</option>
                                        <option value="checker">Assistant Accounts Officer (AAO)</option>
                                        <option value="approver">Senior Accounts Officer (Sr. AO)</option>
                                        <option value="dispatch">Outward Dispatch</option>
                                        <option value="admin">Director / Admin</option>
                                    </select>
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 uppercase mb-1">
                                        Locked for Email (Optional)
                                    </label>
                                    <input
                                        type="email"
                                        value={tokenForm.data.issued_for_email}
                                        onChange={(e) => tokenForm.setData('issued_for_email', e.target.value)}
                                        placeholder="officer@tripura.gov.in"
                                        className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-100 font-mono"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-slate-300 uppercase mb-1">
                                        Validity Duration (Days) <span className="text-rose-400">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        min="1"
                                        max="30"
                                        value={tokenForm.data.expiry_days}
                                        onChange={(e) => tokenForm.setData('expiry_days', parseInt(e.target.value) || 1)}
                                        className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-100 font-mono"
                                        required
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-slate-300 uppercase mb-1">
                                    Administrative Notes / Issuance Reason
                                </label>
                                <textarea
                                    rows="2"
                                    value={tokenForm.data.notes}
                                    onChange={(e) => tokenForm.setData('notes', e.target.value)}
                                    placeholder="e.g. Authorized for newly joined AAO in Fund-I branch."
                                    className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-100"
                                />
                            </div>

                            <div className="flex items-center justify-end gap-2 pt-3 border-t border-slate-800">
                                <button
                                    type="button"
                                    onClick={() => setShowTokenModal(false)}
                                    className="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    disabled={tokenForm.processing}
                                    className="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-lg shadow-indigo-600/30"
                                >
                                    {tokenForm.processing ? 'Generating...' : 'Generate & Issue Token'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
