 <!--Brand Settings-->
 <div id="site-settings" class="">
     {{ Form::open(['route' => ['super.admin.settings.save'], 'enctype' => 'multipart/form-data', 'id' => 'setting-form']) }}
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
                                             data-filename="logo_dark"
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
                                             id="logo_light" data-filename="logo_light"
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
                                             data-filename="favicon"
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
                 <div class="col-sm-3 col-12">
                     <div class="form-check form-switch mt-2">
                         <input type="checkbox" class="form-check-input" id="signup" name="signup"
                             {{ isset($settings['signup']) && $settings['signup'] == 'on' ? 'checked' : '' }} />
                         <label class="form-check-label f-w-600 pl-1" for="signup">{{ __('Enable Signup') }}</label>

                     </div>
                 </div>
                 <div class="col-auto ">
                     <div class="form-check form-switch mt-2">
                         <input type="checkbox" class="form-check-input" id="email_verification"
                             name="email_verification"
                             {{ isset($settings['email_verification']) && $settings['email_verification'] == 'on' ? 'checked' : '' }} />
                         <label class="form-check-label f-w-600 pl-1"
                             for="email_verification">{{ __('Email Verification') }}</label>

                     </div>
                 </div>
                 <div class="row mt-3">
                     <h4 class="small-title">{{ __('Theme Customizer') }}</h4>
                     <div class="setting-card setting-logo-box p-3">
                         <div class="row">
                             <div class="col-xxl-2 col-md-4 col-sm-6 col-12">
                                 <h6 class="text-md">
                                     <i class="ti ti-credit-card me-2 h5"></i>{{ __('Primary Color Settings') }}
                                 </h6>

                                 <hr class="my-2" />
                                 <div class="color-wrp">
                                     <div class="theme-color themes-color">
                                         <a href="#!"
                                             class="themes-color-change {{ isset($settings['color']) && $settings['color'] == 'theme-1' ? 'active_color' : '' }}"
                                             data-value="theme-1"></a>
                                         <input type="radio" class="theme_color d-none" name="color"
                                             value="theme-1" {{ isset($settings['color']) && $settings['color'] == 'theme-1' ? 'checked' : '' }}>
                                         <a href="#!"
                                             class="themes-color-change {{ isset($settings['color']) && $settings['color'] == 'theme-2' ? 'active_color' : '' }}"
                                             data-value="theme-2"></a>
                                         <input type="radio" class="theme_color d-none" name="color"
                                             value="theme-2" {{ isset($settings['color']) && $settings['color'] == 'theme-2' ? 'checked' : '' }}>
                                         <a href="#!"
                                             class="themes-color-change {{ isset($settings['color']) && $settings['color'] == 'theme-3' ? 'active_color' : '' }}"
                                             data-value="theme-3"></a>
                                         <input type="radio" class="theme_color d-none" name="color"
                                             value="theme-3" {{ isset($settings['color']) && $settings['color'] == 'theme-3' ? 'checked' : '' }}>

                                         <a href="#!"
                                             class="themes-color-change {{ isset($settings['color']) && $settings['color'] == 'theme-4' ? 'active_color' : '' }}"
                                             data-value="theme-4"></a>
                                         <input type="radio" class="theme_color d-none" name="color"
                                             value="theme-4" {{ isset($settings['color']) && $settings['color'] == 'theme-4' ? 'checked' : '' }}>
                                         <a href="#!"
                                             class="themes-color-change {{ isset($settings['color']) && $settings['color'] == 'theme-5' ? 'active_color' : '' }}"
                                             data-value="theme-5"></a>
                                         <input type="radio" class="theme_color d-none" name="color"
                                             value="theme-5" {{ isset($settings['color']) && $settings['color'] == 'theme-5' ? 'checked' : '' }}>

                                         <a href="#!"
                                             class="themes-color-change {{ isset($settings['color']) && $settings['color'] == 'theme-6' ? 'active_color' : '' }}"
                                             data-value="theme-6"></a>
                                         <input type="radio" class="theme_color d-none" name="color"
                                             value="theme-6" {{ isset($settings['color']) && $settings['color'] == 'theme-6' ? 'checked' : '' }}>

                                         <a href="#!"
                                             class="themes-color-change {{ isset($settings['color']) && $settings['color'] == 'theme-7' ? 'active_color' : '' }}"
                                             data-value="theme-7"></a>
                                         <input type="radio" class="theme_color d-none" name="color"
                                             value="theme-7" {{ isset($settings['color']) && $settings['color'] == 'theme-7' ? 'checked' : '' }}>
                                         <a href="#!"
                                             class="themes-color-change {{ isset($settings['color']) && $settings['color'] == 'theme-8' ? 'active_color' : '' }}"
                                             data-value="theme-8"></a>
                                         <input type="radio" class="theme_color d-none" name="color"
                                             value="theme-8" {{ isset($settings['color']) && $settings['color'] == 'theme-8' ? 'checked' : '' }}>
                                         <a href="#!"
                                             class="themes-color-change {{ isset($settings['color']) && $settings['color'] == 'theme-9' ? 'active_color' : '' }}"
                                             data-value="theme-9"></a>
                                         <input type="radio" class="theme_color d-none" name="color"
                                             value="theme-9" {{ isset($settings['color']) && $settings['color'] == 'theme-9' ? 'checked' : '' }}>

                                         <a href="#!"
                                             class="themes-color-change {{ isset($settings['color']) && $settings['color'] == 'theme-10' ? 'active_color' : '' }}"
                                             data-value="theme-10"></a>
                                         <input type="radio" class="theme_color d-none" name="color"
                                             value="theme-10" {{ isset($settings['color']) && $settings['color'] == 'theme-10' ? 'checked' : '' }}>
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
                                     <i class="ti ti-layout-sidebar me-2 h5"></i> {{ __('Sidebar Settings') }}
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
             {{ Form::open(['route' => ['super.admin.system.setting.store'], 'id' => 'setting-system-form']) }}
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
                                     {{ Str::ucfirst($language) }}
                                 </option>
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
                                 <option value="d-m-Y" @if (isset($settings['site_date_format']) && $settings['site_date_format']=='d-m-Y' ) selected="selected" @endif>
                                     DD-MM-YYYY</option>
                                 <option value="m-d-Y" @if (isset($settings['site_date_format']) && $settings['site_date_format']=='m-d-Y' ) selected="selected" @endif>
                                     MM-DD-YYYY</option>
                                 <option value="Y-m-d" @if (isset($settings['site_date_format']) && $settings['site_date_format']=='Y-m-d' ) selected="selected" @endif>
                                     YYYY-MM-DD</option>
                                 <option value="M d, Y" @if (isset($settings['site_date_format']) && $settings['site_date_format']=='M d, Y' ) selected="selected" @endif>
                                     {{ __('Month Day, Year') }} (Feb 21, 2026)</option>
                             </select>
                         </div>
                     </div>
                     <div class="col-6">
                         <div class="form-group">
                             <label for="site_time_format" class="form-label">{{ __('Time Format') }}</label>
                             <select type="text" name="site_time_format" class="form-control selectric"
                                 id="site_time_format">
                                 <option value="g:i A" @if (isset($settings['site_time_format']) && $settings['site_time_format']=='g:i A' ) selected="selected" @endif>
                                     10:30 PM</option>
                                 <option value="H:i" @if (isset($settings['site_time_format']) && $settings['site_time_format']=='H:i' ) selected="selected" @endif>
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

 {{-- Cookie settings --}}
 <div class="card" id="cookie-sidenav">
     {{ Form::open(['route' => ['cookie.setting.store'], 'method' => 'post']) }}
     <div class="card-header">
         <div class="row">
             <div class="col-lg-10 col-md-10 col-sm-10">
                 <h5 class="">{{ __('Cookie Settings') }}</h5>
             </div>
             <div class="col-lg-2 col-md-2 col-sm-2 text-end">
                 <div class="form-check form-switch custom-switch-v1 float-end">
                     <input type="checkbox" name="enable_cookie" class="form-check-input input-primary"
                         id="enable_cookie"
                         {{ (isset($settings['enable_cookie']) ? $settings['enable_cookie'] : 'off') == 'on' ? ' checked ' : '' }}>
                     <label class="form-check-label" for="enable_cookie"></label>
                 </div>
             </div>
         </div>
     </div>
     <div class="card-body">
         <div class="row ">
             <div class="col-md-6">
                 <div class="form-check form-switch custom-switch-v1" id="cookie_log">
                     <input type="checkbox" name="cookie_logging"
                         class="form-check-input input-primary cookie_setting" id="cookie_logging"
                         {{ (isset($settings['cookie_logging']) ? $settings['cookie_logging'] : 'off') == 'on' ? ' checked ' : '' }}>
                     <label class="form-check-label" for="cookie_logging">{{ __('Enable logging') }}</label>
                     <small
                         class="text-danger">{{ __('After enabling logging, user cookie data will be stored in CSV file.') }}</small>
                 </div>
                 <div class="form-group">
                     {{ Form::label('cookie_title', __('Cookie Title'), ['class' => 'form-label']) }}
                     {{ Form::text('cookie_title', !empty($settings['cookie_title']) ? $settings['cookie_title'] : null, ['class' => 'form-control cookie_setting']) }}
                 </div>
                 <div class="form-group ">
                     {{ Form::label('cookie_description', __('Cookie Description'), ['class' => ' form-label']) }}
                     {!! Form::textarea(
                     'cookie_description',
                     !empty($settings['cookie_description']) ? $settings['cookie_description'] : null,
                     ['class' => 'form-control cookie_setting', 'rows' => '3'],
                     ) !!}
                 </div>
             </div>
             <div class="col-md-6">
                 <div class="form-check form-switch custom-switch-v1 ">
                     <input type="checkbox" name="necessary_cookies"
                         class="form-check-input input-primary cookie_setting" id="necessary_cookies" checked
                         onclick="return false">
                     <label class="form-check-label"
                         for="necessary_cookies">{{ __('Strictly necessary cookies') }}</label>
                 </div>
                 <div class="form-group ">
                     {{ Form::label('strictly_cookie_title', __(' Strictly Cookie Title'), ['class' => 'form-label']) }}
                     {{ Form::text('strictly_cookie_title', !empty($settings['strictly_cookie_title']) ? $settings['strictly_cookie_title'] : null, ['class' => 'form-control cookie_setting']) }}
                 </div>
                 <div class="form-group ">
                     {{ Form::label('strictly_cookie_description', __('Strictly Cookie Description'), ['class' => ' form-label']) }}
                     {!! Form::textarea(
                     'strictly_cookie_description',
                     !empty($settings['strictly_cookie_description']) ? $settings['strictly_cookie_description'] : null,
                     ['class' => 'form-control cookie_setting ', 'rows' => '3'],
                     ) !!}
                 </div>
             </div>
             <div class="col-12">
                 <h5>{{ __('More Information') }}</h5>
             </div>
             <div class="col-md-6">
                 <div class="form-group">
                     {{ Form::label('more_information_description', __('Contact Us Description'), ['class' => 'form-label']) }}
                     {{ Form::text('more_information_description', !empty($settings['more_information_description']) ? $settings['more_information_description'] : null, ['class' => 'form-control cookie_setting']) }}
                 </div>
             </div>
             <div class="col-md-6">
                 <div class="form-group ">
                     {{ Form::label('contactus_url', __('Contact Us URL'), ['class' => 'form-label']) }}
                     {{ Form::text('contactus_url', !empty($settings['contactus_url']) ? $settings['contactus_url'] : null, ['class' => 'form-control cookie_setting']) }}
                 </div>
             </div>
         </div>
     </div>
     <div class="card-footer">
         <div class="row">
             @if ((isset($settings['cookie_logging']) ? $settings['cookie_logging'] : 'off') == 'on')
             @if (checkFile('uploads/sample/cookie_data.csv'))
             <div class="col-6">
                 <label for="file" class="form-label">{{ __('Download cookie accepted data') }}</label>
                 <a href="{{ asset('uploads/sample/cookie_data.csv') }}" class="btn btn-rc-primary mr-3">
                     <i class="ti ti-download"></i>
                 </a>
             </div>
             @endif
             @endif
             <div class="col-6 text-end ">
                 <input class="btn btn-print-invoice btn-rc-primary" type="submit" value="{{ __('Save Changes') }}">
             </div>
         </div>
     </div>
     {{ Form::close() }}
 </div>

 {{-- SEO settings --}}

 <div id="seo-sidenav" class="card">
     <div class="card-header">
         <div class="row">
             <div class="col-lg-10 col-md-10 col-sm-10">
                 <h5>{{ __('SEO Settings') }}</h5>
             </div>
         </div>
     </div>
     {{ Form::open(['url' => route('seo.setting.save'), 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
     @csrf
     <div class="card-body">
         <div class="row">
             <div class="col-md-7">
                 <div class="form-group">
                     {{ Form::label('meta_title', __('Meta Title'), ['class' => 'form-label']) }}
                     {{ Form::text('meta_title', !empty($settings['meta_title']) ? $settings['meta_title'] : null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => 'Meta Title']) }}
                 </div>
                 <div class="form-group">
                     {{ Form::label('meta_keywords', __('Meta Keywords'), ['class' => 'form-label']) }}
                     {{ Form::textarea('meta_keywords', !empty($settings['meta_keywords']) ? $settings['meta_keywords'] : null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => 'Meta Keywords', 'rows' => 2]) }}
                 </div>
                 <div class="form-group">
                     {{ Form::label('meta_description', __('Meta Description'), ['class' => 'form-label']) }}
                     {{ Form::textarea('meta_description', !empty($settings['meta_description']) ? $settings['meta_description'] : null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => 'Meta Description', 'rows' => 3]) }}
                 </div>
             </div>
             <div class="col-md-5">
                 <div class="form-group mb-0">
                     {{ Form::label('Meta Image', __('Meta Image'), ['class' => 'form-label']) }}
                 </div>
                 <div class="setting-card">
                     <div class="logo-content">
                         <img id="image2"
                             src="{{ getFile(!empty($settings['meta_image']) ? (checkFile($settings['meta_image']) ? $settings['meta_image'] : 'uploads/meta/meta_image.png') : 'uploads/meta/meta_image.png') }}{{ '?' . time() }}"
                             class="img_setting seo_image">
                     </div>
                     <div class="choose-files mt-4">
                         <label for="meta_image">
                             <div class="bg-primary company_favicon_update"> <i
                                     class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                             </div>
                             <input type="file" class="form-control file"
                                 accept="image/png, image/gif, image/jpeg,image/jpg" id="meta_image"
                                 name="meta_image"
                                 onchange="document.getElementById('image2').src = window.URL.createObjectURL(this.files[0])"
                                 data-filename="meta_image">
                         </label>
                     </div>
                     @error('meta_image')
                     <div class="row">
                         <span class="invalid-logo" role="alert">
                             <strong class="text-danger">{{ $message }}</strong>
                         </span>
                     </div>
                     @enderror
                 </div>
             </div>
         </div>
     </div>
     <div class="card-footer text-end">
         <input class="btn btn-print-invoice btn-rc-primary m-r-10" type="submit" value="{{ __('Save Changes') }}">
     </div>
     {{ Form::close() }}
 </div>

 
 <script>
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

     function removeClassByPrefix(node, prefix) {
         for (let i = 0; i < node.classList.length; i++) {
             let value = node.classList[i];
             if (value.startsWith(prefix)) {
                 node.classList.remove(value);
             }
         }
     }
 </script>

 {{-- cookie setting --}}
 @if (isset($settings['enable_cookie']) && $settings['enable_cookie'] != 'on')
     <script>
         $(document).ready(function() {
             $('.cookie_setting').attr("disabled", "disabled");
         });
     </script>
 @endif
 <script>
     $(document).on('click', '#enable_cookie', function() {
         if ($('#enable_cookie').prop('checked')) {
             $(".cookie_setting").removeAttr("disabled");
         } else {
             $('.cookie_setting').attr("disabled", "disabled");
         }
     });
 </script>
 <script>
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

 {{-- Dark Mode & RTL Layout Toggles --}}