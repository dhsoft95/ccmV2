<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendSMSJob;
use App\Models\Supporters;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SmSController extends Controller
{
    public function sendSMSInvitation(Request $request): \Illuminate\Http\JsonResponse
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
            $supporterPhoneNumbers = Supporters::where('candidate_id', $candidateId)
                ->whereNotNull('phone_number')
                ->where('phone_number', '!=', '')
                ->pluck('phone_number');

            if ($supporterPhoneNumbers->isEmpty()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Failed to send SMS invitations: No supporters with valid phone numbers found',
                ], 422);
            }

            $dispatched = 0;
            $skipped = 0;

            foreach ($supporterPhoneNumbers as $phoneNumber) {
                if (!empty($phoneNumber)) {
                    // Dispatch the job to send SMS asynchronously
                    SendSMSJob::dispatch($phoneNumber, $smsContent, $candidateId);
                    $dispatched++;
                } else {
                    $skipped++;
                }
            }

            Log::info('SMS invitations processed', [
                'candidate_id' => $candidateId,
                'dispatched' => $dispatched,
                'skipped' => $skipped,
                'total_supporters' => $supporterPhoneNumbers->count()
            ]);

            return response()->json([
                'message' => "SMS invitations queued for sending to {$dispatched} supporters.",
                'stats' => [
                    'dispatched' => $dispatched,
                    'skipped' => $skipped,
                    'total' => $dispatched + $skipped
                ]
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
