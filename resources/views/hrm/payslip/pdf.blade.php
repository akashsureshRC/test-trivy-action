@php
    $company_settings = getCompanyAllSetting();

    // Parse salary month for display
    $salaryMonthFormatted = '-';
    if (!empty($payslip->salary_month)) {
        try {
            $salaryMonthFormatted = \Carbon\Carbon::parse($payslip->salary_month)->format('F Y');
        } catch (\Exception $e) {
            $salaryMonthFormatted = $payslip->salary_month;
        }
    }

    // Safely decode JSON fields
    $allowances = json_decode($payslipDetail['payslip']->allowance ?? '[]');
    $commissions = json_decode($payslipDetail['payslip']->commission ?? '[]');
    $other_payments = json_decode($payslipDetail['payslip']->other_payment ?? '[]');
    $overtimes = json_decode($payslipDetail['payslip']->overtime ?? '[]');
    $company_contributions = json_decode($payslipDetail['payslip']->company_contribution ?? '[]');
    $loans = json_decode($payslipDetail['payslip']->loan ?? '[]');
    $saturation_deductions = json_decode($payslipDetail['payslip']->saturation_deduction ?? '[]');

    // Ensure all are iterable
    $allowances = is_array($allowances) || is_object($allowances) ? $allowances : [];
    $commissions = is_array($commissions) || is_object($commissions) ? $commissions : [];
    $other_payments = is_array($other_payments) || is_object($other_payments) ? $other_payments : [];
    $overtimes = is_array($overtimes) || is_object($overtimes) ? $overtimes : [];
    $company_contributions = is_array($company_contributions) || is_object($company_contributions) ? $company_contributions : [];
    $loans = is_array($loans) || is_object($loans) ? $loans : [];
    $saturation_deductions = is_array($saturation_deductions) || is_object($saturation_deductions) ? $saturation_deductions : [];

    $hasEarnings = count((array)$allowances) || count((array)$commissions) || count((array)$other_payments) || count((array)$overtimes) || count((array)$company_contributions);
    $hasDeductions = count((array)$loans) || count((array)$saturation_deductions);
@endphp

