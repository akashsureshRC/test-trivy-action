@extends('hrm.ess.layouts.app')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Welcome back, ' . $employee->first_name . '!')

@section('styles')
<style>
    .dashboard-hero {
        background: linear-gradient(135deg, #655997 0%, #8b5cf6 100%);
        border-radius: var(--ess-border-radius);
        padding: 32px;
        color: #fff;
        margin-bottom: 32px;
        position: relative;
        overflow: hidden;
    }

    .dashboard-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .dashboard-hero::after {
        content: '';
        position: absolute;
        bottom: -30%;
        right: 10%;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
    }

    .dashboard-hero-content {
        position: relative;
        z-index: 1;
    }

    .dashboard-hero h2 {
        font-size: 28px;
        font-weight: 700;
        margin: 0 0 8px;
        color: #fff;
    }

    .dashboard-hero p {
        font-size: 15px;
        opacity: 0.9;
        margin: 0;
    }

    .dashboard-hero-stats {
        display: flex;
        gap: 32px;
        margin-top: 24px;
    }

    .dashboard-hero-stat {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .dashboard-hero-stat-icon {
        width: 48px;
        height: 48px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .dashboard-hero-stat-icon svg {
        width: 24px;
        height: 24px;
    }

    .dashboard-hero-stat-text h4 {
        font-size: 20px;
        font-weight: 700;
        margin: 0;
        color: #fff;
    }

    .dashboard-hero-stat-text span {
        font-size: 13px;
        opacity: 0.8;
    }

    .section-header {
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .section-block {
        margin-bottom: 32px;
    }

    .section-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--ess-text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title svg {
        width: 20px;
        height: 20px;
        color: var(--ess-primary);
    }

    .leave-balance-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 20px;
    }

    @media (max-width: 1400px) {
        .leave-balance-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (max-width: 1100px) {
        .leave-balance-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .leave-balance-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 480px) {
        .leave-balance-grid {
            grid-template-columns: 1fr;
        }
    }

    .leave-balance-card {
        background: var(--ess-card-bg);
        border-radius: var(--ess-border-radius);
        border: 1px solid var(--ess-border);
        padding: 24px;
        position: relative;
        overflow: hidden;
        transition: var(--ess-transition);
    }

    .leave-balance-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--ess-shadow-lg);
    }

    .leave-balance-card.primary::before {
        background: linear-gradient(90deg, #655997 0%, #8b5cf6 100%);
    }

    .leave-balance-card.success::before {
        background: linear-gradient(90deg, #10b981 0%, #059669 100%);
    }

    .leave-balance-card.warning::before {
        background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
    }

    .leave-balance-card.danger::before {
        background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%);
    }

    .leave-balance-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .leave-balance-name {
        font-size: 15px;
        font-weight: 600;
        color: var(--ess-text);
    }

    .leave-balance-days {
        font-size: 32px;
        font-weight: 800;
        color: var(--ess-text);
        letter-spacing: -1px;
        margin-bottom: 4px;
    }

    .leave-balance-days span {
        font-size: 14px;
        font-weight: 500;
        color: var(--ess-text-muted);
        letter-spacing: 0;
    }

    .leave-balance-meta {
        font-size: 13px;
        color: var(--ess-text-muted);
        margin-bottom: 16px;
    }

    .leave-balance-progress {
        height: 6px;
        background: var(--ess-border);
        border-radius: 50px;
        overflow: hidden;
    }

    .leave-balance-progress-bar {
        height: 100%;
        border-radius: 50px;
        transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .top-row {
        display: grid;
        grid-template-columns: 1fr 280px;
        gap: 24px;
        margin-bottom: 32px;
        align-items: stretch;
    }

    @media (max-width: 1200px) {
        .top-row {
            grid-template-columns: 1fr;
        }
    }

    .top-row .section-block {
        margin-bottom: 0;
        display: flex;
        flex-direction: column;
    }

    .quick-actions-list {
        background: var(--ess-card-bg);
        border: 1px solid var(--ess-border);
        border-radius: var(--ess-border-radius);
        overflow: hidden;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .quick-action-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 20px;
        text-decoration: none;
        transition: var(--ess-transition);
        border-bottom: 1px solid var(--ess-border-light);
    }

    .quick-action-item:last-child {
        border-bottom: none;
    }

    .quick-action-item:hover {
        background: var(--ess-bg);
    }

    .quick-action-item:hover .quick-action-icon {
        background: linear-gradient(135deg, var(--ess-primary) 0%, var(--ess-secondary) 100%);
        color: #fff;
    }

    .quick-action-icon {
        width: 40px;
        height: 40px;
        background: var(--ess-primary-light);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--ess-primary);
        transition: var(--ess-transition);
        flex-shrink: 0;
    }

    .quick-action-icon svg {
        width: 20px;
        height: 20px;
    }

    .quick-action-text {
        font-size: 14px;
        font-weight: 600;
        color: var(--ess-text);
    }

    .info-card {
        background: var(--ess-card-bg);
        border: 1px solid var(--ess-border);
        border-radius: var(--ess-border-radius);
        padding: 24px;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }

    @media (max-width: 992px) {
        .info-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .info-grid {
            grid-template-columns: 1fr;
        }
    }

    .info-item {
        padding: 16px;
        background: var(--ess-bg);
        border-radius: var(--ess-border-radius-sm);
    }

    .info-item-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--ess-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }

    .info-item-value {
        font-size: 14px;
        font-weight: 600;
        color: var(--ess-text);
    }

    .activity-card {
        background: var(--ess-card-bg);
        border: 1px solid var(--ess-border);
        border-radius: var(--ess-border-radius);
        height: 100%;
        min-height: 220px;
        display: flex;
        flex-direction: column;
    }

    .activity-card .ess-card-body {
        padding: 16px 20px;
        flex: 1;
    }

    .row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -12px;
    }

    .row>.col-lg-6 {
        padding: 0 12px;
        flex: 0 0 50%;
        max-width: 50%;
    }

    @media (max-width: 991px) {
        .row>.col-lg-6 {
            flex: 0 0 100%;
            max-width: 100%;
            margin-bottom: 20px;
        }

        .row>.col-lg-6:last-child {
            margin-bottom: 0;
        }
    }

    .row>.col-lg-6 .section-block {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .row>.col-lg-6 .activity-card {
        flex: 1;
    }

    .recent-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 0;
        border-bottom: 1px solid var(--ess-border-light);
    }

    .recent-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .recent-item:first-child {
        padding-top: 0;
    }

    .recent-item-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .recent-item-icon.payslip {
        background: var(--ess-success-light);
        color: var(--ess-success);
    }

    .recent-item-icon.leave {
        background: var(--ess-warning-light);
        color: var(--ess-warning);
    }

    .recent-item-icon svg {
        width: 18px;
        height: 18px;
    }

    .recent-item-content {
        flex: 1;
        min-width: 0;
    }

    .recent-item-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--ess-text);
        margin: 0;
    }

    .recent-item-meta {
        font-size: 12px;
        color: var(--ess-text-muted);
        margin: 2px 0 0;
    }

    .empty-state-sm {
        text-align: center;
        padding: 32px 20px;
    }

    .empty-state-sm .empty-icon {
        width: 56px;
        height: 56px;
        background: var(--ess-primary-light);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 14px;
    }

    .empty-state-sm .empty-icon svg {
        width: 24px;
        height: 24px;
        color: var(--ess-primary);
    }

    .empty-state-sm h5 {
        font-size: 14px;
        font-weight: 600;
        color: var(--ess-text);
        margin: 0 0 4px;
    }

    .empty-state-sm p {
        font-size: 13px;
        color: var(--ess-text-muted);
        margin: 0;
    }

    @media (max-width: 767px) {
        .dashboard-hero {
            padding: 24px;
        }

        .dashboard-hero h2 {
            font-size: 22px;
        }

        .dashboard-hero-stats {
            flex-direction: column;
            gap: 16px;
        }

        .quick-actions-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<!-- Hero Section -->
<div class="dashboard-hero animate-fade-in-up">
    <div class="dashboard-hero-content">
        <h2>Good {{ now()->format('H') < 12 ? 'Morning' : (now()->format('H') < 17 ? 'Afternoon' : 'Evening') }}, {{ $employee->first_name }}! 👋</h2>
        <p>Here's an overview of your employee portal</p>

        <div class="dashboard-hero-stats">
            <div class="dashboard-hero-stat">
                <div class="dashboard-hero-stat-icon">
                    <i data-feather="briefcase"></i>
                </div>
                <div class="dashboard-hero-stat-text">
                    <h4>{{ $employee->designation->name ?? 'N/A' }}</h4>
                    <span>{{ $employee->department->name ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="dashboard-hero-stat">
                <div class="dashboard-hero-stat-icon">
                    <i data-feather="calendar"></i>
                </div>
                <div class="dashboard-hero-stat-text">
                    @php
                    $joiningDate = $employee->date_of_appointment ? \Carbon\Carbon::parse($employee->date_of_appointment) : null;
                    $yearsWorked = $joiningDate ? $joiningDate->diffInYears(now()) : 0;
                    @endphp
                    <h4>{{ $yearsWorked }} {{ $yearsWorked == 1 ? 'Year' : 'Years' }}</h4>
                    <span>With the company</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leave Balances -->
<div class="section-header">
    <h3 class="section-title">
        <i data-feather="calendar"></i>
        Leave Balances
    </h3>
    <a href="{{ route('ess.leave') }}" class="ess-btn ess-btn-sm ess-btn-outline">View All</a>
</div>

@if(count($leaveBalances) > 0)
<div class="leave-balance-grid mb-5">
    @foreach($leaveBalances as $index => $balance)
    @php
    $percentage = $balance['total'] > 0 ? (($balance['total'] - $balance['remaining']) / $balance['total']) * 100 : 0;
    $colorClass = $balance['remaining'] > 5 ? 'success' : ($balance['remaining'] > 0 ? 'warning' : 'danger');
    @endphp
    <div class="leave-balance-card {{ $colorClass }} animate-fade-in-up stagger-{{ $index + 1 }}">
        <div class="leave-balance-header">
            <span class="leave-balance-name">{{ $balance['name'] }}</span>
            @if(isset($balance['is_unpaid']) && $balance['is_unpaid'])
            <span class="ess-badge ess-badge-warning">Unpaid</span>
            @endif
        </div>
        <div class="leave-balance-days">
            {{ $balance['remaining'] }} <span>days remaining</span>
        </div>
        <div class="leave-balance-meta">
            {{ $balance['used'] }} used of {{ $balance['total'] }} total
        </div>
        <div class="leave-balance-progress">
            <div class="leave-balance-progress-bar ess-progress-bar {{ $colorClass }}" style="width: {{ $percentage }}%"></div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="ess-card mb-4">
    <div class="ess-empty-state">
        <div class="ess-empty-state-icon">
            <i data-feather="alert-circle"></i>
        </div>
        <h4>No Leave Balances</h4>
        <p>No leave balances configured. Contact HR for more information.</p>
    </div>
</div>
@endif

<!-- My Information & Quick Actions Row -->
<div class="top-row">
    <!-- My Information Section -->
    <div class="section-block">
        <div class="section-header">
            <h3 class="section-title">
                <i data-feather="user"></i>
                My Information
            </h3>
            <a href="{{ route('ess.profile') }}" class="ess-btn ess-btn-sm ess-btn-outline">View Profile</a>
        </div>
        <div class="info-card">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-item-label">Employee ID</div>
                    <div class="info-item-value">{{ $employee->employee_id }}</div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Department</div>
                    <div class="info-item-value">{{ $employee->department->name ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Designation</div>
                    <div class="info-item-value">{{ $employee->designation->name ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Date of Joining</div>
                    <div class="info-item-value">
                        {{ $employee->date_of_appointment ? formatDate($employee->date_of_appointment) : 'N/A' }}
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Phone</div>
                    <div class="info-item-value">{{ $employee->phone_number ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Email</div>
                    <div class="info-item-value">{{ $employee->email ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="section-block">
        <div class="section-header">
            <h3 class="section-title">
                <i data-feather="zap"></i>
                Quick Actions
            </h3>
        </div>
        <div class="quick-actions-list">
            <a href="{{ route('ess.leave.apply') }}" class="quick-action-item">
                <div class="quick-action-icon">
                    <i data-feather="plus-circle"></i>
                </div>
                <span class="quick-action-text">Apply for Leave</span>
            </a>
            <a href="{{ route('ess.payslips') }}" class="quick-action-item">
                <div class="quick-action-icon">
                    <i data-feather="file-text"></i>
                </div>
                <span class="quick-action-text">View Payslips</span>
            </a>
            <a href="{{ route('ess.filing') }}" class="quick-action-item">
                <div class="quick-action-icon">
                    <i data-feather="download"></i>
                </div>
                <span class="quick-action-text">Tax Certificates</span>
            </a>
            <a href="{{ route('ess.profile') }}" class="quick-action-item">
                <div class="quick-action-icon">
                    <i data-feather="user"></i>
                </div>
                <span class="quick-action-text">My Profile</span>
            </a>
        </div>
    </div>
</div>

<!-- Recent Activity Row -->
<div class="row">
    <div class="col-lg-6">
        <div class="section-block">
            <div class="section-header">
                <h3 class="section-title">
                    <i data-feather="file-text"></i>
                    Recent Payslips
                </h3>
                <a href="{{ route('ess.payslips') }}" class="ess-btn ess-btn-sm ess-btn-outline">View All</a>
            </div>
            <div class="activity-card">
                <div class="ess-card-body">
                    @forelse($recentPayslips as $payslip)
                    <div class="recent-item">
                        <div class="recent-item-icon payslip">
                            <i data-feather="file-text"></i>
                        </div>
                        <div class="recent-item-content">
                            <h4 class="recent-item-title">{{ formatMonthYear($payslip->salary_month) }}</h4>
                            <p class="recent-item-meta">Generated {{ $payslip->created_at->diffForHumans() }}</p>
                        </div>
                        <a href="{{ route('ess.payslips.download', \Illuminate\Support\Facades\Crypt::encrypt($payslip->id)) }}" class="ess-btn ess-btn-sm ess-btn-outline" target="_blank">
                            <i data-feather="download"></i>
                        </a>
                    </div>
                    @empty
                    <div class="empty-state-sm">
                        <div class="empty-icon">
                            <i data-feather="file-text"></i>
                        </div>
                        <h5>No Payslips Yet</h5>
                        <p>No payslips available at the moment.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="section-block">
            <div class="section-header">
                <h3 class="section-title">
                    <i data-feather="clock"></i>
                    Pending Leave Requests
                </h3>
                <a href="{{ route('ess.leave') }}" class="ess-btn ess-btn-sm ess-btn-outline">View All</a>
            </div>
            <div class="activity-card">
                <div class="ess-card-body">
                    @forelse($pendingLeaves as $leave)
                    <div class="recent-item">
                        <div class="recent-item-icon leave">
                            <i data-feather="calendar"></i>
                        </div>
                        <div class="recent-item-content">
                            <h4 class="recent-item-title">{{ $leave->leaveManagement->leave_name ?? 'Leave Request' }}</h4>
                            <p class="recent-item-meta">
                                {{ formatDayMonth($leave->start_date) }} - {{ formatDate($leave->end_date) }}
                            </p>
                        </div>
                        <span class="ess-badge ess-badge-warning">Pending</span>
                    </div>
                    @empty
                    <div class="empty-state-sm">
                        <div class="empty-icon">
                            <i data-feather="check-circle"></i>
                        </div>
                        <h5>All Caught Up!</h5>
                        <p>No pending leave requests at the moment.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    feather.replace();
</script>
@endsection