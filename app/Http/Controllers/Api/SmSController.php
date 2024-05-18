<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\sms_logs;
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
            'sms_content' => 'required|string|max:160', // Adjust character limit as needed
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $client = new GuzzleClient();

        try {
            $candidateId = Auth::id(); // Get candidate ID

            $smsContent = $request->input('sms_content');

            // Retrieve phone numbers of supporters associated with the candidate
            $supporterPhoneNumbers = supporters::where('candidate_id', $candidateId)
                ->pluck('phone_number');

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
                        'sender_id' => 'HARUS YANGU',
                        'message' => $smsContent,
                    ],
                ]);

                $responseData = json_decode($response->getBody()->getContents(), true);

                $status = $responseData['status'] ?? 'error';
                $responseMessage = $responseData['message'] ?? 'Unknown error';

                $statusValue = $status === 'success' ? 1 : 0;

                $responseArray[] = [
                    'recipient' => $phoneNumber,
                    'status' => $statusValue, // Set status as 1 for true (success) and 0 for false (failure)
                    'message' => $responseMessage,
                ];

                if ($status !== 'success') {
                    Queue::push(new SmsSendingFailed($phoneNumber, $responseMessage));
                }

                // Save the log
                sms_logs::create([
                    'candidate_id' => $candidateId,
                    'recipient' => $phoneNumber,
                    'status' => $statusValue,
                    'message' => $responseMessage,
                ]);
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
