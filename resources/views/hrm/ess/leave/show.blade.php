@extends('hrm.ess.layouts.app')

@section('page-title', 'Leave Request Details')
@section('page-subtitle', 'View leave request information')

@section('styles')
<style>
    .leave-detail-container {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 32px;
    }

    .status-banner {
        padding: 20px 24px;
        border-radius: var(--ess-border-radius);
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }

    .status-banner.pending {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border: 1px solid #fcd34d;
    }

    .status-banner.approved {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        border: 1px solid #6ee7b7;
    }

    .status-banner.rejected {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border: 1px solid #fca5a5;
    }

    .status-banner-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .status-banner.pending .status-banner-icon {
        background: rgba(245, 158, 11, 0.2);
        color: #b45309;
    }

    .status-banner.approved .status-banner-icon {
        background: rgba(16, 185, 129, 0.2);
        color: #059669;
    }

    .status-banner.rejected .status-banner-icon {
        background: rgba(239, 68, 68, 0.2);
        color: #dc2626;
    }

    .status-banner-icon svg {
        width: 28px;
        height: 28px;
    }

    .status-banner-text h4 {
        font-size: 18px;
        font-weight: 700;
        margin: 0 0 4px;
    }

    .status-banner.pending .status-banner-text h4 { color: #92400e; }
    .status-banner.approved .status-banner-text h4 { color: #065f46; }
    .status-banner.rejected .status-banner-text h4 { color: #991b1b; }

    .status-banner-text p {
        font-size: 13px;
        margin: 0;
    }

    .status-banner.pending .status-banner-text p { color: #b45309; }
    .status-banner.approved .status-banner-text p { color: #047857; }
    .status-banner.rejected .status-banner-text p { color: #b91c1c; }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
    }

    .detail-item {
        padding: 20px;
        background: var(--ess-bg);
        border-radius: var(--ess-border-radius-sm);
    }

    .detail-item-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--ess-text-muted);
        margin-bottom: 8px;
    }

    .detail-item-value {
        font-size: 16px;
        font-weight: 600;
        color: var(--ess-text);
    }

    .detail-item-value.primary {
        color: var(--ess-primary);
    }

    .detail-item.full {
        grid-column: span 2;
    }

    .reason-box {
        padding: 20px;
        background: var(--ess-bg);
        border-radius: var(--ess-border-radius-sm);
        font-size: 14px;
        color: var(--ess-text);
        line-height: 1.6;
    }

    .rejection-box {
        padding: 20px;
        background: var(--ess-danger-light);
        border-left: 4px solid var(--ess-danger);
        border-radius: var(--ess-border-radius-sm);
        font-size: 14px;
        color: #991b1b;
        line-height: 1.6;
    }

    .remark-box {
        padding: 20px;
        background: var(--ess-info-light);
        border-left: 4px solid var(--ess-info);
        border-radius: var(--ess-border-radius-sm);
        font-size: 14px;
        color: #1e40af;
        line-height: 1.6;
    }

    .detail-actions {
        display: flex;
        gap: 12px;
        padding-top: 24px;
        border-top: 1px solid var(--ess-border);
        margin-top: 24px;
    }

    /* Timeline Sidebar */
    .timeline-sidebar {
        position: sticky;
        top: 100px;
    }

    .timeline {
        position: relative;
        padding-left: 32px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 7px;
        top: 16px;
        bottom: 16px;
        width: 2px;
        background: var(--ess-border);
    }

    .timeline-item {
        position: relative;
        padding-bottom: 24px;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -32px;
        top: 4px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 3px solid var(--ess-card-bg);
        box-shadow: 0 0 0 3px var(--ess-border);
    }

    .timeline-item.completed::before {
        background: var(--ess-success);
        box-shadow: 0 0 0 3px var(--ess-success-light);
    }

    .timeline-item.pending::before {
        background: var(--ess-warning);
        box-shadow: 0 0 0 3px var(--ess-warning-light);
    }

    .timeline-item.rejected::before {
        background: var(--ess-danger);
        box-shadow: 0 0 0 3px var(--ess-danger-light);
    }

    .timeline-item-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--ess-text);
        margin: 0 0 4px;
    }

    .timeline-item-date {
        font-size: 12px;
        color: var(--ess-text-muted);
    }

    @media (max-width: 1199px) {
        .leave-detail-container {
            grid-template-columns: 1fr;
        }

        .timeline-sidebar {
            position: static;
        }
    }

    @media (max-width: 767px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }

        .detail-item.full {
            grid-column: span 1;
        }
    }
</style>
@endsection

@section('content')
@php
    $leaveNotStarted = \Carbon\Carbon::parse($leave->start_date)->startOfDay()->gt(\Carbon\Carbon::now()->startOfDay());
    $canCancel = in_array($leave->status, ['Pending', 'Approved']) && $leaveNotStarted;
    
    $statusClass = 'pending';
    if ($leave->status === 'Approved') $statusClass = 'approved';
    elseif ($leave->status === 'Rejected') $statusClass = 'rejected';
