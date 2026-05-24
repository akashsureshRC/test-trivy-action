<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Language;
use App\Models\Setting;
use App\Models\User;


class DefultSetting extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // admin settings
            $admin = User::where('type','super admin')->first();
            if (empty($admin)) {
                return;
            }
            $admin_setting = [
                "defult_language" => "en",
                "defult_timezone" => "Africa/Johannesburg",
                "title_text" => !empty(env('APP_NAME')) ? env('APP_NAME') : 'RC ClearPay',
                "footer_text" => "Copyright © ".(!empty(env('APP_NAME')) ? env('APP_NAME') : 'RC ClearPay'),
                "site_rtl" => "off",
                "cust_darklayout" => "off",
                "site_transparent" => "on",
                "signup" => "on",
                "color" => "theme-1",

                'email_verification'=>'on',

                // for cookie
                'enable_cookie'=>'on',
                'necessary_cookies'=>'on',
                'cookie_logging'=>'on',
                'cookie_title'=>'We use cookies!',
                'cookie_description'=>'Hi, this website uses essential cookies to ensure its proper operation and tracking cookies to understand how you interact with it',
                'strictly_cookie_title'=>'Strictly necessary cookies',
                'strictly_cookie_description'=>'These cookies are essential for the proper functioning of my website. Without these cookies, the website would not work properly',
                'more_information_description'=>'For any queries in relation to our policy on cookies and your choices, please contact us',
                'contactus_url'=>'#',

                // for cookie

                "meta_title" => "RC ClearPay - SARS Compliant Payroll Software",
                "meta_keywords" => "RC ClearPay, ClearPay, Payroll Software, Payroll System, SARS Compliant Payroll Software",
                "meta_description" => "Simplify your payroll operations with our automated, SARS-compliant platform, built for South African businesses.",

                "storage_setting" => "local",

                // for email setting
                "email_setting" => "smtp",
            ];
            foreach($admin_setting as $key =>  $value){
                Setting::updateOrCreate(
                    [
                        'key' => $key,
                        'workspace' => 0,
                        'created_by' => $admin->id,
                    ],
                    [
                        'value' => $value,
                    ]
                );
            }
        // admin settings End

    }
}
