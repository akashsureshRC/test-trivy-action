@php
    $admin_settings = getAdminAllSetting();
    $color = !empty($admin_settings['color']) ? $admin_settings['color'] : 'theme-1';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <title>@yield('page-title') | Employee Self-Service</title>
    
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" href="{{ getFaviconUrl() }}" type="image/x-icon">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/bootstrap.min.css') }}">
    
    <!-- Feather Icons -->
    <script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
    
    <!-- Custom CSS -->
    <style>
        :root {
            --ess-primary: #655997;
            --ess-primary-dark: #4f46e5;
            --ess-primary-gradient: linear-gradient(135deg, #655997 0%, #8b5cf6 100%);
            --ess-bg: #f8fafc;
            --ess-card-bg: #ffffff;
            --ess-text: #1f2937;
            --ess-text-muted: #6b7280;
            --ess-border: #e5e7eb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* Left side - Decorative */
        .auth-left {
            flex: 1;
            display: none;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.9) 0%, rgba(139, 92, 246, 0.9) 100%);
            position: relative;
            padding: 60px;
            color: white;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        @media (min-width: 992px) {
            .auth-left {
                display: flex;
            }
        }

        .auth-left::before {
            content: '';
            position: absolute;
            top: -200px;
            right: -200px;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }

        .auth-left::after {
            content: '';
            position: absolute;
            bottom: -150px;
            left: -100px;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .auth-left-content {
            position: relative;
            z-index: 1;
            max-width: 450px;
        }

        .auth-left-content h1 {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .auth-left-content p {
            font-size: 18px;
            opacity: 0.9;
            line-height: 1.7;
            margin-bottom: 40px;
        }

        .auth-features {
            list-style: none;
        }

        .auth-features li {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 18px;
            font-size: 16px;
        }

        .auth-features li .feature-icon {
            width: 44px;
            height: 44px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-features li svg {
            width: 22px;
            height: 22px;
        }

        /* Right side - Form */
        .auth-right {
            flex: 0 0 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background: var(--ess-bg);
            overflow-y: auto;
        }

        @media (min-width: 992px) {
            .auth-right {
                flex: 0 0 520px;
            }
        }

        .ess-auth-container {
            width: 100%;
            max-width: 420px;
        }

        .ess-auth-card {
            background: var(--ess-card-bg);
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            padding: 48px 40px;
            border: 1px solid var(--ess-border);
        }

        .ess-logo {
            text-align: center;
            margin-bottom: 36px;
        }

        .ess-logo img {
            max-height: 48px;
            width: auto;
        }

        .ess-logo h1 {
            font-size: 26px;
            font-weight: 800;
            background: var(--ess-primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-top: 20px;
        }

        .ess-logo p {
            color: var(--ess-text-muted);
            font-size: 15px;
            margin-top: 8px;
        }

        .ess-form-group {
            margin-bottom: 24px;
        }

        .ess-form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--ess-text);
            margin-bottom: 10px;
        }

        .ess-form-control {
            width: 100%;
            padding: 16px 20px;
            font-size: 15px;
            border: 2px solid var(--ess-border);
            border-radius: 14px;
            transition: all 0.2s ease;
            background: #fafbfc;
        }

        .ess-form-control:focus {
            outline: none;
            border-color: var(--ess-primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .ess-form-control::placeholder {
            color: #9ca3af;
        }

        .ess-form-control.is-invalid {
            border-color: #ef4444;
        }

        .ess-invalid-feedback {
            color: #ef4444;
            font-size: 13px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .ess-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 16px 24px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .ess-btn-rc-primary {
            background: var(--ess-primary-gradient);
            color: #fff;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .ess-btn-rc-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
        }

        .ess-btn-rc-primary:active {
            transform: translateY(0);
        }

        .ess-form-footer {
            text-align: center;
            margin-top: 28px;
        }

        .ess-form-footer a {
            color: var(--ess-primary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .ess-form-footer a:hover {
            color: var(--ess-primary-dark);
        }

        .ess-checkbox-group {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }

        .ess-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .ess-checkbox input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--ess-primary);
            cursor: pointer;
        }

        .ess-checkbox label {
            font-size: 14px;
            color: var(--ess-text-muted);
            cursor: pointer;
        }

        .ess-alert {
            padding: 16px 20px;
            border-radius: 14px;
            margin-bottom: 24px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .ess-alert svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .ess-alert-success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }

        .ess-alert-error {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
        }

        .ess-divider {
            text-align: center;
            margin: 28px 0;
            position: relative;
        }

        .ess-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--ess-border);
        }

        .ess-divider span {
            background: var(--ess-card-bg);
            padding: 0 16px;
            color: var(--ess-text-muted);
            font-size: 13px;
            position: relative;
        }

        .help-text {
            color: var(--ess-text-muted);
            font-size: 13px;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--ess-border);
        }

        @media (max-width: 480px) {
            .ess-auth-card {
                padding: 32px 24px;
                border-radius: 20px;
            }
            
            .ess-logo h1 {
                font-size: 22px;
            }
        }

        /* Floating shapes animation */
        .floating-shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .floating-shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-shape:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 60%;
            left: 80%;
            animation-delay: 2s;
        }

        .floating-shape:nth-child(3) {
            width: 40px;
            height: 40px;
            top: 80%;
            left: 30%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(10deg); }
        }
    </style>

    @yield('styles')
</head>

<body>
    <!-- Left Side - Decorative -->
    <div class="auth-left">
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
        
        <div class="auth-left-content">
            <h1>Welcome to Your Employee Portal</h1>
            <p>Access your payslips, manage leave requests, and stay connected with your workplace - all in one place.</p>
            
            <ul class="auth-features">
                <li>
                    <span class="feature-icon"><i data-feather="file-text"></i></span>
                    View and download your payslips anytime
                </li>
                <li>
                    <span class="feature-icon"><i data-feather="download"></i></span>
                    Download your tax certificates easily
                </li>
                <li>
                    <span class="feature-icon"><i data-feather="calendar"></i></span>
                    Apply for leave with just a few clicks
                </li>
                <li>
                    <span class="feature-icon"><i data-feather="shield"></i></span>
                    Secure access to your employment data
                </li>
            </ul>
        </div>
    </div>

    <!-- Right Side - Form -->
    <div class="auth-right">
        <div class="ess-auth-container">
            @yield('content')
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
    <script>
        feather.replace();
    </script>
    @yield('scripts')
</body>

</html>
