@extends('hrm.ess.layouts.auth')

@section('page-title', 'Login')

@section('content')
<div class="ess-auth-card">
    <div class="ess-logo">
        @php $logoUrl = sidebarLogo(); @endphp
        <img src="{{ $logoUrl ?: getLogoFallback('dark') }}" alt="Logo" onerror="this.onerror=null;this.src='{{ getLogoFallback('dark') }}'">
        <h1>Employee Self-Service</h1>
        <p>Sign in to access your portal</p>
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

    <form method="POST" action="{{ route('ess.login.submit') }}">
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

        <div class="ess-form-group">
            <label for="password">Password</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                class="ess-form-control @error('password') is-invalid @enderror" 
                placeholder="Enter your password"
                required
            >
            @error('password')
                <div class="ess-invalid-feedback">
                    <i data-feather="alert-circle" style="width: 14px; height: 14px;"></i>
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="ess-checkbox-group">
            <div class="ess-checkbox">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label for="remember">Remember me</label>
            </div>
            <a href="{{ route('ess.forgot-password') }}">Forgot password?</a>
        </div>

        <button type="submit" class="ess-btn ess-btn-rc-primary">
            <i data-feather="log-in" style="width: 18px; height: 18px;"></i>
            Sign In
        </button>
    </form>

    <p class="help-text" style="text-align: center;">
        Need help? Contact your HR department for assistance.
    </p>
</div>
@endsection
