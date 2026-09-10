@php
    use App\Services\Format\IndianCurrencyFormatter;

    $cagLogoPath = public_path('images/cag_logo.png');
    $cagLogoBase64 = file_exists($cagLogoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($cagLogoPath)) : '';

    $ashokPath = public_path('images/ashok_stambh.png');
    $ashokBase64 = file_exists($ashokPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($ashokPath)) : '';

    $sectionName = $case->section ?: 'FUND-I';
    $pensionTypeCode = 'LTA';
    $openingFinYear = $run?->opening_fin_year ?: '2023-2024';
    $isRevised = str_contains($authority->authority_number, 'Revised');

    $openingBal = (float)($run?->opening_balance_amount ?? 0);
    $subscriptions = (float)($run?->total_subscriptions ?? 0) + (float)($run?->excess_deposits ?? 0) + (float)($run?->total_refunds ?? 0);
    $withdrawals = (float)($run?->total_withdrawals ?? 0);
    $interest = (float)($run?->actual_interest_computed ?? 0) + (float)($run?->delayed_interest_computed ?? 0);
    if ($interest == 0 && (float)($run?->total_interest_computed ?? 0) > 0) {
        $interest = (float)$run->total_interest_computed;
    }
    $finalAmount = (float)($authority->net_amount ?? $run?->final_closing_balance ?? 0);
    $amountInWords = IndianCurrencyFormatter::toWords($finalAmount);

    $interestAllowedDate = $run?->interest_allowed_upto ? \Carbon\Carbon::parse($run->interest_allowed_upto) : ($case->event_date ? \Carbon\Carbon::parse($case->event_date) : now());
    $interestMonthYear = $interestAllowedDate->format('F, Y');
    $payableOnOrAfter = $interestAllowedDate->format('d-M-Y');
    $approvedDateFormatted = $authority->authority_date ? $authority->authority_date->format('d / m / Y') : now()->format('d / m / Y');

    $missingCredits = $missing_credits_text ?? 'nil';

    $subscriberTitle = $case->name_title ?: 'Sri';
    $desgTitle = $case->designation_title ?: '';
    $subscriberFullName = trim($subscriberTitle . ' ' . $case->subscriber_name_cache);
    $ltaClaimant = $case->lta_to_whom ?: ($case->spouse_name ? $case->spouse_name . ', ' . ($case->spouse_relation ?: 'Spouse') : $subscriberFullName);

    $memoNo = "No. {$sectionName} / LTA / " . ($isRevised ? 'Revised / ' : '') . "{$pensionTypeCode} / {$openingFinYear} / {$case->registration_no} /";
    
    // QR Code data payload
    $qrData = "Office of the Accountant General (A&E), Tripura. Regd No: {$case->registration_no}. Approval Date: " . ($authority->authority_date ? $authority->authority_date->format('d/m/Y') : date('d/m/Y')) . ". Final Payment Amount: Rs. " . number_format($finalAmount, 2, '.', '');
    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=" . urlencode($qrData);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LTA Authority Report - {{ $case->registration_no }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 12mm 15mm;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 13.5px;
            line-height: 1.55;
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
            line-height: 1.2;
        }
        .header-en {
            font-size: 13.5px;
            font-weight: bold;
            text-align: center;
            letter-spacing: 0.3px;
            line-height: 1.2;
        }
        hr.divider {
            border: 0;
            border-top: 1.5px solid #000;
            margin: 6px 0 10px 0;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 13px;
        }
        .meta-table td {
            vertical-align: top;
        }
        .doc-title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }
        .body-text {
            text-align: justify;
            line-height: 1.7;
            font-size: 13px;
        }
        .calc-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            text-align: center;
            font-size: 12.5px;
        }
        .calc-table th, .calc-table td {
            border: 1px solid #000;
            padding: 6px 4px;
        }
        .calc-table th {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .signatory-right {
            text-align: right;
            font-weight: bold;
            margin: 15px 0 10px 0;
        }
        .copy-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12.5px;
        }
        .copy-table td {
            vertical-align: top;
        }
        .dsc-box {
            border: 1.5px dashed #0f766e;
            background: #f0fdf4;
            color: #065f46;
            padding: 8px 10px;
            font-size: 11px;
            border-radius: 4px;
            display: inline-block;
            text-align: left;
            margin-top: 6px;
        }
        .no-print-bar {
            margin-bottom: 15px;
            padding: 10px 15px;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        @media print {
            .no-print-bar {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="no-print-bar">
        <div style="font-size: 12px; font-family: sans-serif; color: #475569;">
            <strong>Lifetime Arrears (LTA) Authority Letter</strong> &bull; Registration: <code>{{ $case->registration_no }}</code>
        </div>
        <div>
            <button onclick="window.print()" style="padding: 7px 18px; background: #4f46e5; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 12px; font-family: sans-serif;">
                🖨️ Print / Save as PDF
            </button>
        </div>
    </div>

    <!-- Header with CAG Logo & Ashok Stambh -->
    <table class="header-table">
        <tr>
            <td width="10%" align="left" valign="middle">
                @if($cagLogoBase64)
                    <img src="{{ $cagLogoBase64 }}" width="64" height="64" alt="CAG Logo" />
                @else
                    <img src="{{ asset('images/cag_logo.png') }}" width="64" height="64" alt="CAG Logo" />
                @endif
            </td>
            <td width="80%" align="center" valign="middle">
                <div class="header-hi">महालेखाकार का कार्यालय (लेखा एवं हक), त्रिपुरा - अगरतला</div>
                <div class="header-en">OFFICE OF THE ACCOUNTANT GENERAL (A & E), TRIPURA ::: AGARTALA</div>
            </td>
            <td width="10%" align="right" valign="middle">
                @if($ashokBase64)
                    <img src="{{ $ashokBase64 }}" width="44" height="64" alt="Ashok Stambh" />
                @else
                    <img src="{{ asset('images/ashok_stambh.png') }}" width="44" height="64" alt="Ashok Stambh" />
                @endif
            </td>
        </tr>
    </table>
    <hr class="divider">

    <!-- Memo Reference & Date -->
    <table class="meta-table">
        <tr>
            <td style="width: 65%; text-align: left; font-weight: 500;">
                {{ $memoNo }}
            </td>
            <td style="width: 35%; text-align: right; font-weight: 500;">
                Date : {{ $approvedDateFormatted }}
            </td>
        </tr>
    </table>

    <div class="doc-title">Authorization letter</div>

    <div class="body-text">
        In terms of Rule 31 / 32 / 33 of Central GPF Rule 1960 ( As adopted by the state ) / Rule 28 of All India Service GPF Rules 1955, as applicable, the authorization for payment of <strong>₹ {{ number_format($finalAmount, 2, '.', '') }}/- (Rupees {{ $amountInWords }})</strong> only is hereby accorded towards final withdrawal from GPF account of <strong>{{ $subscriberFullName }}</strong>, <strong>{{ $desgTitle }} {{ $case->designation }}</strong>, <strong>Account no. {{ $case->formatted_gpf_account }}</strong>@if($case->employee_code && $case->employee_code !== '---'), Employee code: {{ $case->employee_code }}@endif @if($case->beneficiary_code && $case->beneficiary_code !== '---'), Beneficiary code: {{ $case->beneficiary_code }}@endif with interest calculated upto <strong>{{ $interestMonthYear }}</strong>.<br>

        2. Authority for the payment of the residual balance, if any, will be issued as soon as credit(s) for <strong>{{ $missingCredits }}</strong> is/are traced and adjusted in his/her ledger account.<br>

        3. The payment is debitable to the head of account <strong>8009-01-101</strong> (for Government of Tripura employees) and <strong>8009-01-104</strong> (for All India Service Officers).<br>

        4. The payable sum has been worked out as follows :<br>

        <!-- 5-Column Financial Working Table -->
        <table class="calc-table">
            <thead>
                <tr>
                    <th width="24%">O.B. as at the begining of the year<br>( Rs )</th>
                    <th width="24%">Subscription / Refund during the year<br>( Rs )</th>
                    <th width="16%">Withdrawal / Advance<br>( Rs )</th>
                    <th width="16%">Interest<br>( Rs )</th>
                    <th width="20%">Closing balance<br>( Rs )</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ number_format($openingBal, 2, '.', '') }}</td>
                    <td>{{ number_format($subscriptions, 2, '.', '') }}</td>
                    <td>{{ number_format($withdrawals, 2, '.', '') }}</td>
                    <td>{{ number_format($interest, 2, '.', '') }}</td>
                    <td><strong>{{ number_format($finalAmount, 2, '.', '') }}</strong></td>
                </tr>
            </tbody>
        </table>

        <strong>5. Payment is subject to adjustment of any payment made by the DDO between the date of forwarding the application to the date of actual payment.</strong><br>

        6. The whole amount may be paid to <strong>{{ $ltaClaimant }}</strong> of <strong>{{ $subscriberFullName }}</strong>.<br>

        7. The amount is payable on or after <strong>{{ $payableOnOrAfter }}</strong> only and authorization is valid for six months from the date of issue.<br>

        <div class="signatory-right">
            Authorized Signatory
        </div>

        <!-- Copy Forwarded Block with QR Code -->
        <table class="copy-table">
            <tr>
                <td width="78%">
                    <strong>Copy forwarded for information and necessary action to :-</strong><br><br>
                    1. <strong>Treasury Officer</strong> - {{ $case->treasury_name }}@if($case->sub_treasury_name && $case->sub_treasury_name !== $case->treasury_name), payable to {{ $case->sub_treasury_name }}@endif ({{ $case->treasury_code }}).<br><br>
                    2. <strong>{{ $case->ddo_designation }}</strong> ({{ $case->ddo_code }}).<br><br>
                    3. <strong>{{ $ltaClaimant }}</strong> of <strong>{{ $subscriberFullName }}</strong>, {{ $desgTitle }} {{ $case->designation }}, {{ $case->personal_address }}.<br>
                    @if($case->mobile_no)
                        Mobile: {{ $case->mobile_no }}
                    @endif
                </td>
                <td width="22%" align="right" valign="top">
                    <img src="{{ $qrCodeUrl }}" width="100" height="100" style="border: 1px solid #ccc; padding: 2px;" alt="QR Code" /><br>
                    <span style="font-size: 9px; color: #555;">Scan to Verify</span>
                </td>
            </tr>
        </table>

        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 15px;">
            <div>
                @if($authority->is_signed && $authority->digitalSignature)
                <div class="dsc-box">
                    <strong>✅ DIGITALLY SIGNED</strong><br>
                    Signatory: {{ $authority->digitalSignature->signatory_name }}<br>
                    Role: {{ $authority->digitalSignature->signatory_role }}<br>
                    Time: {{ $authority->signed_at ? $authority->signed_at->format('d/m/Y H:i:s') : now()->format('d/m/Y H:i:s') }}<br>
                    Serial: {{ $authority->digitalSignature->certificate_serial }}
                </div>
                @endif
            </div>
            <div class="signatory-right" style="margin: 0;">
                Authorized Signatory
            </div>
        </div>
    </div>
</body>
</html>
