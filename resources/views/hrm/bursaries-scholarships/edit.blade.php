@extends('layouts.main')

@section('page-title')
    {{ __('Bursaries & Scholarships') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Payroll') }},
    {{ __('Bursaries & Scholarships') }}
@endsection

@section('page-action')
    <div>
        <a href="{{ route('payroll.index', ['employee_id' => $bursaries_scholarships->employee_id, 'term' => $term]) }}" class="btn btn-rc-outline btn-sm">
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
            <form action="{{ route('bursaries-scholarships.update', $bursaries_scholarships) }}" method="POST" id="editForm">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-12 col-xs-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Taxable Portion') }}</label>
                                    <input class="form-control @error('taxable_portion') is-invalid @enderror"
                                        type="number" step="0.01" min="0" name="taxable_portion"
                                        value="{{ old('taxable_portion', $bursaries_scholarships->taxable_portion) }}"
                                        placeholder="{{ __('Enter Amount') }}">
                                    @error('taxable_portion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Exempt Portion') }}</label>
                                    <input class="form-control @error('exempt_portion') is-invalid @enderror" type="number"
                                        step="0.01" min="0" name="exempt_portion"
                                        value="{{ old('exempt_portion', $bursaries_scholarships->exempt_portion) }}"
                                        placeholder="{{ __('Enter Amount') }}">
                                    @error('exempt_portion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Type') }}</label>
                                    <select name="bursary_type" id="bursary_type"
                                        class="form-control @error('bursary_type') is-invalid @enderror">
                                        <option value="">Select Taxable Percentage</option>
                                        <option value="Basic Education(Grade R to 12 and NQF levels 1 to 4)"
                                            {{ $bursaries_scholarships->bursary_type == 'Basic Education(Grade R to 12 and NQF levels 1 to 4)' ? 'selected' : '' }}>
                                            Basic Education (Grade R to 12 and NQF levels 1 to 4)
                                        </option>
                                        <option value="Further Education(NQF levels 5 to 10)"
                                            {{ $bursaries_scholarships->bursary_type == 'Further Education(NQF levels 5 to 10)' ? 'selected' : '' }}>
                                            Further Education (NQF levels 5 to 10)
                                        </option>
                                    </select>
                                    @error('bursary_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>


                                <div class="form-group col-md-6">
                                    <input type="hidden" name="employee_handles_payment" value="0">
                                    <label>
                                        <input type="checkbox" class="form-check-input" name="employee_handles_payment" value="1"
                                            {{ $bursaries_scholarships->employee_handles_payment == '1' ? 'checked' : '' }}>
                                        Employee Handles Payment
                                    </label>
                                </div>


                                <div class="form-group col-md-6">
                                    <input type="hidden" name="to_disabled_person" value="No">
                                    <label>
                                        <input type="checkbox" class="form-check-input" name="to_disabled_person" value="Yes"
                                            {{ $bursaries_scholarships->to_disabled_person == 'Yes' ? 'checked' : '' }}>
                                        To Disabled Person
                                    </label>
                                </div>
                               
                                <input type="hidden" name="term" value="{{ $term }}">
                                <div class="d-flex justify-content-end gap-2">
                                    <a class="btn btn-rc-outline"
                                        href="{{ route('payroll.index', ['employee_id' => $bursaries_scholarships->employee_id]) }}">
                                        {{ __('Cancel') }}
                                    </a>
                                    <button class="btn btn-rc-primary"
                                        type="submit">{{ __('Update') }}</button>
                                </div>
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
                url: '{{ route("bursaries-scholarships.ajax-validate-update", $bursaries_scholarships->id) }}',
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