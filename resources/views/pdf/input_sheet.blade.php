@php
    use App\Services\Format\IndianCurrencyFormatter;

    $cagLogoPath = public_path('images/cag_logo.png');
    $cagLogoBase64 = file_exists($cagLogoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($cagLogoPath)) : '';

    $ashokPath = public_path('images/ashok_stambh.png');
    $ashokBase64 = file_exists($ashokPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($ashokPath)) : '';

    $sectionName = $case->section ?: 'Fund Section I';
    $pensionType = $case->pension_type_name ?: ($case->case_type?->label() ?? 'Superannuation');
    $openingFinYear = $run?->opening_fin_year ?: '2023-2024';

    $openingBal = (float)($run?->opening_balance_amount ?? 0);
    $deposits = (float)($run?->total_subscriptions ?? 0) + (float)($run?->excess_deposits ?? 0) + (float)($run?->total_refunds ?? 0);
    $withdrawals = (float)($run?->total_withdrawals ?? 0);
    $interest = (float)($run?->actual_interest_computed ?? 0) + (float)($run?->delayed_interest_computed ?? 0);
    if ($interest == 0 && (float)($run?->total_interest_computed ?? 0) > 0) {
        $interest = (float)$run->total_interest_computed;
    }
    $finalAmount = (float)($run?->final_closing_balance ?? 0);
    $dlisAmount = (float)($run?->dlis_amount ?? 0);
    $isDlis = (bool)($run?->dlis_admissible);

    $missingCredits = $missing_credits_text ?? 'Nil';

    $sig1 = $sig_first ?? 'Dealing Assistant';
    $sig2 = $sig_second ?? 'Asstt. Accounts Officer';
    $sig3 = $sig_third ?? 'Sr. Accounts Officer / Fund';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Sheet - {{ $case->registration_no }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 12mm 15mm;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 13px;
            line-height: 1.45;
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
            margin: 6px 0 12px 0;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 12.5px;
        }
        .section-heading {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 14px 0 6px 0;
            text-decoration: underline;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 12.5px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 5px 8px;
            vertical-align: middle;
        }
        table.data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 50px;
        }
        .sig-table td {
            width: 33.33%;
            text-align: center;
            font-weight: bold;
            padding-top: 40px;
            vertical-align: bottom;
        }
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
                No. {{ $sectionName }} / FP / Input sheet / {{ $case->registration_no }} /
            </td>
            <td align="right">
                Date: <strong>{{ date('d / m / Y') }}</strong>
            </td>
        </tr>
    </table>

    <div class="section-heading">Basic Information</div>
    <table class="data-table">
        <tr>
            <td><span class="font-bold">Name:</span> {{ $case->name_title }} {{ $case->subscriber_name_cache }}</td>
            <td><span class="font-bold">GPF A/c No:</span> {{ $case->formatted_gpf_account }}</td>
            <td><span class="font-bold">Designation:</span> {{ $case->designation_title }} {{ $case->designation }}</td>
        </tr>
        <tr>
            <td><span class="font-bold">Employee Code:</span> {{ $case->employee_code ?: 'N/A' }}</td>
            <td><span class="font-bold">Beneficiary Code:</span> {{ $case->beneficiary_code ?: 'N/A' }}</td>
            <td><span class="font-bold">Mobile No:</span> {{ $case->mobile_no ?: 'N/A' }}</td>
        </tr>
        <tr>
            <td colspan="3"><span class="font-bold">Personal Address:</span> {{ $case->personal_address ?: 'N/A' }}</td>
        </tr>
        <tr>
            <td colspan="3"><span class="font-bold">DDO Code & Address:</span> {{ $case->ddo_code }} - {{ $case->ddo_designation }}</td>
        </tr>
        <tr>
            <td><span class="font-bold">Treasury:</span> {{ $case->treasury_name }} ({{ $case->treasury_code }})</td>
            <td><span class="font-bold">Date of Effect:</span> {{ $case->event_date ? \Carbon\Carbon::parse($case->event_date)->format('d/m/Y') : 'N/A' }}</td>
            <td><span class="font-bold">Last Fund Deduction:</span> {{ $case->last_fund_deduction ? \Carbon\Carbon::parse($case->last_fund_deduction)->format('F, Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <td><span class="font-bold">Interest Allowed Upto:</span> {{ $run?->interest_allowed_upto ? \Carbon\Carbon::parse($run->interest_allowed_upto)->format('F, Y') : 'N/A' }}</td>
            <td><span class="font-bold">DLIS Admissible:</span> {{ $isDlis ? 'Yes' : 'No' }}</td>
            <td><span class="font-bold">Debit During Year:</span> ₹ {{ number_format((float)$case->debit_during_year, 2) }}</td>
        </tr>
        @if($case->spouse_name)
        <tr>
            <td colspan="3"><span class="font-bold">Spouse / Claimant:</span> {{ $case->spouse_name }} ({{ $case->spouse_relation ?: 'Relation' }})</td>
        </tr>
        @endif
        @if($case->lta_to_whom)
        <tr>
            <td><span class="font-bold">LTA Claimant:</span> {{ $case->lta_to_whom }}</td>
            <td colspan="2"><span class="font-bold">Date of Death After Retirement:</span> {{ $case->date_of_lta ? \Carbon\Carbon::parse($case->date_of_lta)->format('d/m/Y') : 'N/A' }}</td>
        </tr>
        @endif
    </table>

    <div class="section-heading">Financial Information & Ledger Verification</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="40%" class="text-center">PARTICULARS</th>
                <th width="25%" class="text-center">AMOUNT (₹)</th>
                <th width="35%" class="text-center">MISSING CREDITS / REMARKS</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="font-bold">OPENING BALANCE (As on 01/04/{{ substr($openingFinYear, 0, 4) }})</td>
                <td class="text-right font-bold">{{ number_format($openingBal, 2) }}</td>
                <td rowspan="5" valign="top" style="font-size: 11.5px; background: #fafafa;">
                    <strong>Uncredited / Missing Credit Months:</strong><br />
                    {{ $missingCredits }}
                    @if($run?->delay_justification)
                        <br /><br />
                        <strong>Delay Justification (Rule 11(4)):</strong><br />
                        {{ $run->delay_justification }}
                    @endif
                </td>
            </tr>
            <tr>
                <td>DEPOSITS (Subscriptions + Refunds + Excess)</td>
                <td class="text-right">{{ number_format($deposits, 2) }}</td>
            </tr>
            <tr>
                <td>INTEREST (Actual + Delayed Interest)</td>
                <td class="text-right">{{ number_format($interest, 2) }}</td>
            </tr>
            <tr>
                <td>WITHDRAWALS / ADVANCES</td>
                <td class="text-right">{{ number_format($withdrawals, 2) }}</td>
            </tr>
            <tr style="background-color: #f2f2f2;">
                <td class="font-bold">FINAL CLOSING BALANCE NET PAYABLE</td>
                <td class="text-right font-bold" style="font-size: 13.5px;">₹ {{ number_format($finalAmount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin: 12px 0; font-size: 13px; line-height: 1.6;">
        <div><strong>FINAL PAYMENT AMOUNT:</strong> ₹ {{ number_format($finalAmount, 2) }} (Rupees {{ IndianCurrencyFormatter::toWords($finalAmount) }})</div>
        @if($isDlis && $dlisAmount > 0)
            <div><strong>DEPOSIT-LINKED INSURANCE SCHEME (DLIS) AMOUNT:</strong> ₹ {{ number_format($dlisAmount, 2) }} (Rupees {{ IndianCurrencyFormatter::toWords($dlisAmount) }})</div>
        @endif
    </div>

    <table class="sig-table">
        <tr>
            <td>
                ____________________________<br />
                {{ $sig1 }}<br />
                <span style="font-size: 11px; font-weight: normal;">(Dealing Assistant / DEO)</span>
            </td>
            <td>
                ____________________________<br />
                {{ $sig2 }}<br />
                <span style="font-size: 11px; font-weight: normal;">(Asstt. Accounts Officer)</span>
            </td>
            <td>
                ____________________________<br />
                {{ $sig3 }}<br />
                <span style="font-size: 11px; font-weight: normal;">(Sr. Accounts Officer)</span>
            </td>
        </tr>
    </table>
</body>
</html>
