<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LectureController;

Route::post('login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('check-reset-password-token', [AuthController::class, 'checkResetPasswordToken']);
    Route::post('reset-password-with-token', [AuthController::class, 'resetPasswordWithToken']);

Route::middleware('jwt.auth')->group(function () {
    Route::get('auth', [AuthController::class, 'auth']);
    Route::get('menu', [AuthController::class, 'menu']);
    Route::post('log-as', [AuthController::class, 'logAs']);
    Route::post('logout-as', [AuthController::class, 'logoutAs']);

    Route::resource('users', 'Api\UserController');
    Route::resource('stases', 'Api\StaseController');
    Route::get('stase-list', [\App\Http\Controllers\Api\StaseController::class, 'list']);
    Route::resource('stase-tasks', 'Api\StaseTaskController');
    Route::resource('tasks', 'Api\TaskController');
    Route::resource('lectures', 'Api\LectureController');
    Route::get('lecture-list', [\App\Http\Controllers\Api\LectureController::class, 'list']);
    Route::get('lecture-profile', [LectureController::class, 'profile']);
    Route::patch('lecture-profile', [LectureController::class, 'updateProfile']);
    Route::resource('students', 'Api\StudentController');
    Route::post('logbooks/bulk', [\App\Http\Controllers\Api\LogbookController::class, 'bulk']);
    Route::resource('logbooks', 'Api\LogbookController');
    Route::get('presences/daily', [\App\Http\Controllers\Api\PresenceController::class, 'daily']);
    Route::get('presences/monthly', [\App\Http\Controllers\Api\PresenceController::class, 'monthly']);
    Route::get('presences', [\App\Http\Controllers\Api\PresenceController::class, 'index']);
});
