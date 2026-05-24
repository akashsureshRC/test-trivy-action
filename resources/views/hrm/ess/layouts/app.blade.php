@php
    $admin_settings = getAdminAllSetting();
    $employee = Auth::guard('employee')->user();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <title>@yield('page-title') | Employee Self-Service</title>
    
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#655997">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="ESS Portal">
    <meta name="application-name" content="ESS Portal">
    <meta name="description" content="Employee Self-Service Portal - Access your payslips, leave requests, and profile information.">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('ess-manifest.json') }}">

    <!-- Favicon -->
    <link rel="icon" href="{{ getFaviconUrl() }}" type="image/x-icon">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="{{ asset('assets/images/ess-icons/icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('assets/images/ess-icons/icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="96x96" href="{{ asset('assets/images/ess-icons/icon-96x96.png') }}">
    <link rel="apple-touch-icon" sizes="128x128" href="{{ asset('assets/images/ess-icons/icon-128x128.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('assets/images/ess-icons/icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('assets/images/ess-icons/icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('assets/images/ess-icons/icon-192x192.png') }}">
    <link rel="apple-touch-icon" sizes="384x384" href="{{ asset('assets/images/ess-icons/icon-384x384.png') }}">
    <link rel="apple-touch-icon" sizes="512x512" href="{{ asset('assets/images/ess-icons/icon-512x512.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS (Bootstrap 5.2.3 included in style.css) -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    
    <!-- Feather Icons -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
    
    <!-- Custom CSS -->
    <style>
        :root {
            /* Primary Colors - Vibrant Gradient */
            --ess-primary: #655997;
            --ess-primary-dark: #4f46e5;
            --ess-primary-darker: #4338ca;
            --ess-primary-light: #eef2ff;
            --ess-primary-rgb: 99, 102, 241;
            
            /* Secondary Colors */
            --ess-secondary: #8b5cf6;
            --ess-accent: #06b6d4;
            
            /* Background Colors */
            --ess-bg: #f8fafc;
            --ess-bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --ess-sidebar-bg: linear-gradient(180deg, #1e1b4b 0%, #312e81 100%);
            --ess-card-bg: #ffffff;
            
            /* Text Colors */
            --ess-text: #1e293b;
            --ess-text-secondary: #475569;
            --ess-text-muted: #94a3b8;
            --ess-text-light: #cbd5e1;
            
            /* Border & Dividers */
            --ess-border: #e2e8f0;
            --ess-border-light: #f1f5f9;
            
            /* Status Colors */
            --ess-success: #10b981;
            --ess-success-light: #d1fae5;
            --ess-warning: #f59e0b;
            --ess-warning-light: #fef3c7;
            --ess-danger: #ef4444;
            --ess-danger-light: #fee2e2;
            --ess-info: #3b82f6;
            --ess-info-light: #dbeafe;
            
            /* Dimensions */
            --ess-sidebar-width: 280px;
            --ess-sidebar-collapsed: 80px;
            --ess-header-height: 72px;
            --ess-border-radius: 16px;
            --ess-border-radius-sm: 12px;
            --ess-border-radius-xs: 8px;
            
            /* Shadows */
            --ess-shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --ess-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --ess-shadow-md: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --ess-shadow-lg: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --ess-shadow-xl: 0 25px 50px -12px rgb(0 0 0 / 0.25);
            --ess-shadow-glow: 0 0 40px rgba(99, 102, 241, 0.15);
            
            /* Transitions */
            --ess-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --ess-transition-fast: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--ess-bg);
            min-height: 100vh;
            color: var(--ess-text);
            overflow-x: hidden;
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--ess-text-light);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--ess-text-muted);
        }

        /* ==================== SIDEBAR ==================== */
        .ess-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--ess-sidebar-width);
            height: 100vh;
            background: var(--ess-sidebar-bg);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: var(--ess-transition);
            box-shadow: var(--ess-shadow-xl);
        }

        .ess-sidebar-header {
            padding: 24px;
            position: relative;
            z-index: 1;
        }

        .ess-sidebar-logo {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .ess-sidebar-logo img {
            max-height: 40px;
            width: auto;
        }

        .ess-sidebar-brand {
            display: flex;
            flex-direction: column;
        }

        .ess-sidebar-brand span {
            font-size: 18px;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.5px;
        }

        .ess-sidebar-brand small {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        .ess-sidebar-nav {
            flex: 1;
            padding: 8px 16px;
            overflow-y: auto;
            position: relative;
            z-index: 1;
        }

        .ess-nav-section {
            margin-bottom: 24px;
        }

        .ess-nav-section-title {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255, 255, 255, 0.4);
            padding: 0 16px;
            margin-bottom: 12px;
        }

        .ess-nav-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 18px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            border-radius: var(--ess-border-radius-sm);
            margin-bottom: 6px;
            transition: var(--ess-transition);
            font-size: 14px;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .ess-nav-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: transparent;
            border-radius: 0 3px 3px 0;
            transition: var(--ess-transition);
        }

        .ess-nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            transform: translateX(4px);
        }

        .ess-nav-item.active {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.8) 0%, rgba(139, 92, 246, 0.8) 100%);
            color: #fff;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }

        .ess-nav-item i,
        .ess-nav-item svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .ess-nav-badge {
            margin-left: auto;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 50px;
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }

        /* User Profile in Sidebar */
        .ess-sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 1;
        }

        .ess-user-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: var(--ess-border-radius-sm);
            margin-bottom: 14px;
            transition: var(--ess-transition);
        }

        .ess-user-card:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        .ess-user-avatar {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--ess-primary) 0%, var(--ess-secondary) 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 15px;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .ess-user-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 12px;
            object-fit: cover;
        }

        .ess-user-details {
            flex: 1;
            min-width: 0;
        }

        .ess-user-details h4 {
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ess-user-details p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
            margin: 2px 0 0;
        }

        .ess-logout-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 20px;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: var(--ess-border-radius-xs);
            color: #fca5a5;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--ess-transition);
        }

        .ess-logout-btn:hover {
            background: rgba(239, 68, 68, 0.25);
            color: #fff;
            transform: translateY(-2px);
        }

        /* ==================== MAIN CONTENT ==================== */
        .ess-main {
            margin-left: var(--ess-sidebar-width);
            min-height: 100vh;
            transition: var(--ess-transition);
        }

        /* Header */
        .ess-header {
            background: var(--ess-card-bg);
            border-bottom: 1px solid var(--ess-border);
            padding: 0 32px;
            height: var(--ess-header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }

        .ess-header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .ess-mobile-toggle {
            display: none;
            width: 40px;
            height: 40px;
            background: var(--ess-bg);
            border: 1px solid var(--ess-border);
            border-radius: var(--ess-border-radius-xs);
            cursor: pointer;
            transition: var(--ess-transition);
            align-items: center;
            justify-content: center;
        }

        .ess-mobile-toggle:hover {
            background: var(--ess-primary-light);
            border-color: var(--ess-primary);
        }

        .ess-mobile-toggle svg {
            width: 20px;
            height: 20px;
            color: var(--ess-text);
        }

        .ess-header-title h1 {
            font-size: 22px;
            font-weight: 700;
            color: var(--ess-text);
            margin: 0;
            letter-spacing: -0.5px;
        }

        .ess-header-title p {
            font-size: 13px;
            color: var(--ess-text-muted);
            margin: 4px 0 0;
        }

        .ess-header-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .ess-header-date {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: var(--ess-bg);
            border-radius: var(--ess-border-radius-xs);
            font-size: 13px;
            color: var(--ess-text-secondary);
        }

        .ess-header-date svg {
            width: 16px;
            height: 16px;
            color: var(--ess-primary);
        }

        /* Content Area */
        .ess-content {
            padding: 32px;
            max-width: 100%;
        }

        /* ==================== CARDS ==================== */
        .ess-card {
            background: var(--ess-card-bg);
            border-radius: var(--ess-border-radius);
            border: 1px solid var(--ess-border);
            box-shadow: var(--ess-shadow-sm);
            transition: var(--ess-transition);
            overflow: hidden;
        }

        .ess-card:hover {
            box-shadow: var(--ess-shadow-md);
        }

        .ess-card-body {
            padding: 24px;
        }

        .ess-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-bottom: 1px solid var(--ess-border-light);
        }

        .ess-card-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--ess-text);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .ess-card-title svg {
            width: 20px;
            height: 20px;
            color: var(--ess-primary);
        }

        /* ==================== STAT CARDS ==================== */
        .ess-stat-card {
            background: var(--ess-card-bg);
            border-radius: var(--ess-border-radius);
            border: 1px solid var(--ess-border);
            padding: 24px;
            display: flex;
            align-items: flex-start;
            gap: 20px;
            transition: var(--ess-transition);
            position: relative;
            overflow: hidden;
        }

        .ess-stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--ess-primary-light) 0%, transparent 70%);
            border-radius: 50%;
            transform: translate(30%, -30%);
            opacity: 0;
            transition: var(--ess-transition);
        }

        .ess-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--ess-shadow-lg);
        }

        .ess-stat-card:hover::before {
            opacity: 1;
        }

        .ess-stat-icon {
            width: 56px;
            height: 56px;
            border-radius: var(--ess-border-radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .ess-stat-icon svg {
            width: 24px;
            height: 24px;
        }

        .ess-stat-icon.primary {
            background: linear-gradient(135deg, var(--ess-primary) 0%, var(--ess-secondary) 100%);
            color: #fff;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
        }

        .ess-stat-icon.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
        }

        .ess-stat-icon.warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #fff;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.4);
        }

        .ess-stat-icon.danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #fff;
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.4);
        }

        .ess-stat-icon.info {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #fff;
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.4);
        }

        .ess-stat-content {
            flex: 1;
        }

        .ess-stat-content h3 {
            font-size: 28px;
            font-weight: 800;
            color: var(--ess-text);
            margin: 0;
            letter-spacing: -1px;
        }

        .ess-stat-content h3 small {
            font-size: 14px;
            font-weight: 500;
            color: var(--ess-text-muted);
            letter-spacing: 0;
        }

        .ess-stat-content p {
            font-size: 13px;
            color: var(--ess-text-muted);
            margin: 6px 0 0;
        }

        /* ==================== BUTTONS ==================== */
        .ess-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            border-radius: var(--ess-border-radius-xs);
            cursor: pointer;
            text-decoration: none;
            transition: var(--ess-transition);
            white-space: nowrap;
            position: relative;
            overflow: hidden;
        }

        .ess-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .ess-btn:active::before {
            width: 300px;
            height: 300px;
        }

        .ess-btn svg {
            width: 18px;
            height: 18px;
        }

        .ess-btn-rc-primary {
            background: linear-gradient(135deg, var(--ess-primary) 0%, var(--ess-primary-dark) 100%);
            color: #fff;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
        }

        .ess-btn-rc-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
            color: #fff;
        }

        .ess-btn-success {
            background: linear-gradient(135deg, var(--ess-success) 0%, #059669 100%);
            color: #fff;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
        }

        .ess-btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
            color: #fff;
        }

        .ess-btn-danger {
            background: linear-gradient(135deg, var(--ess-danger) 0%, #dc2626 100%);
            color: #fff;
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.4);
        }

        .ess-btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.5);
            color: #fff;
        }

        .ess-btn-outline {
            padding: 6px 16px !important;
            background: transparent;
            border: 2px solid var(--ess-border);
            color: var(--ess-text);
        }

        .ess-btn-outline:hover {
            background: var(--ess-bg);
            border-color: var(--ess-primary);
            color: var(--ess-primary);
        }

        .ess-btn-sm {
            padding: 8px 16px;
            font-size: 13px;
        }

        .ess-btn-sm svg {
            width: 16px;
            height: 16px;
        }

        .ess-btn-lg {
            padding: 16px 32px;
            font-size: 16px;
        }

        /* ==================== BADGES ==================== */
        .ess-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 50px;
            white-space: nowrap;
        }

        .ess-badge svg {
            width: 14px;
            height: 14px;
        }

        .ess-badge-primary {
            background: var(--ess-primary-light);
            color: var(--ess-primary-dark);
        }

        .ess-badge-success {
            background: var(--ess-success-light);
            color: #059669;
        }

        .ess-badge-warning {
            background: var(--ess-warning-light);
            color: #b45309;
        }

        .ess-badge-danger {
            background: var(--ess-danger-light);
            color: #dc2626;
        }

        .ess-badge-info {
            background: var(--ess-info-light);
            color: #1d4ed8;
        }

        /* ==================== LIST ITEMS ==================== */
        .ess-list-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
            border-bottom: 1px solid var(--ess-border-light);
            transition: var(--ess-transition);
        }

        .ess-list-item:last-child {
            border-bottom: none;
        }

        .ess-list-item:hover {
            background: var(--ess-bg);
        }

        .ess-list-item-content {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .ess-list-item-icon {
            width: 44px;
            height: 44px;
            border-radius: var(--ess-border-radius-xs);
            background: var(--ess-primary-light);
            color: var(--ess-primary);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ess-list-item-icon svg {
            width: 20px;
            height: 20px;
        }

        .ess-list-item-text h4 {
            font-size: 14px;
            font-weight: 600;
            color: var(--ess-text);
            margin: 0;
        }

        .ess-list-item-text p {
            font-size: 13px;
            color: var(--ess-text-muted);
            margin: 4px 0 0;
        }

        /* ==================== ALERTS ==================== */
        .ess-alert {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 16px 20px;
            border-radius: var(--ess-border-radius-sm);
            margin-bottom: 24px;
            font-size: 14px;
            line-height: 1.5;
        }

        .ess-alert svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .ess-alert-success {
            background: var(--ess-success-light);
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .ess-alert-success svg {
            color: var(--ess-success);
        }

        .ess-alert-error {
            background: var(--ess-danger-light);
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .ess-alert-error svg {
            color: var(--ess-danger);
        }

        .ess-alert-warning {
            background: var(--ess-warning-light);
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .ess-alert-warning svg {
            color: var(--ess-warning);
        }

        /* ==================== FORMS ==================== */
        .ess-form-group {
            margin-bottom: 24px;
        }

        .ess-form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--ess-text);
            margin-bottom: 8px;
        }

        .ess-form-label .required {
            color: var(--ess-danger);
        }

        .ess-form-control {
            width: 100%;
            padding: 14px 18px;
            font-size: 15px;
            font-family: inherit;
            color: var(--ess-text);
            background: var(--ess-card-bg);
            border: 2px solid var(--ess-border);
            border-radius: var(--ess-border-radius-xs);
            transition: var(--ess-transition);
            appearance: none;
        }

        .ess-form-control:focus {
            outline: none;
            border-color: var(--ess-primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .ess-form-control::placeholder {
            color: var(--ess-text-light);
        }

        .ess-form-control.is-invalid {
            border-color: var(--ess-danger);
        }

        .ess-form-control.is-invalid:focus {
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }

        .ess-form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 18px;
            padding-right: 50px;
        }

        .ess-invalid-feedback {
            font-size: 13px;
            color: var(--ess-danger);
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* ==================== TABLES ==================== */
        .ess-table {
            width: 100%;
            border-collapse: collapse;
        }

        .ess-table thead th {
            padding: 14px 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--ess-text-muted);
            background: var(--ess-bg);
            text-align: left;
            border-bottom: 1px solid var(--ess-border);
        }

        .ess-table tbody td {
            padding: 16px 20px;
            font-size: 14px;
            color: var(--ess-text);
            border-bottom: 1px solid var(--ess-border-light);
            vertical-align: middle;
        }

        .ess-table tbody tr:hover {
            background: var(--ess-bg);
        }

        .ess-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* ==================== PROGRESS ==================== */
        .ess-progress {
            height: 8px;
            background: var(--ess-border);
            border-radius: 50px;
            overflow: hidden;
        }

        .ess-progress-bar {
            height: 100%;
            border-radius: 50px;
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .ess-progress-bar.primary {
            background: linear-gradient(90deg, var(--ess-primary) 0%, var(--ess-secondary) 100%);
        }

        .ess-progress-bar.success {
            background: linear-gradient(90deg, var(--ess-success) 0%, #059669 100%);
        }

        .ess-progress-bar.warning {
            background: linear-gradient(90deg, var(--ess-warning) 0%, #d97706 100%);
        }

        .ess-progress-bar.danger {
            background: linear-gradient(90deg, var(--ess-danger) 0%, #dc2626 100%);
        }

        /* ==================== EMPTY STATES ==================== */
        .ess-empty-state {
            text-align: center;
            padding: 60px 40px;
        }

        .ess-empty-state-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, var(--ess-primary-light) 0%, #e0e7ff 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ess-empty-state-icon svg {
            width: 36px;
            height: 36px;
            color: var(--ess-primary);
        }

        .ess-empty-state h4 {
            font-size: 18px;
            font-weight: 600;
            color: var(--ess-text);
            margin: 0 0 8px;
        }

        .ess-empty-state p {
            font-size: 14px;
            color: var(--ess-text-muted);
            margin: 0;
            max-width: 320px;
            margin: 0 auto;
        }

        /* ==================== MOBILE OVERLAY ==================== */
        .ess-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .ess-overlay.active {
            display: block;
            opacity: 1;
        }

        /* ==================== ANIMATIONS ==================== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.4s ease-out forwards;
        }

        .animate-slide-in-right {
            animation: slideInRight 0.4s ease-out forwards;
        }

        /* Staggered animations */
        .stagger-1 { animation-delay: 0.05s; }
        .stagger-2 { animation-delay: 0.1s; }
        .stagger-3 { animation-delay: 0.15s; }
        .stagger-4 { animation-delay: 0.2s; }
        .stagger-5 { animation-delay: 0.25s; }
        .stagger-6 { animation-delay: 0.3s; }

        /* ==================== UTILITIES ==================== */
        .text-primary { color: var(--ess-primary) !important; }
        .text-success { color: var(--ess-success) !important; }
        .text-warning { color: var(--ess-warning) !important; }
        .text-danger { color: var(--ess-danger) !important; }
        .text-muted { color: var(--ess-text-muted) !important; }

        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 1199px) {
            .ess-content {
                padding: 24px;
            }
        }

        @media (max-width: 991px) {
            .ess-sidebar {
                transform: translateX(-100%);
            }

            .ess-sidebar.open {
                transform: translateX(0);
            }

            .ess-main {
                margin-left: 0;
            }

            .ess-mobile-toggle {
                display: flex;
            }

            .ess-header {
                padding: 0 20px;
            }

            .ess-content {
                padding: 20px;
            }

            .ess-header-date {
                display: none;
            }
        }

        @media (max-width: 767px) {
            .ess-header-title h1 {
                font-size: 18px;
            }

            .ess-stat-card {
                padding: 20px;
            }

            .ess-stat-content h3 {
                font-size: 24px;
            }
        }

        @media (max-width: 575px) {
            .ess-content {
                padding: 16px;
            }

            .ess-card-body {
                padding: 16px;
            }

            .ess-card-header {
                padding: 16px;
            }

            .ess-btn {
                padding: 10px 16px;
                font-size: 13px;
            }
        }
    </style>

    @yield('styles')
    @stack('styles')
</head>

<body>
    <!-- Overlay for mobile -->
    <div class="ess-overlay" id="overlay"></div>

    <!-- Sidebar -->
    <aside class="ess-sidebar" id="sidebar">
        <div class="ess-sidebar-header">
            <div class="ess-sidebar-logo">
                @php $logoUrl = sidebarLogo(); @endphp
                <img src="{{ $logoUrl ?: getLogoFallback('dark') }}" alt="Logo" onerror="this.onerror=null;this.src='{{ getLogoFallback('dark') }}'">
                <div class="ess-sidebar-brand">
                    <span>ClearPay</span>
                    <small>Employee Self-Service</small>
                </div>
            </div>
        </div>

        <nav class="ess-sidebar-nav">
            <a href="{{ route('ess.dashboard') }}" class="ess-nav-item {{ request()->routeIs('ess.dashboard*') ? 'active' : '' }}">
                <i data-feather="grid"></i>
                Dashboard
            </a>
            <a href="{{ route('ess.payslips') }}" class="ess-nav-item {{ request()->routeIs('ess.payslips*') ? 'active' : '' }}">
                <i data-feather="file-text"></i>
                Payslips
            </a>
            <a href="{{ route('ess.leave') }}" class="ess-nav-item {{ request()->routeIs('ess.leave*') ? 'active' : '' }}">
                <i data-feather="calendar"></i>
                Leave Requests
            </a>
            <a href="{{ route('ess.filing') }}" class="ess-nav-item {{ request()->routeIs('ess.filing*') ? 'active' : '' }}">
                <i data-feather="folder"></i>
                Tax Certificates
            </a>
            <a href="{{ route('ess.profile') }}" class="ess-nav-item {{ request()->routeIs('ess.profile*') ? 'active' : '' }}">
                <i data-feather="user"></i>
                My Profile
            </a>
        </nav>

        <div class="ess-sidebar-footer">
            <div class="ess-user-card">
                <div class="ess-user-avatar">
                    @if($employee->profile_picture)
                        <img src="{{ asset('storage/' . $employee->profile_picture) }}" alt="Profile">
                    @else
                        {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name ?? '', 0, 1)) }}
                    @endif
                </div>
                <div class="ess-user-details">
                    <h4>{{ $employee->first_name }} {{ $employee->last_name }}</h4>
                    <p>{{ $employee->employee_id }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('ess.logout') }}">
                @csrf
                <button type="submit" class="ess-logout-btn">
                    <i data-feather="log-out" style="width: 18px; height: 18px;"></i>
                    Sign Out
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="ess-main">
        <header class="ess-header">
            <div class="ess-header-left">
                <button class="ess-mobile-toggle" id="menuToggle">
                    <i data-feather="menu"></i>
                </button>
                <div class="ess-header-title">
                    <h1>@yield('page-title', 'Dashboard')</h1>
                    <p>@yield('page-subtitle', 'Welcome to Employee Self-Service')</p>
                </div>
            </div>
            <div class="ess-header-right">
                <div class="ess-header-date">
                    <i data-feather="calendar"></i>
                    <span>{{ now()->format('l') . ', ' . formatDate(now()) }}</span>
                </div>
                @yield('header-actions')
            </div>
        </header>

        <div class="ess-content">
            @if(session('success'))
                <div class="ess-alert ess-alert-success animate-fade-in-up">
                    <i data-feather="check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="ess-alert ess-alert-error animate-fade-in-up">
                    <i data-feather="alert-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Scripts -->
    <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>
    <script>
        // Initialize Feather Icons
        feather.replace();

        function openEssConfirmDialog(message) {
            return Swal.fire({
                html: '<div class="rc-confirm-body">'
                    + '<div class="rc-confirm-icon" aria-hidden="true"><i class="ti ti-alert-triangle"></i></div>'
                    + '<h2 class="rc-confirm-heading">Are you sure?</h2>'
                    + '<p class="rc-confirm-text">' + message + '</p>'
                    + '</div>',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                buttonsStyling: false,
                focusCancel: true,
                customClass: {
                    popup: 'rc-confirm-popup',
                    htmlContainer: 'rc-confirm-html',
                    actions: 'rc-confirm-actions',
                    confirmButton: 'btn btn-rc-primary',
                    cancelButton: 'btn btn-rc-outline'
                },
                showClass: {
                    popup: ''
                },
                hideClass: {
                    popup: ''
                }
            });
        }

        function hasNativeConfirm(handler) {
            return typeof handler === 'string' && handler.indexOf('confirm(') !== -1;
        }

        function extractConfirmMessage(handler) {
            if (!hasNativeConfirm(handler)) {
                return '';
            }
            var match = handler.match(/confirm\s*\(([^)]*)\)/i);
            if (!match || !match[1]) {
                return '';
            }
            var expression = match[1].trim();
            if (
                (expression.startsWith("'") && expression.endsWith("'")) ||
                (expression.startsWith('"') && expression.endsWith('"'))
            ) {
                expression = expression.substring(1, expression.length - 1);
            }
            return expression.replace(/\\'/g, "'").replace(/\\"/g, '"');
        }

        document.addEventListener('submit', function (event) {
            var form = event.target;
            if (!(form instanceof HTMLFormElement)) {
                return;
            }
            if (form.dataset.rcConfirmBypass === '1') {
                delete form.dataset.rcConfirmBypass;
                return;
            }
            var inlineHandler = form.getAttribute('onsubmit');
            if (!hasNativeConfirm(inlineHandler)) {
                return;
            }
            event.preventDefault();
            event.stopImmediatePropagation();

            var message = extractConfirmMessage(inlineHandler) || 'This action can not be undone. Do you want to continue?';
            openEssConfirmDialog(message).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }
                form.dataset.rcConfirmBypass = '1';
                form.removeAttribute('onsubmit');
                form.submit();
            });
        }, true);

        document.addEventListener('click', function (event) {
            var trigger = event.target.closest('[onclick]');
            if (!trigger) {
                return;
            }
            var inlineHandler = trigger.getAttribute('onclick');
            if (!hasNativeConfirm(inlineHandler)) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();

            var message = extractConfirmMessage(inlineHandler) || 'This action can not be undone. Do you want to continue?';
            openEssConfirmDialog(message).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                var formIdMatch = inlineHandler.match(/document\.getElementById\(['\"]([^'\"]+)['\"]\)\.submit\(\)/i);
                if (formIdMatch && formIdMatch[1]) {
                    var targetForm = document.getElementById(formIdMatch[1]);
                    if (targetForm) {
                        targetForm.submit();
                        return;
                    }
                }

                var nearestForm = trigger.closest('form');
                if (nearestForm) {
                    nearestForm.dataset.rcConfirmBypass = '1';
                    nearestForm.submit();
                    return;
                }

                var href = trigger.getAttribute('href');
                if (href && href !== '#' && href.toLowerCase().indexOf('javascript:') !== 0) {
                    window.location.href = href;
                }
            });
        }, true);

        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('active');
            });
        }

        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
            });
        }

        // Close sidebar when pressing Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
            }
        });
        
        // Register Service Worker for PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/ess-sw.js', { scope: '/ess/' })
                    .then((registration) => {
                        console.log('[ESS] Service Worker registered successfully:', registration.scope);
                        
                        // Check for updates
                        registration.addEventListener('updatefound', () => {
                            const newWorker = registration.installing;
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // New content available, show update notification
                                    openEssConfirmDialog('New version available! Reload to update?').then((result) => {
                                        if (result.isConfirmed) {
                                            newWorker.postMessage({ type: 'SKIP_WAITING' });
                                            window.location.reload();
                                        }
                                    });
                                }
                            });
                        });
                    })
                    .catch((error) => {
                        console.error('[ESS] Service Worker registration failed:', error);
                    });
            });
        }
        
        // Detect if app is installed
        window.addEventListener('appinstalled', () => {
            console.log('[ESS] App was installed');
        });
    </script>
    @yield('scripts')
    @stack('scripts')
</body>

</html>
