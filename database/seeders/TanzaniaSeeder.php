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
                'Arusha City', 'Arusha Rural', 'Karatu', 'Longido', 'Monduli', 'Ngorongoro'
            ],
            'Dar es Salaam' => [
                'Ilala', 'Kinondoni', 'Temeke', 'Ubungo', 'Kigamboni'
            ],
            'Dodoma' => [
                'Dodoma Urban', 'Dodoma Rural', 'Bahi', 'Chamwino', 'Chemba', 'Kondoa', 'Kongwa', 'Mpwapwa'
            ],
            'Geita' => [
                'Geita Town', 'Geita Rural', 'Bukombe', 'Chato', 'Mbogwe', 'Nyang\'hwale'
            ],
            'Iringa' => [
                'Iringa Urban', 'Iringa Rural', 'Kilolo', 'Mafinga', 'Mufindi'
            ],
            'Kagera' => [
                'Bukoba Urban', 'Bukoba Rural', 'Biharamulo', 'Karagwe', 'Kyerwa', 'Misenyi', 'Muleba', 'Ngara'
            ],
            'Katavi' => [
                'Mpanda Town', 'Mpanda Rural', 'Mlele'
            ],
            'Kigoma' => [
                'Kigoma Urban', 'Kigoma Rural', 'Buhigwe', 'Kakonko', 'Kasulu', 'Kibondo', 'Uvinza'
            ],
            'Kilimanjaro' => [
                'Moshi Urban', 'Moshi Rural', 'Hai', 'Rombo', 'Same', 'Siha'
            ],
            'Lindi' => [
                'Lindi Urban', 'Lindi Rural', 'Kilifi', 'Liwale', 'Nachingwea', 'Ruangwa'
            ],
            'Manyara' => [
                'Babati Town', 'Babati Rural', 'Hanang', 'Kiteto', 'Mbulu', 'Simanjiro'
            ],
            'Mara' => [
                'Musoma Urban', 'Musoma Rural', 'Bunda', 'Butiama', 'Rorya', 'Serengeti', 'Tarime'
            ],
            'Mbeya' => [
                'Mbeya City', 'Mbeya Rural', 'Busokelo', 'Chunya', 'Kyela', 'Mbarali', 'Rungwe'
            ],
            'Morogoro' => [
                'Morogoro Urban', 'Morogoro Rural', 'Gairo', 'Kilombero', 'Kilosa', 'Malinyi', 'Mvomero', 'Ulanga'
            ],
            'Mtwara' => [
                'Mtwara Urban', 'Mtwara Rural', 'Masasi', 'Nanyumbu', 'Newala', 'Tandahimba'
            ],
            'Mwanza' => [
                'Ilemela', 'Nyamagana', 'Busega', 'Kwimba', 'Magu', 'Misungwi', 'Sengerema', 'Ukerewe'
            ],
            'Njombe' => [
                'Njombe Town', 'Njombe Rural', 'Ludewa', 'Makambako', 'Makete', 'Wanging\'ombe'
            ],
            'Pwani' => [
                'Kibaha Town', 'Kibaha Rural', 'Bagamoyo', 'Chalinze', 'Kisarawe', 'Mafia', 'Mkuranga', 'Rufiji'
            ],
            'Rukwa' => [
                'Sumbawanga Urban', 'Sumbawanga Rural', 'Kalambo', 'Nkasi'
            ],
            'Ruvuma' => [
                'Songea Urban', 'Songea Rural', 'Madaba', 'Mbinga', 'Namtumbo', 'Nyasa', 'Tunduru'
            ],
            'Shinyanga' => [
                'Shinyanga Urban', 'Shinyanga Rural', 'Kahama Town', 'Kahama Rural', 'Kishapu', 'Msalala'
            ],
            'Simiyu' => [
                'Bariadi Town', 'Bariadi Rural', 'Itilima', 'Maswa', 'Meatu'
            ],
            'Singida' => [
                'Singida Urban', 'Singida Rural', 'Ikungi', 'Iramba', 'Manyoni', 'Mkalama'
            ],
            'Songwe' => [
                'Mbozi', 'Momba', 'Songwe', 'Ileje'
            ],
            'Tabora' => [
                'Tabora Urban', 'Tabora Rural', 'Igunga', 'Kaliua', 'Nzega', 'Sikonge', 'Urambo', 'Uyui'
            ],
            'Tanga' => [
                'Tanga City', 'Tanga Rural', 'Handeni Town', 'Handeni Rural', 'Kilifi', 'Korogwe Town', 'Korogwe Rural', 'Lushoto', 'Mkinga', 'Muheza', 'Pangani'
            ],
            // Zanzibar regions
            'Kaskazini Unguja' => [
                'Kaskazini A', 'Kaskazini B'
            ],
            'Kusini Unguja' => [
                'Kusini', 'Kusini Unguja'
            ],
            'Mjini Magharibi' => [
                'Mjini', 'Magharibi A', 'Magharibi B'
            ],
            'Kaskazini Pemba' => [
                'Kaskazini Pemba', 'Micheweni'
            ],
            'Kusini Pemba' => [
                'Kusini Pemba', 'Mkoani'
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

        // Districts where we'll create wards (major urban centers)
        $districtsWithWards = [
            'Ilala', 'Kinondoni', 'Temeke', 'Ubungo', 'Kigamboni',  // Dar es Salaam
            'Arusha City',  // Arusha
            'Ilemela', 'Nyamagana',  // Mwanza
            'Dodoma Urban',  // Dodoma
            'Mbeya City',  // Mbeya
            'Moshi Urban',  // Kilimanjaro
            'Iringa Urban',  // Iringa
            'Bukoba Urban',  // Kagera
            'Kigoma Urban',  // Kigoma
            'Tanga City',  // Tanga
            'Morogoro Urban',  // Morogoro
            'Mtwara Urban',  // Mtwara
            'Shinyanga Urban',  // Shinyanga
            'Singida Urban',  // Singida
            'Tabora Urban',  // Tabora
            'Mjini'  // Zanzibar
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
            'Ilala' => [
                'Buguruni', 'Gerezani', 'Ilala', 'Jangwani', 'Kariakoo', 'Kisutu', 'Mchikichini', 'Upanga East', 'Upanga West'
            ],
            'Kinondoni' => [
                'Hananasif', 'Kawe', 'Kinondoni', 'Mabibo', 'Magomeni', 'Makongo', 'Manzese', 'Msasani', 'Sinza'
            ],
            'Temeke' => [
                'Chang\'ombe', 'Keko', 'Kurasini', 'Mbagala', 'Miburani', 'Mtoni', 'Sandali', 'Temeke', 'Vijibweni'
            ],
            'Ubungo' => [
                'Goba', 'Kibamba', 'Kimara', 'Makongo Juu', 'Manzese', 'Mwenge', 'Saranga', 'Ubungo'
            ],
            'Kigamboni' => [
                'Kigamboni', 'Kibada', 'Kisarawe II', 'Mjimwema', 'Somangila', 'Tungi'
            ],

            // Arusha wards
            'Arusha City' => [
                'Daraja Mbili', 'Elerai', 'Engutoto', 'Kaloleni', 'Kati', 'Kimandolu', 'Levolosi', 'Ngarenaro', 'Sekei', 'Sokon I', 'Sokon II', 'Themi'
            ],

            // Mwanza wards
            'Ilemela' => [
                'Buhongwa', 'Ibungilo', 'Ilemela', 'Kitangiri', 'Ngudu', 'Pasiansi'
            ],
            'Nyamagana' => [
                'Bugando', 'Buzuruga', 'Igogo', 'Mahina', 'Mwanza', 'Nyakato', 'Nyamanoro', 'Pamba'
            ],

            // Dodoma wards
            'Dodoma Urban' => [
                'Chang\'ombe', 'Chihanga', 'Iyumbu', 'Kiwanja cha Ndege', 'Makole', 'Mkonze', 'Msalato', 'Nala', 'Uhuru', 'Zuzu'
            ],

            // Mbeya wards
            'Mbeya City' => [
                'Forest', 'Ghana', 'Itende', 'Iwambi', 'Mwanjelwa', 'Nzovwe', 'Sisimba', 'Soweto'
            ],

            // Moshi wards
            'Moshi Urban' => [
                'Bondeni', 'Kiboriloni', 'Kilimanjaro', 'Kiusa', 'Korongoni', 'Longuo', 'Majengo', 'Mabogini', 'Msaranga', 'Rau'
            ],

            // Iringa wards
            'Iringa Urban' => [
                'Gangilonga', 'Kihesa', 'Kitanzini', 'Mivinjeni', 'Mkwawa', 'Ruaha'
            ],

            // Bukoba wards
            'Bukoba Urban' => [
                'Hamugembe', 'Ijuganyondo', 'Kahororo', 'Karabagaine', 'Nyakaiga', 'Nyakato'
            ],

            // Kigoma wards
            'Kigoma Urban' => [
                'Bangwe', 'Gungu', 'Kalalangabo', 'Katubuka', 'Kigoma', 'Mahembe', 'Mwanga Kaskazini', 'Mwanga Kusini'
            ],

            // Tanga wards
            'Tanga City' => [
                'Central', 'Chumbageni', 'Hospital', 'Makorora', 'Mzizima', 'Ngamiani Kaskazini', 'Ngamiani Kusini', 'Usagara'
            ],

            // Morogoro wards
            'Morogoro Urban' => [
                'Boma', 'Kihonda', 'Kingolwira', 'Mazimbu', 'Mfukulembe', 'Mji Mkuu', 'Mwembesongo', 'Sabasaba'
            ],

            // Mtwara wards
            'Mtwara Urban' => [
                'Chumbageni', 'Kombeni', 'Majengo', 'Mchinga', 'Msimbazi', 'Mtanda', 'Shangani', 'Shimo la Udongo'
            ],

            // Shinyanga wards
            'Shinyanga Urban' => [
                'Ibadakuli', 'Kambarage', 'Kitangiri', 'Kolandoto', 'Mabuki', 'Majengo', 'Ndoleleji', 'Shinyanga'
            ],

            // Singida wards
            'Singida Urban' => [
                'Mtipa', 'Mwasauya', 'Ndevelya', 'Singida', 'Uhambo'
            ],

            // Tabora wards
            'Tabora Urban' => [
                'Cheyo', 'Gongoni', 'Ipala', 'Isevya', 'Kanyenye', 'Kiloleni', 'Ng\'ambo', 'Tumbi'
            ],

            // Zanzibar wards
            'Mjini' => [
                'Funguni', 'Forodhani', 'Hurumzi', 'Jang\'ombe', 'Karakana', 'Kikwajuni', 'Kiponda', 'Kwahani', 'Malindi', 'Mchangani', 'Meya', 'Mikunguni', 'Mlandege', 'Muembe Makumbi', 'Muembe Wambaa', 'Rahaleo', 'Shangani', 'Shaurimoyo', 'Stone Town', 'Tomondo', 'Vikokotoni'
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
