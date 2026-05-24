<?php

namespace Database\Seeders\Hrm;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Hrm\Country;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $provincesByCountry = [
            'Algeria' => ['Adrar', 'Chlef', 'Laghouat', 'Oum El Bouaghi', 'Batna', 'Bejaia', 'Biskra', 'Blida', 'Bouira', 'Tamanrasset', 'Tebessa', 'Tlemcen', 'Algiers', 'Oran', 'Constantine', 'Annaba'],
            'Angola' => ['Bengo', 'Benguela', 'Bie', 'Cabinda', 'Cuando Cubango', 'Cuanza Norte', 'Cuanza Sul', 'Cunene', 'Huambo', 'Huila', 'Luanda', 'Lunda Norte', 'Lunda Sul', 'Malanje', 'Moxico', 'Namibe', 'Uige', 'Zaire'],
            'Benin' => ['Alibori', 'Atakora', 'Atlantique', 'Borgou', 'Collines', 'Couffo', 'Donga', 'Littoral', 'Mono', 'Oueme', 'Plateau', 'Zou'],
            'Botswana' => ['Central', 'Chobe', 'Ghanzi', 'Kgalagadi', 'Kgatleng', 'Kweneng', 'North-East', 'North-West', 'South-East', 'Southern'],
            'Burkina Faso' => ['Boucle du Mouhoun', 'Cascades', 'Centre', 'Centre-Est', 'Centre-Nord', 'Centre-Ouest', 'Centre-Sud', 'Est', 'Hauts-Bassins', 'Nord', 'Plateau-Central', 'Sahel', 'Sud-Ouest'],
            'Burundi' => ['Bujumbura Mairie', 'Bujumbura Rural', 'Bubanza', 'Bururi', 'Cankuzo', 'Cibitoke', 'Gitega', 'Karuzi', 'Kayanza', 'Kirundo', 'Makamba', 'Muramvya', 'Muyinga', 'Mwaro', 'Ngozi', 'Rutana', 'Ruyigi'],
            'Cabo Verde' => ['Boa Vista', 'Brava', 'Fogo', 'Maio', 'Sal', 'Santiago', 'Santo Antao', 'Sao Nicolau', 'Sao Vicente'],
            'Cameroon' => ['Adamawa', 'Centre', 'East', 'Far North', 'Littoral', 'North', 'North-West', 'West', 'South', 'South-West'],
            'Central African Republic' => ['Bamingui-Bangoran', 'Bangui', 'Basse-Kotto', 'Haute-Kotto', 'Haut-Mbomou', 'Kemo', 'Lobaye', 'Mambere-Kadei', 'Mbomou', 'Nana-Grebizi', 'Nana-Mambere', 'Ombella-MPoko', 'Ouaka', 'Ouham', 'Ouham-Pende', 'Sangha-Mbaere', 'Vakaga'],
            'Chad' => ['Bahr El Gazel', 'Batha', 'Borkou', 'Chari-Baguirmi', 'Ennedi-Est', 'Ennedi-Ouest', 'Guera', 'Hadjer-Lamis', 'Kanem', 'Lac', 'Logone Occidental', 'Logone Oriental', 'Mandoul', 'Mayo-Kebbi Est', 'Mayo-Kebbi Ouest', 'Moyen-Chari', 'Ndjamena', 'Ouaddai', 'Salamat', 'Sila', 'Tandjile', 'Tibesti', 'Wadi Fira'],
            'Comoros' => ['Anjouan', 'Grande Comore', 'Moheli'],
            'Democratic Republic of the Congo' => ['Bas-Uele', 'Equateur', 'Haut-Katanga', 'Haut-Lomami', 'Haut-Uele', 'Ituri', 'Kasai', 'Kasai-Central', 'Kasai-Oriental', 'Kinshasa', 'Kongo-Central', 'Kwango', 'Kwilu', 'Lomami', 'Lualaba', 'Mai-Ndombe', 'Maniema', 'Mongala', 'Nord-Kivu', 'Nord-Ubangi', 'Sankuru', 'South-Kivu', 'Sud-Ubangi', 'Tanganyika', 'Tshopo', 'Tshuapa'],
            'Djibouti' => ['Ali Sabieh', 'Arta', 'Dikhil', 'Djibouti', 'Obock', 'Tadjourah'],
            'Egypt' => ['Alexandria', 'Aswan', 'Asyut', 'Beheira', 'Beni Suef', 'Cairo', 'Dakahlia', 'Damietta', 'Faiyum', 'Gharbia', 'Giza', 'Ismailia', 'Kafr El Sheikh', 'Luxor', 'Matrouh', 'Minya', 'Monufia', 'New Valley', 'North Sinai', 'Port Said', 'Qalyubia', 'Qena', 'Red Sea', 'Sharqia', 'Sohag', 'South Sinai', 'Suez'],
            'Equatorial Guinea' => ['Annobon', 'Bioko Norte', 'Bioko Sur', 'Centro Sur', 'Kie-Ntem', 'Litoral', 'Wele-Nzas'],
            'Eritrea' => ['Anseba', 'Debub', 'Gash-Barka', 'Maekel', 'Northern Red Sea', 'Southern Red Sea'],
            'Eswatini' => ['Hhohho', 'Lubombo', 'Manzini', 'Shiselweni'],
            'Ethiopia' => ['Addis Ababa', 'Afar', 'Amhara', 'Benishangul-Gumuz', 'Dire Dawa', 'Gambela', 'Harari', 'Oromia', 'Sidama', 'Somali', 'South West Ethiopia', 'Southern Nations Nationalities and Peoples', 'Tigray'],
            'Gabon' => ['Estuaire', 'Haut-Ogooue', 'Moyen-Ogooue', 'Ngounie', 'Nyanga', 'Ogooue-Ivindo', 'Ogooue-Lolo', 'Ogooue-Maritime', 'Woleu-Ntem'],
            'Gambia' => ['Banjul', 'Central River', 'Lower River', 'North Bank', 'Upper River', 'West Coast'],
            'Ghana' => ['Ahafo', 'Ashanti', 'Bono', 'Bono East', 'Central', 'Eastern', 'Greater Accra', 'Northern', 'North East', 'Oti', 'Savannah', 'Upper East', 'Upper West', 'Volta', 'Western', 'Western North'],
            'Guinea' => ['Boke', 'Conakry', 'Faranah', 'Kankan', 'Kindia', 'Labe', 'Mamou', 'Nzerekore'],
            'Guinea-Bissau' => ['Bafata', 'Biombo', 'Bissau', 'Bolama', 'Cacheu', 'Gabu', 'Oio', 'Quinara', 'Tombali'],
            'Ivory Coast' => ['Abidjan', 'Bas-Sassandra', 'Comoe', 'Denguele', 'Fromager', 'Goh-Djiboua', 'Lacs', 'Lagunes', 'Montagnes', 'Sassandra-Marahoue', 'Savanes', 'Vallee du Bandama', 'Woroba', 'Yamoussoukro', 'Zanzan'],
            'Kenya' => ['Central', 'Coast', 'Eastern', 'Nairobi', 'North Eastern', 'Nyanza', 'Rift Valley', 'Western'],
            'Lesotho' => ['Berea', 'Butha-Buthe', 'Leribe', 'Mafeteng', 'Maseru', 'Mohale\'s Hoek', 'Mokhotlong', 'Qacha\'s Nek', 'Quthing', 'Thaba-Tseka'],
            'Liberia' => ['Bomi', 'Bong', 'Gbarpolu', 'Grand Bassa', 'Grand Cape Mount', 'Grand Gedeh', 'Grand Kru', 'Lofa', 'Margibi', 'Maryland', 'Montserrado', 'Nimba', 'River Cess', 'River Gee', 'Sinoe'],
            'Libya' => ['Al Butnan', 'Al Jabal al Akhdar', 'Al Jabal al Gharbi', 'Al Jfara', 'Al Kufrah', 'Al Marj', 'Al Wahat', 'An Nuqat al Khams', 'Az Zawiyah', 'Benghazi', 'Derna', 'Ghat', 'Jafara', 'Jufra', 'Misrata', 'Murqub', 'Nalut', 'Sabha', 'Sirte', 'Tripoli', 'Wadi al Hayaa', 'Wadi al Shatii'],
            'Madagascar' => ['Alaotra-Mangoro', 'Analamanga', 'Analanjirofo', 'Androy', 'Anosy', 'Atsimo-Andrefana', 'Atsimo-Atsinanana', 'Atsinanana', 'Betsiboka', 'Boeny', 'Bongolava', 'Diana', 'Fitovinany', 'Haute Matsiatra', 'Ihorombe', 'Itasy', 'Melaky', 'Menabe', 'Sava', 'Sofia', 'Vakinankaratra', 'Vatovavy'],
            'Malawi' => ['Central Region', 'Northern Region', 'Southern Region', 'Balaka', 'Blantyre', 'Chikwawa', 'Dedza', 'Karonga', 'Lilongwe', 'Mangochi', 'Mzuzu', 'Ntcheu', 'Zomba'],
            'Mali' => ['Bamako', 'Gao', 'Kayes', 'Kidal', 'Koulikoro', 'Menaka', 'Mopti', 'Segou', 'Sikasso', 'Taoudenit', 'Tombouctou'],
            'Mauritania' => ['Adrar', 'Assaba', 'Brakna', 'Dakhlet Nouadhibou', 'Gorgol', 'Guidimaka', 'Hodh Ech Chargui', 'Hodh El Gharbi', 'Inchiri', 'Nouakchott-Nord', 'Nouakchott-Ouest', 'Nouakchott-Sud', 'Tagant', 'Tiris Zemmour', 'Trarza'],
            'Mauritius' => ['Black River', 'Flacq', 'Grand Port', 'Moka', 'Pamplemousses', 'Plaines Wilhems', 'Port Louis', 'Riviere du Rempart', 'Savanne', 'Rodrigues'],
            'Morocco' => ['Beni Mellal-Khenifra', 'Casablanca-Settat', 'Dakhla-Oued Ed-Dahab', 'Draa-Tafilalet', 'Fes-Meknes', 'Guelmim-Oued Noun', 'Laayoune-Sakia El Hamra', 'Marrakesh-Safi', 'Oriental', 'Rabat-Sale-Kenitra', 'Souss-Massa', 'Tanger-Tetouan-Al Hoceima'],
            'Mozambique' => ['Cabo Delgado', 'Gaza', 'Inhambane', 'Manica', 'Maputo', 'Maputo City', 'Nampula', 'Niassa', 'Sofala', 'Tete', 'Zambezia'],
            'Namibia' => ['Erongo', 'Hardap', 'Karas', 'Kavango East', 'Kavango West', 'Khomas', 'Kunene', 'Ohangwena', 'Omaheke', 'Omusati', 'Oshana', 'Oshikoto', 'Otjozondjupa', 'Zambezi'],
            'Niger' => ['Agadez', 'Diffa', 'Dosso', 'Maradi', 'Niamey', 'Tahoua', 'Tillaberi', 'Zinder'],
            'Nigeria' => ['Abia', 'Adamawa', 'Akwa Ibom', 'Anambra', 'Bauchi', 'Bayelsa', 'Benue', 'Borno', 'Cross River', 'Delta', 'Ebonyi', 'Edo', 'Ekiti', 'Enugu', 'FCT Abuja', 'Gombe', 'Imo', 'Jigawa', 'Kaduna', 'Kano', 'Katsina', 'Kebbi', 'Kogi', 'Kwara', 'Lagos', 'Nasarawa', 'Niger', 'Ogun', 'Ondo', 'Osun', 'Oyo', 'Plateau', 'Rivers', 'Sokoto', 'Taraba', 'Yobe', 'Zamfara'],
            'Republic of the Congo' => ['Bouenza', 'Brazzaville', 'Cuvette', 'Cuvette-Ouest', 'Kouilou', 'Lekoumou', 'Likouala', 'Niari', 'Plateaux', 'Pointe-Noire', 'Pool', 'Sangha'],
            'Rwanda' => ['Eastern Province', 'Kigali City', 'Northern Province', 'Southern Province', 'Western Province'],
            'Sao Tome and Principe' => ['Agua Grande', 'Cantagalo', 'Caué', 'Lembá', 'Lobata', 'Mé-Zóchi', 'Pagué'],
            'Senegal' => ['Dakar', 'Diourbel', 'Fatick', 'Kaffrine', 'Kaolack', 'Kedougou', 'Kolda', 'Louga', 'Matam', 'Saint-Louis', 'Sedhiou', 'Tambacounda', 'Thies', 'Ziguinchor'],
            'Seychelles' => ['Anse aux Pins', 'Anse Boileau', 'Anse Etoile', 'Anse Royale', 'Baie Lazare', 'Baie Sainte Anne', 'Beau Vallon', 'Bel Air', 'Bel Ombre', 'Cascade', 'English River', 'Grand Anse Mahe', 'Grand Anse Praslin', 'La Digue and Inner Islands', 'Mont Buxton', 'Mont Fleuri', 'Plaisance', 'Pointe La Rue', 'Port Glaud', 'Saint Louis', 'Takamaka'],
            'Sierra Leone' => ['Eastern Province', 'North Eastern Province', 'North Western Province', 'Northern Province', 'Southern Province', 'Western Area Rural', 'Western Area Urban'],
            'Somalia' => ['Awdal', 'Bakool', 'Banadir', 'Bari', 'Bay', 'Galguduud', 'Gedo', 'Hiran', 'Lower Juba', 'Lower Shabelle', 'Middle Juba', 'Middle Shabelle', 'Mudug', 'Nugal', 'Sanaag', 'Togdheer'],
            'South Africa' => ['Eastern Cape', 'Free State', 'Gauteng', 'KwaZulu-Natal', 'Limpopo', 'Mpumalanga', 'North West', 'Northern Cape', 'Western Cape'],
            'South Sudan' => ['Central Equatoria', 'Eastern Equatoria', 'Jonglei', 'Lakes', 'Northern Bahr el Ghazal', 'Unity', 'Upper Nile', 'Warrap', 'Western Bahr el Ghazal', 'Western Equatoria'],
            'Sudan' => ['Blue Nile', 'Central Darfur', 'East Darfur', 'Gedaref', 'Gezira', 'Kassala', 'Khartoum', 'North Darfur', 'North Kordofan', 'Northern', 'Red Sea', 'River Nile', 'Sennar', 'South Darfur', 'South Kordofan', 'West Darfur', 'West Kordofan', 'White Nile'],
            'Tanzania' => ['Arusha', 'Dar es Salaam', 'Dodoma', 'Geita', 'Iringa', 'Kagera', 'Katavi', 'Kigoma', 'Kilimanjaro', 'Lindi', 'Manyara', 'Mara', 'Mbeya', 'Morogoro', 'Mtwara', 'Mwanza', 'Njombe', 'Pemba North', 'Pemba South', 'Pwani', 'Rukwa', 'Ruvuma', 'Shinyanga', 'Simiyu', 'Singida', 'Songwe', 'Tabora', 'Tanga', 'Zanzibar North', 'Zanzibar South', 'Zanzibar West'],
            'Togo' => ['Centrale', 'Kara', 'Maritime', 'Plateaux', 'Savanes'],
            'Tunisia' => ['Ariana', 'Beja', 'Ben Arous', 'Bizerte', 'Gabes', 'Gafsa', 'Jendouba', 'Kairouan', 'Kasserine', 'Kebili', 'Kef', 'Mahdia', 'Manouba', 'Medenine', 'Monastir', 'Nabeul', 'Sfax', 'Sidi Bouzid', 'Siliana', 'Sousse', 'Tataouine', 'Tozeur', 'Tunis', 'Zaghouan'],
            'Uganda' => ['Central Region', 'Eastern Region', 'Northern Region', 'Western Region', 'Kampala'],
            'Zambia' => ['Central', 'Copperbelt', 'Eastern', 'Luapula', 'Lusaka', 'Muchinga', 'Northern', 'North-Western', 'Southern', 'Western'],
            'Zimbabwe' => ['Bulawayo', 'Harare', 'Manicaland', 'Mashonaland Central', 'Mashonaland East', 'Mashonaland West', 'Masvingo', 'Matabeleland North', 'Matabeleland South', 'Midlands'],
        ];

        $countryIds = Country::query()->pluck('id', 'name');

        foreach ($provincesByCountry as $countryName => $provinces) {
            $countryId = $countryIds->get($countryName);

            if (!$countryId) {
                continue;
            }

            foreach (array_unique($provinces) as $provinceName) {
                DB::table('provinces')->updateOrInsert(
                    [
                        'name' => $provinceName,
                        'country_id' => $countryId,
                    ],
                    [
                        'status' => 'Active',
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }
    }
}

