@extends('layouts.main')
@section('page-title')
{{ __('Entitlement Policies') }}
@endsection

@section('page-breadcrumb')
{{ __('Leave Management') }}, {{ __('Entitlement Policies') }}
@endsection

@section('page-action')
<div>
    <a href="{{ route('hrm.leave-management.index') }}" class="btn btn-sm btn-rc-icon"
        data-bs-toggle="tooltip" data-bs-original-title="{{ __('Back to Leave Management') }}">
        <i class="ti ti-arrow-left text-white"></i>
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        @if (Auth::check())
        <div class="card mb-4">
            <div class="card-header bg-white border d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Leave Details</h4>
                <a href="{{ route('hrm.leave-management.edit', $leave->id) }}" class="btn btn-sm btn-rc-primary">
                    <i class="ti ti-edit"></i> {{ __('Edit') }}
                </a>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Name:</strong> {{ $leave->leave_name }}</div>
                    <div class="col-md-4"><strong>Cycle Length:</strong> {{ $leave->cycle_length }}</div>
                    <div class="col-md-4"><strong>Cycle Start Date:</strong> {{ $leave->cycle_start_type }}</div>
                </div>
                <div class="row mb-2 mt-2">
                    <div class="col-md-4"><strong>Unpaid:</strong> {{ $leave->is_unpaid ? 'Yes' : 'No' }}</div>
                    <div class="col-md-4"><strong>Set Minimum Balance Rule:</strong>
                        {{ $leave->set_min_balance ? 'Yes' : 'No' }}
                    </div>
                    <div class="col-md-4"><strong>Minimum Balance:</strong> {{ $leave->minimum_balance }}</div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-4"><strong>Allow Rule Override:</strong> {{ $leave->allow_rule_override }}</div>
                </div>
            </div>
        </div>

        {{-- Available Entitlement Policies --}}
        <x-rc-table title="{{ __('Available Entitlement Policies') }}">
            <x-slot name="headerActions">
                <a href="{{ route('entitlement-policies.create', ['leave' => $leave->id]) }}" class="btn btn-sm btn-rc-primary">
                    <i class="ti ti-plus"></i> {{ __('Create Entitlement Policy') }}
                </a>
            </x-slot>

            <x-rc-table.content>
                <table class="rc-table">
                    <thead>
                        <tr>
                            <th class="col-sno">{{ __('S.No') }}</th>
                            <th>{{ __('Entitlement Days') }}</th>
                            <th>{{ __('Custom Name') }}</th>
                            <th class="col-actions">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($policies as $policy)
                        <tr>
                            <td class="col-sno">{{ $loop->iteration }}</td>
                            <td class="font-style">{{ $policy->default_entitlement }} {{ __('Days') }}</td>
                            <td>{{ $policy->custom_name ?? '-' }}</td>
                            <td class="col-actions">
                                <a href="{{ route('entitlement-policies.edit', $policy->id) }}"
                                    class="rc-table-action rc-table-action-view"
                                    title="{{ __('View') }}">
                                    <i class="ti ti-eye"></i>
                                </a>
                                <form action="{{ route('entitlement-policies.destroy', $policy->id) }}" method="POST"
                                    class="d-inline"
                                    data-confirm-message="{{ __('Are you sure you want to delete this policy?') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rc-table-action rc-table-action-delete show_confirm"
                                        title="{{ __('Delete') }}"
                                        data-confirm="{{ __('Are You Sure?') }}"
                                        data-text="{{ __('This action can not be undone. Do you want to continue?') }}">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <x-rc-table.empty :asRow="true" :colspan="4" icon="ti ti-file-off"
                            title="{{ __('No Entitlement Policies') }}"
                            message="{{ __('No entitlement policies found for this leave module.') }}" />
                        @endforelse
                    </tbody>
                </table>
            </x-rc-table.content>
        </x-rc-table>
        {{-- Entitlement Policy Ranges --}}
        <x-rc-table title="{{ __('Entitlement Policy Ranges') }}" class="mt-4">
            <x-slot name="headerActions">
                <button type="button" id="showAddFormBtn" class="btn btn-sm btn-rc-primary">
                    <i class="ti ti-plus"></i> {{ __('Add Range') }}
                </button>
            </x-slot>

            <x-rc-table.content>
                <table class="rc-table">
                    <thead>
                        <tr>
                            <th>{{ __('Effective From') }}</th>
                            <th>{{ __('Effective Until') }}</th>
                            <th>{{ __('Entitlement Policy') }}</th>
                            <th class="col-actions">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ranges as $range)
                        <tr>
                            <td>{{ $range->start_date ? formatDate($range->start_date) : '-' }}</td>
                            <td>{{ $range->end_date ? formatDate($range->end_date) : '-' }}</td>
                            <td>
                                @php
                                $policy = $policies->firstWhere('id', $range->entitlement_policy_id);
                                @endphp
                                {{ $policy ? $policy->default_entitlement . ' ' . __('days') : '-' }}
                            </td>
                            <td class="col-actions">
                                <button type="button" class="rc-table-action rc-table-action-edit edit-range-btn"
                                    data-range-id="{{ $range->id }}"
                                    data-start="{{ $range->start_date }}"
                                    data-end="{{ $range->end_date }}"
                                    data-policy="{{ $range->entitlement_policy_id }}"
                                    title="{{ __('Edit') }}">
                                    <i class="ti ti-edit"></i>
                                </button>
                                <form action="{{ route('entitlement-policies.delete-range') }}" method="POST"
                                    class="d-inline"
                                    data-confirm-message="{{ __('Are you sure you want to delete this range?') }}">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $range->id }}">
                                    <button type="submit" class="rc-table-action rc-table-action-delete show_confirm"
                                        title="{{ __('Delete') }}"
                                        data-confirm="{{ __('Are You Sure?') }}"
                                        data-text="{{ __('This action can not be undone. Do you want to continue?') }}">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <x-rc-table.empty :asRow="true" :colspan="4" icon="ti ti-calendar-off"
                            title="{{ __('No Policy Ranges') }}"
                            message="{{ __('No policy ranges have been configured yet.') }}" />
                        @endforelse
                    </tbody>
                </table>
            </x-rc-table.content>
        </x-rc-table>

        {{-- Add/Edit Range Form (Hidden by default) --}}
        <div id="rangeFormContainer" class="card mt-3" style="display: none;">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0 text-white" id="formTitle">{{ __('Add Policy Range') }}</h6>
            </div>
            <div class="card-body">
                <form id="rangeForm" action="{{ route('entitlement-policies.create-range') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="range_id">
                    <input type="hidden" name="leave_id" value="{{ $leave->id }}">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Effective From') }} <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Effective Until') }} <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="end_date" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Entitlement Policy') }} <span class="text-danger">*</span></label>
                            <select name="entitlement_policy_id" id="entitlement_policy_id" class="form-control" required>
                                <option value="">{{ __('Select Policy') }}</option>
                                @foreach ($policies as $policy)
                                <option value="{{ $policy->id }}">{{ $policy->default_entitlement }} {{ __('days') }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-rc-primary">
                            <span id="submitBtnText">{{ __('Add') }}</span>
                        </button>
                        <button type="button" class="btn btn-rc-outline" id="cancelRangeBtn">
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>


        @else
        <div class="alert alert-danger">{{ __('You must be logged in to access this page.') }}</div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const showAddFormBtn = document.getElementById('showAddFormBtn');
        const rangeFormContainer = document.getElementById('rangeFormContainer');
        const cancelRangeBtn = document.getElementById('cancelRangeBtn');
        const rangeForm = document.getElementById('rangeForm');
        const formTitle = document.getElementById('formTitle');
        const submitBtnText = document.getElementById('submitBtnText');
        const rangeIdInput = document.getElementById('range_id');

        // Show Add Form
        if (showAddFormBtn) {
            showAddFormBtn.addEventListener('click', function() {
                rangeFormContainer.style.display = 'block';
                rangeForm.action = '{{ route("entitlement-policies.create-range") }}';
                formTitle.textContent = '{{ __("Add Policy Range") }}';
                submitBtnText.textContent = '{{ __("Add") }}';
                rangeIdInput.value = '';
                rangeForm.reset();
            });
        }

        // Cancel Form
        if (cancelRangeBtn) {
            cancelRangeBtn.addEventListener('click', function() {
                rangeFormContainer.style.display = 'none';
                rangeForm.reset();
            });
        }

        // Edit Range
        const editRangeBtns = document.querySelectorAll('.edit-range-btn');
        editRangeBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                const rangeId = this.getAttribute('data-range-id');
                const startDate = this.getAttribute('data-start');
                const endDate = this.getAttribute('data-end');
                const policyId = this.getAttribute('data-policy');

                rangeFormContainer.style.display = 'block';
                rangeForm.action = '{{ route("entitlement-policies.update-range") }}';
                formTitle.textContent = '{{ __("Edit Policy Range") }}';
                submitBtnText.textContent = '{{ __("Update") }}';

                rangeIdInput.value = rangeId;
                document.getElementById('start_date').value = startDate;
                document.getElementById('end_date').value = endDate;
                document.getElementById('entitlement_policy_id').value = policyId;
            });
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