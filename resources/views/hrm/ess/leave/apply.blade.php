@extends('hrm.ess.layouts.app')

@section('page-title', 'Apply for Leave')
@section('page-subtitle', 'Submit a new leave request')

@section('styles')
<style>
    .apply-leave-container {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 32px;
    }

    .form-section {
        margin-bottom: 32px;
    }

    .form-section-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--ess-text);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid var(--ess-border);
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        padding-top: 24px;
        border-top: 1px solid var(--ess-border);
        margin-top: 32px;
    }

    .leave-sidebar {
        position: sticky;
        top: 100px;
    }

    .balance-list-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 0;
        border-bottom: 1px solid var(--ess-border-light);
    }

    .balance-list-item:last-child {
        border-bottom: none;
    }

    .balance-list-item-name {
        font-size: 14px;
        font-weight: 500;
        color: var(--ess-text);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .balance-list-item-days {
        font-size: 16px;
        font-weight: 700;
        color: var(--ess-primary);
    }

    .balance-list-item-meta {
        font-size: 12px;
        color: var(--ess-text-muted);
        margin-top: 4px;
    }

    .tips-card {
        margin-top: 20px;
    }

    .tips-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .tips-list li {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 0;
        font-size: 13px;
        color: var(--ess-text-secondary);
        border-bottom: 1px solid var(--ess-border-light);
    }

    .tips-list li:last-child {
        border-bottom: none;
    }

    .tips-list li svg {
        width: 16px;
        height: 16px;
        color: var(--ess-primary);
        flex-shrink: 0;
        margin-top: 2px;
    }

    .total-days-display {
        display: none;
        padding: 16px 20px;
        background: linear-gradient(135deg, var(--ess-primary-light) 0%, #e0e7ff 100%);
        border-radius: var(--ess-border-radius-xs);
        margin-top: 20px;
    }

    .total-days-display.show {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .total-days-label {
        font-size: 14px;
        font-weight: 500;
        color: var(--ess-text);
    }

    .total-days-value {
        font-size: 24px;
        font-weight: 800;
        color: var(--ess-primary);
    }

    @media (max-width: 1199px) {
        .apply-leave-container {
            grid-template-columns: 1fr;
        }

        .leave-sidebar {
            position: static;
        }
    }

    @media (max-width: 767px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="apply-leave-container">
    <!-- Main Form -->
    <div class="apply-leave-form">
        <div class="ess-card">
            <div class="ess-card-header">
                <h3 class="ess-card-title">
                    <i data-feather="calendar"></i>
                    Leave Application Form
                </h3>
            </div>
            <div class="ess-card-body">
                <form action="{{ route('ess.leave.store') }}" method="POST">
                    @csrf
                    
                    <div class="form-section">
                        <h4 class="form-section-title">Leave Details</h4>
                        
                        <div class="ess-form-group">
                            <label class="ess-form-label">
                                Leave Type <span class="required">*</span>
                            </label>
                            <select name="leave_type_id" id="leave_type_id" class="ess-form-control ess-form-select @error('leave_type_id') is-invalid @enderror" required>
                                <option value="">Select Leave Type</option>
                                @foreach($leaveBalances as $balance)
                                    <option value="{{ $balance['id'] }}" {{ old('leave_type_id') == $balance['id'] ? 'selected' : '' }}>
                                        {{ $balance['name'] }} ({{ $balance['available'] }} days available)
                                    </option>
                                @endforeach
                            </select>
                            @error('leave_type_id')
                                <div class="ess-invalid-feedback">
                                    <i data-feather="alert-circle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>
                        
                        <div class="form-row">
                            <div class="ess-form-group">
                                <label class="ess-form-label">
                                    Start Date <span class="required">*</span>
                                </label>
                                <input type="date" name="start_date" id="start_date" 
                                       class="ess-form-control @error('start_date') is-invalid @enderror"
                                       value="{{ old('start_date') }}"
                                       min="{{ date('Y-m-d') }}"
                                       required>
                                @error('start_date')
                                    <div class="ess-invalid-feedback">
                                        <i data-feather="alert-circle"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            <div class="ess-form-group">
                                <label class="ess-form-label">
                                    End Date <span class="required">*</span>
                                </label>
                                <input type="date" name="end_date" id="end_date" 
                                       class="ess-form-control @error('end_date') is-invalid @enderror"
                                       value="{{ old('end_date') }}"
                                       min="{{ date('Y-m-d') }}"
                                       required>
                                @error('end_date')
                                    <div class="ess-invalid-feedback">
                                        <i data-feather="alert-circle"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="total-days-display" id="total-days-display">
                            <span class="total-days-label">Total Leave Days</span>
                            <span class="total-days-value"><span id="total-days-count">0</span> days</span>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h4 class="form-section-title">Additional Information</h4>
                        
                        <div class="ess-form-group">
                            <label class="ess-form-label">
                                Reason for Leave <span class="required">*</span>
                            </label>
                            <textarea name="leave_reason" id="leave_reason" rows="4" 
                                      class="ess-form-control @error('leave_reason') is-invalid @enderror"
                                      placeholder="Please provide a reason for your leave request..."
                                      required>{{ old('leave_reason') }}</textarea>
                            @error('leave_reason')
                                <div class="ess-invalid-feedback">
                                    <i data-feather="alert-circle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="ess-btn ess-btn-rc-primary ess-btn-lg">
                            <i data-feather="send"></i> Submit Request
                        </button>
                        <a href="{{ route('ess.leave') }}" class="ess-btn ess-btn-outline ess-btn-lg">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="leave-sidebar">
        <!-- Leave Balances -->
        <div class="ess-card">
            <div class="ess-card-header">
                <h3 class="ess-card-title">
                    <i data-feather="pie-chart"></i>
                    Your Balances
                </h3>
            </div>
            <div class="ess-card-body">
                @forelse($leaveBalances as $balance)
                <div class="balance-list-item">
                    <div>
                        <div class="balance-list-item-name">
                            {{ $balance['name'] }}
                            @if($balance['is_unpaid'])
                                <span class="ess-badge ess-badge-warning" style="font-size: 9px; padding: 2px 6px;">Unpaid</span>
                            @endif
                        </div>
                        <div class="balance-list-item-meta">Used: {{ $balance['used'] }} / {{ $balance['total'] }}</div>
                    </div>
                    <span class="balance-list-item-days">{{ $balance['available'] }}</span>
                </div>
                @empty
                <p style="color: var(--ess-text-muted); text-align: center; padding: 20px;">
                    No leave entitlements found.
                </p>
                @endforelse
            </div>
        </div>
        
        <!-- Tips Card -->
        <div class="ess-card tips-card">
            <div class="ess-card-header">
                <h3 class="ess-card-title">
                    <i data-feather="info"></i>
                    Tips
                </h3>
            </div>
            <div class="ess-card-body">
                <ul class="tips-list">
                    <li>
                        <i data-feather="check-circle"></i>
                        <span>Submit leave requests at least 24 hours in advance</span>
                    </li>
                    <li>
                        <i data-feather="check-circle"></i>
                        <span>Ensure you have sufficient leave balance</span>
                    </li>
                    <li>
                        <i data-feather="check-circle"></i>
                        <span>Future leave can be cancelled before the start date</span>
                    </li>
                    <li>
                        <i data-feather="check-circle"></i>
                        <span>Leave is recorded immediately upon submission</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const totalDaysDisplay = document.getElementById('total-days-display');
    const totalDaysCount = document.getElementById('total-days-count');
    
    function calculateDays() {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            
            if (end >= start) {
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                totalDaysCount.textContent = diffDays;
                totalDaysDisplay.classList.add('show');
            } else {
                totalDaysDisplay.classList.remove('show');
            }
        } else {
            totalDaysDisplay.classList.remove('show');
        }
    }
    
    startDateInput.addEventListener('change', function() {
        endDateInput.min = this.value;
        if (endDateInput.value && endDateInput.value < this.value) {
            endDateInput.value = this.value;
        }
        calculateDays();
    });
    
    endDateInput.addEventListener('change', calculateDays);
    
    // Initial calculation if values exist
    calculateDays();
    
    feather.replace();
});
</script>
@endsection
