@extends('hrm.ess.layouts.app')

@section('page-title', 'Change Password')
@section('page-subtitle', 'Update your account password')

@push('styles')
<style>
    .password-card {
        background: white;
        border-radius: 24px;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        border: 1px solid var(--ess-border);
    }
    
    .password-header {
        background: linear-gradient(135deg, #655997 0%, #8b5cf6 100%);
        padding: 40px 30px;
        text-align: center;
        color: white;
    }
    
    .password-header-icon {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }
    
    .password-header-icon svg {
        width: 36px;
        height: 36px;
    }
    
    .password-header h2 {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 8px;
        color: #fff;
    }
    
    .password-header p {
        opacity: 0.9;
        margin: 0;
        font-size: 14px;
    }
    
    .password-body {
        padding: 30px;
    }
    
    .requirements-box {
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 30px;
        border: 1px solid rgba(99, 102, 241, 0.2);
    }
    
    .requirements-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        color: var(--ess-primary);
        margin-bottom: 12px;
    }
    
    .requirements-title svg {
        width: 20px;
        height: 20px;
    }
    
    .requirements-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .requirements-list li {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--ess-text-muted);
        font-size: 13px;
        padding: 6px 0;
    }
    
    .requirements-list li::before {
        content: '';
        width: 6px;
        height: 6px;
        background: var(--ess-primary);
        border-radius: 50%;
    }
    
    .password-input-group {
        position: relative;
        margin-bottom: 24px;
    }
    
    .password-input-group label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: var(--ess-text);
        margin-bottom: 10px;
    }
    
    .password-input-wrapper {
        position: relative;
    }
    
    .password-input-wrapper input {
        width: 100%;
        padding: 16px 50px 16px 20px;
        border: 2px solid var(--ess-border);
        border-radius: 14px;
        font-size: 15px;
        transition: all 0.2s ease;
        background: #fafbfc;
    }
    
    .password-input-wrapper input:focus {
        outline: none;
        border-color: var(--ess-primary);
        background: white;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }
    
    .password-toggle {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--ess-text-muted);
        cursor: pointer;
        padding: 4px;
        transition: color 0.2s ease;
    }
    
    .password-toggle:hover {
        color: var(--ess-primary);
    }
    
    .password-toggle svg {
        width: 20px;
        height: 20px;
    }
    
    .password-actions {
        display: flex;
        gap: 12px;
        margin-top: 30px;
    }
    
    .password-btn {
        flex: 1;
        padding: 16px 24px;
        border-radius: 14px;
        font-size: 15px;
        font-weight: 600;
        text-decoration: none;
        text-align: center;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .password-btn svg {
        width: 18px;
        height: 18px;
    }
    
    .password-btn-outline {
        background: white;
        border: 2px solid var(--ess-border);
        color: var(--ess-text);
    }
    
    .password-btn-outline:hover {
        border-color: var(--ess-text-muted);
        color: var(--ess-text);
    }
    
    .password-btn-rc-primary {
        background: var(--ess-primary-gradient);
        border: none;
        color: white;
    }
    
    .password-btn-rc-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(99, 102, 241, 0.35);
    }
    
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--ess-text-muted);
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        padding: 10px 16px;
        border-radius: 10px;
        transition: all 0.2s ease;
        margin-bottom: 24px;
    }
    
    .back-link:hover {
        background: white;
        color: var(--ess-primary);
    }
    
    .back-link svg {
        width: 18px;
        height: 18px;
    }
    
    .error-alert {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border: none;
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 24px;
        color: #991b1b;
    }
    
    .error-alert ul {
        margin: 0;
        padding-left: 20px;
    }
    
    .success-alert {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        border: none;
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 24px;
        color: #065f46;
        display: flex;
        align-items: center;
        gap: 10px;
    }
</style>
@endpush

@section('content')
<a href="{{ route('ess.profile') }}" class="back-link">
    <i data-feather="arrow-left"></i>
    Back to Profile
</a>

<div class="row justify-content-center">
    <div class="col-lg-5 col-md-7">
        @if($errors->any())
            <div class="error-alert">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="success-alert">
                <i data-feather="check-circle" style="width: 20px; height: 20px;"></i>
                {{ session('success') }}
            </div>
        @endif

        <div class="password-card">
            <div class="password-header">
                <div class="password-header-icon">
                    <i data-feather="shield"></i>
                </div>
                <h2>Update Password</h2>
                <p>Keep your account secure with a strong password</p>
            </div>
            
            <div class="password-body">
                <div class="requirements-box">
                    <div class="requirements-title">
                        <i data-feather="info"></i>
                        Password Requirements
                    </div>
                    <ul class="requirements-list">
                        <li>Minimum 8 characters long</li>
                        <li>Include at least one uppercase letter</li>
                        <li>Include at least one number</li>
                        <li>Include at least one special character</li>
                    </ul>
                </div>
                
                <form action="{{ route('ess.profile.update-password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="password-input-group">
                        <label for="current_password">Current Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" 
                                   id="current_password" 
                                   name="current_password" 
                                   required
                                   placeholder="Enter your current password">
                            <button type="button" class="password-toggle" onclick="togglePassword('current_password', this)">
                                <i data-feather="eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="password-input-group">
                        <label for="password">New Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   required
                                   placeholder="Enter new password">
                            <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                                <i data-feather="eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="password-input-group" style="margin-bottom: 0;">
                        <label for="password_confirmation">Confirm New Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required
                                   placeholder="Confirm new password">
                            <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation', this)">
                                <i data-feather="eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="password-actions">
                        <a href="{{ route('ess.profile') }}" class="password-btn password-btn-outline">
                            Cancel
                        </a>
                        <button type="submit" class="password-btn password-btn-rc-primary">
                            <i data-feather="check"></i>
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId, button) {
    const field = document.getElementById(fieldId);
    const isPassword = field.type === 'password';
    field.type = isPassword ? 'text' : 'password';
    
    // Update icon
    const icon = button.querySelector('svg');
    if (icon) {
        icon.outerHTML = isPassword 
            ? '<i data-feather="eye-off"></i>' 
            : '<i data-feather="eye"></i>';
        feather.replace();
    }
}
</script>
@endsection
