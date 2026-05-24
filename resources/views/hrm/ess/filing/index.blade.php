@extends('hrm.ess.layouts.app')

@section('page-title', 'Tax Certificates')
@section('page-subtitle', 'View your tax filings and download certificates')

@section('content')
<!-- Tax Years -->
<div class="row">
    <div class="col-12">
        <div class="ess-card">
            <div class="ess-card-header">
                <h3 class="ess-card-title">Tax Years</h3>
            </div>
            
            @if(count($biFilingData) > 0)
                @foreach($biFilingData as $season)
                    <div class="filing-season-card" style="border-bottom: 1px solid #eee; padding: 20px;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 style="margin: 0; color: var(--ess-primary); font-weight: 600;">
                                    {{ $season['season_label'] }}
                                </h5>
                                <small style="color: var(--ess-text-muted);">
                                    {{ $season['totals']['payrun_count'] }} payrun(s) in this season
                                </small>
                            </div>
                            <a href="{{ route('ess.filing.show', $season['season']) }}" 
                               class="btn btn-outline-primary btn-sm">
                                <i data-feather="eye" style="width: 14px; height: 14px;"></i>
                                View Details
                            </a>
                        </div>
                        
                        <!-- Season Summary -->
                        <div class="row">
                            <div class="col-md-3 col-6">
                                <div class="filing-stat" style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px; margin-bottom: 10px;">
                                    <small style="color: var(--ess-text-muted); display: block; margin-bottom: 5px;">Gross Earnings</small>
                                    <strong style="color: var(--ess-text); font-size: 16px;">R {{ number_format($season['totals']['gross_salary'], 2) }}</strong>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="filing-stat" style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px; margin-bottom: 10px;">
                                    <small style="color: var(--ess-text-muted); display: block; margin-bottom: 5px;">PAYE Deducted</small>
                                    <strong style="color: #dc3545; font-size: 16px;">R {{ number_format($season['totals']['paye'], 2) }}</strong>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="filing-stat" style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px; margin-bottom: 10px;">
                                    <small style="color: var(--ess-text-muted); display: block; margin-bottom: 5px;">UIF Contribution</small>
                                    <strong style="color: #fd7e14; font-size: 16px;">R {{ number_format($season['totals']['uif'], 2) }}</strong>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="filing-stat" style="text-align: center; padding: 15px; background: #e8f5e8; border-radius: 8px; margin-bottom: 10px;">
                                    <small style="color: var(--ess-text-muted); display: block; margin-bottom: 5px;">Net Earnings</small>
                                    <strong style="color: #28a745; font-size: 16px;">R {{ number_format($season['totals']['net_salary'], 2) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div style="text-align: center; padding: 60px 20px;">
                    <div style="width: 80px; height: 80px; margin: 0 auto 20px; background: var(--ess-primary-light); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i data-feather="file-text" style="width: 32px; height: 32px; color: var(--ess-primary);"></i>
                    </div>
                    <h5 style="color: var(--ess-text); margin-bottom: 8px;">No Tax Data Available</h5>
                    <p style="color: var(--ess-text-muted); margin: 0;">You don't have any tax certificate data yet. Tax data will appear here once payslips are processed through the payrun system.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
