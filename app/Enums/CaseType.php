<?php

namespace App\Enums;

enum CaseType: string
{
    case NORMAL_SUPERANNUATION = 'F';
    case DEATH_IN_SERVICE = 'D';
    case RESIGNATION = 'R';
    case LTA_SPECIAL = 'L';
    case BALANCE_TRANSFER = 'B';
    case CORRESPONDENCE = 'C';

    public function label(): string
    {
        return match ($this) {
            self::NORMAL_SUPERANNUATION => 'Normal Superannuation / Retirement',
            self::DEATH_IN_SERVICE => 'Death in Service (DLIS Applicable)',
            self::RESIGNATION => 'Resignation / Discharge',
            self::LTA_SPECIAL => 'Lifetime Arrears (LTA)',
            self::BALANCE_TRANSFER => 'Balance Transfer to Other Office',
            self::CORRESPONDENCE => 'Correspondence / Query',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::NORMAL_SUPERANNUATION => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            self::DEATH_IN_SERVICE => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
            self::RESIGNATION => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
            self::LTA_SPECIAL => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
            self::BALANCE_TRANSFER => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
            self::CORRESPONDENCE => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
        };
    }
}
