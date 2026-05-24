<!--Brand Settings-->
<div id="site-settings" class="">
    {{ Form::open(['route' => ['company.settings.save'], 'enctype' => 'multipart/form-data', 'id' => 'setting-form']) }}
    @method('post')
    <div class="card">
        <div class="card-header">
            <h5>{{ __('Brand Settings') }}</h5>
        </div>
        <div class="card-body pb-0">
            <div class="row">
                <div class="col-lg-4 col-12 d-flex">
                    <div class="card w-100">
                        <div class="card-header">
                            <h5 class="small-title">{{ __('Logo Dark') }}</h5>
                        </div>
                        <div class="card-body setting-card setting-logo-box p-3" style="border-top-left-radius: 0; border-top-right-radius: 0;">
                            <div class="d-flex flex-column justify-content-between align-items-center h-100">
                                <div class="logo-content img-fluid logo-set-bg text-center py-2">
                                    @php $logo_dark_url = getLogoUrl($settings['logo_dark'] ?? null, 'dark'); @endphp
                                    @if($logo_dark_url)
                                        <img alt="image" src="{{ $logo_dark_url }}"
                                            class="small-logo" id="pre_default_logo" onerror="this.onerror=null;this.src='{{ getLogoFallback('dark') }}'">
                                    @else
                                        <span class="text-muted" id="pre_default_logo_text">{{ __('Not Uploaded') }}</span>
                                        <img alt="image" src="" class="small-logo d-none" id="pre_default_logo">
                                    @endif
                                </div>
                                <div class="choose-files mt-3">
                                    <label for="logo_dark">
                                        <div class=" bg-primary "> <i
                                                class="ti ti-upload px-1"></i>{{ __('Choose file here') }}</div>
                                        <input type="file" class="form-control file" name="logo_dark" id="logo_dark"
                                            data-filename="logo_dark" accept="image/*"
                                            onchange="document.getElementById('pre_default_logo').src = window.URL.createObjectURL(this.files[0]); document.getElementById('pre_default_logo').classList.remove('d-none'); var txt = document.getElementById('pre_default_logo_text'); if(txt) txt.classList.add('d-none');">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-12 d-flex">
                    <div class="card w-100">
                        <div class="card-header">
                            <h5 class="small-title">{{ __('Logo Light') }}</h5>
                        </div>
                        <div class="card-body setting-card setting-logo-box p-3" style="border-top-left-radius: 0; border-top-right-radius: 0;">
                            <div class="d-flex flex-column justify-content-between align-items-center h-100">
                                <div class="logo-content img-fluid logo-set-bg text-center py-2">
                                    @php $logo_light_url = getLogoUrl($settings['logo_light'] ?? null, 'light'); @endphp
                                    @if($logo_light_url)
                                        <img alt="image" src="{{ $logo_light_url }}"
                                            class="small-logo" id="landing_page_logo" onerror="this.onerror=null;this.src='{{ getLogoFallback('light') }}'">
                                    @else
                                        <span class="text-muted" id="landing_page_logo_text">{{ __('Not Uploaded') }}</span>
                                        <img alt="image" src="" class="small-logo d-none" id="landing_page_logo">
                                    @endif
                                </div>
                                <div class="choose-files mt-3">
                                    <label for="logo_light">
                                        <div class=" bg-primary "> <i
                                                class="ti ti-upload px-1"></i>{{ __('Choose file here') }}</div>
                                        <input type="file" class="form-control file" name="logo_light"
                                            id="logo_light" data-filename="logo_light" accept="image/*"
                                            onchange="document.getElementById('landing_page_logo').src = window.URL.createObjectURL(this.files[0]); document.getElementById('landing_page_logo').classList.remove('d-none'); var txt = document.getElementById('landing_page_logo_text'); if(txt) txt.classList.add('d-none');">

                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-12 d-flex">
                    <div class="card w-100">
                        <div class="card-header">
                            <h5 class="small-title">{{ __('Favicon') }}</h5>
                        </div>
                        <div class="card-body setting-card setting-logo-box p-3" style="border-top-left-radius: 0; border-top-right-radius: 0;">
                            <div class="d-flex flex-column justify-content-between align-items-center h-100">
                                <div class="logo-content img-fluid logo-set-bg text-center py-2">
                                    @php $favicon_url = getLogoUrl($settings['favicon'] ?? null, 'favicon'); @endphp
                                    @if($favicon_url)
                                        <img src="{{ $favicon_url }}" class="setting-img"
                                            width="40px" id="img_favicon" onerror="this.onerror=null;this.src='{{ getLogoFallback('favicon') }}'" />
                                    @else
                                        <span class="text-muted" id="img_favicon_text">{{ __('Not Uploaded') }}</span>
                                        <img src="" class="setting-img d-none" width="40px" id="img_favicon" />
                                    @endif
                                </div>
                                <div class="choose-files mt-3">
                                    <label for="favicon">
                                        <div class=" bg-primary "> <i
                                                class="ti ti-upload px-1"></i>{{ __('Choose file here') }}</div>
                                        <input type="file" class="form-control file" name="favicon" id="favicon"
                                            data-filename="favicon" accept="image/*"
                                            onchange="document.getElementById('img_favicon').src = window.URL.createObjectURL(this.files[0]); document.getElementById('img_favicon').classList.remove('d-none'); var txt = document.getElementById('img_favicon_text'); if(txt) txt.classList.add('d-none');">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 col-12">
                    <div class="form-group">
                        <label for="title_text" class="form-label">{{ __('Title Text') }}</label>
                        {{ Form::text('title_text', !empty($settings['title_text']) ? $settings['title_text'] : null, ['class' => 'form-control', 'placeholder' => __('Enter Title Text')]) }}
                    </div>
                </div>
                <div class="col-sm-6 col-12">
                    <div class="form-group">
                        <label for="footer_text" class="form-label">{{ __('Footer Text') }}</label>
                        {{ Form::text('footer_text', !empty($settings['footer_text']) ? $settings['footer_text'] : null, ['class' => 'form-control', 'placeholder' => __('Enter Footer Text')]) }}
                    </div>
                </div>
                <div class="row mt-2">
                    <h4 class="small-title">{{ __('Theme Customizer') }}</h4>
                    <div class="setting-card setting-logo-box p-3">
                        <div class="row gy-2">
                            <div class="col-xxl-2 col-md-4 col-sm-6 col-12">
                                <h6 class="text-md">
                                    <i class="ti ti-credit-card me-2 h5"></i>{{ __('Primary color settings') }}
                                </h6>

                                <hr class="my-2" />
                                <div class="color-wrp">
                                    <div class="theme-color themes-color">
                                        <a href="#!"
                                            class="themes-color-change {{ isset($settings['color']) && $settings['color'] == 'theme-1' ? 'active_color' : '' }}"
                                            data-value="theme-1"></a>
                                        <input type="radio" class="theme_color d-none" name="color"
                                            value="theme-1"{{ isset($settings['color']) && $settings['color'] == 'theme-1' ? 'checked' : '' }}>
                                        <a href="#!"
                                            class="themes-color-change {{ isset($settings['color']) && $settings['color'] == 'theme-2' ? 'active_color' : '' }}"
                                            data-value="theme-2"></a>
                                        <input type="radio" class="theme_color d-none" name="color"
                                            value="theme-2"{{ isset($settings['color']) && $settings['color'] == 'theme-2' ? 'checked' : '' }}>
                                        <a href="#!"
                                            class="themes-color-change {{ isset($settings['color']) && $settings['color'] == 'theme-3' ? 'active_color' : '' }}"
                                            data-value="theme-3"></a>
                                        <input type="radio" class="theme_color d-none" name="color"
                                            value="theme-3"{{ isset($settings['color']) && $settings['color'] == 'theme-3' ? 'checked' : '' }}>

                                        <a href="#!"
                                            class="themes-color-change {{ isset($settings['color']) && $settings['color'] == 'theme-4' ? 'active_color' : '' }}"
                                            data-value="theme-4"></a>
                                        <input type="radio" class="theme_color d-none" name="color"
                                            value="theme-4"{{ isset($settings['color']) && $settings['color'] == 'theme-4' ? 'checked' : '' }}>
                                        <a href="#!"
                                            class="themes-color-change {{ isset($settings['color']) && $settings['color'] == 'theme-5' ? 'active_color' : '' }}"
                                            data-value="theme-5"></a>
                                        <input type="radio" class="theme_color d-none" name="color"
                                            value="theme-5"{{ isset($settings['color']) && $settings['color'] == 'theme-5' ? 'checked' : '' }}>

                                        <a href="#!"
                                            class="themes-color-change {{ isset($settings['color']) && $settings['color'] == 'theme-6' ? 'active_color' : '' }}"
                                            data-value="theme-6"></a>
                                        <input type="radio" class="theme_color d-none" name="color"
                                            value="theme-6"{{ isset($settings['color']) && $settings['color'] == 'theme-6' ? 'checked' : '' }}>

                                        <a href="#!"
                                            class="themes-color-change {{ isset($settings['color']) && $settings['color'] == 'theme-7' ? 'active_color' : '' }}"
                                            data-value="theme-7"></a>
                                        <input type="radio" class="theme_color d-none" name="color"
                                            value="theme-7"{{ isset($settings['color']) && $settings['color'] == 'theme-7' ? 'checked' : '' }}>

                                        <a href="#!"
                                            class="themes-color-change {{ isset($settings['color']) && $settings['color'] == 'theme-8' ? 'active_color' : '' }}"
                                            data-value="theme-8"></a>
                                        <input type="radio" class="theme_color d-none" name="color"
                                            value="theme-8"{{ isset($settings['color']) && $settings['color'] == 'theme-8' ? 'checked' : '' }}>
                                        <a href="#!"
                                            class="themes-color-change {{ isset($settings['color']) && $settings['color'] == 'theme-9' ? 'active_color' : '' }}"
                                            data-value="theme-9"></a>
                                        <input type="radio" class="theme_color d-none" name="color"
                                            value="theme-9"{{ isset($settings['color']) && $settings['color'] == 'theme-9' ? 'checked' : '' }}>

                                        <a href="#!"
                                            class="themes-color-change {{ isset($settings['color']) && $settings['color'] == 'theme-10' ? 'active_color' : '' }}"
                                            data-value="theme-10"></a>
                                        <input type="radio" class="theme_color d-none" name="color"
                                            value="theme-10"{{ isset($settings['color']) && $settings['color'] == 'theme-10' ? 'checked' : '' }}>
                                            <div class="color-picker-wrp ">
                                                <input type="color"
                                                    value="{{ isset($settings['color']) ? $settings['color'] : '' }}"
                                                    class="colorPicker {{ isset($settings['color_flag']) && $settings['color_flag'] == 'true' ? 'active_color' : '' }}"
                                                    name="custom_color" id="color-picker">
                                                <input type='hidden' name="color_flag"
                                                    value={{ isset($settings['color_flag']) && $settings['color_flag'] == 'true' ? 'true' : 'false' }}>
                                            </div>
                                    </div>

                                </div>
                            </div>
                            <div class="col-xxl-3 col-md-4 col-sm-6 col-12">
                                <h6 class="text-md">
                                    <i class="ti ti-layout-sidebar me-2 h5"></i> {{ __('Sidebar settings') }}
                                </h6>
                                <hr class="my-2" />
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="site_transparent"
                                        name="site_transparent"
                                        {{ isset($settings['site_transparent']) && $settings['site_transparent'] == 'on' ? 'checked' : '' }} />

                                    <label class="form-check-label f-w-600 pl-1"
                                        for="site_transparent">{{ __('Transparent layout') }}</label>
                                </div>
                            </div>
                            <div class="col-xxl-2 col-md-4 col-sm-6 col-12">
                                <h6 class="text-md">
                                    <i class="ti ti-sun me-2 h5"></i>{{ __('Layout settings') }}
                                </h6>
                                <hr class=" my-2 " />
                                <div class="form-check form-switch mt-2">

                                    <input type="checkbox" class="form-check-input" id="cust-darklayout"
                                        name="cust_darklayout"
                                        {{ isset($settings['cust_darklayout']) && $settings['cust_darklayout'] == 'on' ? 'checked' : '' }} />
                                    <label class="form-check-label f-w-600 pl-1"
                                        for="cust-darklayout">{{ __('Dark Layout') }}</label>

                                </div>
                            </div>
                            <div class="col-xxl-2 col-md-4 col-sm-6 col-12">
                                <h6 class="text-md">
                                    <i class="ti ti-align-right me-2 h5"></i>{{ __('Enable RTL') }}
                                </h6>
                                <hr class=" my-2 " />
                                <div class="form-check form-switch mt-2">

                                    <input type="checkbox" class="form-check-input" id="site_rtl" name="site_rtl"
                                        {{ isset($settings['site_rtl']) && $settings['site_rtl'] == 'on' ? 'checked' : '' }} />
                                    <label class="form-check-label f-w-600 pl-1"
                                        for="site_rtl">{{ __('RTL Layout') }}</label>

                                </div>
                            </div>
                            <div class="col-xxl-3 col-md-4 col-sm-6 col-12">
                                <h6 class="text-md">
                                    <i class="ti ti-align-right me-2 h5"></i>{{ __('Category Wise Sidemenu') }}
                                </h6>
                                <hr class=" my-2 " />
                                <div class="form-check form-switch mt-2">

                                    <input type="checkbox" class="form-check-input" id="category_wise_sidemenu" name="category_wise_sidemenu"
                                        {{ isset($settings['category_wise_sidemenu']) && $settings['category_wise_sidemenu'] == 'on' ? 'checked' : '' }} />
                                    <label class="form-check-label f-w-600 pl-1"
                                        for="category_wise_sidemenu">{{ __('Category Wise Sidemenu') }}</label>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="card-footer text-end">
            <input class="btn btn-print-invoice btn-rc-primary " type="submit" value="{{ __('Save Changes') }}">
        </div>
        {{ Form::close() }}
    </div>
