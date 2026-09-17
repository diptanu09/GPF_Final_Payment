@php
    $cagLogoPath = public_path('images/cag_logo.png');
    $cagLogoBase64 = file_exists($cagLogoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($cagLogoPath)) : '';

    $ashokPath = public_path('images/ashok_stambh.png');
    $ashokBase64 = file_exists($ashokPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($ashokPath)) : '';

    $sectionName = $case->section ?: 'Fund Section I';
    $pensionType = $case->pension_type_name ?: 'Superannuation';
    $openingFinYear = $run?->opening_fin_year ?: '2023-2024';

    $signature = $signature_title ?? 'Accounts Officer / Fund (FP)';
    $memoNo = "No. {$sectionName} / FP / Objection / {$pensionType} / {$openingFinYear} / {$case->registration_no} /";

    $defects = $objection_points ?? [
        "The application should be submitted in statutory Form 10-A / 10-B / 10-C duly filled in all respects.",
        "Exact Date of Retirement / Demise / Resignation is not authenticated or missing from records.",
        "Attested Death Certificate from Registrar of Births & Deaths not enclosed.",
        "Survival Certificate and Legal Succession Certificate / Nominee affidavit required.",
        "Last Fund Deduction statement showing Subscription, Refund, DA with Treasury Bill No. and Date.",
        "Certificate of Advance / Withdrawal drawn, if any, during the last 12 (twelve) months immediately preceding retirement/demise with Bill No. and Date.",
        "Statement showing monthly G.P.F. Subscription / Refund / DA details for last 12 months.",
        "The application form must be stamped and counter-signed by the Head of Office / Department."
    ];

    $customRemarks = $custom_remarks ?? "In inviting a reference to the GPF Final Payment application submitted in respect of {$case->name_title} {$case->subscriber_name_cache}, {$case->designation_title} {$case->designation}, GPF A/c No. {$case->formatted_gpf_account}, I am to return herewith the original application along with enclosures due to the discrepancies noted below.";
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Objection Memo - {{ $case->registration_no }}</title>
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
        .content-block { text-align: justify; margin-bottom: 18px; }
        .checklist-ol { margin-left: 20px; padding-left: 0; line-height: 1.6; }
        .checklist-ol li { margin-bottom: 8px; }
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

    <div class="doc-title">OBJECTION / DEFECT RETURN MEMORANDUM</div>

    <div style="margin-bottom: 15px;">
        To<br />
        <strong>{{ $case->ddo_designation }}</strong><br />
        DDO Code: <strong>{{ $case->ddo_code }}</strong><br />
        Tripura.
    </div>

    <div style="margin-bottom: 12px;">
        <strong>Subject:</strong> Return of GPF Final Payment Application in respect of <strong>{{ $case->name_title }} {{ $case->subscriber_name_cache }}</strong>, <strong>GPF A/c No. {{ $case->formatted_gpf_account }}</strong>.
    </div>

    <div class="content-block">
        Sir / Madam,<br /><br />
        {{ $customRemarks }}
    </div>

    <div style="margin-bottom: 20px;">
        <strong>Objection / Discrepancy Points:</strong>
        <ol class="checklist-ol">
            @foreach($defects as $defect)
                <li>{{ $defect }}</li>
            @endforeach
        </ol>
    </div>

    <div class="content-block">
        You are therefore requested to resubmit the case after rectifying the above noted defects and furnishing complete documents at an early date for final authorization.
    </div>

    <div style="margin-bottom: 30px;">
        <strong>Enclosure:</strong> Original GPF Final Payment Application Form along with relevant records.
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

    <div style="margin-top: 30px; font-size: 12.5px; border-top: 1px dashed #666; padding-top: 10px;">
        <strong>Copy forwarded for information to:</strong><br />
        1. <strong>{{ $case->name_title }} {{ $case->subscriber_name_cache }}</strong>, {{ $case->designation_title }} {{ $case->designation }}, {{ $case->personal_address ?: 'Address on record' }}.
    </div>
</body>
</html>
