@extends('layouts.main')

@section('page-title')
    {{ __('Medical Costs (Other)') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Payroll') }},
    {{ __('Medical Costs') }}
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
            <form id="createForm" action="{{ route('medical-costs.store') }}" method="POST">

        @csrf
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Amount') }}</label>
                                <input type="number" step="0.01" min="0" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                       value="{{ old('amount') }}" placeholder="{{ __('Enter Amount') }}">
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Beneficiary of Medical Costs') }}</label>
                                <select name="medical_cost" class="form-control @error('medical_cost') is-invalid @enderror">
                                    <option value="">{{ __('Select Medical Costs') }}</option>
                                    <option value="Employee,Spouse or Child" {{ old('medical_cost') == 'Employee,Spouse or Child' ? 'selected' : '' }}>{{ __('Employee, Spouse or Child') }}</option>
                                    <option value="others relatives or Dependents" {{ old('medical_cost') == 'others relatives or Dependents' ? 'selected' : '' }}>{{ __('Other Relatives or Dependents') }}</option>
                                </select>
                                @error('medical_cost') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                url: '{{ route("medical-costs.ajax-validate-store") }}',
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