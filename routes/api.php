<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\LectureController;
use App\Http\Controllers\Api\OpenStaseTaskController;
use App\Http\Controllers\Api\ScoreController;
use App\Http\Controllers\Api\CmdController;

Route::post('login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('check-reset-password-token', [AuthController::class, 'checkResetPasswordToken']);
Route::post('reset-password-with-token', [AuthController::class, 'resetPasswordWithToken']);
Route::get('firebase-config', [AuthController::class, 'firebaseConfig']);
Route::get('cmd/clear-open-stase-task', [CmdController::class, 'celarOpenStaseTask']);

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
    Route::get('activities-today', [ActivityController::class, 'activitiesToday']);
    Route::get('open-stase-tasks', [OpenStaseTaskController::class, 'openStaseTask']);
    Route::get('generate-task-log-detail', [ScoreController::class, 'generateTaskLogDetail']);
    Route::get('scoring-stat', [ScoreController::class, 'stat']);
    Route::post('stase-task-logs-update-score/{id}', [ScoreController::class, 'staseTaskLogUpdate']);
    Route::post('stase-task-logs-update-score-tesis/{id}', [ScoreController::class, 'staseTaskLogUpdateTesis']);
    Route::post('stase-task-logs-update-score-proposal/{id}', [ScoreController::class, 'staseTaskLogUpdateProposal']);
    Route::resource('activities', 'Api\ActivityController');
    Route::resource('students', 'Api\StudentController');
    Route::get('student-score/{student_id}', [\App\Http\Controllers\Api\StudentController::class, 'score']);
    Route::post('update-score', [\App\Http\Controllers\Api\StaseTaskLogController::class, 'updateScore']);
    Route::post('add-score', [\App\Http\Controllers\Api\StaseTaskLogController::class, 'createScore']);
    Route::post('lecture-add-score', [\App\Http\Controllers\Api\StaseTaskLogController::class, 'lectureAddScore']);
    Route::post('delete-score', [\App\Http\Controllers\Api\StaseTaskLogController::class, 'deleteScore']);
    Route::get('stase-task-logs', [\App\Http\Controllers\Api\StaseTaskLogController::class, 'index']);
    Route::post('logbooks/bulk', [\App\Http\Controllers\Api\LogbookController::class, 'bulk']);
    Route::resource('logbooks', 'Api\LogbookController');
    Route::get('presences/daily', [\App\Http\Controllers\Api\PresenceController::class, 'daily']);
    Route::get('presences/monthly', [\App\Http\Controllers\Api\PresenceController::class, 'monthly']);
    Route::get('presences/student/{student_id}', [\App\Http\Controllers\Api\PresenceController::class, 'student']);
    Route::get('presences', [\App\Http\Controllers\Api\PresenceController::class, 'index']);
});
