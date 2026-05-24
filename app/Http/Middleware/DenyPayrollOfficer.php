<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DenyPayrollOfficer
{
    /**
     * Deny access to payroll officer users.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (Auth::check() && Auth::user()->type === 'payroll_officer') {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