</div>

<!--system settings-->
<div class="row">
    <div class="col-sm-12 col-md-12">
        <div class="card" id="system-settings">
            <div class="card-header">
                <h5 class="small-title">{{ __('System Settings') }}</h5>
            </div>
            {{ Form::open(['route' => ['company.system.setting.store'], 'id' => 'setting-system-form']) }}
            @method('post')
            <div class="card-body pb-0">
                <div class="row">
                    <div class="col-6">
                        <div class="form-group col switch-width">
                            {{ Form::label('defult_language', __('Default Language'), ['class' => ' form-label']) }}
                            <select class="form-control" data-trigger name="defult_language" id="defult_language"
                                placeholder="This is a search placeholder">
                                @foreach (languages() as $key => $language)
                                    <option value="{{ $key }}"
                                        {{ isset($settings['defult_language']) && $settings['defult_language'] == $key ? 'selected' : '' }}>
                                        {{ Str::ucfirst($language) }} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6 col-6">
                        <div class="form-group col switch-width">
                            {{ Form::label('defult_timezone', __('Default Timezone'), ['class' => ' form-label']) }}
                            {{ Form::select('defult_timezone', $timezones, isset($settings['defult_timezone']) ? $settings['defult_timezone'] : null, ['id' => 'timezone', 'class' => 'form-control choices', 'searchEnabled' => 'true']) }}
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="site_date_format" class="form-label">{{ __('Date Format') }}</label>
                            <select type="text" name="site_date_format" class="form-control selectric"
                                id="site_date_format">
                                <option value="d-m-Y" @if (isset($settings['site_date_format']) && $settings['site_date_format'] == 'd-m-Y') selected="selected" @endif>
                                    DD-MM-YYYY</option>
                                <option value="m-d-Y" @if (isset($settings['site_date_format']) && $settings['site_date_format'] == 'm-d-Y') selected="selected" @endif>
                                    MM-DD-YYYY</option>
                                <option value="Y-m-d" @if (isset($settings['site_date_format']) && $settings['site_date_format'] == 'Y-m-d') selected="selected" @endif>
                                    YYYY-MM-DD</option>
                                <option value="M d, Y" @if (isset($settings['site_date_format']) && $settings['site_date_format'] == 'M d, Y') selected="selected" @endif>
                                    {{ __('Month Day, Year') }} (Feb 21, 2026)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="site_time_format" class="form-label">{{ __('Time Format') }}</label>
                            <select type="text" name="site_time_format" class="form-control selectric"
                                id="site_time_format">
                                <option value="g:i A" @if (isset($settings['site_time_format']) && $settings['site_time_format'] == 'g:i A') selected="selected" @endif>
                                    10:30 PM</option>
                                <option value="H:i" @if (isset($settings['site_time_format']) && $settings['site_time_format'] == 'H:i') selected="selected" @endif>
                                    22:30</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <input class="btn btn-print-invoice btn-rc-primary " type="submit" value="{{ __('Save Changes') }}">
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>

