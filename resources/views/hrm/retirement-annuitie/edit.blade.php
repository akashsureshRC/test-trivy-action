@extends('layouts.main')

@section('page-title')
    {{ __('Retirement Annuity Fund') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Payroll') }},
    {{ __('Retirement Annuity Fund') }}
@endsection

@section('page-action')
    <div>
        <a href="{{ route('payroll.index', ['employee_id' => $retirementAnnuity->employee_id, 'term' => $term]) }}" class="btn btn-rc-outline btn-sm">
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
            <form id="editForm" action="{{ route('retirement-annuitie.update', $retirementAnnuity) }}" method="POST">
            @csrf
            @method('PUT') {{-- Add PUT method for update --}}
            <div class="row">
                <div class="col-md-12 col-xs-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">
                                    <label class="require form-label">{{ __('Amount per month') }}</label>
                                    <input class="form-control @error('amount') is-invalid @enderror" type="number"
                                        step="0.01" min="0" name="amount"
                                        value="{{ old('amount', $retirementAnnuity->amount) }}"
                                        placeholder="{{ __('Enter Amount') }}">
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Portion contributed by employer') }}</label>
                                    <input class="form-control @error('portion') is-invalid @enderror" type="number"
                                        step="0.01" min="0" name="Portion"
                                        value="{{ old('portion', $retirementAnnuity->portion) }}"
                                        placeholder="{{ __('Enter Amount') }}">
                                    @error('portion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Employee handles the payment') }}</label>
                                <input type="checkbox" name="employee_payment" value="1" id="employee_payment"
                                    {{ $retirementAnnuity->employee_payment ? 'checked' : '' }}>
                            </div>

                            <div class="form-group col-md-6" id="beneficiary_section">
                                <label class="require form-label">{{ __('Beneficiary') }}</label>
                                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                <input class="form-control" value="{{ $employee->first_name }} {{ $employee->last_name }}"
                                    readonly>
                                @error('employee_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <input type="hidden" name="term" value="{{ $term }}">

                            <div class="d-flex justify-content-end gap-2">
                                <a class="btn btn-rc-outline"
                                    href="{{ route('payroll.index', ['employee_id' => $retirementAnnuity->employee_id]) }}">
                                    {{ __('Cancel') }}
                                </a>
                                <button class="btn btn-rc-primary" type="submit">{{ __('Update') }}</button>
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
        $('#editForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = form.serialize();
            $.ajax({
                type: 'POST',
                url: '{{ route("retirement-annuitie.ajax-validate-update", $retirementAnnuity->id) }}',
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