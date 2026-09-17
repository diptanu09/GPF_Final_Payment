import React, { useState, useEffect, useRef, useMemo } from 'react';
import { router } from '@inertiajs/react';
import {
    Search,
    LayoutDashboard,
    FilePlus,
    Calculator,
    Users,
    CheckCircle2,
    Award,
    Send,
    BarChart3,
    FileText,
    ShieldAlert,
    UserCircle2,
    Sun,
    Moon,
    Volume2,
    VolumeX,
    ArrowRight,
    Command,
    X
} from 'lucide-react';
import { useTheme } from '@/Context/ThemeContext';
import audioFeedback from '@/Services/AudioFeedbackService';

export default function CommandPalette({ isOpen, onClose }) {
    const [query, setQuery] = useState('');
    const [selectedIndex, setSelectedIndex] = useState(0);
    const { theme, setTheme, isDark } = useTheme();
    const [isMuted, setIsMuted] = useState(audioFeedback.getMuted());
    const inputRef = useRef(null);

    // Focus input on open
    useEffect(() => {
        if (isOpen) {
            audioFeedback.playCommand();
            setQuery('');
            setSelectedIndex(0);
            setTimeout(() => inputRef.current?.focus(), 50);
        }
    }, [isOpen]);

    // Keyboard navigation (Escape, Up, Down, Enter)
    useEffect(() => {
        const handleKeyDown = (e) => {
            if (!isOpen) {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                    e.preventDefault();
                    onClose(false); // toggle trigger
                }
                return;
            }

            if (e.key === 'Escape') {
                e.preventDefault();
                onClose();
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                setSelectedIndex((prev) => (prev < filteredItems.length - 1 ? prev + 1 : 0));
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                setSelectedIndex((prev) => (prev > 0 ? prev - 1 : filteredItems.length - 1));
            } else if (e.key === 'Enter') {
                e.preventDefault();
                const item = filteredItems[selectedIndex];
                if (item) {
                    item.action();
                }
            }
        };

        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [isOpen, selectedIndex]);

    // Base actions & routes
    const allItems = useMemo(() => [
        {
            category: 'Navigation',
            title: 'Executive Dashboard',
            subtitle: 'Overview KPIs, statutory aging, and recent workflow activity',
            icon: LayoutDashboard,
            action: () => {
                audioFeedback.playClick();
                onClose();
                router.visit('/dashboard');
            },
        },
        {
            category: 'Navigation',
            title: 'New Inward Docket Registration',
            subtitle: 'Register subscriber docket and auto-fetch VLC demographics',
            icon: FilePlus,
            action: () => {
                audioFeedback.playClick();
                onClose();
                router.visit('/inward/create');
            },
        },
        {
            category: 'Navigation',
            title: 'Calculation & Ledgers',
            subtitle: 'Progressive interest compounding and voucher verification',
            icon: Calculator,
            action: () => {
                audioFeedback.playClick();
                onClose();
                router.visit('/calculation');
            },
        },
        {
            category: 'Navigation',
            title: 'Nominee & Share Matrix',
            subtitle: 'Beneficiary allocation and odd paisa reconciliation',
            icon: Users,
            action: () => {
                audioFeedback.playClick();
                onClose();
                router.visit('/nominee');
            },
        },
        {
            category: 'Navigation',
            title: 'AAO Audit & Verification Queue',
            subtitle: 'Audit monthly vouchers, interest calculation, and missing credits',
            icon: CheckCircle2,
            action: () => {
                audioFeedback.playClick();
                onClose();
                router.visit('/approval/check-index');
            },
        },
        {
            category: 'Navigation',
            title: 'Sr. AO Approval Queue',
            subtitle: 'Final settlement sanctions and authorization readiness',
            icon: Award,
            action: () => {
                audioFeedback.playClick();
                onClose();
                router.visit('/approval/approve-index');
            },
        },
        {
            category: 'Navigation',
            title: 'Authority Orders & DSC Signing',
            subtitle: 'Generate statutory letters and apply PKI digital signatures',
            icon: FileText,
            action: () => {
                audioFeedback.playClick();
                onClose();
                router.visit('/authority');
            },
        },
        {
            category: 'Navigation',
            title: 'HRMS Dispatch & Outward Postal',
            subtitle: 'Track Speed Post barcodes and HRMS cloud dispatches',
            icon: Send,
            action: () => {
                audioFeedback.playClick();
                onClose();
                router.visit('/dispatch');
            },
        },
        {
            category: 'Navigation',
            title: 'Statutory Letters & Notices Action Hub',
            subtitle: 'Input Sheet, Intimation, Corrigendum, Revalidation, Objection, Minus Balance',
            icon: FileText,
            action: () => {
                audioFeedback.playClick();
                onClose();
                router.visit('/letters');
            },
        },
        {
            category: 'Navigation',
            title: 'Global Multi-Criteria Docket Search',
            subtitle: 'Deep search across accounts, names, employee codes with 6-tab inspection',
            icon: Search,
            action: () => {
                audioFeedback.playClick();
                onClose();
                router.visit('/search');
            },
        },
        {
            category: 'Navigation',
            title: 'Case Administration Governance Console',
            subtitle: 'Unapprove cases, cancellations, DSC resets, draft deletions',
            icon: ShieldAlert,
            action: () => {
                audioFeedback.playClick();
                onClose();
                router.visit('/admin/cases');
            },
        },
        {
            category: 'Navigation',
            title: 'Management Information System (MIS) Reports',
            subtitle: 'Settled cases, pending cases, staff productivity, DSC audit logs',
            icon: BarChart3,
            action: () => {
                audioFeedback.playClick();
                onClose();
                router.visit('/reports');
            },
        },
        {
            category: 'Navigation',
            title: 'Officer Profile & Security Settings',
            subtitle: 'Manage institutional profile, password changes, and audit counters',
            icon: UserCircle2,
            action: () => {
                audioFeedback.playClick();
                onClose();
                router.visit('/profile');
            },
        },
        {
            category: 'System & Theme',
            title: isDark ? 'Switch to Light Theme' : 'Switch to Dark Theme',
            subtitle: isDark ? 'Switch interface to clean daylight high-contrast mode' : 'Switch interface to night obsidian dark mode',
            icon: isDark ? Sun : Moon,
            action: () => {
                const next = isDark ? 'light' : 'dark';
                setTheme(next);
                audioFeedback.playToggle();
                onClose();
            },
        },
        {
            category: 'System & Theme',
            title: isMuted ? 'Unmute Acoustic Feedback' : 'Mute Acoustic Feedback',
            subtitle: isMuted ? 'Enable subtle UI clicks and chimes' : 'Disable all Web Audio micro-feedback sounds',
            icon: isMuted ? Volume2 : VolumeX,
            action: () => {
                const newMuted = audioFeedback.toggleMute();
                setIsMuted(newMuted);
                onClose();
            },
        },
    ], [isDark, isMuted, setTheme, onClose]);

    // Filter items based on query
    const filteredItems = useMemo(() => {
        if (!query.trim()) return allItems;
        const q = query.toLowerCase();

        const matches = allItems.filter(
            (item) =>
                item.title.toLowerCase().includes(q) ||
                item.subtitle.toLowerCase().includes(q) ||
                item.category.toLowerCase().includes(q)
        );

        // If query looks like an account or registration number, add direct search action
        if (/^\d+$/.test(query.trim()) || query.trim().length >= 3) {
            matches.unshift({
                category: 'Live Docket Search',
                title: `Search dockets matching "${query}"`,
                subtitle: 'Jump to Global Multi-Criteria Search with this query',
                icon: Search,
                action: () => {
                    audioFeedback.playClick();
                    onClose();
                    router.visit(`/search?query=${encodeURIComponent(query.trim())}`);
                },
            });
        }

        return matches;
    }, [query, allItems, onClose]);

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-start justify-center pt-16 sm:pt-24 px-4 bg-slate-950/70 backdrop-blur-md animate-in fade-in duration-150">
            <div
                className="w-full max-w-2xl bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl shadow-black/30 dark:shadow-black/70 overflow-hidden flex flex-col max-h-[80vh] animate-in zoom-in-95 duration-150"
                onClick={(e) => e.stopPropagation()}
            >
                {/* Search Bar Header */}
                <div className="flex items-center gap-3 px-4 py-3.5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                    <Search className="w-5 h-5 text-slate-400 dark:text-slate-500 flex-shrink-0" />
                    <input
                        ref={inputRef}
                        type="text"
                        value={query}
                        onChange={(e) => {
                            setQuery(e.target.value);
                            setSelectedIndex(0);
                        }}
                        placeholder="Type a command, page, or docket registration number..."
                        className="flex-1 bg-transparent border-0 text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:outline-none focus:ring-0"
                    />
                    <div className="flex items-center gap-1.5">
                        <kbd className="hidden sm:inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-medium text-slate-400 bg-slate-200/60 dark:bg-slate-800 rounded border border-slate-300 dark:border-slate-700">
                            ESC
                        </kbd>
                        <button
                            type="button"
                            onClick={onClose}
                            className="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-200/60 dark:hover:bg-slate-800"
                        >
                            <X className="w-4 h-4" />
                        </button>
                    </div>
                </div>

                {/* Results List */}
                <div className="flex-1 overflow-y-auto p-2 space-y-1">
                    {filteredItems.length === 0 ? (
                        <div className="py-12 text-center text-slate-400 dark:text-slate-500 text-xs">
                            No commands or pages found for &quot;{query}&quot;
                        </div>
                    ) : (
                        filteredItems.map((item, index) => {
                            const Icon = item.icon;
                            const isSelected = index === selectedIndex;
                            return (
                                <button
                                    key={index}
                                    type="button"
                                    onClick={item.action}
                                    onMouseEnter={() => setSelectedIndex(index)}
                                    className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition-all ${
                                        isSelected
                                            ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20'
                                            : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/50'
                                    }`}
                                >
                                    <div
                                        className={`p-2 rounded-lg ${
                                            isSelected
                                                ? 'bg-white/20 text-white'
                                                : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'
                                        }`}
                                    >
                                        <Icon className="w-4 h-4" />
                                    </div>

                                    <div className="flex-1 min-w-0">
                                        <div className="flex items-center gap-2">
                                            <span className="text-xs font-semibold truncate">{item.title}</span>
                                            <span
                                                className={`text-[10px] px-1.5 py-0.2 rounded font-mono ${
                                                    isSelected
                                                        ? 'bg-white/20 text-white'
                                                        : 'bg-slate-100 dark:bg-slate-800 text-slate-400'
                                                }`}
                                            >
                                                {item.category}
                                            </span>
                                        </div>
                                        <p
                                            className={`text-[11px] truncate mt-0.5 ${
                                                isSelected ? 'text-indigo-100' : 'text-slate-500 dark:text-slate-400'
                                            }`}
                                        >
                                            {item.subtitle}
                                        </p>
                                    </div>

                                    {isSelected && (
                                        <ArrowRight className="w-4 h-4 text-white/80 flex-shrink-0 animate-pulse" />
                                    )}
                                </button>
                            );
                        })
                    )}
                </div>

                {/* Footer Navigation Hints */}
                <div className="px-4 py-2.5 bg-slate-50 dark:bg-slate-950/80 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                    <div className="flex items-center gap-3">
                        <span className="flex items-center gap-1">
                            <kbd className="px-1.5 py-0.5 bg-slate-200 dark:bg-slate-800 rounded font-mono text-[10px]">↑↓</kbd> to navigate
                        </span>
                        <span className="flex items-center gap-1">
                            <kbd className="px-1.5 py-0.5 bg-slate-200 dark:bg-slate-800 rounded font-mono text-[10px]">↵</kbd> to select
                        </span>
                    </div>
                    <span className="flex items-center gap-1">
                        <kbd className="px-1.5 py-0.5 bg-slate-200 dark:bg-slate-800 rounded font-mono text-[10px]">Ctrl+K</kbd> to toggle
                    </span>
                </div>
            </div>
        </div>
    );
}
