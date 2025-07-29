<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

// Keep Horizon running
Schedule::command('horizon')->everyMinute()->withoutOverlapping();

// Your other scheduled tasks
Schedule::call(function () {
    Log::info('Daily SMS report task executed');
})->daily();

Schedule::call(function () {
    \App\Models\sms_logs::where('created_at', '<', now()->subDays(30))->delete();
})->weekly();
