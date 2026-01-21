<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;

Route::post('login', [AuthController::class, 'login']);
Route::post('fotgot-password', [AuthController::class, 'forgotPassword']);
Route::get('menu', [AuthController::class, 'menu']);
