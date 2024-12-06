<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendSMSJob;
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

    public function sendSMSInvitation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sms_content' => 'required|string|max:160',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

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

            foreach ($supporterPhoneNumbers as $phoneNumber) {
                // Dispatch the job to send SMS asynchronously
                SendSMSJob::dispatch($phoneNumber, $smsContent, $candidateId);
            }

            return response()->json([
                'message' => 'SMS invitations queued for sending.',
            ]);
        } catch (\Exception $e) {
            Log::error('SMS sending failed: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Failed to send SMS invitations: ' . $e->getMessage(),
            ], 500);
        }
    }
}
