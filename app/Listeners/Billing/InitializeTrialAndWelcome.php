<?php

namespace App\Listeners\Billing;

use App\Events\CreateUser;
use App\Mail\Billing\WelcomeEmail;
use App\Services\BillingService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InitializeTrialAndWelcome
{
    protected BillingService $billingService;

    /**
     * Create the event listener.
     */
    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    /**
     * Handle the event.
     * 
     * This listener is triggered when a new company user is created.
     * It initializes their trial period and sends a welcome email.
     */
    public function handle(CreateUser $event): void
    {
        $user = $event->user;
        $request = $event->request;

        // Only process for company users (customers)
        if ($user->type !== 'company') {
            return;
        }

        try {
            // Check if super admin selected a specific plan type
            $planType = $request->input('plan_type', 'trial');
            
            if ($planType === 'paid') {
                // Start user directly on paid plan
                $user->forceFill([
                    'billing_status' => 'active',
                    'trial_ends_at' => null,
                    'trial_payslips_used' => 0,
                    'trial_payslips_limit' => 0,
                ])->save();

                Log::info('InitializeTrialAndWelcome: Started user on paid plan', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            } else {
                // Initialize trial for the new user (default)
                $this->billingService->initializeTrial($user);

                Log::info('InitializeTrialAndWelcome: Trial initialized for new company', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            }

            // Send welcome email
            setAdminConfigEmail();
            Mail::to($user->email)->send(new WelcomeEmail($user));

            Log::info('InitializeTrialAndWelcome: Welcome email sent', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

        } catch (\Exception $e) {
            Log::error('InitializeTrialAndWelcome: Error processing new company', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
