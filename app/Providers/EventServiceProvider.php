<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\CreateUser;
use App\Events\Hrm\PayrunProcessed;
use App\Events\Hrm\DestroyMonthlyPayslip;
use App\Listeners\Billing\InitializeTrialAndWelcome;
use App\Listeners\Billing\TrackPayrunBilling;
use App\Listeners\Billing\HandlePayslipDeletion;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        // Note: Menu events (CompanyMenuEvent, SuperAdminMenuEvent, etc.) are registered in MenuServiceProvider
        
        // New company user created - initialize trial and send welcome email
        CreateUser::class => [
            InitializeTrialAndWelcome::class,
        ],

        // Billing - Track payrun processing for usage billing (only bill when payment run is done)
        PayrunProcessed::class => [
            TrackPayrunBilling::class,
        ],
        // Handle payslip deletion - remove billing record if not yet invoiced
        DestroyMonthlyPayslip::class => [
            HandlePayslipDeletion::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
