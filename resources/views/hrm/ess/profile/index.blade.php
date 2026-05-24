@extends('hrm.ess.layouts.app')

@section('page-title', 'My Profile')
@section('page-subtitle', 'View your personal information')

@push('styles')
<style>
    .profile-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 24px;
        padding: 40px;
        color: white;
        position: relative;
        overflow: hidden;
        margin-bottom: 30px;
    }
    
    .profile-hero::before {
        content: '';
        position: absolute;
        top: -100px;
        right: -100px;
        width: 400px;
        height: 400px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
    }
    
    .profile-hero::after {
        content: '';
        position: absolute;
        bottom: -150px;
        left: -100px;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
    }
    
    .profile-avatar-wrapper {
        position: relative;
        display: inline-block;
    }
    
    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
        justify-content: center;
        border: 4px solid rgba(255, 255, 255, 0.3);
        font-size: 42px;
        font-weight: 700;
    }
    
    .profile-avatar img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }
    
    .profile-status-badge {
        position: absolute;
        bottom: 5px;
        right: 5px;
        width: 28px;
        height: 28px;
        background: #22c55e;
        border-radius: 50%;
        border: 3px solid white;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .profile-status-badge svg {
        width: 14px;
        height: 14px;
        color: white;
    }
    
    .profile-name {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 4px;
    }
    
    .profile-role {
        font-size: 16px;
        opacity: 0.9;
        margin-bottom: 20px;
    }
    
    .profile-meta-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        padding: 10px 18px;
        border-radius: 30px;
        font-size: 14px;
        margin-right: 10px;
        margin-bottom: 10px;
    }
    
    .profile-meta-item svg {
        width: 16px;
        height: 16px;
    }
    
    .profile-actions {
        position: absolute;
        top: 30px;
        right: 30px;
        z-index: 1;
    }
    
    .profile-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        color: white;
        padding: 12px 20px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.2s ease;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .profile-action-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
        transform: translateY(-2px);
    }
    
    .profile-action-btn svg {
        width: 18px;
        height: 18px;
    }
    
    .info-section {
        background: white;
        border-radius: 20px;
        margin-bottom: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
        border: 1px solid var(--ess-border);
        overflow: hidden;
    }
    
    .info-section-header {
        padding: 20px 24px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid var(--ess-border);
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .info-section-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .info-section-icon svg {
        width: 22px;
        height: 22px;
        color: white;
    }
    
    .info-section-icon.personal { background: linear-gradient(135deg, #655997 0%, #8b5cf6 100%); }
    .info-section-icon.employment { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .info-section-icon.address { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
    .info-section-icon.emergency { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
    
    .info-section-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--ess-text);
        margin: 0;
    }
    
    .info-section-body {
        padding: 24px;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
    }
    
    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .info-item {
        position: relative;
    }
    
    .info-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--ess-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
    
    .info-value {
        font-size: 15px;
        font-weight: 600;
        color: var(--ess-text);
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }
    
    .status-badge.active {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
    }
    
    .status-badge.inactive {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
    }
    
    .status-badge svg {
        width: 14px;
        height: 14px;
    }
    
    @media (max-width: 768px) {
        .profile-hero {
            padding: 30px 20px;
            text-align: center;
        }
        
        .profile-actions {
            position: static;
            margin-top: 20px;
        }
        
        .profile-name {
            font-size: 22px;
        }
    }
</style>
@endpush

@section('content')
<!-- Profile Hero -->
<div class="profile-hero">
    <div class="profile-actions">
        <a href="{{ route('ess.profile.edit') }}" class="profile-action-btn" style="margin-right: 10px;">
            <i data-feather="edit-2"></i>
            Edit Profile
        </a>
        <a href="{{ route('ess.profile.change-password') }}" class="profile-action-btn">
            <i data-feather="lock"></i>
            Change Password
        </a>
    </div>
    
    <div class="d-flex flex-column flex-md-row align-items-center gap-4">
        <div class="profile-avatar-wrapper">
            <div class="profile-avatar">
                @if($employee->profile_picture)
                    <img src="{{ asset('storage/' . $employee->profile_picture) }}" alt="Profile">
                @else
                    {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name ?? '', 0, 1)) }}
                @endif
            </div>
            @if($employee->status === 'active')
                <div class="profile-status-badge">
                    <i data-feather="check"></i>
                </div>
            @endif
        </div>
        
        <div class="text-center text-md-start">
            <div class="profile-name">{{ $employee->first_name }} {{ $employee->last_name }}</div>
            <div class="profile-role">{{ $employee->designation->name ?? 'Employee' }}</div>
            
            <div class="profile-meta">
                <span class="profile-meta-item">
                    <i data-feather="hash"></i>
                    {{ $employee->employee_id }}
                </span>
                <span class="profile-meta-item">
                    <i data-feather="briefcase"></i>
                    {{ $employee->department->name ?? 'N/A' }}
                </span>
                @if($employee->email)
                <span class="profile-meta-item">
                    <i data-feather="mail"></i>
                    {{ $employee->email }}
                </span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Personal Information -->
<div class="info-section">
    <div class="info-section-header">
        <div class="info-section-icon personal">
            <i data-feather="user"></i>
        </div>
        <h3 class="info-section-title">Personal Information</h3>
    </div>
    <div class="info-section-body">
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Full Name</div>
                <div class="info-value">{{ $employee->first_name }} {{ $employee->last_name }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Date of Birth</div>
                <div class="info-value">{{ $employee->date_of_birth ? formatDate($employee->date_of_birth) : 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Gender</div>
                <div class="info-value">{{ ucfirst($employee->gender ?? 'N/A') }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Identification Type</div>
                <div class="info-value">{{ $employee->identification_type ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">ID Number</div>
                <div class="info-value">{{ $employee->id_number ?? 'N/A' }}</div>
            </div>
            @if($employee->identification_type == 'Passport/foreign id' && $employee->passport_country)
            <div class="info-item">
                <div class="info-label">Passport Country</div>
                <div class="info-value">{{ $employee->passport_country }}</div>
            </div>
            @endif
            <div class="info-item">
                <div class="info-label">Tax Reference Number</div>
                <div class="info-value">{{ $employee->tax_reference_number ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Phone Number</div>
                <div class="info-value">{{ $employee->phone_number ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Email Address</div>
                <div class="info-value">{{ $employee->email ?? 'N/A' }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Employment Information -->
<div class="info-section">
    <div class="info-section-header">
        <div class="info-section-icon employment">
            <i data-feather="briefcase"></i>
        </div>
        <h3 class="info-section-title">Employment Information</h3>
    </div>
    <div class="info-section-body">
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Employee ID</div>
                <div class="info-value">{{ $employee->employee_id }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Department</div>
                <div class="info-value">{{ $employee->department->name ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Designation</div>
                <div class="info-value">{{ $employee->designation->name ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Date of Appointment</div>
                <div class="info-value">{{ $employee->date_of_appointment ? formatDate($employee->date_of_appointment) : 'N/A' }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Address Information -->
<div class="info-section">
    <div class="info-section-header">
        <div class="info-section-icon address">
            <i data-feather="map-pin"></i>
        </div>
        <h3 class="info-section-title">Address</h3>
    </div>
    <div class="info-section-body">
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Flat/Unit No.</div>
                <div class="info-value">{{ $employee->flat_no ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Street</div>
                <div class="info-value">{{ $employee->street ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">City</div>
                <div class="info-value">{{ $employee->city ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">State/Province</div>
                <div class="info-value">{{ $employee->state ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Postal Code</div>
                <div class="info-value">{{ $employee->pincode ?? 'N/A' }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Emergency Contact -->
<div class="info-section">
    <div class="info-section-header">
        <div class="info-section-icon emergency">
            <i data-feather="alert-circle"></i>
        </div>
        <h3 class="info-section-title">Emergency Contacts</h3>
    </div>
    <div class="info-section-body">
        @if(!empty($emergencyContacts) && count($emergencyContacts) > 0)
            @foreach($emergencyContacts as $index => $contact)
                @if(!empty($contact['name']) || !empty($contact['phone']))
                <div class="info-grid" @if(!$loop->last) style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid var(--ess-border);" @endif>
                    <div class="info-item">
                        <div class="info-label">Contact {{ $index + 1 }} Name</div>
                        <div class="info-value">{{ $contact['name'] ?: 'Not set' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Contact {{ $index + 1 }} Phone</div>
                        <div class="info-value">{{ $contact['phone'] ?: 'Not set' }}</div>
                    </div>
                </div>
                @endif
            @endforeach
        @else
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Contact Name</div>
                    <div class="info-value">Not set</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Contact Phone</div>
                    <div class="info-value">Not set</div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
