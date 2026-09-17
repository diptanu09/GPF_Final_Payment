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
    $signature = $signature_title ?? 'Accounts Officer / Fund (FP)';
    $memoNo = "No. {$sectionName} / FP / Revalidation / {$pensionType} / {$openingFinYear} / {$case->registration_no} /";

    $matter = $revalidation_matter ?? ("To\nThe Treasury Officer,\n{$case->treasury_name},\nTripura.\n\nSubject: Revalidation of GPF Final Payment Authority.\n\nSir,\n       In inviting a reference to the letter received on the above subject, I am to furnish herewith the GPF Final Payment Authority as was issued in favour of {$case->name_title} {$case->subscriber_name_cache}, {$case->designation_title} {$case->designation}, holder of GPF A/c No. {$case->formatted_gpf_account} amounting to ₹ " . number_format($finalAmount, 2) . "/- (Rupees " . IndianCurrencyFormatter::toWords($finalAmount) . ") vide No. {$sectionName} / FP / {$pensionType} / {$openingFinYear} / {$case->registration_no} / dated " . ($authority?->authority_date ? $authority->authority_date->format('d/m/Y') : date('d/m/Y')) . " after necessary revalidation with a request to arrange for payment through the {$case->treasury_name} at an early date.\n\nEnclo: Revalidated GPF Final Payment Authority in original.");

    $copyTo = $copy_to ?? ("1. {$case->ddo_designation} ({$case->ddo_code}) for information.\n2. The Treasury Officer, {$case->treasury_name} for information and necessary action. He is requested to arrange for payment through the designated sub-treasury.\n3. {$case->name_title} {$case->subscriber_name_cache}, {$case->designation_title} {$case->designation}, {$case->personal_address}.");
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revalidation Order - {{ $case->registration_no }}</title>
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
        .doc-title { text-align: center; font-weight: bold; text-decoration: underline; margin-bottom: 18px; font-size: 15px; letter-spacing: 1px; }
        .content-block { text-align: justify; margin-bottom: 25px; white-space: pre-line; }
        .copy-to-block { margin-top: 30px; font-size: 12.5px; line-height: 1.5; white-space: pre-line; }
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

    <div class="doc-title">REVALIDATION ORDER</div>

    <div class="content-block">
        {{ $matter }}
    </div>

    <table width="100%">
        <tr>
            <td width="50%"></td>
            <td width="50%" align="right">
                Yours faithfully,<br /><br /><br />
                <strong>{{ $signature }}</strong><br />
                <span style="font-size: 11px;">Office of the Accountant General (A&E), Tripura</span>
            </td>
        </tr>
    </table>

    <div class="copy-to-block">
        <hr style="border: 0; border-top: 1px dashed #666; margin: 20px 0 10px 0;" />
        <strong>Copy forwarded for information and necessary action to:</strong><br /><br />
        {{ $copyTo }}
    </div>
</body>
</html>
