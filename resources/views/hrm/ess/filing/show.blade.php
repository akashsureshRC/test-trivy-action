@extends('hrm.ess.layouts.app')

@section('page-title', $seasonData['season_label'])
@section('page-subtitle', 'Period: ' . $seasonData['period_start'] . ' - ' . $seasonData['period_end'])

@section('content')

<!-- Back Button -->
<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route('ess.filing') }}" class="btn btn-outline-secondary btn-sm">
            <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i>
            Back to Tax Certificates
        </a>
    </div>
</div>

<!-- Season Summary Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 col-6">
        <div class="ess-card" style="text-align: center; padding: 20px;">
            <div style="width: 50px; height: 50px; margin: 0 auto 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i data-feather="dollar-sign" style="width: 24px; height: 24px; color: white;"></i>
            </div>
            <small style="color: var(--ess-text-muted); display: block; margin-bottom: 5px;">Total Gross</small>
            <strong style="color: var(--ess-text); font-size: 18px;">R {{ number_format($seasonData['totals']['gross_salary'], 2) }}</strong>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-6">
        <div class="ess-card" style="text-align: center; padding: 20px;">
            <div style="width: 50px; height: 50px; margin: 0 auto 15px; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i data-feather="minus-circle" style="width: 24px; height: 24px; color: white;"></i>
            </div>
            <small style="color: var(--ess-text-muted); display: block; margin-bottom: 5px;">PAYE Deducted</small>
            <strong style="color: #dc3545; font-size: 18px;">R {{ number_format($seasonData['totals']['paye'], 2) }}</strong>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-6">
        <div class="ess-card" style="text-align: center; padding: 20px;">
            <div style="width: 50px; height: 50px; margin: 0 auto 15px; background: linear-gradient(135deg, #fd7e14 0%, #e86d0e 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i data-feather="shield" style="width: 24px; height: 24px; color: white;"></i>
            </div>
            <small style="color: var(--ess-text-muted); display: block; margin-bottom: 5px;">UIF</small>
            <strong style="color: #fd7e14; font-size: 18px;">R {{ number_format($seasonData['totals']['uif'], 2) }}</strong>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-6">
        <div class="ess-card" style="text-align: center; padding: 20px;">
            <div style="width: 50px; height: 50px; margin: 0 auto 15px; background: linear-gradient(135deg, #28a745 0%, #218838 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i data-feather="check-circle" style="width: 24px; height: 24px; color: white;"></i>
            </div>
            <small style="color: var(--ess-text-muted); display: block; margin-bottom: 5px;">Net Earnings</small>
            <strong style="color: #28a745; font-size: 18px;">R {{ number_format($seasonData['totals']['net_salary'], 2) }}</strong>
        </div>
    </div>
</div>

<!-- Payrun Details Table -->
<div class="row">
    <div class="col-12">
        <div class="ess-card">
            <div class="ess-card-header d-flex justify-content-between align-items-center">
                <h3 class="ess-card-title">Monthly Breakdown</h3>
            </div>
            
            @if(count($seasonData['payruns']) > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="padding: 15px; font-weight: 600; color: var(--ess-text-muted); font-size: 12px; text-transform: uppercase;">Month</th>
                                <th style="padding: 15px; font-weight: 600; color: var(--ess-text-muted); font-size: 12px; text-transform: uppercase; text-align: right;">Gross</th>
                                <th style="padding: 15px; font-weight: 600; color: var(--ess-text-muted); font-size: 12px; text-transform: uppercase; text-align: right;">PAYE</th>
                                <th style="padding: 15px; font-weight: 600; color: var(--ess-text-muted); font-size: 12px; text-transform: uppercase; text-align: right;">UIF</th>
                                <th style="padding: 15px; font-weight: 600; color: var(--ess-text-muted); font-size: 12px; text-transform: uppercase; text-align: right;">Net</th>
                                <th style="padding: 15px; font-weight: 600; color: var(--ess-text-muted); font-size: 12px; text-transform: uppercase; text-align: center;">Download</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($seasonData['payruns'] as $payrun)
                                <tr>
                                    <td style="padding: 15px;">
                                        <strong style="color: var(--ess-text);">{{ $payrun['month_name'] }}</strong>
                                    </td>
                                    <td style="padding: 15px; text-align: right;">
                                        <span style="color: var(--ess-text);">R {{ number_format($payrun['gross_salary'], 2) }}</span>
                                    </td>
                                    <td style="padding: 15px; text-align: right;">
                                        <span style="color: #dc3545;">R {{ number_format($payrun['paye'], 2) }}</span>
                                    </td>
                                    <td style="padding: 15px; text-align: right;">
                                        <span style="color: #fd7e14;">R {{ number_format($payrun['uif'], 2) }}</span>
                                    </td>
                                    <td style="padding: 15px; text-align: right;">
                                        <strong style="color: #28a745;">R {{ number_format($payrun['net_salary'], 2) }}</strong>
                                    </td>
                                    <td style="padding: 15px; text-align: center;">
                                        <a href="{{ route('ess.filing.download', $payrun['payslip_id_encrypted']) }}" 
                                           class="btn btn-outline-primary btn-sm"
                                           title="Download Filing Report">
                                            <i data-feather="download" style="width: 14px; height: 14px;"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot style="background: #f8f9fa;">
                            <tr>
                                <td style="padding: 15px;"><strong>Season Total</strong></td>
                                <td style="padding: 15px; text-align: right;"><strong>R {{ number_format($seasonData['totals']['gross_salary'], 2) }}</strong></td>
                                <td style="padding: 15px; text-align: right;"><strong style="color: #dc3545;">R {{ number_format($seasonData['totals']['paye'], 2) }}</strong></td>
                                <td style="padding: 15px; text-align: right;"><strong style="color: #fd7e14;">R {{ number_format($seasonData['totals']['uif'], 2) }}</strong></td>
                                <td style="padding: 15px; text-align: right;"><strong style="color: #28a745;">R {{ number_format($seasonData['totals']['net_salary'], 2) }}</strong></td>
                                <td style="padding: 15px;"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div style="text-align: center; padding: 40px 20px;">
                    <p style="color: var(--ess-text-muted); margin: 0;">No payrun data found for this season.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Tax Information Card -->
<div class="row mt-4">
    <div class="col-12">
        <div class="ess-card">
            <div class="ess-card-header">
                <h3 class="ess-card-title">Tax Deduction Summary</h3>
            </div>
            <div style="padding: 20px;">
                <div class="row">
                    <div class="col-md-6">
                        <h6 style="color: var(--ess-text-muted); margin-bottom: 15px;">What These Deductions Mean</h6>
                        <ul style="color: var(--ess-text); font-size: 14px; line-height: 1.8;">
                            <li><strong>PAYE (Pay As You Earn):</strong> Income tax deducted from your salary and paid to SARS on your behalf.</li>
                            <li><strong>UIF (Unemployment Insurance Fund):</strong> Contributes to unemployment benefits. Both you and your employer contribute 1% each.</li>
                            <li><strong>SDL (Skills Development Levy):</strong> Employer contribution for skills development. Usually 1% of payroll.</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 style="color: var(--ess-text-muted); margin-bottom: 15px;">Important Notes</h6>
                        <ul style="color: var(--ess-text); font-size: 14px; line-height: 1.8;">
                            <li>Your IRP5/IT3(a) tax certificate for annual SARS filing is based on this data.</li>
                            <li>Keep these records for your tax returns.</li>
                            <li>Contact HR if you notice any discrepancies in your tax deductions.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
