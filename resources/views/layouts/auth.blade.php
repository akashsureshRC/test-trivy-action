@php
$admin_settings = getAdminAllSetting();
$temp_lang = \App::getLocale('lang');
if ($temp_lang == 'ar' || $temp_lang == 'he') {
$rtl = 'on';
} else {
$rtl = isset($admin_settings['site_rtl']) ? $admin_settings['site_rtl'] : 'off';
}
$color = !empty($admin_settings['color']) ? $admin_settings['color'] : 'theme-1';

if (isset($admin_settings['color_flag']) && $admin_settings['color_flag'] == 'true') {
$themeColor = 'custom-color';
} else {
$themeColor = $color;
}
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{  $rtl == 'on' ? 'rtl' : ''}}">

<head>

    <title>@yield('page-title') |
        {{ !empty($admin_settings['title_text']) ? $admin_settings['title_text'] : config('app.name', 'RC ClearPay') }}
    </title>

    <meta name="title"
        content="{{ !empty($admin_settings['meta_title']) ? $admin_settings['meta_title'] : 'RC ClearPay' }}">
    <meta name="keywords"
        content="{{ !empty($admin_settings['meta_keywords']) ? $admin_settings['meta_keywords'] : 'RC ClearPay, ClearPay, Payroll Software, Payroll System, SARS Compliant Payroll Software' }}">
    <meta name="description"
        content="{{ !empty($admin_settings['meta_description']) ? $admin_settings['meta_description'] : 'Simplify your payroll operations with our automated, SARS-compliant platform, built for South African businesses.'}}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ env('APP_URL') }}">
    <meta property="og:title"
        content="{{ !empty($admin_settings['meta_title']) ? $admin_settings['meta_title'] : 'RC ClearPay' }}">
    <meta property="og:description"
        content="{{ !empty($admin_settings['meta_description']) ? $admin_settings['meta_description'] : 'Simplify your payroll operations with our automated, SARS-compliant platform, built for South African businesses.'}} ">
    <meta property="og:image"
        content="{{ getFile((!empty($admin_settings['meta_image'])) ? (checkFile($admin_settings['meta_image'])) ? $admin_settings['meta_image'] : 'uploads/meta/meta_image.png' : 'uploads/meta/meta_image.png') }}{{'?' . time() }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ env('APP_URL') }}">
    <meta property="twitter:title"
        content="{{ !empty($admin_settings['meta_title']) ? $admin_settings['meta_title'] : 'RC ClearPay' }}">
    <meta property="twitter:description"
        content="{{ !empty($admin_settings['meta_description']) ? $admin_settings['meta_description'] : 'Simplify your payroll operations with our automated, SARS-compliant platform, built for South African businesses.'}} ">
    <meta property="twitter:image"
        content="{{ getFile((!empty($admin_settings['meta_image'])) ? (checkFile($admin_settings['meta_image'])) ? $admin_settings['meta_image'] : 'uploads/meta/meta_image.png' : 'uploads/meta/meta_image.png') }}{{'?' . time() }}">

    <meta name="author" content="RC ClearPay">

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon"
        href="{{ getFaviconUrl() }}{{'?' . time()}}"
        type="image/x-icon" />
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
    <style>
    :root {
        --color-customColor:
            <?=$color ?>;
    }
    </style>

    <link rel="stylesheet" href="{{ asset('css/custom-color.css') }}">

    @if ($rtl == 'on')
    <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom-auth-rtl.css') }}" id="main-style-link">
    @else
    <link rel="stylesheet" href="{{ asset('css/custom-auth.css') }}" id="main-style-link">
    @endif

    @if((isset($admin_settings['cust_darklayout']) ? $admin_settings['cust_darklayout'] : 'off') == 'on')
    <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}" id="main-style-link">
    <link rel="stylesheet" href="{{ asset('css/custom-auth-dark.css') }}" id="main-style-link">
    @endif

    @if($rtl != 'on' && (isset($admin_settings['cust_darklayout']) ? $admin_settings['cust_darklayout'] : 'off') !=
    'on')
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
    @endif

    <!-- Design System - Unified UI/UX Components -->
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">

    <style>
    .navbar-brand .auth-navbar-brand {
        max-height: 38px !important;
    }

    .custom-wrapper {
        padding: 0px 0 40px !important;
    }

    html,
    body {
        margin: 0;
        padding: 0;
        height: 100%;
        overflow: hidden;
        /* Prevent scrolling */
    }

    .custom-login {
        display: flex;
        height: 100vh;
        width: 100%;
        align-items: center;
    }

    .custom-login .left-side,
    .custom-login .right-side {
        flex: 1;
        height: 100vh;
    }

    .custom-login .left-side {
        display: flex;
        justify-content: center;
        align-items: center;
        background: #fff;
    }

    .custom-login .right-side img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    </style>
</head>

<body class="{{ $themeColor }}">


    <div class="custom-login d-flex" style="min-height: 100vh;">
    <div class="col-md-6 d-flex justify-content-center align-items-center">
        <main class="custom-wrapper w-100">
            <div class="custom-row">
                <div class="card" style="top:30%; border:0;">
                    @yield('content')
                    <p class="fw-600 text-center mt-4">
                        Copyright ©️ <span class="text-primary">2020-2026
                            Reliance Corporation</span>. All Rights Reserved.
                    </p>
                </div>
            </div>
        </main>
    </div>
    <div class="col-md-6 p-0">
        <img src="{{ asset('assets/images/loginbackground.webp') }}" alt="Login Background"
            style="width: 100%; height: 100vh; object-fit: cover;" />
    </div>
</div>




    @if((isset($admin_settings['enable_cookie']) ? $admin_settings['enable_cookie'] : 'off') == 'on')
    @include('layouts.cookie_consent')
    @endif
    @stack('custom-scripts')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
    @stack('script')
    @if((isset($admin_settings['cust_darklayout']) ? $admin_settings['cust_darklayout'] : 'off') == 'on')
    <script>
    document.addEventListener('DOMContentLoaded', (event) => {
        const recaptcha = document.querySelector('.g-recaptcha');
        recaptcha.setAttribute("data-theme", "dark");
    });
    </script>
    @endif
</body>

</html>