<div class="modal-body p-0">
    {{-- Action Buttons --}}
    <div class="d-flex justify-content-end gap-2 p-3 pb-0">
        <a href="{{ route('payslip.preview', [$employee->id, $payslip->salary_month]) }}"
            class="btn btn-rc-icon btn-sm" data-bs-toggle="tooltip" title="{{ __('Download PDF') }}" target="_blank">
            <i class="ti ti-download text-white"></i>
        </a>
        <a href="{{ route('payslip.send', [$employee->id, $payslip->salary_month]) }}"
            class="btn btn-sm btn-rc-icon" style="background: var(--rc-warning, #f59e0b);" data-bs-toggle="tooltip" title="{{ __('Send to Employee') }}">
            <i class="ti ti-send text-white"></i>
        </a>
    </div>

    {{-- Payslip Content --}}
    <div class="p-4" id="printableArea">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                @if(sidebarLogo())
                    <img src="{{ sidebarLogo() }}" alt="Logo" style="height: 50px; margin-bottom: 10px;">
                @endif
                <h5 class="mb-1" style="color: var(--rc-gray-800); font-weight: 600;">
                    {{ !empty($company_settings['company_name']) ? $company_settings['company_name'] : '' }}
                </h5>
                <p class="text-muted mb-0" style="font-size: 12px; line-height: 1.5;">
                    {{ !empty($company_settings['company_address']) ? $company_settings['company_address'] : '' }}{{ !empty($company_settings['company_city']) ? ', ' . $company_settings['company_city'] : '' }}<br>
                    {{ !empty($company_settings['company_state']) ? $company_settings['company_state'] : '' }}{{ !empty($company_settings['company_country']) ? ', ' . $company_settings['company_country'] : '' }}
                </p>
            </div>
            <div class="text-end">
                <span class="badge rounded-pill px-3 py-2" style="background: var(--rc-primary); font-size: 13px;">
                    {{ __('PAYSLIP') }}
                </span>
                <p class="text-muted mt-2 mb-0" style="font-size: 12px;">
                    {{ __('Period') }}: <strong>{{ $salaryMonthFormatted }}</strong>
                </p>
            </div>
        </div>

        <hr style="border-color: var(--rc-gray-200);">

        {{-- Employee Info --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <table style="font-size: 13px; line-height: 2;">
                    <tr>
                        <td style="color: var(--rc-gray-500); padding-right: 12px; border: none;">{{ __('Employee') }}</td>
                        <td style="font-weight: 600; border: none;">{{ $employee->first_name }} {{ $employee->last_name }}</td>
                    </tr>
                    <tr>
                        <td style="color: var(--rc-gray-500); padding-right: 12px; border: none;">{{ __('Employee ID') }}</td>
                        <td style="font-weight: 600; border: none;">{{ $employee->employee_id ? App\Models\Hrm\Employee::employeeIdFormat($employee->employee_id) : '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color: var(--rc-gray-500); padding-right: 12px; border: none;">{{ __('Created') }}</td>
                        <td style="font-weight: 600; border: none;">{{ companyDateFormate($payslip->created_at) }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table style="font-size: 13px; line-height: 2;">
                    @if ($employee->department)
                    <tr>
                        <td style="color: var(--rc-gray-500); padding-right: 12px; border: none;">{{ __('Department') }}</td>
                        <td style="font-weight: 600; border: none;">{{ $employee->department->name ?? '-' }}</td>
                    </tr>
                    @endif
                    @if ($employee->designation)
                    <tr>
                        <td style="color: var(--rc-gray-500); padding-right: 12px; border: none;">{{ __('Designation') }}</td>
                        <td style="font-weight: 600; border: none;">{{ $employee->designation->name ?? '-' }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="color: var(--rc-gray-500); padding-right: 12px; border: none;">{{ __('Status') }}</td>
                        <td style="border: none;">
                            @if ($payslip->status == 2)
                                <span class="badge bg-success rounded-pill">{{ __('Paid') }}</span>
                            @elseif ($payslip->status == 1)
                                <span class="badge bg-info rounded-pill">{{ __('Finalized') }}</span>
                            @else
                                <span class="badge bg-warning rounded-pill">{{ __('Draft') }}</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Earnings Table --}}
        <div class="mb-4">
            <h6 class="mb-3" style="color: var(--rc-primary); font-weight: 600;">
                <i class="ti ti-plus me-1"></i>{{ __('Earnings') }}
            </h6>
            <div class="table-responsive">
                <table class="table table-sm mb-0" style="font-size: 13px;">
                    <thead>
                        <tr style="background: var(--rc-gray-50, #f8fafc);">
                            <th style="font-weight: 600; color: var(--rc-gray-600); border-bottom: 2px solid var(--rc-gray-200);">{{ __('Description') }}</th>
                            <th style="font-weight: 600; color: var(--rc-gray-600); border-bottom: 2px solid var(--rc-gray-200);">{{ __('Type') }}</th>
                            <th class="text-end" style="font-weight: 600; color: var(--rc-gray-600); border-bottom: 2px solid var(--rc-gray-200);">{{ __('Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="border-bottom: 1px solid var(--rc-gray-100);">{{ __('Basic Salary') }}</td>
                            <td style="border-bottom: 1px solid var(--rc-gray-100);">-</td>
                            <td class="text-end" style="font-weight: 500; border-bottom: 1px solid var(--rc-gray-100);">{{ currencyFormat($payslip->basic_salary) }}</td>
                        </tr>
                        @foreach ($allowances as $item)
                        <tr>
                            <td style="border-bottom: 1px solid var(--rc-gray-100);">{{ __('Allowance') }}: {{ $item->title }}</td>
                            <td style="border-bottom: 1px solid var(--rc-gray-100);">{{ ucfirst($item->type) }}</td>
                            <td class="text-end" style="font-weight: 500; border-bottom: 1px solid var(--rc-gray-100);">
                                @if ($item->type != 'percentage')
                                    {{ currencyFormat($item->amount) }}
                                @else
                                    {{ $item->amount }}% ({{ currencyFormat(($item->amount * $payslip->basic_salary) / 100) }})
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @foreach ($commissions as $item)
                        <tr>
                            <td style="border-bottom: 1px solid var(--rc-gray-100);">{{ __('Commission') }}: {{ $item->title }}</td>
                            <td style="border-bottom: 1px solid var(--rc-gray-100);">{{ ucfirst($item->type) }}</td>
                            <td class="text-end" style="font-weight: 500; border-bottom: 1px solid var(--rc-gray-100);">
                                @if ($item->type != 'percentage')
                                    {{ currencyFormat($item->amount) }}
                                @else
                                    {{ $item->amount }}% ({{ currencyFormat(($item->amount * $payslip->basic_salary) / 100) }})
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @foreach ($other_payments as $item)
                        <tr>
                            <td style="border-bottom: 1px solid var(--rc-gray-100);">{{ __('Other Payment') }}: {{ $item->title }}</td>
                            <td style="border-bottom: 1px solid var(--rc-gray-100);">{{ ucfirst($item->type) }}</td>
                            <td class="text-end" style="font-weight: 500; border-bottom: 1px solid var(--rc-gray-100);">
                                @if ($item->type != 'percentage')
                                    {{ currencyFormat($item->amount) }}
                                @else
                                    {{ $item->amount }}% ({{ currencyFormat(($item->amount * $payslip->basic_salary) / 100) }})
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @foreach ($overtimes as $item)
                        <tr>
                            <td style="border-bottom: 1px solid var(--rc-gray-100);">{{ __('Overtime') }}: {{ $item->title }}</td>
                            <td style="border-bottom: 1px solid var(--rc-gray-100);">-</td>
                            <td class="text-end" style="font-weight: 500; border-bottom: 1px solid var(--rc-gray-100);">{{ currencyFormat($item->number_of_days * $item->hours * $item->rate) }}</td>
                        </tr>
                        @endforeach
                        @foreach ($company_contributions as $item)
                        <tr>
                            <td style="border-bottom: 1px solid var(--rc-gray-100);">{{ __('Company Contribution') }}: {{ $item->title }}</td>
                            <td style="border-bottom: 1px solid var(--rc-gray-100);">{{ ucfirst($item->type) }}</td>
                            <td class="text-end" style="font-weight: 500; border-bottom: 1px solid var(--rc-gray-100);">
                                @if ($item->type != 'percentage')
                                    {{ currencyFormat($item->amount) }}
                                @else
                                    {{ $item->amount }}% ({{ currencyFormat(($item->amount * $payslip->basic_salary) / 100) }})
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Deductions Table --}}
        @if ($hasDeductions)
        <div class="mb-4">
            <h6 class="mb-3" style="color: var(--rc-danger, #dc3545); font-weight: 600;">
                <i class="ti ti-minus me-1"></i>{{ __('Deductions') }}
            </h6>
            <div class="table-responsive">
                <table class="table table-sm mb-0" style="font-size: 13px;">
                    <thead>
                        <tr style="background: var(--rc-gray-50, #f8fafc);">
                            <th style="font-weight: 600; color: var(--rc-gray-600); border-bottom: 2px solid var(--rc-gray-200);">{{ __('Description') }}</th>
                            <th style="font-weight: 600; color: var(--rc-gray-600); border-bottom: 2px solid var(--rc-gray-200);">{{ __('Type') }}</th>
                            <th class="text-end" style="font-weight: 600; color: var(--rc-gray-600); border-bottom: 2px solid var(--rc-gray-200);">{{ __('Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($loans as $item)
                        <tr>
                            <td style="border-bottom: 1px solid var(--rc-gray-100);">{{ __('Loan') }}: {{ $item->title }}</td>
                            <td style="border-bottom: 1px solid var(--rc-gray-100);">{{ ucfirst($item->type) }}</td>
                            <td class="text-end" style="font-weight: 500; border-bottom: 1px solid var(--rc-gray-100);">
                                @if ($item->type != 'percentage')
                                    {{ currencyFormat($item->amount) }}
                                @else
                                    {{ $item->amount }}% ({{ currencyFormat(($item->amount * $payslip->basic_salary) / 100) }})
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @foreach ($saturation_deductions as $item)
                        <tr>
                            <td style="border-bottom: 1px solid var(--rc-gray-100);">{{ __('Deduction') }}: {{ $item->title }}</td>
                            <td style="border-bottom: 1px solid var(--rc-gray-100);">{{ ucfirst($item->type) }}</td>
                            <td class="text-end" style="font-weight: 500; border-bottom: 1px solid var(--rc-gray-100);">
                                @if ($item->type != 'percentage')
                                    {{ currencyFormat($item->amount) }}
                                @else
                                    {{ $item->amount }}% ({{ currencyFormat(($item->amount * $payslip->basic_salary) / 100) }})
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Summary --}}
        <div class="mt-4 p-3" style="background: var(--rc-gray-50, #f8fafc); border-radius: 8px;">
            <div class="row">
                <div class="col-md-6"></div>
                <div class="col-md-6">
                    <table style="width: 100%; font-size: 13px;">
                        <tr>
                            <td style="padding: 6px 0; color: var(--rc-gray-600); border: none;">{{ __('Total Earnings') }}</td>
                            <td class="text-end" style="padding: 6px 0; font-weight: 500; border: none;">{{ currencyFormat($payslipDetail['totalEarning']) }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 6px 0; color: var(--rc-gray-600); border: none;">{{ __('Total Deductions') }}</td>
                            <td class="text-end" style="padding: 6px 0; font-weight: 500; color: var(--rc-danger, #dc3545); border: none;">- {{ currencyFormat($payslipDetail['totalDeduction']) }}</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="border: none;"><hr style="margin: 4px 0; border-color: var(--rc-gray-300);"></td>
                        </tr>
                        <tr>
                            <td style="padding: 6px 0; color: var(--rc-gray-600); border: none;">{{ __('Taxable Earnings') }}</td>
                            <td class="text-end" style="padding: 6px 0; font-weight: 500; border: none;">{{ currencyFormat($payslipDetail['taxable_earning']) }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 6px 0; color: var(--rc-gray-600); border: none;">{{ __('Tax') }} ({{ $payslipDetail['tax_rate'] }}%)</td>
                            <td class="text-end" style="padding: 6px 0; font-weight: 500; color: var(--rc-danger, #dc3545); border: none;">- {{ currencyFormat($payslipDetail['tax_amount']) }}</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="border: none;"><hr style="margin: 4px 0; border-color: var(--rc-gray-300);"></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; font-weight: 700; font-size: 15px; color: var(--rc-gray-800); border: none;">{{ __('Net Salary') }}</td>
                            <td class="text-end" style="padding: 8px 0; font-weight: 700; font-size: 15px; color: var(--rc-primary); border: none;">{{ currencyFormatWithSym($payslip->net_payble) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
