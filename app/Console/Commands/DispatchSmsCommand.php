<?php

namespace App\Console\Commands;

use App\Models\sms_logs;
use Illuminate\Console\Command;

class DispatchSmsCommand extends Command
{
    protected $signature = 'sms:daily-report';
    protected $description = 'Send daily SMS usage report';

    public function handle()
    {
        $todayCount = sms_logs::whereDate('created_at', today())->count();
        $successCount = sms_logs::whereDate('created_at', today())
            ->where('status', 1)
            ->count();

        $this->info("SMS Report: {$successCount}/{$todayCount} sent successfully today");

        // Send report via email, Slack, etc.

        return Command::SUCCESS;
    }
}
