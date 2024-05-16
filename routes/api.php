<?php
use App\Http\Controllers\Api\AuthenticationController;
use App\Http\Controllers\Api\CandidateController; // Assuming you have one
use App\Http\Controllers\Api\DropdownMenuController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\SmSController;
use App\Http\Controllers\Api\SupportersController;
use Illuminate\Support\Facades\Route;

// Route to get dropdown data
Route::get('/dropdown-data', [DropdownMenuController::class, 'getDropdownData']);
Route::get('/positions-data', [DropdownMenuController::class, 'getPositionData']);

// Middleware group for authenticated routes
Route::middleware('auth:api')->group(function () {
    // Route to logout
    Route::post('/logout', [AuthenticationController::class, 'logout']);

    // Route to send SMS invitations (restricted to authenticated candidates)

    Route::middleware('auth:api')->post('/send-sms-invitation', [SmSController::class, 'sendSMSInvitation']);
    Route::middleware('auth:api')->post('/supporters', [SupportersController::class, 'store']);
    Route::middleware('auth')->get('/all-supporters', [SupportersController::class, 'index']);


    // Route to send messages (authenticated users only)
    Route::post('/messages', [MessageController::class, 'sendMessageToSupporters'])
        ->name('messages');
});


// Route to register and login (outside the middleware group)
Route::post('/register', [AuthenticationController::class, 'register']);
Route::post('/login', [AuthenticationController::class, 'login']);
Route::post('/sendOtp', [AuthenticationController::class, 'sendOtp']);
//Route::post('/verifyOtp', [AuthenticationController::class, 'verifyOtp']);



//Route::group(['middleware' => ['auth:api']], function () {
////    Route::get('/all-supporters', 'SupportersController@index');
//});
