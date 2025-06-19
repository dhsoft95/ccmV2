<?php
namespace Database\Seeders;

use App\Models\districts;
use App\Models\regions;
use Illuminate\Database\Seeder;
use App\Models\Ward;
use App\Models\Village;
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
    /**
     * Create a default village with ID=1 since wards table references it as default
     */
    private function seedDefaultVillage(): void
    {

        if (!\App\Models\Village::find(1)) {
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
            \App\Models\Village::create([
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
     */
    private function seedRegionsAndDistricts(): void
    {
        $tanzaniaData = [
            'Arusha' => [
                'Arusha City', 'Arusha Rural', 'Karatu', 'Longido', 'Monduli', 'Ngorongoro', 'Siha'
            ],
            'Dar es Salaam' => [
                'Ilala', 'Kinondoni', 'Temeke', 'Ubungo', 'Kigamboni'
            ],
            'Dodoma' => [
                'Dodoma Urban', 'Dodoma Rural', 'Bahi', 'Chamwino', 'Chemba', 'Kondoa', 'Kongwa', 'Mpwapwa'
            ],
            'Geita' => [
                'Geita Town', 'Bukombe', 'Chato', 'Geita', 'Mbogwe', 'Nyang\'hwale'
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
                'Mbeya City', 'Mbeya Rural', 'Busokelo', 'Chunya', 'Kyela', 'Mbarali', 'Momba', 'Rungwe'
            ],
            'Morogoro' => [
                'Morogoro Urban', 'Morogoro Rural', 'Gairo', 'Kilombero', 'Kilosa', 'Malinyi', 'Mvomero', 'Ulanga'
            ],
            'Mtwara' => [
                'Mtwara Urban', 'Mtwara Rural', 'Masasi', 'Nanyumbu', 'Newala', 'Tandahimba'
            ],
            'Mwanza' => [
                'Ilemela', 'Nyamagana', 'Buchosa', 'Kwimba', 'Magu', 'Misungwi', 'Sengerema', 'Ukerewe'
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
                'Bariadi Town', 'Bariadi Rural', 'Busega', 'Itilima', 'Maswa', 'Meatu'
            ],
            'Singida' => [
                'Singida Urban', 'Singida Rural', 'Ikungi', 'Iramba', 'Manyoni', 'Mkalama'
            ],
            'Songwe' => [
                'Mbozi', 'Momba', 'Songwe'
            ],
            'Tabora' => [
                'Tabora Urban', 'Tabora Rural', 'Igunga', 'Kaliua', 'Nzega', 'Sikonge', 'Urambo', 'Uyui'
            ],
            'Tanga' => [
                'Tanga City', 'Tanga Rural', 'Handeni Town', 'Handeni Rural', 'Kilifi', 'Korogwe Town', 'Korogwe Rural', 'Lushoto', 'Mkinga', 'Muheza', 'Pangani'
            ],
            'Zanzibar North' => [
                'Kaskazini A', 'Kaskazini B'
            ],
            'Zanzibar South' => [
                'Kusini', 'Kusini Unguja'
            ],
            'Zanzibar Urban/West' => [
                'Mjini Magharibi'
            ],
            'Pemba North' => [
                'Kaskazini Pemba'
            ],
            'Pemba South' => [
                'Kusini Pemba'
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

        // We need to create villages for the districts where we'll create wards
        $districtsWithWards = ['Ilala', 'Kinondoni', 'Temeke', 'Arusha City', 'Ilemela', 'Nyamagana', 'Dodoma Urban', 'Mbeya City'];

        foreach ($districtsWithWards as $districtName) {
            $district = districts::where('name', $districtName)->first();

            if ($district) {
                echo "Creating sample village for {$districtName}\n";

                // Create a default village for this district
                \App\Models\Village::create([
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
            ]
        ];

        foreach ($wardData as $districtName => $wards) {
            $district = districts::where('name', $districtName)->first();

            if ($district) {
                echo "Creating wards for {$districtName}:\n";

                // Get the village for this district
                $village = \App\Models\Village::where('district_id', $district->id)->first();

                if (!$village) {
                    echo "  - No village found for {$districtName}, skipping wards\n";
                    continue;
                }
                echo "  - Using village: {$village->name}\n";
                // Create each ward for this district

                foreach ($wards as $wardName) {
                    echo "  - Creating ward: {$wardName}\n";

                    Ward::create([
                        'name' => $wardName,
                        'region_id' => $district->region_id,
                        'district_id' => $district->id,
                        'village_id' => $village->id,
                        'other_villages_details' => "Ward of {$wardName} in {$districtName} district"
                    ]);
                }
            }
        }

        echo "Successfully seeded " . Ward::count() . " wards.\n";
    }
}
