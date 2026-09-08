<?php

namespace App\Enums;

enum CaseWorkflowStatus: int
{
    case DRAFT = 1;
    case UNDER_VERIFICATION = 2;
    case PRE_CALCULATED = 3;
    case CALCULATED = 4;
    case CHECKED = 5;
    case APPROVED = 6;
    case AUTHORIZED = 7;
    case HRMS_SYNCED = 8;
    case DISPATCHED = 9;
    case MINUS_BALANCE = 10;
    case CANCELLED = 11;
    case LTA_REGISTERED = 12;
    case LTA_ENTERED = 13;
    case LTA_CHECKED = 14;
    case LTA_APPROVED = 15;
    case LTA_AUTHORIZED = 16;
    case REVERTED = 17;

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Inward Registered (Draft)',
            self::UNDER_VERIFICATION => 'Under Verification (DEO)',
            self::PRE_CALCULATED => 'Pre-Calculated / Broad Sheet Set',
            self::CALCULATED => 'Calculation Generated',
            self::CHECKED => 'Checked by AAO',
            self::APPROVED => 'Approved by Accounts Officer',
            self::AUTHORIZED => 'Digitally Authorized (Signed)',
            self::HRMS_SYNCED => 'Uploaded to State eHRMS',
            self::DISPATCHED => 'Dispatched (Outward)',
            self::MINUS_BALANCE => 'Minus Balance Objection',
            self::CANCELLED => 'Case Cancelled',
            self::LTA_REGISTERED => 'LTA Registered',
            self::LTA_ENTERED => 'LTA Application Entered',
            self::LTA_CHECKED => 'LTA Checked',
            self::LTA_APPROVED => 'LTA Approved',
            self::LTA_AUTHORIZED => 'LTA Digitally Authorized',
            self::REVERTED => 'Reverted for Correction',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::DRAFT, self::LTA_REGISTERED => 'bg-slate-500/10 text-slate-300 border-slate-500/20',
            self::UNDER_VERIFICATION, self::LTA_ENTERED => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
            self::PRE_CALCULATED, self::CALCULATED => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
            self::CHECKED, self::LTA_CHECKED => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
            self::APPROVED, self::LTA_APPROVED => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            self::AUTHORIZED, self::LTA_AUTHORIZED => 'bg-teal-500/10 text-teal-300 border-teal-500/20 shadow-sm shadow-teal-500/10',
            self::HRMS_SYNCED => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
            self::DISPATCHED => 'bg-cyan-500/10 text-cyan-300 border-cyan-500/20',
            self::MINUS_BALANCE => 'bg-red-500/10 text-red-400 border-red-500/20',
            self::CANCELLED => 'bg-rose-900/20 text-rose-400 border-rose-800/30',
            self::REVERTED => 'bg-orange-500/10 text-orange-400 border-orange-500/20',
        };
    }
}
