<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Jobs\RecordLoginDetail;
use App\Models\User;
use App\Models\WorkSpace;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Nwidart\Modules\Facades\Module;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function __construct()
    {
        if(!file_exists(storage_path() . "/installed"))
        {
            header('location:install');
            die;
        }
        $admin_settings = getAdminAllSetting();
        if(moduleIsActive('GoogleCaptcha') && (isset($admin_settings['google_recaptcha_is_on']) ? $admin_settings['google_recaptcha_is_on'] : 'off') == 'on' )
        {
            config(['captcha.secret' => isset($admin_settings['google_recaptcha_secret']) ? $admin_settings['google_recaptcha_secret'] : '']);
            config(['captcha.sitekey' => isset($admin_settings['google_recaptcha_key']) ? $admin_settings['google_recaptcha_key'] : '']);
        }
        // $this->middleware('guest')->except('logout');
    }
    public function create($lang = '')
    {
        if(Auth::check())
        {
            if(Auth::user()->type == 'super admin')
            {
                return redirect('/home');
            } else {
                return redirect('/dashboard/account');
            }
        }
        if($lang == '')
        {
            $lang = getActiveLanguage();
        }
        else
        {
            $lang = array_key_exists($lang, languages()) ? $lang : 'en';
            session(['locale' => $lang]);
        }
        \App::setLocale($lang);
        return view('auth.login',compact('lang'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $redirect = false;
        
        $request->authenticate();

        $request->session()->regenerate();

        // Record login details asynchronously (IP geolocation + browser detection)
        RecordLoginDetail::dispatch(
            userId: Auth::id(),
            ip: $request->ip(),
            userType: Auth::user()->type,
            createdBy: creatorId(),
            workspaceId: getActiveWorkspace(),
            userAgent: $request->userAgent(),
            acceptLanguage: $request->header('Accept-Language'),
            httpReferer: $request->header('Referer'),
        );

        // custom domain code
        if(Auth::user()->type != 'super admin')
        {
            $uri = url()->full();
            $segments = explode('/', str_replace(''.url('').'', '', $uri));
            $segments = $segments[1] ?? null;

            $local = parse_url(config('app.url'))['host'];
            // Get the request host
            $remote = request()->getHost();
            if($local != $remote)
            {
                $remote = str_replace('www.', '', $remote);
                $workSpace = WorkSpace::where('domain',$remote)->orwhere('subdomain',$remote)->where('created_by',creatorId())->first();
                if($workSpace && ($workSpace->enable_domain == 'on'))
                {
                    $redirect = true;
                    $user = User::find(Auth::user()->id);
                    $user->active_workspace = $workSpace->id;
                    $user->save();
                }
            }
        }

         // Update wizard - Check for pending migrations
         if(Auth::user()->type == 'super admin')
         {
            $ranMigrations = DB::table('migrations')->pluck('migration');
            // $modules = Module::all();
            $modules = Module::getByStatus(1);

            $migrationFiles = collect(File::glob(database_path('migrations/*.php')))
            ->map(function ($path) {
                return File::name($path);
            });
            foreach ($modules as $key => $module) {
                // Get the module directorie in your project
                $directory = "Modules/".$module->getName()."/Database/Migrations";

                $files = collect(File::glob("{$directory}/*.php"))
                    ->map(function ($path) {
                        return File::name($path);
                    });
                $migrationFiles = $migrationFiles->merge($files);
            }
            // Calculate the pending migrations by diffing the two lists
            $pendingMigrations = $migrationFiles->diff($ranMigrations);
            if(count($pendingMigrations) > 0)
            {
                // RC ClearPay: Redirect to dashboard with warning instead of non-existent updater
                return redirect()->route('home')->with('warning', __('There are pending database migrations. Please run: php artisan migrate'));
            }
        }
        
        // Subscription system removed - no plan expiration checks
        // All users have unlimited access

        if($redirect)
        {
            return redirect()->away('http://'.$remote.'/dashboard');
        }
        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
         Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
