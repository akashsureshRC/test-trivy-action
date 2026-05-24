<?php

namespace App\Events\Hrm;

use App\Models\Hrm\Payrun;
use App\Models\Hrm\PaySlip;
use Illuminate\Queue\SerializesModels;

class PayrunProcessed
{
    use SerializesModels;

    /**
     * The payrun that was processed
     */
    public Payrun $payrun;

    /**
     * The payslip associated with this payrun
     */
    public PaySlip $payslip;

    /**
     * The workspace ID
     */
    public int $workspaceId;

    /**
     * Create a new event instance.
     */
    public function __construct(Payrun $payrun, PaySlip $payslip, int $workspaceId)
    {
        $this->payrun = $payrun;
        $this->payslip = $payslip;
        $this->workspaceId = $workspaceId;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
