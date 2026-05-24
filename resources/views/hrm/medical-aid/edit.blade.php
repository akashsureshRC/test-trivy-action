@extends('layouts.main')

@section('page-title')
    {{ __('Medical Aid') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Payroll') }},
    {{ __('Medical Aid') }}
@endsection

@section('page-action')
    <div>
        <a href="{{ route('payroll.index', ['employee_id' => $medicalAid->employee_id, 'term' => $term]) }}" class="btn btn-rc-outline btn-sm">
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
            <form action="{{ route('medical-aid.update', $medicalAid) }}" method="POST" id="editForm">
            @csrf
            @method('PUT') {{-- Use PUT for update --}}
            <div class="row">
                <div class="col-md-12 col-xs-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">
                                    <label class="require form-label">{{ __('Total amount per period') }}</label>
                                    <input class="form-control @error('total_amount') is-invalid @enderror" type="number"
                                        step="0.01" min="0" name="total_amount"
                                        value="{{ old('total_amount', $medicalAid->total_amount) }}"
                                        placeholder="{{ __('Enter Amount') }}">
                                    @error('total_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <p>This is the total of the medical aid premium, including the employer and employee’s
                                    contribution.</p>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Portion contributed by employer') }}</label>
                                    <input class="form-control @error('employer_contribution') is-invalid @enderror"
                                        type="number" step="0.01" min="0" name="employer_contribution"
                                        value="{{ old('employer_contribution', $medicalAid->employer_contribution) }}"
                                        placeholder="{{ __('Enter Amount') }}">
                                    @error('employer_contribution')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <p>The system deducts the employer portion from the Total amount per period to determine the
                                    employee contribution automatically.</p>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Employee handles the payment') }}</label>
                                <input type="checkbox" class="form-check-input" name="employee_payment" value="1" id="employee_payment"
                                    {{ $medicalAid->employee_payment ? 'checked' : '' }}>
                            </div>

                            <div class="form-group col-md-6" id="beneficiary_section">
                                <label class="require form-label">{{ __('Beneficiary') }}</label>
                                <select class="form-control" id="employee_id" name="employee_id">
                                    <option value="">Select Employee</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}"
                                            {{ $employee->id == $medicalAid->employee_id ? 'selected' : '' }}>
                                            {{ $employee->first_name }} {{ $employee->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('employee_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6" id="tax_credits_section" style="display: none;">
                                <label class="require form-label">{{ __('Don\'t apply tax credits') }}</label>
                                <input type="checkbox" class="form-check-input" name="apply_tax_credits" value="1"
                                    {{ $medicalAid->apply_tax_credits ? 'checked' : '' }}>
                                <p>If paid by employee you can optionally skip applying tax credits on the payroll</p>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Members (incl. employee)') }}</label>
                                    <input class="form-control @error('members') is-invalid @enderror" type="number"
                                        step="0.01" min="0" name="members"
                                        value="{{ old('members', $medicalAid->members) }}"
                                        placeholder="{{ __('Enter Amount') }}">
                                    @error('members')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <input type="hidden" name="term" value="{{ $term }}">
                            <div class="d-flex justify-content-end gap-2">
                                <a class="btn btn-rc-outline"
                                    href="{{ route('payroll.index', ['employee_id' => $medicalAid->employee_id]) }}">
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
                url: '{{ route("medical-aid.ajax-validate-update", $medicalAid->id) }}',
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