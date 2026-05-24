@extends('layouts.main')

@section('page-title')
    {{ __('Gain on Vesting of Equity Instruments') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Payroll') }},
    {{ __('Equity Instruments') }}
@endsection

@section('page-action')
    <div>
        <a href="{{ route('payroll.index', ['employee_id' => $employee->id, 'term' => $term]) }}" class="btn btn-rc-outline btn-sm">
            <i class="ti ti-arrow-left"></i> {{ __('Back to Payroll') }}
        </a>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <form id="createForm" action="{{ route('equity-instruments.store') }}" method="POST">

        @csrf
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Directive Number') }}</label>
                                <input type="text" name="directive_number" class="form-control @error('directive_number') is-invalid @enderror"
                                       value="{{ old('directive_number') }}" placeholder="{{ __('Enter Directive Number') }}">
                                @error('directive_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Directive Issue Date') }}</label>
                                <input type="date" name="directive_issue_date" class="form-control @error('directive_issue_date') is-invalid @enderror"
                                       value="{{ old('directive_issue_date') }}">
                                @error('directive_issue_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <p class="text-muted small mt-1">{{ __('Only applies to payslips from March 2021 onwards') }}</p>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Amount of Tax to Deduct (from Tax Directive)') }}</label>
                                <input type="number" step="0.01" min="0" name="tax_deduct_amount" class="form-control @error('tax_deduct_amount') is-invalid @enderror"
                                       value="{{ old('tax_deduct_amount') }}" placeholder="{{ __('Enter Amount') }}">
                                @error('tax_deduct_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Directive Income Amount') }}</label>
                                <input type="number" step="0.01" min="0" name="directive_income_amount" class="form-control @error('directive_income_amount') is-invalid @enderror"
                                       value="{{ old('directive_income_amount', 0) }}">
                                @error('directive_income_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                        <input type="hidden" name="term" value="{{ $term }}">

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a class="btn btn-rc-outline" href="{{ route('payroll.index', ['employee_id' => $employee->id, 'term' => $term]) }}">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-rc-primary">{{ __('Submit') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
                </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#createForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();
            $.ajax({
                type: 'POST',
                url: '{{ route("equity-instruments.ajax-validate-store") }}',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        form.off('submit');
                        form[0].submit();
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    $('.error-text').remove();
                    $('input, select, textarea').removeClass('is-invalid');
                    $.each(errors, function(field, messages) {
                        var input = $('[name="' + field + '"]');
                        input.addClass('is-invalid');
                        input.after('<span class="text-danger error-text">' + messages[0] + '</span>');
                    });
                }
            });
        });
    });
</script>
@endpush