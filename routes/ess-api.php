<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Hrm\Api\Ess\EssAuthApiController;
use App\Http\Controllers\Hrm\Api\Ess\EssDashboardApiController;
use App\Http\Controllers\Hrm\Api\Ess\EssProfileApiController;
use App\Http\Controllers\Hrm\Api\Ess\EssPayslipApiController;
use App\Http\Controllers\Hrm\Api\Ess\EssLeaveApiController;
use App\Http\Controllers\Hrm\Api\Ess\EssTaxCertificateApiController;
use App\Http\Controllers\Hrm\Api\Ess\EssAccountApiController;
use App\Http\Controllers\Hrm\Api\Ess\EssNotificationApiController;
use App\Http\Controllers\Hrm\Api\Ess\EssAttendanceApiController;

/*
|--------------------------------------------------------------------------
| ESS Mobile API Routes
|--------------------------------------------------------------------------
|
| These routes are for the Employee Self-Service Flutter mobile app.
| They use JWT authentication with the 'employee' provider.
|
| Base URL: /api/ess
|
*/

Route::prefix('ess')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Health Check (No Authentication Required)
    |--------------------------------------------------------------------------
    */
    Route::get('health', function () {
        return response()->json([
            'status' => 1,
            'message' => 'API is running',
            'timestamp' => now()->toIso8601String(),
        ]);
    });

    /*
    |--------------------------------------------------------------------------
    | Public Routes (No Authentication Required)
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {
        Route::post('login', [EssAuthApiController::class, 'login']);
        Route::post('refresh', [EssAuthApiController::class, 'refresh']); // Moved to public
        Route::post('forgot-password', [EssAuthApiController::class, 'forgotPassword'])
            ->middleware('throttle:ess-otp-send');
        Route::post('verify-reset-token', [EssAuthApiController::class, 'verifyResetToken'])
            ->middleware('throttle:ess-otp-verify');
        Route::post('reset-password', [EssAuthApiController::class, 'resetPassword'])
            ->middleware('throttle:ess-otp-reset');
    });

    /*
    |--------------------------------------------------------------------------
    | Protected Routes (JWT Authentication Required)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['ess.api.auth'])->group(function () {
        
        // Auth Routes
        Route::prefix('auth')->group(function () {
            Route::post('logout', [EssAuthApiController::class, 'logout']);
            Route::put('change-password', [EssAuthApiController::class, 'changePassword']);
        });

        // Dashboard
        Route::get('dashboard', [EssDashboardApiController::class, 'index']);

        // Profile Routes
        Route::prefix('profile')->group(function () {
            Route::get('/', [EssProfileApiController::class, 'index']);
            Route::put('/', [EssProfileApiController::class, 'update']);
            Route::get('countries', [EssProfileApiController::class, 'getCountries']);
            Route::get('provinces/{country_id}', [EssProfileApiController::class, 'getProvinces']);
        });

        // Payslip Routes
        Route::prefix('payslips')->group(function () {
            Route::get('/', [EssPayslipApiController::class, 'index']);
            Route::get('{id}/download', [EssPayslipApiController::class, 'download']);
        });

        // Leave Routes
        Route::prefix('leave')->group(function () {
            Route::get('/', [EssLeaveApiController::class, 'index']);
            Route::post('/', [EssLeaveApiController::class, 'store']);
            Route::get('balances', [EssLeaveApiController::class, 'balances']);
            Route::get('types', [EssLeaveApiController::class, 'leaveTypes']);
            Route::get('{id}', [EssLeaveApiController::class, 'show']);
            Route::delete('{id}', [EssLeaveApiController::class, 'cancel']);
        });

        // Attendance Routes (Time & Attendance with Geofencing)
        Route::prefix('attendance')->group(function () {
            Route::get('status', [EssAttendanceApiController::class, 'status']);
            Route::post('clock-in', [EssAttendanceApiController::class, 'clockIn']);
            Route::post('clock-out', [EssAttendanceApiController::class, 'clockOut']);
            Route::get('history', [EssAttendanceApiController::class, 'history']);
        });

        // Tax Certificates Routes
        Route::prefix('tax-certificates')->group(function () {
            Route::get('/', [EssTaxCertificateApiController::class, 'index']);
            Route::get('{season}', [EssTaxCertificateApiController::class, 'show']);
            Route::get('{id}/download', [EssTaxCertificateApiController::class, 'download']);
        });

        // Account Routes (GDPR/Store Compliance)
        Route::prefix('account')->group(function () {
            Route::post('delete', [EssAccountApiController::class, 'delete']);
        });

        // Push Notification Routes
        Route::prefix('notifications')->group(function () {
            Route::post('register-device', [EssNotificationApiController::class, 'registerDevice']);
            Route::post('unregister-device', [EssNotificationApiController::class, 'unregisterDevice']);
        });
    });
});