@endphp

<div class="leave-detail-container">
    <!-- Main Content -->
    <div class="leave-detail-main">
        <!-- Status Banner -->
        <div class="status-banner {{ $statusClass }}">
            <div class="status-banner-icon">
                @if($leave->status === 'Pending')
                    <i data-feather="clock"></i>
                @elseif($leave->status === 'Approved')
                    <i data-feather="check-circle"></i>
                @else
                    <i data-feather="x-circle"></i>
                @endif
            </div>
            <div class="status-banner-text">
                @if($leave->status === 'Pending')
                    <h4>Pending Review</h4>
                    <p>Your request is being reviewed by HR</p>
                @elseif($leave->status === 'Approved')
                    <h4>Leave Approved</h4>
                    <p>Your leave request has been approved</p>
                @else
                    <h4>Leave Rejected</h4>
                    <p>Your leave request was not approved</p>
                @endif
            </div>
        </div>

        <!-- Leave Details Card -->
        <div class="ess-card">
            <div class="ess-card-header">
                <h3 class="ess-card-title">
                    <i data-feather="calendar"></i>
                    Leave Details
                </h3>
            </div>
            <div class="ess-card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-item-label">Leave Type</div>
                        <div class="detail-item-value">{{ $leaveType->leave_name ?? 'N/A' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-item-label">Total Days</div>
                        <div class="detail-item-value primary">{{ $leave->total_leave_days }} day(s)</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-item-label">Start Date</div>
                        <div class="detail-item-value">{{ formatDate($leave->start_date) }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-item-label">End Date</div>
                        <div class="detail-item-value">{{ formatDate($leave->end_date) }}</div>
                    </div>
                    <div class="detail-item full">
                        <div class="detail-item-label">Reason for Leave</div>
                        <div class="reason-box">{{ $leave->leave_reason }}</div>
                    </div>
                    
                    @if(!empty($leave->remark))
                    <div class="detail-item full">
                        <div class="detail-item-label">HR Remark</div>
                        <div class="remark-box">{{ $leave->remark }}</div>
                    </div>
                    @endif
                    
                    @if($leave->status === 'Rejected' && $leave->rejection_reason)
                    <div class="detail-item full">
                        <div class="detail-item-label">Rejection Reason</div>
                        <div class="rejection-box">{{ $leave->rejection_reason }}</div>
                    </div>
                    @endif
                </div>
                
                <div class="detail-actions">
                    <a href="{{ route('ess.leave') }}" class="ess-btn ess-btn-outline">
                        <i data-feather="arrow-left"></i> Back to Leave
                    </a>
                    @if($canCancel)
                    <form action="{{ route('ess.leave.cancel', $leave->id) }}" method="POST" style="display: inline;" data-confirm-message="Are you sure you want to cancel this leave request?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="ess-btn ess-btn-danger">
                            <i data-feather="x"></i> Cancel Request
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Timeline Sidebar -->
    <div class="timeline-sidebar">
        <div class="ess-card">
            <div class="ess-card-header">
                <h3 class="ess-card-title">
                    <i data-feather="clock"></i>
                    Request Timeline
                </h3>
            </div>
            <div class="ess-card-body">
                <div class="timeline">
                    <!-- Submitted -->
                    <div class="timeline-item completed">
                        <div class="timeline-item-title">Request Submitted</div>
                        <div class="timeline-item-date">
                            {{ formatDateTime($leave->created_at) }}
                        </div>
                    </div>
                    
                    <!-- Status -->
                    @if($leave->status === 'Pending')
                        <div class="timeline-item pending">
                            <div class="timeline-item-title">Awaiting Approval</div>
                            <div class="timeline-item-date">Your request is being reviewed by HR</div>
                        </div>
                    @elseif($leave->status === 'Approved')
                        <div class="timeline-item completed">
                            <div class="timeline-item-title">Request Approved</div>
                            <div class="timeline-item-date">
                                @if($leave->approved_at)
                                    {{ formatDateTime($leave->approved_at) }}
                                @else
                                    {{ formatDateTime($leave->updated_at) }}
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="timeline-item rejected">
                            <div class="timeline-item-title">Request Rejected</div>
                            <div class="timeline-item-date">
                                @if($leave->approved_at)
                                    {{ formatDateTime($leave->approved_at) }}
                                @else
                                    {{ formatDateTime($leave->updated_at) }}
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Quick Info -->
        <div class="ess-card" style="margin-top: 20px;">
            <div class="ess-card-header">
                <h3 class="ess-card-title">
                    <i data-feather="info"></i>
                    Quick Info
                </h3>
            </div>
            <div class="ess-card-body">
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div>
                        <div style="font-size: 12px; color: var(--ess-text-muted); margin-bottom: 4px;">Applied On</div>
                        <div style="font-size: 14px; font-weight: 600; color: var(--ess-text);">
                            {{ formatDate($leave->applied_on) }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: var(--ess-text-muted); margin-bottom: 4px;">Current Status</div>
                        <div>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
