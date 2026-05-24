@extends('layouts.main')
@section('page-title')
{{ __('Employees') }}
@endsection
@section('page-breadcrumb')
{{ __('Employees') }}
@endsection
@section('page-action')
<div>
    <a href="{{ route('employees.list') }}" class="btn btn-sm btn-rc-icon" data-bs-toggle="tooltip" title="{{ __('List View') }}">
        <i class="ti ti-list text-white"></i>
    </a>
    @permission('employee create')
    <a href="{{ route('employees.new') }}" class="btn btn-sm btn-rc-icon" data-bs-toggle="tooltip" title="{{ __('Create') }}">
        <i class="ti ti-plus text-white"></i>
    </a>
    @endpermission
</div>
@endsection
@section('content')
<div class="row">
    <div class="col-sm-12">
        <x-rc-table class="mb-4">
            <x-rc-table.filter action="{{ route('employees.grid') }}">
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
        </x-rc-table>

        <div class="row g-3">
            @foreach ($employees as $employee)
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="card employee-grid-card">
                    <div class="employee-grid-status">
                        <span class="rc-status {{ $employee->status == 'Active' ? 'rc-status-success' : 'rc-status-danger' }}">
                            {{ __($employee->status) }}
                        </span>
                    </div>
                    <div class="employee-grid-actions">
                        <div class="dropdown">
                            <button type="button" class="btn grid-actions-btn" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="feather icon-more-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                @permission('employee show')
                                @if (!empty($employee->employee_id))
                                <a href="{{ route('employees.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}" class="dropdown-item">
                                    <i class="ti ti-eye"></i> {{ __('View') }}
                                </a>
                                @endif
                                @endpermission
                                <a href="{{ route('employees.modify', $employee->id) }}" class="dropdown-item">
                                    <i class="ti ti-pencil"></i> {{ __('Edit') }}
                                </a>
                                @if (!empty($employee->employee_id))
                                {!! Form::open(['method' => 'DELETE', 'route' => ['employees.destroy', $employee->id]]) !!}
                                <a href="#!" class="dropdown-item show_confirm">
                                    <i class="ti ti-trash"></i> {{ __('Delete') }}
                                </a>
                                {!! Form::close() !!}
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="employee-avatar-wrapper">
                            @php
                            $avatarUrl = getAvatarUrl($employee->avatar);
                            $initials = strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1));
                            $fullName = $employee->first_name . ' ' . $employee->last_name;
                            @endphp
                            @if (!empty($avatarUrl))
                            <img src="{{ $avatarUrl }}" alt="{{ $fullName }}" class="employee-avatar">
                            @else
                            <div class="employee-avatar-placeholder">{{ $initials }}</div>
                            @endif
                        </div>
                        <div class="employee-grid-name">
                            @permission('employee show')
                            @if (!empty($employee->employee_id))
                            <a href="{{ route('employees.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}">{{ $fullName }}</a>
                            @else
                            {{ $fullName }}
                            @endif
                            @else
                            {{ $fullName }}
                            @endpermission
                        </div>
                        @if (!empty($employee->employee_id))
                        <div class="employee-grid-id">
                            {{ App\Models\Hrm\Employee::employeeIdFormat($employee->employee_id) }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
            @permission('employee create')
            <div class="col-xl-3 col-lg-4 col-md-6">
                <a href="{{ route('employees.new') }}" class="employee-add-card">
                    <div class="add-icon">
                        <i class="ti ti-plus"></i>
                    </div>
                    <h6>New Employee</h6>
                    <p>Click here to add new employee</p>
                </a>
            </div>
            @endpermission
        </div>
    </div>
</div>
@endsection