<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Hrm\Ess\EssAuthController;
use App\Http\Controllers\Hrm\Ess\EssDashboardController;
use App\Http\Controllers\Hrm\Ess\EssProfileController;
use App\Http\Controllers\Hrm\Ess\EssPayslipController;
use App\Http\Controllers\Hrm\Ess\EssLeaveController;
use App\Http\Controllers\Hrm\Ess\EssFilingController;

/*
|--------------------------------------------------------------------------
| ESS (Employee Self-Service) Routes
|--------------------------------------------------------------------------
|
| These routes are for the Employee Self-Service portal.
| They use a separate authentication guard 'employee'.
|
*/

Route::prefix('ess')->name('ess.')->group(function () {
    
    // Guest routes (not authenticated)
    Route::middleware(['web', 'ess.guest'])->group(function () {
        Route::get('login', [EssAuthController::class, 'showLogin'])->name('login');
        Route::post('login', [EssAuthController::class, 'login'])->name('login.submit');
        
        Route::get('forgot-password', [EssAuthController::class, 'showForgotPassword'])->name('forgot-password');
        Route::post('forgot-password', [EssAuthController::class, 'forgotPassword'])->name('forgot-password.submit');
    });

    // Password setup routes (token-based, no auth required)
    Route::middleware(['web'])->group(function () {
        Route::get('setup/{token}', [EssAuthController::class, 'showSetup'])->name('setup');
        Route::post('setup/{token}', [EssAuthController::class, 'setup'])->name('setup.submit');
    });

    // Authenticated routes
    Route::middleware(['web', 'ess.auth'])->group(function () {
        // Logout
        Route::post('logout', [EssAuthController::class, 'logout'])->name('logout');
        
        // Dashboard
        Route::get('/', [EssDashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard', [EssDashboardController::class, 'index'])->name('dashboard.index');

        // Profile routes
        Route::get('profile', [EssProfileController::class, 'index'])->name('profile');
        Route::get('profile/edit', [EssProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [EssProfileController::class, 'update'])->name('profile.update');
        Route::get('profile/change-password', [EssProfileController::class, 'showChangePassword'])->name('profile.change-password');
        Route::put('profile/password', [EssProfileController::class, 'updatePassword'])->name('profile.update-password');
        Route::get('get-provinces/{country}', [EssProfileController::class, 'getProvinces'])->name('profile.provinces');

        // Payslip routes
        Route::get('payslips', [EssPayslipController::class, 'index'])->name('payslips');
        Route::get('payslips/{id}', [EssPayslipController::class, 'show'])->name('payslips.show');
        Route::get('payslips/{id}/download', [EssPayslipController::class, 'download'])->name('payslips.download');

        // Leave routes
        Route::get('leave', [EssLeaveController::class, 'index'])->name('leave');
        Route::get('leave/apply', [EssLeaveController::class, 'create'])->name('leave.apply');
        Route::post('leave', [EssLeaveController::class, 'store'])->name('leave.store');
        Route::get('leave/{id}', [EssLeaveController::class, 'show'])->name('leave.show');
        Route::delete('leave/{id}/cancel', [EssLeaveController::class, 'cancel'])->name('leave.cancel');

        // Tax Certificates routes
        Route::get('tax-certificates', [EssFilingController::class, 'index'])->name('filing');
        Route::get('tax-certificates/download/{payslipId}', [EssFilingController::class, 'downloadPdf'])->name('filing.download');
        Route::get('tax-certificates/{season}', [EssFilingController::class, 'show'])->name('filing.show');
    });
});
