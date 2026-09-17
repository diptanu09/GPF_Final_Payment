@php
    use App\Services\Format\IndianCurrencyFormatter;

    $cagLogoPath = public_path('images/cag_logo.png');
    $cagLogoBase64 = file_exists($cagLogoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($cagLogoPath)) : '';

    $ashokPath = public_path('images/ashok_stambh.png');
    $ashokBase64 = file_exists($ashokPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($ashokPath)) : '';

    $sectionName = $case->section ?: 'Fund Section I';
    $pensionType = $case->pension_type_name ?: 'Superannuation';
    $openingFinYear = $run?->opening_fin_year ?: '2023-2024';

    $finalAmount = abs((float)($run?->final_closing_balance ?? 0));
    $signature = $signature_title ?? 'Accounts Officer / Fund (FP)';
    $memoNo = "No. {$sectionName} / FP / Minus-Balance / {$pensionType} / {$openingFinYear} / {$case->registration_no} /";
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minus Balance Notice - {{ $case->registration_no }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 20mm 15mm 20mm;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 13.5px;
            line-height: 1.6;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 10px;
        }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        .header-hi { font-size: 18px; font-weight: bold; text-align: center; }
        .header-en { font-size: 13.5px; font-weight: bold; text-align: center; letter-spacing: 0.3px; }
        hr.divider { border: 0; border-top: 1.5px solid #000; margin: 6px 0 14px 0; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 13px; }
        .doc-title { text-align: center; font-weight: bold; text-decoration: underline; margin-bottom: 18px; font-size: 15px; color: #991b1b; letter-spacing: 0.5px; }
        .content-block { text-align: justify; margin-bottom: 14px; }
        .statutory-box { border: 1.5px solid #991b1b; background-color: #fef2f2; padding: 10px 14px; margin: 15px 0; font-size: 13px; line-height: 1.55; }
        .font-bold { font-weight: bold; }
        .print-btn-bar { margin-bottom: 15px; text-align: right; }
        .btn-print { background-color: #1e3a8a; color: #fff; padding: 8px 16px; font-size: 13px; font-weight: bold; border: none; border-radius: 4px; cursor: pointer; }
        @media print { .print-btn-bar { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="print-btn-bar">
        <button class="btn-print" onclick="window.print()">Print / Save PDF</button>
    </div>

    <table class="header-table">
        <tr>
            <td width="12%" align="left" valign="top">
                @if($cagLogoBase64) <img src="{{ $cagLogoBase64 }}" width="60" height="60" alt="CAG Logo" /> @endif
            </td>
            <td width="76%">
                <div class="header-hi">महालेखाकार का कार्यालय (लेखा एवं हक), त्रिपुरा - अगरतला</div>
                <div class="header-en">OFFICE OF THE ACCOUNTANT GENERAL (A & E), TRIPURA ::: AGARTALA</div>
            </td>
            <td width="12%" align="right" valign="top">
                @if($ashokBase64) <img src="{{ $ashokBase64 }}" width="45" height="60" alt="Ashok Stambh" /> @endif
            </td>
        </tr>
    </table>
    <hr class="divider" />

    <table class="meta-table">
        <tr>
            <td align="left" class="font-bold">{{ $memoNo }}</td>
            <td align="right">Date: <strong>{{ date('d / m / Y') }}</strong></td>
        </tr>
    </table>

    <div class="doc-title">NOTICE: MINUS BALANCE / OVERDRAWAL RECOVERY UNDER RULE 11(7)</div>

    <div style="margin-bottom: 14px;">
        To<br />
        <strong>{{ $case->ddo_designation }}</strong><br />
        DDO Code: <strong>{{ $case->ddo_code }}</strong><br />
        Tripura.
    </div>

    <div style="margin-bottom: 12px;">
        <strong>Subject:</strong> Minus Balance / Overdrawal of <strong>₹ {{ number_format($finalAmount, 2) }}/-</strong> in respect of GPF Account No. <strong>{{ $case->formatted_gpf_account }}</strong> ({{ $case->name_title }} {{ $case->subscriber_name_cache }}, {{ $case->designation_title }} {{ $case->designation }}).
    </div>

    <div class="content-block">
        Sir / Madam,<br /><br />
        On scrutinizing the GPF Final Payment calculation in respect of the above-mentioned subscriber, it is revealed that his/her account reflects a <strong>Minus Balance (Overdrawal) of ₹ {{ number_format($finalAmount, 2) }}/- (Rupees {{ IndianCurrencyFormatter::toWords($finalAmount) }})</strong> due to withdrawals sanctioned in excess of the accumulated balance at credit.
    </div>

    <div class="statutory-box">
        <strong>Statutory Position under Rule 11(7) of GPF (Central Services) Rules, 1960:</strong><br />
        In case a subscriber is found to have drawn from the Fund an amount in excess of the amount standing to his/her credit on the date of drawal, the overdrawn amount shall be repaid by him/her forthwith along with penal interest.<br /><br />
        <strong>Mandatory Accounting Heads for Recovery:</strong>
        <ul style="margin: 6px 0 0 20px; padding-left: 0;">
            <li><strong>Principal Overdrawn Amount:</strong> Must be credited through Treasury Challan to Major Head <strong>'8009 - State Provident Funds'</strong>.</li>
            <li><strong>Penal Interest:</strong> Must be credited through separate Challan to Head <strong>'0049 - Interest Receipts, 03 - Other Interest Receipts of State Government, 102 - Interest from State Provident Funds'</strong>.</li>
        </ul>
    </div>

    <div class="content-block">
        This is a serious irregular transaction involving financial loss to the Government exchequer. You are hereby requested to immediately arrange for recovery of the entire overdrawal amount together with statutory interest from the subscriber, or adjust the same against other admissible terminal benefits (e.g. Gratuity / DCRG / Leave Encashment), and deposit the proceeds into Government Account under the designated heads under intimation to this office along with treasury challan particulars.
    </div>

    <table width="100%" style="margin-top: 40px;">
        <tr>
            <td width="50%"></td>
            <td width="50%" align="right">
                Yours faithfully,<br /><br /><br />
                <strong>{{ $signature }}</strong><br />
                <span style="font-size: 11px;">Office of the Accountant General (A&E), Tripura</span>
            </td>
        </tr>
    </table>

    <div style="margin-top: 25px; font-size: 12.5px; border-top: 1px dashed #666; padding-top: 10px;">
        <strong>Copy forwarded for immediate information and compliance to:</strong><br />
        1. <strong>The Accounts Officer / Sr. Accounts Officer (Pension / Gratuity Section):</strong> Requested to withhold the Gratuity / DCRG of {{ $case->name_title }} {{ $case->subscriber_name_cache }} until recovery of ₹ {{ number_format($finalAmount, 2) }}/- plus penal interest is fully effected.<br />
        2. <strong>{{ $case->name_title }} {{ $case->subscriber_name_cache }}</strong>, {{ $case->personal_address ?: 'Address on record' }}.
    </div>
</body>
</html>
