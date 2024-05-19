<?php

namespace App\Http\Controllers;

use App\Models\sms_logs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmsLogsController extends Controller
{
    public function countMessages()
    {
        $candidateId = Auth::id();
        \Log::info('Authenticated User ID in countMessages: ' . $candidateId);

        $successfulCount = sms_logs::where('candidate_id', $candidateId)
            ->where('status', 1)
            ->count();
        $failedCount = sms_logs::where('candidate_id', $candidateId)
            ->where('status', 0)
            ->count();
        $pendingCount = sms_logs::where('candidate_id', $candidateId)
            ->where('status', -1)
            ->count();

        return response()->json([
            'successful_count' => $successfulCount,
            'pending_count' => $pendingCount,
            'failed_count' => $failedCount,
        ]);
    }

    public function recentTransactions()
    {
        $candidateId = Auth::id();
        \Log::info('Authenticated User ID in recentTransactions: ' . $candidateId);

        $recentTransactions = sms_logs::where('candidate_id', $candidateId)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        return response()->json($recentTransactions);
    }
}
