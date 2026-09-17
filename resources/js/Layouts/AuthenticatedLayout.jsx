import React, { useState, useEffect, useRef } from 'react';
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
    PanelLeftClose,
    PanelLeftOpen,
    Shield,
    Bell,
    Clock,
    UserCheck,
    UserCircle2,
    Layers,
    Search,
    ShieldAlert,
    Volume2,
    VolumeX,
    Command
} from 'lucide-react';
import { ThemeProvider, useTheme } from '@/Context/ThemeContext';
import ThemeToggle from '@/Components/UI/ThemeToggle';
import CommandPalette from '@/Components/Navigation/CommandPalette';
import ToastContainer from '@/Components/UI/ToastContainer';
import audioFeedback from '@/Services/AudioFeedbackService';

function AuthenticatedLayoutContent({ children, title }) {
    const { url, props } = usePage();
    const { auth = {}, flash = {}, app_name = 'GPF Final Payment Portal', office_name = 'Office of the Accountant General (A&E), Tripura' } = props || {};
    const [sidebarOpen, setSidebarOpen] = useState(true);
    const [commandPaletteOpen, setCommandPaletteOpen] = useState(false);
    const [currentTime, setCurrentTime] = useState('');
    const [isMuted, setIsMuted] = useState(audioFeedback.getMuted());
    const [isScrolled, setIsScrolled] = useState(false);
    const mainScrollRef = useRef(null);

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

    // Global shortcut Ctrl+K / Cmd+K listener
    useEffect(() => {
        const handleKeyDown = (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                setCommandPaletteOpen((prev) => !prev);
            }
        };
        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, []);

    // Detect scroll for dynamic transparent topbar
    useEffect(() => {
        const handleScroll = (e) => {
            const y = e?.target?.scrollTop !== undefined ? e.target.scrollTop : (window.scrollY || document.documentElement.scrollTop);
            setIsScrolled(y > 8);
        };

        window.addEventListener('scroll', handleScroll, { passive: true });
        const mainEl = mainScrollRef.current;
        if (mainEl) {
            mainEl.addEventListener('scroll', handleScroll, { passive: true });
        }

        return () => {
            window.removeEventListener('scroll', handleScroll);
            if (mainEl) {
                mainEl.removeEventListener('scroll', handleScroll);
            }
        };
    }, []);

    const toggleAudioMute = () => {
        const newMuted = audioFeedback.toggleMute();
        setIsMuted(newMuted);
    };

    const navItems = [
        { label: 'Dashboard', href: '/', icon: LayoutDashboard, active: url === '/' },
        { label: 'Inward & Cases', href: '/inward', icon: FileText, active: url.startsWith('/inward') },
        { label: 'Approvals Queue', href: '/approval', icon: CheckCircle2, active: url.startsWith('/approval'), badge: auth?.user?.is_approver || auth?.user?.is_checker },
        { label: 'Authorities (DSC)', href: '/authority', icon: Award, active: url.startsWith('/authority') },
        { label: 'Letters & Notices', href: '/letters', icon: Layers, active: url.startsWith('/letters') },
        { label: 'Global Search', href: '/search', icon: Search, active: url.startsWith('/search') },
        { label: 'Outward & eHRMS', href: '/dispatch', icon: Send, active: url.startsWith('/dispatch') },
        { label: 'MIS Reports', href: '/reports', icon: BarChart3, active: url.startsWith('/reports') },
    ];

    if (auth?.user?.is_super_admin || auth?.user?.is_approver) {
        navItems.push({
            label: 'Case Governance',
            href: '/admin/cases',
            icon: ShieldAlert,
            active: url.startsWith('/admin/cases'),
        });
    }

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
        <div className="min-h-screen bg-slate-100 dark:bg-slate-950 canvas-mesh text-slate-900 dark:text-slate-100 flex flex-col font-sans transition-colors duration-200">
            {/* Top Navigation Bar: 100% Transparent when at top, Glassmorphic when scrolled */}
            <header
                className={`fixed top-0 left-0 right-0 h-14 z-40 flex items-center justify-between px-3 sm:px-5 transition-all duration-300 ${
                    isScrolled
                        ? 'bg-white/95 dark:bg-slate-900/85 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 shadow-sm dark:shadow-none'
                        : 'bg-transparent border-b border-transparent shadow-none'
                }`}
            >
                <div className="flex items-center gap-2 sm:gap-3">
                    {/* Modern Panel Toggle Icon (PanelLeftClose / PanelLeftOpen) */}
                    <button
                        onClick={() => {
                            audioFeedback.playClick();
                            setSidebarOpen(!sidebarOpen);
                        }}
                        className="p-1.5 rounded-xl text-slate-700 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 bg-white/90 dark:bg-slate-800/60 hover:bg-white dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xs hover:shadow-xs transition group"
                        title={sidebarOpen ? 'Collapse sidebar' : 'Expand sidebar'}
                    >
                        {sidebarOpen ? (
                            <PanelLeftClose className="w-4.5 h-4.5 transition-transform group-hover:scale-105" />
                        ) : (
                            <PanelLeftOpen className="w-4.5 h-4.5 transition-transform group-hover:scale-105" />
                        )}
                    </button>

                    <Link href="/" className="flex items-center gap-2 group">
                        <img
                            src="/images/gpf_seal_badge.png"
                            alt="GPF Portal"
                            className="w-8 h-8 object-contain drop-shadow-sm group-hover:scale-105 transition-transform"
                        />
                        <div>
                            <h1 className="text-xs sm:text-sm font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-1.5">
                                {app_name}
                                <span className="px-1.5 py-0.2 text-[9px] font-semibold bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20 rounded-full">
                                    v2.0
                                </span>
                            </h1>
                            <p className="text-[10px] text-slate-600 dark:text-slate-400 hidden sm:block truncate max-w-sm">
                                {office_name}
                            </p>
                        </div>
                    </Link>
                </div>

                <div className="flex items-center gap-1.5 sm:gap-2.5">
                    {/* Command Palette Trigger Pill */}
                    <button
                        type="button"
                        onClick={() => setCommandPaletteOpen(true)}
                        className="hidden md:flex items-center gap-2 px-2.5 py-1 rounded-xl bg-white dark:bg-slate-800/60 hover:bg-white dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-slate-100 text-xs border border-slate-200 dark:border-slate-700/60 transition shadow-2xs hover:shadow-xs"
                        title="Search dockets or press Ctrl+K"
                    >
                        <Search className="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" />
                        <span className="text-[11px] font-medium">Quick Search</span>
                        <kbd className="inline-flex items-center gap-0.5 px-1.5 py-0.2 text-[9px] font-mono bg-slate-100 dark:bg-slate-900 rounded border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-400">
                            <Command className="w-2.5 h-2.5" /> K
                        </kbd>
                    </button>

                    {/* Live Clock */}
                    <div className="hidden lg:flex items-center gap-1.5 text-[11px] text-slate-700 dark:text-slate-400 bg-white dark:bg-slate-800/50 px-2.5 py-1 rounded-xl border border-slate-200 dark:border-slate-700/50 shadow-2xs">
                        <Clock className="w-3 h-3 text-indigo-600 dark:text-indigo-400" />
                        <span className="font-mono">{currentTime}</span>
                    </div>

                    {/* Pending User Approvals Alert for Admins */}
                    {auth?.user?.is_super_admin && auth?.pending_users_count > 0 && (
                        <Link
                            href="/admin/users?status=pending"
                            className="hidden sm:flex items-center gap-1 px-2 py-0.8 rounded-xl bg-amber-500/15 border border-amber-500/30 text-amber-700 dark:text-amber-300 text-[11px] font-medium hover:bg-amber-500/25 transition animate-pulse shadow-xs"
                            title={`${auth.pending_users_count} pending registration approval(s)`}
                        >
                            <Bell className="w-3 h-3 text-amber-600 dark:text-amber-400" />
                            <span>{auth.pending_users_count} Pending</span>
                        </Link>
                    )}

                    {/* Sound Mute Toggle */}
                    <button
                        type="button"
                        onClick={toggleAudioMute}
                        className="p-1.5 rounded-xl text-slate-700 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 bg-white/90 dark:bg-slate-800/60 hover:bg-white dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xs hover:shadow-xs transition"
                        title={isMuted ? 'Unmute UI sounds' : 'Mute UI sounds'}
                    >
                        {isMuted ? (
                            <VolumeX className="w-4 h-4 text-slate-400 dark:text-slate-500" />
                        ) : (
                            <Volume2 className="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                        )}
                    </button>

                    {/* Theme Toggle (Light / Dark / System) */}
                    <ThemeToggle />

                    {/* User Profile Link */}
                    <div className="flex items-center gap-1.5 sm:gap-2.5 pl-1.5 sm:pl-2.5 border-l border-slate-300/80 dark:border-slate-800">
                        <Link
                            href="/profile"
                            className="flex items-center gap-2 text-right group hover:opacity-90 transition"
                            title="View / Edit Profile"
                        >
                            <div className="hidden sm:block text-right leading-tight">
                                <div className="text-xs font-semibold text-slate-800 dark:text-slate-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-300 transition truncate max-w-[120px]">
                                    {auth.user?.name}
                                </div>
                                <div className="text-[10px] text-indigo-600 dark:text-indigo-400 font-medium">
                                    {auth.user?.role_label}
                                </div>
                            </div>
                            <div className="w-8 h-8 rounded-full bg-indigo-50 dark:bg-slate-800 border border-indigo-200/90 dark:border-slate-700 flex items-center justify-center text-xs font-bold text-indigo-700 dark:text-indigo-300 group-hover:border-indigo-500 transition shadow-xs">
                                {auth.user?.name?.charAt(0) || 'U'}
                            </div>
                        </Link>

                        <button
                            onClick={() => {
                                audioFeedback.playClick();
                                router.post('/logout');
                            }}
                            className="p-1.5 rounded-xl text-slate-500 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition"
                            title="Sign Out"
                        >
                            <LogOut className="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </header>

            {/* Layout Body */}
            <div className="flex flex-1 pt-14 relative">
                {/* Fixed Left Sidebar: Pinned in position, does NOT scroll with the page */}
                <aside
                    className={`fixed top-14 bottom-0 left-0 z-30 transition-all duration-300 flex flex-col border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/95 backdrop-blur-md overflow-y-auto shadow-xs ${
                        sidebarOpen ? 'w-56' : 'w-16'
                    }`}
                >
                    <nav className="p-2 space-y-1 flex-1">
                        {navItems.map((item) => {
                            const Icon = item.icon;
                            return (
                                <Link
                                    key={item.label}
                                    href={item.href}
                                    onClick={() => audioFeedback.playClick()}
                                    className={`flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-xs font-medium transition-all group ${
                                        item.active
                                            ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25 font-semibold'
                                            : 'text-slate-700 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60'
                                    }`}
                                >
                                    <Icon className={`w-4 h-4 flex-shrink-0 ${item.active ? 'text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400'}`} />
                                    {sidebarOpen && (
                                        <span className="truncate flex-1">{item.label}</span>
                                    )}
                                    {sidebarOpen && item.pendingCount > 0 && (
                                        <span className="px-1.5 py-0.2 text-[9px] font-bold rounded-full bg-amber-500 text-slate-950">
                                            {item.pendingCount}
                                        </span>
                                    )}
                                    {sidebarOpen && !item.pendingCount && item.badge && (
                                        <span className="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                    )}
                                </Link>
                            );
                        })}

                        {/* Profile Direct Nav */}
                        <Link
                            href="/profile"
                            onClick={() => audioFeedback.playClick()}
                            className={`flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-xs font-medium transition-all group ${
                                url.startsWith('/profile')
                                    ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25'
                                    : 'text-slate-700 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60'
                            }`}
                        >
                            <UserCircle2 className={`w-4 h-4 flex-shrink-0 ${url.startsWith('/profile') ? 'text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400'}`} />
                            {sidebarOpen && (
                                <span className="truncate flex-1">My Profile</span>
                            )}
                        </Link>
                    </nav>

                    {/* System Institutional Badge */}
                    {sidebarOpen && (
                        <div className="p-3 m-2 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 text-[10px] text-slate-600 dark:text-slate-400 space-y-1">
                            <div className="flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-300">
                                <Shield className="w-3 h-3 text-indigo-600 dark:text-indigo-400" />
                                <span>Statutory Dual Core</span>
                            </div>
                            <p className="text-[9px] text-slate-500 leading-tight">
                                Connected to Oracle 11g & PostgreSQL 18.
                            </p>
                        </div>
                    )}
                </aside>

                {/* Main Content Area: Offset by fixed sidebar width and topbar height */}
                <main
                    ref={mainScrollRef}
                    className={`flex-1 transition-all duration-300 min-h-[calc(100vh-3.5rem)] ${
                        sidebarOpen ? 'md:ml-56' : 'md:ml-16'
                    }`}
                >
                    <div className="p-4 sm:p-5 lg:p-6 max-w-7xl mx-auto w-full">
                        {children}
                    </div>
                </main>
            </div>

            {/* Global Interactive Command Palette HUD */}
            <CommandPalette
                isOpen={commandPaletteOpen}
                onClose={() => setCommandPaletteOpen(false)}
            />

            {/* Floating Toast Flash Notifications */}
            <ToastContainer />
        </div>
    );
}

export default function AuthenticatedLayout(props) {
    return (
        <ThemeProvider>
            <AuthenticatedLayoutContent {...props} />
        </ThemeProvider>
    );
}
