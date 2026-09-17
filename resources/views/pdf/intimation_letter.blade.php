@php
    use App\Services\Format\IndianCurrencyFormatter;

    $cagLogoPath = public_path('images/cag_logo.png');
    $cagLogoBase64 = file_exists($cagLogoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($cagLogoPath)) : '';

    $ashokPath = public_path('images/ashok_stambh.png');
    $ashokBase64 = file_exists($ashokPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($ashokPath)) : '';

    $sectionName = $case->section ?: 'Fund Section I';
    $pensionType = $case->pension_type_name ?: 'Superannuation';
    $openingFinYear = $run?->opening_fin_year ?: '2023-2024';

    $finalAmount = (float)($authority?->net_amount ?? $run?->final_closing_balance ?? 0);
    $amountInWords = IndianCurrencyFormatter::toWords($finalAmount);
    $approvedDate = $authority?->authority_date ? $authority->authority_date->format('d/m/Y') : now()->format('d/m/Y');

    $missingCredits = $missing_credits_text ?? 'Nil';
    $signature = $signature_title ?? 'Accounts Officer / Fund (FP)';

    $memoNo = "No. {$sectionName} / FP / Intimation / {$pensionType} / {$openingFinYear} / {$case->registration_no} /";

    $qrData = "AG Tripura Intimation: Regd No: {$case->registration_no}. A/c: {$case->formatted_gpf_account}. Amount: Rs. " . number_format($finalAmount, 2, '.', '') . ". Dated: {$approvedDate}";
    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($qrData);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intimation Letter - {{ $case->registration_no }}</title>
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
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .header-hi {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
        }
        .header-en {
            font-size: 13.5px;
            font-weight: bold;
            text-align: center;
            letter-spacing: 0.3px;
        }
        hr.divider {
            border: 0;
            border-top: 1.5px solid #000;
            margin: 6px 0 14px 0;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 13px;
        }
        .annexure-title {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 4px;
            font-size: 14px;
        }
        .doc-title {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 18px;
            font-size: 14px;
        }
        .content-block {
            text-align: justify;
            margin-bottom: 15px;
        }
        .font-bold { font-weight: bold; }
        .print-btn-bar {
            margin-bottom: 15px;
            text-align: right;
        }
        .btn-print {
            background-color: #1e3a8a;
            color: #fff;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: bold;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        @media print {
            .print-btn-bar { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="print-btn-bar">
        <button class="btn-print" onclick="window.print()">Print / Save PDF</button>
    </div>

    <table class="header-table">
        <tr>
            <td width="12%" align="left" valign="top">
                @if($cagLogoBase64)
                    <img src="{{ $cagLogoBase64 }}" width="60" height="60" alt="CAG Logo" />
                @endif
            </td>
            <td width="76%">
                <div class="header-hi">महालेखाकार का कार्यालय (लेखा एवं हक), त्रिपुरा - अगरतला</div>
                <div class="header-en">OFFICE OF THE ACCOUNTANT GENERAL (A & E), TRIPURA ::: AGARTALA</div>
            </td>
            <td width="12%" align="right" valign="top">
                @if($ashokBase64)
                    <img src="{{ $ashokBase64 }}" width="45" height="60" alt="Ashok Stambh" />
                @endif
            </td>
        </tr>
    </table>
    <hr class="divider" />

    <table class="meta-table">
        <tr>
            <td align="left" class="font-bold">
                {{ $memoNo }}
            </td>
            <td align="right">
                Date: <strong>{{ date('d / m / Y') }}</strong>
            </td>
        </tr>
    </table>

    <div class="annexure-title">Annexure - 5.24</div>
    <div class="doc-title">Intimation to Subscriber on Issue of Authorization</div>

    <div style="margin-bottom: 14px;">
        To<br />
        <strong>{{ $case->name_title }} {{ $case->subscriber_name_cache }}</strong>,<br />
        {{ $case->designation_title }} {{ $case->designation }}<br />
        {{ $case->personal_address ?: 'Address on record' }}
    </div>

    <div style="margin-bottom: 12px;">
        <strong>Subject:</strong> Final Payment in the accumulation in the General Provident Fund Account of <strong>{{ $case->name_title }} {{ $case->subscriber_name_cache }}</strong>, <strong>GPF Account No. {{ $case->formatted_gpf_account }}</strong>.
    </div>

    <div style="margin-bottom: 14px;">
        <table width="100%">
            <tr>
                <td><strong>Ref / Diary No.:</strong> {{ $case->diary_number ?: $case->registration_no }}</td>
                <td><strong>Dated:</strong> {{ $case->diary_date ? \Carbon\Carbon::parse($case->diary_date)->format('d/m/Y') : date('d/m/Y') }}</td>
            </tr>
        </table>
    </div>

    <div class="content-block">
        Sir / Madam,<br /><br />
        1. Necessary authorization for the payment of <strong>₹ {{ number_format($finalAmount, 2) }}/- (Rupees {{ $amountInWords }})</strong> has been issued from this office vide letter No. <strong>{{ $memoNo }}</strong> dated <strong>{{ $approvedDate }}</strong>. The authorization is current for <strong>six (6) months</strong> from the date of its issue. Please contact the drawing and disbursing officer concerned and obtain the payment within the period of currency of the authorization.
    </div>

    <div class="content-block">
        2. The following credits have not been authorized at present and an authorization for payment of the residual balance will be issued in due course on receipt of particulars of missing credits:<br /><br />
        <div style="padding-left: 20px; font-weight: bold; background: #f8fafc; padding: 6px 12px; border: 1px solid #e2e8f0;">
            {{ $missingCredits }}
        </div><br />
        Please furnish the details of deductions / credits for the above months within <strong>90 days</strong> through the Departmental Officer / DDO ({{ $case->ddo_code }} - {{ $case->ddo_designation }}) to enable this office to trace the missing credits and issue authorization for the residual balance, if any due.
    </div>

    <div class="content-block">
        3. A certificate of non-drawal of withdrawal/advance after <strong>{{ $case->event_date ? \Carbon\Carbon::parse($case->event_date)->format('d/m/Y') : date('d/m/Y') }}</strong> must be attached to the bill at the time of presentation at the Treasury ({{ $case->treasury_name }}).
    </div>

    <table width="100%" style="margin-top: 40px;">
        <tr>
            <td width="60%" valign="bottom">
                <img src="{{ $qrCodeUrl }}" width="85" height="85" alt="Verification QR" style="border: 1px solid #ddd; padding: 2px;" /><br />
                <span style="font-size: 10px; color: #555;">Scan to verify AG Tripura docket status</span>
            </td>
            <td width="40%" align="right" valign="bottom">
                Yours faithfully,<br /><br /><br />
                <strong>{{ $signature }}</strong><br />
                <span style="font-size: 11px;">Office of the Accountant General (A&E), Tripura</span>
            </td>
        </tr>
    </table>
</body>
</html>
