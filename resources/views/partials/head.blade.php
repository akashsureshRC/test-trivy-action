@php
    $faviconUrl = getFaviconUrl();
@endphp
<head>

    <title>@yield('page-title') | {{ !empty($company_settings['title_text']) ? $company_settings['title_text'] : (!empty($admin_settings['title_text']) ? $admin_settings['title_text'] :'RC ClearPay') }}
    </title>

    <meta name="title" content="{{ !empty($admin_settings['meta_title']) ? $admin_settings['meta_title'] : 'RC ClearPay' }}">
    <meta name="keywords" content="{{ !empty($admin_settings['meta_keywords']) ? $admin_settings['meta_keywords'] : 'RC ClearPay, ClearPay, Payroll Software, Payroll System, SARS Compliant Payroll Software' }}">
    <meta name="description" content="{{ !empty($admin_settings['meta_description']) ? $admin_settings['meta_description'] : 'Simplify your payroll operations with our automated, SARS-compliant platform, built for South African businesses.'}}">

    <meta name="author" content="RC ClearPay">

    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="url" content="{{ url('').'/'.config('chatify.routes.prefix') }}" data-user="{{ Auth::user()->id }}">

    {{-- <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests" /> --}}

    <!-- Favicon icon -->
    <link rel="icon" href="{{ $faviconUrl }}{{'?'.time()}}" type="image/x-icon" />

    <!-- font css -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css')}}">

    <!-- vendor css -->
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/style.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/plugins/bootstrap-switch-button.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/datepicker-bs5.min.css') }}" >
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/flatpickr.min.css') }}" >
    <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">
    
    <style>
        :root {
            --color-customColor: <?= $color ?>;
        }
    </style>

    <link rel="stylesheet" href="{{ asset('css/custom-color.css') }}">
    @if ((isset($company_settings['site_rtl']) ? $company_settings['site_rtl'] : 'off')== 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}" id="rtl-style-link">
    @endif

    @if ((isset($company_settings['cust_darklayout']) ? $company_settings['cust_darklayout'] : 'off') == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}" id="main-style-link">
    @else
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
    @endif

    <!-- Custom overrides - loaded last for highest priority -->
    <link rel="stylesheet" href="{{ asset('css/custome.css') }}">

    <!-- Design System - Unified UI/UX Components -->
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">

    @stack('css')
    @stack('availabilitylink')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <link rel='stylesheet' href='https://unpkg.com/nprogress@0.2.0/nprogress.css'/>
    <script src='https://unpkg.com/nprogress@0.2.0/nprogress.js'></script>
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
</head>
