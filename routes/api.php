<?php

use App\Http\Controllers\Api\AuthenticationController;

use App\Http\Controllers\Api\DropdownMenuController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\SmSController;
use App\Http\Controllers\Api\SupportersController;
use App\Http\Controllers\regigionsController;
use App\Http\Controllers\SmsLogsController;
use Illuminate\Support\Facades\Route;

// Routes outside middleware group for registration, login, and OTP
 Route::post('/register', [AuthenticationController::class, 'register']); // Register a new user
 Route::post('/login', [AuthenticationController::class, 'login']); // Login user
 Route::post('/send-otp', [AuthenticationController::class, 'send-otp']); // Send OTP for authentication
 Route::post('/regions-all', [regigionsController::class, 'regions']); // Send OTP for authentication


// Middleware group for authenticated routes
Route::middleware('auth:api')->group(function () {
    // Route to log out
    Route::post('/logout', [AuthenticationController::class, 'logout']); // Logout user

    // Routes for counting messages and fetching recent transactions
    Route::get('/count-messages', [SmsLogsController::class, 'countMessages']); // Count messages
    Route::get('/recent-transactions', [SmsLogsController::class, 'recentTransactions']); // Get recent transactions

    // Route to send SMS invitations (restricted to authenticated candidates)
    Route::post('/send-sms-invitation', [SmSController::class, 'sendSMSInvitation']); // Send SMS invitation
    Route::post('/supporters', [SupportersController::class, 'store']); // Store supporter data
    Route::get('/all-supporters', [SupportersController::class, 'index']); // Get all supporters

    // Route for deleting a supporter (protected by auth middleware)
    Route::middleware('auth:api')->delete('supporters/{id}', [SupportersController::class, 'destroy']);
    // Route for updating candidate profile
    Route::put('/candidate-update', [AuthenticationController::class, 'update'])->middleware('auth:api');




    // Route to send messages (authenticated users only)
    Route::post('/messages', [MessageController::class, 'sendMessageToSupporters'])->name('messages');

});

// Routes for fetching dropdown and positions data
Route::get('/dropdown-data', [DropdownMenuController::class, 'getDropdownData']); // Get dropdown data
Route::get('/positions-data', [DropdownMenuController::class, 'getPositionData']); // Get positions data
