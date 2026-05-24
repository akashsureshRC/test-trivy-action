<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class EssApiAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Authenticate using the ess-api guard
            $employee = auth('ess-api')->authenticate();

            if (!$employee) {
                return response()->json([
                    'status' => 0,
                    'message' => 'User not found',
                    'error_code' => 'USER_NOT_FOUND'
                ], 401);
            }

            // Routes that don't require ESS enabled check (logout, refresh token)
            $exemptRoutes = [
                'api/ess/auth/logout',
                'api/ess/auth/refresh'
            ];

            // Check if ESS is enabled for this employee (skip for exempt routes)
            if (!in_array($request->path(), $exemptRoutes) && !$employee->ess_enabled) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Your Employee Self-Service access has been disabled. Please contact HR.',
                    'error_code' => 'ESS_DISABLED'
                ], 403);
            }

            // Add employee to request for easy access in controllers
            $request->merge(['ess_employee' => $employee]);
            $request->setUserResolver(function () use ($employee) {
                return $employee;
            });

        } catch (TokenExpiredException $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Token has expired',
                'error_code' => 'TOKEN_EXPIRED'
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Token is invalid',
                'error_code' => 'TOKEN_INVALID'
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Authorization token not found',
                'error_code' => 'TOKEN_NOT_FOUND'
            ], 401);
        }

        return $next($request);
    }
}
