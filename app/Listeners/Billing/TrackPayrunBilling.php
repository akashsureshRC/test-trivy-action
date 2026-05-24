<?php

namespace App\Listeners\Billing;

use App\Events\Hrm\PayrunProcessed;
use App\Services\BillingService;
use Illuminate\Support\Facades\Log;

class TrackPayrunBilling
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
     * This listener is triggered when a Payment Run is processed,
     * which is when we actually bill for the payslip.
     */
    public function handle(PayrunProcessed $event): void
    {
        Log::info('TrackPayrunBilling: Event received', [
            'payslip_id' => $event->payslip?->id,
            'workspace_id' => $event->workspaceId,
        ]);

        $payslip = $event->payslip;
        $workspaceId = $event->workspaceId;
        
        if (!$payslip) {
            Log::warning('TrackPayrunBilling: No payslip in event');
            return;
        }

        try {
            // Track the payrun for billing when payment run is processed
            $usage = $this->billingService->trackPayrunProcessed($payslip, $workspaceId);
            
            Log::info('TrackPayrunBilling: Usage tracked', [
                'payslip_id' => $payslip->id,
                'usage_id' => $usage?->id,
            ]);
        } catch (\Exception $e) {
            Log::error('TrackPayrunBilling: Error tracking usage', [
                'payslip_id' => $payslip->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
