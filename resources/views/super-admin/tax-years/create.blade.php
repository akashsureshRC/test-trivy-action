@extends('layouts.main')

@section('page-title')
{{ __('Create Tax Year') }}
@endsection

@section('page-breadcrumb')
{{ __('Tax Settings') }}, {{ __('Create') }}
@endsection

@section('page-action')
<div>
    <a href="{{ route('tax-years.index') }}" class="btn btn-sm btn-rc-outline">
        <i class="ti ti-arrow-left me-1"></i>{{ __('Back') }}
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <form method="POST" action="{{ route('tax-years.store') }}" id="tax-year-form">
            @csrf

            {{-- Basic Info --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __('Basic Information') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label class="require form-label">{{ __('Label') }}</label>
                                    <input type="text" name="label" class="form-control @error('label') is-invalid @enderror"
                                        value="{{ old('label') }}" placeholder="e.g. 2025/2026" required>
                                    @error('label')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="require form-label">{{ __('Effective From') }}</label>
                                    <input type="date" name="effective_from" class="form-control @error('effective_from') is-invalid @enderror"
                                        value="{{ old('effective_from') }}" required>
                                    @error('effective_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="require form-label">{{ __('Effective To') }}</label>
                                    <input type="date" name="effective_to" class="form-control @error('effective_to') is-invalid @enderror"
                                        value="{{ old('effective_to') }}" required>
                                    @error('effective_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tax Brackets --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>{{ __('PAYE Tax Brackets') }}</h5>
                            <button type="button" class="btn btn-sm btn-rc-outline" id="add-bracket-btn">
                                <i class="ti ti-plus me-1"></i>{{ __('Add Bracket') }}
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="brackets-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Min Income (R)') }}</th>
                                            <th>{{ __('Max Income (R)') }}</th>
                                            <th>{{ __('Base Tax (R)') }}</th>
                                            <th>{{ __('Rate (decimal)') }}</th>
                                            <th>{{ __('Threshold (R)') }}</th>
                                            <th style="width: 60px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="brackets-body">
                                        @php
                                        $brackets = old('brackets', [
                                        ['min' => 1, 'max' => '', 'base_tax' => 0, 'rate' => '', 'threshold' => 0]
                                        ]);
                                        @endphp
                                        @foreach($brackets as $i => $bracket)
                                        <tr class="bracket-row">
                                            <td><input type="number" step="1" name="brackets[{{ $i }}][min]" class="form-control form-control-sm" value="{{ $bracket['min'] }}" required></td>
                                            <td><input type="number" step="1" name="brackets[{{ $i }}][max]" class="form-control form-control-sm" value="{{ $bracket['max'] }}" required></td>
                                            <td><input type="number" step="0.01" name="brackets[{{ $i }}][base_tax]" class="form-control form-control-sm" value="{{ $bracket['base_tax'] }}" required></td>
                                            <td><input type="number" step="0.01" name="brackets[{{ $i }}][rate]" class="form-control form-control-sm" value="{{ $bracket['rate'] }}" required></td>
                                            <td><input type="number" step="1" name="brackets[{{ $i }}][threshold]" class="form-control form-control-sm" value="{{ $bracket['threshold'] }}" required></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger remove-bracket-btn" title="{{ __('Remove') }}">
                                                    <i class="ti ti-x"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @error('brackets')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rebates --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __('Tax Rebates') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label class="require form-label">{{ __('Primary Rebate (R)') }}</label>
                                    <input type="number" step="0.01" name="primary_rebate" class="form-control @error('primary_rebate') is-invalid @enderror"
                                        value="{{ old('primary_rebate') }}" required>
                                    @error('primary_rebate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="require form-label">{{ __('Secondary Rebate (R)') }}</label>
                                    <input type="number" step="0.01" name="secondary_rebate" class="form-control @error('secondary_rebate') is-invalid @enderror"
                                        value="{{ old('secondary_rebate') }}" required>
                                    @error('secondary_rebate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="require form-label">{{ __('Tertiary Rebate (R)') }}</label>
                                    <input type="number" step="0.01" name="tertiary_rebate" class="form-control @error('tertiary_rebate') is-invalid @enderror"
                                        value="{{ old('tertiary_rebate') }}" required>
                                    @error('tertiary_rebate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Secondary Rebate Age') }}</label>
                                    <input type="number" name="secondary_rebate_age" class="form-control @error('secondary_rebate_age') is-invalid @enderror"
                                        value="{{ old('secondary_rebate_age', 65) }}" required>
                                    @error('secondary_rebate_age')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Tertiary Rebate Age') }}</label>
                                    <input type="number" name="tertiary_rebate_age" class="form-control @error('tertiary_rebate_age') is-invalid @enderror"
                                        value="{{ old('tertiary_rebate_age', 75) }}" required>
                                    @error('tertiary_rebate_age')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statutory Rates --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __('Statutory Rates') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label class="require form-label">{{ __('UIF Rate') }}</label>
                                    <input type="number" step="0.0001" name="uif_rate" class="form-control @error('uif_rate') is-invalid @enderror"
                                        value="{{ old('uif_rate', '0.01') }}" required>
                                    <small class="form-text text-muted">{{ __('e.g. 0.01 for 1%') }}</small>
                                    @error('uif_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="require form-label">{{ __('UIF Monthly Ceiling (R)') }}</label>
                                    <input type="number" step="0.01" name="uif_ceiling" class="form-control @error('uif_ceiling') is-invalid @enderror"
                                        value="{{ old('uif_ceiling', '177.12') }}" required>
                                    @error('uif_ceiling')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="require form-label">{{ __('SDL Rate') }}</label>
                                    <input type="number" step="0.0001" name="sdl_rate" class="form-control @error('sdl_rate') is-invalid @enderror"
                                        value="{{ old('sdl_rate', '0.01') }}" required>
                                    <small class="form-text text-muted">{{ __('e.g. 0.01 for 1%') }}</small>
                                    @error('sdl_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ETI --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __('Employment Tax Incentive (ETI)') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label class="require form-label">{{ __('Minimum Age') }}</label>
                                    <input type="number" name="eti_min_age" class="form-control @error('eti_min_age') is-invalid @enderror"
                                        value="{{ old('eti_min_age', 18) }}" required>
                                    @error('eti_min_age')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="require form-label">{{ __('Maximum Age') }}</label>
                                    <input type="number" name="eti_max_age" class="form-control @error('eti_max_age') is-invalid @enderror"
                                        value="{{ old('eti_max_age', 29) }}" required>
                                    @error('eti_max_age')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="require form-label">{{ __('Salary Cap (R)') }}</label>
                                    <input type="number" step="0.01" name="eti_salary_cap" class="form-control @error('eti_salary_cap') is-invalid @enderror"
                                        value="{{ old('eti_salary_cap', '6500') }}" required>
                                    @error('eti_salary_cap')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('ETI Rate') }}</label>
                                    <input type="number" step="0.0001" name="eti_rate" class="form-control @error('eti_rate') is-invalid @enderror"
                                        value="{{ old('eti_rate', '0.50') }}" required>
                                    <small class="form-text text-muted">{{ __('e.g. 0.50 for 50%') }}</small>
                                    @error('eti_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="require form-label">{{ __('Maximum ETI Amount (R)') }}</label>
                                    <input type="number" step="0.01" name="eti_max_amount" class="form-control @error('eti_max_amount') is-invalid @enderror"
                                        value="{{ old('eti_max_amount', '1000') }}" required>
                                    @error('eti_max_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Other Rates --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __('Other Rates') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label class="require form-label">{{ __('OT Multiplier') }}</label>
                                    <input type="number" step="0.01" name="ot_multiplier" class="form-control @error('ot_multiplier') is-invalid @enderror"
                                        value="{{ old('ot_multiplier', '1.50') }}" required>
                                    <small class="form-text text-muted">{{ __('e.g. 1.50 for time-and-a-half') }}</small>
                                    @error('ot_multiplier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="require form-label">{{ __('Medical Aid Tax Rate') }}</label>
                                    <input type="number" step="0.0001" name="medical_aid_tax_rate" class="form-control @error('medical_aid_tax_rate') is-invalid @enderror"
                                        value="{{ old('medical_aid_tax_rate', '0.10') }}" required>
                                    <small class="form-text text-muted">{{ __('e.g. 0.10 for 10%') }}</small>
                                    @error('medical_aid_tax_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="require form-label">{{ __('Travel Allowance Tax Rate') }}</label>
                                    <input type="number" step="0.0001" name="travel_allowance_tax_rate" class="form-control @error('travel_allowance_tax_rate') is-invalid @enderror"
                                        value="{{ old('travel_allowance_tax_rate', '0.10') }}" required>
                                    <small class="form-text text-muted">{{ __('e.g. 0.10 for 10%') }}</small>
                                    @error('travel_allowance_tax_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="row mb-4">
                <div class="col-md-12 text-end">
                    <a href="{{ route('tax-years.index') }}" class="btn btn-secondary me-2">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-rc-primary">
                        <i class="ti ti-device-floppy me-1"></i>{{ __('Save Tax Year') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var bracketIndex = {{ count($brackets) }};
        var addBtn = document.getElementById('add-bracket-btn');
        var tbody = document.getElementById('brackets-body');

        addBtn.addEventListener('click', function() {
            var tr = document.createElement('tr');
            tr.className = 'bracket-row';
            tr.innerHTML = '<td><input type="number" step="1" name="brackets[' + bracketIndex + '][min]" class="form-control form-control-sm" required></td>' +
                '<td><input type="number" step="1" name="brackets[' + bracketIndex + '][max]" class="form-control form-control-sm" required></td>' +
                '<td><input type="number" step="0.01" name="brackets[' + bracketIndex + '][base_tax]" class="form-control form-control-sm" required></td>' +
                '<td><input type="number" step="0.01" name="brackets[' + bracketIndex + '][rate]" class="form-control form-control-sm" required></td>' +
                '<td><input type="number" step="1" name="brackets[' + bracketIndex + '][threshold]" class="form-control form-control-sm" required></td>' +
                '<td class="text-center"><button type="button" class="btn btn-sm btn-danger remove-bracket-btn" title="{{ __("Remove") }}"><i class="ti ti-x"></i></button></td>';
            tbody.appendChild(tr);
            bracketIndex++;
        });

        tbody.addEventListener('click', function(e) {
            var btn = e.target.closest('.remove-bracket-btn');
            if (btn) {
                var rows = tbody.querySelectorAll('.bracket-row');
                if (rows.length > 1) {
                    btn.closest('tr').remove();
                }
            }
        });
    });
</script>
@endpush