<?php

namespace App\Services;

use App\Models\sms_logs;
use App\Models\Candidate;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected GuzzleClient $client;
    protected string $apiUrl;
    protected string $apiToken;
    protected string $defaultSenderId;

    public function __construct()
    {
        $this->client = new GuzzleClient();
        $this->apiUrl = config('sms.api_url');
        $this->apiToken = config('sms.api_token');
        $this->defaultSenderId = config('sms.default_sender_id');
    }

    public function sendSms(string $recipient, string $message, ?int $candidateId = null): array
    {
        $senderId = $this->resolveSenderId($candidateId);

        try {
            $response = $this->client->post($this->apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'recipient' => $recipient,
                    'sender_id' => $senderId,
                    'message' => $message,
                ],
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            return [
                'success' => ($responseData['status'] ?? 'error') === 'success',
                'message' => $responseData['message'] ?? 'Unknown response',
                'sender_id' => $senderId,
                'data' => $responseData,
            ];

        } catch (RequestException $e) {
            Log::error('SMS API request failed', [
                'recipient' => $recipient,
                'sender_id' => $senderId,
                'candidate_id' => $candidateId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'API request failed: ' . $e->getMessage(),
                'sender_id' => $senderId,
                'data' => null,
            ];

        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'recipient' => $recipient,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Unexpected error: ' . $e->getMessage(),
                'sender_id' => $senderId,
                'data' => null,
            ];
        }
    }

    protected function resolveSenderId(?int $candidateId = null): string
    {
        if ($candidateId) {
            $candidate = Candidate::find($candidateId);
            if ($candidate && !empty($candidate->sender_id)) {
                return $candidate->sender_id;
            }
        }

        return $this->defaultSenderId;
    }

    public function logSms(int $candidateId, string $recipient, string $message, bool $success, string $senderId): sms_logs
    {
        return sms_logs::create([
            'candidate_id' => $candidateId,
            'recipient' => $recipient,
            'message' => $message,
            'sender_id' => $senderId,
            'status' => $success ? 1 : 0,
            'sent_at' => now(),
        ]);
    }
}
