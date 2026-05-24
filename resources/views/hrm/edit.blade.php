@extends('layouts.main')
@section('page-title')
    {{ __('Edit Employee') }}
@endsection
@section('page-breadcrumb')
    {{ __('Employee') }},
    {{ __('Edit Employee') }}
@endsection
@php
    $company_settings = getCompanyAllSetting();
@endphp

@section('content')
    <div class="container mt-5">
        <div class=" border-0">
            <div class="card-body">
                <!-- <h3 class="card-title">
                    Employee Details
                </h3> -->
                <form action="{{ route('employees.update', $employee->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <!-- Employee Details Section -->
                    <div class=" border bg-white rounded mt-8">
                        <div class="tagitem1">
                            <label class="returntext font">
                                Profile
                            </label>
                        </div>
                        <div class="p-8 ">
                            <div class="row mt-8">
                                <div class="col-md-2">
                                    <label for="profile_pic_path" class="form-label fw-bold">Profile Image</label>
                                </div>
                                <div class="col-md-2">



                                    <div class=" justify-content-center border-dashed p-4 rounded "
                                        style="display:flex; flex-direction:column; width:fit-content;justify-self:center">

                                        <input type="file" id="profile_pic_path" name="profile_pic_path"
                                            class="form-control" accept="image/*" style="display: none;"
                                            onchange="previewImage(event)">
                                        @if ($employee->profile_pic_path)
                                            <img id="imagePreview"
                                                src="{{ asset('uploads/' . $employee->profile_pic_path) }}" alt="Preview"
                                                class="img-fluid" style="max-height: 100%;" />
                                        @endif
                                    </div>

                                    <button type="button" class="btn btn-link mt-2"
                                        onclick="document.getElementById('profile_pic_path').click();">Upload
                                        Image</button>
                                    @error('profile_pic_path')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>


                            <div class="row mt-16">
                                <div class="col-md-2">
                                    <label for="employee_id" class="form-label fw-bold">Employee Id<span
                                            class="text-danger">*</span>
                                    </label>
                                </div>
                                <div class="col-md-4">

                                    <input type="text" id="employee_id" name="employee_id" class="form-control"
                                        value="{{ old('employee_id', $employee->employee_id) }}" readonly>
                                    @error('employee_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <!-- General Information -->
                            <div class="row mt-8 mb-4">
                                <div class="col-md-2">
                                    <label for="salutation" class="form-label fw-bold">Salutation <span
                                            class="text-danger">*</span>
                                    </label>
                                </div>
                                <div class="col-md-4">

                                    <select id="salutation" name="salutation" class="form-select">
                                        <option value="">Select</option>
                                        <option value="Mr"
                                            {{ old('salutation', $employee->salutation) == 'Mr' ? 'selected' : '' }}>
                                            Mr</option>
                                        <option value="Mrs"
                                            {{ old('salutation', $employee->salutation) == 'Mrs' ? 'selected' : '' }}>
                                            Mrs
                                        </option>
                                        <option value="Dr"
                                            {{ old('salutation', $employee->salutation) == 'Dr' ? 'selected' : '' }}>
                                            Dr</option>
                                        <option value="Miss"
                                            {{ old('salutation', $employee->salutation) == 'Miss' ? 'selected' : '' }}>
                                            Miss
                                        </option>
                                    </select>
                                    @error('salutation')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="alert mt-8 font-bold font"
                        style="background-color: #f2f3f5cf; color: #333;font-weight:500;border:1px solid #d9dce0cf ">
                        You have the same organization in all applications. Altering any information on this page
                        will update it in all apps.
                        <a href="#" class="text-decoration-none float-end">Trusted by Employees</a>
                    </div>


                    <!-- first and lastname -->
                    <div class=" border bg-white rounded mt-8">
                        <div class="tagitem1">
                            <label class="returntext font">
                                Types
                            </label>
                        </div>
                        <div class="p-8 ">
                            <div class="row mt-8">
                                <div class="col-md-2">
                                    <label for="first_name" class="form-label fw-bold">First Name<span
                                            class="text-danger">*</span>
                                    </label>
                                </div>
                                <div class="col-md-4">

                                    <input type="text" id="first_name" name="first_name" class="form-control"
                                        value="{{ old('first_name', $employee->first_name) }}">
                                    @error('first_name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>


                                <div class="col-md-2">
                                    <label for="last_name" class="form-label fw-bold">Last Name<span
                                            class="text-danger">*</span>
                                    </label>
                                </div>
                                <div class="col-md-4">

                                    <input type="text" id="last_name" name="last_name" class="form-control"
                                        value="{{ old('last_name', $employee->last_name) }}">
                                    @error('last_name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <!-- department and designation -->
                            <div class="row mt-8">
                                <div class="col-md-2">
                                    <label for="department" class="form-label fw-bold">Department</label>
                                </div>
                                <div class="col-md-4">

                                    <select id="department" class="form-select">
                                        <option value="">Select Department</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}"
                                                {{ $employee->designation->department->id == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('department')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>


                                <div class="col-md-2">
                                    <label for="designation" class="form-label fw-bold">Designation</label>
                                </div>
                                <div class="col-md-4">

                                    <select id="designation" name="designation_id" class="form-select">
                                        <option value="">Select Designation</option>
                                        @foreach ($designations as $designation)
                                            <option value="{{ $designation->id }}"
                                                {{ old('designation_id', $employee->designation_id ?? '') == $designation->id ? 'selected' : '' }}>
                                                {{ $designation->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('designation_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <!-- phonenumber and email -->
                            <div class="row mt-8">
                                <div class="col-md-2">
                                    <label for="phone_number" class="form-label fw-bold">Phone Number<span
                                            class="text-danger">*</span>
                                    </label>
                                </div>
                                <div class="col-md-4">

                                    <input type="tel" id="phone_number" name="phone_number" class="form-control"
                                        value="{{ old('phone_number', $employee->phone_number) }}">
                                    @error('phone_number')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>


                                <div class="col-md-2">
                                    <label for="email" class="form-label fw-bold">Email<span
                                            class="text-danger">*</span>
                                    </label>
                                </div>
                                <div class="col-md-4">

                                    <input type="email" id="email" name="email" class="form-control"
                                        value="{{ old('email', $employee->email) }}">
                                    @error('email')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <!-- date-of-birth and gender -->
                            <div class="row mt-8 mb-4">
                                <div class="col-md-2">
                                    <label for="date_of_birth" class="form-label fw-bold">Date of Birth</label>
                                </div>
                                <div class="col-md-4">

                                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-control"
                                        value="{{ old('date_of_birth', $employee->date_of_birth) }}"
                                        max="{{ $today }}">
                                    @error('date_of_birth')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>


                                <div class="col-md-2">
                                    <label for="gender" class="form-label fw-bold">Gender</label>
                                </div>
                                <div class="col-md-4">

                                    <select id="gender" name="gender" class="form-select">
                                        <option value="">Select</option>
                                        <option value="Male"
                                            {{ old('gender', $employee->gender) == 'Male' ? 'selected' : '' }}>
                                            Male</option>
                                        <option value="Female"
                                            {{ old('gender', $employee->gender) == 'Female' ? 'selected' : '' }}>Female
                                        </option>
                                        <option value="Other"
                                            {{ old('gender', $employee->gender) == 'Other' ? 'selected' : '' }}>Other
                                        </option>
                                    </select>
                                    @error('gender')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <!-- PayFrequency -->

                            <div class="row mt-8 mb-4">
                                <div class="col-md-2">
                                    <label for="pay_frequency" class="form-label fw-bold">Pay Frequency</label>
                                </div>
                                <div class="col-md-4">
                                    <select id="pay_frequency" name="pay_frequency" class="form-control">
                                        <option value="">Select Pay Frequency</option>
                                        @foreach (\App\Models\Hrm\PayFrequency::all() as $frequency)
                                            <option value="{{ $frequency->id }}"
                                                {{ old('pay_frequency', $employee->pay_frequency) == $frequency->id ? 'selected' : '' }}>
                                                {{ $frequency->pay_frequency }} ({{ $frequency->biweekly_date }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('pay_frequency')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-2">
                                    <label for="date_of_appointment" class="form-label fw-bold">Date of
                                        Appointment</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="date" id="date_of_appointment" name="date_of_appointment"
                                        class="form-control" max="{{ now()->toDateString() }}"
                                        value="{{ $employee->date_of_appointment }}">
                                    @error('date_of_appointment')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mt-8 mb-4">
                                <div class="col-md-2">
                                    <label for="identification_type" class="form-label fw-bold">Identification
                                        Type</label>
                                </div>
                                <div class="col-md-4">
                                    <select id="identification_type" name="identification_type" class="form-control">
                                        <option value="">Select Identification Type</option>
                                        <option value="RSA ID"
                                            {{ old('identification_type', $employee->identification_type) == 'RSA ID' ? 'selected' : '' }}>
                                            RSA ID</option>
                                        <option value="Passport/foregin id"
                                            {{ old('identification_type', $employee->identification_type) == 'Passport/foregin id' ? 'selected' : '' }}>
                                            Passport</option>
                                        <option value="Refugee id"
                                            {{ old('identification_type', $employee->identification_type) == 'Refugee id' ? 'selected' : '' }}>
                                            Refugee Id</option>
                                    </select>
                                    @error('identification_type')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-2">
                                    <label for="id_number" class="form-label fw-bold">ID Number</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" id="id_number" name="id_number" class="form-control"
                                        value="{{ $employee->id_number }}" placeholder="Enter ID Number">
                                    @error('id_number')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--Permanent  house and pincode -->
                    <h3 class="mt-8">Permanent Address</h3>
                    <div class=" border bg-white rounded mt-8">
                        <div class="tagitem1">
                            <label class="returntext font">
                                Permanent Address
                            </label>
                        </div>
                        <div class="p-8 ">
                            <div class="row mt-8">
                                <div class="col-md-2">
                                    <label for="flat_no" class="form-label fw-bold">House/Building/Flat No</label>
                                </div>
                                <div class="col-md-4">

                                    <input type="text" id="flat_no" name="flat_no"
                                        value="{{ old('flat_no', $employee->flat_no) }}" class="form-control">
                                    @error('flat_no')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-2">
                                    <label for="pincode" class="form-label fw-bold">Pincode</label>
                                </div>
                                <div class="col-md-4">

                                    <input type="text" id="pincode" name="pincode"
                                        value="{{ old('pincode', $employee->pincode) }}" class="form-control">
                                    @error('pincode')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>


                            <!-- area and province -->
                            <div class="row mt-8">
                                <div class="col-md-2">
                                    <label for="street" class="form-label fw-bold">Area/Street/Village</label>
                                </div>
                                <div class="col-md-4">

                                    <input type="text" id="street" name="street"
                                        value="{{ old('street', $employee->street) }}" class="form-control">
                                    @error('street')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="country" class="form-label fw-bold">Country</label>
                                </div>
                                <div class="col-md-4">

                                    <select id="country" name="country" class="form-select"
                                        onchange="getProvinces(this.value)">
                                        <option value="">Select Country</option>
                                        @foreach ($countries as $country)
                                            <option value="{{ $country->name }}"
                                                {{ old('country', $employee->country) == $country->name ? 'selected' : '' }}>
                                                {{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('country')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <!-- town and country -->
                            <div class="row mt-8 mb-4">
                                <div class="col-md-2">
                                    <label for="state" class="form-label fw-bold">Province</label>
                                </div>
                                <div class="col-md-4">
                                    <select id="state" name="state" class="form-select">
                                        <option value="">Select Province</option>
                                        @foreach ($provinces as $province)
                                            <option value="{{ $province->name }}"
                                                {{ old('state', $employee->state) == $province->name ? 'selected' : '' }}>
                                                {{ $province->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('state')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="city" class="form-label fw-bold">Town/City</label>
                                </div>
                                <div class="col-md-4">

                                    <input type="text" id="city" name="city"
                                        value="{{ old('city', $employee->city) }}" class="form-control">
                                    @error('city')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="alert mt-8 font-bold font"
                        style="background-color: #f2f3f5cf; color: #333;font-weight:500;border:1px solid #d9dce0cf ">
                        You have the same organization in all applications. Altering any information on this page
                        will update it in all apps.
                        <a href="#" class="text-decoration-none float-end">Trusted by Restaurants</a>
                    </div>

                    <!-- Emergency Contact -->
                    <h3 class="mt-8">Emergency Contact</h3>

                    <div class=" border bg-white rounded mt-8">
                        <div class="tagitem1">
                            <label class="returntext font">
                                Emergency Contact
                            </label>
                        </div>
                        <div class="p-8 ">
                            <div class="row mt-6">
                                <table style="padding: 10px;">
                                    <thead>
                                        <tr>
                                            <!-- <th>Contact Person</th>
                                    <th>Phone Number</th> -->
                                        </tr>
                                    </thead>
                                    <tbody id="table-body">
                                        @foreach ($emergencyContacts as $emergencyContact)
                                            <tr>
                                                <td>
                                                    <div class="row mt-4">
                                                        <div class="col-md-4">
                                                            <label for="emergency_contact_name[]"
                                                                class="form-label fw-bold">Contact
                                                                Person<span class="text-danger">*</span>
                                                            </label>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <input type="text" name="emergency_contact_name[]"
                                                                class="form-control {{ $loop->index > 0 ? 'mt-3' : '' }}"
                                                                value="{{ $emergencyContact['name'] }}"
                                                                placeholder="Contact Person">
                                                </td>
                            </div>
                            <td style="padding-left: 20px;">
                                <div class="row mt-4 pl-8">
                                    <div class="col-md-4">
                                        <label for="emergency_contact_phone[]" class="form-label fw-bold">Phone
                                            Number<span class="text-danger">*</span>
                                        </label>
                                    </div>
                                    <div class="col-md-8" style="padding-right: 10px;">
                                        <input type="tel" name="emergency_contact_phone[]"
                                            class="form-control {{ $loop->index > 0 ? 'mt-3' : '' }} "
                                            value="{{ $emergencyContact['number'] }}" placeholder="Phone Number">
                            </td>
                            </tr>
                            @endforeach
                            </tbody>
                            </table>
                        </div>
                    </div>
            </div>
        </div>
        <div class="alert mt-5 font-bold font"
            style="background-color: #f2f3f5cf; color: #333;font-weight:500;border:1px solid #d9dce0cf ">
            You have the same organization in all applications. Altering any information on this page
            will update it in all apps.
            <a href="#" class="text-decoration-none float-end">Trusted by Employees</a>
        </div>
        <!-- Emergency Contact -->
        <h3 class="mt-8">Bank Account Details</h3>

        <div class="mb-3">
            <label for="bank" class="form-label">Bank</label>
            <select name="bank" id="bank" class="form-control">
                <option value="">Select Bank</option>
                <option value="ABSA Bank" {{ old('bank', $employee->bank) == 'ABSA Bank' ? 'selected' : '' }}>ABSA Bank
                </option>
                <option value="Capitec Bank" {{ old('bank', $employee->bank) == 'Capitec Bank' ? 'selected' : '' }}>
                    Capitec Bank</option>
                <option value="First National Bank"
                    {{ old('bank', $employee->bank) == 'First National Bank' ? 'selected' : '' }}>First National Bank
                </option>
                <option value="Nedbank" {{ old('bank', $employee->bank) == 'Nedbank' ? 'selected' : '' }}>Nedbank</option>
                <option value="Standard Bank" {{ old('bank', $employee->bank) == 'Standard Bank' ? 'selected' : '' }}>
                    Standard Bank</option>
                <option value="Access Bank South Africa Limited"
                    {{ old('bank', $employee->bank) == 'Access Bank South Africa Limited' ? 'selected' : '' }}>Access Bank
                    South Africa Limited</option>
            </select>
            @error('bank')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <!-- Account Number -->
        <div class="mb-3">
            <label for="account_number" class="form-label">Account Number</label>
            <input type="text" name="account_number" id="account_number" class="form-control"
                value="{{ $employee->account_number }}">
            @error('account_number')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <!-- Branch Code -->
        <div class="mb-3">
            <label for="branch_code" class="form-label">Branch Code</label>
            <input type="text" name="branch_code" id="branch_code" class="form-control"
                value="{{ $employee->branch_code }}">
            @error('branch_code')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <!-- Account Type Dropdown -->
        <div class="mb-3">
            <label for="account_type" class="form-label">Account Type</label>
            <select name="account_type" id="account_type" class="form-control">
                <option value="">Select Account Type</option>
                <option value="savings" {{ old('account_type', $employee->account_type) == 'savings' ? 'selected' : '' }}>
                    Savings</option>
                <option value="current" {{ old('account_type', $employee->account_type) == 'current' ? 'selected' : '' }}>
                    Current</option>
                <option value="Bond" {{ old('account_type', $employee->account_type) == 'Bond' ? 'selected' : '' }}>Bond
                </option>
                <option value="Transmission"
                    {{ old('account_type', $employee->account_type) == 'Transmission' ? 'selected' : '' }}>Transmission
                </option>
            </select>
            @error('account_type')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <!-- Holder Relationship Dropdown -->
        <div class="mb-3">
            <label for="holder_relationship" class="form-label">Holder Relationship</label>
            <select name="holder_relationship" id="holder_relationship" class="form-control">
                <option value="">Select Relationship</option>
                <option value="Own"
                    {{ old('holder_relationship', $employee->holder_relationship) == 'Own' ? 'selected' : '' }}>Own</option>
                <option value="Joined"
                    {{ old('holder_relationship', $employee->holder_relationship) == 'Joined' ? 'selected' : '' }}>Joined
                </option>
                <option value="Third Party"
                    {{ old('holder_relationship', $employee->holder_relationship) == 'Third Party' ? 'selected' : '' }}>
                    Third Party</option>
            </select>
            @error('holder_relationship')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>


        <div class="d-flex justify-content-start mt-8">
            <button type="submit" class="btn btn-rc-primary">Update Employee</button>
            <a class="btn btn-rc-outline ms-3" href="{{ route('employees.list') }}">Cancel</a>
        </div>
        </form>
    </div>
    </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const checkbox = document.getElementById("permanent_address");

            checkbox.addEventListener("change", function() {
                if (this.checked) {
                    document.getElementById("flat_no").value = document.getElementById("temp_flat_no")
                    .value;
                    document.getElementById("street").value = document.getElementById("temp_street").value;
                    document.getElementById("city").value = document.getElementById("temp_city").value;
                    document.getElementById("state").value = document.getElementById("temp_state").value;
                    document.getElementById("country").value = document.getElementById("temp_country")
                    .value;
                    document.getElementById("pincode").value = document.getElementById("temp_pincode")
                    .value;
                } else {
                    document.getElementById("flat_no").value = "";
                    document.getElementById("street").value = "";
                    document.getElementById("city").value = "";
                    document.getElementById("state").value = "";
                    document.getElementById("country").value = "";
                    document.getElementById("pincode").value = "";
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const departmentId = {
                {
                    $employee - > designation - > department - > id
                }
            };
            const designationSelect = document.getElementById('designation');
            const employeeDesignationId = {
                {
                    json_encode($employee - > designation_id)
                }
            };

            let checkedState = false
            checkedState = document.getElementById("flat_no").value == document.getElementById("temp_flat_no")
                .value;
            checkedState = document.getElementById("street").value == document.getElementById("temp_street").value;
            checkedState = document.getElementById("city").value == document.getElementById("temp_city").value;
            checkedState = document.getElementById("pincode").value == document.getElementById("temp_pincode")
                .value;
            checkedState = document.getElementById("state").value == document.getElementById("temp_state").value;
            checkedState = document.getElementById("country").value == document.getElementById("temp_country")
                .value;
            if (checkedState) {
                document.getElementById('permanent_address').checked = true;
            }
            // Clear existing options
            designationSelect.innerHTML = '<option value="">Select Designation</option>';
            if (departmentId) {
                // Fetch designations for the selected department
                fetch(`/departments/${departmentId}/designations`)
                    .then(response => response.json())
                    .then(data => {
                        if (Array.isArray(data)) {
                            data.forEach(designation => {
                                const option = document.createElement('option');
                                option.value = designation.id;
                                option.textContent = designation.name;
                                if (designation.id === employeeDesignationId) {
                                    option.selected = true;
                                }
                                designationSelect.appendChild(option);
                            });
                        } else {
                            console.error('Unexpected response:', data);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching designations:', error);
                    });
            }
        });
    </script>
    <script>
        document.getElementById('department').addEventListener('change', function() {
            const departmentId = this.value;
            const designationSelect = document.getElementById('designation');

            // Clear existing options
            designationSelect.innerHTML = '<option value="">Select Designation</option>';
            if (departmentId) {
                // Fetch designations for the selected department
                fetch(`/departments/${departmentId}/designations`)
                    .then(response => response.json())
                    .then(data => {
                        if (Array.isArray(data)) {
                            data.forEach(designation => {
                                const option = document.createElement('option');
                                option.value = designation.id;
                                option.textContent = designation.name;
                                designationSelect.appendChild(option);
                            });
                        } else {
                            console.error('Unexpected response:', data);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching designations:', error);
                    });
            }
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
        // Enable/Disable "Permanent" button based on checkbox
        document.getElementById('permanent_address').addEventListener('change', function() {
            const checkedState = this.checked;
            document.getElementById("flat_no").value = checkedState ? document.getElementById("temp_flat_no")
                .value : '';
            document.getElementById("street").value = checkedState ? document.getElementById("temp_street").value :
                '';
            document.getElementById("city").value = checkedState ? document.getElementById("temp_city").value : '';
            document.getElementById("state").value = checkedState ? document.getElementById("temp_state").value :
                '';
            document.getElementById("country").value = checkedState ? document.getElementById("temp_country")
                .value : '';
        });
    </script>
    <script>
        function addRow() {
            const tableBody = document.getElementById('table-body');
            const rowCount = tableBody.rows.length + 1;
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
            <td>
                <input type="text" name="emergency_contact_name[]" class="form-control mt-3" placeholder="Contact Person">
            </td>
            <td>
                <input type="tel" name="emergency_contact_phone[]" class="form-control mt-3" placeholder="Phone Number">
            </td>
        `;
            tableBody.appendChild(newRow);
        }
    </script>
@endsection
@push('scripts')
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/dropzone-amd-module.min.js') }}"></script>
    <script>
        function changetab(tabname) {
            var someTabTriggerEl = document.querySelector('button[data-bs-target="' + tabname + '"]');
            var actTab = new bootstrap.Tab(someTabTriggerEl);
            actTab.show();
        }
    </script>

    <script type="text/javascript">
        $(document).on('change', '#branch_id', function() {
            var branch_id = $(this).val();
            getDepartment(branch_id);
        });

        function getDepartment(branch_id) {
            var data = {
                "branch_id": branch_id,
                "_token": "{{ csrf_token() }}",
            }

            $.ajax({
                url: '{{ route('employee.getdepartments') }}',
                method: 'POST',
                data: data,
                success: function(data) {
                    $('#department_id').empty();
                    $('#department_id').append(
                        '<option value="" disabled>{{ __('Select Department') }}</option>');

                    $.each(data, function(key, value) {
                        $('#department_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                    $('#department_id').val('');
                }
            });
        }

        $(document).on('change', 'select[name=department_id]', function() {
            var department_id = $(this).val();
            getDesignation(department_id);
        });

        function getDesignation(did) {
            $.ajax({
                url: '{{ route('employee.getdesignations') }}',
                type: 'POST',
                data: {
                    "department_id": did,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('#designation_id').empty();
                    $('#designation_id').append(
                        '<option value="">{{ __('Select Designation') }}</option>');
                    $.each(data, function(key, value) {
                        $('#designation_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                }
            });
        }
    </script>
    <script>
        $("#submit").click(function() {
            $(".doc_data").each(function() {
                if (!isNaN(this.value)) {
                    var id = '#doc_validation-' + $(this).data("key");
                    $(id).removeClass('d-none')
                    return false;
                }
            });
        });
    </script>
    <script>
        function getProvinces(country) {
            if (country === "") {
                document.getElementById("state").innerHTML = '<option value="">Select Province</option>';
                return;
            }

            fetch(`/get-provinces/${country}`)
                .then(response => response.json())
                .then(data => {
                    let options = '<option value="">Select Province</option>';
                    data.forEach(province => {
                        options += `<option value="${province.name}">${province.name}</option>`;
                    });
                    document.getElementById("state").innerHTML = options;
                })
                .catch(error => console.error("Error fetching provinces:", error));
        }
    </script>
@endpush
