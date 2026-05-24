<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EssRedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     * Redirect authenticated employees away from guest pages (login, setup).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('employee')->check()) {
            return redirect()->route('ess.dashboard');
        }

        return $next($request);
    }
}
