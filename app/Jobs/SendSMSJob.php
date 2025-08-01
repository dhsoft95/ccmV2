<?php

namespace App\Jobs;

use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSMSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // Limit retry attempts
    public $timeout = 60; // Job timeout

    public function __construct(
        protected string $phoneNumber,
        protected string $smsContent,
        protected int $candidateId
    ) {}

    public function handle(SmsService $smsService): void
    {
        try {
            Log::info('Processing SMS job', [
                'phone' => $this->phoneNumber,
                'candidate_id' => $this->candidateId
            ]);

            $result = $smsService->sendSms(
                $this->phoneNumber,
                $this->smsContent,
                $this->candidateId
            );

            $smsService->logSms(
                $this->candidateId,
                $this->phoneNumber,
                $this->smsContent,
                $result['success'],
                $result['sender_id']
            );

            if (!$result['success']) {
                Log::warning('SMS delivery failed', [
                    'recipient' => $this->phoneNumber,
                    'candidate_id' => $this->candidateId,
                    'sender_id' => $result['sender_id'],
                    'error' => $result['message'],
                ]);
            } else {
                Log::info('SMS sent successfully', [
                    'recipient' => $this->phoneNumber,
                    'candidate_id' => $this->candidateId,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('SMS job failed with exception', [
                'phone' => $this->phoneNumber,
                'candidate_id' => $this->candidateId,
                'error' => $e->getMessage(),
                'attempts' => $this->attempts()
            ]);

            // Don't retry if it's a critical error
            if ($this->attempts() >= $this->tries) {
                Log::error('SMS job failed permanently', [
                    'phone' => $this->phoneNumber,
                    'candidate_id' => $this->candidateId
                ]);
            }

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SMS job completely failed', [
            'phone' => $this->phoneNumber,
            'candidate_id' => $this->candidateId,
            'error' => $exception->getMessage()
        ]);
    }
}
