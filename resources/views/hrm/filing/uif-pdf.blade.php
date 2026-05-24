<!DOCTYPE html>
<html>
<head>
    <title>UIF Declaration - {{ formatMonthYear($month) }}</title>
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
        <h2>UIF Declaration Details</h2>
        <h3>{{ formatMonthYear($month) }}</h3>
    </div>

    @if(isset($uifData))
    <table class="table">
        <tr>
            <td>Total UIF Liability</td>
            <td class="text-right">R {{ number_format($uifData['total_uif_liability'], 2) }}</td>
        </tr>
        <tr>
            <td>Employee Contribution (1%)</td>
            <td class="text-right">R {{ number_format($uifData['employee_contribution'], 2) }}</td>
        </tr>
        <tr>
            <td>Employer Contribution (1%)</td>
            <td class="text-right">R {{ number_format($uifData['employer_contribution'], 2) }}</td>
        </tr>
        <tr>
            <td>Total Remuneration</td>
            <td class="text-right">R {{ number_format($uifData['total_remuneration'], 2) }}</td>
        </tr>
        <tr>
            <td>Total Employees</td>
            <td class="text-right">{{ isset($payslips) ? $payslips->count() : 0 }}</td>
        </tr>
        <tr class="total-row">
            <td>Total UIF Payable</td>
            <td class="text-right">R {{ number_format($uifData['total_uif_liability'], 2) }}</td>
        </tr>
    </table>
    @endif
</body>
</html>