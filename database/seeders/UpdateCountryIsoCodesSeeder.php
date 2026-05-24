<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateCountryIsoCodesSeeder extends Seeder
{
    public function run()
    {
        $countries = [
            'Algeria' => 'DZ',
            'Angola' => 'AO',
            'Benin' => 'BJ',
            'Botswana' => 'BW',
            'Burkina Faso' => 'BF',
            'Burundi' => 'BI',
            'Cabo Verde' => 'CV',
            'Cameroon' => 'CM',
            'Central African Republic' => 'CF',
            'Chad' => 'TD',
            'Comoros' => 'KM',
            'Democratic Republic of the Congo' => 'CD',
            'Djibouti' => 'DJ',
            'Egypt' => 'EG',
            'Equatorial Guinea' => 'GQ',
            'Eritrea' => 'ER',
            'Eswatini' => 'SZ',
            'Ethiopia' => 'ET',
            'Gabon' => 'GA',
            'Gambia' => 'GM',
            'Ghana' => 'GH',
            'Guinea' => 'GN',
            'Guinea-Bissau' => 'GW',
            'Ivory Coast' => 'CI', // Côte d’Ivoire
            'Kenya' => 'KE',
            'Lesotho' => 'LS',
            'Liberia' => 'LR',
            'Libya' => 'LY',
            'Madagascar' => 'MG',
            'Malawi' => 'MW',
            'Mali' => 'ML',
            'Mauritania' => 'MR',
            'Mauritius' => 'MU',
            'Morocco' => 'MA',
            'Mozambique' => 'MZ',
            'Namibia' => 'NA',
            'Niger' => 'NE',
            'Nigeria' => 'NG',
            'Republic of the Congo' => 'CG',
            'Rwanda' => 'RW',
            'Sao Tome and Principe' => 'ST',
            'Senegal' => 'SN',
            'Seychelles' => 'SC',
            'Sierra Leone' => 'SL',
            'Somalia' => 'SO',
            'South Africa' => 'ZA',
            'South Sudan' => 'SS',
            'Sudan' => 'SD',
            'Tanzania' => 'TZ',
            'Togo' => 'TG',
            'Tunisia' => 'TN',
            'Uganda' => 'UG',
            'Zambia' => 'ZM',
            'Zimbabwe' => 'ZW',
        ];

        foreach ($countries as $name => $iso) {
            DB::table('countries')
                ->where('name', $name)
                ->update(['iso_code' => $iso]);
        }
    }
}
