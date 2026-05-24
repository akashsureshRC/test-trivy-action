<?php

namespace Database\Seeders;

use App\Models\TaxYear;
use Illuminate\Database\Seeder;

class TaxYearSeeder extends Seeder
{
    /**
     * Seed the 2025/2026 SA tax year with current SARS values.
     * Locked by default so existing payroll continues working immediately.
     */
    public function run(): void
    {
        TaxYear::updateOrCreate(
            ['label' => '2025/2026'],
            [
                'effective_from' => '2025-03-01',
                'effective_to'   => '2026-02-28',

                'tax_brackets' => [
                    ['min' => 1,       'max' => 237100,       'base_tax' => 0,      'rate' => 0.18, 'threshold' => 0],
                    ['min' => 237101,  'max' => 370500,       'base_tax' => 42678,  'rate' => 0.26, 'threshold' => 237100],
                    ['min' => 370501,  'max' => 512800,       'base_tax' => 77362,  'rate' => 0.31, 'threshold' => 370500],
                    ['min' => 512801,  'max' => 673000,       'base_tax' => 121475, 'rate' => 0.36, 'threshold' => 512800],
                    ['min' => 673001,  'max' => 857900,       'base_tax' => 179147, 'rate' => 0.39, 'threshold' => 673000],
                    ['min' => 857901,  'max' => 1817000,      'base_tax' => 251258, 'rate' => 0.41, 'threshold' => 857900],
                    ['min' => 1817001, 'max' => 99999999999,  'base_tax' => 644489, 'rate' => 0.45, 'threshold' => 1817000],
                ],

                'primary_rebate'      => 17235,
                'secondary_rebate'    => 9444,
                'tertiary_rebate'     => 3145,
                'secondary_rebate_age' => 65,
                'tertiary_rebate_age'  => 75,

                'uif_rate'    => 0.01,
                'uif_ceiling' => 177.12,
                'sdl_rate'    => 0.01,

                'eti_min_age'    => 18,
                'eti_max_age'    => 29,
                'eti_salary_cap' => 6500,
                'eti_max_amount' => 1000,
                'eti_rate'       => 0.50,

                'ot_multiplier'             => 1.50,
                'medical_aid_tax_rate'      => 0.10,
                'travel_allowance_tax_rate' => 0.10,

                'is_locked' => true,
                'locked_by' => null,
                'locked_at' => now(),
            ]
        );
    }
}
