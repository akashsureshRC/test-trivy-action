
<style>
  /* Light mode sidebar styles */
  body:not(.dark) .dash-sidebar.light-sidebar {
    background: #fff !important;
    border-right: 1px solid #ddd !important;
  }
  
  body:not(.dark) .dash-sidebar .sidebar-brand-text {
    color: #5f5f5f !important;
  }
  
  /* Dark mode sidebar styles */
  body.dark .dash-sidebar.light-sidebar {
    background: #1c232f !important;
    border-right: 1px solid #3E3F4A !important;
  }
  
  body.dark .dash-sidebar .navbar-content {
    background: #1c232f !important;
  }
  
  body.dark .dash-sidebar .m-header {
    background: #1c232f !important;
  }
  
  body.dark .dash-sidebar .m-header p,
  body.dark .dash-sidebar .sidebar-brand-text {
    color: #c5c5c5 !important;
  }
  
  body.dark .dash-sidebar .dash-navbar .dash-item .dash-link:hover,
  body.dark .dash-sidebar .dash-navbar .dash-item .dash-link.active {
    color: #ffffff !important;
  }
  
  body.dark .dash-sidebar .dash-navbar .dash-submenu .dash-link {
    color: #a0a0a0 !important;
  }
  
  body.dark .dash-sidebar .dash-navbar .dash-submenu .dash-link:hover {
    color: #ffffff !important;
  }

  body.dark .dash-sidebar .dash-navbar .dash-submenu .dash-item.active > .dash-link,
  body.dark .dash-sidebar .dash-navbar .dash-submenu .dash-link.active,
  body.dark .dash-sidebar .dash-navbar .dash-submenu .dash-link[aria-current="page"] {
    color: #f3e8ff !important;
  }
</style>
<nav class="dash-sidebar light-sidebar {{ empty($company_settings['site_transparent']) || $company_settings['site_transparent'] == 'on' ? 'transprent-bg' : '' }}">
    <div class="navbar-wrapper">
        <div class="m-header main-logo justify-content-start">
            <a href="{{ route('home') }}" class="b-brand d-flex align-items-center">
                <!-- Dynamic logo based on uploaded settings -->
                @php $logoUrl = sidebarLogo(); @endphp
                <img src="{{ $logoUrl ?: getLogoFallback('dark') }}" alt="" class="logo logo-lg" style="height:100% !important" onerror="this.onerror=null;this.src='{{ getLogoFallback('dark') }}'"/>
                <p class="mb-0 sidebar-brand-text" style="font-weight:500;font-size: 24px;letter-spacing: 0.5px;">ClearPay</p>
            </a>
        </div>
        @if(!empty($company_settings['category_wise_sidemenu']) && $company_settings['category_wise_sidemenu'] == 'on')
          <div class="tab-container">
            <div class="tab-sidemenu">
              <ul class="dash-tab-link nav flex-column" role="tablist" id="dash-layout-submenus">
              </ul>
            </div>
            <div class="tab-link">
              <div class="navbar-content">
                <div class="tab-content" id="dash-layout-tab">
                </div>
                <ul class="dash-navbar">
                    {!! getMenu() !!}
                    @stack('custom_side_menu')
                </ul>
              </div>
            </div>
          </div>
        @else
          <div class="navbar-content">
              <ul class="dash-navbar">
                  {!! getMenu() !!}
                  @stack('custom_side_menu')
              </ul>
          </div>
        @endif

    </div>
</nav>