{{-- company setting  --}}
<div class="card" id="company-setting-sidenav">
    {{ Form::open(['route' => 'company.setting.save', 'id' => 'company-settings-form']) }}
    <div class="card-header">
        <div class="row">
            <div class="col-lg-10 col-md-10 col-sm-10">
                <h5 class="">{{ __('Company Settings') }}</h5>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row mt-2">
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('company_name', __('Company Name'), ['class' => 'form-label']) }} <span class="text-danger">*</span>
                    {{ Form::text('company_name', !empty($settings['company_name']) ? $settings['company_name'] : null, ['class' => 'form-control ', 'placeholder' => 'Enter Company Name']) }}
                    <div class="text-danger d-none" id="company_name_error">Company Name is required.</div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-group">
                    {{ Form::label('company_address', __('Address'), ['class' => 'form-label']) }} <span class="text-danger">*</span>
                    {{ Form::text('company_address', !empty($settings['company_address']) ? $settings['company_address'] : null, ['class' => 'form-control ', 'placeholder' => 'Enter Address']) }}
                    <div class="text-danger d-none" id="company_address_error">Address is required.</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('company_address_2', __('Address 2'), ['class' => 'form-label']) }}
                    {{ Form::text('company_address_2', !empty($settings['company_address_2']) ? $settings['company_address_2'] : null, ['class' => 'form-control ', 'placeholder' => 'Enter Address 2']) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('company_address_3', __('Address 3'), ['class' => 'form-label']) }}
                    {{ Form::text('company_address_3', !empty($settings['company_address_3']) ? $settings['company_address_3'] : null, ['class' => 'form-control ', 'placeholder' => 'Enter Address 3']) }}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('company_country', __('Country'), ['class' => 'form-label']) }} <span class="text-danger">*</span>
                    {{ Form::select('company_country', $countries, !empty($settings['company_country']) ? $settings['company_country'] : old('company_country'), ['class' => 'form-control', 'placeholder' => 'Select Country']) }}
                    <div class="text-danger d-none" id="company_country_error">Country is required.</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('company_state', __('State'), ['class' => 'form-label']) }} <span class="text-danger">*</span>
                    {{ Form::select('company_state', [], !empty($settings['company_state']) ? $settings['company_state'] : old('company_state'), ['class' => 'form-control', 'placeholder' => 'Select Country First']) }}
                    <div class="text-danger d-none" id="company_state_error">State is required.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('company_city', __('City'), ['class' => 'form-label']) }} <span class="text-danger">*</span>
                    {{ Form::text('company_city', !empty($settings['company_city']) ? $settings['company_city'] : null, ['class' => 'form-control ', 'placeholder' => 'Enter City']) }}
                    <div class="text-danger d-none" id="company_city_error">City is required.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('company_zipcode', __('Zip/Post Code'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::text('company_zipcode', !empty($settings['company_zipcode']) ? $settings['company_zipcode'] : null, ['class' => 'form-control ', 'placeholder' => 'Enter Zip/Post Code']) }}
                    <div class="text-danger d-none" id="company_zipcode_error">Zip/Post Code is required.</div>
                </div>
            </div>
             <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('company_telephone', __('Telephone'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::text('company_telephone', !empty($settings['company_telephone']) ? $settings['company_telephone'] : null, ['class' => 'form-control ', 'placeholder' => 'Enter Telephone']) }}
                    <div class="text-danger d-none" id="company_telephone_error">Telephone is required.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('company_email_from_name', __('Email (From Name)'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::text('company_email_from_name', !empty($settings['company_email_from_name']) ? $settings['company_email_from_name'] : null, ['class' => 'form-control ', 'placeholder' => 'Enter Email From Name']) }}
                    <div class="text-danger d-none" id="company_email_from_name_error">Email From Name is required.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('registration_number', __('Company Registration Number'), ['class' => 'form-label']) }}
                    {{ Form::text('registration_number', !empty($settings['registration_number']) ? $settings['registration_number'] : null, ['class' => 'form-control ', 'placeholder' => 'Enter Company Registration Number']) }}
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-group">
                    {{ Form::label('company_email', __('System Email'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::text('company_email', !empty($settings['company_email']) ? $settings['company_email'] : null, ['class' => 'form-control ', 'placeholder' => 'Enter System Email']) }}
                    <div class="text-danger d-none" id="company_email_error">System Email is required.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('sdl_number', __('SDL Number'), ['class' => 'form-label']) }}
                    {{ Form::text('sdl_number', !empty($settings['sdl_number']) ? $settings['sdl_number'] : null, ['class' => 'form-control ', 'placeholder' => 'Enter SDL Number']) }}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('tax_number', __('Vat (PAYE) Number'), ['class' => 'form-label']) }}
                    {{ Form::text('tax_number', !empty($settings['tax_number']) ? $settings['tax_number'] : null, ['class' => 'form-control ', 'placeholder' => 'Enter Vat (PAYE) Number']) }}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('uif_number', __('UIF Number'), ['class' => 'form-label']) }}
                    {{ Form::text('uif_number', !empty($settings['uif_number']) ? $settings['uif_number'] : null, ['class' => 'form-control ', 'placeholder' => 'Enter UIF Number Number']) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('company_sic7_category', __('SIC7 Category'), ['class' => 'form-label']) }}
                    {{ Form::select('company_sic7_category', $sic7Categories, !empty($settings['company_sic7_category']) ? $settings['company_sic7_category'] : old('company_sic7_category'), ['class' => 'form-control', 'id' => 'company_sic7_category', 'placeholder' => 'Select SIC7 Category']) }}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('company_sic7_code', __('SIC7 Code'), ['class' => 'form-label']) }}
                    {{ Form::select('company_sic7_code', [], !empty($settings['company_sic7_code']) ? $settings['company_sic7_code'] : old('company_sic7_code'), ['class' => 'form-control', 'id' => 'company_sic7_code', 'placeholder' => 'Select Category First']) }}
                </div>
            </div>
            
            <!-- Accountant Information Section -->
            <div class="col-12 mt-4">
                <h6 class="text-muted">{{ __('Accountant Information') }}</h6>
                <hr>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('accountant_first_name', __('Accountant First Name'), ['class' => 'form-label']) }}
                    {{ Form::text('accountant_first_name', !empty($settings['accountant_first_name']) ? $settings['accountant_first_name'] : null, ['class' => 'form-control ', 'placeholder' => 'Enter Accountant First Name']) }}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('accountant_last_name', __('Accountant Last Name'), ['class' => 'form-label']) }}
                    {{ Form::text('accountant_last_name', !empty($settings['accountant_last_name']) ? $settings['accountant_last_name'] : null, ['class' => 'form-control ', 'placeholder' => 'Enter Accountant Last Name']) }}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('accountant_position', __('Accountant Position'), ['class' => 'form-label']) }}
                    {{ Form::text('accountant_position', !empty($settings['accountant_position']) ? $settings['accountant_position'] : null, ['class' => 'form-control ', 'placeholder' => 'Enter Accountant Position']) }}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('accountant_primary_number', __('Accountant Primary Number'), ['class' => 'form-label']) }}
                    {{ Form::text('accountant_primary_number', !empty($settings['accountant_primary_number']) ? $settings['accountant_primary_number'] : null, ['class' => 'form-control ', 'placeholder' => 'Enter Accountant Primary Number']) }}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('accountant_secondary_number', __('Accountant Secondary Number'), ['class' => 'form-label']) }}
                    {{ Form::text('accountant_secondary_number', !empty($settings['accountant_secondary_number']) ? $settings['accountant_secondary_number'] : null, ['class' => 'form-control ', 'placeholder' => 'Enter Accountant Secondary Number']) }}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('accountant_email', __('Accountant Email'), ['class' => 'form-label']) }}
                    {{ Form::email('accountant_email', !empty($settings['accountant_email']) ? $settings['accountant_email'] : null, ['class' => 'form-control ', 'placeholder' => 'Enter Accountant Email']) }}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('is_sdl_calculate', __('SDL Calculation'), ['class' => 'form-label']) }}
                    <div class="form-check form-switch">
                        {{ Form::hidden('is_sdl_calculate', 0) }}
                        {{ Form::checkbox('is_sdl_calculate', 1, !empty($settings['is_sdl_calculate']) ? $settings['is_sdl_calculate'] : false, ['class' => 'form-check-input', 'id' => 'is_sdl_calculate']) }}
                        <label class="form-check-label" for="is_sdl_calculate">{{ __('Enable') }}</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer text-end">
        <input class="btn btn-print-invoice btn-rc-primary m-r-10" type="submit" value="{{ __('Save Changes') }}">
    </div>
    {{ Form::close() }}
