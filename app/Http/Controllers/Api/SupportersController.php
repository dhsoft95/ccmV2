<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\supporters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            'village_id' => 'required',
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

    public function index()
    {
        // Get the authenticated user's ID
        $userId = Auth::id();
        // Fetch all supporters where candidate_id is equal to the authenticated user's ID
        $supporters = DB::table('supporters')->where('candidate_id', $userId)->get();

        // Check if the collection is empty
        if ($supporters->isEmpty()) {
            // Return a custom message if no data is found
            return response()->json(['message' => 'data not found'], 404);
        }

        // Return the data as a JSON response
        return response()->json($supporters);
    }



}
