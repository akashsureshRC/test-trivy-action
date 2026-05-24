<style>
    /* Light mode styles - only apply when NOT in dark mode */
    body:not(.dark) .dash-header {
        border-bottom: 1px solid #dddddd !important;
        background: #fff !important;
        position: fixed;
    }

    /* Dark mode styles */
    body.dark .dash-header {
        border-bottom: 1px solid #3E3F4A !important;
        background: #1c232f !important;
        position: fixed;
    }

    body.dark .dash-header .dash-head-link,
    body.dark .dash-header .hide-mob,
    body.dark .dash-header span {
        color: #c5c5c5 !important;
    }

    body.dark .dash-header .dash-h-item>.dash-head-link.dropdown-toggle {
        background: #232c39 !important;
        border: 1px solid #3E3F4A !important;
        border-radius: 10px !important;
        color: #d1d5db !important;
    }

    body.dark .dash-header .dash-h-item>.dash-head-link.dropdown-toggle:hover {
        background: #2d3748 !important;
        border-color: #4b5563 !important;
        color: #ffffff !important;
    }

    body.dark .dash-header .cust-btn {
        background: #232c39 !important;
        border: 1px solid #3E3F4A !important;
        color: #d1d5db !important;
    }

    body.dark .dash-header .cust-btn:hover {
        background: #2d3748 !important;
        border-color: #4b5563 !important;
        color: #ffffff !important;
    }

    body.dark .dash-header .dropdown-menu {
        background: #1c232f !important;
        border-color: #3E3F4A !important;
    }

    body.dark .dash-header .dropdown-item {
        color: #c5c5c5 !important;
    }

    body.dark .dash-header .dropdown-item:hover {
        background: #2d3748 !important;
        color: #ffffff !important;
    }

    /* Workspace Dropdown Improvements */
    .dash-h-dropdown .dropdown-item {
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
    }

    .dash-h-dropdown .workspace-item-active {
        background-color: #f8f9fa;
        font-weight: 500;
        padding: 0.75rem 1rem;
        border-radius: 0.375rem;
    }

    body.dark .dash-h-dropdown .workspace-item-active {
        background-color: #2d3748;
    }

    .dash-h-dropdown .dropdown-item i {
        font-size: 1.125rem;
        width: 1.25rem;
        text-align: center;
    }

    .dash-h-dropdown .workspace-item-active .badge {
        font-size: 0.625rem;
        padding: 0.25rem 0.5rem;
        font-weight: 600;
        background-color: #fff !important;
        color: #4f46e5 !important;
        border: 1px solid #e5e7eb;
    }

    body.dark .dash-h-dropdown .workspace-item-active .badge {
        background-color: #1c232f !important;
        color: #818cf8 !important;
        border-color: #3E3F4A;
    }

    .dash-h-dropdown .dropdown-item .badge {
        font-size: 0.625rem;
        padding: 0.25rem 0.5rem;
        font-weight: 600;
    }

    body.dark .dash-h-dropdown .dropdown-item .badge.bg-light {
        background-color: #374151 !important;
        color: #d1d5db !important;
        border-color: #4b5563 !important;
    }

    .dash-h-dropdown .workspace-item-active .workspace-edit-btn {
        color: #6b7280;
        padding: 0.25rem;
        border-radius: 0.25rem;
        transition: all 0.15s ease;
        position: relative;
        z-index: 10;
        cursor: pointer;
    }

    .dash-h-dropdown .workspace-item-active .workspace-edit-btn:hover {
        background-color: #e5e7eb;
        color: #374151;
        text-decoration: none;
    }

    body.dark .dash-h-dropdown .workspace-item-active .workspace-edit-btn {
        color: #9ca3af;
    }

    body.dark .dash-h-dropdown .workspace-item-active .workspace-edit-btn:hover {
        background-color: #374151;
        color: #d1d5db;
        text-decoration: none;
    }

    .dash-h-dropdown .dropdown-item.disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .dash-h-dropdown .dropdown-divider {
        margin: 0;
    }

    .dash-h-dropdown .dropdown-item .text-muted {
        font-size: 0.75rem;
    }

    .icon-button {
        padding: 10px 20px;
        background: #4f46e5;
        color: white;
        border: none;
        cursor: pointer;
        border-radius: 5px;
    }

    /* Slide Panel - Light mode */
    .side-panel {
        position: fixed;
        top: 0;
        right: -100%;
        width: 550px;
        height: 100%;
        background: white;
        box-shadow: -4px 0 12px rgba(0, 0, 0, 0.1);
        transition: right 0.3s ease-in-out;
        z-index: 9999;
    }

    html[dir="rtl"] .dash-header {
        z-index: 10001 !important;
    }

    html[dir="rtl"] .side-panel {
        right: auto;
        left: -100%;
        transition: left 0.3s ease-in-out;
        z-index: 10002;
    }

    /* Slide Panel - Dark mode */
    body.dark .side-panel {
        background: #1c232f;
        box-shadow: -4px 0 12px rgba(0, 0, 0, 0.3);
    }

    .side-panel.open {
        right: 0;
    }

    html[dir="rtl"] .side-panel.open {
        right: auto;
        left: 0;
    }

    .side-panel .close-btn {
        right: 15px;
        font-size: 24px;
        cursor: pointer;
        color: #333;
    }

    body.dark .side-panel .close-btn {
        color: #c5c5c5;
    }
