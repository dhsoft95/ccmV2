<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\candidates;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use GuzzleHttp\Client as GuzzleClient;

class AuthenticationController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'full_name' => 'required|string',
                'phone' => 'required|string|unique:candidates,phone',
                'email' => 'required|email|unique:candidates,email',
                'party_affiliation' => 'required|string',
                'position_id' => 'required|exists:positions,id',
                'region_id' => 'required|exists:regions,id',
                'village_id' => 'required|exists:villages,id',
                'ward_id' => 'required|exists:wards,id',
                'district_id' => 'required|exists:districts,id',
                'other_candidate_details' => 'nullable|string',
                'password' => 'required|min:6'
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        $user = Candidates::create([
            'full_name' => $validatedData['full_name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'phone' => $validatedData['phone'],
            'party_affiliation' => $validatedData['party_affiliation'],
            'position_id' => $validatedData['position_id'],
            'region_id' => $validatedData['region_id'],
            'village_id' => $validatedData['village_id'],
            'ward_id' => $validatedData['ward_id'],
            'district_id' => $validatedData['district_id'],
            'other_candidate_details' => $validatedData['other_candidate_details']
        ]);

        $token = $user->createToken('auth_token')->accessToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Registered successfully.',
            'data' => [
                'user' => [
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'position_id' => $user->position_id,
                    'phone' => $user->phone,
                ],
                'token' => $token
            ]
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $candidate = Candidates::where('email', $request->input('email'))->first();

        if (!$candidate || !Hash::check($request->input('password'), $candidate->password)) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'The provided credentials are incorrect'
            ], 401);
        }

        // Fetch position name from the database table
        $positionName = DB::table('positions')->where('id', $candidate->position_id)->value('name');

        // Generate access token
        $token = $candidate->createToken('auth_token')->accessToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Logged in successfully.',
            'data' => [
                'user' => [
                    'full_name' => $candidate->full_name,
                    'email' => $candidate->email,
                    'position_name' => $positionName, // Position name retrieved from the database table
                    'phone' => $candidate->phone,
                ],
                'token' => $token
            ]
        ]);
    }

    public function logout(Request $request): \Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        $request->user()->token()->revoke();
        return response([
            'message' => 'Logged out successfully'
        ]);
    }

    public function sendOtp(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['phone' => 'required|string']);
        try {
            $otp = mt_rand(100000, 999999);

            DB::table('otps')->updateOrInsert(
                ['phone' => $request->phone],
                [
                    'otp' => Hash::make($otp),
                    'expires_at' => now()->addMinutes(10),
                    'created_at' => now()
                ]
            );

            $client = new GuzzleClient();
            $response = $client->post(env('SMS_API_URL'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('SMS_API_TOKEN'),
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'recipient' => $request->phone,
                    'sender_id' => env('SMS_SENDER_ID'),
                    'message' => "Your OTP is: $otp",
                ],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'OTP sent successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('OTP sending failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to send OTP'], 500);
        }
    }

    public function verifyOtp(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $request->validate(['otp' => 'required|string|size:6']);

            $storedOtp = DB::table('otps')
                ->where('phone', $request->phone)
                ->where('expires_at', '>', now())
                ->first();

            if (!$storedOtp || !Hash::check($request->otp, $storedOtp->otp)) {
                return response()->json(['status' => 'error', 'message' => 'Invalid OTP'], 401);
            }

            DB::table('otps')->where('phone', $request->phone)->delete();

            return response()->json(['status' => 'success', 'message' => 'OTP verified']);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Verification failed'], 500);
        }
    }
    public function update(Request $request): \Illuminate\Http\JsonResponse
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Get the authenticated user's ID
        $id = Auth::id();

        // Validate incoming request data
        $validatedData = $request->validate([
            'full_name' => 'string',
            'phone' => 'string|unique:candidates,phone,' . $id,
            'email' => '|email|unique:candidates,email,' . $id,
            'party_affiliation' => 'string',
            'position_id' => 'exists:positions,id',
            'region_id' => 'exists:regions,id',
            'village_id' => 'exists:villages,id',
            'ward_id' => 'exists:wards,id',
            'district_id' => 'exists:districts,id',
            'other_candidate_details' => 'nullable|string',
            'password' => 'nullable|min:6'
        ]);

        // Find the candidate by ID
        $candidate = Candidates::find($id);

        // Check if the candidate exists
        if (!$candidate) {
            return response()->json(['message' => 'Candidate not found.'], 404);
        }

        // Update candidate data
        $candidate->update([
            'full_name' => $validatedData['full_name'] ?? $candidate->full_name,
            'email' => $validatedData['email'] ?? $candidate->email,
            'phone' => $validatedData['phone'] ?? $candidate->phone,
            'party_affiliation' => $validatedData['party_affiliation'] ?? $candidate->party_affiliation,
            'position_id' => $validatedData['position_id'] ?? $candidate->position_id,
            'region_id' => $validatedData['region_id'] ?? $candidate->region_id,
            'village_id' => $validatedData['village_id'] ?? $candidate->village_id,
            'ward_id' => $validatedData['ward_id'] ?? $candidate->ward_id,
            'district_id' => $validatedData['district_id'] ?? $candidate->district_id,
            'other_candidate_details' => $validatedData['other_candidate_details'] ?? $candidate->other_candidate_details,
            'password' => isset($validatedData['password']) ? Hash::make($validatedData['password']) : $candidate->password,
        ]);

        // Generate a new access token for the updated candidate
        $token = $candidate->createToken('auth_token')->accessToken;

        // Fetch position name from the database table
        $positionName = DB::table('positions')->where('id', $candidate->position_id)->value('name');

        return response()->json([
            'status' => 'success',
            'message' => 'Candidate updated successfully.',
            'data' => [
                'user' => [
                    'full_name' => $candidate->full_name,
                    'email' => $candidate->email,
                    'phone' => $candidate->phone,
                    'position_name' => $positionName,
                ],
            ]
        ]);
    }

}
