<?php

namespace App\Events\Hrm;

use Illuminate\Queue\SerializesModels;

class DestroyCommission
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $commission;

    public function __construct($commission)
    {
        $this->commission = $commission;
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
