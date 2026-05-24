<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip - {{ formatMonthYear($payslip->salary_month) }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }
        .container {
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #2563eb;
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 14px;
        }
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .info-box {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 10px;
        }
        .info-box h3 {
            font-size: 12px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .info-row label {
            color: #666;
            font-size: 10px;
        }
        .info-row p {
            font-weight: bold;
            font-size: 11px;
        }
        .table-section {
            margin-bottom: 20px;
        }
        .section-title {
            background: #f3f4f6;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 13px;
            border-left: 4px solid #2563eb;
            margin-bottom: 10px;
        }
        .section-title.earnings {
            border-left-color: #10b981;
        }
        .section-title.deductions {
            border-left-color: #ef4444;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background: #f9fafb;
            font-size: 10px;
            text-transform: uppercase;
            color: #666;
        }
        td {
            font-size: 11px;
        }
        td.amount {
            text-align: right;
            font-family: monospace;
        }
        .total-row {
            background: #f3f4f6;
            font-weight: bold;
        }
        .total-row.earnings {
            background: #d1fae5;
            color: #065f46;
        }
        .total-row.deductions {
            background: #fee2e2;
            color: #991b1b;
        }
        .net-pay-section {
            background: #2563eb;
            color: white;
            padding: 15px 20px;
            margin-top: 20px;
        }
        .net-pay-section table {
            width: 100%;
        }
        .net-pay-section td {
            border: none;
            padding: 5px 0;
            color: white;
        }
        .net-pay-section .label {
            font-size: 14px;
            font-weight: bold;
        }
        .net-pay-section .amount {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            font-family: monospace;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
        .confidential {
            color: #ef4444;
            font-weight: bold;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>PAYSLIP</h1>
            <p>{{ formatMonthYear($payslip->salary_month) }}</p>
        </div>

        <!-- Employee & Pay Period Info -->
        <div class="info-section">
            <div class="info-box">
                <h3>Employee Details</h3>
                <div class="info-row">
                    <label>Name</label>
                    <p>{{ $employee->first_name }} {{ $employee->last_name }}</p>
                </div>
                <div class="info-row">
                    <label>Employee ID</label>
                    <p>{{ $employee->employee_id }}</p>
                </div>
                <div class="info-row">
                    <label>Department</label>
                    <p>{{ $employee->department->name ?? 'N/A' }}</p>
                </div>
                <div class="info-row">
                    <label>Designation</label>
                    <p>{{ $employee->designation->name ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="info-box">
                <h3>Payment Details</h3>
                <div class="info-row">
                    <label>Pay Period</label>
                    <p>{{ formatMonthYear($payslip->salary_month) }}</p>
                </div>
                <div class="info-row">
                    <label>Payment Date</label>
                    <p>{{ formatDate($payslip->created_at) }}</p>
                </div>
                <div class="info-row">
                    <label>ID Number</label>
                    <p>{{ $employee->id_number ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Earnings -->
        <div class="table-section">
            <div class="section-title earnings">EARNINGS</div>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th style="text-align: right;">Amount (R)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Basic Salary</td>
                        <td class="amount">{{ number_format($payslipDetail['basic_salary'], 2) }}</td>
                    </tr>
                    @foreach($payslipDetail['allowances'] as $allowance)
                        <tr>
                            <td>{{ $allowance['title'] }}</td>
                            <td class="amount">{{ number_format($allowance['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                    @foreach($payslipDetail['commissions'] as $commission)
                        <tr>
                            <td>{{ $commission['title'] }}</td>
                            <td class="amount">{{ number_format($commission['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                    @foreach($payslipDetail['other_payments'] as $payment)
                        <tr>
                            <td>{{ $payment['title'] }}</td>
                            <td class="amount">{{ number_format($payment['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                    @foreach($payslipDetail['overtimes'] as $overtime)
                        <tr>
                            <td>{{ $overtime['title'] }} ({{ $overtime['hours'] }} hrs @ R{{ number_format($overtime['rate'], 2) }})</td>
                            <td class="amount">{{ number_format($overtime['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row earnings">
                        <td>Total Earnings</td>
                        <td class="amount">{{ number_format($payslipDetail['total_earnings'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Deductions -->
        <div class="table-section">
            <div class="section-title deductions">DEDUCTIONS</div>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th style="text-align: right;">Amount (R)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(array_merge($payslipDetail['deductions'], $payslipDetail['loans']) as $deduction)
                        <tr>
                            <td>{{ $deduction['title'] }}</td>
                            <td class="amount">{{ number_format($deduction['amount'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="text-align: center; color: #666;">No deductions</td>
                        </tr>
                    @endforelse
                    <tr class="total-row deductions">
                        <td>Total Deductions</td>
                        <td class="amount">{{ number_format($payslipDetail['total_deductions'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Net Pay -->
        <div class="net-pay-section">
            <table>
                <tr>
                    <td class="label">NET PAY</td>
                    <td class="amount">R {{ number_format($payslipDetail['net_pay'], 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p class="confidential">CONFIDENTIAL</p>
            <p>This is a computer-generated document. No signature is required.</p>
            <p>Generated on {{ formatDateTime(now()) }}</p>
        </div>
    </div>
</body>
</html>