</div>@php
    $active_module = activatedModule();
    $dependency = explode(',', 'Account,Taskly');
@endphp

@if (!empty(array_intersect($dependency, $active_module)))
    <!--Proposal print Setting-->
    @php
        $proposal_template = isset($settings['proposal_template']) ? $settings['proposal_template'] : '';
        $proposal_color = isset($settings['proposal_color']) ? $settings['proposal_color'] : '';
    @endphp
    <div id="proposal-print-sidenav" class="card">
        <div class="card-header">
            <h5>{{ __('Proposal Print Settings') }}</h5>
            <small class="text-muted">{{ __('Edit your Company Proposal details') }}</small>
        </div>
        <div class="bg-none">
            <div class="row company-setting">
                <div class="">
                    <form id="setting-form" method="post" action="{{ route('proposal.template.setting') }}"
                        enctype ="multipart/form-data">
                        @csrf
                        <div class="card-header card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('proposal_prefix', __('Prefix'), ['class' => 'form-label']) }}
                                        {{ Form::text('proposal_prefix', isset($settings['proposal_prefix']) ? $settings['proposal_prefix'] : '#PROP0', ['class' => 'form-control', 'placeholder' => 'Enter Prefix']) }}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        {{ Form::label('proposal_starting_number', __('Starting Number'), ['class' => 'form-label']) }}
                                        {{ Form::number('proposal_starting_number', isset($settings['proposal_starting_number']) ? $settings['proposal_starting_number'] : 1, ['class' => 'form-control', 'placeholder' => 'Enter Starting Number']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        {{ Form::label('proposal_footer_title', __('Footer Title'), ['class' => 'form-label']) }}
                                        {{ Form::text('proposal_footer_title', isset($settings['proposal_footer_title']) ? $settings['proposal_footer_title'] : '', ['class' => 'form-control', 'placeholder' => 'Enter Footer Title']) }}
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        {{ Form::label('proposal_footer_notes', __('Footer Notes'), ['class' => 'form-label']) }}
                                        {{ Form::textarea('proposal_footer_notes', isset($settings['proposal_footer_notes']) ? $settings['proposal_footer_notes'] : '', ['class' => 'form-control', 'rows' => '1', 'placeholder' => 'Enter Footer Notes']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card-header card-body">
                                    <div class="form-group d-flex align-items-center justify-content-between">
                                        {{ Form::label('proposal_shipping_display', __('Shipping Display?'), ['class' => 'form-label']) }}
                                        <div class="text-end form-check form-switch d-inline-block">
                                            <input type="checkbox" class="form-check-input"
                                            name="proposal_shipping_display" id="proposal_shipping_display"
                                            {{ (isset($settings['proposal_shipping_display']) ? $settings['proposal_shipping_display'] : 'off') == 'on' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    <div class="form-group d-flex align-items-center justify-content-between">
                                        {{ Form::label('proposal_qr_display', __('QR Display?'), ['class' => 'form-label']) }}
                                        <div class="text-end form-check form-switch d-inline-block">
                                            <input type="checkbox" class="form-check-input"
                                            name="proposal_qr_display" id="proposal_qr_display"
                                            {{ (isset($settings['proposal_qr_display']) ? $settings['proposal_qr_display'] : 'off') == 'on' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="proposal_template"
                                            class="form-label">{{ __('Template') }}</label>
                                        <select class="form-control" name="proposal_template" id="proposal_template">
                                            @foreach (templateData()['templates'] as $key => $template)
                                                <option value="{{ $key }}"
                                                    {{ $proposal_template == $key ? 'selected' : '' }}>
                                                    {{ $template }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">{{ __('Color Input') }}</label>
                                        <div class="row gutters-xs">
                                            @foreach (templateData()['colors'] as $key => $color)
                                                <div class="col-auto">
                                                    <label class="colorinput">
                                                        <input name="proposal_color" type="radio"
                                                            value="{{ $color }}" class="colorinput-input"
                                                            {{ $proposal_color == $color ? 'checked' : '' }}>
                                                        <span class="colorinput-color"
                                                            style="background: #{{ $color }}"></span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">{{ __('Logo') }}</label>
                                        <div class="choose-files mt-3">
                                            <label for="proposal_logo">
                                                <div class=" bg-primary "> <i
                                                        class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                </div>
                                                <img id="blah12" class="mt-3" src="" width="70%" />
                                                <input type="file" class="form-control file" name="proposal_logo"
                                                    id="proposal_logo" data-filename="proposal_logo_update"
                                                    onchange="document.getElementById('blah12').src = window.URL.createObjectURL(this.files[0])">
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group mt-2 text-end">
                                        <button type="submit" class="btn btn-print-invoice btn-rc-primary m-r-10">{{ __('Save Changes') }}</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                @if (!empty($proposal_template) && !empty($proposal_color))
                                    <iframe id="proposal_frame" class="w-100 h-100" frameborder="0"
                                        src="{{ route('proposal.preview', [$proposal_template, $proposal_color]) }}"></iframe>
                                @else
                                    <iframe id="proposal_frame" class="w-100 h-100" frameborder="0"
                                        src="{{ route('proposal.preview', ['template1', 'fffff']) }}"></iframe>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--Invoice print Setting-->
    @php
        $invoice_template = isset($settings['invoice_template']) ? $settings['invoice_template'] : '';
        $invoice_color = isset($settings['invoice_color']) ? $settings['invoice_color'] : '';
    @endphp
    <div id="invoice-print-sidenav" class="card">
        <div class="card-header">
            <h5>{{ __('Invoice Print Settings') }}</h5>
            <small class="text-muted">{{ __('Edit your Company invoice details') }}</small>
        </div>
        <div class="bg-none">
            <div class="row company-setting">
                <form id="setting-form" method="post" action="{{ route('invoice.template.setting') }}"
                    enctype ="multipart/form-data">
                    @csrf
                    <div class="card-header card-body">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    {{ Form::label('invoice_prefix', __('Prefix'), ['class' => 'form-label']) }}
                                    {{ Form::text('invoice_prefix', isset($settings['invoice_prefix']) ? $settings['invoice_prefix'] : '#INV', ['class' => 'form-control', 'placeholder' => 'Enter Prefix']) }}
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    {{ Form::label('proforma_invoice_prefix', __('Proforma Invoice Prefix'), ['class' => 'form-label']) }}
                                    {{ Form::text('proforma_invoice_prefix', isset($settings['proforma_invoice_prefix']) ? $settings['proforma_invoice_prefix'] : '#PRIN', ['class' => 'form-control', 'placeholder' => 'Enter Prefix']) }}
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    {{ Form::label('invoice_starting_number', __('Starting Number'), ['class' => 'form-label']) }}
                                    {{ Form::number('invoice_starting_number', isset($settings['invoice_starting_number']) ? $settings['invoice_starting_number'] : 1, ['class' => 'form-control', 'placeholder' => 'Enter Invoice Starting Number']) }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('proforma_invoice_starting_number', __('Proforma Invoice Starting Number'), ['class' => 'form-label']) }}
                                    {{ Form::number('proforma_invoice_starting_number', isset($settings['proforma_invoice_starting_number']) ? $settings['proforma_invoice_starting_number'] : 1, ['class' => 'form-control', 'placeholder' => 'Enter Proforma Invoice Starting Number']) }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('invoice_footer_title', __('Footer Title'), ['class' => 'form-label']) }}
                                    {{ Form::text('invoice_footer_title', isset($settings['invoice_footer_title']) ? $settings['invoice_footer_title'] : '', ['class' => 'form-control', 'placeholder' => 'Enter Footer Title']) }}
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    {{ Form::label('invoice_footer_notes', __('Footer Notes'), ['class' => 'form-label']) }}
                                    {{ Form::textarea('invoice_footer_notes', isset($settings['invoice_footer_notes']) ? $settings['invoice_footer_notes'] : '', ['class' => 'form-control', 'rows' => '1', 'placeholder' => 'Enter Footer Notes']) }}
                                </div>
                            </div>


                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card-header card-body">
                                <div class="form-group d-flex align-items-center justify-content-between">
                                    {{ Form::label('invoice_shipping_display', __('Shipping Display?'), ['class' => 'form-label']) }}
                                    <div class="text-end form-check form-switch d-inline-block">
                                        <input type="checkbox" class="form-check-input"
                                        name="invoice_shipping_display" id="invoice_shipping_display"
                                        {{ (isset($settings['invoice_shipping_display']) ? $settings['invoice_shipping_display'] : 'off') == 'on' ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <div class="form-group d-flex align-items-center justify-content-between">
                                    {{ Form::label('invoice_qr_display', __('QR Display?'), ['class' => 'form-label']) }}
                                    <div class="text-end form-check form-switch d-inline-block">
                                        <input type="checkbox" class="form-check-input"
                                        name="invoice_qr_display" id="invoice_qr_display"
                                        {{ (isset($settings['invoice_qr_display']) ? $settings['invoice_qr_display'] : 'off') == 'on' ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="invoice_template"
                                        class="form-label">{{ __('Template') }}</label>
                                    <select class="form-control" name="invoice_template" id="invoice_template">
                                        @foreach (templateData()['templates'] as $key => $template)
                                            <option value="{{ $key }}"
                                                {{ $invoice_template == $key ? 'selected' : '' }}>
                                                {{ $template }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">{{ __('Color Input') }}</label>
                                    <div class="row gutters-xs">
                                        @foreach (templateData()['colors'] as $key => $color)
                                            <div class="col-auto">
                                                <label class="colorinput">
                                                    <input name="invoice_color" type="radio"
                                                        value="{{ $color }}" class="colorinput-input"
                                                        {{ $invoice_color == $color ? 'checked' : '' }}>
                                                    <span class="colorinput-color"
                                                        style="background: #{{ $color }}"></span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">{{ __('Logo') }}</label>
                                    <div class="choose-files mt-3">
                                        <label for="invoice_logo">
                                            <div class=" bg-primary "> <i
                                                    class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                            </div>
                                            <img id="blah6" class="mt-3" src="" width="70%" />
                                            <input type="file" class="form-control file" name="invoice_logo"
                                                id="invoice_logo" data-filename="invoice_logo_update"
                                                onchange="document.getElementById('blah6').src = window.URL.createObjectURL(this.files[0])">
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group mt-2 text-end">
                                    <button type="submit" class="btn btn-print-invoice btn-rc-primary m-r-10">{{ __('Save Changes') }}</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            @if (!empty($invoice_template) && !empty($invoice_color))
                                <iframe id="invoice_frame" class="w-100 h-100" frameborder="0"
                                    src="{{ route('invoice.preview', [$invoice_template, $invoice_color]) }}"></iframe>
                            @else
                                <iframe id="invoice_frame" class="w-100 h-100" frameborder="0"
                                    src="{{ route('invoice.preview', ['template1', 'fffff']) }}"></iframe>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Purchase Print Settings -->

    @php
        $purchase_template = isset($settings['purchase_template']) ? $settings['purchase_template'] : '';
        $purchase_color = isset($settings['purchase_color']) ? $settings['purchase_color'] : '';
    @endphp

    <div id="purchase-print-sidenav" class="card">
        <div class="card-header">
            <h5>{{ __('Purchase Print Settings') }}</h5>
            <small class="text-muted">{{ __('Edit details about your Company Bill') }}</small>
        </div>
        <div class="bg-none">
            <div class="row company-setting">
                <form id="setting-form" method="post" action="{{ route('purchases.template.setting') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="card-header card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('purchase_prefix', __('Prefix'), ['class' => 'form-label']) }}
                                    {{ Form::text('purchase_prefix', isset($settings['purchase_prefix']) && !empty($settings['purchase_prefix']) ? $settings['purchase_prefix'] : '#PUR', ['class' => 'form-control', 'placeholder' => 'Enter Purchase Prefix']) }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {{ Form::label('purchase_footer_title', __('Footer Title'), ['class' => 'form-label']) }}
                                    {{ Form::text('purchase_footer_title', isset($settings['purchase_footer_title']) && !empty($settings['purchase_footer_title']) ? $settings['purchase_footer_title'] : '', ['class' => 'form-control', 'placeholder' => 'Enter Footer Title']) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('purchase_footer_notes', __('Footer Notes'), ['class' => 'form-label']) }}
                                    {{ Form::textarea('purchase_footer_notes', isset($settings['purchase_footer_notes']) && !empty($settings['purchase_footer_notes']) ? $settings['purchase_footer_notes'] : '', ['class' => 'form-control', 'rows' => '1', 'placeholder' => 'Enter Purchase Footer Notes']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card-header card-body">
                                <div class="form-group d-flex align-items-center justify-content-between">
                                    {{ Form::label('purchase_shipping_display', __('Shipping Display?'), ['class' => 'form-label']) }}
                                    <div class="text-end form-check form-switch d-inline-block">
                                        <input type="checkbox" class="form-check-input"
                                        name="purchase_shipping_display" id="purchase_shipping_display"
                                        {{ (isset($settings['purchase_shipping_display']) ? $settings['purchase_shipping_display'] : 'off') == 'on' ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <div class="form-group d-flex align-items-center justify-content-between">
                                    {{ Form::label('purchase_qr_display', __('QR Display?'), ['class' => 'form-label']) }}
                                    <div class="text-end form-check form-switch d-inline-block">
                                        <input type="checkbox" class="form-check-input"
                                        name="purchase_qr_display" id="purchase_qr_display"
                                        {{ (isset($settings['purchase_qr_display']) ? $settings['purchase_qr_display'] : 'off') == 'on' ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="purchase_template"
                                        class="form-label">{{ __('Template') }}</label>
                                    <select class="form-control" name="purchase_template" id="purchase_template">
                                        @foreach (templateData()['templates'] as $key => $template)
                                            <option value="{{ $key }}"
                                                {{ $purchase_template == $key ? 'selected' : '' }}>
                                                {{ $template }}</option>
                                        @endforeach
                                    </select>
                                </div>


                                <div class="form-group">
                                    <label class="form-label">{{ __('Color Input') }}</label>
                                    <div class="row gutters-xs">
                                        @foreach (templateData()['colors'] as $key => $color)
                                            <div class="col-auto">
                                                <label class="colorinput">
                                                    <input name="purchase_color" type="radio"
                                                        value="{{ $color }}" class="colorinput-input"
                                                        {{ $purchase_color == $color ? 'checked' : '' }}>
                                                    <span class="colorinput-color"
                                                        style="background: #{{ $color }}"></span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">{{ __('Logo') }}</label>
                                    <div class="choose-files mt-3">
                                        <label for="purchase_logo">
                                            <div class=" bg-primary "> <i
                                                    class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                            </div>
                                            <img id="blah7" class="mt-3" src="" width="70%" />
                                            <input type="file" class="form-control file" name="purchase_logo"
                                                id="purchase_logo" data-filename="purchase_logo"
                                                onchange="document.getElementById('blah7').src = window.URL.createObjectURL(this.files[0])">
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group mt-2 text-end">
                                    <button type="submit" class="btn btn-print-invoice btn-rc-primary m-r-10">{{ __('Save Changes') }}</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            @if (!empty($purchase_template) && !empty($purchase_color))
                                <iframe id="purchase_frame" class="w-100 h-100" frameborder="0"
                                    src="{{ route('purchases.preview', [$purchase_template, $purchase_color]) }}"></iframe>
                            @else
                                <iframe id="purchase_frame" class="w-100 h-100" frameborder="0"
                                    src="{{ route('purchases.preview', ['template1', 'fffff']) }}"></iframe>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
<script>
    $(document).ready(function() {
        choices();
    });

    function check_theme(color_val) {
        $('input[value="' + color_val + '"]').prop('checked', true);
        $('a[data-value]').removeClass('active_color');
        $('a[data-value="' + color_val + '"]').addClass('active_color');
    }
    var themescolors = document.querySelectorAll(".themes-color > a");
    for (var h = 0; h < themescolors.length; h++) {
        var c = themescolors[h];

        c.addEventListener("click", function(event) {
            var targetElement = event.target;
            if (targetElement.tagName == "SPAN") {
                targetElement = targetElement.parentNode;
            }
            var temp = targetElement.getAttribute("data-value");
            removeClassByPrefix(document.querySelector("body"), "theme-");
            document.querySelector("body").classList.add(temp);
        });
    }

    function removeClassByPrefix(node, prefix) {
        for (let i = 0; i < node.classList.length; i++) {
            let value = node.classList[i];
            if (value.startsWith(prefix)) {
                node.classList.remove(value);
            }
        }
    }
    if ($('#useradd-sidenav').length > 0) {
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300,
        });
    }
</script>
<script>
{{-- Dark mode handler removed - unified handler below --}}
    function cust_theme_bg(params) {
        var custthemebg = document.querySelector("#site_transparent");
        var val = "checked";
        if (val) {
            document.querySelector(".dash-sidebar").classList.add("transprent-bg");
            document
                .querySelector(".dash-header:not(.dash-mob-header)")
                .classList.add("transprent-bg");
        } else {
            document.querySelector(".dash-sidebar").classList.remove("transprent-bg");
            document
                .querySelector(".dash-header:not(.dash-mob-header)")
                .classList.remove("transprent-bg");
        }
    }
    if ($('#site_transparent').length > 0) {
        var custthemebg = document.querySelector("#site_transparent");
        custthemebg.addEventListener("click", function() {
            if (custthemebg.checked) {
                document.querySelector(".dash-sidebar").classList.add("transprent-bg");
                document
                    .querySelector(".dash-header:not(.dash-mob-header)")
                    .classList.add("transprent-bg");
            } else {
                document.querySelector(".dash-sidebar").classList.remove("transprent-bg");
                document
                    .querySelector(".dash-header:not(.dash-mob-header)")
                    .classList.remove("transprent-bg");
            }
        });
    }
</script>
{{-- VAT & GST Number --}}
<script>
    $(document).on('change', '#sdl_tax_uif_number_switch', function() {
        if ($(this).is(':checked')) {
            $('.tax_type_div').removeClass('d-none');

        } else {
            $('.tax_type_div').addClass('d-none');

        }
    });
</script>
<script>
    $(document).on("change", "select[name='proposal_template'], input[name='proposal_color']", function() {
        var template = $("select[name='proposal_template']").val();
        var color = $("input[name='proposal_color']:checked").val();
        $('#proposal_frame').attr('src', '{{ url('/proposal/preview') }}/' + template + '/' + color);
    });
</script>
<script>
    $(document).on("change", "select[name='invoice_template'], input[name='invoice_color']", function() {
        var template = $("select[name='invoice_template']").val();
        var color = $("input[name='invoice_color']:checked").val();
        $('#invoice_frame').attr('src', '{{ url('/invoices/preview') }}/' + template + '/' + color);
    });
</script>

<script>
    $(document).on("change", "select[name='purchase_template'], input[name='purchase_color']", function() {
        var template = $("select[name='purchase_template']").val();
        var color = $("input[name='purchase_color']:checked").val();
        $('#purchase_frame').attr('src', '{{ url('/purchases/preview') }}/' + template + '/' + color);
    });
</script>
<script>
$(document).ready(function() {
    $('select[name="company_country"]').on('change', function() {
        var countryId = $(this).val();
        var stateSelect = $('select[name="company_state"]');
        
        if (countryId) {
            $.ajax({
                url: '{{ route("country.provinces.list", ":id") }}'.replace(':id', countryId),
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    stateSelect.empty();
                    stateSelect.append('<option value="">Select State</option>');
                    $.each(data, function(key, value) {
                        stateSelect.append('<option value="' + key + '">' + value + '</option>');
                    });

                    const selectedState = '{{ !empty($settings["company_state"]) ? $settings["company_state"] : old("company_state") }}';
                    if (selectedState) {
                        stateSelect.val(selectedState);
                    }
                }
            });
        } else {
            stateSelect.empty();
            stateSelect.append('<option value="">Select State</option>');
        }
    });

    @if (!empty($settings['company_country']) || old('company_country'))
        $('select[name="company_country"]').trigger('change');
    @endif

    $('select[name="company_sic7_category"]').on('change', function() {
        var category = $(this).val();
        var codeSelect = $('select[name="company_sic7_code"]');
        
        if (category) {
            $.ajax({
                url: '{{ route("get.sic7.codes.by.category") }}',
                type: 'GET',
                data: {
                    category: category
                },
                dataType: 'json',
                success: function(data) {
                    codeSelect.empty();
                    codeSelect.append('<option value="">Select SIC7 Code</option>');
                    if (data.success && data.codes) {
                        $.each(data.codes, function(key, code) {
                            codeSelect.append('<option value="' + code.id + '">' + code.display_text + '</option>');
                        });
                    }

                    // Set selected value if exists
                    const selectedCode = '{{ !empty($settings["company_sic7_code"]) ? $settings["company_sic7_code"] : old("company_sic7_code") }}';
                    if (selectedCode) {
                        codeSelect.val(selectedCode);
                    }
                },
                error: function() {
                    codeSelect.empty();
                    codeSelect.append('<option value="">Error loading codes</option>');
                }
            });
        } else {
            codeSelect.empty();
            codeSelect.append('<option value="">Select Category First</option>');
        }
    });

    // Trigger SIC7 category change on page load if category is selected
    @if (!empty($settings['company_sic7_category']) || old('company_sic7_category'))
        $('select[name="company_sic7_category"]').trigger('change');
    @endif
});
</script>
{{-- theme color --}}
<script>
    $('.colorPicker').on('click', function(e) {
        $('body').removeClass('custom-color');
        if (/^theme-\d+$/) {
            $('body').removeClassRegex(/^theme-\d+$/);
        }
        $('body').addClass('custom-color');
        $('.themes-color-change').removeClass('active_color');
        $(this).addClass('active_color');
        const input = document.getElementById("color-picker");
        setColor();
        input.addEventListener("input", setColor);

        function setColor() {
            document.documentElement.style.setProperty('--color-customColor', input.value);
        }

        $(`input[name='color_flag`).val('true');
    });

    $('.themes-color-change').on('click', function() {

        $(`input[name='color_flag`).val('false');

        var color_val = $(this).data('value');
        $('body').removeClass('custom-color');
        if (/^theme-\d+$/) {
            $('body').removeClassRegex(/^theme-\d+$/);
        }
        $('body').addClass(color_val);
        $('.theme-color').prop('checked', false);
        $('.themes-color-change').removeClass('active_color');
        $('.colorPicker').removeClass('active_color');
        $(this).addClass('active_color');
        $(`input[value=${color_val}]`).prop('checked', true);
    });

    $.fn.removeClassRegex = function(regex) {
        return $(this).removeClass(function(index, classes) {
            return classes.split(/\s+/).filter(function(c) {
                return regex.test(c);
            }).join(' ');
        });
    };
</script>
<script>
    $(document).ready(function() {
        $('#company-settings-form').on('submit', function(e) {
            let isValid = true;
            
            $('div[id$="_error"]').addClass('d-none');
            
            const companyName = $('input[name="company_name"]').val().trim();
            if (companyName === '') {
                $('#company_name_error').removeClass('d-none');
                isValid = false;
            }
            

            const companyAddress = $('input[name="company_address"]').val().trim();
            if (companyAddress === '') {
                $('#company_address_error').removeClass('d-none');
                isValid = false;
            }
            
            const companyCountry = $('select[name="company_country"]').val();
            if (!companyCountry || companyCountry === '') {
                $('#company_country_error').removeClass('d-none');
                isValid = false;
            }
            
            const companyState = $('select[name="company_state"]').val();
            if (!companyState || companyState === '') {
                $('#company_state_error').removeClass('d-none');
                isValid = false;
            }
            
            const companyCity = $('input[name="company_city"]').val().trim();
            if (companyCity === '') {
                $('#company_city_error').removeClass('d-none');
                isValid = false;
            }
            
            const companyZipcode = $('input[name="company_zipcode"]').val().trim();
            if (companyZipcode === '') {
                $('#company_zipcode_error').removeClass('d-none');
                isValid = false;
            }
            
            const companyTelephone = $('input[name="company_telephone"]').val().trim();
            if (companyTelephone === '') {
                $('#company_telephone_error').removeClass('d-none');
                isValid = false;
            }
            
            const companyEmail = $('input[name="company_email"]').val().trim();
            if (companyEmail === '') {
                $('#company_email_error').removeClass('d-none');
                isValid = false;
            } else if (!isValidEmail(companyEmail)) {
                $('#company_email_error').text('Please enter a valid email address.').removeClass('d-none');
                isValid = false;
            }
            
            const companyEmailFromName = $('input[name="company_email_from_name"]').val().trim();
            if (companyEmailFromName === '') {
                $('#company_email_from_name_error').removeClass('d-none');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: $('div[id$="_error"]:not(.d-none)').first().offset().top - 100
                }, 500);
            }
        });
        
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        $('input[name="company_name"]').on('input', function() {
            if ($(this).val().trim() !== '') {
                $('#company_name_error').addClass('d-none');
            }
        });
        
        $('input[name="company_address"]').on('input', function() {
            if ($(this).val().trim() !== '') {
                $('#company_address_error').addClass('d-none');
            }
        });
        
        $('select[name="company_country"]').on('change', function() {
            if ($(this).val() !== '') {
                $('#company_country_error').addClass('d-none');
            }
        });
        
        $('select[name="company_state"]').on('change', function() {
            if ($(this).val() !== '') {
                $('#company_state_error').addClass('d-none');
            }
        });
        
        $('input[name="company_city"]').on('input', function() {
            if ($(this).val().trim() !== '') {
                $('#company_city_error').addClass('d-none');
            }
        });
        
        $('input[name="company_zipcode"]').on('input', function() {
            if ($(this).val().trim() !== '') {
                $('#company_zipcode_error').addClass('d-none');
            }
        });
        
        $('input[name="company_telephone"]').on('input', function() {
            if ($(this).val().trim() !== '') {
                $('#company_telephone_error').addClass('d-none');
            }
        });
        
        $('input[name="company_email"]').on('input', function() {
            const email = $(this).val().trim();
            if (email !== '') {
                if (isValidEmail(email)) {
                    $('#company_email_error').addClass('d-none');
                } else {
                    $('#company_email_error').text('Please enter a valid email address.').removeClass('d-none');
                }
            }
        });
        
        $('input[name="company_email_from_name"]').on('input', function() {
            if ($(this).val().trim() !== '') {
                $('#company_email_from_name_error').addClass('d-none');
            }
        });
    });
</script>
<script>
    function allowOnlyNumbers(event) {
        const char = String.fromCharCode(event.which);
        const regex = /^[0-9]+$/;
        if (!regex.test(char)) {
            event.preventDefault();
        }
    
</script>
{{-- Dark Mode & RTL Layout Toggles --}}
<script>
    $(document).ready(function() {
        var mainStyleLink = document.getElementById('main-style-link');
        var logoLight = "{{ getLogoUrl($settings['logo_light'] ?? null, 'light') ?? getLogoFallback('light') }}";
        var logoDark = "{{ getLogoUrl($settings['logo_dark'] ?? null, 'dark') ?? getLogoFallback('dark') }}";
        var logoEl = document.querySelector(".m-header > .b-brand > .logo-lg");

        // Dark Mode Toggle
        $('#cust-darklayout').on('change', function() {
            if (this.checked) {
                if (logoDark && logoEl) logoEl.setAttribute("src", logoDark);
                if (mainStyleLink) {
                    mainStyleLink.href = '{{ asset("assets/css/style-dark.css") }}';
                }
                $('body').addClass('dark');
            } else {
                if (logoLight && logoEl) logoEl.setAttribute("src", logoLight);
                if (mainStyleLink) {
                    mainStyleLink.href = '{{ asset("assets/css/style.css") }}';
                }
                $('body').removeClass('dark');
            }
        });

        // RTL Layout Toggle
        $('#site_rtl').on('change', function() {
            var isDark = $('#cust-darklayout').is(':checked');
            var rtlLink = document.getElementById('rtl-style-link');

            if (this.checked) {
                // Enable RTL: load style-rtl.css
                if (!rtlLink) {
                    rtlLink = document.createElement('link');
                    rtlLink.rel = 'stylesheet';
                    rtlLink.id = 'rtl-style-link';
                    rtlLink.href = '{{ asset("assets/css/style-rtl.css") }}';
                    mainStyleLink.parentNode.insertBefore(rtlLink, mainStyleLink);
                } else {
                    rtlLink.href = '{{ asset("assets/css/style-rtl.css") }}';
                }
                document.documentElement.setAttribute('dir', 'rtl');
            } else {
                // Disable RTL: remove style-rtl.css, restore style.css if not dark
                if (rtlLink) {
                    rtlLink.remove();
                }
                if (!isDark && mainStyleLink) {
                    mainStyleLink.href = '{{ asset("assets/css/style.css") }}';
                }
                document.documentElement.setAttribute('dir', '');
            }
        });
    });
</script>