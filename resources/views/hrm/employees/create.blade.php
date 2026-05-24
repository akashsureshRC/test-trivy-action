@extends('layouts.main')

@section('page-title')
    {{ __('Create Employee') }}
@endsection

@section('page-breadcrumb')
    {{ __('Employees') }}, {{ __('Create Employee') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <form id="employeeForm" action="{{ route('employees.save') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Employee Profile Section --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="form-section-title mb-0">{{ __('Employee Profile') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Employee ID') }} <span class="text-danger">*</span></label>
                                    <input type="text" id="employee_id" name="employee_id" class="form-control"
                                        value="{{ old('employee_id', $employeeId) }}">
                                    @error('employee_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="salutation" class="form-label">{{ __('Title') }} <span class="text-danger">*</span></label>
                                    <select id="salutation" name="salutation" class="form-control">
                                        <option value="">{{ __('Select') }}</option>
                                        <option value="Mr" {{ old('salutation') == 'Mr' ? 'selected' : '' }}>Mr</option>
                                        <option value="Mrs" {{ old('salutation') == 'Mrs' ? 'selected' : '' }}>Mrs</option>
                                        <option value="Dr" {{ old('salutation') == 'Dr' ? 'selected' : '' }}>Dr</option>
                                        <option value="Miss" {{ old('salutation') == 'Miss' ? 'selected' : '' }}>Miss</option>
                                    </select>
                                    @error('salutation')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_name" class="form-label">{{ __('First Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" id="first_name" name="first_name" class="form-control"
                                        value="{{ old('first_name') }}" onkeypress="blockNumbers(event)" autocomplete="off">
                                    @error('first_name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="last_name" class="form-label">{{ __('Last Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" id="last_name" name="last_name" class="form-control"
                                        value="{{ old('last_name') }}" onkeypress="blockNumbers(event)">
                                    @error('last_name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="branch_id" class="form-label">{{ __('Branch') }} <span class="text-danger">*</span></label>
                                    <select id="branch_id" name="branch_id" class="form-control">
                                        <option value="">{{ __('Select Branch') }}</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}"
                                                {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('branch_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="department" class="form-label">{{ __('Department') }} <span class="text-danger">*</span></label>
                                    <select id="department_id" name="department_id" class="form-control">
                                        <option value="">{{ __('Select Department') }}</option>
                                        @if (old('branch_id'))
                                            @php
                                                $branchDepartments = App\Models\Hrm\Department::where('branch_id', old('branch_id'))
                                                    ->where('workspace', getActiveWorkspace())->get();
                                            @endphp
                                            @foreach ($branchDepartments as $department)
                                                <option value="{{ $department->id }}"
                                                    {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                    {{ $department->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                        <option value="add_new">➕ {{ __('Add New') }}</option>
                                    </select>
                                    @error('department_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="designation" class="form-label">{{ __('Designation') }} <span class="text-danger">*</span></label>
                                    <select id="designation" name="designation_id" class="form-control"
                                        onchange="checkDesignation(this)">
                                        <option value="">{{ __('Select Designation') }}</option>
                                        @if (old('department_id'))
                                            @php
                                                $designations = App\Models\Hrm\Designation::where(
                                                    'department_id',
                                                    old('department_id'),
                                                )->get();
                                            @endphp
                                            @foreach ($designations as $designation)
                                                <option value="{{ $designation->id }}"
                                                    {{ old('designation_id') == $designation->id ? 'selected' : '' }}>
                                                    {{ $designation->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                        <option value="add_new" title="Add a new designation">➕ {{ __('Add New') }}</option>
                                    </select>
                                    @error('designation_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone_number" class="form-label">{{ __('Phone Number') }}</label>
                                    <input type="number" id="phone_number" name="phone_number" class="form-control"
                                        value="{{ old('phone_number') }}">
                                    @error('phone_number')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">{{ __('Email') }} <span class="text-danger">*</span></label>
                                    <input type="email" id="email" name="email" class="form-control"
                                        value="{{ old('email') }}">
                                    @error('email')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_of_birth" class="form-label">{{ __('Date of Birth') }} <span class="text-danger">*</span></label>
                                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-control"
                                        value="{{ old('date_of_birth') }}" max="{{ date('Y-m-d') }}">
                                    @error('date_of_birth')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="gender" class="form-label">{{ __('Gender') }}</label>
                                    <select id="gender" name="gender" class="form-control">
                                        <option value="">{{ __('Select') }}</option>
                                        <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                        <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('gender')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pay_frequency" class="form-label">{{ __('Pay Frequency') }} <span class="text-danger">*</span></label>
                                    <select id="pay_frequency" name="pay_frequency" class="form-control">
                                        <option value="">{{ __('Select Pay Frequency') }}</option>
                                        @foreach (\App\Models\Hrm\PayFrequency::all() as $frequency)
                                            <option value="{{ $frequency->id }}"
                                                {{ old('pay_frequency') == $frequency->id ? 'selected' : '' }}>
                                                {{ $frequency->pay_frequency }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('pay_frequency')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_of_appointment" class="form-label">{{ __('Date of Appointment') }} <span class="text-danger">*</span></label>
                                    <input type="date" id="date_of_appointment" name="date_of_appointment"
                                        class="form-control" value="{{ old('date_of_appointment') }}">
                                    @error('date_of_appointment')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="identification_type" class="form-label">{{ __('Identification Type') }} <span class="text-danger">*</span></label>
                                    <select id="identification_type" name="identification_type" class="form-control">
                                        <option value="">{{ __('Select Identification Type') }}</option>
                                        <option value="RSA ID" {{ old('identification_type') == 'RSA ID' ? 'selected' : '' }}>RSA ID</option>
                                        <option value="Passport/foreign id" {{ old('identification_type') == 'Passport/foreign id' ? 'selected' : '' }}>Passport</option>
                                        <option value="Refugee id" {{ old('identification_type') == 'Refugee id' ? 'selected' : '' }}>Refugee Id</option>
                                    </select>
                                    @error('identification_type')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_number" class="form-label">{{ __('ID Number') }} <span class="text-danger">*</span></label>
                                    <input type="text" id="id_number" name="id_number" class="form-control"
                                        value="{{ old('id_number') }}" placeholder="{{ __('Enter ID Number') }}">
                                    @error('id_number')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tax_reference_number" class="form-label">{{ __('Tax Reference Number') }}</label>
                                    <input type="text" id="tax_reference_number" name="tax_reference_number" class="form-control"
                                        value="{{ old('tax_reference_number') }}">
                                    @error('tax_reference_number')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6" id="passport_country_div" style="display: none;">
                                <div class="form-group">
                                    <label for="passport_country" class="form-label">{{ __('Passport Country') }}</label>
                                    <select id="passport_country" name="passport_country" class="form-control">
                                        <option value="">{{ __('Select Country') }}</option>
                                        @foreach ($allCountries as $cou)
                                            <option value="{{ $cou->name }}"
                                                {{ old('passport_country') == $cou->name ? 'selected' : '' }}>
                                                {{ $cou->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('passport_country')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Address Section --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="form-section-title mb-0">{{ __('Address') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="flat_no" class="form-label">{{ __('House/Building/Flat No') }}</label>
                                    <input type="text" id="flat_no" name="flat_no" class="form-control"
                                        value="{{ old('flat_no') }}">
                                    @error('flat_no')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pincode" class="form-label">{{ __('Zip Code') }}</label>
                                    <input type="text" id="pincode" name="pincode" class="form-control"
                                        value="{{ old('pincode') }}">
                                    @error('pincode')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="street" class="form-label">{{ __('Area/Street/Village') }}</label>
                                    <input type="text" id="street" name="street" value="{{ old('street') }}"
                                        class="form-control">
                                    @error('street')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="city" class="form-label">{{ __('Town/City') }}</label>
                                    <input type="text" id="city" name="city" value="{{ old('city') }}"
                                        class="form-control">
                                    @error('city')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country" class="form-label">{{ __('Country') }}</label>
                                    <select id="country" name="country" class="form-control"
                                        onchange="getProvinces(this.value)">
                                        <option value="">{{ __('Select Country') }}</option>
                                        @foreach ($countries as $country)
                                            <option value="{{ $country->id }}" data-name="{{ $country->name }}"
                                                {{ old('country') == $country->id ? 'selected' : '' }}>
                                                {{ $country->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('country')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="state" class="form-label">{{ __('Province') }}</label>
                                    <select id="state" name="state" value="{{ old('state') }}"
                                        class="form-control">
                                        <option>{{ __('Select Province') }}</option>
                                        @if (old('country'))
                                            @php
                                                $oldCountryValue = old('country');
                                                $old_country = is_numeric($oldCountryValue)
                                                    ? App\Models\Hrm\Country::find($oldCountryValue)
                                                    : App\Models\Hrm\Country::where('name', $oldCountryValue)->first();

                                                if ($old_country && is_numeric($oldCountryValue)) {
                                                    $allCountryIds = App\Models\Hrm\Country::where('name', $old_country->name)->pluck('id');
                                                } else {
                                                    $allCountryIds = $old_country
                                                        ? App\Models\Hrm\Country::where('name', $old_country->name)->pluck('id')
                                                        : collect();
                                                }

                                                $provinces = $old_country
                                                    ? App\Models\Hrm\Province::whereIn('country_id', $allCountryIds)
                                                        ->where('status', 'Active')
                                                        ->selectRaw('MIN(id) as id, name')
                                                        ->groupBy('name')
                                                        ->orderBy('name')
                                                        ->get()
                                                    : [];
                                            @endphp
                                            @foreach ($provinces as $province)
                                                <option value="{{ $province->name }}"
                                                    {{ old('state') == $province->name ? 'selected' : '' }}>
                                                    {{ $province->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('state')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Emergency Contact Section --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="form-section-title mb-0">{{ __('Emergency Contact') }}</h5>
                    </div>
                    <div class="card-body">
                        <div id="table-body">
                            @php
                                $oldContacts = old('emergency_contact_name', []);
                                $oldPhones = old('emergency_contact_phone', []);
                                $contactCount = max(count($oldContacts), 1);
                            @endphp

                            @for ($i = 0; $i < $contactCount; $i++)
                                <div class="contact-row">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">{{ __('Contact Person') }}</label>
                                                <input type="text" name="emergency_contact_name[]" class="form-control"
                                                    placeholder="{{ __('Contact Person') }}" value="{{ $oldContacts[$i] ?? '' }}"
                                                    onkeypress="blockNumbers(event)">
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label class="form-label">{{ __('Phone Number') }}</label>
                                                <input type="number" name="emergency_contact_phone[]" class="form-control"
                                                    placeholder="{{ __('Phone Number') }}" value="{{ $oldPhones[$i] ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-1" style="margin-top: 32px;">
                                            <div class="form-group">
                                                @if ($i > 0)
                                                    <button type="button" class="btn btn-danger btn-sm remove-contact-btn">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                        <button type="button" id="add-contact-btn" class="btn btn-rc-primary btn-sm mt-2">
                            <i class="ti ti-plus"></i> {{ __('Add Emergency Contact') }}
                        </button>
                    </div>
                </div>

                {{-- Attendance Settings Section --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="form-section-title mb-0">{{ __('Attendance Settings') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Attendance Tracking') }}</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="attendance_enabled"
                                            id="attendance_enabled" value="1"
                                            {{ old('attendance_enabled', 1) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="attendance_enabled">
                                            {{ __('Enable attendance tracking for this employee') }}
                                        </label>
                                    </div>
                                    <small class="text-muted">{{ __('When enabled, employee can clock in/out via the mobile app') }}</small>
                                </div>
                            </div>
                            <div class="col-md-12">
                               <div class="form-group" style="margin: 0px">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="use_custom_working_hours"
                                            id="use_custom_working_hours" value="1"
                                            {{ old('use_custom_working_hours') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="use_custom_working_hours">
                                            {{ __('Use Custom Working Hours (Overwrites Branch Timings)') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12" id="workingHoursSection" style="{{ old('use_custom_working_hours') ? '' : 'display:none;' }}">
                                <a href="javascript:void(0);"
                                    class="btn btn-sm btn-outline-primary"
                                    id="openWorkingHoursModalBtn">
                                    <i class="ti ti-clock"></i> {{ __('Configure Working Hours') }}
                                </a>
                                {{-- Hidden inputs that sync from the modal --}}
                                <div id="workingHoursHiddenInputs">
                                    @php
                                        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                    @endphp
                                    @foreach($days as $index => $day)
                                        @php
                                            $isWorking = old("working_hours.{$index}.is_working_day", in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']) ? '1' : '0');
                                            $startTime = old("working_hours.{$index}.start_time", '08:00');
                                            $endTime = old("working_hours.{$index}.end_time", '17:00');
                                            $lunchStart = old("working_hours.{$index}.lunch_start_time", '');
                                            $lunchEnd = old("working_hours.{$index}.lunch_end_time", '');
                                        @endphp
                                        <input type="hidden" name="working_hours[{{ $index }}][day]" value="{{ $day }}">
                                        <input type="hidden" name="working_hours[{{ $index }}][is_working_day]" class="wh-hidden-is-working" data-day="{{ $day }}" value="{{ $isWorking ? '1' : '' }}">
                                        <input type="hidden" name="working_hours[{{ $index }}][start_time]" class="wh-hidden-start" data-day="{{ $day }}" value="{{ $startTime }}">
                                        <input type="hidden" name="working_hours[{{ $index }}][end_time]" class="wh-hidden-end" data-day="{{ $day }}" value="{{ $endTime }}">
                                        <input type="hidden" name="working_hours[{{ $index }}][lunch_start_time]" class="wh-hidden-lunch-start" data-day="{{ $day }}" value="{{ $lunchStart }}">
                                        <input type="hidden" name="working_hours[{{ $index }}][lunch_end_time]" class="wh-hidden-lunch-end" data-day="{{ $day }}" value="{{ $lunchEnd }}">
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bank Account Details Section --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="form-section-title mb-0">{{ __('Bank Account Details') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bank" class="form-label">{{ __('Bank') }} <span class="text-danger">*</span></label>
                                    <select name="bank" id="bank" class="form-control">
                                        <option value="">{{ __('Select Bank') }}</option>
                                        <option value="ABSA Bank" {{ old('bank') == 'ABSA Bank' ? 'selected' : '' }}>ABSA Bank</option>
                                        <option value="African Bank" {{ old('bank') == 'African Bank' ? 'selected' : '' }}>African Bank</option>
                                        <option value="Bidvest Bank" {{ old('bank') == 'Bidvest Bank' ? 'selected' : '' }}>Bidvest Bank</option>
                                        <option value="Bank Zero" {{ old('bank') == 'Bank Zero' ? 'selected' : '' }}>Bank Zero</option>
                                        <option value="Capitec Bank" {{ old('bank') == 'Capitec Bank' ? 'selected' : '' }}>Capitec Bank</option>
                                        <option value="Capitec Business Bank" {{ old('bank') == 'Capitec Business Bank' ? 'selected' : '' }}>Capitec Business Bank</option>
                                        <option value="Discovery Bank" {{ old('bank') == 'Discovery Bank' ? 'selected' : '' }}>Discovery Bank</option>
                                        <option value="FNB" {{ old('bank') == 'FNB' ? 'selected' : '' }}>FNB</option>
                                        <option value="Investec" {{ old('bank') == 'Investec' ? 'selected' : '' }}>Investec</option>
                                        <option value="Mukuru bank" {{ old('bank') == 'Mukuru bank' ? 'selected' : '' }}>Mukuru bank</option>
                                        <option value="Nedbank" {{ old('bank') == 'Nedbank' ? 'selected' : '' }}>Nedbank</option>
                                        <option value="Postbank" {{ old('bank') == 'Postbank' ? 'selected' : '' }}>Postbank</option>
                                        <option value="Standard Bank" {{ old('bank') == 'Standard Bank' ? 'selected' : '' }}>Standard Bank</option>
                                        <option value="Sasfin Bank" {{ old('bank') == 'Sasfin Bank' ? 'selected' : '' }}>Sasfin Bank</option>
                                        <option value="TymeBank" {{ old('bank') == 'TymeBank' ? 'selected' : '' }}>TymeBank</option>
                                        <option value="Albaraka Bank" {{ old('bank') == 'Albaraka Bank' ? 'selected' : '' }}>Albaraka Bank</option>
                                        <option value="HBZ Bank" {{ old('bank') == 'HBZ Bank' ? 'selected' : '' }}>HBZ Bank</option>
                                    </select>
                                    @error('bank')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="account_number" class="form-label">{{ __('Account Number') }} <span class="text-danger">*</span></label>
                                    <input type="number" name="account_number" id="account_number" class="form-control"
                                        value="{{ old('account_number') }}">
                                    @error('account_number')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="branch_name" class="form-label">{{ __('Branch Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="branch_name" id="branch_name" class="form-control"
                                        value="{{ old('branch_name') }}">
                                    @error('branch_name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="branch_code" class="form-label">{{ __('Branch Code') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="branch_code" id="branch_code" value="{{ old('branch_code') }}"
                                        class="form-control">
                                    @error('branch_code')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="account_type" class="form-label">{{ __('Account Type') }} <span class="text-danger">*</span></label>
                                    <select name="account_type" id="account_type" class="form-control">
                                        <option value="">{{ __('Select Account Type') }}</option>
                                        <option value="savings" {{ old('account_type') == 'savings' ? 'selected' : '' }}>Savings</option>
                                        <option value="current" {{ old('account_type') == 'current' ? 'selected' : '' }}>Current</option>
                                        <option value="Bond" {{ old('account_type') == 'Bond' ? 'selected' : '' }}>Bond</option>
                                        <option value="Transmission" {{ old('account_type') == 'Transmission' ? 'selected' : '' }}>Transmission</option>
                                    </select>
                                    @error('account_type')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="holder_relationship" class="form-label">{{ __('Holder Relationship') }} <span class="text-danger">*</span></label>
                                    <select name="holder_relationship" id="holder_relationship" class="form-control">
                                        <option value="">{{ __('Select Relationship') }}</option>
                                        <option value="Own" {{ old('holder_relationship') == 'Own' ? 'selected' : '' }}>Own</option>
                                        <option value="Joined" {{ old('holder_relationship') == 'Joined' ? 'selected' : '' }}>Joined</option>
                                        <option value="Third Party" {{ old('holder_relationship') == 'Third Party' ? 'selected' : '' }}>Third Party</option>
                                    </select>
                                    @error('holder_relationship')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="card">
                    <div class="card-body d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-rc-outline"
                            onclick="window.location.href='{{ route('employees.list') }}'">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-rc-primary">{{ __('Add Employee') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Add Department Modal --}}
    <div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDepartmentModalLabel">{{ __('Add New Department') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addDepartmentForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="modal_branch_id" class="form-label">{{ __('Branch') }} <span class="text-danger">*</span></label>
                                    <select id="modal_branch_id" name="branch_id" class="form-control">
                                        <option value="">{{ __('Select Branch') }}</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                        <option value="add_new">➕ {{ __('Add New Branch') }}</option>
                                    </select>
                                    <small class="text-danger" id="branchError"></small>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="new_department" class="form-label">{{ __('Department Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" id="new_department" name="name" class="form-control">
                                    <small class="text-danger" id="nameError"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-rc-primary">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Add Branch Modal --}}
    <div class="modal fade" id="addBranchModal" tabindex="-1" aria-labelledby="addBranchModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBranchModalLabel">{{ __('Add New Branch') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addBranchForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="new_branch" class="form-label">{{ __('Branch Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" id="new_branch" name="name" class="form-control">
                                    <span class="error-text text-danger" id="error-name"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-rc-primary">{{ __('Save Branch') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Add Designation Modal --}}
    <div class="modal fade" id="addDesignationModal" tabindex="-1" aria-labelledby="addDesignationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDesignationModalLabel">{{ __('Add New Designation') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="designationForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="name" class="form-label">{{ __('Designation Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name">
                                    <small class="text-danger" id="error-name"></small>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="department_id" class="form-label">{{ __('Department') }} <span class="text-danger">*</span></label>
                                    <select id="designation_department" name="department_id" class="form-control">
                                        <option value="">{{ __('Select Department') }}</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-danger" id="error-department_id"></small>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="branch_id" class="form-label">{{ __('Branch') }} <span class="text-danger">*</span></label>
                                    <select id="branch_id" name="branch_id" class="form-control">
                                        <option value="">{{ __('Select Branch') }}</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-danger" id="error-branch_id"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-rc-primary">{{ __('Save Designation') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Custom Working Hours Modal --}}
    <div class="modal fade" id="workingHoursModal" tabindex="-1" aria-labelledby="workingHoursModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header" style="background: var(--rc-gray-50, #f8f9fa) !important;">
                    <h5 class="modal-title" id="workingHoursModalLabel">{{ __('Custom Working Hours') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <style>
                            .wh-time-input { width: 110px; }
                            .wh-lunch-inputs { display: flex; gap: 5px; align-items: center; }
                        </style>
                        <table class="table" id="workingHoursTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Day') }}</th>
                                    <th style="width:50px;">{{ __('Working') }}</th>
                                    <th>{{ __('Start') }}</th>
                                    <th>{{ __('End') }}</th>
                                    <th>{{ __('Lunch') }}</th>
                                    <th>{{ __('Hours') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                @endphp
                                @foreach($days as $index => $day)
                                    @php
                                        $isWorking = old("working_hours.{$index}.is_working_day", in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']) ? '1' : '0');
                                        $startTime = old("working_hours.{$index}.start_time", '08:00');
                                        $endTime = old("working_hours.{$index}.end_time", '17:00');
                                        $lunchStart = old("working_hours.{$index}.lunch_start_time", '');
                                        $lunchEnd = old("working_hours.{$index}.lunch_end_time", '');
                                    @endphp
                                    <tr data-day="{{ $day }}">
                                        <td><strong>{{ ucfirst($day) }}</strong></td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input working-day-toggle"
                                                    type="checkbox"
                                                    value="1"
                                                    data-day="{{ $day }}"
                                                    {{ $isWorking ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="time"
                                                class="form-control wh-time-input start-time"
                                                value="{{ $startTime }}"
                                                data-day="{{ $day }}"
                                                {{ !$isWorking ? 'disabled' : '' }}>
                                        </td>
                                        <td>
                                            <input type="time"
                                                class="form-control wh-time-input end-time"
                                                value="{{ $endTime }}"
                                                data-day="{{ $day }}"
                                                {{ !$isWorking ? 'disabled' : '' }}>
                                        </td>
                                        <td>
                                            <div class="wh-lunch-inputs">
                                                <input type="time"
                                                    class="form-control wh-time-input lunch-start"
                                                    value="{{ $lunchStart }}"
                                                    placeholder="Start"
                                                    data-day="{{ $day }}"
                                                    {{ !$isWorking ? 'disabled' : '' }}>
                                                <span>-</span>
                                                <input type="time"
                                                    class="form-control wh-time-input lunch-end"
                                                    value="{{ $lunchEnd }}"
                                                    placeholder="End"
                                                    data-day="{{ $day }}"
                                                    {{ !$isWorking ? 'disabled' : '' }}>
                                            </div>
                                        </td>
                                        <td class="hours-display" data-day="{{ $day }}">
                                            {{ $isWorking ? '9.0h' : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-secondary">
                                    <td colspan="5"><strong>{{ __('Total Weekly Hours') }}</strong></td>
                                    <td><strong id="totalWeeklyHours">45.0h</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="ti ti-info-circle"></i> {{ __('Leave lunch times empty if no lunch break applies.') }}
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-rc-outline" data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script>
        function checkDesignation(select) {
            if (select.value === "add_new") {
                $('#addDesignationModal').modal('show');
                select.value = '';
            }
        }
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            $('#designationForm').submit(function(e) {
                e.preventDefault();

                $.ajax({
                    url: "{{ route('designation.new_designation') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            let designationDropdown = document.getElementById("designation");
                            let departmentDropdown = document.getElementById("designation_department");
                            if (departmentDropdown.value == response.designation.department_id) {
                                let addNewOption = designationDropdown.querySelector('option[value="add_new"]');
                                if (addNewOption) {
                                    designationDropdown.removeChild(addNewOption);
                                }
                                let newOption = document.createElement("option");
                                newOption.value = response.designation.id;
                                newOption.textContent = response.designation.name;
                                designationDropdown.appendChild(newOption);
                                let newAddOption = document.createElement("option");
                                newAddOption.value = "add_new";
                                newAddOption.textContent = "Add a new designation";
                                designationDropdown.appendChild(newAddOption);
                            }

                            let addDesignationModal = document.getElementById('addDesignationModal');
                            let modalInstance = bootstrap.Modal.getInstance(addDesignationModal);
                            if (modalInstance) {
                                modalInstance.hide();
                            }
                            document.getElementById("name").value = "";
                        } else {
                            alert('Error: ' + response.error);
                        }
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $('#designationForm .text-danger').remove();
                            $.each(errors, function(field, messages) {
                                let inputField = $('[name="' + field + '"]');
                                if (inputField.length) {
                                    inputField.next('.text-danger').remove();
                                    inputField.after('<small class="text-danger">' + messages[0] + '</small>');
                                }
                            });
                        } else if (xhr.status === 401 || xhr.status === 403) {
                            toastr.error(xhr.responseJSON.error || 'Permission denied.');
                        } else {
                            toastr.error('An unexpected error occurred.');
                        }
                    }
                });
            });
        });
    </script>

    <script>
        document.getElementById('add-contact-btn').addEventListener('click', function() {
            let tableBody = document.getElementById('table-body');
            let newRow = document.createElement('div');
            newRow.classList.add('contact-row');

            newRow.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">{{ __('Contact Person') }}</label>
                        <input type="text" name="emergency_contact_name[]"
                               class="form-control"
                               placeholder="{{ __('Contact Person') }}">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label class="form-label">{{ __('Phone Number') }}</label>
                        <input type="tel" name="emergency_contact_phone[]"
                               class="form-control"
                               placeholder="{{ __('Phone Number') }}">
                    </div>
                </div>
                <div class="col-md-1" style="margin-top: 32px;">
                    <div class="form-group">
                        <button type="button" class="btn btn-danger btn-sm remove-contact-btn">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

            tableBody.appendChild(newRow);
        });

        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('remove-contact-btn') || event.target.closest('.remove-contact-btn')) {
                let btn = event.target.closest('.remove-contact-btn');
                btn.closest('.contact-row').remove();
            }
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Branch → Department cascade
            document.getElementById('branch_id').addEventListener('change', function() {
                let branchId = this.value;
                let departmentDropdown = document.getElementById('department_id');
                let designationDropdown = document.getElementById('designation');

                // Reset department and designation
                departmentDropdown.innerHTML = '<option value="">Select Department</option>';
                designationDropdown.innerHTML = '<option value="">Select Designation</option><option value="add_new" title="Add a new designation">➕ Add New</option>';

                if (branchId && branchId !== '') {
                    fetch(`/get-departments/${branchId}`)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(department => {
                                departmentDropdown.innerHTML += `<option value="${department.id}">${department.name}</option>`;
                            });
                            departmentDropdown.innerHTML += '<option value="add_new">➕ Add New</option>';
                        })
                        .catch(error => console.error('Error fetching departments:', error));
                }

                // Pre-select branch in the Add Department modal
                let modalBranchSelect = document.getElementById('modal_branch_id');
                if (modalBranchSelect) {
                    modalBranchSelect.value = branchId;
                }
            });

            // Department → Designation cascade
            document.getElementById('department_id').addEventListener('change', function() {
                let departmentId = this.value;

                if (departmentId === 'add_new') {
                    this.value = '';
                    // Pre-select the current branch in the modal
                    let currentBranch = document.getElementById('branch_id').value;
                    let modalBranchSelect = document.getElementById('modal_branch_id');
                    if (modalBranchSelect) modalBranchSelect.value = currentBranch;
                    // Clear previous errors
                    let branchErr = document.getElementById('branchError');
                    let nameErr = document.getElementById('nameError');
                    if (branchErr) branchErr.textContent = '';
                    if (nameErr) nameErr.textContent = '';
                    document.getElementById('new_department').value = '';
                    let addDepartmentModal = new bootstrap.Modal(document.getElementById('addDepartmentModal'));
                    addDepartmentModal.show();
                } else if (departmentId) {
                    fetch(`/get-designations/${departmentId}`)
                        .then(response => response.json())
                        .then(data => {
                            let designationDropdown = document.getElementById('designation');
                            designationDropdown.innerHTML = `<option value="">Select Designation</option>`;

                            data.forEach(designation => {
                                designationDropdown.innerHTML += `<option value="${designation.id}">${designation.name}</option>`;
                            });

                            designationDropdown.innerHTML += `<option value="add_new" title="Add a new designation">➕ Add New</option>`;
                        })
                        .catch(error => console.error('Error fetching designations:', error));
                }
            });
        });
    </script>

    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            const reader = new FileReader();
            reader.onload = function() {
                const image = document.getElementById('imagePreview');
                image.src = reader.result;
                image.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    </script>

    <script>
        function getProvinces(countryId) {
            if (countryId === "") {
                document.getElementById("state").innerHTML = '<option value="">Select Province</option>';
                return;
            }

            fetch(`/get-provinces/${encodeURIComponent(countryId)}`)
                .then(response => response.json())
                .then(data => {
                    let options = '<option value="">Select Province</option>';
                    Object.entries(data).forEach(([id, name]) => {
                        options += `<option value="${name}">${name}</option>`;
                    });
                    document.getElementById("state").innerHTML = options;
                })
                .catch(error => {
                    console.error("Error fetching provinces:", error);
                    document.getElementById("state").innerHTML = '<option value="">Error loading provinces</option>';
                });
        }
        document.addEventListener('DOMContentLoaded', function() {
            const identificationType = document.getElementById('identification_type');
            const passportDiv = document.getElementById('passport_country_div');
            identificationType.addEventListener('change', function() {
                if (this.value === 'Passport/foreign id') {
                    passportDiv.style.display = 'block';
                } else {
                    passportDiv.style.display = 'none';
                }
            });
            if (identificationType.value === 'Passport/foreign id') {
                passportDiv.style.display = 'block';
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#employeeForm').on('submit', function(e) {
                e.preventDefault();

                var form = $(this);
                var formData = form.serialize();

                $.ajax({
                    type: 'POST',
                    url: '{{ route('employees.ajax-validate') }}',
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

                        $.each(errors, function(field, messages) {
                            var input = $('[name="' + field + '"]');
                            input.after('<span class="text-danger error-text">' + messages[0] + '</span>');
                        });
                    }
                });
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let departmentModalBranchSelect = document.getElementById('modal_branch_id');
            if (departmentModalBranchSelect) {
                departmentModalBranchSelect.addEventListener('change', function() {
                    if (this.value === 'add_new') {
                        this.value = '';
                        let departmentModal = bootstrap.Modal.getInstance(document.getElementById('addDepartmentModal'));
                        departmentModal.hide();
                        let branchModal = new bootstrap.Modal(document.getElementById('addBranchModal'));
                        branchModal.show();
                    }
                });
            }
        });
        $(document).ready(function () {
            $('#addBranchForm').on('submit', function (e) {
                e.preventDefault();
                $('#addBranchForm .text-danger').remove();

                let branchName = $('#new_branch').val();

                $.ajax({
                    url: "{{ route('branch.store') }}",
                    type: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        name: branchName
                    },
                    success: function (response) {
                        if (response.success) {
                            let branchDropdowns = $('select[name="branch_id"]');
                            branchDropdowns.each(function () {
                                let currentDropdown = $(this);
                                currentDropdown.find('option[value="add_new"]').remove();
                                currentDropdown.append(new Option(response.branch.name, response.branch.id, false, true));
                                currentDropdown.append(new Option('➕ Add New Branch', 'add_new'));
                            });
                            $('#addBranchModal').modal('hide');
                            $('#new_branch').val('');
                            $('#addDepartmentModal').modal('show');
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function (field, messages) {
                                let inputField = $('#addBranchForm').find('[name="' + field + '"]');
                                if (inputField.length) {
                                    inputField.after('<span class="text-danger">' + messages[0] + '</span>');
                                }
                            });
                        } else {
                            console.error('Unexpected error:', xhr);
                        }
                    }
                });
            });
        });
    </script>
    <script>
        function blockNumbers(event) {
            const char = String.fromCharCode(event.which);
            const regex = /^[A-Za-z\s]+$/;
            if (!regex.test(char)) {
                event.preventDefault();
            }
        }
    </script>
    <script>
        function allowOnlyNumbers(event) {
            const char = String.fromCharCode(event.which);
            const regex = /^[0-9]+$/;
            if (!regex.test(char)) {
                event.preventDefault();
            }
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const departmentSelect = document.getElementById('department_id');
            const addDepartmentForm = document.getElementById('addDepartmentForm');

            function clearErrors() {
                document.getElementById('branchError').textContent = '';
                document.getElementById('nameError').textContent = '';
            }

            addDepartmentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                clearErrors();

                const branchId = document.getElementById('modal_branch_id').value.trim();
                const departmentName = document.getElementById('new_department').value.trim();

                fetch("{{ route('department.store') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                        },
                        body: JSON.stringify({
                            branch_id: branchId,
                            name: departmentName
                        }),
                    })
                    .then(async response => {
                        const data = await response.json();

                        if (response.ok && data.success) {
                            const option = document.createElement('option');
                            option.value = data.department.id;
                            option.textContent = data.department.name;
                            option.selected = true;

                            const addNewOption = departmentSelect.querySelector('option[value="add_new"]');
                            if (addNewOption) addNewOption.remove();

                            departmentSelect.appendChild(option);

                            const addNew = document.createElement('option');
                            addNew.value = 'add_new';
                            addNew.textContent = '➕ Add New';
                            departmentSelect.appendChild(addNew);

                            const modalElement = document.getElementById('addDepartmentModal');
                            const modalInstance = bootstrap.Modal.getInstance(modalElement);
                            if (modalInstance) {
                                modalInstance.hide();
                            }
                        } else {
                            if (data.errors) {
                                if (data.errors.branch_id) {
                                    document.getElementById('branchError').textContent = data.errors.branch_id[0];
                                }
                                if (data.errors.name) {
                                    document.getElementById('nameError').textContent = data.errors.name[0];
                                }
                            } else {
                                alert(data.error || 'An error occurred');
                            }
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Something went wrong. Please try again.');
                    });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            JsSearchBox();

            $('#use_custom_working_hours').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#workingHoursSection').slideDown();
                } else {
                    $('#workingHoursSection').slideUp();
                }
            });

            // Open working hours modal
            $('#openWorkingHoursModalBtn').on('click', function() {
                let whModal = new bootstrap.Modal(document.getElementById('workingHoursModal'));
                whModal.show();
            });

            // Sync modal values to hidden inputs when modal closes
            $('#workingHoursModal').on('hidden.bs.modal', function() {
                const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                days.forEach(day => {
                    const row = $(`#workingHoursTable tr[data-day="${day}"]`);
                    const isWorking = row.find('.working-day-toggle').is(':checked');

                    $(`.wh-hidden-is-working[data-day="${day}"]`).val(isWorking ? '1' : '');
                    $(`.wh-hidden-start[data-day="${day}"]`).val(row.find('.start-time').val());
                    $(`.wh-hidden-end[data-day="${day}"]`).val(row.find('.end-time').val());
                    $(`.wh-hidden-lunch-start[data-day="${day}"]`).val(row.find('.lunch-start').val());
                    $(`.wh-hidden-lunch-end[data-day="${day}"]`).val(row.find('.lunch-end').val());
                });
            });

            // Calculate hours accounting for lunch
            function calculateDayHours(day) {
                const row = $(`#workingHoursTable tr[data-day="${day}"]`);
                const isWorking = row.find('.working-day-toggle').is(':checked');
                const startTime = row.find('.start-time').val();
                const endTime = row.find('.end-time').val();
                const lunchStart = row.find('.lunch-start').val();
                const lunchEnd = row.find('.lunch-end').val();

                if (!isWorking || !startTime || !endTime) {
                    row.find('.hours-display').text('-');
                    return 0;
                }

                const start = new Date(`2000-01-01T${startTime}`);
                const end = new Date(`2000-01-01T${endTime}`);
                let diffMs = end - start;

                // Subtract lunch time if both start and end are set
                if (lunchStart && lunchEnd) {
                    const lunchStartDate = new Date(`2000-01-01T${lunchStart}`);
                    const lunchEndDate = new Date(`2000-01-01T${lunchEnd}`);
                    const lunchMs = lunchEndDate - lunchStartDate;
                    if (lunchMs > 0) {
                        diffMs -= lunchMs;
                    }
                }

                const diffHours = diffMs / (1000 * 60 * 60);
                row.find('.hours-display').text(diffHours > 0 ? diffHours.toFixed(1) + 'h' : '-');
                return diffHours > 0 ? diffHours : 0;
            }

            function calculateTotalHours() {
                let total = 0;
                const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                days.forEach(day => {
                    total += calculateDayHours(day);
                });
                $('#totalWeeklyHours').text(total.toFixed(1) + 'h');
            }

            $('.working-day-toggle').on('change', function() {
                const day = $(this).data('day');
                const isWorking = $(this).is(':checked');
                const row = $(`#workingHoursTable tr[data-day="${day}"]`);

                row.find('.start-time, .end-time, .lunch-start, .lunch-end').prop('disabled', !isWorking);
                if (!isWorking) {
                    row.find('.hours-display').text('-');
                } else {
                    calculateDayHours(day);
                }
                calculateTotalHours();
            });

            $('.start-time, .end-time, .lunch-start, .lunch-end').on('change', function() {
                calculateTotalHours();
            });

            calculateTotalHours();
        });
    </script>
@endsection
