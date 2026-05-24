<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllowSuspendedBillingAccess
{
    /**
     * Handle an incoming request.
     * Allow suspended users to access billing and payment pages
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // If user is suspended but trying to access billing/payment routes, allow it
        if ($user && $user->billing_status === 'suspended') {
            // Check if accessing billing, payment, or logout routes
            if ($request->is('my-billing*') || 
                $request->is('billing/invoices*') ||
                $request->routeIs('my-billing.*') ||
                $request->routeIs('billing.invoices.*') ||
                $request->routeIs('logout') ||
                $request->routeIs('ess.logout') ||
                $request->is('logout') ||
                $request->is('ess/logout')) {
                // Allow access to billing pages and logout for suspended users
                return $next($request);
            }
            
            // Block access to other pages for suspended users
            return redirect()->route('my-billing.invoices')
                ->with('error', __('Your account is suspended due to overdue payment. Please pay your outstanding invoices to reactivate your account.'));
        }
        
        return $next($request);
    }
}
