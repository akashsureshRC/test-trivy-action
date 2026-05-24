<?php

namespace App\Http\Controllers\Auth;

use App\Events\DefaultData;
use App\Events\CreateUser;
use App\Events\GivePermissionToRole;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkSpace;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public $admin_settings;

    public function setting(){
        $this->admin_settings = getAdminAllSetting();

    }
    public function __construct()
    {
        $this->setting();

        if(!file_exists(storage_path() . "/installed"))
        {
            header('location:install');
            die;
        }
        if(moduleIsActive('GoogleCaptcha') && (isset($this->admin_settings['google_recaptcha_is_on']) ? $this->admin_settings['google_recaptcha_is_on'] : 'off') == 'on' )
        {
            config(['captcha.secret' => isset($this->admin_settings['google_recaptcha_secret']) ? $this->admin_settings['google_recaptcha_secret'] : '']);
            config(['captcha.sitekey' => isset($this->admin_settings['google_recaptcha_key']) ? $this->admin_settings['google_recaptcha_key'] : '']);
        }
        // $this->middleware('guest')->except('logout');
    }
    public function create(Request $request,$lang = '')
    {
        if (empty( $this->admin_settings['signup']) ||  (isset($this->admin_settings['signup']) ? $this->admin_settings['signup'] : 'off') == "on")
        {
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

            return view('auth.register',compact('lang'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'workspace' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'password_confirmation' => 'required'

        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $user->forceFill([
            'type' => 'company',
        ])->save();
        Auth::login($user);

        $role_r = Role::where('name','company')->first();
        if(!empty($user))
        {
            $user->addRole($role_r);
            // WorkSpace slug create on WorkSpace Model
            $workspace = new WorkSpace();
            $workspace->name = $request->workspace;
            $workspace->created_by = $user->id;
            $workspace->save();

            $user_work = User::find($user->id);
            $user_work->active_workspace = $workspace->id;
            $user_work->workspace_id = $workspace->id;
            $user_work->save();

            User::CompanySetting($user->id);

            $user->MakeRole();

            // Use the same customer welcome flow as admin-created customer accounts
            // (initializes trial and sends WelcomeEmail via InitializeTrialAndWelcome listener)
            event(new CreateUser($user, $request));


            // Subscription system removed - all users have unlimited access

            if ( adminSetting('email_verification') == 'on')
            {
                setAdminConfigEmail();

                try {
                    event(new Registered($user));
                } catch (\Exception $e) {
                    \Log::error('Verification email send failed during registration: ' . $e->getMessage());
                }
            }
            else
            {
                $user_work = User::find($user->id);
                $user_work->email_verified_at = date('Y-m-d h:i:s');
                $user_work->save();
            }

        }

        return redirect(RouteServiceProvider::HOME);
    }
}
