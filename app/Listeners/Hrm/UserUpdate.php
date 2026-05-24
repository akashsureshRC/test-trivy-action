<?php

namespace App\Listeners\Hrm;

use App\Events\UpdateUser;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Hrm\Employee;

class UserUpdate
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(UpdateUser $event)
    {
        $request = $event->request;
        $user = $event->user;
        $employee = Employee::where('user_id', $user->id)->first();
        if(!empty($employee))
        {
            // Update first_name and last_name from name
            $nameParts = explode(' ', $request->name, 2);
            $employee->first_name = $nameParts[0] ?? '';
            $employee->last_name = $nameParts[1] ?? '';
            $employee->save();
        }
    }
}
