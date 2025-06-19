<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\regions;
use App\Models\districts;
use App\Models\ward;
use App\Models\village;
use App\Models\Supporters;
use App\Models\candidates;
use App\Models\positions;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class CommitteeMembersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the main region
        $region = regions::create([
            'name' => 'Mtwara',
        ]);

        // Create a default district
        $district = districts::create([
            'region_id' => $region->id,
            'name' => 'Mtwara Urban',
        ]);

        // Create a default position if none exists
        $position = positions::firstOrCreate(
            ['name' => 'Member of Parliament'],
            [
                'description' => 'Member of Parliament position',
            ]
        );

        // Create a default village first (wards need village_id)
        $defaultVillage = village::create([
            'name' => 'Default Village',
            'region_id' => $region->id,
            'district_id' => $district->id,
        ]);

        // Create a default ward (requires village_id)
        $defaultWard = ward::create([
            'name' => 'Default Ward',
            'region_id' => $region->id,
            'district_id' => $district->id,
            'village_id' => $defaultVillage->id,
        ]);

        // Create the candidate Haroun Rashid Maarifa
        $candidate = candidates::create([
            'full_name' => 'Haroun Rashid Maarifa',
            'name' => 'Haroun Rashid Maarifa',
            'email' => 'mrmaarifa@gmail.com',
            'phone' => '0700000000', // Placeholder phone number
            'party_affiliation' => 'CCM', // Assumed based on context
            'position_id' => $position->id,
            'region_id' => $region->id,
            'district_id' => $district->id,
            'village_id' => $defaultVillage->id,
            'ward_id' => $defaultWard->id,
            'password' => Hash::make('password123'),
        ]);

        // Committee data organized by wards and branches
        $committeeData = [
            'TULINDANE' => [
                'LUCHINGU A' => [
                    ['name' => 'AHMADI S. CHIKWAYA', 'phone' => '0710121722'],
                    ['name' => 'SOFIA GEORGE', 'phone' => '0616147540'],
                    ['name' => 'LUKIA DADI', 'phone' => '0784925283'],
                    ['name' => 'GRACE MROPE', 'phone' => '0782708279'],
                    ['name' => 'SAIDI HAMISI ALLY', 'phone' => '0674502398'],
                ],
                'LINGANA' => [
                    ['name' => 'FARAJI MATENDO', 'phone' => '0656004520'],
                    ['name' => 'SALIMA S MASUDI', 'phone' => '0697001897'],
                    ['name' => 'SALUMU A. MAWAJA', 'phone' => '0657755084'],
                    ['name' => 'AGNES FAUSTINE', 'phone' => '0786512374'],
                    ['name' => 'MAISHA BAKILI', 'phone' => '0786747113'],
                    ['name' => 'RASHIDI LIKONGWA', 'phone' => '0692586147'],
                ],
                'MUUNGANO' => [
                    ['name' => 'LUKIA MFAUME', 'phone' => '0689271815'],
                    ['name' => 'ZAINABU MSHANGABNI', 'phone' => '0785503494'],
                    ['name' => 'BENEDETA MANENO', 'phone' => '0656298493'],
                    ['name' => 'ZAINABU MKAUMA', 'phone' => '0695612002'],
                    ['name' => 'SAIDI H. SIDIMALI', 'phone' => '0626504793'],
                    ['name' => 'JUMA SAIDI', 'phone' => '0696106678'],
                ],
                'TUPENDANE' => [
                    ['name' => 'GRACE MWANGO', 'phone' => ''],
                    ['name' => 'REHEMA CHIMPELE', 'phone' => '0776147587'],
                    ['name' => 'SOFIA BURIANI', 'phone' => '0693446261'],
                    ['name' => 'SOFIA RAMADHANI', 'phone' => '0618818004'],
                    ['name' => 'ABDUL SHAIBU', 'phone' => '0681177384'],
                ],
            ],
            'JULIA' => [
                'KIDUNI' => [
                    ['name' => 'SHARIFU BAKARI LIYANGA', 'phone' => '0689178440'],
                    ['name' => 'MWAMTUMU S. KARIMU', 'phone' => '0616020311'],
                    ['name' => 'ALLY MUSA CHIKOTA', 'phone' => '0784937198'],
                    ['name' => 'SALUMU SAMLI ALFANI', 'phone' => '0687585285'],
                    ['name' => 'SALMA SULUMU MDISI', 'phone' => '0712352598'],
                    ['name' => 'ASIA JUMA CHIKOMELE', 'phone' => '0712645893'],
                ],
                'JULIA' => [
                    ['name' => 'ADAMU FAKII', 'phone' => '0773497619'],
                    ['name' => 'HUSNA HASSANI', 'phone' => '0717790143'],
                    ['name' => 'AMANI AMANI', 'phone' => '0718099762'],
                    ['name' => 'MUGA HAMISI MUGA', 'phone' => '0688629639'],
                    ['name' => 'MZEE BWATAMU', 'phone' => '0676211222'],
                    ['name' => 'BINTI NAMTUMBIKA', 'phone' => '0682880405'],
                    ['name' => 'MZEE MAARIFA', 'phone' => '0686440048'],
                    ['name' => 'SOFIA ACHILAMBO', 'phone' => '0657931366'],
                    ['name' => 'SOFIA CHILEMBWE', 'phone' => '0614552516'],
                ],
                'LEGEZA' => [
                    ['name' => 'RAJABU RASHIDI', 'phone' => '0710644083'],
                    ['name' => 'SHAFII ABDUL JUMA', 'phone' => '0788655152'],
                    ['name' => 'ISSA AMANI SUBIRI', 'phone' => '0688772218'],
                    ['name' => 'FADINA MOHAMEDI LIPANDE', 'phone' => '0686675798'],
                    ['name' => 'MWANAHAMISI TARATIBU', 'phone' => '0682018959'],
                ],
                'KILIMAHEWA' => [
                    ['name' => 'BAKARI NANDULE', 'phone' => '0693407314'],
                    ['name' => 'HASHIMU TILI', 'phone' => '0786867729'],
                    ['name' => 'ZENA WAPAKAYA', 'phone' => '0689392440'],
                    ['name' => 'MWAJUMA BAKARI', 'phone' => '0693726641'],
                    ['name' => 'ARABIA AKILI', 'phone' => '0686466468'],
                ],
            ],
            'MKULUNG\'ULU' => [
                'KILIDU' => [
                    ['name' => 'SOMOE KUMESA', 'phone' => '0659104736'],
                    ['name' => 'ZAINABU NAMKOKO', 'phone' => '0712220228'],
                    ['name' => 'SHANI MALELA', 'phone' => '0676545349'],
                    ['name' => 'SAIDI NDALE', 'phone' => '0714713449'],
                    ['name' => 'AMI AHMADI JONGO', 'phone' => '0618010966'],
                    ['name' => 'HABIBA MAULIDI', 'phone' => '0654483045'],
                ],
                'MKULUNG\'ULU' => [
                    ['name' => 'MUHIDI MUSSA NDOLANGA', 'phone' => '0718199206'],
                    ['name' => 'MUSSA LUKWEKWE', 'phone' => '0628794953'],
                    ['name' => 'BIBIE OMARY NAKOPA', 'phone' => '0654169436'],
                    ['name' => 'FADHILA DOVYA', 'phone' => '0612746118'],
                    ['name' => 'JAMAL ABEDI', 'phone' => ''],
                    ['name' => 'ZUHURA HAMISI', 'phone' => '0689696459'],
                ],
                'MAGOMBO' => [
                    ['name' => 'SHEDA MBOMBA', 'phone' => ''],
                    ['name' => 'ASHURA MPITAMWAKE', 'phone' => ''],
                    ['name' => 'FEDINA NANJOCHA', 'phone' => ''],
                ],
                'LONDO' => [
                    ['name' => 'SINAE MWARABU', 'phone' => ''],
                    ['name' => 'FEDINA CHIKOTA', 'phone' => ''],
                    ['name' => 'ZAINABU LUNDA', 'phone' => ''],
                    ['name' => 'YAHAYA LIPUKA', 'phone' => ''],
                ],
            ],
            'MCHOLI II' => [
                'LIDUMBE' => [
                    ['name' => 'SOPHIA HASHIMU', 'phone' => '0678316703'],
                    ['name' => 'HAWA ULEDI', 'phone' => '0689597784'],
                    ['name' => 'MOZA AMRANI', 'phone' => '0686340969'],
                    ['name' => 'AMINA LIKAKA', 'phone' => '0623234057'],
                    ['name' => 'ZAINABU HASHIMU', 'phone' => ''],
                ],
                'MITUMBATI' => [
                    ['name' => 'MWANAISHA JIHANDO', 'phone' => '0659277399'],
                    ['name' => 'HIDAYA ADAM CHAKA', 'phone' => '0653026959'],
                    ['name' => 'FATUMA MPENDE', 'phone' => '0679704227'],
                    ['name' => 'SOFIA LIGOME', 'phone' => '0654900767'],
                    ['name' => 'ZAITUNI MANZI MAULIDI', 'phone' => '0615214814'],
                ],
                'TAWALA' => [
                    ['name' => 'REHEMA CHIPIKITI', 'phone' => '0656304783'],
                    ['name' => 'SOFIA SAGAMAWE', 'phone' => '0785868951'],
                    ['name' => 'SALIMA SONGORO', 'phone' => '0714647787'],
                    ['name' => 'RABIA DURU', 'phone' => '0652206660'],
                    ['name' => 'SHARIFA', 'phone' => '0682216272'],
                    ['name' => 'SAIDI SAIDI LISUMA', 'phone' => ''],
                ],
                'MSILILI' => [
                    ['name' => 'ZUHURA RAJABU', 'phone' => '0654885427'],
                    ['name' => 'FATUMA ADAMU', 'phone' => '0610140951'],
                    ['name' => 'SALMA CHAPACHA', 'phone' => '0712257753'],
                    ['name' => 'SOFIA MTOPE', 'phone' => '0711717640'],
                    ['name' => 'ZAINABU MCHIWALA', 'phone' => '0686740775'],
                ],
                'MNAIDA' => [
                    ['name' => 'ZAINABU MUHIDINI', 'phone' => '0683823156'],
                    ['name' => 'SALMA MUHTAR', 'phone' => '0710229614'],
                    ['name' => 'MARIUMU MUSSA', 'phone' => '0683344483'],
                    ['name' => 'HAMZA MNAMALA', 'phone' => '0714502557'],
                    ['name' => 'RUKIA MKUMBACHI', 'phone' => '0717217795'],
                ],
            ],
            'MNEKACHI' => [
                'NAMBUNGA' => [
                    ['name' => 'MOHAMEDI MKULUKALA', 'phone' => ''],
                    ['name' => 'HOFU RAFAEL NANDADYA', 'phone' => '0687181218'],
                    ['name' => 'IBRAHIMU SAID NDINDA', 'phone' => '0654334565'],
                    ['name' => 'HAMIDA MOHAMED MMNUNG\'A', 'phone' => '0683736680'],
                    ['name' => 'MUSSA ZAWADI NAMDONG\'A', 'phone' => '0784463035'],
                    ['name' => 'SHAHARA MUHIBU AKULE', 'phone' => '0685612198'],
                    ['name' => 'JUMA MAHAMUDU SABIHI', 'phone' => '0678289696'],
                ],
                'IMANI' => [
                    ['name' => 'ABDALLAH NALINGA', 'phone' => '0788720155'],
                    ['name' => 'SHARIFU MNEKE', 'phone' => ''],
                    ['name' => 'MOHAMEDI ABDALLAH KIBO', 'phone' => '0782346551'],
                    ['name' => 'MUBARAKA MUSSA', 'phone' => '0715960396'],
                    ['name' => 'FATUMA SALUMU MOHAMED', 'phone' => '0686765936'],
                    ['name' => 'JUMA MOHAMED SAMRI (STIKI)', 'phone' => '0685139651'],
                ],
                'MKOMA' => [
                    ['name' => 'ALFRED MTANDAO', 'phone' => '0683610958'],
                    ['name' => 'JUDITH YONA', 'phone' => '0683610958'],
                    ['name' => 'PENDO MOHAMEDI', 'phone' => '0696701582'],
                    ['name' => 'MSAFIRI LIHULA', 'phone' => '0672401143'],
                    ['name' => 'FIDEA ADAM', 'phone' => ''],
                    ['name' => 'FRIDA MCHIRA', 'phone' => '0781019454'],
                    ['name' => 'KWELI LIKUMBO', 'phone' => '0688632972'],
                    ['name' => 'ASHA MTUMBA', 'phone' => '0692080476'],
                    ['name' => 'SHAIBU HATARI', 'phone' => '0686554760'],
                    ['name' => 'FATUMA BAKARI', 'phone' => '0687844349'],
                ],
                'CHIWAMBO' => [
                    ['name' => 'PENDO AHMADI CHILEU', 'phone' => '0682417164'],
                    ['name' => 'SOFINA SADIKI FUNDI', 'phone' => '0782153171'],
                    ['name' => 'BAKARI MUSA MNUMBILE', 'phone' => '0784020538'],
                    ['name' => 'JUMA WAHILU SELEMANI', 'phone' => '0698267575'],
                    ['name' => 'FADINA ATHUMANI MNADI', 'phone' => '0697337394'],
                    ['name' => 'SAIDI RASHIDI MSANJIKA', 'phone' => '0684787051'],
                    ['name' => 'JAZIMU SAIDI NAYOPA', 'phone' => '0699218851'],
                    ['name' => 'KULUTHUMU HUSSEIN ALLY', 'phone' => '0789017868'],
                    ['name' => 'ZAITUNI NYATINYATI', 'phone' => '0684786912'],
                    ['name' => 'JAMILA MAARUFU MNALIMA', 'phone' => '0684491764'],
                    ['name' => 'KASIMU SAIDI NAYOPA', 'phone' => '0685144324'],
                ],
                'KAZAMOYO' => [
                    ['name' => 'SAIDI SHAHA', 'phone' => '0699114274'],
                    ['name' => 'HAMISI MAMLO', 'phone' => '0788667691'],
                    ['name' => 'ASHURA KAZUMAR', 'phone' => '0625050753'],
                    ['name' => 'SHAHA SAIDI', 'phone' => '0776733966'],
                    ['name' => 'MWAJUMA MKOVA', 'phone' => ''],
                    ['name' => 'FADINA MCHOMI', 'phone' => '0788679132'],
                ],
            ],
            // Continue with more wards as needed...
        ];

        // Process each ward and its branches
        foreach ($committeeData as $wardName => $branches) {
            // First, create all villages/branches for this ward
            $villageRecords = [];
            foreach ($branches as $branchName => $members) {
                $villageRecord = village::create([
                    'name' => $branchName,
                    'region_id' => $region->id,
                    'district_id' => $district->id,
                ]);
                $villageRecords[] = $villageRecord;
            }

            // Create ward with reference to first village
            $wardRecord = ward::create([
                'name' => $wardName,
                'region_id' => $region->id,
                'district_id' => $district->id,
                'village_id' => $villageRecords[0]->id, // Reference first village
            ]);

            // Now create committee members for each village
            $villageIndex = 0;
            foreach ($branches as $branchName => $members) {
                $villageRecord = $villageRecords[$villageIndex];

                foreach ($members as $member) {
                    // Parse name to get first and last name
                    $nameParts = explode(' ', trim($member['name']), 2);
                    $firstName = $nameParts[0] ?? '';
                    $lastName = $nameParts[1] ?? '';

                    Supporters::create([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'dob' => null, // Not provided in the data
                        'gander' => null, // Using 'gander' as per your migration (typo)
                        'region_id' => $region->id,
                        'village_id' => $villageRecord->id,
                        'ward_id' => $wardRecord->id,
                        'district_id' => $district->id,
                        'candidate_id' => $candidate->id, // Assign to Haroun Rashid Maarifa
                        'phone_number' => !empty($member['phone']) ? $member['phone'] : null,
                        'promised' => true, // Assuming committee members are committed
                        'other_supporter_details' => 'Committee member - MD-2025'
                    ]);
                }
                $villageIndex++;
            }
        }

        $this->command->info('Committee members data seeded successfully!');
        $this->command->info("Created candidate: {$candidate->full_name} ({$candidate->email})");
        $this->command->info("Created {$region->name} region with committee structure");
        $this->command->info("Total wards: " . ward::count() . " (including 1 default ward)");
        $this->command->info("Total villages/branches: " . village::count() . " (including 1 default village)");
        $this->command->info("Total committee members: " . Supporters::count());
        $this->command->info("All supporters assigned to: {$candidate->full_name}");
    }
}
