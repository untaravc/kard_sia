<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;

Route::post('login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('check-reset-password-token', [AuthController::class, 'checkResetPasswordToken']);
Route::post('reset-password-with-token', [AuthController::class, 'resetPasswordWithToken']);

Route::middleware('jwt.auth')->group(function () {
    Route::get('auth', [AuthController::class, 'auth']);
    Route::get('menu', [AuthController::class, 'menu']);
    Route::resource('users', 'Api\UserController');
    Route::resource('stases', 'Api\StaseController');
});
