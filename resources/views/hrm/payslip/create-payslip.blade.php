@extends('layouts.main')

@section('page-title')
    {{ __('Add Payslip') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Payroll') }},
    {{ __('Add Payslip') }}
@endsection

@section('page-action')
    <div>
        <a href="{{ route('payroll.index', ['employee_id' => $employee->id]) }}" class="btn btn-rc-outline btn-sm">
            <i class="ti ti-arrow-left"></i> {{ __('Back to Payroll') }}
        </a>
    </div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
@endpush

@php
    $company_settings = getCompanyAllSetting();
@endphp

@section('content')
    <div class="row">
        <div class="col-xxl-4 col-xl-4 col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        {{ __('Add Once-Off Payslip') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('payroll.once-off-payslip', $employee->id) }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label class="require form-label">{{ __('Date') }}</label>
                            <input class="form-control @error('date') is-invalid @enderror"
                                   type="date" name="date" value="{{ old('date') }}" 
                                   min="{{ $employee->date_of_appointment }}"
                                   placeholder="{{ __('Date') }}" 
                                   max="{{ carbon\Carbon::now()->addYears(2)->format('Y-m-d') }}">
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="submit" class="btn btn-rc-primary">{{ __('Create') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-xl-4 col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        {{ __('Add Next Regular Payslip') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('payroll.next-payslip', $employee->id) }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">{{ __('Date') }}</label>
                            <h6 class="mt-2">{{ $next_payslip }}</h6>
                            <input class="form-control @error('date') is-invalid @enderror"
                                    type="hidden" name="date" value="{{ $next_payslip }}">
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="submit" class="btn btn-rc-primary">{{ __('Create') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleRow(rowId) {
            const row = document.getElementById(rowId);
            row.style.display = row.style.display === "none" ? "flex" : "none";
        }
    </script>
@endsection
