import React from 'react';
import { Shield, ShieldAlert, Lock, Eye, Sparkles, Loader2, CheckCircle2 } from 'lucide-react';

/**
 * Reactive Official Security Seal Avatar.
 * Integrates the official GPF Final Payment sovereign seal emblem with dynamic
 * gyro orbiting rings, real-time focus micro-indicators, laser scanline, and audit status.
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
            text: 'OFFICIAL AUDIT TERMINAL • SECURE',
            badgeClass: 'text-slate-300 bg-slate-900/80 border-slate-700/70',
            dotClass: 'bg-emerald-400',
            ringClass: 'border-indigo-500/30',
            glowColor: 'rgba(99, 102, 241, 0.25)',
            subIcon: null,
        },
        username: {
            text: 'SCANNING OFFICER IDENTITY...',
            badgeClass: 'text-cyan-300 bg-cyan-950/70 border-cyan-500/40',
            dotClass: 'bg-cyan-400 animate-ping',
            ringClass: 'border-cyan-400/70',
            glowColor: 'rgba(6, 182, 212, 0.35)',
            subIcon: <Shield className="w-4 h-4 text-cyan-300" />,
        },
        password: {
            text: 'ENCRYPTED KEYSTORE ARMED',
            badgeClass: 'text-indigo-300 bg-indigo-950/70 border-indigo-500/40',
            dotClass: 'bg-indigo-400',
            ringClass: 'border-indigo-400/80',
            glowColor: 'rgba(99, 102, 241, 0.45)',
            subIcon: <Lock className="w-4 h-4 text-indigo-300" />,
        },
        peek: {
            text: 'PLAINTEXT PREVIEW ACTIVE',
            badgeClass: 'text-amber-300 bg-amber-950/70 border-amber-500/40',
            dotClass: 'bg-amber-400 animate-pulse',
            ringClass: 'border-amber-400/80',
            glowColor: 'rgba(245, 158, 11, 0.45)',
            subIcon: <Eye className="w-4 h-4 text-amber-300" />,
        },
        token: {
            text: 'ADMIN SECURITY TOKEN ENGAGED',
            badgeClass: 'text-emerald-300 bg-emerald-950/70 border-emerald-500/40',
            dotClass: 'bg-emerald-400 animate-pulse',
            ringClass: 'border-emerald-400/80',
            glowColor: 'rgba(16, 185, 129, 0.45)',
            subIcon: <Sparkles className="w-4 h-4 text-emerald-300" />,
        },
        processing: {
            text: 'VERIFYING CREDENTIALS & AUDIT LOGS...',
            badgeClass: 'text-sky-300 bg-sky-950/70 border-sky-500/40',
            dotClass: 'bg-sky-400 animate-spin',
            ringClass: 'border-sky-400',
            glowColor: 'rgba(56, 189, 248, 0.55)',
            subIcon: <Loader2 className="w-4 h-4 text-sky-300 animate-spin" />,
        },
        error: {
            text: 'AUTHENTICATION REJECTED',
            badgeClass: 'text-rose-300 bg-rose-950/70 border-rose-500/40',
            dotClass: 'bg-rose-400 animate-ping',
            ringClass: 'border-rose-500/90',
            glowColor: 'rgba(244, 63, 94, 0.5)',
            subIcon: <ShieldAlert className="w-4 h-4 text-rose-400" />,
        },
    };

    const current = statusConfig[state] || statusConfig.idle;

    return (
        <div className="text-center space-y-3">
            {/* Interactive Sovereign Emblem Badge Core */}
            <div className="relative inline-flex items-center justify-center p-3">
                {/* Rotating Outer Gyro Ring */}
                <div
                    className={`absolute -inset-2 rounded-full border border-dashed transition-all duration-700 pointer-events-none ${current.ringClass} ${
                        state === 'processing'
                            ? 'animate-radar-spin-fast'
                            : state === 'username'
                            ? 'animate-radar-spin'
                            : 'animate-reverse-spin'
                    }`}
                />

                {/* Counter-rotating Inner Precision Dials */}
                <div
                    className={`absolute -inset-0.5 rounded-full border border-slate-700/50 transition-all duration-500 pointer-events-none ${
                        state === 'password' ? 'rotate-45' : 'animate-reverse-spin'
                    }`}
                    style={{ animationDuration: '16s' }}
                />

                {/* Ambient Center Glow behind Seal */}
                <div
                    className="absolute inset-2 rounded-full blur-xl transition-all duration-500 pointer-events-none"
                    style={{ backgroundColor: current.glowColor }}
                />

                {/* Laser Scanline Beam in Username Scan mode */}
                {state === 'username' && (
                    <div className="absolute inset-1 rounded-full overflow-hidden pointer-events-none z-20">
                        <div className="absolute left-0 right-0 h-1 bg-gradient-to-r from-transparent via-cyan-400 to-transparent animate-scanline shadow-[0_0_12px_rgba(34,211,238,0.9)]" />
                    </div>
                )}

                {/* Official GPF Sovereign Seal Emblem */}
                <div
                    className={`relative z-10 transition-transform duration-300 ${
                        state === 'error' ? 'animate-micro-shake' : 'hover:scale-105'
                    }`}
                >
                    <img
                        src="/images/gpf_seal_badge.png"
                        alt="GPF Final Payment System Emblem"
                        className="w-24 h-24 sm:w-28 sm:h-28 rounded-full object-contain drop-shadow-[0_10px_25px_rgba(0,0,0,0.65)]"
                    />

                    {/* Reactive Status Micro-Badge (Bottom Right) */}
                    {current.subIcon && (
                        <div className="absolute -bottom-1 -right-1 p-1.5 rounded-full bg-slate-900 border border-slate-700 shadow-lg shadow-black/60 flex items-center justify-center animate-pulse">
                            {current.subIcon}
                        </div>
                    )}
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
                    className={`inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-[10px] font-mono font-semibold tracking-wider uppercase border transition-all duration-300 shadow-sm ${current.badgeClass}`}
                >
                    <span className={`w-1.5 h-1.5 rounded-full ${current.dotClass}`} />
                    <span>{current.text}</span>
                </div>
            </div>
        </div>
    );
}
