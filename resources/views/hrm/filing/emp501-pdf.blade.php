<!DOCTYPE html>
<html>
<head>
    <title>EMP501 - {{ $seasonData['season_label'] }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2, .header h3 { margin: 0; }
        .section { margin-bottom: 20px; }
        .section h4 {
            background-color: #3956ca;
            color: #fff;
            padding: 5px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .table td, .table th { border: 1px solid #ccc; padding: 6px; text-align: left; }
        .table th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .total-row td { font-weight: bold; background-color: #e8f5e8; }
        .summary-table td { border: none; padding: 2px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h2>EMP501</h2>
        <h3>{{ $seasonData['season_label'] }}</h3>
        <p>Period: {{ formatDate($seasonData['start_date']) }} to {{ formatDate($seasonData['end_date']) }}</p>
    </div>

    <div class="section">
        <h4>Reconciliation Summary</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Amount (R)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total PAYE Liability</td>
                    <td class="text-right">{{ number_format($summary['total_paye'], 2) }}</td>
                </tr>
                <tr>
                    <td>Total UIF Liability</td>
                    <td class="text-right">{{ number_format($summary['total_uif'], 2) }}</td>
                </tr>
                <tr>
                    <td>Total SDL Liability</td>
                    <td class="text-right">{{ number_format($summary['total_sdl'], 2) }}</td>
                </tr>
                <tr>
                    <td>Total ETI Calculated</td>
                    <td class="text-right">{{ number_format($summary['total_eti'], 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>Total Payment Due for Period</td>
                    <td class="text-right">{{ number_format($summary['total_payable'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h4>Monthly Breakdown</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th class="text-right">Gross Salary (R)</th>
                    <th class="text-right">PAYE (R)</th>
                    <th class="text-right">UIF (R)</th>
                    <th class="text-right">SDL (R)</th>
                    <th class="text-right">ETI (R)</th>
                    <th class="text-right">Total Payable (R)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($monthlyData as $month)
                <tr>
                    <td>{{ $month['month_name'] }}</td>
                    <td class="text-right">{{ number_format($month['gross_salary'], 2) }}</td>
                    <td class="text-right">{{ number_format($month['paye_liability'], 2) }}</td>
                    <td class="text-right">{{ number_format($month['uif_liability'], 2) }}</td>
                    <td class="text-right">{{ number_format($month['sdl_liability'], 2) }}</td>
                    <td class="text-right">{{ number_format($month['eti_current_month'], 2) }}</td>
                    <td class="text-right">{{ number_format($month['total_payable'], 2) }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td>Total</td>
                    <td class="text-right">{{ number_format(array_sum(array_column($monthlyData, 'gross_salary')), 2) }}</td>
                    <td class="text-right">{{ number_format($summary['total_paye'], 2) }}</td>
                    <td class="text-right">{{ number_format($summary['total_uif'], 2) }}</td>
                    <td class="text-right">{{ number_format($summary['total_sdl'], 2) }}</td>
                    <td class="text-right">{{ number_format($summary['total_eti'], 2) }}</td>
                    <td class="text-right">{{ number_format($summary['total_payable'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h4>Employee Certificate Summary (IRP5/IT3a)</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Employee No.</th>
                    <th class="text-right">Total Remuneration (R)</th>
                    <th class="text-right">Total PAYE (R)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employeeSummary as $employee)
                <tr>
                    <td>{{ $employee['name'] }}</td>
                    <td>{{ $employee['employee_id'] }}</td>
                    <td class="text-right">{{ number_format($employee['total_remuneration'], 2) }}</td>
                    <td class="text-right">{{ number_format($employee['total_paye'], 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center;">No employee data for this period.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>