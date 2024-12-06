<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\candidates;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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


    private function formatPhoneNumber($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (substr($phone, 0, 1) === '0') {
            $phone = '254' . substr($phone, 1);
        }

        if (substr($phone, 0, 3) !== '254') {
            $phone = '254' . $phone;
        }

        return $phone;
    }

    public function sendOtp(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'phone' => 'required|string'
        ]);

        try {
            $formattedPhone = $this->formatPhoneNumber($request->phone);
            $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = Carbon::now()->addMinutes(5);
            $referenceId = Str::uuid()->toString();

            Log::info('Generating OTP', [
                'phone' => $formattedPhone,
                'reference_id' => $referenceId,
                'expires_at' => $expiresAt
            ]);

            Otp::create([
                'reference_id' => $referenceId,
                'phone' => $formattedPhone,
                'otp' => $otp,
                'expires_at' => $expiresAt,
            ]);

            $client = new GuzzleClient();
            $response = $client->post(env('SMS_API_URL'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('SMS_API_TOKEN'),
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'recipient' => $formattedPhone,
                    'sender_id' => env('SMS_SENDER_ID'),
                    'message' => "Your verification code is: $otp. Valid for 5 minutes.",
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('SMS sending failed');
            }

            return response()->json([
                'message' => 'OTP sent successfully',
                'reference_id' => $referenceId,
                'expires_at' => $expiresAt
            ]);

        } catch (\Exception $e) {
            Log::error('OTP Sending Failed', [
                'error' => $e->getMessage(),
                'phone' => $formattedPhone ?? $request->phone
            ]);

            return response()->json([
                'error' => true,
                'message' => 'Failed to send OTP. Please try again.'
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string'
        ]);

        try {
            // Find valid OTP
            $otpData = Otp::where('otp', $request->otp)
                ->where('is_used', false)
                ->where('expires_at', '>', Carbon::now())
                ->latest()
                ->first();

            Log::info('OTP Verification Attempt', [
                'otp' => $request->otp,
                'found' => $otpData ? 'yes' : 'no'
            ]);

            if (!$otpData) {
                return response()->json([
                    'error' => true,
                    'message' => 'Invalid OTP or OTP expired. Please request a new one.'
                ], 400);
            }

            // Mark OTP as used
            $otpData->update(['is_used' => true]);

            // Return success response with the phone number
            return response()->json([
                'message' => 'OTP verified successfully',
                'phone' => $otpData->phone,
                'verified' => true
            ]);

        } catch (\Exception $e) {
            Log::error('OTP Verification Failed', [
                'error' => $e->getMessage(),
                'otp' => $request->otp
            ]);

            return response()->json([
                'error' => true,
                'message' => 'OTP verification failed. Please try again.'
            ], 500);
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
