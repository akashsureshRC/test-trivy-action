@php
    $admin_settings = getAdminAllSetting();
    $company_settings = getCompanyAllSetting($company_id,$workspace_id);
    $temp_lang = \App::getLocale('lang');
    if($temp_lang == 'ar' || $temp_lang == 'he'){
        $rtl = 'on';
    }
    else {
        $rtl = isset($company_settings['site_rtl']) ? $company_settings['site_rtl'] : 'off';
    }
    $favicon = isset($company_settings['favicon']) ? $company_settings['favicon'] : (isset($admin_settings['favicon']) ? $admin_settings['favicon'] : null);
    $logo_dark = isset($company_settings['logo_dark']) ? $company_settings['logo_dark'] : (isset($admin_settings['logo_dark']) ? $admin_settings['logo_dark'] : null);
@endphp

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $rtl == 'on'?'rtl':''}}">
<head>

    <title>@yield('page-title') | {{ isset($company_settings['title_text']) ? $company_settings['title_text'] : (isset($admin_settings['title_text']) ? $admin_settings['title_text'] :'RC ClearPay') }}</title>

    <meta name="title" content="{{ isset($admin_settings['meta_title']) ? $admin_settings['meta_title'] : 'RC ClearPay' }}">
    <meta name="keywords" content="{{ isset($admin_settings['meta_keywords']) ? $admin_settings['meta_keywords'] : 'RC ClearPay, ClearPay, Payroll Software, Payroll System, SARS Compliant Payroll Software' }}">
    <meta name="description" content="{{ isset($admin_settings['meta_description']) ? $admin_settings['meta_description'] : 'Simplify your payroll operations with our automated, SARS-compliant platform, built for South African businesses.'}}">

    <meta name="author" content="RC ClearPay">

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" href="{{ getLogoUrl($favicon, 'favicon') ?? getLogoFallback('favicon') }}" type="image/x-icon" />
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">

    <!-- font css -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">
    <!-- vendor css -->
    <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">
    <!-- custom css -->
    <link rel="stylesheet" href="{{ asset('css/custome.css') }}">

    @if ($rtl == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}">
    @endif
    @if((isset($company_settings['cust_darklayout']) ? $company_settings['cust_darklayout'] : 'off') == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}" id="main-style-link">
    @else
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
    @endif
    <style>
        .navbar-brand .auth-navbar-brand
        {
            max-height: 38px !important;
        }
    </style>
</head>
<body class="{{ isset($company_settings['color']) ? $company_settings['color'] :'theme-1' }}">
    <div class="auth-wrapper auth-v3">
        <div class="bg-auth-side bg-primary"></div>
        <div class="auth-content">
            <nav class="navbar navbar-expand-md navbar-light default">
                <div class="container-fluid">
                    <a class="navbar-brand" href="{{ url('/') }}">
                        @php
                            $isDark = isset($company_settings['cust_darklayout']) && $company_settings['cust_darklayout'] == 'on';
                            // logo_dark = shown in dark mode; logo_light = shown in light mode
                            $logoKey = $isDark ? 'logo_dark' : 'logo_light';
                            $logoType = $isDark ? 'dark' : 'light';
                            $logoFile = $company_settings[$logoKey] ?? $admin_settings[$logoKey] ?? null;
                            $logo = getLogoUrl($logoFile, $logoType) ?? getLogoFallback($logoType);
                        @endphp
                        <img src="{{ $logo }}" alt="{{ config('app.name', 'RC ClearPay') }}" class="navbar-brand-img auth-navbar-brand" onerror="this.onerror=null;this.src='{{ getLogoFallback($isDark ? 'dark' : 'light') }}'">
                    </a>
                    <div class="lang-dropdown-only-mobile ">
                        @yield('language-bar')
                    </div>
                    <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarTogglerDemo01" style="flex-grow: 0;">
                        <ul class="navbar-nav align-items-center ms-auto mb-2 mb-lg-0">
                            @if(moduleIsActive('LandingPage'))
                                @include('landingpage::layouts.buttons')
                            @endif
                            <div class="lang-dropdown-only-desk">
                                @yield('language-bar')
                            </div>
                        </ul>
                    </div>
                </div>
            </nav>
            @yield('content')
            <div class="auth-footer">
                <div class="container-fluid">
                    <p class="">@if (isset($company_settings['footer_text'])) {{$company_settings['footer_text']}} @elseif (isset($admin_settings['footer_text'])) {{ $admin_settings['footer_text'] }} @else{{__('Copyright')}} &copy; {{ config('app.name', 'RC ClearPay') }}@endif{{date('Y')}}</p>
                </div>
            </div>
        </div>
    </div>
<script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
<script src="{{ asset('assets/js/theme.js') }}"></script>
<script src="{{ asset('assets/js/dash.js') }}"></script>
<script src="{{asset('assets/js/plugins/simple-datatables.js')}}"></script>
<script src="{{ asset('assets/js/plugins/bootstrap-switch-button.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/datepicker-full.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/flatpickr.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
<script src="{{ asset('js/jquery.form.js') }}"></script>



<script src="{{ asset('js/custom.js') }}"></script>
@if($message = Session::get('success'))
    <script>
        toastrs('Success', '{!! $message !!}', 'success');
    </script>
@endif
@if($message = Session::get('error'))
    <script>
        toastrs('Error', '{!! $message !!}', 'error');
    </script>
@endif
@if($admin_settings['enable_cookie'] == 'on')
@include('layouts.cookie_consent')
@endif
@stack('scripts')
</body>
</html>
