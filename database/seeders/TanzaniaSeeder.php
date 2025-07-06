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
        $this->seedRegionsAndConstituencies();
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

            // Then create a default constituency (renamed from district)
            $defaultConstituency = districts::firstOrCreate([
                'name' => 'Default Constituency',
                'region_id' => $defaultRegion->id
            ], [
                'other_district_details' => 'Default constituency for system use'
            ]);

            // Create the default village with ID=1
            village::create([
                'id' => 1,
                'name' => 'Default Village',
                'region_id' => $defaultRegion->id,
                'district_id' => $defaultConstituency->id,
                'other_villages_details' => 'Default village for system use'
            ]);

            echo "Default village created.\n";
        }
    }

    /**
     * Seed all Tanzania regions and their parliamentary constituencies (majimbo ya ubunge)
     * Based on the 272 constituencies for 2025 elections (increased from 239 in 2020)
     */
    private function seedRegionsAndConstituencies(): void
    {
        $tanzaniaConstituencies = [
            'Arusha' => [
                'Arusha Mjini', 'Arusha Vijijini', 'Karatu', 'Longido', 'Meru',
                'Monduli Mjini', 'Monduli Vijijini', 'Ngorongoro'
            ],
            'Dar es Salaam' => [
                'Ilala', 'Kigamboni', 'Kinondoni', 'Temeke', 'Ubungo',
                'Kawe', 'Msasani', 'Magomeni', 'Mwananyamala', 'Chamazi', 'Kivule'
            ],
            'Dodoma' => [
                'Bahi', 'Chamwino', 'Chemba', 'Dodoma Mjini', 'Kondoa Mjini',
                'Kondoa Vijijini', 'Kongwa', 'Mpwapwa'
            ],
            'Geita' => [
                'Bukombe', 'Chato', 'Geita Mjini', 'Geita Vijijini',
                'Mbogwe', 'Nyang\'hwale'
            ],
            'Iringa' => [
                'Iringa Mjini', 'Iringa Vijijini', 'Kilolo', 'Mafinga', 'Mufindi Kaskazini',
                'Mufindi Kusini'
            ],
            'Kagera' => [
                'Biharamulo Mashariki', 'Biharamulo Magharibi', 'Bukoba Mjini',
                'Bukoba Vijijini', 'Karagwe', 'Kyerwa', 'Missenyi',
                'Muleba Kaskazini', 'Muleba Kusini', 'Ngara'
            ],
            'Kaskazini Pemba' => [
                'Micheweni', 'Wete'
            ],
            'Kaskazini Unguja' => [
                'Kaskazini A', 'Kaskazini B'
            ],
            'Katavi' => [
                'Mlele', 'Mpanda Mjini', 'Mpanda Vijijini', 'Nsimbo'
            ],
            'Kigoma' => [
                'Buhigwe', 'Kakonko', 'Kasulu Mjini', 'Kasulu Vijijini',
                'Kibondo Mashariki', 'Kibondo Magharibi', 'Kigoma Mjini',
                'Kigoma Vijijini', 'Uvinza'
            ],
            'Kilimanjaro' => [
                'Hai', 'Moshi Mjini', 'Moshi Vijijini', 'Mwanga Kaskazini',
                'Mwanga Kusini', 'Rombo', 'Same Mashariki', 'Same Magharibi', 'Siha'
            ],
            'Kusini Pemba' => [
                'Chake Chake', 'Mkoani'
            ],
            'Kusini Unguja' => [
                'Kati', 'Kusini'
            ],
            'Lindi' => [
                'Kilwa Kaskazini', 'Kilwa Kusini', 'Lindi Mjini', 'Lindi Vijijini',
                'Liwale', 'Nachingwea', 'Ruangwa'
            ],
            'Manyara' => [
                'Babati Mjini', 'Babati Vijijini', 'Hanang', 'Kiteto',
                'Mbulu Mjini', 'Mbulu Vijijini', 'Simanjiro'
            ],
            'Mara' => [
                'Bunda Mjini', 'Bunda Vijijini', 'Butiama', 'Musoma Mjini',
                'Musoma Vijijini', 'Rorya', 'Serengeti', 'Tarime Mjini',
                'Tarime Vijijini'
            ],
            'Mbeya' => [
                'Busokelo', 'Chunya', 'Kyela', 'Mbarali', 'Mbeya Mjini',
                'Mbeya Vijijini', 'Rungwe Kaskazini', 'Rungwe Kusini'
            ],
            'Mjini Magharibi' => [
                'Magharibi A', 'Magharibi B', 'Mjini'
            ],
            'Morogoro' => [
                'Gairo', 'Ifakara', 'Kilosa', 'Malinyi', 'Mlimba',
                'Morogoro Mjini', 'Morogoro Vijijini', 'Mvomero Kaskazini',
                'Mvomero Kusini', 'Ulanga Kaskazini', 'Ulanga Kusini'
            ],
            'Mtwara' => [
                'Masasi Mjini', 'Masasi Vijijini', 'Mtwara Mjini', 'Mtwara Vijijini',
                'Nanyumbu', 'Newala Kaskazini', 'Newala Kusini', 'Tandahimba'
            ],
            'Mwanza' => [
                'Buchosa', 'Ilemela', 'Kwimba', 'Magu Kaskazini', 'Magu Kusini',
                'Misungwi', 'Mwanza Kati', 'Mwanza Kaskazini', 'Mwanza Kusini',
                'Sengerema', 'Ukerewe'
            ],
            'Njombe' => [
                'Ludewa', 'Makambako', 'Makete', 'Njombe Mjini',
                'Njombe Vijijini', 'Wanging\'ombe'
            ],
            'Pwani' => [
                'Bagamoyo', 'Chalinze', 'Kibaha Mashariki', 'Kibaha Magharibi',
                'Kisarawe Kaskazini', 'Kisarawe Kusini', 'Mafia', 'Mkuranga',
                'Rufiji Kaskazini', 'Rufiji Kusini'
            ],
            'Rukwa' => [
                'Kalambo', 'Nkasi', 'Sumbawanga Mjini', 'Sumbawanga Vijijini'
            ],
            'Ruvuma' => [
                'Madaba', 'Mbinga Mjini', 'Mbinga Vijijini', 'Namtumbo',
                'Nyasa', 'Songea Mjini', 'Songea Vijijini', 'Tunduru Kaskazini',
                'Tunduru Kusini'
            ],
            'Shinyanga' => [
                'Kahama Mjini', 'Kahama Vijijini', 'Kishapu', 'Msalala',
                'Shinyanga Mjini', 'Shinyanga Vijijini', 'Ushetu'
            ],
            'Simiyu' => [
                'Bariadi Mjini', 'Bariadi Vijijini', 'Busega', 'Itilima',
                'Maswa Mashariki', 'Maswa Magharibi', 'Meatu Kaskazini', 'Meatu Kusini'
            ],
            'Singida' => [
                'Ikungi', 'Iramba', 'Itigi', 'Manyoni Kaskazini', 'Manyoni Kusini',
                'Mkalama', 'Singida Mjini', 'Singida Vijijini'
            ],
            'Songwe' => [
                'Ileje', 'Mbozi Kaskazini', 'Mbozi Kusini', 'Momba', 'Songwe', 'Tunduma'
            ],
            'Tabora' => [
                'Igunga', 'Kaliua', 'Nzega Mjini', 'Nzega Vijijini',
                'Sikonge', 'Tabora Mjini', 'Tabora Vijijini', 'Urambo Kaskazini',
                'Urambo Kusini', 'Uyui'
            ],
            'Tanga' => [
                'Bumbuli', 'Handeni Mjini', 'Handeni Vijijini', 'Kilifi Kaskazini',
                'Kilifi Kusini', 'Korogwe Mjini', 'Korogwe Vijijini', 'Lushoto Kaskazini',
                'Lushoto Kusini', 'Mkinga', 'Muheza', 'Pangani', 'Tanga Mjini'
            ]
        ];

        echo "Seeding Tanzania regions and parliamentary constituencies...\n";

        foreach ($tanzaniaConstituencies as $regionName => $constituencies) {
            echo "Creating region: {$regionName}\n";

            $region = regions::create([
                'name' => $regionName,
            ]);

            foreach ($constituencies as $constituencyName) {
                echo "  - Creating constituency: {$constituencyName}\n";

                districts::create([
                    'region_id' => $region->id,
                    'name' => $constituencyName,
                    'other_district_details' => "Parliamentary constituency of {$constituencyName} in {$regionName} region"
                ]);
            }
        }

        echo "Successfully seeded " . regions::count() . " regions and " . districts::count() . " parliamentary constituencies.\n";
    }

    /**
     * Seed sample villages for constituencies that will have wards
     */
    private function seedSampleVillages(): void
    {
        echo "Seeding sample villages...\n";

        // Major constituencies where we'll create wards
        $constituenciesWithWards = [
            // Dar es Salaam
            'Ilala', 'Kinondoni', 'Temeke', 'Ubungo', 'Kigamboni',
            'Kawe', 'Msasani', 'Magomeni', 'Mwananyamala', 'Chamazi', 'Kivule',

            // Major cities
            'Arusha Mjini', 'Dodoma Mjini', 'Mbeya Mjini', 'Tanga Mjini',

            // Major urban centers
            'Moshi Mjini', 'Iringa Mjini', 'Bukoba Mjini', 'Kigoma Mjini',
            'Morogoro Mjini', 'Mtwara Mjini', 'Shinyanga Mjini', 'Singida Mjini',
            'Tabora Mjini', 'Sumbawanga Mjini', 'Songea Mjini',

            // Mwanza constituencies
            'Ilemela', 'Mwanza Kati', 'Mwanza Kaskazini', 'Mwanza Kusini',

            // Zanzibar
            'Mjini', 'Magharibi A', 'Magharibi B',

            // Other major towns
            'Musoma Mjini', 'Kasulu Mjini', 'Babati Mjini', 'Makambako',
            'Njombe Mjini', 'Kibaha Mashariki', 'Geita Mjini', 'Mafinga',

            // Rural constituencies with significant population
            'Moshi Vijijini', 'Rungwe Kaskazini', 'Rungwe Kusini'
        ];

        foreach ($constituenciesWithWards as $constituencyName) {
            $constituency = districts::where('name', $constituencyName)->first();

            if ($constituency) {
                echo "Creating sample village for {$constituencyName}\n";

                village::create([
                    'name' => "{$constituencyName} Central Village",
                    'region_id' => $constituency->region_id,
                    'district_id' => $constituency->id,
                    'other_villages_details' => "Central village for {$constituencyName} constituency"
                ]);
            }
        }

        echo "Successfully seeded sample villages.\n";
    }

    /**
     * Seed sample wards for major constituencies
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
            'Arusha Mjini' => [
                'Daraja Mbili', 'Elerai', 'Engutoto', 'Kaloleni', 'Kati', 'Kimandolu', 'Levolosi', 'Ngarenaro', 'Sekei', 'Sokon I', 'Sokon II', 'Themi'
            ],

            // Mwanza wards
            'Ilemela' => [
                'Buhongwa', 'Ibungilo', 'Ilemela', 'Kitangiri', 'Ngudu', 'Pasiansi'
            ],
            'Mwanza Kati' => [
                'Bugando', 'Buzuruga', 'Igogo', 'Mahina', 'Mwanza', 'Nyakato', 'Nyamanoro', 'Pamba'
            ],

            // Dodoma wards
            'Dodoma Mjini' => [
                'Chang\'ombe', 'Chihanga', 'Iyumbu', 'Kiwanja cha Ndege', 'Makole', 'Mkonze', 'Msalato', 'Nala', 'Uhuru', 'Zuzu'
            ],

            // Mbeya wards
            'Mbeya Mjini' => [
                'Forest', 'Ghana', 'Itende', 'Iwambi', 'Mwanjelwa', 'Nzovwe', 'Sisimba', 'Soweto'
            ],

            // Moshi wards
            'Moshi Mjini' => [
                'Bondeni', 'Kiboriloni', 'Kilimanjaro', 'Kiusa', 'Korongoni', 'Longuo', 'Majengo', 'Mabogini', 'Msaranga', 'Rau'
            ],

            // Moshi Rural wards
            'Moshi Vijijini' => [
                'Kirua Vunjo Magharibi', 'Marangu East', 'Marangu West', 'Mwika', 'Old Moshi East', 'Old Moshi West',
                'Uru Kaskazini', 'Uru Kusini', 'Uru Mashariki', 'Uru Shimbwe', 'Chekereni', 'Kahe', 'Kimochi',
                'Kirua Vunjo Mashariki', 'Machame Kaskazini', 'Machame Kusini', 'Machame Mashariki', 'Machame Magharibi'
            ],

            // Zanzibar wards
            'Mjini' => [
                'Funguni', 'Forodhani', 'Hurumzi', 'Jang\'ombe', 'Karakana', 'Kikwajuni', 'Kiponda', 'Kwahani', 'Malindi', 'Mchangani', 'Meya', 'Mikunguni', 'Mlandege', 'Muembe Makumbi', 'Muembe Wambaa', 'Rahaleo', 'Shangani', 'Shaurimoyo', 'Stone Town', 'Tomondo', 'Vikokotoni'
            ],

            // Rungwe wards
            'Rungwe Kaskazini' => [
                'Bagamoyo', 'Bujela', 'Bulyaga', 'Ibighi', 'Ikama', 'Ikuti',
                'Ilima', 'Iponjela', 'Isongole', 'Itagata', 'Kawetele', 'Kikole',
                'Kinyala', 'Kisiba', 'Kisondela', 'Kiwira'
            ],
            'Rungwe Kusini' => [
                'Kyimo', 'Lufingo', 'Lupepo', 'Makandana', 'Malindo', 'Masebe',
                'Masoko', 'Masukulu', 'Matwebe', 'Mpuguso', 'Msasani', 'Ndato',
                'Nkunga', 'Suma', 'Swaya'
            ]
        ];

        foreach ($wardData as $constituencyName => $wards) {
            $constituency = districts::where('name', $constituencyName)->first();

            if ($constituency) {
                echo "Creating wards for {$constituencyName}:\n";

                // Get the village for this constituency
                $village = village::where('district_id', $constituency->id)->first();

                if (!$village) {
                    echo "  - No village found for {$constituencyName}, skipping wards\n";
                    continue;
                }

                echo "  - Using village: {$village->name}\n";

                foreach ($wards as $wardName) {
                    echo "  - Creating ward: {$wardName}\n";

                    ward::create([
                        'name' => $wardName,
                        'region_id' => $constituency->region_id,
                        'district_id' => $constituency->id,
                        'village_id' => $village->id,
                        'other_villages_details' => "Ward of {$wardName} in {$constituencyName} constituency"
                    ]);
                }
            }
        }

        echo "Successfully seeded " . ward::count() . " wards.\n";
    }
}
