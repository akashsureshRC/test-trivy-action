<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class SetLang
{
    /**
     * Handle an incoming request.
     *
     * Sets the application locale based on (in priority order):
     * 1. Session value (set when user switches language)
     * 2. Authenticated user's saved language preference
     * 3. Admin default language setting
     * 4. Fallback to 'en'
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Session takes priority (allows guests to switch language too)
        $locale = session('locale');

        // Fall back to authenticated user's saved preference
        if (empty($locale) && Auth::check() && !empty(Auth::user()->lang)) {
            $locale = Auth::user()->lang;
        }

        // Fall back to company-level default for non-super-admin users
        if (empty($locale) && Auth::check() && Auth::user()->type != 'super admin') {
            try {
                $company_settings = getCompanyAllSetting();
                if (!empty($company_settings['defult_language'])) {
                    $locale = $company_settings['defult_language'];
                }
            } catch (\Exception $e) {
                // Ignore — fall through to admin default
            }
        }

        // Fall back to admin default or 'en'
        if (empty($locale)) {
            $locale = getActiveLanguage();
        }

        // Validate the locale exists in available languages
        $available = languages();
        if (!array_key_exists($locale, $available)) {
            $locale = config('app.locale', 'en');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
