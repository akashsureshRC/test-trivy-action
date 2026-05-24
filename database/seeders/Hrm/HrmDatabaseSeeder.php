<?php

namespace Database\Seeders\Hrm;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Nwidart\Modules\Facades\Module;

class HrmDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $this->call(CountrySeeder::class);
        $this->call(ProvinceSeeder::class);

        // $this->call(EmailTemplateTableSeeder::class);
        // $this->call(PermissionTableSeeder::class);
        // $this->call(RoleTableSeeder::class);
        // $this->call(NotificationsTableSeeder::class);
        // $check = Module::find('CustomField');
        // if($check ){
        //     $this->call(CustomFieldListTableSeeder::class);
        // }
        // if(moduleIsActive('AIAssistant'))
        // {
        //     $this->call(AIAssistantTemplateListTableSeeder::class);
        // }
        // if(moduleIsActive('LandingPage'))
        // {
        //     $this->call(MarketPlaceSeederTableSeeder::class);
        // }
    }
}
