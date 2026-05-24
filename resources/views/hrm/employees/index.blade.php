@extends('layouts.main')

@section('page-title')
{{ __('Employees') }}
@endsection

@section('page-breadcrumb')
{{ __('Employees') }}
@endsection

@section('page-action')
<div>
    @stack('addButtonHook')
    <a href="{{ route('employees.grid') }}" class="btn btn-sm btn-rc-icon"
        data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Grid View') }}">
        <i class="ti ti-layout-grid text-white"></i>
    </a>
    @permission('employee create')
    <a href="{{ route('employees.new') }}" class="btn btn-sm btn-rc-icon"
        data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Create') }}">
        <i class="ti ti-plus text-white"></i>
    </a>
    @endpermission
</div>
@endsection

@php
$company_settings = getCompanyAllSetting();
@endphp

@section('content')
<div class="row">
    <div class="col-sm-12">
        <x-rc-table>
            <x-rc-table.filter action="{{ route('employees.list') }}">
                <x-rc-table.filter-group label="{{ __('Search') }}" wide>
                    <input type="text" name="search" class="rc-filter-input" placeholder="{{ __('Name, ID, email, phone...') }}" value="{{ request('search') }}">
                </x-rc-table.filter-group>
                <x-rc-table.filter-group label="{{ __('Status') }}" narrow>
                    <select name="status" class="rc-filter-select">
                        <option value="">{{ __('All') }}</option>
                        <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="Inactive" {{ request('status') == 'Inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                    </select>
                </x-rc-table.filter-group>
            </x-rc-table.filter>

            <x-rc-table.content>
                <table class="rc-table" id="employees-table">
                    <thead>
                        <tr>
                            <th class="col-sno">{{ __('S.No') }}</th>
                            <th class="col-id">{{ __('Employee ID') }}</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Phone') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th class="col-status">{{ __('Status') }}</th>
                            <th class="col-actions">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                        <tr>
                            <td class="col-sno">{{ $employees->firstItem() + $loop->index }}</td>
                            <td class="col-id">
                                <a href="{{ route('employees.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}"
                                    class="">
                                    {{ $employee->employee_id }}
                                </a>
                            </td>
                            <td>{{ trim($employee->first_name . ' ' . $employee->last_name) }}</td>
                            <td>{{ $employee->phone_number }}</td>
                            <td>{{ $employee->email }}</td>
                            <td class="col-status">
                                <label class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input toggle-status" type="checkbox"
                                        {{ $employee->status == "Active" ? 'checked' : '' }}
                                        data-id="{{ $employee->id }}" />
                                    <span class="form-check-label {{ $employee->status == 'Active' ? 'text-success' : 'text-danger' }}">
                                        {{ $employee->status }}
                                    </span>
                                </label>
                            </td>
                            <td class="col-actions">
                                @php
                                $latestTerm = \App\Models\Hrm\PaySlip::where('employee_id', $employee->id)->latest('id')->value('salary_month');
                                if (is_null($latestTerm)) {
                                $latestTerm = \Carbon\Carbon::parse($employee->date_of_appointment)->endOfMonth()->format('Y-m-d');
                                }
                                @endphp
                                <a href="{{ route('employees.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}"
                                    class="rc-table-action rc-table-action-view" data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('View') }}">
                                    <i class="ti ti-eye"></i>
                                </a>
                                <a href="{{ route('payroll.index', ['employee_id' => $employee->id, 'term' => $latestTerm]) }}"
                                    class="rc-table-action rc-table-action-view" data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('Payslip') }}">
                                    <i class="ti ti-receipt"></i>
                                </a>
                                <a href="{{ route('employees.modify', $employee->id) }}"
                                    class="rc-table-action rc-table-action-edit" data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('Edit') }}">
                                    <i class="ti ti-edit"></i>
                                </a>
                                {!! Form::open(['method' => 'DELETE', 'route' => ['employees.destroy', $employee->id], 'id' => 'delete-form-' . $employee->id, 'style' => 'display:inline']) !!}
                                <a href="#" class="rc-table-action rc-table-action-delete show_confirm"
                                    data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('Delete') }}">
                                    <i class="ti ti-trash"></i>
                                </a>
                                {!! Form::close() !!}
                            </td>
                        </tr>
                        @empty
                        <x-rc-table.empty :asRow="true" :colspan="7" icon="ti ti-users" title="{{ __('No Employees Found') }}" message="{{ __('There are no employees to display.') }}" />
                        @endforelse
                    </tbody>
                </table>
            </x-rc-table.content>

            <x-rc-table.footer :paginator="$employees" />
        </x-rc-table>
    </div>
</div>

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    document.querySelectorAll('.toggle-status').forEach(switchElement => {
        switchElement.addEventListener('change', function() {
            const isChecked = switchElement.checked;
            const employeeId = switchElement.dataset.id;
            const url = `/employees/status/${employeeId}`;

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        status: isChecked ? 'Active' : 'Inactive'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        const label = switchElement.nextElementSibling;
                        label.textContent = data.newStatus;
                        label.classList.toggle('text-success', isChecked);
                        label.classList.toggle('text-danger', !isChecked);
                        toastr.success("Employee status updated successfully!");
                    } else {
                        switchElement.checked = !isChecked;
                        toastr.error("Failed to update status. Try again!");
                    }
                })
                .catch(error => {
                    switchElement.checked = !isChecked;
                    toastr.error("Something went wrong!");
                });
        });
    });
</script>
@endpush
@endsection