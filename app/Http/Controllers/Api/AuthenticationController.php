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
        // Log the registration attempt with sanitized data
        Log::info('Registration attempt started', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'position_id' => $request->input('position_id'),
            'region_id' => $request->input('region_id'),
            'timestamp' => now()
        ]);

        try {
            $validatedData = $request->validate([
                'full_name' => 'required|string',
                'phone' => 'required|string|unique:candidates,phone',
                'email' => 'required|email|unique:candidates,email',
                'party_affiliation' => 'required|string',
                'position_id' => 'required|exists:positions,id',
                'region_id' => 'required|exists:regions,id',
                'village_id' => 'required',
                'ward_id' => 'exists:wards',
                'district_id' => 'required|exists:districts,id',
                'other_candidate_details' => 'nullable|string',
                'password' => 'required|min:6'
            ]);

            Log::info('Registration validation passed', [
                'email' => $validatedData['email'],
                'phone' => $validatedData['phone']
            ]);

        } catch (ValidationException $e) {
            Log::warning('Registration validation failed', [
                'errors' => $e->errors(),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'ip' => $request->ip(),
                'input_data' => $request->except(['password']) // Log input without password
            ]);

            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Unexpected validation error during registration', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Registration failed',
                'message' => 'An unexpected error occurred during validation'
            ], 500);
        }

        try {
            // Log before attempting to create candidate
            Log::info('Attempting to create candidate record', [
                'email' => $validatedData['email'],
                'phone' => $validatedData['phone'],
                'position_id' => $validatedData['position_id']
            ]);

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
            // Log successful candidate creation
            Log::info('Candidate record created successfully', [
                'candidate_id' => $user->id,
                'email' => $user->email,
                'phone' => $user->phone
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error during candidate creation', [
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'sql' => $e->getSql() ?? 'N/A',
                'bindings' => $e->getBindings() ?? [],
                'email' => $validatedData['email'] ?? 'N/A',
                'phone' => $validatedData['phone'] ?? 'N/A',
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            // Check for specific database errors
            if ($e->getCode() === '23000') { // Integrity constraint violation
                return response()->json([
                    'error' => 'Registration failed',
                    'message' => 'Email or phone number already exists'
                ], 409);
            }

            return response()->json([
                'error' => 'Registration failed',
                'message' => 'Database error occurred. Please try again.'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Unexpected error during candidate creation', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'email' => $validatedData['email'] ?? 'N/A',
                'phone' => $validatedData['phone'] ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Registration failed',
                'message' => 'An unexpected error occurred'
            ], 500);
        }

        try {
            // Log before token creation
            Log::info('Attempting to create access token', [
                'candidate_id' => $user->id,
                'email' => $user->email
            ]);

            $token = $user->createToken('auth_token')->accessToken;

            Log::info('Registration completed successfully', [
                'candidate_id' => $user->id,
                'email' => $user->email,
                'phone' => $user->phone,
                'position_id' => $user->position_id,
                'token_created' => true
            ]);

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

        } catch (\Exception $e) {
            Log::error('Token creation failed during registration', [
                'candidate_id' => $user->id ?? 'N/A',
                'email' => $user->email ?? 'N/A',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Registration completed but token creation failed',
                'message' => 'Please try logging in manually'
            ], 500);
        }
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
                    'position_name' => $positionName,
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


    private function formatPhoneNumber($phone): array|string|null
    {
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Check if number starts with 255
        if (substr($phone, 0, 3) !== '255') {
            throw new \Exception('Phone number must start with 255');
        }

        // Validate length (must be 12 characters: 255XXXXXXXXX)
        if (strlen($phone) !== 12) {
            throw new \Exception('Invalid phone number length');
        }

        // Validate Tanzania prefixes
        $validPrefixes = ['71', '74', '75', '76', '77', '78', '68', '69'];
        $prefix = substr($phone, 3, 2);
        if (!in_array($prefix, $validPrefixes)) {
            throw new \Exception('Invalid Tanzania phone number prefix');
        }

        return $phone;
    }

    public function sendOtp(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'phone' => 'required|string'
        ]);

        try {
            try {
                $formattedPhone = $this->formatPhoneNumber($request->phone);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => true,
                    'message' => 'Invalid phone number. Number must start with 255 followed by valid Tanzania prefix (e.g., 255712345678)'
                ], 400);
            }

            // Generate OTP
            $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = Carbon::now()->addMinutes(5);

            // Log OTP generation
            Log::info('Generating OTP', [
                'phone' => $formattedPhone,
                'expires_at' => $expiresAt
            ]);

            // Store OTP
            Otp::create([
                'phone' => $formattedPhone,
                'otp' => $otp,
                'expires_at' => $expiresAt,
            ]);

            // Send SMS
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

            return response()->json([
                'message' => 'OTP sent successfully',
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

    public function verifyOtp(Request $request): \Illuminate\Http\JsonResponse
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
