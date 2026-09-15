import React, { useMemo } from 'react';
import { Check, X } from 'lucide-react';

/**
 * Interactive Password Strength Gauge & Criteria Checklist.
 * Provides animated visual feedback on password complexity.
 */
export default function PasswordStrengthMeter({ password = '' }) {
    const analysis = useMemo(() => {
        if (!password) {
            return {
                score: 0,
                label: 'Password required',
                color: 'bg-slate-700',
                textColor: 'text-slate-500',
                checks: {
                    length: false,
                    uppercase: false,
                    number: false,
                    special: false,
                },
            };
        }

        const checks = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[^A-Za-z0-9]/.test(password),
        };

        const passedCount = Object.values(checks).filter(Boolean).length;

        let score = passedCount;
        let label = 'Weak';
        let color = 'bg-rose-500';
        let textColor = 'text-rose-400';

        if (score === 2) {
            label = 'Fair';
            color = 'bg-amber-500';
            textColor = 'text-amber-400';
        } else if (score === 3) {
            label = 'Good';
            color = 'bg-sky-500';
            textColor = 'text-sky-400';
        } else if (score >= 4) {
            label = 'Strong';
            color = 'bg-emerald-500';
            textColor = 'text-emerald-400';
        }

        return { score, label, color, textColor, checks };
    }, [password]);

    if (!password) return null;

    return (
        <div className="mt-2.5 p-2.5 rounded-xl bg-slate-950/60 border border-slate-800/80 space-y-2 transition-all duration-300">
            {/* 4-segment progress bars */}
            <div className="flex items-center justify-between text-[11px]">
                <span className="text-slate-400 font-medium">Security Strength:</span>
                <span className={`font-semibold font-mono tracking-wider ${analysis.textColor}`}>
                    {analysis.label}
                </span>
            </div>

            <div className="grid grid-cols-4 gap-1.5 h-1.5 w-full">
                {[1, 2, 3, 4].map((step) => {
                    const isActive = analysis.score >= step;
                    return (
                        <div
                            key={step}
                            className={`h-full rounded-full transition-all duration-300 ${
                                isActive ? analysis.color : 'bg-slate-800'
                            }`}
                        />
                    );
                })}
            </div>

            {/* Micro badges for criteria */}
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-1.5 pt-1">
                <RequirementBadge
                    label="8+ Chars"
                    passed={analysis.checks.length}
                />
                <RequirementBadge
                    label="Uppercase"
                    passed={analysis.checks.uppercase}
                />
                <RequirementBadge
                    label="Number"
                    passed={analysis.checks.number}
                />
                <RequirementBadge
                    label="Symbol"
                    passed={analysis.checks.special}
                />
            </div>
        </div>
    );
}

function RequirementBadge({ label, passed }) {
    return (
        <div
            className={`flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-mono transition-colors duration-200 ${
                passed
                    ? 'text-emerald-300 bg-emerald-950/40 border border-emerald-500/30'
                    : 'text-slate-500 bg-slate-900/40 border border-slate-800'
            }`}
        >
            {passed ? (
                <Check className="w-3 h-3 text-emerald-400 flex-shrink-0 animate-bounce" style={{ animationIterationCount: 1 }} />
            ) : (
                <X className="w-3 h-3 text-slate-600 flex-shrink-0" />
            )}
            <span className="truncate">{label}</span>
        </div>
    );
}
