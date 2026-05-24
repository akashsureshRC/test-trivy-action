<?php

namespace Database\Seeders\Hrm;

use Database\Seeders\CountryWithIsoSeeder;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new CountryWithIsoSeeder())->run();
    }
}
