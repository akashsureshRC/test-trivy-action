<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MasterAdminController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Company\SettingsController as CompanySettingsController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\HelpdeskConversionController;
use App\Http\Controllers\HelpdeskTicketController;
use App\Http\Controllers\HelpdeskTicketCategoryController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SuperAdmin\SettingsController as SuperAdminSettingsController;
use App\Http\Controllers\WorkSpaceController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Auth::routes();
require __DIR__.'/auth.php';

Route::get('/register/{lang?}', [RegisteredUserController::class, 'create'])->name('register');
Route::get('/login/{lang?}', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::get('/forgot-password/{lang?}', [PasswordResetLinkController::class, 'create'])->name('password.request');
Route::get('/verify-email/{lang?}', [EmailVerificationPromptController::class, '__invoke'])->name('verification.notice');

// module page before login
Route::get('add-on', [HomeController::class, 'Software'])->name('apps.software');
Route::get('add-on/details/{slug}', [HomeController::class, 'SoftwareDetails'])->name('software.details');
Route::get('pricing', [HomeController::class, 'Pricing'])->name('apps.pricing');
Route::get('pages', [HomeController::class, 'CustomPage'])->name('custompage');
Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('login.redirect');

Route::middleware(['auth','verified'])->group(function () {

    Route::resource('permissions', PermissionController::class);

    // Master Administrator Management (super admin only)
    Route::resource('master-admin', MasterAdminController::class);
    Route::get('master-admin/{id}/manage-companies', [MasterAdminController::class, 'manageCompanies'])->name('master-admin.manage-companies');
    Route::post('master-admin/{id}/update-companies', [MasterAdminController::class, 'updateCompanies'])->name('master-admin.update-companies');

    // Master Administrator Pages (for master_admin users)
    Route::prefix('ma')->name('master-admin.')->middleware('auth')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\MasterAdminPagesController::class, 'dashboard'])->name('dashboard');
        Route::get('/companies', [\App\Http\Controllers\MasterAdminPagesController::class, 'companies'])->name('companies');
        Route::get('/companies/list', [\App\Http\Controllers\MasterAdminPagesController::class, 'companiesList'])->name('companies.list');
        Route::get('/companies/create', [\App\Http\Controllers\MasterAdminPagesController::class, 'createCompany'])->name('companies.create');
        Route::post('/companies', [\App\Http\Controllers\MasterAdminPagesController::class, 'storeCompany'])->name('companies.store');
        Route::get('/companies/{id}/login', [\App\Http\Controllers\MasterAdminPagesController::class, 'loginAsCompany'])->name('login-as-company');
        Route::get('/return', [\App\Http\Controllers\MasterAdminPagesController::class, 'returnToMasterAdmin'])->name('return');
        Route::get('/invoices', [\App\Http\Controllers\MasterAdminPagesController::class, 'invoices'])->name('invoices');
        Route::get('/reports', [\App\Http\Controllers\MasterAdminPagesController::class, 'reports'])->name('reports');
        Route::get('/payroll-cycles', [\App\Http\Controllers\MasterAdminPagesController::class, 'payrollCycles'])->name('payroll-cycles');
    });

    // Super Admin Payroll Cycles
    Route::get('/super-admin/payroll-cycles', [HomeController::class, 'payrollCycles'])->name('super-admin.payroll-cycles')->middleware('auth');
    
    // Super Admin Reports
    Route::get('/super-admin/reports', [HomeController::class, 'reports'])->name('super-admin.reports')->middleware('auth');

    //dashboard
    if(function_exists('moduleIsActive') && moduleIsActive('GoogleAuthentication'))
    {
        Route::get('/dashboard', [HomeController::class,'Dashboard'])->name('dashboard')->middleware(['2fa']);
        Route::get('/home', [HomeController::class,'Dashboard'])->name('home')->middleware(['2fa']);
    }
    else
    {
        Route::get('/dashboard', [HomeController::class,'Dashboard'])->name('dashboard');
        Route::get('/dashboard/account', [HomeController::class,'Dashboard'])->name('dashboard-home');
        Route::get('/home', [HomeController::class,'Dashboard'])->name('home');
    }

     // settings
    Route::resource('settings', SettingsController::class);
    Route::post('settings-save', [CompanySettingsController::class, 'store'])->name('settings.save');
    Route::post('company/settings-save', [CompanySettingsController::class, 'store'])->name('company.settings.save');
    Route::post('super-admin/settings-save', [SuperAdminSettingsController::class, 'store'])->name('super.admin.settings.save');
    Route::post('super-admin/system-settings-save', [SuperAdminSettingsController::class, 'SystemStore'])->name('super.admin.system.setting.store');
    Route::post('company/system-settings-save', [CompanySettingsController::class, 'SystemStore'])->name('company.system.setting.store');
    Route::post('company-setting-save', [CompanySettingsController::class, 'companySettingStore'])->name('company.setting.save');
    Route::get('get-sic7-codes-by-category', [CompanySettingsController::class, 'getSic7CodesByCategory'])->name('get.sic7.codes.by.category');

    // Country provinces route
    Route::get('country/provinces-list/{id}', [CompanySettingsController::class, 'getProvinces'])->name('country.provinces.list');

    Route::post('email-settings-save', [SettingsController::class, 'mailStore'])->name('email.setting.store');
    Route::post('test-mail', [SettingsController::class, 'testMail'])->name('test.mail');
    Route::post('test-mail-send', [SettingsController::class, 'sendTestMail'])->name('test.mail.send');
    Route::post('email/getfields',[SettingsController::class,'getfields'])->name('get.emailfields');
    Route::post('email-notification-settings-save', [SettingsController::class, 'mailNotificationStore'])->name('email.notification.setting.store');

    Route::post('cookie-settings-save', [SuperAdminSettingsController::class, 'CookieSetting'])->name('cookie.setting.store');
    Route::post('seo/setting/save', [SuperAdminSettingsController::class, 'seoSetting'])->name('seo.setting.save');

    Route::get('/setting/section/{module}/{methord?}', [SettingsController::class,'getSettingSection'])->name('setting.section.get');

    // Tax Year Configuration (Super Admin)
    Route::prefix('tax-years')->name('tax-years.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SuperAdmin\TaxYearController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\SuperAdmin\TaxYearController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\SuperAdmin\TaxYearController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [\App\Http\Controllers\SuperAdmin\TaxYearController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\SuperAdmin\TaxYearController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\SuperAdmin\TaxYearController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/lock', [\App\Http\Controllers\SuperAdmin\TaxYearController::class, 'lock'])->name('lock');
        Route::post('/{id}/unlock', [\App\Http\Controllers\SuperAdmin\TaxYearController::class, 'unlock'])->name('unlock');
    });

    // Billing Configuration (Super Admin)
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/', function() { return redirect()->route('billing.tiers.index'); })->name('index');
        Route::get('/settings', [\App\Http\Controllers\SuperAdmin\BillingConfigController::class, 'settings'])->name('settings');
        Route::post('/settings', [\App\Http\Controllers\SuperAdmin\BillingConfigController::class, 'updateSettings'])->name('settings.update');
        
        // Billing Tiers
        Route::get('/tiers', [\App\Http\Controllers\SuperAdmin\BillingConfigController::class, 'tiers'])->name('tiers.index');
        Route::get('/tiers/create', [\App\Http\Controllers\SuperAdmin\BillingConfigController::class, 'createTier'])->name('tiers.create');
        Route::post('/tiers', [\App\Http\Controllers\SuperAdmin\BillingConfigController::class, 'storeTier'])->name('tiers.store');
        Route::get('/tiers/{id}/edit', [\App\Http\Controllers\SuperAdmin\BillingConfigController::class, 'editTier'])->name('tiers.edit');
        Route::put('/tiers/{id}', [\App\Http\Controllers\SuperAdmin\BillingConfigController::class, 'updateTier'])->name('tiers.update');
        Route::delete('/tiers/{id}', [\App\Http\Controllers\SuperAdmin\BillingConfigController::class, 'destroyTier'])->name('tiers.destroy');
        Route::post('/tiers/bulk-save', [\App\Http\Controllers\SuperAdmin\BillingConfigController::class, 'bulkSaveTiers'])->name('tiers.bulk-save');
        
        // Preview calculation
        Route::post('/preview-calculation', [\App\Http\Controllers\SuperAdmin\BillingConfigController::class, 'previewCalculation'])->name('preview.calculation');
    });

    //users
    Route::resource('users', UserController::class);
    Route::get('users/create-page', [UserController::class, 'createPage'])->name('users.create.page');
    Route::get('users/list/view', [UserController::class, 'List'])->name('users.list.view');
    Route::get('profile', [UserController::class, 'profile'])->name('profile');
    Route::post('edit-profile', [UserController::class, 'editprofile'])->name('edit.profile');
    Route::post('change-password', [UserController::class, 'updatePassword'])->name('update.password');
    Route::any('user-reset-password/{id}', [UserController::class, 'UserPassword'])->name('users.reset');
    Route::get('user-login/{id}', [UserController::class, 'LoginManage'])->name('users.login');
    Route::post('user-reset-password/{id}', [UserController::class, 'UserPasswordReset'])->name('user.password.update');
    Route::get('users/{id}/login-with-company', [UserController::class, 'LoginWithCompany'])->name('login.with.company');
    Route::get('company-info/{id}', [UserController::class, 'companyInfo'])->name('company.info');
    Route::post('user-unable', [UserController::class, 'UserUnable'])->name('user.unable');

    //User Log
    Route::get('users/logs/history', [UserController::class, 'UserLogHistory'])->name('users.userlog.history');
    Route::get('users/logs/{id}', [UserController::class, 'UserLogView'])->name('users.userlog.view');
    Route::delete('users/logs/destroy/{id}', [UserController::class, 'UserLogDestroy'])->name('users.userlog.destroy');

    // users import
    Route::get('users/import/export', [UserController::class, 'fileImportExport'])->name('users.file.import');
    Route::get('users/import/modal', [UserController::class, 'fileImportModal'])->name('users.import.modal');
    Route::post('users/import', [UserController::class, 'fileImport'])->name('users.import');
    Route::post('users/data/import/', [UserController::class, 'UserImportdata'])->name('users.import.data');

    // impersonating
    Route::get('login-with-company/exit', [UserController::class, 'ExitCompany'])->name('exit.company');

    // Language
    Route::get('/lang/change/{lang}', [LanguageController::class, 'changeLang'])->name('lang.change');
    Route::get('langmanage/{lang?}/{module?}', [LanguageController::class, 'index'])->name('lang.index');
    Route::get('create-language', [LanguageController::class, 'create'])->name('create.language');
    Route::post('langs/{lang?}/{module?}', [LanguageController::class, 'storeData'])->name('lang.store.data');
    Route::post('disable-language',[LanguageController::class,'disableLang'])->name('disablelanguage');
    Route::any('store-language', [LanguageController::class, 'store'])->name('store.language');
    Route::delete('/lang/{id}', [LanguageController::class, 'destroy'])->name('lang.destroy');
    // End Language

    // Workspace
    Route::resource('workspace', WorkSpaceController::class);
    Route::get('workspace/change/{id}', [WorkSpaceController::class, 'change'])->name('workspace.change');
    Route::post('workspace/check', [WorkSpaceController::class, 'workspaceCheck'])->name('workspace.check');
    // End Workspace

    // Module Install
    Route::get('modules/list', [ModuleController::class, 'index'])->name('module.index');
    Route::get('modules/add', [ModuleController::class, 'add'])->name('module.add');
    Route::post('install-modules', [ModuleController::class, 'install'])->name('module.install');
    Route::post('remove-modules/{module}', [ModuleController::class, 'remove'])->name('module.remove');
    Route::post('modules-enable', [ModuleController::class, 'enable'])->name('module.enable');
    Route::get('cancel/add-on/{name}', [ModuleController::class, 'CancelAddOn'])->name('cancel.add.on');
    // End Module Install

    // Email Templates
    Route::resource('email-templates', EmailTemplateController::class);
    Route::get('email-templates/{id}/lang/{lang?}', [EmailTemplateController::class, 'show'])->name('manage.email.language');
    Route::put('email-templates/{pid}/store-lang', [EmailTemplateController::class, 'storeEmailLang'])->name('store.email.language');
    Route::put('email-templates/{id}/status', [EmailTemplateController::class, 'updateStatus'])->name('status.email.language');
    // End Email Templates

    // helpdesk
    Route::resource('helpdesk', HelpdeskTicketController::class);
    Route::resource('helpdeskticket-category', HelpdeskTicketCategoryController::class);
    Route::get('helpdesk-tickets/search/{status?}', [HelpdeskTicketController::class, 'index'])->name('helpdesk-tickets.search');
    Route::post('helpdesk-ticket/getUser', [HelpdeskTicketController::class, 'getUser'])->name('helpdesk-tickets.getuser');
    Route::post('helpdesk-ticket/{id}', [HelpdeskTicketController::class, 'reply'])->name('helpdesk-ticket.reply');
    Route::post('helpdesk-ticket/{id}/conversion', [HelpdeskConversionController::class, 'store'])->name('helpdesk-ticket.conversion.store');
    Route::post('helpdesk-ticket/{id}/note', [HelpdeskTicketController::class, 'storeNote'])->name('helpdesk-ticket.note.store');
    Route::delete('helpdesk-ticket-attachment/{tid}/destroy/{id}', [HelpdeskTicketController::class, 'attachmentDestroy'])->name('helpdesk-ticket.attachment.destroy');
    // End helpdesk

    //notification
    Route::resource('notification-template', NotificationController::class);
    Route::get('notification-template/{id}/{lang?}', [NotificationController::class, 'show'])->name('manage.notification.language');
    Route::post('notification-template/{pid}', [NotificationController::class, 'storeNotificationLang'])->name('store.notification.language');

    // User Billing Dashboard (for company owners — not payroll officers)
    Route::prefix('my-billing')->name('my-billing.')->middleware(\App\Http\Middleware\DenyPayrollOfficer::class)->group(function () {
        Route::get('/', [\App\Http\Controllers\UserBillingController::class, 'index'])->name('index');
        Route::get('/usage', [\App\Http\Controllers\UserBillingController::class, 'usage'])->name('usage');
        Route::get('/invoices', [\App\Http\Controllers\UserBillingController::class, 'invoices'])->name('invoices');
        Route::get('/invoices/{id}', [\App\Http\Controllers\UserBillingController::class, 'showInvoice'])->name('invoices.show');
        Route::get('/invoices/{id}/download', [\App\Http\Controllers\UserBillingController::class, 'downloadInvoice'])->name('invoices.download');
        Route::get('/invoices/{id}/view-pdf', [\App\Http\Controllers\UserBillingController::class, 'viewInvoicePdf'])->name('invoices.view-pdf');
        Route::get('/pricing', [\App\Http\Controllers\UserBillingController::class, 'pricing'])->name('pricing');
        
        // Trial upgrade
        Route::post('/upgrade-trial', [\App\Http\Controllers\UserBillingController::class, 'upgradeFromTrial'])->name('upgrade-trial');
        
        // API endpoints
        Route::post('/calculate-estimate', [\App\Http\Controllers\UserBillingController::class, 'calculateEstimate'])->name('calculate-estimate');
        Route::get('/status', [\App\Http\Controllers\UserBillingController::class, 'status'])->name('status');
        Route::get('/current-cycle', [\App\Http\Controllers\UserBillingController::class, 'currentCycle'])->name('current-cycle');
        
        // Payment routes
        Route::get('/pay/{invoice}', [\App\Http\Controllers\Billing\PaymentController::class, 'initiate'])->name('pay');
        Route::get('/payment/success', [\App\Http\Controllers\Billing\PaymentController::class, 'success'])->name('payment.success');
        Route::get('/payment/cancel', [\App\Http\Controllers\Billing\PaymentController::class, 'cancel'])->name('payment.cancel');
        
        // EFT Proof Submission (user)
        Route::post('/invoices/{invoice}/submit-eft-proof', [\App\Http\Controllers\Billing\PaymentController::class, 'submitBankTransferProof'])->name('submit-eft-proof');
    });
    
    // Super Admin Invoice Management
    Route::prefix('billing/invoices')->name('billing.invoices.')->middleware(['auth'])->group(function () {
        Route::get('/', [\App\Http\Controllers\Billing\PaymentController::class, 'adminInvoices'])->name('index');
        Route::get('/export', [\App\Http\Controllers\Billing\PaymentController::class, 'exportInvoices'])->name('export');
        Route::post('/generate', [\App\Http\Controllers\Billing\PaymentController::class, 'adminGenerateInvoices'])->name('generate');
        Route::get('/{id}', [\App\Http\Controllers\Billing\PaymentController::class, 'adminShowInvoice'])->name('show');
        Route::get('/{id}/download', [\App\Http\Controllers\Billing\PaymentController::class, 'adminDownloadInvoice'])->name('download');
        Route::post('/{id}/manual-payment', [\App\Http\Controllers\Billing\PaymentController::class, 'processManualPayment'])->name('manual-payment');
        Route::post('/{id}/send-reminder', [\App\Http\Controllers\Billing\PaymentController::class, 'sendReminder'])->name('send-reminder');
        
        // EFT Proof Review (admin)
        Route::post('/eft-submission/{id}/review', [\App\Http\Controllers\Billing\PaymentController::class, 'reviewBankTransferProof'])->name('review-eft');
    });
});

// PayFast ITN (Instant Transaction Notification) - Must be outside auth middleware
Route::post('/payfast/notify', [\App\Http\Controllers\Billing\PaymentController::class, 'notify'])->name('payfast.notify');

Route::get('module/reset', [ModuleController::class, 'ModuleReset'])->name('module.reset');
Route::post('guest/module/selection', [ModuleController::class, 'GuestModuleSelection'])->name('guest.module.selection');

// cookie
Route::get('cookie/consent', [SuperAdminSettingsController::class, 'CookieConsent'])->name('cookie.consent');
