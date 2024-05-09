<?php
use App\Http\Controllers\Api\AuthenticationController;
use App\Http\Controllers\Api\CandidateController; // Assuming you have one
use App\Http\Controllers\Api\DropdownMenuController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\SmSController;
use Illuminate\Support\Facades\Route;

// Route to get dropdown data
Route::get('/dropdown-data', [DropdownMenuController::class, 'getDropdownData']);

// Middleware group for authenticated routes
Route::middleware('auth:api')->group(function () {
    // Route to logout
    Route::post('/logout', [AuthenticationController::class, 'logout']);

    // Route to send SMS invitations (restricted to authenticated candidates)

    Route::middleware('auth:api')->post('/send-sms-invitation', [SmSController::class, 'sendSMSInvitation']);


    // Route to send messages (authenticated users only)
    Route::post('/messages', [MessageController::class, 'sendMessageToSupporters'])
        ->name('messages');
});

// Route to register and login (outside the middleware group)
Route::post('/register', [AuthenticationController::class, 'register']);
Route::post('/login', [AuthenticationController::class, 'login']);
