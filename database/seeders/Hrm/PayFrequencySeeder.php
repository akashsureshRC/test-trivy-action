<?php

namespace Database\Seeders\Hrm;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayFrequencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        $payFrequencies = [
            [
                'id' => 1,
                'pay_frequency' => 'Daily - Every Day',
                'last_day_of_period' => null,      // Not needed for daily
                'biweekly_date' => null,           // Not needed for daily
                'last_day_of_month' => null,       // Not needed for daily
                'go_further_back' => 1,
                'years_back' => 'max',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'pay_frequency' => 'Weekly - Once every Week (Mon - Sun)',
                'last_day_of_period' => 'Sunday',  // Week ends on Sunday (Mon-Sun week)
                'biweekly_date' => null,           // Not needed for weekly
                'last_day_of_month' => null,       // Not needed for weekly
                'go_further_back' => 1,
                'years_back' => 'max',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 6,
                'pay_frequency' => 'Fortnightly - Every Two Weeks',
                'last_day_of_period' => 'Friday',  // Fortnight ends on Friday
                'biweekly_date' => '2026-01-09',   // Anchor date (a Friday) - calculates every 14 days from this
                'last_day_of_month' => null,       // Not needed for fortnightly
                'go_further_back' => 1,
                'years_back' => '2',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 7,
                'pay_frequency' => 'Monthly - Once a Month',
                'last_day_of_period' => null,      // Not needed for monthly
                'biweekly_date' => null,           // Not needed for monthly
                'last_day_of_month' => 25,         // Payday is 25th of each month
                'go_further_back' => 1,
                'years_back' => '2',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('add_pay_frequencies')->upsert(
            $payFrequencies,
            ['id'],
            [
                'pay_frequency',
                'last_day_of_period',
                'biweekly_date',
                'last_day_of_month',
                'go_further_back',
                'years_back',
                'updated_at',
            ]
        );
    }
}
