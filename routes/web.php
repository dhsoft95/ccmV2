<?php

use App\Http\Controllers\regigionsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/insert-all-data', [regigionsController::class, 'insertJsonData']);
