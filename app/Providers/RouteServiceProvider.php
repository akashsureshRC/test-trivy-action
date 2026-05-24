<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard/account';

    protected $namespace = 'App\Http\Controllers';

    /**
     * The HRM controller namespace (merged from module)
     *
     * @var string
     */
    protected $hrmNamespace = 'App\Http\Controllers\Hrm';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            // Main API routes
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Main Web routes
            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));

            // HRM Web routes (merged from module)
            Route::middleware('web')
                ->namespace($this->hrmNamespace)
                ->group(base_path('routes/hrm.php'));

            // HRM API routes (merged from module)
            if (file_exists(base_path('routes/hrm-api.php'))) {
                Route::prefix('api')
                    ->middleware('api')
                    ->namespace($this->hrmNamespace)
                    ->group(base_path('routes/hrm-api.php'));
            }

            // ESS (Employee Self-Service) routes
            Route::namespace($this->hrmNamespace . '\Ess')
                ->group(base_path('routes/ess.php'));

            // ESS Mobile API routes
            if (file_exists(base_path('routes/ess-api.php'))) {
                Route::prefix('api')
                    ->middleware('api')
                    ->group(base_path('routes/ess-api.php'));
            }
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('ess-otp-send', function (Request $request) {
            $email = strtolower((string) $request->input('email', ''));

            return [
                Limit::perMinute(3)->by('ess-otp-send:ip:' . $request->ip()),
                Limit::perHour(10)->by('ess-otp-send:email:' . ($email !== '' ? $email : $request->ip())),
            ];
        });

        RateLimiter::for('ess-otp-verify', function (Request $request) {
            $email = strtolower((string) $request->input('email', ''));

            return [
                Limit::perMinute(6)->by('ess-otp-verify:ip:' . $request->ip()),
                Limit::perHour(30)->by('ess-otp-verify:email:' . ($email !== '' ? $email : $request->ip())),
            ];
        });

        RateLimiter::for('ess-otp-reset', function (Request $request) {
            $email = strtolower((string) $request->input('email', ''));

            return [
                Limit::perMinute(3)->by('ess-otp-reset:ip:' . $request->ip()),
                Limit::perHour(10)->by('ess-otp-reset:email:' . ($email !== '' ? $email : $request->ip())),
            ];
        });
    }
}
