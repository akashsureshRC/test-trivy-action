<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EssAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('employee')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('ess.login');
        }

        $employee = Auth::guard('employee')->user();

        // Check if ESS is enabled for this employee
        if (!$employee->ess_enabled) {
            Auth::guard('employee')->logout();
            return redirect()->route('ess.login')
                ->with('error', 'Your Employee Self-Service access has been disabled. Please contact HR.');
        }

        return $next($request);
    }
}
