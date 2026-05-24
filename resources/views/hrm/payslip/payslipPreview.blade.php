<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
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
            margin: 0;
            padding: 14px;
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

        .logo {
            max-height: 46px;
            max-width: 180px;
            margin-bottom: 6px;
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

        .right {
            text-align: right;
        }

        .section {
            margin-bottom: 10px;
        }

        .section td,
        .section th {
            border: 1px solid #e5e7eb;
            padding: 6px;
        }

        .section-title {
            background: #f3f4f6;
            color: #111827;
            font-weight: bold;
        }

        .amount {
            text-align: right;
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
    </style>
</head>
<body>

@php
    $records = [];

    if (isset($payslips) && is_iterable($payslips)) {
        foreach ($payslips as $payslipItem) {
            $payload = $payslipItem->preview_payload ?? $payslipItem->data ?? null;

            if (is_array($payload)) {
                $records[] = $payload;
            } elseif (is_object($payload)) {
                $records[] = (array) $payload;
            } elseif (is_string($payload) && $payload !== '') {
                $decoded = json_decode($payload, true);
                if (is_array($decoded)) {
                    $records[] = $decoded;
                }
            }
        }
    }

    if (empty($records)) {
        $records[] = [
            'employee' => $employee ?? [],
            'income' => $income ?? ['total' => 0, 'items' => []],
            'allowance' => $allowance ?? ['total' => 0, 'items' => []],
            'deduction' => $deduction ?? ['total' => 0, 'items' => []],
            'benefit' => $benefit ?? ['total' => 0, 'items' => []],
            'tax_exemption' => $tax_exemption ?? ['total' => 0, 'items' => []],
            'tax_credit' => $tax_credit ?? ['total' => 0, 'items' => []],
            'leave' => $leave ?? [],
            'netPay' => $netPay ?? 0,
            'company_name' => $company_name ?? '',
            'sdl_number' => $sdl_number ?? '',
            'tax_number' => $tax_number ?? '',
            'uif_number' => $uif_number ?? '',
            'logo_light_url' => $logo_light_url ?? null,
            'company_address' => $company_address ?? '',
            'company_city' => $company_city ?? '',
            'company_state' => $company_state ?? '',
            'company_country' => $company_country ?? '',
            'company_zipcode' => $company_zipcode ?? '',
        ];
    }
@endphp

@foreach($records as $payslipData)

<div class="card">
    <div class="card-body">
        <table>
            <tr>
                <td width="55%" style="vertical-align: top;">
                    @if(!empty($payslipData['logo_light_url']))
                        <img src="{{ $payslipData['logo_light_url'] }}" alt="{{ $payslipData['company_name'] ?? '' }}" class="logo">
                    @endif
                    <div class="company-name">{{ $payslipData['company_name'] ?? '' }}</div>
                    <div class="label">Official Employee Payslip</div>
                    @if(!empty($payslipData['tax_year_label']))
                    <div class="label" style="margin-top: 2px;">{{ $payslipData['tax_year_label'] }}</div>
                    @endif
                    <div class="chip"><strong>Period: {{ $payslipData['employee']['period'] ?? '' }}</strong></div>
                </td>
                <td width="45%" class="right" style="vertical-align: top;">
                    <div><strong>SDL:</strong> {{ !empty($payslipData['sdl_number']) ? $payslipData['sdl_number'] : 'N/A' }}</div>
                    <div><strong>PAYE:</strong> {{ !empty($payslipData['tax_number']) ? $payslipData['tax_number'] : 'N/A' }}</div>
                    <div><strong>UIF:</strong> {{ !empty($payslipData['uif_number']) ? $payslipData['uif_number'] : 'N/A' }}</div>
                    <div style="margin-top:6px; color:#6b7280;">
                        {{ $payslipData['company_address'] ?? '' }}<br>
                        {{ $payslipData['company_city'] ?? '' }}, {{ $payslipData['company_state'] ?? '' }}<br>
                        {{ $payslipData['company_country'] ?? '' }}, {{ $payslipData['company_zipcode'] ?? '' }}
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
                    <div class="value">{{ $payslipData['employee']['name'] ?? '' }}</div>
                </td>
                <td width="33%">
                    <div class="label">Employment Date</div>
                    <div class="value">{{ $payslipData['employee']['employment_date'] ?? '' }}</div>
                </td>
                <td width="34%" class="right">
                    <div class="label">Pay Period</div>
                    <div class="value">{{ $payslipData['employee']['period'] ?? '' }}</div>
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
                    <td class="amount">{{ number_format((float) ($payslipData['income']['total'] ?? 0), 2) }}</td>
                </tr>
                @foreach(($payslipData['income']['items'] ?? []) as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td class="amount">{{ number_format((float) ($item['amount'] ?? 0), 2) }}</td>
                </tr>
                @endforeach

                @if(count($payslipData['benefit']['items'] ?? []) > 0)
                <tr class="section-title">
                    <td>Benefit</td>
                    <td class="amount">{{ number_format((float) ($payslipData['benefit']['total'] ?? 0), 2) }}</td>
                </tr>
                @foreach(($payslipData['benefit']['items'] ?? []) as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td class="amount">{{ number_format((float) ($item['amount'] ?? 0), 2) }}</td>
                </tr>
                @endforeach
                @endif

                @if(count($payslipData['tax_credit']['items'] ?? []) > 0)
                <tr class="section-title">
                    <td>Tax Credit</td>
                    <td class="amount">{{ number_format((float) ($payslipData['tax_credit']['total'] ?? 0), 2) }}</td>
                </tr>
                @foreach(($payslipData['tax_credit']['items'] ?? []) as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td class="amount">{{ number_format((float) ($item['amount'] ?? 0), 2) }}</td>
                </tr>
                @endforeach
                @endif
            </table>
        </td>

        <td width="50%" style="vertical-align: top; padding-left: 6px;">
            <table class="section">
                @if(count($payslipData['tax_exemption']['items'] ?? []) > 0)
                <tr class="section-title">
                    <td>Tax Exemption</td>
                    <td class="amount">{{ number_format((float) ($payslipData['tax_exemption']['total'] ?? 0), 2) }}</td>
                </tr>
                @foreach(($payslipData['tax_exemption']['items'] ?? []) as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td class="amount">{{ number_format((float) ($item['amount'] ?? 0), 2) }}</td>
                </tr>
                @endforeach
                @endif

                @if(count($payslipData['allowance']['items'] ?? []) > 0)
                <tr class="section-title">
                    <td>Allowance</td>
                    <td class="amount">{{ number_format((float) ($payslipData['allowance']['total'] ?? 0), 2) }}</td>
                </tr>
                @foreach(($payslipData['allowance']['items'] ?? []) as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td class="amount">{{ number_format((float) ($item['amount'] ?? 0), 2) }}</td>
                </tr>
                @endforeach
                @endif

                <tr class="section-title">
                    <td>Deduction</td>
                    <td class="amount">{{ number_format((float) ($payslipData['deduction']['total'] ?? 0), 2) }}</td>
                </tr>
                @foreach(($payslipData['deduction']['items'] ?? []) as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td class="amount">{{ number_format((float) ($item['amount'] ?? 0), 2) }}</td>
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
    @if(is_array($payslipData['leave'] ?? null) && count($payslipData['leave']) > 0)
    @foreach($payslipData['leave'] as $leaveItem)
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
        <td>NETT PAY : R {{ number_format((float) ($payslipData['netPay'] ?? 0), 2) }}</td>
    </tr>
</table>

@if(!$loop->last)
    <div style="page-break-before: always;"></div>
@endif

@endforeach

</body>
</html>
