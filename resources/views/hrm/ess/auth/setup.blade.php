@extends('hrm.ess.layouts.auth')

@section('page-title', 'Set Up Your Account')

@section('styles')
<style>
    .password-requirements {
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 28px;
        border: 1px solid rgba(99, 102, 241, 0.2);
    }
    
    .password-requirements h4 {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 600;
        color: var(--ess-primary);
        margin-bottom: 12px;
    }
    
    .password-requirements ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .password-requirements li {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        color: var(--ess-text-muted);
        padding: 6px 0;
    }
    
    .password-requirements li::before {
        content: '';
        width: 6px;
        height: 6px;
        background: var(--ess-primary);
        border-radius: 50%;
    }
    
    .disabled-input {
        background: #f3f4f6 !important;
        color: var(--ess-text-muted) !important;
        cursor: not-allowed;
    }
</style>
@endsection

@section('content')
<div class="ess-auth-card">
    <div class="ess-logo">
        @php
            $admin_settings = getAdminAllSetting();
            $logo_dark_url = getLogoUrl($admin_settings['logo_dark'] ?? null, 'dark');
        @endphp
        <img src="{{ $logo_dark_url ?: getLogoFallback('dark') }}" alt="Logo" onerror="this.onerror=null;this.src='{{ getLogoFallback('dark') }}'">
        <h1>Welcome, {{ $employee->first_name }}!</h1>
        <p>Set up your password to access Employee Self-Service</p>
    </div>

    @if(session('error'))
        <div class="ess-alert ess-alert-error">
            <i data-feather="alert-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('ess.setup.submit', $token) }}">
        @csrf

        <div class="ess-form-group">
            <label for="email">Email Address</label>
            <input 
                type="email" 
                class="ess-form-control disabled-input" 
                value="{{ $employee->email }}" 
                disabled
            >
        </div>

        <div class="ess-form-group">
            <label for="password">Create Password</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                class="ess-form-control @error('password') is-invalid @enderror" 
                placeholder="Create a strong password"
                required 
                autofocus
            >
            @error('password')
                <div class="ess-invalid-feedback">
                    <i data-feather="alert-circle" style="width: 14px; height: 14px;"></i>
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="ess-form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input 
                type="password" 
                id="password_confirmation" 
                name="password_confirmation" 
                class="ess-form-control" 
                placeholder="Confirm your password"
                required
            >
        </div>

        <div class="password-requirements">
            <h4>
                <i data-feather="shield" style="width: 18px; height: 18px;"></i>
                Password Requirements
            </h4>
            <ul>
                <li>At least 8 characters long</li>
                <li>Include at least one uppercase letter</li>
                <li>Include at least one number</li>
                <li>Use symbols for better security</li>
            </ul>
        </div>

        <button type="submit" class="ess-btn ess-btn-rc-primary">
            <i data-feather="check-circle" style="width: 18px; height: 18px;"></i>
            Set Password & Continue
        </button>
    </form>

    <div class="ess-form-footer">
        <p style="color: var(--ess-text-muted); font-size: 13px; margin-top: 28px;">
            Already have an account? <a href="{{ route('ess.login') }}">Sign in</a>
        </p>
    </div>
</div>
@endsection
