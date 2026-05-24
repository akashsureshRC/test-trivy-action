@extends('layouts.main')
@section('page-title')
{{ __('Create Entitlement Policy') }}
@endsection

@section('page-breadcrumb')
{{ __('Leave Management') }}, {{ __('Entitlement Policies') }}, {{ __('Create') }}
@endsection

@section('page-action')
<div>
    <a href="{{ route('entitlement-policies.index', ['leave' => $leaveManagement->id ?? '']) }}" class="btn btn-sm btn-rc-icon"
        data-bs-toggle="tooltip" data-bs-original-title="{{ __('Back to Entitlement Policies') }}">
        <i class="ti ti-arrow-left text-white"></i>
    </a>
</div>
@endsection

@section('content')
@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
<div class="row">
    <div class="col-sm-12">
        @if (Auth::check())
            <form action="{{ route('entitlement-policies.store') }}" method="POST">
                @csrf

                <div class="row">
                    {{-- Left Column: Leave Type & Accrual Settings --}}
                    <div class="col-lg-6">
                        <div class="card">
                            <h5 class="card-header">{{ __('Leave Type & Accrual') }}</h5>
                            <div class="card-body">
                                {{-- Leave Type (read-only) --}}
                                <div class="form-group mb-3">
                                    <label for="leave_management_id" class="form-label">{{ __('Leave Type') }}</label>
                                    <input type="text" class="form-control" value="{{ $leaveManagement ? $leaveManagement->leave_name : '' }}" readonly>
                                    <input type="hidden" name="leave_management_id" id="leave_management_id" value="{{ $leaveManagement ? $leaveManagement->id : '' }}">
                                    @error('leave_management_id')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Use Hours Worked For Accrual --}}
                                <div class="form-group mb-3">
                                    <div class="form-check">
                                        <input type="hidden" name="use_hours_worked" value="0">
                                        <input type="checkbox" class="form-check-input" id="use_hours_worked" name="use_hours_worked" value="1"
                                            {{ old('use_hours_worked') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="use_hours_worked">
                                            {{ __('Use hours worked for accrual (casual staff)') }}
                                        </label>
                                    </div>

                                    <div id="hours_worked_section" class="mt-2 ps-4" style="{{ old('use_hours_worked') ? '' : 'display:none;' }}">
                                        <div class="form-group mb-2">
                                            <label class="form-label">{{ __('Hours per leave day') }}</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="text-muted">{{ __('1 hour of leave for every') }}</span>
                                                <input type="number" name="hours_per_leave"
                                                    class="form-control @error('hours_per_leave') is-invalid @enderror"
                                                    style="width: 100px;" placeholder="e.g. 17"
                                                    value="{{ old('hours_per_leave') }}">
                                                <span class="text-muted">{{ __('hours worked') }}</span>
                                            </div>
                                            @error('hours_per_leave')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-check">
                                            <input type="hidden" name="paid_leave_contributes" value="0">
                                            <input type="checkbox" class="form-check-input" name="paid_leave_contributes" id="paid_leave_contributes" value="1"
                                                {{ old('paid_leave_contributes') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="paid_leave_contributes">
                                                {{ __('Paid leave contributes to hours worked for accrual?') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                {{-- Default Entitlement (hidden when hours worked is checked) --}}
                                <div id="standard_accrual_section" style="{{ old('use_hours_worked') ? 'display:none;' : '' }}">
                                    <div class="form-group mb-3">
                                        <label for="default_entitlement" class="form-label">{{ __('Default entitlement') }}</label>
                                        <input type="number" name="default_entitlement" id="default_entitlement" step="any"
                                            class="form-control @error('default_entitlement') is-invalid @enderror"
                                            placeholder="{{ __('Days') }}" value="{{ old('default_entitlement') }}">
                                        @error('default_entitlement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="entitlement_after_months" class="form-label">{{ __('Entitlement only available after') }}</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="number" name="entitlement_after_months" id="entitlement_after_months"
                                                class="form-control @error('entitlement_after_months') is-invalid @enderror"
                                                style="width: 100px;" placeholder="{{ __('Months') }}"
                                                value="{{ old('entitlement_after_months') }}">
                                            <span class="text-muted">{{ __('months') }}</span>
                                        </div>
                                        @error('entitlement_after_months')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-3">
                                        <div class="form-check">
                                            <input type="hidden" name="use_upfront_accrual" value="0">
                                            <input type="checkbox" class="form-check-input" name="use_upfront_accrual" id="use_upfront_accrual" value="1"
                                                {{ old('use_upfront_accrual') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="use_upfront_accrual">{{ __('Use upfront accrual?') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Custom Name & Carry Forward --}}
                    <div class="col-lg-6">
                        <div class="card">
                            <h5 class="card-header">{{ __('Naming & Carry Forward') }}</h5>
                            <div class="card-body">
                                {{-- Use Custom Name --}}
                                <div class="form-group mb-3">
                                    <div class="form-check">
                                        <input type="hidden" name="use_custom_name" value="0">
                                        <input type="checkbox" class="form-check-input" id="use_custom_name" name="use_custom_name" value="1"
                                            {{ old('use_custom_name') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="use_custom_name">{{ __('Use custom name?') }}</label>
                                    </div>
                                    <div id="custom_name_wrapper" class="mt-2 ps-4" style="{{ old('use_custom_name') ? '' : 'display:none;' }}">
                                        <input type="text" id="custom_name_input" name="custom_name"
                                            class="form-control @error('custom_name') is-invalid @enderror"
                                            placeholder="{{ __('Custom name') }}" value="{{ old('custom_name') }}"
                                            {{ old('use_custom_name') ? 'required' : '' }}>
                                        @error('custom_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <hr>

                                {{-- Carry Forward --}}
                                <div class="form-group mb-3">
                                    <div class="form-check">
                                        <input type="hidden" name="allow_carry_forward" value="0">
                                        <input type="checkbox" class="form-check-input" id="allow_carry_forward" name="allow_carry_forward" value="1"
                                            {{ old('allow_carry_forward') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="allow_carry_forward">
                                            {{ __('Allow leave to be carried forward to next cycle?') }}
                                        </label>
                                    </div>

                                    <div id="carry_forward_section" class="mt-3 ps-4" style="{{ old('allow_carry_forward') ? '' : 'display:none;' }}">
                                        <div class="form-group mb-3">
                                            <label class="form-label" for="carry_forward_expiry_months">{{ __('Carry forward expiry') }}</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="number" name="carry_forward_expiry_months" id="carry_forward_expiry_months"
                                                    class="form-control @error('carry_forward_expiry_months') is-invalid @enderror"
                                                    style="width: 100px;" placeholder="{{ __('Months') }}"
                                                    value="{{ old('carry_forward_expiry_months') }}">
                                                <span class="text-muted">{{ __('months') }}</span>
                                            </div>
                                            @error('carry_forward_expiry_months')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="limit_type" class="form-label">{{ __('Limit Type') }}</label>
                                            <select name="limit_type" id="limit_type"
                                                class="form-control @error('limit_type') is-invalid @enderror">
                                                <option value="no" {{ old('limit_type', 'no') == 'no' ? 'selected' : '' }}>
                                                    {{ __('No limit') }}
                                                </option>
                                                <option value="percentage" {{ old('limit_type') == 'percentage' ? 'selected' : '' }}>
                                                    {{ __('Percentage of balance') }}
                                                </option>
                                                <option value="percentage_entitlement" {{ old('limit_type') == 'percentage_entitlement' ? 'selected' : '' }}>
                                                    {{ __('Percentage of entitlement') }}
                                                </option>
                                                <option value="fixed" {{ old('limit_type') == 'fixed' ? 'selected' : '' }}>
                                                    {{ __('Fixed number of days') }}
                                                </option>
                                            </select>
                                            @error('limit_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div id="limit_value_input" class="form-group mb-3" style="{{ in_array(old('limit_type'), ['percentage', 'percentage_entitlement', 'fixed']) ? '' : 'display:none;' }}">
                                            <label for="limit_value" class="form-label">{{ __('Value') }}</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="number" id="limit_value" name="limit_value"
                                                    class="form-control @error('limit_value') is-invalid @enderror"
                                                    style="width: 100px;" placeholder="e.g. 50"
                                                    value="{{ old('limit_value') }}">
                                                <span id="unitLabel" class="text-muted">
                                                    @if(in_array(old('limit_type'), ['percentage', 'percentage_entitlement']))
                                                    %
                                                    @elseif(old('limit_type') == 'fixed')
                                                    {{ __('days') }}
                                                    @endif
                                                </span>
                                            </div>
                                            @error('limit_value')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Cycle Specific Rules (full width) --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ __('Cycle Specific Rules') }}</h5>
                        <button type="button" class="btn btn-rc-outline btn-sm" id="addRuleBtn">
                            <i class="ti ti-plus"></i> {{ __('Add Rule') }}
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="noRulesMessage" class="text-center py-4 {{ old('first_cycle') && count(old('first_cycle')) > 0 ? 'd-none' : '' }}">
                            <p class="text-muted mb-0">{{ __('No cycle specific rules defined. Click "Add Rule" to create one.') }}</p>
                        </div>
                        <div id="rulesContainer">
                            @if(old('first_cycle'))
                            @foreach(old('first_cycle') as $index => $first)
                            <div class="row mb-2 align-items-center rule-row">
                                <div class="col-md-3">
                                    <input type="number" name="first_cycle[]" class="form-control" value="{{ old('first_cycle')[$index] }}" placeholder="{{ __('First Cycle') }}">
                                    @error("first_cycle.$index") <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-3">
                                    <input type="number" name="last_cycle[]" class="form-control" value="{{ old('last_cycle')[$index] }}" placeholder="{{ __('Last Cycle') }}">
                                    @error("last_cycle.$index") <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-3">
                                    <input type="number" name="entitlement[]" class="form-control" value="{{ old('entitlement')[$index] }}" placeholder="{{ __('Entitlement') }}">
                                    @error("entitlement.$index") <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-rc-danger btn-sm remove-btn">
                                        <i class="ti ti-trash"></i> {{ __('Delete') }}
                                    </button>
                                </div>
                            </div>
                            @endforeach
                            @endif
                        </div>

                        <!-- <p class="text-muted mt-3 mb-0">
                            {{ __('Please see the help section for guidance in configuring these rules.') }}
                        </p> -->
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="card">
                    <div class="card-body d-flex justify-content-end gap-2">
                        <a href="{{ route('entitlement-policies.index', ['leave' => $leaveManagement->id ?? '']) }}" class="btn btn-rc-outline">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-rc-primary">
                            <i class="ti ti-device-floppy"></i> {{ __('Save') }}
                        </button>
                    </div>
                </div>
            </form>
        @else
            <div class="alert alert-danger">{{ __('You must be logged in to access this page.') }}</div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const customCheckbox = document.getElementById('use_custom_name');
        const customNameWrapper = document.getElementById('custom_name_wrapper');
        const useHoursWorked = document.getElementById('use_hours_worked');
        const hoursWorkedSection = document.getElementById('hours_worked_section');
        const standardAccrualSection = document.getElementById('standard_accrual_section');
        const carryForwardCheckbox = document.getElementById('allow_carry_forward');
        const carryForwardSection = document.getElementById('carry_forward_section');
        const limitType = document.getElementById('limit_type');
        const limitValueInput = document.getElementById('limit_value_input');
        const unitLabel = document.getElementById('unitLabel');

        function toggleCustomInput() {
            const isChecked = customCheckbox.checked;
            customNameWrapper.style.display = isChecked ? '' : 'none';
            document.getElementById('custom_name_input').required = isChecked;
        }

        function toggleHoursWorked() {
            const isChecked = useHoursWorked.checked;
            hoursWorkedSection.style.display = isChecked ? '' : 'none';
            standardAccrualSection.style.display = isChecked ? 'none' : '';
        }

        function toggleCarryForward() {
            carryForwardSection.style.display = carryForwardCheckbox.checked ? '' : 'none';
        }

        function toggleLimitValue() {
            const val = limitType.value;
            limitValueInput.style.display = val === 'no' ? 'none' : '';
            if (unitLabel) {
                if (val === 'fixed') unitLabel.textContent = '{{ __("days") }}';
                else if (val === 'percentage' || val === 'percentage_entitlement') unitLabel.textContent = '%';
                else unitLabel.textContent = '';
            }
        }

        function toggleNoRulesMessage() {
            const container = document.getElementById('rulesContainer');
            const message = document.getElementById('noRulesMessage');
            if (container.children.length === 0) {
                message.classList.remove('d-none');
            } else {
                message.classList.add('d-none');
            }
        }

        customCheckbox.addEventListener('change', toggleCustomInput);
        useHoursWorked.addEventListener('change', toggleHoursWorked);
        carryForwardCheckbox.addEventListener('change', toggleCarryForward);
        limitType.addEventListener('change', toggleLimitValue);

        // Initialize states on page load
        toggleCustomInput();
        toggleHoursWorked();
        toggleCarryForward();
        toggleLimitValue();
        toggleNoRulesMessage();

        // Cycle rules: Add
        document.getElementById('addRuleBtn').addEventListener('click', function() {
            const row = document.createElement('div');
            row.className = 'row mb-2 align-items-center rule-row';
            row.innerHTML = `
                    <div class="col-md-3">
                        <input type="number" name="first_cycle[]" class="form-control" placeholder="{{ __('First Cycle') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="last_cycle[]" class="form-control" placeholder="{{ __('Last Cycle') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="entitlement[]" class="form-control" placeholder="{{ __('Entitlement') }}">
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-rc-danger btn-sm remove-btn">
                            <i class="ti ti-trash"></i> {{ __('Delete') }}
                        </button>
                    </div>
                `;
            document.getElementById('rulesContainer').appendChild(row);
            toggleNoRulesMessage();
        });

        // Cycle rules: Remove (event delegation)
        document.getElementById('rulesContainer').addEventListener('click', function(e) {
            const btn = e.target.closest('.remove-btn');
            if (btn) {
                btn.closest('.rule-row').remove();
                toggleNoRulesMessage();
            }
        });
    });
</script>
<script>
    // Force Leave sidebar menu active for entitlement-policies pages
    document.addEventListener('DOMContentLoaded', function() {
        var sidebarLinks = document.querySelectorAll('.dash-sidebar .dash-navbar a.dash-link');
        sidebarLinks.forEach(function(link) {
            var href = link.getAttribute('href') || '';
            if (href.indexOf('employee-entitlement') !== -1) {
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