@extends('layouts.main')
@section('page-title')
    {{ __('Employee Set Salary') }}
@endsection
@section('page-breadcrumb')
    {{ __('Employee Set Salary') }}
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('Modules/Hrm/Resources/assets/css/custom.css') }}">
@endpush
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="row">
                <div class="col-xl-6">
                    <div class="card set-card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6">
                                    <h5>{{ __('Employee Salary') }}</h5>
                                </div>
                                @permission('setsalary create')
                                    <div class="col-6 text-end">
                                        <a data-url="{{ route('employee.basic.salary', $employee->id) }}" data-ajax-popup="true"
                                            data-title="{{ __('Set Basic Salary') }}" data-bs-toggle="tooltip" title=""
                                            class="btn btn-sm btn-rc-primary" data-bs-original-title="{{ __('Set Salary') }}">
                                            <i class="ti ti-plus"></i>
                                        </a>
                                    </div>
                                @endpermission
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="project-info d-flex text-sm">
                                <div class="project-info-inner mr-3 col-4">
                                    <b class="m-0"> {{ __('Payslip Type') }} </b>
                                    <div class="project-amnt pt-1">
                                        {{ !empty($employee->salary_type()) ? $employee->salary_type() ?? '' : '' }}
                                    </div>
                                </div>
                                <div class="project-info-inner mr-3 col-4">
                                    <b class="m-0"> {{ __('Salary') }} </b>
                                    <div class="project-amnt pt-1">{{ currencyFormatWithSym($employee->salary) }}</div>
                                </div>
                                @if (moduleIsActive('Account'))
                                    <div class="project-info-inner mr-3 col-4">
                                        <b class="m-0"> {{ __('Account Type') }} </b>
                                        <div class="project-amnt pt-1">
                                            {{ !empty($employee->account_type) ? $employee->accountType->bank_name . ' ' . $employee->accountType->holder_name ?? '' : '--' }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- allowance -->
                @permission('allowance manage')
                    <div class="col-md-6">
                        <div class="card set-card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-6">
                                        <h5>{{ __('Allowance') }}</h5>
                                    </div>
                                    @permission('allowance create')
                                        <div class="col-6 text-end">
                                            <a data-url="{{ route('allowances.create', $employee->id) }}" data-ajax-popup="true"
                                                data-title="{{ __('Create Allowance') }}" data-bs-toggle="tooltip" title=""
                                                class="btn btn-sm btn-rc-primary" data-bs-original-title="{{ __('Create') }}">
                                                <i class="ti ti-plus"></i>
                                            </a>
                                        </div>
                                    @endpermission
                                </div>
                            </div>
                            <div class=" card-body table-border-style">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Employee Name') }}</th>
                                                <th>{{ __('Allownace Option') }}</th>
                                                <th>{{ __('Title') }}</th>
                                                <th>{{ __('Type') }}</th>
                                                <th>{{ __('Amount') }}</th>
                                                @if (Laratrust::hasPermission('allowance edit') || Laratrust::hasPermission('allowance delete'))
                                                    <th>{{ __('Action') }}</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($allowances as $allowance)
                                                <tr>
                                                    <td>{{ !empty(App\Services\PayrollHelperService::GetEmployeeByEmp($allowance->employee_id)) ? App\Services\PayrollHelperService::GetEmployeeByEmp($allowance->employee_id)->full_name : '' }}
                                                    </td>
                                                    <td>{{ $allowance->title }}</td>

                                                    <td>{{ ucfirst($allowance->type) }}</td>
                                                    @if ($allowance->type == 'fixed')
                                                        <td>{{ currencyFormatWithSym($allowance->amount) }}</td>
                                                    @else
                                                        <td>{{ $allowance->amount }}%
                                                            ({{ currencyFormatWithSym($allowance->tota_allow) }})
                                                        </td>
                                                    @endif
                                                    @if (Laratrust::hasPermission('allowance edit') || Laratrust::hasPermission('allowance delete'))
                                                        <td class="Action">
                                                            <span>
                                                                @permission('allowance edit')
                                                                    <a class="btn btn-icon btn-sm action-btn-edit"
                                                                        data-url="{{ URL::to('allowance/' . $allowance->id . '/edit') }}"
                                                                        data-ajax-popup="true" data-size="md"
                                                                        data-bs-toggle="tooltip"
                                                                        data-title="{{ __('Edit Allowance') }}"
                                                                        title="{{ __('Edit') }}">
                                                                        <i class="ti ti-pencil"></i>
                                                                    </a>
                                                                @endpermission
                                                                @permission('allowance delete')
                                                                    {{ Form::open(['route' => ['allowance.destroy', $allowance->id], 'class' => 'm-0 d-inline']) }}
                                                                    @method('DELETE')
                                                                    <a class="btn btn-icon btn-sm action-btn-delete bs-pass-para show_confirm"
                                                                        data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                                        aria-label="Delete"
                                                                        data-confirm="{{ __('Are You Sure?') }}"
                                                                        data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                        data-confirm-yes="delete-form-{{ $allowance->id }}">
                                                                        <i class="ti ti-trash"></i>
                                                                    </a>
                                                                    {{ Form::close() }}
                                                                @endpermission
                                                            </span>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endpermission

                <!-- Commission -->
                @permission('commission manage')
                    <div class="col-md-6">
                        <div class="card set-card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-6">
                                        <h5>{{ __('Commission') }}</h5>
                                    </div>
                                    @permission('commission create')
                                        <div class="col text-end">
                                            <a data-url="{{ route('commissions.create', $employee->id) }}" data-ajax-popup="true"
                                                data-title="{{ __('Create Commission') }}" data-bs-toggle="tooltip" title=""
                                                class="btn btn-sm btn-rc-primary" data-bs-original-title="{{ __('Create') }}">
                                                <i class="ti ti-plus"></i>
                                            </a>

                                        </div>
                                    @endpermission
                                </div>
                            </div>
                            <div class=" card-body table-border-style">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Employee Name') }}</th>
                                                <th>{{ __('Title') }}</th>
                                                <th>{{ __('Type') }}</th>
                                                <th>{{ __('Amount') }}</th>
                                                <th>{{ __('Start Date') }}</th>
                                                <th>{{ __('End Date') }}</th>
                                                <th>{{ __('Status') }}</th>
                                                @if (Laratrust::hasPermission('commission edit') || Laratrust::hasPermission('commission delete'))
                                                    <th>{{ __('Action') }}</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($commissions as $commission)
                                                <tr>
                                                    <td>{{ !empty(App\Services\PayrollHelperService::GetEmployeeByEmp($commission->employee_id)) ? App\Services\PayrollHelperService::GetEmployeeByEmp($commission->employee_id)->full_name : '' }}
                                                    </td>
                                                    <td>{{ $commission->title }}</td>
                                                    <td>{{ ucfirst($commission->type) }}</td>
                                                    @if ($commission->type == 'fixed')
                                                        <td>{{ currencyFormatWithSym($commission->amount) }}</td>
                                                    @else
                                                        <td>{{ $commission->amount }}%
                                                            ({{ currencyFormatWithSym($commission->tota_allow) }})
                                                        </td>
                                                    @endif
                                                    <td>{{ !empty($commission->start_date) ? companyDateFormate($commission->start_date) : '-' }}
                                                    </td>
                                                    <td>{{ !empty($commission->end_date) ? companyDateFormate($commission->end_date) : '-' }}
                                                    </td>
                                                    <td>{{ !empty($commission->status) ? ucfirst($commission->status) : '' }}
                                                    </td>
                                                    @if (Laratrust::hasPermission('commission edit') || Laratrust::hasPermission('commission delete'))
                                                        <td class="Action">
                                                            <span>
                                                                @permission('commission edit')
                                                                    <a class="btn btn-icon btn-sm action-btn-edit"
                                                                        data-url="{{ URL::to('commission/' . $commission->id . '/edit') }}"
                                                                        data-ajax-popup="true" data-size="md"
                                                                        data-bs-toggle="tooltip"
                                                                        data-title="{{ __('Edit Commission') }}"
                                                                        title="{{ __('Edit') }}">
                                                                        <i class="ti ti-pencil"></i>
                                                                    </a>
                                                                @endpermission
                                                                @permission('commission delete')
                                                                    {{ Form::open(['route' => ['commission.destroy', $commission->id], 'class' => 'm-0 d-inline']) }}
                                                                    @method('DELETE')
                                                                    <a class="btn btn-icon btn-sm action-btn-delete bs-pass-para show_confirm"
                                                                        data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                                        aria-label="Delete"
                                                                        data-confirm="{{ __('Are You Sure?') }}"
                                                                        data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                        data-confirm-yes="delete-form-{{ $commission->id }}">
                                                                        <i class="ti ti-trash"></i>
                                                                    </a>
                                                                    {{ Form::close() }}
                                                                @endpermission
                                                            </span>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endpermission

                <!-- Saturation -->
                @permission('saturation deduction manage')
                    <div class="col-md-6">
                        <div class="card set-card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-6">
                                        <h5>{{ __('Saturation Deduction') }}</h5>
                                    </div>
                                    @permission('saturation deduction create')
                                        <div class="col text-end">
                                            <a data-url="{{ route('saturationdeductions.create', $employee->id) }}"
                                                data-ajax-popup="true" data-size="md"
                                                data-title="{{ __('Create Saturation Deduction') }}" data-bs-toggle="tooltip"
                                                title="" class="btn btn-sm btn-rc-primary"
                                                data-bs-original-title="{{ __('Create') }}">
                                                <i class="ti ti-plus"></i>
                                            </a>
                                        </div>
                                    @endpermission
                                </div>
                            </div>
                            <div class=" card-body table-border-style">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Employee Name') }}</th>
                                                <th>{{ __('Deduction Option') }}</th>
                                                <th>{{ __('Title') }}</th>
                                                <th>{{ __('Type') }}</th>
                                                <th>{{ __('Amount') }}</th>
                                                @if (Laratrust::hasPermission('saturation deduction edit') || Laratrust::hasPermission('saturation deduction delete'))
                                                    <th>{{ __('Action') }}</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($saturationdeductions as $saturationdeduction)
                                                <tr>
                                                    <td>{{ !empty(App\Services\PayrollHelperService::GetEmployeeByEmp($saturationdeduction->employee_id)) ? App\Services\PayrollHelperService::GetEmployeeByEmp($saturationdeduction->employee_id)->full_name : '' }}
                                                    </td>
                                                    <td>{{ $saturationdeduction->title }}</td>
                                                    <td>{{ ucfirst($saturationdeduction->type) }}</td>
                                                    @if ($saturationdeduction->type == 'fixed')
                                                        <td>{{ currencyFormatWithSym($saturationdeduction->amount) }}
                                                        </td>
                                                    @else
                                                        <td>{{ $saturationdeduction->amount }}%
                                                            ({{ currencyFormatWithSym($saturationdeduction->tota_allow) }})
                                                        </td>
                                                    @endif
                                                    @if (Laratrust::hasPermission('saturation deduction edit') || Laratrust::hasPermission('saturation deduction delete'))
                                                        <td class="Action">
                                                            <span>
                                                                @permission('saturation deduction edit')
                                                                    <a class="btn btn-icon btn-sm action-btn-edit"
                                                                        data-url="{{ URL::to('saturationdeduction/' . $saturationdeduction->id . '/edit') }}"
                                                                        data-ajax-popup="true" data-size="md"
                                                                        data-bs-toggle="tooltip"
                                                                        data-title="{{ __('Edit Saturation Deduction') }}"
                                                                        title="{{ __('Edit') }}">
                                                                        <i class="ti ti-pencil"></i>
                                                                    </a>
                                                                @endpermission
                                                                @permission('saturation deduction delete')
                                                                    {{ Form::open(['route' => ['saturationdeduction.destroy', $saturationdeduction->id], 'class' => 'm-0 d-inline']) }}
                                                                    @method('DELETE')
                                                                    <a class="btn btn-icon btn-sm action-btn-delete bs-pass-para show_confirm"
                                                                        data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                                        aria-label="Delete"
                                                                        data-confirm="{{ __('Are You Sure?') }}"
                                                                        data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                        data-confirm-yes="delete-form-{{ $saturationdeduction->id }}">
                                                                        <i class="ti ti-trash"></i>
                                                                    </a>
                                                                    {{ Form::close() }}
                                                                @endpermission
                                                            </span>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endpermission

                <!-- other payment-->
                @permission('other payment manage')
                    <div class="col-md-6">
                        <div class="card set-card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-6">
                                        <h5>{{ __('Other Payment') }}</h5>
                                    </div>
                                    @permission('other payment create')
                                        <div class="col text-end">

                                            <a data-url="{{ route('otherpayments.create', $employee->id) }}"
                                                data-ajax-popup="true" data-title="{{ __('Create Other Payment') }}"
                                                data-bs-toggle="tooltip" title="" class="btn btn-sm btn-rc-primary"
                                                data-bs-original-title="{{ __('Create') }}">
                                                <i class="ti ti-plus"></i>
                                            </a>
                                        </div>
                                    @endpermission
                                </div>
                            </div>
                            <div class=" card-body table-border-style">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Employee Name') }}</th>
                                                <th>{{ __('Title') }}</th>
                                                <th>{{ __('Type') }}</th>
                                                <th>{{ __('Amount') }}</th>
                                                @if (Laratrust::hasPermission('other payment edit') || Laratrust::hasPermission('other payment delete'))
                                                    <th>{{ __('Action') }}</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($otherpayments as $otherpayment)
                                                <tr>
                                                    <td>{{ !empty(App\Services\PayrollHelperService::GetEmployeeByEmp($otherpayment->employee_id)) ? App\Services\PayrollHelperService::GetEmployeeByEmp($otherpayment->employee_id)->full_name : '' }}
                                                    </td>
                                                    <td>{{ $otherpayment->title }}</td>
                                                    <td>{{ ucfirst($otherpayment->type) }}</td>
                                                    @if ($otherpayment->type == 'fixed')
                                                        <td>{{ currencyFormatWithSym($otherpayment->amount) }}</td>
                                                    @else
                                                        <td>{{ $otherpayment->amount }}%
                                                            ({{ currencyFormatWithSym($otherpayment->tota_allow) }})
                                                        </td>
                                                    @endif
                                                    @if (Laratrust::hasPermission('other payment edit') || Laratrust::hasPermission('other payment delete'))
                                                        <td class="Action">
                                                            <span>
                                                                @permission('other payment edit')
                                                                    <a class="btn btn-icon btn-sm action-btn-edit"
                                                                        data-url="{{ URL::to('otherpayment/' . $otherpayment->id . '/edit') }}"
                                                                        data-ajax-popup="true" data-size="md"
                                                                        data-bs-toggle="tooltip"
                                                                        data-title="{{ __('Edit Other Payment') }}"
                                                                        title="{{ __('Edit') }}">
                                                                        <i class="ti ti-pencil"></i>
                                                                    </a>
                                                                @endpermission
                                                                @permission('other payment delete')
                                                                    {{ Form::open(['route' => ['otherpayment.destroy', $otherpayment->id], 'class' => 'm-0 d-inline']) }}
                                                                    @method('DELETE')
                                                                    <a class="btn btn-icon btn-sm action-btn-delete bs-pass-para show_confirm"
                                                                        data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                                        aria-label="Delete"
                                                                        data-confirm="{{ __('Are You Sure?') }}"
                                                                        data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                        data-confirm-yes="delete-form-{{ $otherpayment->id }}">
                                                                        <i class="ti ti-trash"></i>
                                                                    </a>
                                                                    {{ Form::close() }}
                                                                @endpermission
                                                            </span>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endpermission

                <!--overtime-->
                @permission('overtime manage')
                    <div class="col-md-6">
                        <div class="card set-card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-6">
                                        <h5>{{ __('Overtime') }}</h5>
                                    </div>
                                    @permission('overtime create')
                                        <div class="col text-end">
                                            <a data-url="{{ route('overtimes.create', $employee->id) }}" data-ajax-popup="true"
                                                data-title="{{ __('Create Overtime') }}" data-bs-toggle="tooltip"
                                                title="" class="btn btn-sm btn-rc-primary"
                                                data-bs-original-title="{{ __('Create') }}">
                                                <i class="ti ti-plus"></i>
                                            </a>
                                        </div>
                                    @endpermission
                                </div>
                            </div>
                            <div class=" card-body table-border-style">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Employee Name') }}</th>
                                                <th>{{ __('Overtime Title') }}</th>
                                                <th>{{ __('Number of days') }}</th>
                                                <th>{{ __('Hours') }}</th>
                                                <th>{{ __('Rate') }}</th>
                                                <th>{{ __('Start Date') }}</th>
                                                <th>{{ __('End Date') }}</th>
                                                <th>{{ __('Status') }}</th>
                                                @if (Laratrust::hasPermission('overtime edit') || Laratrust::hasPermission('overtime delete'))
                                                    <th>{{ __('Action') }}</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($overtimes as $overtime)
                                                <tr>
                                                    <td>{{ !empty(App\Services\PayrollHelperService::GetEmployeeByEmp($overtime->employee_id)) ? App\Services\PayrollHelperService::GetEmployeeByEmp($overtime->employee_id)->full_name : '' }}
                                                    </td>
                                                    <td>{{ $overtime->title }}</td>
                                                    <td>{{ $overtime->number_of_days }}</td>
                                                    <td>{{ $overtime->hours }}</td>
                                                    <td>{{ currencyFormatWithSym($overtime->rate) }}</td>
                                                    <td>{{ companyDateFormate($overtime->start_date) }}</td>
                                                    <td>{{ companyDateFormate($overtime->end_date) }}</td>
                                                    <td>{{ !empty($overtime->status) ? ucfirst($overtime->status) : '' }}</td>
                                                    @if (Laratrust::hasPermission('overtime edit') || Laratrust::hasPermission('overtime delete'))
                                                        <td class="Action">
                                                            <span>
                                                                @permission('overtime edit')
                                                                    <a class="btn btn-icon btn-sm action-btn-edit"
                                                                        data-url="{{ URL::to('overtime/' . $overtime->id . '/edit') }}"
                                                                        data-ajax-popup="true" data-size="md"
                                                                        data-bs-toggle="tooltip"
                                                                        data-title="{{ __('Edit OverTime') }}"
                                                                        title="{{ __('Edit') }}">
                                                                        <i class="ti ti-pencil"></i>
                                                                    </a>
                                                                @endpermission
                                                                @permission('overtime delete')
                                                                    {{ Form::open(['route' => ['overtime.destroy', $overtime->id], 'class' => 'm-0 d-inline']) }}
                                                                    @method('DELETE')
                                                                    <a class="btn btn-icon btn-sm action-btn-delete bs-pass-para show_confirm"
                                                                        data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                                        aria-label="Delete"
                                                                        data-confirm="{{ __('Are You Sure?') }}"
                                                                        data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                        data-confirm-yes="delete-form-{{ $overtime->id }}">
                                                                        <i class="ti ti-trash"></i>
                                                                    </a>
                                                                    {{ Form::close() }}
                                                                @endpermission
                                                            </span>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endpermission
                
                <!-- company contribution-->
                @permission('company contribution manage')
                    <div class="col-md-6">
                        <div class="card set-card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-6">
                                        <h5>{{ __('Company Contribution') }}</h5>
                                    </div>
                                    @permission('company contribution create')
                                        <div class="col text-end">

                                            <a data-url="{{ route('companycontributions.create', $employee->id) }}"
                                                data-ajax-popup="true" data-title="{{ __('Create Company Contribution') }}"
                                                data-bs-toggle="tooltip" title="" class="btn btn-sm btn-rc-primary"
                                                data-bs-original-title="{{ __('Create') }}">
                                                <i class="ti ti-plus"></i>
                                            </a>
                                        </div>
                                    @endpermission
                                </div>
                            </div>
                            <div class=" card-body table-border-style">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Employee Name') }}</th>
                                                <th>{{ __('Title') }}</th>
                                                <th>{{ __('Type') }}</th>
                                                <th>{{ __('Amount') }}</th>
                                                @if (Laratrust::hasPermission('company contribution edit') || Laratrust::hasPermission('company contribution delete'))
                                                    <th>{{ __('Action') }}</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($companycontributions as $companycontribution)
                                                <tr>
                                                    <td>{{ !empty(App\Services\PayrollHelperService::GetEmployeeByEmp($companycontribution->employee_id)) ? App\Services\PayrollHelperService::GetEmployeeByEmp($companycontribution->employee_id)->full_name : '' }}
                                                    </td>
                                                    <td>{{ $companycontribution->title }}</td>
                                                    <td>{{ ucfirst($companycontribution->type) }}</td>
                                                    @if ($companycontribution->type == 'fixed')
                                                        <td>{{ currencyFormatWithSym($companycontribution->amount) }}</td>
                                                    @else
                                                        <td>{{ $companycontribution->amount }}%
                                                            ({{ currencyFormatWithSym($companycontribution->tota_allow) }})
                                                        </td>
                                                    @endif
                                                    @if (Laratrust::hasPermission('company contribution edit') || Laratrust::hasPermission('company contribution delete'))
                                                        <td class="Action">
                                                            <span>
                                                                @permission('company contribution edit')
                                                                    <a class="btn btn-icon btn-sm action-btn-edit"
                                                                        data-url="{{ URL::to('companycontribution/' . $companycontribution->id . '/edit') }}"
                                                                        data-ajax-popup="true" data-size="md"
                                                                        data-bs-toggle="tooltip"
                                                                        data-title="{{ __('Edit Company Contribution') }}"
                                                                        title="{{ __('Edit') }}">
                                                                        <i class="ti ti-pencil"></i>
                                                                    </a>
                                                                @endpermission
                                                                @permission('company contribution delete')
                                                                    {{ Form::open(['route' => ['companycontribution.destroy', $companycontribution->id], 'class' => 'm-0 d-inline']) }}
                                                                    @method('DELETE')
                                                                    <a class="btn btn-icon btn-sm action-btn-delete bs-pass-para show_confirm"
                                                                        data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                                        aria-label="Delete"
                                                                        data-confirm="{{ __('Are You Sure?') }}"
                                                                        data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                        data-confirm-yes="delete-form-{{ $companycontribution->id }}">
                                                                        <i class="ti ti-trash"></i>
                                                                    </a>
                                                                    {{ Form::close() }}
                                                                @endpermission
                                                            </span>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endpermission
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script type="text/javascript">
        $(document).on('change', '.amount_type', function() {
            var val = $(this).val();
            var label_text = 'Amount';
            if (val == 'percentage') {
                var label_text = 'Percentage';
            }
            $('.amount_label').html(label_text);
        });
    </script>
@endpush
