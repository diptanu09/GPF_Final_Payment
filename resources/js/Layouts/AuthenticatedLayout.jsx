import React, { useState, useEffect } from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import {
    LayoutDashboard,
    FileText,
    Calculator,
    Users,
    CheckCircle2,
    Award,
    Send,
    BarChart3,
    LogOut,
    Menu,
    X,
    Shield,
    Bell,
    ChevronRight,
    Clock,
    UserCheck,
    UserCircle2,
    KeyRound
} from 'lucide-react';

export default function AuthenticatedLayout({ children, title }) {
    const { url, props } = usePage();
    const { auth = {}, flash = {}, app_name = 'GPF Final Payment Portal', office_name = 'Office of the Accountant General (A&E), Tripura' } = props || {};
    const [sidebarOpen, setSidebarOpen] = useState(true);
    const [currentTime, setCurrentTime] = useState('');

    useEffect(() => {
        const updateClock = () => {
            const now = new Date();
            setCurrentTime(now.toLocaleDateString('en-IN', {
                weekday: 'short',
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            }));
        };
        updateClock();
        const timer = setInterval(updateClock, 1000);
        return () => clearInterval(timer);
    }, []);

    const navItems = [
        { label: 'Dashboard', href: '/', icon: LayoutDashboard, active: url === '/' },
        { label: 'Inward & Cases', href: '/inward', icon: FileText, active: url.startsWith('/inward') },
        { label: 'Approvals Queue', href: '/approval', icon: CheckCircle2, active: url.startsWith('/approval'), badge: auth?.user?.is_approver || auth?.user?.is_checker },
        { label: 'Authorities (DSC)', href: '/authority', icon: Award, active: url.startsWith('/authority') },
        { label: 'Outward & eHRMS', href: '/dispatch', icon: Send, active: url.startsWith('/dispatch') },
        { label: 'MIS Reports', href: '/reports', icon: BarChart3, active: url.startsWith('/reports') },
    ];

    if (auth?.user?.is_super_admin) {
        navItems.push({
            label: 'User Governance',
            href: '/admin/users',
            icon: UserCheck,
            active: url.startsWith('/admin/users'),
            pendingCount: auth?.pending_users_count || 0,
        });
    }

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans">
            {/* Top Navigation Bar */}
            <header className="h-16 border-b border-slate-800/80 bg-slate-900/75 backdrop-blur-md sticky top-0 z-40 flex items-center justify-between px-4 lg:px-6">
                <div className="flex items-center gap-3">
                    <button
                        onClick={() => setSidebarOpen(!sidebarOpen)}
                        className="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition"
                        title="Toggle Navigation"
                    >
                        {sidebarOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
                    </button>
                    <Link href="/" className="flex items-center gap-2 group">
                        <div className="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-400 flex items-center justify-center text-white font-bold shadow-lg shadow-indigo-500/20 group-hover:scale-105 transition-transform">
                            GPF
                        </div>
                        <div>
                            <h1 className="text-sm font-bold tracking-tight text-white flex items-center gap-2">
                                {app_name}
                                <span className="px-2 py-0.5 text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full">
                                    v2.0 Live
                                </span>
                            </h1>
                            <p className="text-[11px] text-slate-400 hidden sm:block truncate max-w-md">
                                {office_name}
                            </p>
                        </div>
                    </Link>
                </div>

                <div className="flex items-center gap-4">
                    {/* Live Clock */}
                    <div className="hidden md:flex items-center gap-1.5 text-xs text-slate-400 bg-slate-800/50 px-3 py-1.5 rounded-lg border border-slate-700/50">
                        <Clock className="w-3.5 h-3.5 text-indigo-400" />
                        <span className="font-mono">{currentTime}</span>
                    </div>

                    {/* Pending User Approvals Alert for Admins */}
                    {auth?.user?.is_super_admin && auth?.pending_users_count > 0 && (
                        <Link
                            href="/admin/users?status=pending"
                            className="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-500/15 border border-amber-500/30 text-amber-300 text-xs font-medium hover:bg-amber-500/25 transition animate-pulse"
                            title={`${auth.pending_users_count} pending registration approval(s)`}
                        >
                            <Bell className="w-3.5 h-3.5 text-amber-400" />
                            <span>{auth.pending_users_count} Pending Approval{auth.pending_users_count > 1 ? 's' : ''}</span>
                        </Link>
                    )}

                    {/* User Profile Link */}
                    <div className="flex items-center gap-3 pl-3 border-l border-slate-800">
                        <Link
                            href="/profile"
                            className="flex items-center gap-2 text-right group hover:opacity-90 transition"
                            title="View / Edit Profile"
                        >
                            <div className="hidden sm:block text-right">
                                <div className="text-xs font-semibold text-slate-200 group-hover:text-indigo-300 transition">
                                    {auth.user?.name}
                                </div>
                                <div className="text-[10px] text-indigo-400 font-medium">
                                    {auth.user?.role_label}
                                </div>
                            </div>
                            <div className="w-9 h-9 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-xs font-bold text-indigo-300 group-hover:border-indigo-500 group-hover:bg-indigo-950/40 transition">
                                {auth.user?.name?.charAt(0) || 'U'}
                            </div>
                        </Link>
                        <button
                            onClick={() => router.post('/logout')}
                            className="p-2 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition"
                            title="Sign Out"
                        >
                            <LogOut className="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </header>

            <div className="flex flex-1 overflow-hidden">
                {/* Sidebar Navigation */}
                <aside className={`transition-all duration-300 border-r border-slate-800/80 bg-slate-900/50 backdrop-blur flex flex-col ${sidebarOpen ? 'w-64' : 'w-20'}`}>
                    <nav className="p-3 space-y-1.5 flex-1">
                        {navItems.map((item) => {
                            const Icon = item.icon;
                            return (
                                <Link
                                    key={item.label}
                                    href={item.href}
                                    className={`flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group ${
                                        item.active
                                            ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25'
                                            : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/60'
                                    }`}
                                >
                                    <Icon className={`w-5 h-5 flex-shrink-0 ${item.active ? 'text-white' : 'text-slate-400 group-hover:text-indigo-400'}`} />
                                    {sidebarOpen && (
                                        <span className="truncate flex-1">{item.label}</span>
                                    )}
                                    {sidebarOpen && item.pendingCount > 0 && (
                                        <span className="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-500 text-slate-950">
                                            {item.pendingCount}
                                        </span>
                                    )}
                                    {sidebarOpen && !item.pendingCount && item.badge && (
                                        <span className="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                                    )}
                                </Link>
                            );
                        })}

                        {/* Profile Direct Nav */}
                        <Link
                            href="/profile"
                            className={`flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group ${
                                url.startsWith('/profile')
                                    ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25'
                                    : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/60'
                            }`}
                        >
                            <UserCircle2 className={`w-5 h-5 flex-shrink-0 ${url.startsWith('/profile') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-400'}`} />
                            {sidebarOpen && (
                                <span className="truncate flex-1">My Profile & Security</span>
                            )}
                        </Link>
                    </nav>

                    {/* System Institutional Badge */}
                    {sidebarOpen && (
                        <div className="p-4 m-3 rounded-xl bg-slate-950/60 border border-slate-800/80 text-[11px] text-slate-400 space-y-1">
                            <div className="flex items-center gap-1.5 font-semibold text-slate-300">
                                <Shield className="w-3.5 h-3.5 text-indigo-400" />
                                <span>Statutory Dual Core</span>
                            </div>
                            <p className="text-[10px] text-slate-500">
                                Connected to Oracle 11g (Read-Only) & Local Store.
                            </p>
                        </div>
                    )}
                </aside>

                {/* Main Content Area */}
                <main className="flex-1 overflow-y-auto bg-slate-950 p-4 lg:p-8">
                    {/* Flash Notifications */}
                    {flash.success && (
                        <div className="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-sm flex items-center justify-between animate-fadeIn">
                            <div className="flex items-center gap-2">
                                <CheckCircle2 className="w-5 h-5 text-emerald-400 flex-shrink-0" />
                                <span>{flash.success}</span>
                            </div>
                        </div>
                    )}

                    {flash.error && (
                        <div className="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm flex items-center justify-between animate-fadeIn">
                            <div className="flex items-center gap-2">
                                <X className="w-5 h-5 text-rose-400 flex-shrink-0" />
                                <span>{flash.error}</span>
                            </div>
                        </div>
                    )}

                    {flash.info && (
                        <div className="mb-6 p-4 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-300 text-sm flex items-center justify-between animate-fadeIn">
                            <div className="flex items-center gap-2">
                                <Bell className="w-5 h-5 text-blue-400 flex-shrink-0" />
                                <span>{flash.info}</span>
                            </div>
                        </div>
                    )}

                    {children}
                </main>
            </div>
        </div>
    );
}
