@extends('layouts.main')

@section('page-title')
    {{ __('Income Protection') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Payroll') }},
    {{ __('Income Protection') }}
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
            <form action="{{ route('income-protection.store') }}" method="POST" id="createForm">

        @csrf
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Amount paid by employee (not deducted)') }}</label>
                                <input class="form-control @error('amount') is-invalid @enderror"
                                       type="number" step="0.01" min="0" name="amount" value="{{ old('amount') }}"
                                       placeholder="{{ __('Enter Amount') }}">
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Amount deducted from employee') }}</label>
                                <input class="form-control @error('amount_deducted') is-invalid @enderror"
                                       type="number" step="0.01" min="0" name="amount_deducted" value="{{ old('amount_deducted') }}"
                                       placeholder="{{ __('Enter Amount') }}">
                                @error('amount_deducted')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Amount paid by employer') }}</label>
                                <input class="form-control @error('amount_paid') is-invalid @enderror"
                                       type="number" step="0.01" min="0" name="amount_paid" value="{{ old('amount_paid') }}"
                                       placeholder="{{ __('Enter Amount') }}">
                                @error('amount_paid')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="employer_own" value="1" id="employer_own">
                                    <label class="form-check-label" for="employer_own">{{ __('Employer owns the policy') }}</label>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">
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
                url: '{{ route("income-protection.ajax-validate-store") }}',
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