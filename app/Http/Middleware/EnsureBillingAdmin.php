<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBillingAdmin
{
    /**
     * Allow only super admin or master_admin users.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->type, ['super admin', 'master_admin'], true)) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
