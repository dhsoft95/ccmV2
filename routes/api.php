<?php

use App\Http\Controllers\Api\AuthenticationController;
use App\Http\Controllers\Api\CandidateController;
use App\Http\Controllers\Api\MessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::post('/messages', [MessageController::class, 'sendMessageToSupporters'])->name('messages');

Route::post('/candidates', [CandidateController::class, 'store']);

Route::post('/register',[AuthenticationController::class,'register']);
Route::post('/login',[AuthenticationController::class,'login']);
Route::middleware('auth:api')->group(function(){
    Route::post('/logout',[AuthenticationController::class,'logout']);


});
