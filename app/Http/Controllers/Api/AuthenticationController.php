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

        $user = candidates::create([
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

        $candidate = candidates::where('email', $request->input('email'))->first();

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
//                    'position_id' => $candidate->position_id,
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
            'message'=>'Logged out sucesfully'
        ]);
    }

    public function sendOtp(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate input data
        $request->validate([
            'phone' => 'required|string'
        ]);

        try {
            // Generate a random OTP (One-Time Password)
            $otp = mt_rand(100000, 999999);

            // Send the OTP via SMS
            $client = new GuzzleClient();
            $response = $client->post(env('SMS_API_URL'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('SMS_API_TOKEN'),
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'recipient' => $request->phone,
                    'sender_id' => 'name',
                    'message' => "Your OTP is: $otp",
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Failed to send OTP via SMS.');
            }

            // OTP sent successfully
            return response()->json([
                'message' => 'OTP sent successfully.',
                'otp_identifier' => uniqid(),
                ' otp' => $otp
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            Log::error('OTP sending failed: ' . $e->getMessage());

            return response()->json([
                'error' => true,
                'message' => 'Failed to send OTP via SMS. Please try again later.',
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string', // Validate OTP provided by the user
        ]);

        // Retrieve the OTP sent earlier (stored securely on the server side)
        // Here, we're assuming you have stored it in the session for simplicity
        $storedOtp = session()->get('otp');

        if ($storedOtp === null) {
            // No OTP stored in session, meaning it has expired or hasn't been generated yet
            return response()->json([
                'error' => true,
                'message' => 'OTP expired or not generated. Please request a new OTP.'
            ], 400); // Bad Request status code
        }

        // Compare the OTP provided by the user with the stored OTP
        if ($request->otp === $storedOtp) {
            // OTP is correct, perform authentication or desired action

            // Clear the stored OTP from session to prevent reuse
            session()->forget('otp');

            return response()->json([
                'message' => 'OTP verification successful. User authenticated.'
            ]);
        } else {
            // OTP is incorrect, display an error message
            return response()->json([
                'error' => true,
                'message' => 'Incorrect OTP. Please try again.'
            ], 401); // Unauthorized status code
        }
    }



}
