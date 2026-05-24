@extends('layouts.main')

@section('page-title')
    {{ __('Commission') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Payroll') }},
    {{ __('Commission') }}
@endsection

@section('page-action')
    <div>
        <a href="{{ route('payroll.index', ['employee_id' => $payslipCommission->employee_id, 'term' => $term]) }}" class="btn btn-rc-outline btn-sm">
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
            <form id="editForm" action="{{ route('payslip-commissions.update', $payslipCommission) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-12 col-xs-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            {{-- <div class="form-group col-md-6">
                            <label class="require form-label">{{ __('Name_Payslip') }}</label>
                            <input class="form-control @error('name_payslip') is-invalid @enderror"
                                   type="text" name="name_payslip"
                                   value="{{ old('name_payslip', $payslipCommission->name_payslip) }}"
                                   required placeholder="{{ __('Enter Name_Payslip ') }}">
                            @error('name_payslip')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div> --}}


                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Commission') }}</label>
                                <input class="form-control @error('commission_amount') is-invalid @enderror" type="number"
                                    step="0.01" name="commission_amount"
                                    value="{{ old('commission_amount', $payslipCommission->commission_amount) }}" 
                                    placeholder="{{ __('Enter Commission') }}">
                                @error('commission_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">

                                <label class="require form-label">{{ __('Commission_Type') }}</label>
                                <select name="commission_type"
                                    class="form-control @error('commission_type') is-invalid @enderror">
                                    <option value="">Select Type</option>
                                    <option value="percentage"
                                        {{ old('commission_type', $payslipCommission->commission_type) == 'percentage' ? 'selected' : '' }}>
                                        % (Percentage)</option>
                                    <option value="flat"
                                        {{ old('commission_type', $payslipCommission->commission_type) == 'flat' ? 'selected' : '' }}>
                                        Flat</option>
                                </select>
                                @error('commission_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="require form-label">{{ __('Status') }}</label>
                                <select class="form-control @error('status') is-invalid @enderror" name="status">
                                    <option value="">{{ __('Select Status') }}</option>
                                    <option value="Active"
                                        {{ old('status', $payslipCommission->status) == 'Active' ? 'selected' : '' }}>
                                        {{ __('Active') }}</option>
                                    <option value="Inactive"
                                        {{ old('status', $payslipCommission->status) == 'Inactive' ? 'selected' : '' }}>
                                        {{ __('Inactive') }}</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <input type="hidden" name="term" value="{{ request('term') }}">
                        <div class="d-flex justify-content-end gap-2">
                            <a class="btn btn-rc-outline"
                                href="{{ route('payroll.index', ['employee_id' => $payslipCommission->employee_id]) }}">{{ __('Cancel') }}</a>
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
                url: '{{ route("payslip-commissions.ajax-validate-update", $payslipCommission->id) }}',
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