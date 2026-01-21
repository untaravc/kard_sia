<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;

Route::post('login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('check-reset-password-token', [AuthController::class, 'checkResetPasswordToken']);
Route::post('reset-password-with-token', [AuthController::class, 'resetPasswordWithToken']);
Route::get('auth', [AuthController::class, 'auth'])->middleware('jwt.auth');
Route::get('menu', [AuthController::class, 'menu']);
