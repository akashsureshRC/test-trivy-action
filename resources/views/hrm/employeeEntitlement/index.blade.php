@extends('layouts.main')

@section('page-title')
{{ __('Entitlement Policies') }}
@endsection

@section('page-breadcrumb')
{{ __('Entitlement Policies') }}
@endsection
@section('content')
<div class="row">
    <div class="col-sm-12">
        <x-rc-table>
            @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <x-rc-table.filter action="" method="GET">
                <x-rc-table.filter-group label="{{ __('Filter by Employee') }}">
                    <select name="employee_id" id="employee_id" class="form-control" onchange="this.form.submit()">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}"
                            {{ $selectedEmployee == $employee->id ? 'selected' : '' }}>
                            {{ $employee->full_name ?? $employee->id }}
                        </option>
                        @endforeach
                    </select>
                </x-rc-table.filter-group>
            </x-rc-table.filter>

            <x-rc-table.content>
                <table class="rc-table">
                    <thead>
                        <tr>
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('Leave Module') }}</th>
                            <th>{{ __('Custome Name') }}</th>
                            <th>{{ __('Entitlement Days') }}</th>
                            <th class="col-actions">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employeePolicy as $policy)
                        <tr>
                            <td>{{ $policy->employeeProfile->full_name ?? '-' }}</td>
                            <td>{{ $policy->leaveManagement->leave_name ?? '-' }}</td>
                            <td>{{ $policy->entitlementPolicy->custom_name ?? '-' }}</td>
                            <td>
                                {{ rtrim(rtrim(number_format((float) $policy->default_entitlement, 2, '.', ''), '0'), '.') }}
                            </td>
                            <td class="col-actions">
                                <a href="#" class="rc-table-action rc-table-action-edit" data-bs-toggle="modal"
                                    data-bs-target="#editEntitlementModal{{ $policy->id }}"
                                    data-bs-original-title="{{ __('Edit') }}">
                                    <i class="ti ti-edit"></i>
                                </a>
                                <!-- Modal -->
                                <div class="modal fade" id="editEntitlementModal{{ $policy->id }}" tabindex="-1"
                                    aria-labelledby="editEntitlementModalLabel{{ $policy->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form method="POST"
                                            action="{{ route('employee-entitlement.update', $policy->id) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"
                                                        id="editEntitlementModalLabel{{ $policy->id }}">
                                                        {{ __('Edit Entitlement') }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label>{{ __('Select Entitlement') }}</label>
                                                        <select name="entitlement_id"
                                                            class="form-control entitlement-select"
                                                            data-policy-id="{{ $policy->id }}" required>
                                                            @foreach (\App\Models\Hrm\EntitlementPolicy::where('leave_management_id', $policy->leave_management_id)->get() as $entitlement)
                                                            <option value="{{ $entitlement->id }}"
                                                                data-days="{{ $entitlement->default_entitlement }}"
                                                                {{ $policy->entitlement_id == $entitlement->id ? 'selected' : '' }}>
                                                                {{ $entitlement->custom_name ?? 'Entitlement Policy' }}
                                                                ({{ rtrim(rtrim(number_format($entitlement->default_entitlement, 2, '.', ''), '0'), '.') }}
                                                                days)
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label>{{ __('Entitlement Days') }} <span
                                                                class="text-danger">*</span></label>
                                                        <input type="number" step="0.01" name="default_entitlement"
                                                            id="default_entitlement_{{ $policy->id }}"
                                                            class="form-control"
                                                            value="{{ $policy->default_entitlement }}" readonly>
                                                        @error('default_entitlement')
                                                        <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-rc-outline"
                                                        data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                    <button type="submit"
                                                        class="btn btn-rc-primary">{{ __('Save') }}</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <x-rc-table.empty :asRow="true" :colspan="5" icon="ti ti-file-check" title="{{ __('No Entitlement Policies') }}" message="{{ __('No records found.') }}" />
                        @endforelse
                    </tbody>
                </table>
            </x-rc-table.content>

            <x-rc-table.footer :paginator="$employeePolicy" />
        </x-rc-table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const entitlementSelects = document.querySelectorAll('.entitlement-select');
        entitlementSelects.forEach(select => {
            select.addEventListener('change', function() {
                const policyId = this.getAttribute('data-policy-id');
                const selectedOption = this.options[this.selectedIndex];
                const defaultDays = selectedOption.getAttribute('data-days');

                document.getElementById('default_entitlement_' + policyId).value = defaultDays;
            });
        });
    });
</script>
@if ($errors->any())
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var modal = new bootstrap.Modal(document.getElementById("editEntitlementModal{{ $policy->id }}"));
        modal.show();
    });
</script>
@endif

@if (session('success'))
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var modalEl = document.getElementById("editEntitlementModal{{ $policy->id }}");
        var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.hide();
    });
</script>
@endif
@endpush