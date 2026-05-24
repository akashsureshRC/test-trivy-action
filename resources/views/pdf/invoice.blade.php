<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }
        .container {
            padding: 30px;
        }
        
        /* Header */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .header-left, .header-right {
            display: table-cell;
            vertical-align: top;
        }
        .header-left {
            width: 60%;
        }
        .header-right {
            width: 40%;
            text-align: right;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #3956ca;
            margin-bottom: 5px;
        }
        .company-details {
            font-size: 10px;
            color: #666;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #3956ca;
            margin-bottom: 10px;
        }
        .invoice-number {
            font-size: 14px;
            color: #666;
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
        }
        .status-paid {
            background: #d1fae5;
            color: #065f46;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .status-overdue {
            background: #fee2e2;
            color: #991b1b;
        }
        
        /* Info Boxes */
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .info-box {
            display: table-cell;
            width: 33.33%;
            vertical-align: top;
            padding-right: 20px;
        }
        .info-box:last-child {
            padding-right: 0;
        }
        .info-box-title {
            font-size: 10px;
            text-transform: uppercase;
            color: #999;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .info-box-content {
            font-size: 11px;
        }
        .info-box-content strong {
            font-size: 12px;
        }
        
        /* Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background: #3956ca;
            color: white;
            padding: 12px 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
        }
        .items-table th:last-child {
            text-align: right;
        }
        .items-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #eee;
            font-size: 11px;
        }
        .items-table td:last-child {
            text-align: right;
        }
        .items-table .text-right {
            text-align: right;
        }
        .items-table .text-center {
            text-align: center;
        }
        
        /* Totals */
        .totals-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .totals-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }
        .totals-right {
            display: table-cell;
            width: 40%;
            vertical-align: top;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 8px 10px;
            font-size: 11px;
        }
        .totals-table .label {
            text-align: right;
            color: #666;
        }
        .totals-table .value {
            text-align: right;
            width: 120px;
        }
        .totals-table .total-row td {
            font-size: 14px;
            font-weight: bold;
            border-top: 2px solid #3956ca;
            padding-top: 12px;
        }
        .totals-table .total-row .value {
            color: #3956ca;
        }
        
        /* Bank Details */
        .bank-details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            font-size: 10px;
        }
        .bank-details-title {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        .bank-details table {
            width: 100%;
        }
        .bank-details td {
            padding: 3px 0;
        }
        .bank-details .label {
            color: #666;
            width: 40%;
        }
        
        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
        
        /* Notes */
        .notes {
            background: #fff8e6;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 10px;
        }
        .notes-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="company-name">{{ $company['name'] }}</div>
                <div class="company-details">
                    @if($company['address']){{ $company['address'] }}<br>@endif
                    @if($company['phone'])Tel: {{ $company['phone'] }}<br>@endif
                    @if($company['email'])Email: {{ $company['email'] }}<br>@endif
                    @if($company['vat_number'])VAT No: {{ $company['vat_number'] }}<br>@endif
                    @if($company['registration_number'])Reg No: {{ $company['registration_number'] }}@endif
                </div>
            </div>
            <div class="header-right">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number">#{{ $invoice->invoice_number }}</div>
            </div>
        </div>
        
        <!-- Info Section -->
        <div class="info-section">
            <div class="info-box">
                <div class="info-box-title">Bill To</div>
                <div class="info-box-content">
                    <strong>{{ $user->name ?? 'N/A' }}</strong><br>
                    {{ $user->email ?? '' }}<br>
                    @if($user->phone ?? null){{ $user->phone }}<br>@endif
                    @if($user->address ?? null){{ $user->address }}@endif
                </div>
            </div>
            <div class="info-box">
                <div class="info-box-title">Billing Period</div>
                <div class="info-box-content">
                    @if($billingCycle)
                        <strong>{{ formatDate($billingCycle->period_start) }}</strong>
                        to
                        <strong>{{ formatDate($billingCycle->period_end) }}</strong>
                    @elseif($invoice->period_start && $invoice->period_end)
                        <strong>{{ formatDate($invoice->period_start) }}</strong>
                        to
                        <strong>{{ formatDate($invoice->period_end) }}</strong>
                    @else
                        {{ $invoice->created_at ? formatMonthYear($invoice->created_at) : 'N/A' }}
                    @endif
                </div>
            </div>
            <div class="info-box">
                <div class="info-box-title">Invoice Details</div>
                <div class="info-box-content">
                    <strong>Issue Date:</strong> {{ $invoice->issue_date ? formatDate($invoice->issue_date) : ($invoice->created_at ? formatDate($invoice->created_at) : 'N/A') }}<br>
                    <strong>Due Date:</strong> {{ $invoice->due_date ? formatDate($invoice->due_date) : 'Upon Receipt' }}<br>
                </div>
            </div>
        </div>
        
        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Description</th>
                    <th class="text-center" style="width: 15%;">Quantity</th>
                    <th class="text-right" style="width: 15%;">Unit Price</th>
                    <th class="text-right" style="width: 20%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @if(count($tierBreakdown) > 0)
                    @foreach($tierBreakdown as $tier)
                    <tr>
                        <td>{{ $tier['tier_name'] }}</td>
                        <td class="text-center">{{ number_format($tier['quantity']) }}</td>
                        <td class="text-right">R {{ number_format($tier['rate'], 2) }}</td>
                        <td class="text-right">R {{ number_format($tier['amount'], 2) }}</td>
                    </tr>
                    @endforeach
                @elseif($lineItems->count() > 0)
                    @foreach($lineItems as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="text-center">{{ number_format($item->quantity) }}</td>
                        <td class="text-right">R {{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">R {{ number_format($item->amount, 2) }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td>Payslip Processing Services</td>
                        <td class="text-center">{{ number_format($invoice->total_payslips ?? 0) }}</td>
                        <td class="text-right">-</td>
                        <td class="text-right">R {{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
        
        <!-- Totals Section -->
        <div class="totals-section">
            <div class="totals-left">
                <!-- Bank Details -->
                <div class="bank-details">
                    <div class="bank-details-title">Banking Details for EFT Payment</div>
                    <table>
                        <tr>
                            <td class="label">Bank:</td>
                            <td>{{ $company['bank_name'] }}</td>
                        </tr>
                        <tr>
                            <td class="label">Account Name:</td>
                            <td>{{ $company['bank_account_name'] }}</td>
                        </tr>
                        <tr>
                            <td class="label">Account Number:</td>
                            <td><strong>{{ $company['bank_account_number'] }}</strong></td>
                        </tr>
                        <tr>
                            <td class="label">Branch Code:</td>
                            <td>{{ $company['bank_branch_code'] }}</td>
                        </tr>
                        <tr>
                            <td class="label">Reference:</td>
                            <td><strong>{{ $company['bank_reference'] }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="totals-right">
                <table class="totals-table">
                    <tr>
                        <td class="label">Subtotal:</td>
                        <td class="value">R {{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    @if($invoice->vat_amount > 0)
                    <tr>
                        <td class="label">VAT (15%):</td>
                        <td class="value">R {{ number_format($invoice->vat_amount, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="total-row">
                        <td class="label">Total Due:</td>
                        <td class="value">R {{ number_format($invoice->total_amount, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Notes -->
        @if($invoice->notes)
        <div class="notes">
            <div class="notes-title">Notes:</div>
            {{ $invoice->notes }}
        </div>
        @endif
        
        <!-- Footer -->
        <div class="footer">
            Thank you for your business!<br>
            {{ $company['name'] }} | {{ $company['email'] }}
            @if($company['phone']) | {{ $company['phone'] }}@endif
        </div>
    </div>
</body>
</html>
