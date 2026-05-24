<?php

namespace App\Http\Controllers\Hrm\Ess;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use App\Mail\EssPasswordResetMail;
use App\Models\Hrm\Employee;

class EssAuthController extends Controller
{
    /**
     * Show the ESS login form.
     */
    public function showLogin()
    {
        return view('hrm.ess.auth.login');
    }

    /**
     * Handle ESS login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Rate limiting
        $throttleKey = 'ess-login:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => __('Too many login attempts. Please try again in :seconds seconds.', ['seconds' => $seconds]),
            ]);
        }

        // Find employee by email
        $employee = Employee::where('email', $request->email)->first();

        // Check if employee exists
        if (!$employee) {
            RateLimiter::hit($throttleKey, 60);
            throw ValidationException::withMessages([
                'email' => __('The provided credentials are incorrect.'),
            ]);
        }

        // Check if ESS is enabled
        if (!$employee->ess_enabled) {
            throw ValidationException::withMessages([
                'email' => __('Your Employee Self-Service access has not been activated. Please contact HR.'),
            ]);
        }

        // Check if password is set
        if (!$employee->password) {
            throw ValidationException::withMessages([
                'email' => __('Please set up your password first using the link sent to your email.'),
            ]);
        }

        // Validate password
        if (!Hash::check($request->password, $employee->password)) {
            RateLimiter::hit($throttleKey, 60);
            throw ValidationException::withMessages([
                'email' => __('The provided credentials are incorrect.'),
            ]);
        }

        // Clear rate limiter on success
        RateLimiter::clear($throttleKey);

        // Log the employee in
        Auth::guard('employee')->login($employee, $request->boolean('remember'));

        // Update last login
        $employee->updateLastLogin();

        // Regenerate session
        $request->session()->regenerate();

        return redirect()->intended(route('ess.dashboard'));
    }

    /**
     * Log the employee out.
     */
    public function logout(Request $request)
    {
        Auth::guard('employee')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('ess.login')
            ->with('success', __('You have been logged out successfully.'));
    }

    /**
     * Show the password setup form.
     */
    public function showSetup(string $token)
    {
        $employee = Employee::where('ess_setup_token', $token)->first();

        if (!$employee) {
            return redirect()->route('ess.login')
                ->with('error', __('Invalid setup link. Please contact HR for a new invitation.'));
        }

        if (!$employee->hasValidEssSetupToken($token)) {
            return redirect()->route('ess.login')
                ->with('error', __('This setup link has expired. Please contact HR for a new invitation.'));
        }

        return view('hrm.ess.auth.setup', [
            'token' => $token,
            'employee' => $employee,
        ]);
    }

    /**
     * Handle password setup.
     */
    public function setup(Request $request, string $token)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $employee = Employee::where('ess_setup_token', $token)->first();

        if (!$employee || !$employee->hasValidEssSetupToken($token)) {
            return redirect()->route('ess.login')
                ->with('error', __('Invalid or expired setup link. Please contact HR for a new invitation.'));
        }

        // Set the password
        $employee->password = Hash::make($request->password);
        $employee->save();

        // Clear the setup token and enable ESS
        $employee->clearEssSetupToken();

        // Log the employee in
        Auth::guard('employee')->login($employee);
        $employee->updateLastLogin();

        return redirect()->route('ess.dashboard')
            ->with('success', __('Your password has been set successfully. Welcome to Employee Self-Service!'));
    }

    /**
     * Show forgot password form.
     */
    public function showForgotPassword()
    {
        return view('hrm.ess.auth.forgot-password');
    }

    /**
     * Handle forgot password request.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $employee = Employee::where('email', $request->email)
            ->where('ess_enabled', true)
            ->first();

        if ($employee) {
            // Generate new setup token for password reset
            $token = $employee->generateEssSetupToken();
            
            // Dispatch password reset email to the queue.
            // SMTP config is applied at processing time inside EssQueueableMail::send().
            try {
                Mail::to($employee->email)->send(new EssPasswordResetMail($employee, $token));
            } catch (\Exception $e) {
                \Log::error('Failed to send ESS password reset email: ' . $e->getMessage());
            }
        }

        // Always show success message to prevent email enumeration
        return back()->with('success', __('If an account exists with that email, you will receive a password reset link shortly.'));
    }
}
