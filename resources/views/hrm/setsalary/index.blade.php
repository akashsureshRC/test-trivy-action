@extends('layouts.main')
@section('page-title')
    {{ __('Manage Employee Salary') }}
@endsection
@section('page-breadcrumb')
    {{ __('Employee Salary') }}
@endsection
@section('page-action')
    <div>
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <x-rc-table title="{{ __('Employee Salary') }}" titleIcon="ti ti-cash">
                <x-rc-table.content>
                    <table class="rc-table" id="assets">
                        <thead>
                            <tr>
                                <th class="col-id">{{ __('Employee Id') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Payroll Type') }}</th>
                                <th class="col-amount">{{ __('Salary') }}</th>
                                <th class="col-amount">{{ __('Net Salary') }}</th>
                                @if (Laratrust::hasPermission('setsalary edit'))
                                    <th class="col-actions" width="200px">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($employees as $employee)
                                <tr>
                                    <td class="col-id">
                                        @if (Laratrust::hasPermission('setsalary show'))
                                            <a href="{{ route('setsalary.show', $employee->id) }}" class="btn btn-outline-primary">
                                                {{ App\Services\PayrollHelperService::employeeIdFormat($employee->employee_id) }}
                                            </a>
                                        @else
                                            <a class="btn btn-outline-primary">
                                                {{ App\Services\PayrollHelperService::employeeIdFormat($employee->employee_id) }}
                                            </a>
                                        @endif
                                    </td>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ !empty($employee->salary_type) ? $employee->salaryType->name ?? '--' : '--' }}</td>
                                    <td class="col-amount">{{ currencyFormat($employee->salary) }}</td>
                                    <td class="col-amount">{{ !empty($employee->get_net_salary()) ? currencyFormat($employee->get_net_salary()) : '--' }}</td>
                                    @if (Laratrust::hasPermission('setsalary edit'))
                                        <td class="col-actions">
                                            @permission('setsalary edit')
                                                <a href="{{ route('setsalary.show', $employee->id) }}"
                                                    class="rc-table-action rc-table-action-view"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ __('View') }}">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                            @endpermission
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <x-rc-table.empty :asRow="true" :colspan="6" icon="ti ti-cash-off" title="{{ __('No Employee Salaries') }}" message="{{ __('No employee salary records found.') }}" />
                            @endforelse
                        </tbody>
                    </table>
                </x-rc-table.content>
            </x-rc-table>
        </div>
    </div>
@endsection
