@extends('hrm.ess.layouts.app')

@section('page-title', 'Payslips')
@section('page-subtitle', 'View and download your payslips')

@push('styles')
<style>
    .payslip-list {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
        border: 1px solid var(--ess-border);
        overflow: hidden;
    }
    
    .payslip-list-header {
        display: grid;
        grid-template-columns: 2fr 1fr 150px;
        gap: 16px;
        padding: 16px 24px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid var(--ess-border);
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--ess-text-muted);
    }
    
    .payslip-list-item {
        display: grid;
        grid-template-columns: 2fr 1fr 150px;
        gap: 16px;
        padding: 20px 24px;
        align-items: center;
        border-bottom: 1px solid var(--ess-border);
        transition: all 0.2s ease;
    }
    
    .payslip-list-item:last-child {
        border-bottom: none;
    }
    
    .payslip-list-item:hover {
        background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
    }
    
    .payslip-period {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    
    .payslip-period-icon {
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .payslip-period-icon svg {
        width: 22px;
        height: 22px;
        color: var(--ess-primary);
    }
    
    .payslip-period-text {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    
    .payslip-month {
        font-size: 16px;
        font-weight: 600;
        color: var(--ess-text);
    }
    
    .payslip-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
    }
    
    .payslip-status svg {
        width: 14px;
        height: 14px;
    }
    
    .payslip-date {
        font-size: 14px;
        color: var(--ess-text-muted);
    }
    
    .payslip-btn {
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        cursor: pointer;
        white-space: nowrap;
    }
    
    .payslip-btn svg {
        width: 18px;
        height: 18px;
    }
    
    .payslip-btn-rc-primary {
        background: linear-gradient(135deg, var(--ess-primary) 0%, var(--ess-primary-dark) 100%);
        color: #fff !important;
        border: none;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }
    
    .payslip-btn-rc-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        color: #fff !important;
    }
    
    .year-filter-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 30px;
        color: white;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }
    
    .year-filter-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }
    
    .year-filter-card h4 {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 8px;
        position: relative;
        color: #FFF;
    }
    
    .year-filter-card p {
        opacity: 0.9;
        margin-bottom: 20px;
        position: relative;
    }
    
    .year-select-wrapper {
        position: relative;
        display: inline-block;
    }
    
    .year-select {
        appearance: none;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 12px 50px 12px 20px;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .year-select:hover {
        background: rgba(255, 255, 255, 0.3);
    }
    
    .year-select:focus {
        outline: none;
        background: rgba(255, 255, 255, 0.35);
    }
    
    .year-select option {
        color: #1f2937;
        background: white;
    }
    
    .year-select-wrapper::after {
        content: '';
        position: absolute;
        right: 18px;
        top: 50%;
        transform: translateY(-50%);
        width: 0;
        height: 0;
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-top: 6px solid white;
        pointer-events: none;
    }
    
    .payslip-count {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.2);
        padding: 8px 16px;
        border-radius: 30px;
        font-size: 14px;
        font-weight: 500;
        position: relative;
    }
    
    .empty-state {
        text-align: center;
        padding: 80px 20px;
        background: white;
        border-radius: 20px;
        border: 2px dashed var(--ess-border);
    }
    
    .empty-state-icon {
        width: 100px;
        height: 100px;
        margin: 0 auto 24px;
        background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: pulse-soft 2s ease-in-out infinite;
    }
    
    .empty-state-icon svg {
        width: 48px;
        height: 48px;
        color: var(--ess-primary);
    }
    
    @keyframes pulse-soft {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .payslip-list-header {
            display: none;
        }
        
        .payslip-list-item {
            grid-template-columns: 1fr;
            gap: 12px;
            padding: 16px 20px;
        }
        
        .payslip-period {
            justify-content: flex-start;
        }
        
        .payslip-date {
            padding-left: 58px;
        }
        
        .payslip-actions-col {
            padding-left: 58px;
            justify-content: flex-start !important;
        }
    }
</style>
@endpush

@section('content')
<!-- Year Filter Banner -->
<div class="year-filter-card">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h4><i data-feather="file-text" style="display: inline; width: 28px; height: 28px; margin-right: 10px;"></i>Payslip Archive</h4>
            <p>Access and download your monthly payslips. Select a year to view available payslips.</p>
            <form method="GET" action="{{ route('ess.payslips') }}" id="yearForm">
                <div class="year-select-wrapper">
                    <select name="year" id="year" onchange="document.getElementById('yearForm').submit()" class="year-select">
                        @foreach($years as $y => $label)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <div class="payslip-count">
                <i data-feather="layers" style="width: 18px; height: 18px;"></i>
                {{ $payslips->count() }} Payslip{{ $payslips->count() != 1 ? 's' : '' }} Available
            </div>
        </div>
    </div>
</div>

<!-- Payslips List -->
@if($payslips->count() > 0)
    <div class="payslip-list">
        <div class="payslip-list-header">
            <div>Pay Period</div>
            <div>Created Date</div>
            <div>Action</div>
        </div>
        @foreach($payslips as $payslip)
            @php
                $monthDate = \Carbon\Carbon::parse($payslip->salary_month);
            @endphp
            <div class="payslip-list-item">
                <div class="payslip-period">
                    <div class="payslip-period-icon">
                        <i data-feather="file-text"></i>
                    </div>
                    <div class="payslip-period-text">
                        <span class="payslip-month">{{ formatMonthYear($monthDate) }}</span>
                    </div>
                </div>
                <div class="payslip-date">
                    {{ $payslip->created_at ? formatDate($payslip->created_at) : formatDate($monthDate->endOfMonth()) }}
                </div>
                <div class="payslip-actions-col">
                    <a href="{{ route('ess.payslips.download', \Illuminate\Support\Facades\Crypt::encrypt($payslip->id)) }}" 
                       target="_blank"
                       class="payslip-btn payslip-btn-rc-primary">
                        <i data-feather="download"></i>
                        Download
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="empty-state">
        <div class="empty-state-icon">
            <i data-feather="inbox"></i>
        </div>
        <h4 style="color: var(--ess-text); font-weight: 700; margin-bottom: 12px;">No Payslips for {{ $year }}</h4>
        <p style="color: var(--ess-text-muted); max-width: 400px; margin: 0 auto;">
            There are no payslips available for this year yet. They will appear here once processed by your employer.
        </p>
    </div>
@endif
@endsection
