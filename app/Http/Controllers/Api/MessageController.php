<?php

namespace App\Http\Controllers\Api;

use AllowDynamicProperties;
use App\Http\Controllers\Controller;
use App\Models\messaging_logs;
use App\Models\supporters;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

#[AllowDynamicProperties] class MessageController extends Controller
{
    public function __construct()
    {
        $this->accessToken = env('FACEBOOK_ACCESS_TOKEN');
        $this->whatsappApiUrl = env('WHATSAPP_API_URL');
    }

    public function sendMessageToSupporters(Request $request): JsonResponse
    {
        try {
            // Validate the incoming request data
            $request->validate([
                'candidate_id' => 'required|exists:candidates,id',
                'message' => 'required|string',
            ]);

            // Get the candidate ID and message from the request
            $candidateId = $request->input('candidate_id');
            $message = $request->input('message');

            // Retrieve all supporters associated with the candidate who have promised to support
            $supporters = supporters::where('candidate_id', $candidateId)
                ->where('promised', 1)
                ->get();

            // Initialize arrays to track successful and failed messages
            $successfulMessages = [];
            $failedMessages = [];

            // Send messages to each supporter
            foreach ($supporters as $supporter) {
                // Retrieve supporter's WhatsApp number from the database
                $phoneNumber = $supporter->phone_number;

                // Send the WhatsApp message only if supporter has promised to support
                if ($supporter->promised) {
                    // Send the WhatsApp message using the Facebook Graph API
                    $response = Http::post($this->whatsappApiUrl, [
                        'to' => "whatsapp:$phoneNumber",
                        'type' => 'text',
                        'recipient_type'=> 'individual',
                        'messaging_product' => 'whatsapp',
                        'text' => [
                            'preview_url'=> false,
                            'body' => $message,
                        ],
                        'access_token' => $this->accessToken
                    ]);

                    // Extract the response content
                    $responseData = $response->json();

                    // Log the message sending attempt along with the response
                    \Log::info('Message sent to supporter ' . $phoneNumber . ' - Response: ' . json_encode($responseData));

                    // Log the message sending attempt
                    messaging_logs::create([
                        'supporter_id' => $supporter->id,
                        'channel' => 'WhatsApp',
                        'success' => $response->successful(),
                        'response' => json_encode($responseData)
                    ]);

                    // Check if the request was successful
                    if ($response->successful()) {
                        // Log successful message sending
                        $successfulMessages[$phoneNumber] = $responseData;
                        \Log::info('Message sent to supporter ' . $phoneNumber);
                    } else {
                        // Log error if message sending failed
                        $failedMessages[$phoneNumber] = $responseData;
                        \Log::error('Failed to send message to supporter ' . $phoneNumber);
                    }
                }
            }

            // Check if there were any successful messages
            if (empty($successfulMessages)) {
                return response()->json(['error' => 'No messages sent. None of the supporters have promised to support.'], 422);
            }

            // Prepare the response based on successful and failed messages
            if (!empty($failedMessages)) {
                return response()->json(['error' => 'Failed to send messages to some supporters.', 'failed_supporters' => $failedMessages], 422);
            } else {
                return response()->json(['message' => 'Messages sent successfully to all supporters.', 'successful_supporters' => $successfulMessages]);
            }
        } catch (ValidationException $e) {
            // Return validation errors
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Log the exception and return an error response
            \Log::error('Error sending messages: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send messages.'], 500);
        }
    }
}