</style>


<header
    class="dash-header {{ empty($company_settings['site_transparent']) || $company_settings['site_transparent'] == 'on' ? 'transprent-bg' : '' }} ">
    <div class="header-wrapper">
        <div class="ms-auto">
            <ul class="list-unstyled">
                @impersonating($guard = null)
                <li class="dropdown dash-h-item drp-company">
                    <a class="btn btn-danger btn-sm me-3" href="{{ route('exit.company') }}"><i class="ti ti-ban"></i>
                        {{ __('Exit Company Login') }}
                    </a>
                </li>
                @endImpersonating
                @if(session('impersonating_from') && session('impersonating_from_type') === 'master_admin')
                <li class="dropdown dash-h-item drp-company">
                    <a class="btn btn-warning btn-sm me-3" href="{{ route('master-admin.return') }}">
                        <i class="ti ti-arrow-back"></i>
                        {{ __('Return to Master Admin') }}
                    </a>
                </li>
                @endif
                {{-- RC ClearPay: Chat disabled
                @permission('user chat manage')
                    @php
                        $unseenCounter = App\Models\ChMessage::where('to_id', Auth::user()->id)
                            ->where('seen', 0)
                            ->count();
                    @endphp
                    <li class="dash-h-item">
                        <a class="dash-head-link me-0" href="{{ url('/chatify') }}">
                <i class="ti ti-message-circle" style="color:#973894"></i>
                <span
                    class="bg-danger dash-h-badge message-counter custom_messanger_counter">{{ $unseenCounter }}<span
                        class="sr-only"></span>
                    </a>
                    </li>
                    @endpermission
                    --}}
                    @if(in_array(Auth::user()->type, ['company', 'payroll_officer']))
                    @permission('workspace create')
                    @if (planCheck('Workspace', Auth::user()->id) == true)
                    <li class="dash-h-item">
                        <a href="#" class="dash-head-link dropdown-toggle arrow-none me-0 cust-btn"
                            data-url="{{ route('workspace.create') }}" data-ajax-popup="true"
                            data-title="{{ __('Create New Workspace') }}">
                            <i class="ti ti-plus"></i>
                            <span class="hide-mob">{{ __('Create Workspace') }}</span>
                        </a>
                    </li>
                    @endif
                    @endpermission
                    @permission('workspace manage')
                    @if(Auth::user()->type === 'payroll_officer')
                    {{-- Payroll Officer: display workspace name only (no switch/edit) --}}
                    <li class="dash-h-item">
                        <a class="dash-head-link arrow-none me-0 cust-btn" href="javascript:void(0)" style="cursor: default;">
                            <i class="ti ti-layout-grid"></i>
                            <span class="hide-mob">{{ Auth::user()->ActiveWorkspaceName() }}</span>
                        </a>
                    </li>
                    @else
                    <li class="dropdown dash-h-item drp-language">
                        <a class="dash-head-link dropdown-toggle arrow-none me-0 cust-btn" data-bs-toggle="dropdown"
                            href="#" role="button" aria-haspopup="false" aria-expanded="false" data-bs-placement="bottom"
                            data-bs-original-title="Select your business">
                            <i class="ti ti-layout-grid"></i>
                            <span class="hide-mob">{{ Auth::user()->ActiveWorkspaceName() }}</span>
                            <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                        </a>
                        <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                            @foreach (getWorkspace() as $workspace)
                            @if ($workspace->id == getActiveWorkspace())
                            <div class="workspace-item-active">
                                <div class="d-flex align-items-center justify-content-between w-100">
                                    <div class="d-flex align-items-center">
                                        <i class="ti ti-check me-2"></i>
                                        <span>{{ $workspace->name }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($workspace->created_by == Auth::user()->id)
                                        @permission('workspace edit')
                                        <a href="#" data-url="{{ route('workspace.edit', $workspace->id) }}"
                                            data-ajax-popup="true" data-title="{{ __('Edit Workspace Name') }}"
                                            class="workspace-edit-btn">
                                            <i class="ti ti-pencil"></i>
                                        </a>
                                        @endpermission
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @else
                            @php
                            $route = ($workspace->is_disable == 1) ? route('workspace.change', $workspace->id) : '#';
                            $isLocked = $workspace->is_disable == 0;
                            @endphp
                            <a href="{{ $route }}" class="dropdown-item {{ $isLocked ? 'disabled' : '' }}">
                                <div class="d-flex align-items-center justify-content-between w-100">
                                    <div class="d-flex align-items-center">
                                        <i class="ti ti-layout me-2 {{ $isLocked ? 'text-muted' : '' }}"></i>
                                        <span class="{{ $isLocked ? 'text-muted' : '' }}">{{ $workspace->name }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($isLocked)
                                        <i class="ti ti-lock text-muted"></i>
                                        @endif
                                    </div>
                                </div>
                            </a>
                            @endif
                            @endforeach
                            @if (getWorkspace()->count() > 1)
                            <hr class="dropdown-divider" />
                            @if(Auth::user()->type !== 'payroll_officer')
                            @php
                                $adminHubLabel = Auth::user()->type === 'company' ? __('View') : __('Admin Hub');
                                $adminHubTitle = Auth::user()->type === 'company' ? __('Workspace Info') : __('Admin Hub');
                            @endphp
                            <a href="#" data-url="{{route('company.info', Auth::user()->id)}}" class="dropdown-item"
                                data-ajax-popup="true" data-size="lg" data-title="{{ $adminHubTitle }}">
                                <i class="ti ti-settings me-2"></i>
                                <span>{{ $adminHubLabel }}</span>
                            </a>
                            @endif
                            @if(Auth::user()->type !== 'payroll_officer')
                            @permission('workspace delete')
                            <hr class="dropdown-divider" />
                            <form id="remove-workspace-form" action="{{ route('workspace.destroy', getActiveWorkspace()) }}"
                                method="POST">
                                @csrf
                                @method('DELETE')
                                <a href="#" class="dropdown-item text-danger remove_workspace">
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center">
                                            <i class="ti ti-trash me-2"></i>
                                            <span>{{ __('Remove Workspace') }}</span>
                                        </div>
                                        <small class="text-muted ms-4">{{ __('Active workspace will be removed') }}</small>
                                    </div>
                                </a>
                            </form>
                            @endpermission
                            @endif
                            @endif
                        </div>
                    </li>
                    @endif
                    @endpermission
                    @endif

                    <li class="dropdown dash-h-item drp-language">
                        <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                            role="button" aria-haspopup="false" aria-expanded="false">
                            <i class="ti ti-language"></i>
                            <span class="drp-text hide-mob">{{ Str::upper(getActiveLanguage()) }}</span>
                            <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                        </a>
                        <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">

                            @foreach (languages() as $key => $language)
                            <a href="{{ route('lang.change', $key) }}"
                                class="dropdown-item @if ($key == getActiveLanguage()) text-danger @endif">
                                <span>{{ Str::ucfirst($language) }}</span>
                            </a>
                            @endforeach
                            @if (Auth::user()->type == 'super admin')
                            @permission('language create')
                            <a href="#" data-url="{{ route('create.language') }}"
                                class="dropdown-item border-top pt-3 text-primary" data-ajax-popup="true"
                                data-title="{{ __('Create New Language') }}">
                                <span>{{ __('Create Language') }}</span>
                            </a>
                            @endpermission
                            @permission('language manage')
                            <a href="{{ route('lang.index', [Auth::user()->lang]) }}"
                                class="dropdown-item  pt-3 text-primary">
                                <span>{{ __('Manage Languages') }}</span>
                            </a>
                            @endpermission
                            @endif
                        </div>
                    </li>
                    <li>
                        <ul class="list-unstyled p-0">
                            <li class="dash-h-item mob-hamburger">
                                <a href="#!" class="dash-head-link" id="mobile-collapse">
                                    <div class="hamburger hamburger--arrowturn">
                                        <div class="hamburger-box">
                                            <div class="hamburger-inner"></div>
                                        </div>
                                    </div>
                                </a>
                            </li>

                            <li class="dropdown dash-h-item drp-company">
                                <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                                    role="button" aria-haspopup="false" aria-expanded="false">
                                    @php
                                    $avatarUrl = getAvatarUrl(Auth::user()->avatar);
                                    @endphp
                                    @if (!empty($avatarUrl))
                                    <span class="theme-avtar">
                                        <img alt="#"
                                            src="{{ $avatarUrl }}"
                                            class="img-fluid rounded-circle" style="width: 100% ; height: 100%">
                                    </span>
                                    @else
                                    <span class="theme-avtar">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                    @endif
                                    <span class="hide-mob ms-2">{{ Auth::user()->name }}</span>
                                    <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                                </a>
                                <div class="dropdown-menu dash-h-dropdown">
                                    @permission('user profile manage')
                                    <a href="{{ route('profile') }}" class="dropdown-item">
                                        <i class="ti ti-user"></i>
                                        <span>{{ __('Profile') }}</span>
                                    </a>
                                    @endpermission
                                    <a href="{{ route('logout') }}"
                                        onclick="event.preventDefault(); document.getElementById('frm-logout').submit();"
                                        class="dropdown-item">
                                        <i class="ti ti-power"></i>
                                        <span>{{ __('Logout') }}</span>
                                    </a>
                                    <form id="frm-logout" action="{{ route('logout') }}" method="POST" class="d-none">
                                        {{ csrf_field() }}
                                    </form>
                                </div>
                            </li>

                        </ul>
                    </li>
                    <li class="dash-h-item">
                        <div class="dash-head-link me-0 icon-button" onclick="openPanel()">
                            <i class="ti ti-apps"></i>
                        </div>
                    </li>
                    <!-- Sliding Panel -->
                    <div class="side-panel" id="productPanel">
                        <div class="card-header" style="height: 70px; background: rgb(217, 235, 255); background: linear-gradient(250deg, rgba(217, 235, 255, 0.6) 20%, rgba(223, 234, 254, 0.6) 35%, rgba(233, 225, 249, 0.6) 59%, rgba(237, 222, 245, 0.6) 92%);">
                            <div class="d-flex row justify-content-between align-items-center h-100">
                                <div class="col-md-5">
                                    <h4 class="m-0">Our Products</h4>
                                </div>
                                <div class="close-btn col-md-6 d-flex justify-content-end" onclick="closePanel()">
                                    <button type="button" class="btn-close" style="font-size:15px" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                            </div>
                        </div>

                        <div class="panel-content card-body"
                            style="padding-top:40px;padding-bottom:40px; padding-left: 20px;padding-right:20px">
                            <div class="d-flex gap-3 justify-items-center">
                                <div class="col-md-4">
                                    <div class="rounded d-flex justify-content-center align-content-center mx-auto "
                                        style="background:#fff6ea; width:50px; height:50px; align-items:center; justify-content:center; display: flex">
                                        <div class="">
                                            <img src="{{ asset('assets/images/book.png') }}"
                                                alt="RC ClearPay" style="width: 30px; height: 30px;">
                                        </div>
                                    </div>
                                    <h5 class="mt-3" style="font-weight:500;text-align:center;font-size:14px">
                                        RC ClearPay
                                        </h4>
                                </div>
                                <div class="col-md-4">
                                    <div class="rounded d-flex justify-content-center align-content-center mx-auto "
                                        style="background:#e3feed; width:50px; height:50px; align-items:center; justify-content:center; display: flex">
                                        <div class="symbol symbol-30px">
                                            <img src="{{ asset('assets/images/pharmacy.png') }}"
                                                alt="JuniorMinds" style="width: 30px; height: 30px;">
                                        </div>
                                    </div>
                                    <h5 class="mt-3" style="font-weight:500;text-align:center;font-size:14px">
                                        Junior Minds
                                        </h4>
                                </div>
                                <div class="col-md-4">
                                    <div class="rounded d-flex justify-content-center align-content-center mx-auto "
                                        style="background:#fee9f1; width:50px; height:50px; align-items:center; justify-content:center; display: flex">
                                        <div class="symbol symbol-30px">
                                            <img src="{{ asset('assets/images/support.png') }}"
                                                alt="support" style="width: 30px; height: 30px;">
                                        </div>
                                    </div>
                                    <h5 class="mt-3" style="font-weight:500;text-align:center;font-size:14px">
                                        Support Desk
                                    </h5>
                                </div>
                            </div>


                            <div class="d-flex gap-3 justify-items-center" style="margin-top:40px">
                                <div class="col-md-4">
                                    <div class="rounded d-flex justify-content-center align-content-center mx-auto "
                                        style="background:#eee5fc; width:50px; height:50px; align-items:center; justify-content:center; display: flex">
                                        <div class="symbol symbol-30px">
                                            <img src="{{ asset('assets/images/school.png') }}"
                                                alt="School" style="width: 30px; height: 30px;">
                                        </div>
                                    </div>
                                    <h5 class="mt-3" style="font-weight:500;text-align:center;font-size:14px">
                                        Integrated School Management Solutions
                                        </h4>
                                </div>
                                <div class="col-md-4">
                                    <div class="rounded d-flex justify-content-center align-content-center mx-auto "
                                        style="background:#e4ecf7; width:50px; height:50px; align-items:center; justify-content:center; display: flex">
                                        <div class="symbol symbol-30px">
                                            <img src="{{ asset('assets/images/ecommerce.png') }}"
                                                alt="ecommerce" style="width: 30px; height: 30px;">
                                        </div>
                                    </div>
                                    <h5 class="mt-3" style="font-weight:500;text-align:center;font-size:14px">
                                        ECommerce <br>Development
                                        </h4>
                                </div>
                                <div class="col-md-4">
                                    <div class="rounded d-flex justify-content-center align-content-center mx-auto "
                                        style="background:#eae9fa; width:50px; height:50px; align-items:center; justify-content:center; display: flex">
                                        <div class="symbol symbol-30px">
                                            <img src="{{ asset('assets/images/mobile.png') }}"
                                                alt="Mobile App" style="width: 30px; height: 30px;">
                                        </div>
                                    </div>
                                    <h5 class="mt-3" style="font-weight:500;text-align:center;font-size:14px">
                                        Mobile App <br>Development
                                    </h5>
                                </div>
                            </div>

                            <div class="d-flex gap-3 justify-items-center" style="margin-top:40px">
                                <div class="col-md-4">
                                    <div class="rounded d-flex justify-content-center align-content-center mx-auto "
                                        style="background:#def3f8; width:50px; height:50px; align-items:center; justify-content:center; display: flex">
                                        <div class="symbol symbol-30px">
                                            <img src="{{ asset('assets/images/web.png') }}"
                                                alt="Smartretail" style="width: 30px; height: 30px;">
                                        </div>
                                    </div>
                                    <h5 class="mt-3" style="font-weight:500;text-align:center;font-size:14px">
                                        Smart Retail
                                        </h4>
                                </div>
                                <div class="col-md-4">
                                    <div class="rounded d-flex justify-content-center align-content-center mx-auto "
                                        style="background:#f8e1f8; width:50px; height:50px; align-items:center; justify-content:center; display: flex">
                                        <div class="symbol symbol-30px">
                                            <img src="{{ asset('assets/images/solution.png') }}"
                                                alt="Solution" style="width: 30px; height: 30px;">
                                        </div>
                                    </div>
                                    <h5 class="mt-3" style="font-weight:500;text-align:center;font-size:14px">
                                        Solution Design
                                        </h4>
                                </div>
                                <div class="col-md-4">
                                    <div class="rounded d-flex justify-content-center align-content-center mx-auto "
                                        style="background:#fce8e9; width:50px; height:50px; align-items:center; justify-content:center; display: flex">
                                        <div class="symbol symbol-30px">
                                            <img src="{{ asset('assets/images/live.png') }}"
                                                alt="RCPOSLive" style="width: 30px; height: 30px;">
                                        </div>
                                    </div>
                                    <h5 class="mt-3" style="font-weight:500;text-align:center;font-size:14px">
                                        RCPOS Live
                                    </h5>
                                </div>
                            </div>

                        </div>
                        <div class="card-footer p-3" style="  background: rgb(217, 235, 255);
            background: linear-gradient(250deg, rgba(217, 235, 255, 0.6) 20%, rgba(223, 234, 254, 0.6) 35%, rgba(233, 225, 249, 0.6) 59%, rgba(237, 222, 245, 0.6) 92%);
        bottom: 0px;
    position: inherit;
    width: -webkit-fill-available;
    width: -moz-available;
    width: stretch;
">
                            <p class="m-0" style="font-size:13px; text-align:center">
                                Please Contact Our Customer Engagement Team at <span class="fw-bold">(+27) 10 447 1845</span>
                            </p>
                        </div>
                    </div>
            </ul>
        </div>
    </div>
    <!-- Script -->
    <script>
        function openPanel() {
            document.getElementById('productPanel').classList.add('open');
        }

        function closePanel() {
            document.getElementById('productPanel').classList.remove('open');
        }
    </script>
</header>