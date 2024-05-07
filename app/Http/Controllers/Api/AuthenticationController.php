<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\candidates;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
            'full_name' => $validatedData['username'],
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
            'message' => 'User registered successfully.',
            'token' => $token
        ], 201);
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

        $token = $candidate->createToken('auth_token')->accessToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Logged in successfully.',
            'data' => [
                'user' => [
                    'full_name' => $candidate->full_name,
                    'email' => $candidate->email,
                ],
                'token' => $token
            ]
        ]);
    }

    public function logout(Request $request){
        $request->user()->token()->revoke();

        return response([
            'message'=>'Logged out sucesfully'
        ]);
    }
}
