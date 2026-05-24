<!DOCTYPE html>
<html>
<head>
    <title>EMP201 - {{ formatMonthYear($month) }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .table td, .table th { border: 1px solid #000; padding: 8px; text-align: left; }
        .table th { background-color: #f0f0f0; font-weight: bold; }
        .total-row { font-weight: bold; background-color: #f9f9f9; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>EMP201 Payment Details</h2>
        <h3>{{ formatMonthYear($month) }}</h3>
    </div>

    @if(isset($emp201Data))
    <table class="table">
        <tr>
            <td>PAYE Liability</td>
            <td class="text-right">R {{ number_format($emp201Data['paye_liability'], 2) }}</td>
            <td>ETI Brought Forward</td>
            <td class="text-right">R {{ number_format($emp201Data['eti_brought_forward'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>UIF Liability</td>
            <td class="text-right">R {{ number_format($emp201Data['uif_liability'], 2) }}</td>
            <td>SDL Liability</td>
            <td class="text-right">R {{ number_format($emp201Data['sdl_liability'], 2) }}</td>
        </tr>
        <tr>
            <td>ETI Current Month</td>
            <td class="text-right">R {{ number_format($emp201Data['eti_current_month'] ?? 0, 2) }}</td>
            <td>Total Employees</td>
            <td class="text-right">{{ isset($payslips) ? $payslips->count() : 0 }}</td>
        </tr>
        <tr class="total-row">
            <td colspan="3">Total Payable</td>
            <td class="text-right">R {{ number_format($emp201Data['total_payable'], 2) }}</td>
        </tr>
    </table>

    <table class="table">
        <tr>
            <th>ETI Summary</th>
            <th class="text-right">Amount</th>
        </tr>
        <tr>
            <td>ETI for this month</td>
            <td class="text-right">R {{ number_format($emp201Data['eti_current_month'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>ETI for prior months</td>
            <td class="text-right">R {{ number_format($emp201Data['eti_brought_forward'] ?? 0, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td>Total ETI</td>
            <td class="text-right">R {{ number_format($emp201Data['total_eti'] ?? 0, 2) }}</td>
        </tr>
    </table>
    @endif
</body>
</html>