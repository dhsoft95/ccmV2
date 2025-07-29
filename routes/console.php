<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

// Clean up old SMS logs weekly
Schedule::call(function () {
    $deleted = \App\Models\sms_logs::where('created_at', '<', now()->subDays(30))->delete();
    Log::info("Cleaned up {$deleted} old SMS logs");
})->weekly();

// Daily SMS report
Schedule::call(function () {
    $todayCount = \App\Models\sms_logs::whereDate('created_at', today())->count();
    $successCount = \App\Models\sms_logs::whereDate('created_at', today())->where('status', 1)->count();
    Log::info("Daily SMS Report: {$successCount}/{$todayCount} sent successfully");
})->dailyAt('18:00');

// Optional: Restart queue workers every few hours to prevent memory issues
Schedule::command('queue:restart')->everySixHours();
