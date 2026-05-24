@extends('hrm.ess.layouts.app')

@section('page-title', 'Edit Profile')
@section('page-subtitle', 'Update your personal information')

@push('styles')
<style>
    .edit-section {
        background: white;
        border-radius: 16px;
        margin-bottom: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
        border: 1px solid var(--ess-border);
        overflow: hidden;
    }
    
    .edit-section-header {
        padding: 20px 24px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid var(--ess-border);
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .edit-section-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .edit-section-icon svg {
        width: 22px;
        height: 22px;
        color: white;
    }
    
    .edit-section-icon.personal { background: linear-gradient(135deg, #655997 0%, #8b5cf6 100%); }
    .edit-section-icon.address { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
    .edit-section-icon.emergency { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
    
    .edit-section-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--ess-text);
        margin: 0;
    }
    
    .edit-section-body {
        padding: 24px;
    }
    
    .form-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--ess-text);
        margin-bottom: 6px;
    }
    
    .form-label .required {
        color: var(--ess-danger);
    }
    
    .form-control {
        border: 1px solid var(--ess-border);
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    
    .form-control:focus {
        border-color: var(--ess-primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    
    .form-control:disabled, .form-control[readonly] {
        background: #f8fafc;
        color: var(--ess-text-muted);
        cursor: not-allowed;
    }
    
    .form-select {
        border: 1px solid var(--ess-border);
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    
    .form-select:focus {
        border-color: var(--ess-primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    
    .readonly-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        padding: 2px 8px;
        background: #fef3c7;
        color: #92400e;
        border-radius: 4px;
        margin-left: 8px;
    }
    
    .emergency-contact-item {
        background: #f8fafc;
        border: 1px solid var(--ess-border);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 16px;
        position: relative;
    }
    
    .emergency-contact-item:last-child {
        margin-bottom: 0;
    }
    
    .emergency-contact-number {
        position: absolute;
        top: -10px;
        left: 16px;
        background: var(--ess-primary);
        color: white;
        font-size: 12px;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 12px;
    }
    
    .remove-contact-btn {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #fee2e2;
        border: none;
        color: #dc2626;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    
    .remove-contact-btn:hover {
        background: #fecaca;
    }
    
    .remove-contact-btn svg {
        width: 16px;
        height: 16px;
    }
    
    .add-contact-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        color: var(--ess-primary);
        border: 1px dashed var(--ess-primary);
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-top: 16px;
    }
    
    .add-contact-btn:hover {
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
    }
    
    .add-contact-btn svg {
        width: 18px;
        height: 18px;
    }
    
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 32px;
    }
    
    .btn-cancel {
        padding: 12px 24px;
        background: white;
        color: var(--ess-text);
        border: 1px solid var(--ess-border);
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    
    .btn-cancel:hover {
        background: #f8fafc;
        color: var(--ess-text);
    }
    
    .btn-save {
        padding: 12px 24px;
        background: linear-gradient(135deg, var(--ess-primary) 0%, var(--ess-primary-dark) 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
    }
    
    .btn-save svg {
        width: 18px;
        height: 18px;
    }
    
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--ess-text-muted);
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 24px;
        transition: color 0.2s ease;
    }
    
    .back-link:hover {
        color: var(--ess-primary);
    }
    
    .back-link svg {
        width: 18px;
        height: 18px;
    }
    
    .alert {
        border-radius: 12px;
        border: none;
        padding: 16px 20px;
        margin-bottom: 24px;
    }
    
    .alert-danger {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
    }
</style>
@endpush

@section('content')
<!-- Back Link -->
<a href="{{ route('ess.profile') }}" class="back-link">
    <i data-feather="arrow-left"></i>
    Back to Profile
</a>

@if($errors->any())
    <div class="alert alert-danger">
        <strong><i data-feather="alert-circle" style="width: 16px; height: 16px; margin-right: 8px;"></i>Please correct the following errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('ess.profile.update') }}" method="POST" id="profileForm">
    @csrf
    @method('PUT')

    <!-- Personal Information -->
    <div class="edit-section">
        <div class="edit-section-header">
            <div class="edit-section-icon personal">
                <i data-feather="user"></i>
            </div>
            <h3 class="edit-section-title">Personal Information</h3>
        </div>
        <div class="edit-section-body">
            <div class="row g-3">
                <div class="col-md-2">
                    <label for="salutation" class="form-label">Salutation</label>
                    <select class="form-select" id="salutation" name="salutation">
                        <option value="">Select</option>
                        <option value="Mr" {{ old('salutation', $employee->salutation) == 'Mr' ? 'selected' : '' }}>Mr</option>
                        <option value="Mrs" {{ old('salutation', $employee->salutation) == 'Mrs' ? 'selected' : '' }}>Mrs</option>
                        <option value="Ms" {{ old('salutation', $employee->salutation) == 'Ms' ? 'selected' : '' }}>Ms</option>
                        <option value="Dr" {{ old('salutation', $employee->salutation) == 'Dr' ? 'selected' : '' }}>Dr</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="first_name" class="form-label">First Name <span class="required">*</span></label>
                    <input type="text" class="form-control" id="first_name" name="first_name" 
                           value="{{ old('first_name', $employee->first_name) }}" required>
                </div>
                <div class="col-md-5">
                    <label for="last_name" class="form-label">Last Name <span class="required">*</span></label>
                    <input type="text" class="form-control" id="last_name" name="last_name" 
                           value="{{ old('last_name', $employee->last_name) }}" required>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">
                        Email Address
                        <span class="readonly-badge"><i data-feather="lock" style="width: 10px; height: 10px;"></i> HR Only</span>
                    </label>
                    <input type="email" class="form-control" id="email" value="{{ $employee->email }}" disabled>
                </div>
                <div class="col-md-6">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" 
                           value="{{ old('phone_number', $employee->phone_number) }}" 
                           placeholder="Enter phone number">
                </div>
                <div class="col-md-4">
                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                           value="{{ old('date_of_birth', $employee->date_of_birth ? $employee->date_of_birth->format('Y-m-d') : '') }}">
                </div>
                <div class="col-md-4">
                    <label for="gender" class="form-label">Gender</label>
                    <select class="form-select" id="gender" name="gender">
                        <option value="">Select</option>
                        <option value="male" {{ old('gender', $employee->gender) == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $employee->gender) == 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender', $employee->gender) == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="id_number" class="form-label">
                        ID Number
                        <span class="readonly-badge"><i data-feather="lock" style="width: 10px; height: 10px;"></i> HR Only</span>
                    </label>
                    <input type="text" class="form-control" id="id_number" value="{{ $employee->id_number }}" disabled>
                </div>
            </div>
        </div>
    </div>

    <!-- Address Information -->
    <div class="edit-section">
        <div class="edit-section-header">
            <div class="edit-section-icon address">
                <i data-feather="map-pin"></i>
            </div>
            <h3 class="edit-section-title">Address</h3>
        </div>
        <div class="edit-section-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="flat_no" class="form-label">Flat/Unit No.</label>
                    <input type="text" class="form-control" id="flat_no" name="flat_no" 
                           value="{{ old('flat_no', $employee->flat_no) }}" 
                           placeholder="Apartment, unit, etc.">
                </div>
                <div class="col-md-8">
                    <label for="street" class="form-label">Street Address</label>
                    <input type="text" class="form-control" id="street" name="street" 
                           value="{{ old('street', $employee->street) }}" 
                           placeholder="Street address">
                </div>
                <div class="col-md-4">
                    <label for="city" class="form-label">City</label>
                    <input type="text" class="form-control" id="city" name="city" 
                           value="{{ old('city', $employee->city) }}" 
                           placeholder="City">
                </div>
                <div class="col-md-4">
                    <label for="country" class="form-label">Country</label>
                    <select class="form-select" id="country" name="country" onchange="getProvinces(this.value)">
                        <option value="">Select Country</option>
                        @foreach($countries as $countryOption)
                            <option value="{{ $countryOption->name }}" {{ old('country', $employee->country) == $countryOption->name ? 'selected' : '' }}>
                                {{ $countryOption->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="state" class="form-label">State/Province</label>
                    <select class="form-select" id="state" name="state">
                        <option value="">Select Province</option>
                        @foreach($provinces as $province)
                            <option value="{{ $province->name }}" {{ old('state', $employee->state) == $province->name ? 'selected' : '' }}>
                                {{ $province->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="pincode" class="form-label">Postal Code</label>
                    <input type="text" class="form-control" id="pincode" name="pincode" 
                           value="{{ old('pincode', $employee->pincode) }}" 
                           placeholder="Postal code">
                </div>
            </div>
        </div>
    </div>

    <!-- Emergency Contacts -->
    <div class="edit-section">
        <div class="edit-section-header">
            <div class="edit-section-icon emergency">
                <i data-feather="alert-circle"></i>
            </div>
            <h3 class="edit-section-title">Emergency Contacts</h3>
        </div>
        <div class="edit-section-body">
            <p style="color: var(--ess-text-muted); font-size: 13px; margin-bottom: 16px;">
                Add one or more emergency contacts who can be reached in case of an emergency.
            </p>
            
            <div id="emergencyContactsContainer">
                @forelse($emergencyContacts as $index => $contact)
                    @if(!empty($contact['name']) || !empty($contact['phone']))
                    <div class="emergency-contact-item" data-index="{{ $index }}">
                        <span class="emergency-contact-number">Contact {{ $index + 1 }}</span>
                        @if($index > 0 || count($emergencyContacts) > 1)
                        <button type="button" class="remove-contact-btn" onclick="removeContact(this)">
                            <i data-feather="x"></i>
                        </button>
                        @endif
                        <div class="row g-3" style="margin-top: 8px;">
                            <div class="col-md-6">
                                <label class="form-label">Contact Name</label>
                                <input type="text" class="form-control" name="emergency_contact_name[]" 
                                       value="{{ old('emergency_contact_name.' . $index, $contact['name']) }}" 
                                       placeholder="Full name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Phone</label>
                                <input type="text" class="form-control" name="emergency_contact_phone[]" 
                                       value="{{ old('emergency_contact_phone.' . $index, $contact['phone']) }}" 
                                       placeholder="Phone number">
                            </div>
                        </div>
                    </div>
                    @endif
                @empty
                    <div class="emergency-contact-item" data-index="0">
                        <span class="emergency-contact-number">Contact 1</span>
                        <div class="row g-3" style="margin-top: 8px;">
                            <div class="col-md-6">
                                <label class="form-label">Contact Name</label>
                                <input type="text" class="form-control" name="emergency_contact_name[]" 
                                       placeholder="Full name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Phone</label>
                                <input type="text" class="form-control" name="emergency_contact_phone[]" 
                                       placeholder="Phone number">
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
            
            <button type="button" class="add-contact-btn" onclick="addContact()">
                <i data-feather="plus"></i>
                Add Another Contact
            </button>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="form-actions">
        <a href="{{ route('ess.profile') }}" class="btn-cancel">Cancel</a>
        <button type="submit" class="btn-save">
            <i data-feather="check"></i>
            Save Changes
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    let contactIndex = {{ count($emergencyContacts) > 0 ? count($emergencyContacts) : 1 }};
    
    function addContact() {
        contactIndex++;
        const container = document.getElementById('emergencyContactsContainer');
        const newContact = document.createElement('div');
        newContact.className = 'emergency-contact-item';
        newContact.dataset.index = contactIndex - 1;
        newContact.innerHTML = `
            <span class="emergency-contact-number">Contact ${contactIndex}</span>
            <button type="button" class="remove-contact-btn" onclick="removeContact(this)">
                <i data-feather="x"></i>
            </button>
            <div class="row g-3" style="margin-top: 8px;">
                <div class="col-md-6">
                    <label class="form-label">Contact Name</label>
                    <input type="text" class="form-control" name="emergency_contact_name[]" 
                           placeholder="Full name">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Phone</label>
                    <input type="text" class="form-control" name="emergency_contact_phone[]" 
                           placeholder="Phone number">
                </div>
            </div>
        `;
        container.appendChild(newContact);
        feather.replace();
        updateContactNumbers();
    }
    
    function removeContact(button) {
        const item = button.closest('.emergency-contact-item');
        item.remove();
        updateContactNumbers();
    }
    
    function updateContactNumbers() {
        const items = document.querySelectorAll('.emergency-contact-item');
        items.forEach((item, index) => {
            const numberBadge = item.querySelector('.emergency-contact-number');
            numberBadge.textContent = `Contact ${index + 1}`;
            
            // Show/hide remove button based on whether there's more than one contact
            let removeBtn = item.querySelector('.remove-contact-btn');
            if (items.length === 1) {
                if (removeBtn) removeBtn.style.display = 'none';
            } else {
                if (!removeBtn) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'remove-contact-btn';
                    btn.onclick = function() { removeContact(this); };
                    btn.innerHTML = '<i data-feather="x"></i>';
                    item.appendChild(btn);
                    feather.replace();
                } else {
                    removeBtn.style.display = 'flex';
                }
            }
        });
        contactIndex = items.length;
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateContactNumbers();
    });
    
    // Fetch provinces based on selected country
    function getProvinces(country) {
        const stateSelect = document.getElementById('state');
        
        // Clear existing options
        stateSelect.innerHTML = '<option value="">Loading...</option>';
        
        if (!country) {
            stateSelect.innerHTML = '<option value="">Select State/Province</option>';
            return;
        }
        
        fetch(`{{ url('ess/profile/provinces') }}/${encodeURIComponent(country)}`)
            .then(response => response.json())
            .then(data => {
                stateSelect.innerHTML = '<option value="">Select State/Province</option>';
                data.forEach(province => {
                    const option = document.createElement('option');
                    option.value = province.name;
                    option.textContent = province.name;
                    stateSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error fetching provinces:', error);
                stateSelect.innerHTML = '<option value="">Select State/Province</option>';
            });
    }
</script>
@endpush
