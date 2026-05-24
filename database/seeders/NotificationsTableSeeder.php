<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Only payroll notifications that are actually accessible via UI
        $notifications = [
            'Create User',              // User management (accessible)
            'New Payroll',             // Payslip sending (accessible)
            'Leave Request Approved',  // Leave approval notification to employee (accessible)
            'Leave Request Rejected',  // Leave rejection notification to employee (accessible)
            'Employee Leave Received', // Leave request notification to HR (accessible)
            'Employee Leave Cancelled', // Leave cancellation notification to HR (accessible)
        ];
        
        $permissions = [
            'user manage',
            'setsalary pay slip manage',
            'leave manage',
            'leave manage',
            'leave manage',
            'leave manage',
        ];
        
        foreach($notifications as $key => $n){
            Notification::updateOrCreate(
                [
                    'action' => $n,
                    'type' => 'mail',
                ],
                [
                    'status' => 'on',
                    'permissions' => $permissions[$key],
                    'module' => 'payroll', // Single module for payroll app
                ]
            );
        }
    }
}
