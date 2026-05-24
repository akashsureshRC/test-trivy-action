<?php

namespace App\Listeners\Billing;

use App\Events\Hrm\DestroyMonthlyPayslip;
use App\Services\BillingService;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandlePayslipDeletion implements ShouldQueue
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
     */
    public function handle(DestroyMonthlyPayslip $event): void
    {
        $payslip = $event->payslip;
        
        if (!$payslip) {
            return;
        }

        // Handle payslip deletion for billing
        $this->billingService->handlePayslipDeletion($payslip);
    }
}
