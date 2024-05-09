<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\supporters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Queue;

class SmSController extends Controller
{
    public static function sendSMSInvitation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:160', // Adjust character limit as needed
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $client = new GuzzleClient();

        try {
            $candidate = Auth::user();
            $message = $request->input('message');

            // Retrieve the ID of the authenticated user
            $candidateId = Auth::id();

            // Retrieve phone numbers of supporters associated with the candidate
            $supporterPhoneNumbers = supporters::where('candidate_id', $candidateId)
                ->pluck('phone_number');

            // Check if there are supporters associated with the candidate
            if ($supporterPhoneNumbers->isEmpty()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Failed to send SMS invitations: No supporters found',
                ], 422);
            }

            $responseArray = [];
            foreach ($supporterPhoneNumbers as $phoneNumber) {
                $response = $client->post(env('SMS_API_URL'), [
                    'headers' => [
                        'Authorization' => 'Bearer ' . env('SMS_API_TOKEN'),
                        'Accept' => 'application/json',
                    ],
                    'json' => [
                        'recipient' => $phoneNumber,
                        'sender_id' => 'Info',
                        'message' => $message,
                    ],
                ]);

                $responseData = json_decode($response->getBody()->getContents(), true);

                $status = $responseData['status'] ?? 'error';
                $message = $responseData['message'] ?? 'Unknown error';

                $responseArray[] = [
                    'recipient' => $phoneNumber,
                    'status' => $status,
                    'message' => $message,
                ];

                // Queue failed message notification (optional)
                if ($status !== 'success') {
                    Queue::push(new SmsSendingFailed($phoneNumber, $message));
                }

                // Implement bulk sending logic if supported by your SMS API
            }

            return response()->json($responseArray);
        } catch (\Exception $e) {
            Log::error('SMS sending failed: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Failed to send SMS invitations: ' . $e->getMessage(),
            ], 500);
        }
    }
}
