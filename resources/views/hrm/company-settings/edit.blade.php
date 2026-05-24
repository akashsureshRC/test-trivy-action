@extends('layouts.main')

@section('page-title')
    {{ __('Edit Company Settings') }}
@endsection

@section('page-breadcrumb')
    {{ __('Payroll') }},
    {{ __('Edit Company Settings') }}
@endsection

@php
    $company_settings = getCompanyAllSetting();
@endphp

@section('content')
<form action="{{ route('company-settings.update',$companySetting->id) }}" class="mt-3" method="post">
    @csrf
    @method('put')
    <div class="card py-4 px-4 mt-4">
        <div class="row">
            <div class="col-md-4">
                <label class="form-label require" for="minimum_wage">{{ __('Minimum Wage Inputs') }}</label>
                <select name="minimum_wage" id="minimum_wage" class="form-control @error('minimum_wage') is-invalid @enderror">
                    <option value="">Select Minimum Wage Option</option>
                    <option value="not_required" {{ old('minimum_wage',$companySetting->minimum_wage) == 'not_required' ? 'selected' : '' }}>Not Required</option>
                    <option value="monthly_amount" {{ old('minimum_wage',$companySetting->minimum_wage) == 'monthly_amount' ? 'selected' : '' }}>Monthly Amount</option>
                    <option value="hourly_rate" {{ old('minimum_wage',$companySetting->minimum_wage) == 'hourly_rate' ? 'selected' : '' }}>Hourly Rate</option>
                </select>
                @error('minimum_wage') 
                    <div class="invalid-feedback">{{ $message }}</div> 
                @enderror
                <p class="fs-8 py-2">Choose 'Not Required' if the company is exempt from the National Minimum Wage Act.</p>
            </div>
        </div>
        
        <div class="row mt-3">
            <div id="monthly_amount_box" class="col-md-6" style="display: none;">
               <!-- <label for="minimum_wage_monthly">Minimum Wage Monthly:</label>
                <input type="number" name="minimum_wage_monthly" id="minimum_wage_monthly" class="form-control">-->
                <label class="require form-label">{{ __('Minimum Wage Monthly:') }}</label>
                <input class="form-control @error('minimum_wage_monthly') is-invalid @enderror" 
                type="number" step="0.01" name="minimum_wage_monthly" id="minimum_wage_monthly" value="{{ old('minimum_wage_monthly',$companySetting->minimum_wage_monthly) }}" 
                required placeholder="{{ __('Enter Amount') }}">
                @error('minimum_wage_monthly') 
                    <div class="invalid-feedback">{{ $message }}</div> 
                @enderror
            </div>
            
            <div id="hourly_rate_box" class="col-md-4" style="display: none;">
               <!-- <label for="minimum_wage_normal_rate">Minimum Wage Normal Rate:</label>
                <input type="number" name="minimum_wage_normal_rate" id="minimum_wage_normal_rate" class="form-control">-->
                <label class="require form-label">{{ __('Minimum Wage Normal Rate:') }}</label>
                            <input class="form-control @error('minimum_wage_normal_rate') is-invalid @enderror" 
                                   type="number" step="0.01" name="minimum_wage_normal_rate" id="minimum_wage_normal_rate" value="{{ old('minimum_wage_normal_rate',$companySetting->minimum_wage_normal_rate) }}" 
                                   required placeholder="{{ __('Enter Amount') }}">
                @error('minimum_wage_normal_rate') 
                    <div class="invalid-feedback">{{ $message }}</div> 
                @enderror
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-4">
                <input type="checkbox" class="form-check-input" id="special_economic_zone" name="special_economic_zone" value="Special Economic Zone (Prior to March 2019)" onclick="toggleRow('publicRow')">
                <label for="special_economic_zone"> Special Economic Zone (Prior to March 2019)</label>
            </div>
        </div>

        <div class="row mt-3" id="publicRow" style="display: none;">
            <div class="col-md-4">
                <label class="form-label require">{{ __('Economic Zone') }}</label>
                <select name="economic_zone" class="form-control @error('economic_zone') is-invalid @enderror">
                    <option value="">Select SDL Registration</option>
                    <option value="Coega(Port Elizabeth Area)"{{ old('economic_zone',$companySetting->economic_zone) == 'Coega(Port Elizabeth Area)' ? 'selected' : '' }}>Coega (Port Elizabeth Area)</option>
                    <option value="Dube Tradeport(KZN)" {{ old('economic_zone',$companySetting->economic_zone) == 'Dube Tradeport(KZN)' ? 'selected' : '' }}>Dube Tradeport (KZN)</option>
                    <option value="Industrial Development Zone(East London)" {{ old('economic_zone',$companySetting->economic_zone) == 'Industrial Development Zone(East London)' ? 'selected' : '' }}>Industrial Development Zone (East London)</option>
                    <option value="Maluti-a-Phofung(Bethlehem Area)" {{ old('economic_zone',$companySetting->economic_zone) == 'Maluti-a-Phofung(Bethlehem Area)' ? 'selected' : '' }}>Maluti-a-Phofung (Bethlehem Area)</option>
                    <option value="Richards Bay(KZN)" {{ old('economic_zone',$companySetting->economic_zone) == 'Richards Bay(KZN)' ? 'selected' : '' }}>Richards Bay (KZN)</option>
                    
                    <option value="Saldanha Bay(Western Cape)" {{ old('economic_zone',$companySetting->economic_zone) == 'Saldanha Bay(Western Cape)' ? 'selected' : '' }}>Saldanha Bay (Western Cape)</option>
                </select>
                @error('economic_zone') 
                    <div class="invalid-feedback">{{ $message }}</div> 
                @enderror
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-4">
                <label class="form-label require">{{ __('Effective from:') }}</label>
                <input type="date" name="effective_from" class="form-control @error('effective_from') is-invalid @enderror" value="{{ old('effective_from',$companySetting->effective_from) }}">
                @error('effective_from')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
    
    <div class="d-flex justify-content-start mt-4">
        <button type="button" class="btn btn-rc-outline">Cancel</button>
        <input class="btn btn-rc-primary ms-3" type="submit" value="Save">
    </div>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('#minimum_wage').change(function () {
            var selectedValue = $(this).val();
            $('#monthly_amount_box').toggle(selectedValue === 'monthly_amount');
            $('#hourly_rate_box').toggle(selectedValue === 'hourly_rate');
        });
    });
    
    function toggleRow(rowId) {
        $('#' + rowId).toggle();
    }
</script>
@endsection
