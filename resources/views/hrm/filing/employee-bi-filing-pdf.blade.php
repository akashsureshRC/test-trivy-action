<!DOCTYPE html>
<html>
<head>
    <title>Employee Bi-Filing Report - {{ $payslip->employee_profile->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .section { margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .table td, .table th { border: 1px solid #000; padding: 8px; text-align: left; }
        .table th { background-color: #f0f0f0; font-weight: bold; }
        .text-right { text-align: right; }
        .employee-info { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Employee Bi-Filing Report</h2>
        <h3>{{ formatMonthYear($payrun->term) }}</h3>
    </div>

    <!-- Employee Information -->
    <div class="section">
        <h4>Employee Information</h4>
        <table class="table">
            <tr class="employee-info">
                <td><strong>Name:</strong></td>
                <td>{{ $payslip->employee_profile->first_name }} {{ $payslip->employee_profile->last_name }}</td>
                <td><strong>Employee ID:</strong></td>
                <td>{{ $payslip->employee_profile->employee_id }}</td>
            </tr>
            <tr>
                <td><strong>Email:</strong></td>
                <td>{{ $payslip->employee_profile->email ?? 'N/A' }}</td>
                <td><strong>Phone:</strong></td>
                <td>{{ $payslip->employee_profile->phone_number ?? 'N/A' }}</td>
            </tr>
            <tr class="employee-info">
                <td><strong>Department:</strong></td>
                <td>{{ $payslip->employee_profile->department->name ?? 'N/A' }}</td>
                <td><strong>Designation:</strong></td>
                <td>{{ $payslip->employee_profile->designation->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Date of Birth:</strong></td>
                <td>{{ $payslip->employee_profile->date_of_birth ? formatDate($payslip->employee_profile->date_of_birth) : 'N/A' }}</td>
                <td><strong>Gender:</strong></td>
                <td>{{ $payslip->employee_profile->gender ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <!-- Salary Breakdown -->
    <div class="section">
        <h4>Salary Breakdown</h4>
        <table class="table">
            <tr>
                <td>Basic Salary</td>
                <td class="text-right">R {{ number_format($payslip->basic_salary, 2) }}</td>
            </tr>
            <tr>
                <td>Allowances</td>
                <td class="text-right">R {{ number_format($payslip->allowance, 2) }}</td>
            </tr>
            <tr>
                <td>Other Payments</td>
                <td class="text-right">R {{ number_format($payslip->other_payment, 2) }}</td>
            </tr>
            <tr style="font-weight: bold; background-color: #f0f0f0;">
                <td>Gross Salary</td>
                <td class="text-right">R {{ number_format($employeeData['gross_salary'], 2) }}</td>
            </tr>
        </table>
    </div>

    <!-- Tax Deductions -->
    <div class="section">
        <h4>Tax Deductions</h4>
        <table class="table">
            <tr>
                <td>PAYE (Pay As You Earn)</td>
                <td class="text-right">R {{ number_format($employeeData['paye'], 2) }}</td>
            </tr>
            <tr>
                <td>UIF (Unemployment Insurance Fund)</td>
                <td class="text-right">R {{ number_format($employeeData['uif'], 2) }}</td>
            </tr>
            <tr>
                <td>SDL (Skills Development Levy)</td>
                <td class="text-right">R {{ number_format($employeeData['sdl'], 2) }}</td>
            </tr>
            <tr style="font-weight: bold; background-color: #f0f0f0;">
                <td>Total Deductions</td>
                <td class="text-right">R {{ number_format($employeeData['total_deductions'], 2) }}</td>
            </tr>
            <tr style="font-weight: bold; background-color: #e8f5e8;">
                <td>Net Salary</td>
                <td class="text-right">R {{ number_format($employeeData['net_salary'], 2) }}</td>
            </tr>
        </table>
    </div>

    <!-- Additional Information -->
    <div class="section">
        <h4>Additional Information</h4>
        <table class="table">
            <tr>
                <td>Payrun Term</td>
                <td>{{ $payrun->term }}</td>
            </tr>
            <tr>
                <td>Status</td>
                <td>{{ $payslip->emp201_status ?? 'Draft' }}</td>
            </tr>
            <tr>
                <td>Generated Date</td>
                <td>{{ formatDateTime(\Carbon\Carbon::now()) }}</td>
            </tr>
        </table>
    </div>
</body>
</html>