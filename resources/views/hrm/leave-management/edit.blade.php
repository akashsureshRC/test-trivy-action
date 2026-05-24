@extends('layouts.main')
@section('page-title')
    {{ __('Edit Leave Module') }}
@endsection

@section('page-breadcrumb')
    {{ __('Leave Management') }}, {{ __('Edit') }}
@endsection

@section('content')
    @if (Auth::check())
        <form method="POST" action="{{ route('hrm.leave-management.update', $leaveManagement->id) }}" class="mt-3">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-12 col-xs-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <!-- Leave Name -->
                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Leave Name') }}</label>
                                    <input type="text" name="leave_name"
                                        class="form-control @error('leave_name') is-invalid @enderror"
                                        value="{{ old('leave_name', $leaveManagement->leave_name) }}"
                                        placeholder="Enter Leave Name" onkeypress="blockNumbers(event)">
                                    @error('leave_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Cycle Length -->
                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Cycle Length (Months)') }}</label>
                                    <input type="number" name="cycle_length"
                                        class="form-control @error('cycle_length') is-invalid @enderror"
                                        value="{{ old('cycle_length', $leaveManagement->cycle_length) }}"
                                        placeholder="Enter number of months" min="1" step="1">
                                    @error('cycle_length')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Cycle Start Date -->
                                {{-- <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Cycle Start Date') }}</label>
                                    <select name="cycle_start_type" id="cycle_start_type" class="form-control @error('cycle_start_type') is-invalid @enderror">
                                        <option value="">Select Option</option>
                                        <option value="appointment" {{ old('cycle_start_type', $leaveManagement->cycle_start_type) == 'appointment' ? 'selected' : '' }}>Appointment Date</option>
                                        <option value="january" {{ old('cycle_start_type', $leaveManagement->cycle_start_type) == 'january' ? 'selected' : '' }}>01 January</option>
                                        <option value="custom" {{ old('cycle_start_type', $leaveManagement->cycle_start_type) == 'custom' ? 'selected' : '' }}>Custom</option>
                                    </select>
                                    @error('cycle_start_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div> --}}
                                <!-- Cycle Start Date -->
                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Cycle Start Date') }}</label>
                                    <select name="cycle_start_type" id="cycle_start_type"
                                        class="form-control @error('cycle_start_type') is-invalid @enderror">
                                        <option value="">Select Option</option>
                                        <option value="appointment"
                                            {{ old('cycle_start_type', $leaveManagement->cycle_start_type) == 'appointment' ? 'selected' : '' }}>
                                            Appointment Date</option>
                                        <option value="january"
                                            {{ old('cycle_start_type', $leaveManagement->cycle_start_type) == 'january' ? 'selected' : '' }}>
                                            01 January</option>
                                        <option value="custom"
                                            {{ old('cycle_start_type', $leaveManagement->cycle_start_type) == 'custom' ? 'selected' : '' }}>
                                            Custom</option>
                                    </select>
                                    @error('cycle_start_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Custom Date Picker (only shown if "custom" is selected) --}}
                                <div class="form-group col-md-6" id="custom_date_container" style="display: none;">
                                    <label class="form-label">Custom Date</label>
                                    <input type="date" name="custom_cycle_date" class="form-control"
                                        value="{{ old('custom_cycle_date', $leaveManagement->custom_cycle_date) }}">
                                </div>
                                <!-- Visible For -->
                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Visible For') }}</label>
                                    <select name="visible_for"
                                        class="form-control @error('visible_for') is-invalid @enderror">
                                        <option value="">Select Role</option>
                                        <option value="everyone"
                                            {{ old('visible_for', $leaveManagement->visible_for) == 'everyone' ? 'selected' : '' }}>
                                            Everyone</option>
                                        <option value="employees"
                                            {{ old('visible_for', $leaveManagement->visible_for) == 'employees' ? 'selected' : '' }}>
                                            Employees with entitlement</option>
                                    </select>
                                    @error('visible_for')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Unpaid Leave -->
                                <div class="form-group col-md-6">
                                    <div class="form-check">
                                        <input type="hidden" name="unpaid_leave" value="0">
                                        <input type="checkbox" class="form-check-input" id="unpaid_leave" name="unpaid_leave" value="1"
                                            {{ old('unpaid_leave', $leaveManagement->unpaid_leave) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="unpaid_leave">{{ __('Unpaid Leave') }}</label>
                                    </div>
                                </div>

                                <!-- Show on Payslip -->
                                <div class="form-group col-md-6">
                                    <div class="form-check">
                                        <input type="hidden" name="show_on_payslip" value="0">
                                        <input type="checkbox" class="form-check-input" id="show_on_payslip" name="show_on_payslip" value="1"
                                            {{ old('show_on_payslip', $leaveManagement->show_on_payslip) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_on_payslip">{{ __('Show on Payslips') }}</label>
                                    </div>
                                </div>

                                <!-- Show Leave Expiry to Employees -->
                                <div class="form-group col-md-6" id="leave_expiry_wrapper" style="display: none;">
                                    <div class="form-check">
                                        <input type="hidden" name="show_leave_expiry" value="0">
                                        <input type="checkbox" class="form-check-input" id="show_leave_expiry" name="show_leave_expiry" value="1"
                                            {{ old('show_leave_expiry', $leaveManagement->show_leave_expiry) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_leave_expiry">{{ __('Show Leave Expiry to Employees') }}</label>
                                    </div>
                                </div>

                                <!-- Set Minimum Balance Rule -->
                                <div class="form-group col-md-6">
                                    <div class="form-check">
                                        <input type="hidden" name="set_min_balance_rule" value="0">
                                        <input type="checkbox" class="form-check-input" id="set_min_balance_rule" name="set_min_balance_rule"
                                            value="1"
                                            {{ old('set_min_balance_rule', $leaveManagement->set_min_balance_rule) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="set_min_balance_rule">{{ __('Set Minimum Balance Rule') }}</label>
                                    </div>
                                </div>

                                <!-- Min Balance & Rule Override -->
                                <div id="min_balance_wrapper" class="row" style="display: none;">
                                    <div class="form-group col-md-6">
                                        <label class="form-label">{{ __('Minimum Balance') }}</label>
                                        <input type="number" step="0.01" name="minimum_balance" class="form-control"
                                            value="{{ old('minimum_balance', $leaveManagement->minimum_balance) }}">
                                        @error('minimum_balance')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="form-label">{{ __('Allow Rule Override') }}</label>
                                        <select name="allow_rule_override" class="form-control">
                                            <option value="not allowed"
                                                {{ old('allow_rule_override', $leaveManagement->allow_rule_override) == 'not allowed' ? 'selected' : '' }}>
                                                Not Allowed</option>
                                            <option value="admins"
                                                {{ old('allow_rule_override', $leaveManagement->allow_rule_override) == 'admins' ? 'selected' : '' }}>
                                                Admins & Leave Admins</option>
                                            <option value="approvers admin"
                                                {{ old('allow_rule_override', $leaveManagement->allow_rule_override) == 'approvers admin' ? 'selected' : '' }}>
                                                Approvers, Admins & Leave Admins</option>
                                        </select>
                                        @error('allow_rule_override')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Hide Balances -->
                                <div class="form-group col-md-6">
                                    <div class="form-check">
                                        <input type="hidden" name="hide_balances" value="0">
                                        <input type="checkbox" class="form-check-input" id="hide_balances" name="hide_balances" value="1"
                                            {{ old('hide_balances', $leaveManagement->hide_balances) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hide_balances">{{ __('Hide balances in Self-Service') }}</label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <a href="{{ route('hrm.leave-management.create') }}"
                                    class="btn btn-rc-outline">{{ __('Cancel') }}</a>
                                <button type="submit" class="btn btn-rc-primary">{{ __('Update') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @else
        <div class="alert alert-danger">{{ __('You must be logged in to access this page.') }}</div>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const showOnPayslip = document.getElementById('show_on_payslip');
            const leaveExpiryWrapper = document.getElementById('leave_expiry_wrapper');
            const setMinBalanceRule = document.getElementById('set_min_balance_rule');
            const minBalanceWrapper = document.getElementById('min_balance_wrapper');

            // Toggle leave expiry
            showOnPayslip.addEventListener('change', function() {
                leaveExpiryWrapper.style.display = this.checked ? 'block' : 'none';
            });

            // Toggle min balance
            setMinBalanceRule.addEventListener('change', function() {
                minBalanceWrapper.style.display = this.checked ? 'flex' : 'none';
            });

            // Show on page load
            if (showOnPayslip.checked) leaveExpiryWrapper.style.display = 'block';
            if (setMinBalanceRule.checked) minBalanceWrapper.style.display = 'flex';
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cycleTypeSelect = document.getElementById('cycle_start_type');
            const customDateContainer = document.getElementById('custom_date_container');

            function toggleCustomDate() {
                if (cycleTypeSelect.value === 'custom') {
                    customDateContainer.style.display = 'block';
                } else {
                    customDateContainer.style.display = 'none';
                }
            }

            // Run on load
            toggleCustomDate();

            // Run on change
            cycleTypeSelect.addEventListener('change', toggleCustomDate);
        });
    </script>
     <script>
    function blockNumbers(event) {
        const char = String.fromCharCode(event.which);
        const regex = /^[A-Za-z\s]+$/;
        if (!regex.test(char)) {
            event.preventDefault();
        }
    }
</script>
<script>
    // Force Leave sidebar menu active for leave-management pages
    document.addEventListener('DOMContentLoaded', function() {
        var sidebarLinks = document.querySelectorAll('.dash-sidebar .dash-navbar a.dash-link');
        sidebarLinks.forEach(function(link) {
            var href = link.getAttribute('href') || '';
            if (href.indexOf('leave-management') !== -1) {
                link.parentNode.classList.add('active');
                var parentLi = link.parentNode.parentNode.parentNode;
                if (parentLi) {
                    parentLi.classList.add('active');
                    parentLi.classList.add('dash-trigger');
                    var submenu = link.parentNode.parentNode;
                    if (submenu) submenu.style.display = 'block';
                }
            }
        });
    });
</script>
@endpush
