@extends('hrm.ess.layouts.app')

@section('page-title', 'Leave Requests')
@section('page-subtitle', 'View your leave balances and requests')

@section('header-actions')
@endsection

@section('styles')
<style>
    .leave-overview {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 20px;
        margin-bottom: 32px;
    }
    
    @media (max-width: 1400px) {
        .leave-overview {
            grid-template-columns: repeat(4, 1fr);
        }
    }
    
    @media (max-width: 1100px) {
        .leave-overview {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .leave-overview {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 480px) {
        .leave-overview {
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
        display: flex;
        align-items: baseline;
        gap: 8px;
        margin-bottom: 8px;
    }

    .leave-balance-days .number {
        font-size: 36px;
        font-weight: 800;
        color: var(--ess-text);
        letter-spacing: -2px;
        line-height: 1;
    }

    .leave-balance-days .unit {
        font-size: 14px;
        font-weight: 500;
        color: var(--ess-text-muted);
    }

    .leave-balance-meta {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        color: var(--ess-text-muted);
        margin-bottom: 16px;
    }

    .leave-balance-meta .pending {
        color: var(--ess-warning);
    }

    .leave-balance-progress {
        height: 6px;
        background: var(--ess-border);
        border-radius: 50px;
        overflow: hidden;
        margin-bottom: 12px;
    }

    .leave-balance-progress-bar {
        height: 100%;
        border-radius: 50px;
        transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .leave-balance-progress-bar.success {
        background: linear-gradient(90deg, #10b981 0%, #059669 100%);
    }

    .leave-balance-progress-bar.warning {
        background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
    }

    .leave-balance-progress-bar.danger {
        background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%);
    }

    .leave-balance-cycle {
        font-size: 11px;
        color: var(--ess-text-light);
    }

    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .section-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--ess-text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title svg {
        width: 22px;
        height: 22px;
        color: var(--ess-primary);
    }

    .leave-request-row {
        display: grid;
        grid-template-columns: 48px 1fr 80px 120px 220px;
        align-items: center;
        gap: 16px;
        padding: 20px 24px;
        border-bottom: 1px solid var(--ess-border-light);
        transition: var(--ess-transition);
    }

    .leave-request-row:hover {
        background: var(--ess-bg);
    }

    .leave-request-row:last-child {
        border-bottom: none;
    }

    .leave-request-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .leave-request-icon.pending {
        background: var(--ess-warning-light);
        color: var(--ess-warning);
    }

    .leave-request-icon.approved {
        background: var(--ess-success-light);
        color: var(--ess-success);
    }

    .leave-request-icon.rejected {
        background: var(--ess-danger-light);
        color: var(--ess-danger);
    }

    .leave-request-icon svg {
        width: 22px;
        height: 22px;
    }

    .leave-request-info {
        min-width: 0;
    }

    .leave-request-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--ess-text);
        margin: 0 0 4px;
    }

    .leave-request-dates {
        font-size: 13px;
        color: var(--ess-text-muted);
        margin: 0;
    }

    .leave-request-days {
        text-align: center;
    }

    .leave-request-days .number {
        font-size: 24px;
        font-weight: 700;
        color: var(--ess-text);
        line-height: 1.2;
    }

    .leave-request-days .label {
        font-size: 11px;
        color: var(--ess-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .leave-request-status {
        display: flex;
        justify-content: center;
    }

    .leave-request-actions {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }

    @media (max-width: 991px) {
        .leave-request-row {
            grid-template-columns: 48px 1fr auto;
            grid-template-rows: auto auto;
        }
        
        .leave-request-icon {
            grid-row: span 2;
        }
        
        .leave-request-info {
            grid-column: 2;
        }
        
        .leave-request-days {
            grid-column: 3;
            grid-row: span 2;
        }
        
        .leave-request-status {
            grid-column: 2;
            justify-content: flex-start;
        }
        
        .leave-request-actions {
            grid-column: 3;
            grid-row: 2;
        }
    }

    @media (max-width: 767px) {
        .leave-overview {
            grid-template-columns: 1fr;
        }
        
        .leave-request-row {
            grid-template-columns: 48px 1fr;
            gap: 12px;
        }
        
        .leave-request-icon {
            grid-row: 1;
        }
        
        .leave-request-info {
            grid-column: 2;
            grid-row: 1;
        }
        
        .leave-request-days {
            grid-column: 1 / -1;
            grid-row: 2;
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: flex-start;
            padding-left: 64px;
        }
        
        .leave-request-days .number {
            font-size: 18px;
        }
        
        .leave-request-status {
            grid-column: 1 / -1;
            grid-row: 3;
            justify-content: flex-start;
            padding-left: 64px;
        }
        
        .leave-request-actions {
            grid-column: 1 / -1;
            grid-row: 4;
            justify-content: flex-start;
            padding-left: 64px;
        }
    }
</style>
@endsection

@section('content')
<!-- Leave Balances Overview -->
@if(count($leaveBalances) > 0)
<div class="leave-overview">
    @foreach($leaveBalances as $index => $balance)
    @php
        $percentage = $balance['total'] > 0 ? (($balance['total'] - $balance['available']) / $balance['total']) * 100 : 0;
        $colorClass = $balance['available'] > 5 ? 'success' : ($balance['available'] > 0 ? 'warning' : 'danger');
    @endphp
    <div class="leave-balance-card {{ $colorClass }} animate-fade-in-up stagger-{{ $index + 1 }}">
        <div class="leave-balance-header">
            <span class="leave-balance-name">{{ $balance['name'] }}</span>
            @if($balance['is_unpaid'])
                <span class="ess-badge ess-badge-warning">Unpaid</span>
            @endif
        </div>
        <div class="leave-balance-days">
            <span class="number">{{ $balance['available'] }}</span>
            <span class="unit">days available</span>
        </div>
        <div class="leave-balance-meta">
            <span>Used: {{ $balance['used'] }} / {{ $balance['total'] }}</span>
            @if($balance['pending'] > 0)
                <span class="pending">Pending: {{ $balance['pending'] }}</span>
            @endif
        </div>
        <div class="leave-balance-progress">
            <div class="leave-balance-progress-bar {{ $colorClass }}" style="width: {{ $percentage }}%"></div>
        </div>
        <div class="leave-balance-cycle">
            Cycle: {{ $balance['cycle_start'] }} - {{ $balance['cycle_end'] }}
        </div>
    </div>
    @endforeach
</div>
@else
<div class="ess-card mb-4">
    <div class="ess-empty-state">
        <div class="ess-empty-state-icon" style="background: var(--ess-warning-light);">
            <i data-feather="alert-circle" style="color: var(--ess-warning);"></i>
        </div>
        <h4>No Leave Entitlements</h4>
        <p>You don't have any leave entitlements assigned yet. Please contact HR for assistance.</p>
    </div>
</div>
@endif

<!-- Leave Requests Section -->
<div class="section-header">
    <h3 class="section-title">
        <i data-feather="list"></i>
        Leave Requests
    </h3>
    @if(!empty($leaveBalances) && count($leaveBalances) > 0)
    <a href="{{ route('ess.leave.apply') }}" class="ess-btn ess-btn-rc-primary">
        <i data-feather="plus"></i> Apply for Leave
    </a>
    @endif
</div>

<div class="ess-card">
    @if($leaveRequests->count() > 0)
        @foreach($leaveRequests as $leave)
        @php
            $leaveType = \App\Models\Hrm\LeaveManagement::find($leave->leave_management_id);
            $leaveNotStarted = \Carbon\Carbon::parse($leave->start_date)->startOfDay()->gt(\Carbon\Carbon::now()->startOfDay());
            $canCancel = in_array($leave->status, ['Pending', 'Approved']) && $leaveNotStarted;
            
            $statusClass = 'pending';
            $statusIcon = 'clock';
            if ($leave->status === 'Approved') {
                $statusClass = 'approved';
                $statusIcon = 'check-circle';
            } elseif ($leave->status === 'Rejected') {
                $statusClass = 'rejected';
                $statusIcon = 'x-circle';
            }
        @endphp
        <div class="leave-request-row">
            <div class="leave-request-icon {{ $statusClass }}">
                <i data-feather="{{ $statusIcon }}"></i>
            </div>
            <div class="leave-request-info">
                <h4 class="leave-request-title">{{ $leaveType->leave_name ?? 'N/A' }}</h4>
                <p class="leave-request-dates">
                    {{ formatDayMonth($leave->start_date) }} - {{ formatDate($leave->end_date) }}
                    <span style="color: var(--ess-text-light); margin-left: 8px;">
                        Applied {{ formatDate($leave->applied_on) }}
                    </span>
                </p>
            </div>
            <div class="leave-request-days">
                <div class="number">{{ $leave->total_leave_days }}</div>
                <div class="label">{{ $leave->total_leave_days == 1 ? 'Day' : 'Days' }}</div>
            </div>
            <div class="leave-request-status">
                @if($leave->status === 'Pending')
                    <span class="ess-badge ess-badge-warning">
                        <i data-feather="clock"></i> Pending
                    </span>
                @elseif($leave->status === 'Approved')
                    <span class="ess-badge ess-badge-success">
                        <i data-feather="check"></i> Approved
                    </span>
                @else
                    <span class="ess-badge ess-badge-danger">
                        <i data-feather="x"></i> Rejected
                    </span>
                @endif
            </div>
            <div class="leave-request-actions">
                <a href="{{ route('ess.leave.show', $leave->id) }}" class="ess-btn ess-btn-sm ess-btn-outline">
                    <i data-feather="eye"></i> View
                </a>
                @if($canCancel)
                <form action="{{ route('ess.leave.cancel', $leave->id) }}" method="POST" style="display: inline;" data-confirm-message="Are you sure you want to cancel this leave request?">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="ess-btn ess-btn-sm ess-btn-danger">
                        <i data-feather="x"></i> Cancel
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    @else
        <div class="ess-empty-state">
            <div class="ess-empty-state-icon">
                <i data-feather="calendar"></i>
            </div>
            <h4>No Leave Requests</h4>
            <p>You haven't submitted any leave requests yet.</p>
        </div>
    @endif
</div>
@endsection
