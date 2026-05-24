@extends('hrm.ess.layouts.auth')

@section('page-title', 'Forgot Password')

@section('content')
<div class="ess-auth-card">
    <div class="ess-logo">
        @php
            $admin_settings = getAdminAllSetting();
            $logo_dark_url = getLogoUrl($admin_settings['logo_dark'] ?? null, 'dark');
        @endphp
        <img src="{{ $logo_dark_url ?: getLogoFallback('dark') }}" alt="Logo" onerror="this.onerror=null;this.src='{{ getLogoFallback('dark') }}'">
        <h1>Reset Password</h1>
        <p>Enter your email to receive a password reset link</p>
    </div>

    @if(session('success'))
        <div class="ess-alert ess-alert-success">
            <i data-feather="check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="ess-alert ess-alert-error">
            <i data-feather="alert-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('ess.forgot-password.submit') }}">
        @csrf

        <div class="ess-form-group">
            <label for="email">Email Address</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                class="ess-form-control @error('email') is-invalid @enderror" 
                value="{{ old('email') }}" 
                placeholder="name@company.com"
                required 
                autofocus
            >
            @error('email')
                <div class="ess-invalid-feedback">
                    <i data-feather="alert-circle" style="width: 14px; height: 14px;"></i>
                    {{ $message }}
                </div>
            @enderror
        </div>

        <button type="submit" class="ess-btn ess-btn-rc-primary">
            <i data-feather="mail" style="width: 18px; height: 18px;"></i>
            Send Reset Link
        </button>
    </form>

    <div class="ess-form-footer">
        <a href="{{ route('ess.login') }}">
            <i data-feather="arrow-left" style="width: 14px; height: 14px; margin-right: 6px;"></i>
            Back to Sign In
        </a>
    </div>
</div>
@endsection
