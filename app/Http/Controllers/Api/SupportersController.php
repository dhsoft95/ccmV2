<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\supporters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportersController extends Controller
{
    public function store(Request $request)
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Validate incoming request data
        $validatedData = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'dob' => '|date',
            'gender' => 'required|string',
            'region_id' => 'required|integer',
            'village_id' => 'required|integer',
            'ward_id' => 'required|integer',
            'district_id' => 'required|integer',
            'phone_number' => 'required|string|unique:supporters',
            'promised' => 'nullable|string',
            'other_supporter_details' => 'nullable|string',
        ], [
            'phone_number.unique' => 'The phone number has already been taken.'
        ]);

        // Set candidate_id based on the authenticated user's ID
        $validatedData['candidate_id'] = Auth::id();

        // Log the candidate_id for debugging
        Log::info('Authenticated user ID: ' . Auth::id());

        // Create a new instance of Supporters with the validated data
        $newData = supporters::create($validatedData);

        // Return a response indicating success or failure
        if ($newData) {
            $supporterDetails = $validatedData['first_name'] . ' ' . $validatedData['last_name'];
            return response()->json([
                'message' => 'You have successfully saved supporter data: ' . $supporterDetails,
                'data' => $newData // Include the inserted data in the response
            ], 201);
        } else {
            return response()->json(['message' => 'Failed to insert data'], 500);
        }
    }


}
