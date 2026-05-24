@extends('layouts.main')
@section('page-title')
    {{ __('Employee Salary') }}
@endsection
@section('page-breadcrumb')
    {{ __('Employee Salary') }}
@endsection
@section('content')
<div class="row">
    <div class="col-sm-12">
        <x-rc-table title="{{ __('Employee Salary') }}" titleIcon="ti ti-cash">
            <x-rc-table.content>
                <table class="rc-table" id="dataTable1">
                    <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Payroll Month') }}</th>
                            <th>{{ __('Salary') }}</th>
                            <th>{{ __('Net Salary') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="col-actions" width="200px">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payslip as $payslip)
                            <tr>
                                <td>{{ !empty(\App\PaySlip::employee($payslip->employee_id))? \App\PaySlip::employee($payslip->employee_id)->name: '' }}
                                </td>
                                <td>{{ $payslip->salary_month }}</td>
                                <td>{{ $payslip->basic_salary }}</td>
                                <td>{{ $payslip->net_payble }}</td>
                                <td>
                                    @if ($payslip->status == 1)
                                        <span class="badge bg-success">{{ __('Paid') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('Unpaid') }}</span>
                                    @endif
                                </td>
                                <td class="col-actions">
                                    <a 
                                        data-url="{{ route('payslip.showemployee', $payslip->id) }}"
                                        class="rc-table-action rc-table-action-view"
                                        data-ajax-popup="true"
                                        data-title="{{ __('View Employee Detail') }}"
                                        data-bs-toggle="tooltip" data-bs-placement="bottom"
                                        title="{{ __('View Employee Detail') }}">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <a 
                                        data-url="{{ route('payslip.pdf', [$payslip->employee_id, $payslip->salary_month]) }}"
                                        data-size="md-pdf"
                                        class="rc-table-action"
                                        data-ajax-popup="true"
                                        data-title="{{ __('Payslip') }}"
                                        data-bs-toggle="tooltip" data-bs-placement="bottom"
                                        title="{{ __('Payslip') }}">
                                        <i class="ti ti-file-invoice"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <x-rc-table.empty :asRow="true" :colspan="6" icon="ti ti-cash-off" title="{{ __('No Payslips Found') }}" message="{{ __('There are no employee payslips to display.') }}" />
                        @endforelse
                    </tbody>
                </table>
            </x-rc-table.content>
        </x-rc-table>
    </div>
</div>
@endsection
