<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GPF Authority Order - {{ $case->registration_no }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm 15mm 15mm 15mm;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 13px;
            line-height: 1.4;
            color: #111;
            background: #fff;
            margin: 0;
            padding: 20px;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .header-title-hi {
            font-size: 17px;
            font-weight: bold;
            text-align: center;
        }
        .header-title-en {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            letter-spacing: 0.5px;
        }
        .sub-header {
            font-size: 11px;
            text-align: center;
            color: #333;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .meta-table td {
            padding: 3px 0;
            vertical-align: top;
        }
        .content-box {
            border: 1px solid #333;
            padding: 12px;
            margin-bottom: 15px;
        }
        .financial-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .financial-table th, .financial-table td {
            border: 1px solid #444;
            padding: 6px 8px;
            text-align: left;
        }
        .financial-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .financial-table td.amount {
            text-align: right;
            font-family: "Courier New", Courier, monospace;
            font-weight: bold;
        }
        .sig-block {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .dsc-stamp {
            border: 2px dashed #0f766e;
            background: #f0fdf4;
            color: #065f46;
            padding: 10px;
            font-size: 11px;
            border-radius: 4px;
            width: 260px;
        }
        .no-print {
            margin-bottom: 20px;
            padding: 10px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #4338ca; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
            🖨️ Print / Save as PDF
        </button>
    </div>

    <table class="header-table">
        <tr>
            <td width="15%" align="center" style="font-size: 28px;">🏛️</td>
            <td width="70%" align="center">
                <div class="header-title-hi">महालेखाकार का कार्यालय (लेखा एवं हक), त्रिपुरा - अगरतला</div>
                <div class="header-title-en">OFFICE OF THE ACCOUNTANT GENERAL (A & E), TRIPURA ::: AGARTALA</div>
                <div class="sub-header">PO: Kunjaban, Agartala - 799006 | General Provident Fund Final Payment Authority</div>
            </td>
            <td width="15%" align="center" style="font-size: 28px;">🇮🇳</td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td width="55%">
                <strong>Authority No:</strong> {{ $authority->authority_number }}<br>
                <strong>Registration No:</strong> {{ $case->registration_no }}<br>
                <strong>GPF Account No:</strong> <u>{{ $case->formatted_gpf_account }}</u>
            </td>
            <td width="45%" align="right">
                <strong>Date:</strong> {{ $authority->authority_date->format('d/m/Y') }}<br>
                <strong>Case Type:</strong> {{ $case->case_type->label() }}<br>
                <strong>DDO Code:</strong> {{ $case->ddo_code }} ({{ $case->ddo_designation }})
            </td>
        </tr>
    </table>

    <div class="content-box">
        <p>To,<br>
        The Treasury Officer / Sub-Treasury Officer,<br>
        <strong>{{ $case->treasury_name }}</strong>, Tripura.</p>

        <p><strong>Subject: Authority for Final Payment of General Provident Fund balance in respect of {{ $case->name_title }} {{ $case->subscriber_name_cache }}, {{ $case->designation }}.</strong></p>

        <p style="text-indent: 30px; text-align: justify;">
            @if($case->case_type === \App\Enums\CaseType::FAMILY_PENSION || $case->case_type === \App\Enums\CaseType::DEATH_IN_SERVICE || (string)$case->pension_type_id === '2')
                I am to convey the sanction and authority for final withdrawal and payment of accumulated General Provident Fund balance at credit of Late <strong>{{ $case->subscriber_name_cache }}</strong>, holding GPF Account No. <strong>{{ $case->formatted_gpf_account }}</strong>, who demised on <strong>{{ $case->event_date ? $case->event_date->format('d/m/Y') : 'N/A' }}</strong>. The whole certified amount is authorized for disbursement to the eligible family pension beneficiary / legal nominee: <strong>{{ $case->spouse_name ?: 'the legal nominee(s)' }}</strong> ({{ $case->spouse_relation ?? 'Spouse/Legal Heir' }}).
            @else
                I am to convey the sanction and authority for final withdrawal of accumulated General Provident Fund balance at credit of <strong>{{ $case->name_title }} {{ $case->subscriber_name_cache }}</strong>, holding GPF Account No. <strong>{{ $case->formatted_gpf_account }}</strong>, who has retired on <strong>{{ $case->event_date ? $case->event_date->format('d/m/Y') : 'N/A' }}</strong>.
            @endif
        </p>

        <table class="financial-table">
            <thead>
                <tr>
                    <th>Particulars of Settlement Ledger</th>
                    <th style="text-align: right;">Amount (INR)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Opening Balance (Base Year {{ $run?->opening_fin_year ?? '2023-2024' }})</td>
                    <td class="amount">₹ {{ number_format($run?->opening_balance_amount ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>Total Subscriptions & Verified Deposits</td>
                    <td class="amount">₹ {{ number_format($run?->total_subscriptions ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>Excess Deposits (Not eligible for interest)</td>
                    <td class="amount">₹ {{ number_format($run?->excess_deposits ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>Statutory Compound Interest Computed</td>
                    <td class="amount">₹ {{ number_format($run?->total_interest_computed ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>Less: Non-Refundable Withdrawals / Debits during year</td>
                    <td class="amount" style="color: #991b1b;">- ₹ {{ number_format($run?->total_withdrawals ?? 0, 2) }}</td>
                </tr>
                @if($run?->dlis_amount > 0)
                <tr>
                    <td>Deposit Linked Insurance Scheme (DLIS) Admissible Amount</td>
                    <td class="amount">₹ {{ number_format($run->dlis_amount, 2) }}</td>
                </tr>
                @endif
                <tr style="background: #f9fafb; font-size: 14px;">
                    <td><strong>CERTIFIED NET PAYABLE BALANCE</strong></td>
                    <td class="amount" style="font-size: 15px; color: #065f46;">
                        <strong>₹ {{ number_format($authority->net_amount, 2) }}</strong>
                    </td>
                </tr>
            </tbody>
        </table>

        @if($nominees && $nominees->count() > 0)
        <p><strong>Nominee / Shareholder Disbursement Distribution:</strong></p>
        <table class="financial-table">
            <thead>
                <tr>
                    <th>Nominee / Legal Heir</th>
                    <th>Relation</th>
                    <th>Share %</th>
                    <th style="text-align: right;">Allocated Sum (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($nominees as $nominee)
                <tr>
                    <td>{{ $nominee->nominee_name }}</td>
                    <td>{{ $nominee->relationship }}</td>
                    <td>{{ number_format($nominee->share_percentage, 2) }}%</td>
                    <td class="amount">₹ {{ number_format($nominee->allocated_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    <div class="sig-block">
        <div>
            <div style="font-size: 11px; color: #555;">
                Verification Hash (SHA-256):<br>
                <code style="font-size: 9px;">{{ $authority->verification_hash ?? hash('sha256', $authority->authority_number) }}</code>
            </div>
        </div>

        <div>
            @if($authority->is_signed && $authority->digitalSignature)
            <div class="dsc-stamp">
                <strong>✅ DIGITALLY SIGNED</strong><br>
                Signatory: {{ $authority->digitalSignature->signatory_name }}<br>
                Role: {{ $authority->digitalSignature->signatory_role }}<br>
                Time: {{ $authority->signed_at ? $authority->signed_at->format('d/m/Y H:i:s') : now()->format('d/m/Y H:i:s') }}<br>
                Cert Issuer: {{ $authority->digitalSignature->certificate_issuer }}
            </div>
            @else
            <div style="text-align: center; border-top: 1px solid #444; width: 200px; padding-top: 5px; margin-top: 40px;">
                Senior Accounts Officer (Fund)<br>
                O/o Accountant General (A&E), Tripura
            </div>
            @endif
        </div>
    </div>
</body>
</html>
