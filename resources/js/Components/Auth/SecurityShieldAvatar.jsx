import React from 'react';
import { Shield, ShieldCheck, ShieldAlert, Lock, Unlock, Eye, KeyRound, Sparkles, Loader2 } from 'lucide-react';

/**
 * Reactive Security Shield Avatar.
 * Dynamically transforms its visual appearance, animations, and status readout
 * based on user focus, password visibility, processing state, or error state.
 */
export default function SecurityShieldAvatar({
    state = 'idle', // 'idle' | 'username' | 'password' | 'peek' | 'processing' | 'error' | 'token'
    title = 'Government of India • CAG',
    subtitle = 'Office of the Accountant General (A & E), Tripura ::: Agartala',
    heading = 'GPF Final Payment Portal'
}) {
    // Status metadata mapping
    const statusConfig = {
        idle: {
            text: 'OFFICIAL AUDIT TERMINAL • READY',
            badgeClass: 'text-slate-400 bg-slate-800/60 border-slate-700/60',
            dotClass: 'bg-emerald-400',
            ringClass: 'border-indigo-500/30',
            iconGlow: 'shadow-indigo-600/20 text-indigo-400',
            icon: <ShieldCheck className="w-8 h-8 text-indigo-300" />,
        },
        username: {
            text: 'SCANNING OFFICER IDENTITY...',
            badgeClass: 'text-cyan-300 bg-cyan-950/60 border-cyan-500/40',
            dotClass: 'bg-cyan-400 animate-ping',
            ringClass: 'border-cyan-400/60',
            iconGlow: 'shadow-cyan-500/30 text-cyan-300',
            icon: <Shield className="w-8 h-8 text-cyan-300" />,
        },
        password: {
            text: 'ENCRYPTED KEYSTORE ARMED',
            badgeClass: 'text-indigo-300 bg-indigo-950/60 border-indigo-500/40',
            dotClass: 'bg-indigo-400',
            ringClass: 'border-indigo-400/70',
            iconGlow: 'shadow-indigo-500/40 text-indigo-300',
            icon: <Lock className="w-8 h-8 text-indigo-300" />,
        },
        peek: {
            text: 'PLAINTEXT PREVIEW ACTIVE',
            badgeClass: 'text-amber-300 bg-amber-950/60 border-amber-500/40',
            dotClass: 'bg-amber-400 animate-pulse',
            ringClass: 'border-amber-400/70',
            iconGlow: 'shadow-amber-500/40 text-amber-300',
            icon: <Eye className="w-8 h-8 text-amber-300" />,
        },
        token: {
            text: 'ADMIN SECURITY TOKEN ENGAGED',
            badgeClass: 'text-emerald-300 bg-emerald-950/60 border-emerald-500/40',
            dotClass: 'bg-emerald-400 animate-pulse',
            ringClass: 'border-emerald-400/70',
            iconGlow: 'shadow-emerald-500/40 text-emerald-300',
            icon: <Sparkles className="w-8 h-8 text-emerald-300" />,
        },
        processing: {
            text: 'VERIFYING CREDENTIALS & AUDIT LOGS...',
            badgeClass: 'text-sky-300 bg-sky-950/60 border-sky-500/40',
            dotClass: 'bg-sky-400 animate-spin',
            ringClass: 'border-sky-400',
            iconGlow: 'shadow-sky-500/50 text-sky-300',
            icon: <Loader2 className="w-8 h-8 text-sky-300 animate-spin" />,
        },
        error: {
            text: 'AUTHENTICATION REJECTED',
            badgeClass: 'text-rose-300 bg-rose-950/60 border-rose-500/40',
            dotClass: 'bg-rose-400 animate-ping',
            ringClass: 'border-rose-500/80',
            iconGlow: 'shadow-rose-600/40 text-rose-400',
            icon: <ShieldAlert className="w-8 h-8 text-rose-400" />,
        },
    };

    const current = statusConfig[state] || statusConfig.idle;

    return (
        <div className="text-center space-y-3">
            {/* Interactive Hologram Badge Core */}
            <div className="relative inline-flex items-center justify-center p-3">
                {/* Rotating Outer Gyro Ring */}
                <div
                    className={`absolute inset-0 rounded-full border border-dashed transition-all duration-700 ${current.ringClass} ${
                        state === 'processing'
                            ? 'animate-radar-spin-fast'
                            : state === 'username'
                            ? 'animate-radar-spin'
                            : 'animate-reverse-spin'
                    }`}
                />

                {/* Counter-rotating Inner Precision Dials */}
                <div
                    className={`absolute inset-1.5 rounded-full border border-slate-700/50 transition-all duration-500 ${
                        state === 'password' ? 'rotate-45' : 'animate-reverse-spin'
                    }`}
                    style={{ animationDuration: '14s' }}
                />

                {/* Laser Scanline Beam when in Username Scan mode */}
                {state === 'username' && (
                    <div className="absolute inset-0 rounded-2xl overflow-hidden pointer-events-none">
                        <div className="absolute left-0 right-0 h-0.5 bg-gradient-to-r from-transparent via-cyan-400 to-transparent animate-scanline shadow-[0_0_8px_rgba(34,211,238,0.8)]" />
                    </div>
                )}

                {/* Center Hologram Shield Container */}
                <div
                    className={`relative z-10 flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-slate-900 via-slate-800 to-indigo-950 border border-slate-700/70 shadow-2xl transition-all duration-300 ${
                        current.iconGlow
                    } ${state === 'error' ? 'animate-micro-shake' : ''}`}
                >
                    {current.icon}

                    {/* Ambient Center Glow */}
                    <div
                        className={`absolute inset-0 rounded-2xl opacity-20 blur-md transition-colors duration-500 ${
                            state === 'error'
                                ? 'bg-rose-500'
                                : state === 'username'
                                ? 'bg-cyan-500'
                                : state === 'token'
                                ? 'bg-emerald-500'
                                : state === 'peek'
                                ? 'bg-amber-500'
                                : 'bg-indigo-500'
                        }`}
                    />
                </div>
            </div>

            {/* Branding Details */}
            <div className="space-y-1">
                <p className="text-[10.5px] font-bold tracking-widest text-indigo-400 uppercase">
                    {title}
                </p>
                <h1 className="text-xl font-extrabold tracking-tight text-white sm:text-2xl drop-shadow-md">
                    {heading}
                </h1>
                <p className="text-xs text-slate-400 font-medium">
                    {subtitle}
                </p>
            </div>

            {/* Dynamic Status Capsule */}
            <div className="flex justify-center pt-0.5">
                <div
                    className={`inline-flex items-center gap-2 px-3 py-1 rounded-full text-[10px] font-mono font-semibold tracking-wider uppercase border transition-all duration-300 shadow-sm ${current.badgeClass}`}
                >
                    <span className={`w-1.5 h-1.5 rounded-full ${current.dotClass}`} />
                    <span>{current.text}</span>
                </div>
            </div>
        </div>
    );
}
