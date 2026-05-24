<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cron Schedule Times
    |--------------------------------------------------------------------------
    |
    | Daily run times for scheduled billing commands (24-hour HH:MM format).
    |
    */

    'generate_invoices'  => env('CRON_GENERATE_INVOICES', '00:00'),
    'overdue_reminders'  => env('CRON_OVERDUE_REMINDERS', '08:00'),
    'check_overdue'      => env('CRON_CHECK_OVERDUE', '10:00'),
];
