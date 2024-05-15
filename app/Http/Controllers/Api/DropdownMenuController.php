<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\positions;
use App\Models\regions;
use App\Models\village;
use Illuminate\Http\Request;

class DropdownMenuController extends Controller
{
    public function getDropdownData()
    {
        // Fetch all regions with their districts and wards
        $regions = regions::with('districts.wards')->get();

        // Fetch all villages grouped by district
        $villagesGroupedByDistrict = village::all()->groupBy('district_id');

        // Append villages to their respective districts
        foreach ($regions as $region) {
            foreach ($region->districts as $district) {
                $district->villages = $villagesGroupedByDistrict[$district->id] ?? [];
            }
        }

        return response()->json([
            'regions' => $regions,
        ]);
    }


    public function getPositionData()
    {
        // Fetch all regions with their districts and wards
        $positions = positions::All();


        return response()->json([
            'positions' => $positions,
        ]);
    }
}
