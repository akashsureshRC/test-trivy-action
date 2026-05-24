<?php

namespace Database\Seeders\Hrm;

use App\Models\Notification;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class NotificationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Note: Notifications are now consolidated in the main NotificationsTableSeeder.
     * This seeder is kept for backward compatibility but does nothing.
     *
     * @return void
     */
    public function run()
    {
        // All payroll notifications are now in the main NotificationsTableSeeder
        // This seeder is deprecated and kept only for backward compatibility
    }
}
