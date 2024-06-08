<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\regions;
use Illuminate\Http\Request;
use SalimMbise\TanzaniaRegions\TanzaniaRegions;

class regigionsController extends Controller
{
    public function insertJsonData(Request $request)
    {
        // Get all regions, districts, and wards data
        $tanzaniaRegions = new TanzaniaRegions();
        $allRegionsData = $tanzaniaRegions->getAllData();

        // Loop through each region in the data
        foreach ($allRegionsData as $regionName => $districts) {
            // Insert region data
            $region = regions::create([
                'name' => $regionName
            ]);

            // Loop through each district in the region
            foreach ($districts as $districtName => $wardData) {
                // Extract the wards for this district
                $wards = $wardData['wards'];

                // Insert district data linked to the region
                $district = $region->districts()->create([
                    'name' => $districtName
                ]);

                // Loop through each ward in the district
                foreach ($wards as $wardName => $wardCode) {
                    // Insert ward data linked to the region and district
                    $district->wards()->create([
                        'name' => $wardName,
                        'ward_code' => $wardCode,
                        'region_id' => $region->id // Assign region_id
                    ]);
                }
            }
        }

        return response()->json(['message' => 'Data inserted successfully'], 200);
    }
}
