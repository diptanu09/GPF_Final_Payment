import React, { useState, useRef, useEffect } from 'react';
import { useTheme } from '@/Context/ThemeContext';
import { Sun, Moon, Laptop, ChevronDown } from 'lucide-react';
import audioFeedback from '@/Services/AudioFeedbackService';

export default function ThemeToggle({ className = '' }) {
    const { theme, setTheme, isDark } = useTheme();
    const [dropdownOpen, setDropdownOpen] = useState(false);
    const dropdownRef = useRef(null);

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
                setDropdownOpen(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const handleSelect = (mode) => {
        audioFeedback.playToggle();
        setTheme(mode);
        setDropdownOpen(false);
    };

    return (
        <div className={`relative inline-block ${className}`} ref={dropdownRef}>
            <button
                type="button"
                onClick={() => {
                    audioFeedback.playClick();
                    setDropdownOpen(!dropdownOpen);
                }}
                className="flex items-center gap-1.5 p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-200/60 dark:hover:bg-slate-800/60 transition-all border border-transparent hover:border-slate-300 dark:hover:border-slate-700/50"
                title={`Current theme: ${theme} (${isDark ? 'Dark' : 'Light'}) - Click to switch`}
            >
                {theme === 'system' ? (
                    <Laptop className="w-4 h-4 text-indigo-500 dark:text-indigo-400 animate-pulse-glow" />
                ) : isDark ? (
                    <Moon className="w-4 h-4 text-amber-400 transition-transform duration-300 rotate-0 group-hover:-rotate-12" />
                ) : (
                    <Sun className="w-4 h-4 text-amber-500 transition-transform duration-300 rotate-0 group-hover:rotate-45" />
                )}
                <ChevronDown className="w-3 h-3 opacity-60" />
            </button>

            {dropdownOpen && (
                <div className="absolute right-0 mt-2 w-36 py-1 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl shadow-black/10 dark:shadow-black/50 z-50 animate-in fade-in zoom-in-95 duration-150">
                    <button
                        type="button"
                        onClick={() => handleSelect('light')}
                        className={`w-full flex items-center gap-2.5 px-3 py-2 text-xs font-medium transition ${
                            theme === 'light'
                                ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold'
                                : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'
                        }`}
                    >
                        <Sun className="w-4 h-4 text-amber-500" />
                        <span>Light</span>
                    </button>

                    <button
                        type="button"
                        onClick={() => handleSelect('dark')}
                        className={`w-full flex items-center gap-2.5 px-3 py-2 text-xs font-medium transition ${
                            theme === 'dark'
                                ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold'
                                : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'
                        }`}
                    >
                        <Moon className="w-4 h-4 text-amber-400" />
                        <span>Dark</span>
                    </button>

                    <button
                        type="button"
                        onClick={() => handleSelect('system')}
                        className={`w-full flex items-center gap-2.5 px-3 py-2 text-xs font-medium transition border-t border-slate-100 dark:border-slate-800/80 ${
                            theme === 'system'
                                ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 font-semibold'
                                : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'
                        }`}
                    >
                        <Laptop className="w-4 h-4 text-indigo-500 dark:text-indigo-400" />
                        <span>System Auto</span>
                    </button>
                </div>
            )}
        </div>
    );
}
