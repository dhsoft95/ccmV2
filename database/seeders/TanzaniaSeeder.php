<?php
namespace Database\Seeders;

use App\Models\districts;
use App\Models\regions;
use App\Models\village;
use App\Models\ward;
use Illuminate\Database\Seeder;

class TanzaniaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedDefaultVillage();
        $this->seedRegionsAndDistricts();
        $this->seedSampleVillages();
        $this->seedSampleWards();
    }

    /**
     * Seed the default village with ID=1
     * This is necessary because wards table references village with ID=1 as default
     */
    private function seedDefaultVillage(): void
    {
        if (!village::find(1)) {
            echo "Creating default village...\n";

            // First create a default region if it doesn't exist
            $defaultRegion = regions::firstOrCreate(['name' => 'Default Region']);

            // Then create a default district
            $defaultDistrict = districts::firstOrCreate([
                'name' => 'Default District',
                'region_id' => $defaultRegion->id
            ], [
                'other_district_details' => 'Default district for system use'
            ]);

            // Create the default village with ID=1
            village::create([
                'id' => 1,
                'name' => 'Default Village',
                'region_id' => $defaultRegion->id,
                'district_id' => $defaultDistrict->id,
                'other_villages_details' => 'Default village for system use'
            ]);

            echo "Default village created.\n";
        }
    }

    /**
     * Seed all Tanzania regions and their districts
     * Complete list of all 31 regions with their 184 districts as of 2024
     */
    private function seedRegionsAndDistricts(): void
    {
        $tanzaniaData = [
            'Arusha' => [
                'Arusha City', 'Arusha Rural', 'Karatu', 'Longido', 'Meru', 'Monduli', 'Ngorongoro'
            ],
            'Dar es Salaam' => [
                'Ilala Municipal', 'Kigamboni Municipal', 'Kinondoni Municipal', 'Temeke Municipal', 'Ubungo Municipal'
            ],
            'Dodoma' => [
                'Bahi', 'Chamwino', 'Chemba', 'Dodoma City', 'Kondoa Rural', 'Kondoa Town', 'Kongwa', 'Mpwapwa'
            ],
            'Geita' => [
                'Bukombe', 'Chato', 'Geita Rural', 'Geita Town', 'Mbogwe', 'Nyang\'hwale'
            ],
            'Iringa' => [
                'Iringa Municipal', 'Iringa Rural', 'Kilolo', 'Mafinga Town', 'Mufindi'
            ],
            'Kagera' => [
                'Biharamulo', 'Bukoba Municipal', 'Bukoba Rural', 'Karagwe', 'Kyerwa', 'Missenyi', 'Muleba', 'Ngara'
            ],
            'Kaskazini Pemba' => [
                'Micheweni', 'Wete'
            ],
            'Kaskazini Unguja' => [
                'Kaskazini A Town', 'Kaskazini B'
            ],
            'Katavi' => [
                'Mlele', 'Mpanda Municipal', 'Mpimbwe', 'Nsimbo', 'Tanganyika'
            ],
            'Kigoma' => [
                'Buhigwe', 'Kakonko', 'Kasulu Rural', 'Kasulu Town', 'Kibondo', 'Kigoma Municipal', 'Kigoma Rural', 'Uvinza'
            ],
            'Kilimanjaro' => [
                'Hai', 'Moshi Municipal', 'Moshi Rural', 'Mwanga', 'Rombo', 'Same', 'Siha'
            ],
            'Kusini Pemba' => [
                'Chake Chake', 'Mkoani Town'
            ],
            'Kusini Unguja' => [
                'Kati', 'Kusini'
            ],
            'Lindi' => [
                'Kilwa', 'Lindi Municipal', 'Liwale', 'Mtama', 'Nachingwea', 'Ruangwa'
            ],
            'Manyara' => [
                'Babati Rural', 'Babati Town', 'Hanang', 'Kiteto', 'Mbulu Rural', 'Mbulu Town', 'Simanjiro'
            ],
            'Mara' => [
                'Bunda Rural', 'Bunda Town', 'Butiama', 'Musoma Municipal', 'Musoma Rural', 'Rorya', 'Serengeti', 'Tarime Rural', 'Tarime Town'
            ],
            'Mbeya' => [
                'Busekelo', 'Chunya', 'Kyela', 'Mbarali', 'Mbeya City', 'Mbeya Rural', 'Rungwe'
            ],
            'Mjini Magharibi' => [
                'Magharibi A Municipal', 'Magharibi B Municipal', 'Mjini Municipal'
            ],
            'Morogoro' => [
                'Gairo', 'Ifakara Town', 'Kilosa', 'Malinyi', 'Mlimba', 'Morogoro Municipal', 'Morogoro Rural', 'Mvomero', 'Ulanga'
            ],
            'Mtwara' => [
                'Masasi Rural', 'Masasi Town', 'Mtwara Municipal', 'Mtwara Rural', 'Nanyamba Town', 'Nanyumbu', 'Newala Rural', 'Newala Town', 'Tandahimba'
            ],
            'Mwanza' => [
                'Buchosa', 'Ilemela Municipal', 'Kwimba', 'Magu', 'Misungwi', 'Mwanza', 'Sengerema', 'Ukerewe'
            ],
            'Njombe' => [
                'Ludewa', 'Makambako Town', 'Makete', 'Njombe Rural', 'Njombe Town', 'Wanging\'ombe'
            ],
            'Pwani' => [
                'Bagamoyo', 'Chalinze', 'Kibaha', 'Kibaha Town', 'Kibiti', 'Kisarawe', 'Mafia', 'Mkuranga', 'Rufiji'
            ],
            'Rukwa' => [
                'Kalambo', 'Nkasi', 'Sumbawanga Municipal', 'Sumbawanga Rural'
            ],
            'Ruvuma' => [
                'Madaba', 'Mbinga Rural', 'Mbinga Town', 'Namtumbo', 'Nyasa', 'Songea Municipal', 'Songea Rural', 'Tunduru'
            ],
            'Shinyanga' => [
                'Kahama Municipality', 'Kishapu', 'Msalala', 'Shinyanga Municipal', 'Shinyanga Rural', 'Ushetu'
            ],
            'Simiyu' => [
                'Bariadi Rural', 'Bariadi Town', 'Busega', 'Itilima', 'Maswa', 'Meatu'
            ],
            'Singida' => [
                'Ikungi', 'Iramba', 'Itigi', 'Manyoni', 'Mkalama', 'Singida Municipal', 'Singida Rural'
            ],
            'Songwe' => [
                'Ileje', 'Mbozi', 'Momba', 'Songwe', 'Tunduma Town'
            ],
            'Tabora' => [
                'Igunga', 'Kaliua', 'Nzega Rural', 'Nzega Town', 'Sikonge', 'Tabora Municipal', 'Urambo', 'Uyui'
            ],
            'Tanga' => [
                'Bumbuli', 'Handeni Rural', 'Handeni Town', 'Kilindi', 'Korogwe Rural', 'Korogwe Town', 'Lushoto', 'Mkinga', 'Muheza', 'Pangani', 'Tanga City'
            ]
        ];

        echo "Seeding Tanzania regions and districts...\n";

        foreach ($tanzaniaData as $regionName => $districts) {
            echo "Creating region: {$regionName}\n";

            $region = regions::create([
                'name' => $regionName,
            ]);

            foreach ($districts as $districtName) {
                echo "  - Creating district: {$districtName}\n";

                districts::create([
                    'region_id' => $region->id,
                    'name' => $districtName,
                    'other_district_details' => "District of {$districtName} in {$regionName} region"
                ]);
            }
        }

        echo "Successfully seeded " . regions::count() . " regions and " . districts::count() . " districts.\n";
    }

    /**
     * Seed sample villages for districts that will have wards
     */
    private function seedSampleVillages(): void
    {
        echo "Seeding sample villages...\n";

        // Major districts where we'll create wards (expanded list)
        $districtsWithWards = [
            // Dar es Salaam
            'Ilala Municipal', 'Kinondoni Municipal', 'Temeke Municipal', 'Ubungo Municipal', 'Kigamboni Municipal',

            // Major cities
            'Arusha City', 'Dodoma City', 'Mbeya City', 'Tanga City',

            // Major urban centers
            'Moshi Municipal', 'Iringa Municipal', 'Bukoba Municipal', 'Kigoma Municipal',
            'Morogoro Municipal', 'Mtwara Municipal', 'Shinyanga Municipal', 'Singida Municipal',
            'Tabora Municipal', 'Sumbawanga Municipal', 'Songea Municipal',

            // Mwanza districts
            'Ilemela Municipal', 'Mwanza',

            // Zanzibar
            'Mjini Municipal', 'Magharibi A Municipal', 'Magharibi B Municipal',

            // Other major towns
            'Musoma Municipal', 'Kasulu Town', 'Babati Town', 'Makambako Town',
            'Njombe Town', 'Kibaha Town', 'Geita Town', 'Mafinga Town',

            // Special case - Rungwe (since you had its ward data)
            'Rungwe'
        ];

        foreach ($districtsWithWards as $districtName) {
            $district = districts::where('name', $districtName)->first();

            if ($district) {
                echo "Creating sample village for {$districtName}\n";

                village::create([
                    'name' => "{$districtName} Central Village",
                    'region_id' => $district->region_id,
                    'district_id' => $district->id,
                    'other_villages_details' => "Central village for {$districtName} district"
                ]);
            }
        }

        echo "Successfully seeded sample villages.\n";
    }

    /**
     * Seed sample wards for major cities/districts
     */
    private function seedSampleWards(): void
    {
        echo "Seeding sample wards...\n";

        $wardData = [
            // Dar es Salaam wards
            'Ilala Municipal' => [
                'Buguruni', 'Gerezani', 'Ilala', 'Jangwani', 'Kariakoo', 'Kisutu', 'Mchikichini', 'Upanga East', 'Upanga West'
            ],
            'Kinondoni Municipal' => [
                'Hananasif', 'Kawe', 'Kinondoni', 'Mabibo', 'Magomeni', 'Makongo', 'Manzese', 'Msasani', 'Sinza'
            ],
            'Temeke Municipal' => [
                'Chang\'ombe', 'Keko', 'Kurasini', 'Mbagala', 'Miburani', 'Mtoni', 'Sandali', 'Temeke', 'Vijibweni'
            ],
            'Ubungo Municipal' => [
                'Goba', 'Kibamba', 'Kimara', 'Makongo Juu', 'Manzese', 'Mwenge', 'Saranga', 'Ubungo'
            ],
            'Kigamboni Municipal' => [
                'Kigamboni', 'Kibada', 'Kisarawe II', 'Mjimwema', 'Somangila', 'Tungi'
            ],

            // Arusha wards
            'Arusha City' => [
                'Daraja Mbili', 'Elerai', 'Engutoto', 'Kaloleni', 'Kati', 'Kimandolu', 'Levolosi', 'Ngarenaro', 'Sekei', 'Sokon I', 'Sokon II', 'Themi'
            ],

            // Mwanza wards
            'Ilemela Municipal' => [
                'Buhongwa', 'Ibungilo', 'Ilemela', 'Kitangiri', 'Ngudu', 'Pasiansi'
            ],
            'Mwanza' => [
                'Bugando', 'Buzuruga', 'Igogo', 'Mahina', 'Mwanza', 'Nyakato', 'Nyamanoro', 'Pamba'
            ],

            // Dodoma wards
            'Dodoma City' => [
                'Chang\'ombe', 'Chihanga', 'Iyumbu', 'Kiwanja cha Ndege', 'Makole', 'Mkonze', 'Msalato', 'Nala', 'Uhuru', 'Zuzu'
            ],

            // Mbeya wards
            'Mbeya City' => [
                'Forest', 'Ghana', 'Itende', 'Iwambi', 'Mwanjelwa', 'Nzovwe', 'Sisimba', 'Soweto'
            ],

            // Moshi wards
            'Moshi Municipal' => [
                'Bondeni', 'Kiboriloni', 'Kilimanjaro', 'Kiusa', 'Korongoni', 'Longuo', 'Majengo', 'Mabogini', 'Msaranga', 'Rau'
            ],

            // Iringa wards
            'Iringa Municipal' => [
                'Gangilonga', 'Kihesa', 'Kitanzini', 'Mivinjeni', 'Mkwawa', 'Ruaha'
            ],

            // Bukoba wards
            'Bukoba Municipal' => [
                'Hamugembe', 'Ijuganyondo', 'Kahororo', 'Karabagaine', 'Nyakaiga', 'Nyakato'
            ],

            // Kigoma wards
            'Kigoma Municipal' => [
                'Bangwe', 'Gungu', 'Kalalangabo', 'Katubuka', 'Kigoma', 'Mahembe', 'Mwanga Kaskazini', 'Mwanga Kusini'
            ],

            // Tanga wards
            'Tanga City' => [
                'Central', 'Chumbageni', 'Hospital', 'Makorora', 'Mzizima', 'Ngamiani Kaskazini', 'Ngamiani Kusini', 'Usagara'
            ],

            // Morogoro wards
            'Morogoro Municipal' => [
                'Boma', 'Kihonda', 'Kingolwira', 'Mazimbu', 'Mfukulembe', 'Mji Mkuu', 'Mwembesongo', 'Sabasaba'
            ],

            // Mtwara wards
            'Mtwara Municipal' => [
                'Chumbageni', 'Kombeni', 'Majengo', 'Mchinga', 'Msimbazi', 'Mtanda', 'Shangani', 'Shimo la Udongo'
            ],

            // Shinyanga wards
            'Shinyanga Municipal' => [
                'Ibadakuli', 'Kambarage', 'Kitangiri', 'Kolandoto', 'Mabuki', 'Majengo', 'Ndoleleji', 'Shinyanga'
            ],

            // Singida wards
            'Singida Municipal' => [
                'Mtipa', 'Mwasauya', 'Ndevelya', 'Singida', 'Uhambo'
            ],

            // Tabora wards
            'Tabora Municipal' => [
                'Cheyo', 'Gongoni', 'Ipala', 'Isevya', 'Kanyenye', 'Kiloleni', 'Ng\'ambo', 'Tumbi'
            ],

            // Zanzibar wards
            'Mjini Municipal' => [
                'Funguni', 'Forodhani', 'Hurumzi', 'Jang\'ombe', 'Karakana', 'Kikwajuni', 'Kiponda', 'Kwahani', 'Malindi', 'Mchangani', 'Meya', 'Mikunguni', 'Mlandege', 'Muembe Makumbi', 'Muembe Wambaa', 'Rahaleo', 'Shangani', 'Shaurimoyo', 'Stone Town', 'Tomondo', 'Vikokotoni'
            ],

            // Rungwe wards (as provided by user)
            'Rungwe' => [
                'Bagamoyo', 'Bujela', 'Bulyaga', 'Ibighi', 'Ikama', 'Ikuti',
                'Ilima', 'Iponjela', 'Isongole', 'Itagata', 'Kawetele', 'Kikole',
                'Kinyala', 'Kisiba', 'Kisondela', 'Kiwira', 'Kyimo', 'Lufingo',
                'Lupepo', 'Makandana', 'Malindo', 'Masebe', 'Masoko', 'Masukulu',
                'Matwebe', 'Mpuguso', 'Msasani', 'Ndato', 'Nkunga', 'Suma', 'Swaya'
            ],

            // Additional major towns
            'Musoma Municipal' => [
                'Bweri', 'Iringo', 'Kihumbu', 'Makoko', 'Mwisenge', 'Nyamatare', 'Nyasho', 'Pasiansi'
            ],

            'Sumbawanga Municipal' => [
                'Katandala', 'Maendeleo', 'Mapanda', 'Mwanga', 'Ntendo', 'Wampembe'
            ],

            'Songea Municipal' => [
                'Kigonsera', 'Kichangani', 'Mahaba', 'Matogoro', 'Mzinga', 'Raha'
            ]
        ];

        foreach ($wardData as $districtName => $wards) {
            $district = districts::where('name', $districtName)->first();

            if ($district) {
                echo "Creating wards for {$districtName}:\n";

                // Get the village for this district
                $village = village::where('district_id', $district->id)->first();

                if (!$village) {
                    echo "  - No village found for {$districtName}, skipping wards\n";
                    continue;
                }

                echo "  - Using village: {$village->name}\n";

                foreach ($wards as $wardName) {
                    echo "  - Creating ward: {$wardName}\n";

                    ward::create([
                        'name' => $wardName,
                        'region_id' => $district->region_id,
                        'district_id' => $district->id,
                        'village_id' => $village->id,
                        'other_villages_details' => "Ward of {$wardName} in {$districtName} district"
                    ]);
                }
            }
        }

        echo "Successfully seeded " . ward::count() . " wards.\n";
    }
}
