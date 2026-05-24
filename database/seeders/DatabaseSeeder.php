<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(NotificationsTableSeeder::class);
        $this->call(PermissionTableSeeder::class);
        
        // HRM Seeders (merged into core - no module check needed)
        $this->call(\Database\Seeders\Hrm\PermissionTableSeeder::class);
        $this->call(\Database\Seeders\Hrm\CountrySeeder::class);
        $this->call(\Database\Seeders\Hrm\ProvinceSeeder::class);
        $this->call(\Database\Seeders\Hrm\PayFrequencySeeder::class);
        $this->call(\Database\Seeders\Hrm\EmailTemplateTableSeeder::class);
        
        $this->call(UserSeeder::class);
        
        // HRM Role seeder (needs to run after UserSeeder creates company users)
        $this->call(\Database\Seeders\Hrm\RoleTableSeeder::class);
        
        $this->call(DefultSetting::class);
        $this->call(LanguageTableSeeder::class);
        if(moduleIsActive('AIAssistant'))
        {
            $this->call(AIAssistantTemplateListTableSeeder::class);
        }
    }
}
