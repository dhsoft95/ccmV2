<?php

namespace App\Jobs;

use App\Models\sms_logs;
use App\Models\supporters;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SendSMSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $phoneNumber;
    protected $smsContent;
    protected $candidateId;

    /**
     * Create a new job instance.
     *
     * @param string $phoneNumber
     * @param string $smsContent
     * @param int $candidateId
     * @return void
     */
    public function __construct($phoneNumber, $smsContent, $candidateId)
    {
        $this->phoneNumber = $phoneNumber;
        $this->smsContent = $smsContent;
        $this->candidateId = $candidateId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new GuzzleClient();

        try {
            // Send the SMS using Guzzle HTTP client
            $response = $client->post(env('SMS_API_URL'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('SMS_API_TOKEN'),
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'recipient' => $this->phoneNumber,
                    'sender_id' => 'HARUS YANGU',
                    'message' => $this->smsContent,
                ],
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            $status = $responseData['status'] ?? 'error';
            $responseMessage = $responseData['message'] ?? 'Unknown error';

            $statusValue = $status === 'success' ? 1 : 0;

            // Save the log
            sms_logs::create([
                'candidate_id' => $this->candidateId,
                'recipient' => $this->phoneNumber,
                'status' => $statusValue,
                'message' => $this->smsContent,
            ]);

            if ($status !== 'success') {
                // Log the failure
                Log::error('Failed to send SMS to ' . $this->phoneNumber . ': ' . $responseMessage);
            }
        } catch (\Exception $e) {
            Log::error('SMS sending failed to ' . $this->phoneNumber . ': ' . $e->getMessage());
        }
    }
}
