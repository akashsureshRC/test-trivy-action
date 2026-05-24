<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Payslip</title>
    <style>
        @font-face {
            font-family: 'Calibri';
            font-style: normal;
            font-weight: 400;
            src: local('Calibri'), local('Carlito');
        }

        * {
            font-family: 'Calibri', 'DejaVu Sans', sans-serif !important;
        }

        html,
        body,
        table,
        tr,
        td,
        th,
        div,
        span,
        p,
        strong {
            font-family: 'Calibri', 'DejaVu Sans', sans-serif !important;
        }

        body {
            font-family: 'Calibri', 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1f2937;
            line-height: 1.4;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .card {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .card-body {
            padding: 10px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
        }

        .logo {
            max-height: 46px;
            max-width: 180px;
            margin-bottom: 6px;
        }

        .label {
            font-family: 'Calibri', 'DejaVu Sans', sans-serif !important;
            color: #6b7280;
            font-size: 9px;
            margin-bottom: 2px;
        }

        .value {
            font-family: 'Calibri', 'DejaVu Sans', sans-serif !important;
            color: #111827;
            font-weight: 700;
        }

        .right {
            text-align: right;
        }

        .chip {
            display: inline-block;
            margin-top: 5px;
            padding: 3px 8px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            font-size: 9px;
            color: #374151;
        }

        .section {
            margin-bottom: 10px;
        }

        .section td,
        .section th {
            border: 1px solid #e5e7eb;
            padding: 6px;
            text-align: left;
        }

        .amount {
            text-align: right;
        }

        .section-title {
            font-weight: bold;
            background-color: #f3f4f6;
            color: #111827;
        }

        .net td {
            border: 1px solid #d1d5db;
            background: #f9fafb;
            font-size: 14px;
            font-weight: bold;
            color: #111827;
            padding: 10px;
            text-align: right;
        }

        .page-break {
            page-break-before: always;
        }

    </style>
</head>
<body>
    @php
        $i = 0;
    @endphp
    @foreach ( $payslips as $payslip)
    @php
        $i++;
    @endphp
    <div style="min-height: 250mm;">
        <div class="card">
            <div class="card-body">
                <table>
                    <tr>
                        <td width="55%" style="vertical-align: top;">
                            @if(!empty($payslip->data['logo_light_url']))
                                <img src="{{ $payslip->data['logo_light_url'] }}" alt="{{ $payslip->data['company_name'] }}" class="logo">
                            @endif
                            <div class="company-name">{{ $payslip->data['company_name'] }}</div>
                            <div class="label">Official Employee Payslip</div>
                            <div class="chip"><strong>Period: {{ $payslip->data['employee']['period'] }}</strong></div>
                        </td>
                        <td width="45%" class="right" style="vertical-align: top;">
                            <div><strong>SDL:</strong> {{ $payslip->data['sdl_number'] ?: 'N/A' }}</div>
                            <div><strong>PAYE:</strong> {{ $payslip->data['tax_number'] ?: 'N/A' }}</div>
                            <div><strong>UIF:</strong> {{ $payslip->data['uif_number'] ?: 'N/A' }}</div>
                            <div style="margin-top:6px; color:#6b7280;">
                                {{ $payslip->data['company_address'] }}<br>
                                {{ $payslip->data['company_city'] }}, {{ $payslip->data['company_state'] }}<br>
                                {{ $payslip->data['company_country'] }}, {{ $payslip->data['company_zipcode'] }}
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <table>
                    <tr>
                        <td width="33%">
                            <div class="label">Employee</div>
                            <div class="value">{{ $payslip->data['employee']['name'] }}</div>
                        </td>
                        <td width="33%">
                            <div class="label">Employment Date</div>
                            <div class="value">{{ $payslip->data['employee']['employment_date'] }}</div>
                        </td>
                        <td width="34%" class="right">
                            <div class="label">Pay Period</div>
                            <div class="value">{{ $payslip->data['employee']['period'] }}</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <table>
            <tr>
                <td width="50%" style="vertical-align: top; padding-right: 6px;">
                    <table class="section">
                        <tr class="section-title">
                            <td>Income</td>
                            <td class="amount">{{ number_format($payslip->data['income']['total'], 2) }}</td>
                        </tr>
                        @foreach($payslip->data['income']['items'] as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td class="amount">{{ number_format($item['amount'], 2) }}</td>
                        </tr>
                        @endforeach

                        @if(count($payslip->data['benefit']['items']) > 0)
                            <tr class="section-title">
                                <td>Benefit</td>
                                <td class="amount">{{ number_format($payslip->data['benefit']['total'], 2) }}</td>
                            </tr>
                            @foreach($payslip->data['benefit']['items'] as $item)
                            <tr>
                                <td>{{ $item['name'] }}</td>
                                <td class="amount">{{ number_format($item['amount'], 2) }}</td>
                            </tr>
                            @endforeach
                        @endif

                        @if(count($payslip->data['tax_credit']['items']) > 0)
                            <tr class="section-title">
                                <td>Tax Credit</td>
                                <td class="amount">{{ number_format($payslip->data['tax_credit']['total'], 2) }}</td>
                            </tr>
                            @foreach($payslip->data['tax_credit']['items'] as $item)
                                <tr>
                                    <td>{{ $item['name'] }}</td>
                                    <td class="amount">{{ number_format($item['amount'], 2) }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </table>
                </td>

                <td width="50%" style="vertical-align: top; padding-left: 6px;">
                    <table class="section">
                        @if(count($payslip->data['tax_exemption']['items']) > 0)
                        <tr class="section-title">
                            <td>Tax Exemption</td>
                            <td class="amount">{{ number_format($payslip->data['tax_exemption']['total'], 2) }}</td>
                        </tr>
                        @foreach($payslip->data['tax_exemption']['items'] as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td class="amount">{{ number_format($item['amount'], 2) }}</td>
                        </tr>
                        @endforeach
                        @endif
                        @if(count($payslip->data['allowance']['items']) > 0)
                        <tr class="section-title">
                            <td>Allowance</td>
                            <td class="amount">{{ number_format($payslip->data['allowance']['total'], 2) }}</td>
                        </tr>
                        @foreach($payslip->data['allowance']['items'] as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td class="amount">{{ number_format($item['amount'], 2) }}</td>
                        </tr>
                        @endforeach
                        @endif

                        <tr class="section-title">
                            <td>Deduction</td>
                            <td class="amount">{{ number_format($payslip->data['deduction']['total'], 2) }}</td>
                        </tr>
                        @foreach($payslip->data['deduction']['items'] as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td class="amount">{{ number_format($item['amount'], 2) }}</td>
                        </tr>
                        @endforeach

                    </table>
                </td>
            </tr>
        </table>

        <table class="section">
            <tr class="section-title">
                <td>Leave Type</td>
                <td>Balance</td>
                <td>Taken</td>
                <td>Paid</td>
                <td>Unpaid</td>
            </tr>
            @if(is_array($payslip->data['leave']) && count($payslip->data['leave']) > 0)
            @foreach($payslip->data['leave'] as $leaveItem)
            <tr>
                <td>{{ $leaveItem['type'] }}</td>
                <td>{{ $leaveItem['balance'] }}</td>
                <td>{{ $leaveItem['taken'] }}</td>
                <td>{{ $leaveItem['paid_leave'] }}</td>
                <td>{{ $leaveItem['unpaid_leave'] }}</td>
            </tr>
            @endforeach
            @else
            <tr>
                <td colspan="5" style="text-align: center;">No leave data available</td>
            </tr>
            @endif
        </table>

        <table class="net">
            <tr>
                <td>NETT PAY : R {{ number_format($payslip->data['netPay'],2) }}</td>
            </tr>
        </table>
    </div>
    @if ($i < count($payslips))
        <div class="page-break"></div>
    @endif
    @endforeach
</body>
</html>