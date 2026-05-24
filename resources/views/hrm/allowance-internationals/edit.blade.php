@extends('layouts.main')

@section('page-title')
    {{ __('Subsistence Allowance (International)') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Payroll') }},
    {{ __('Subsistence (International)') }}
@endsection

@section('page-action')
    <div>
        <a href="{{ route('payroll.index', ['employee_id' => $allowanceInternational->employee_id, 'term' => $term]) }}" class="btn btn-rc-outline btn-sm">
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
            <form id="editForm" action="{{ route('allowance-internationals.update', $allowanceInternational) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label class="require form-label">{{ __('Total Paid to Employee') }}</label>
                        <input type="text" name="paid_to_employee"
                            class="form-control @error('paid_to_employee') is-invalid @enderror"
                            value="{{ old('paid_to_employee', $allowanceInternational->paid_to_employee) }}">
                        @error('paid_to_employee')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label class="require form-label">{{ __('Maximum Daily Deemed Amount') }}</label>
                        <input type="text" name="deemed_amount"
                            class="form-control @error('deemed_amount') is-invalid @enderror"
                            value="{{ old('deemed_amount', $allowanceInternational->deemed_amount) }}">
                        @error('deemed_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <p>Only applies to payslips from March 2021 onwards</p>
                    <div class="form-group col-md-6">
                        <label class="require form-label">{{ __('Number of days') }}</label>
                        <input type="text" name="number_of_days"
                            class="form-control @error('number_of_days') is-invalid @enderror"
                            value="{{ old('number_of_days', $allowanceInternational->number_of_days) }}">
                        @error('number_of_days')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <input type="hidden" name="term" value="{{ $term }}">
                    <div class="d-flex justify-content-end">
                        <a class="btn btn-rc-outline"
                            href="{{ route('payroll.index', ['employee_id' => $allowanceInternational->employee_id]) }}">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="btn btn-rc-primary">{{ __('Update') }}</button>
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
        $('#editForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();
            $.ajax({
                type: 'POST',
                url: '{{ route("allowance-internationals.ajax-validate-update", $allowanceInternational->id) }}',
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
                        var input = $('[name="' + field + '"');
                        input.addClass('is-invalid');
                        input.after('<span class="text-danger error-text">' + messages[0] + '</span>');
                    });
                }
            });
        });
    });
</script>
@endpush