<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Billing commands - run daily
        $generateInvoicesTime = $this->resolveScheduleTime('cron.generate_invoices', '00:00');
        $overdueRemindersTime = $this->resolveScheduleTime('cron.overdue_reminders', '08:00');
        $checkOverdueTime = $this->resolveScheduleTime('cron.check_overdue', '10:00');

        // Generate invoices for completed billing cycles
        $schedule->command('billing:generate-invoices --close-cycles')
            ->dailyAt($generateInvoicesTime)
            ->withoutOverlapping()
            ->runInBackground();

        // Send overdue invoice reminders (1, 7, 14, 30 days overdue)
        $schedule->command('billing:send-overdue-reminders')
            ->dailyAt($overdueRemindersTime)
            ->withoutOverlapping()
            ->runInBackground();

        // Suspend delinquent accounts
        $schedule->command('billing:check-overdue --suspend')
            ->dailyAt($checkOverdueTime)
            ->withoutOverlapping()
            ->runInBackground();
    }

    private function resolveScheduleTime(string $key, string $fallback): string
    {
        $value = config($key);

        if (!is_string($value)) {
            return $fallback;
        }

        $value = trim($value);

        // Accept strict 24-hour HH:MM format only
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value)) {
            return $fallback;
        }

        return $value;
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
