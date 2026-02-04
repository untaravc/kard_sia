<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\LectureController;
use App\Http\Controllers\Api\OpenStaseTaskController;
use App\Http\Controllers\Api\ScoreController;
use App\Http\Controllers\Api\CmdController;

Route::post('login', [AuthController::class, 'login']);
Route::get('login-google/redirect', [AuthController::class, 'loginGoogleRedirect']);
Route::get('login-google/callback', [AuthController::class, 'loginGoogleCallback']);
Route::post('login-email', [AuthController::class, 'loginEmailRequest']);
Route::post('login-phone', [AuthController::class, 'loginPhoneRequest']);
Route::post('check-login-email-token', [AuthController::class, 'checkLoginEmailToken']);
Route::post('check-login-phone-token', [AuthController::class, 'checkLoginPhoneToken']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('check-reset-password-token', [AuthController::class, 'checkResetPasswordToken']);
Route::post('reset-password-with-token', [AuthController::class, 'resetPasswordWithToken']);
Route::get('firebase-config', [AuthController::class, 'firebaseConfig']);
Route::get('cmd/clear-open-stase-task', [CmdController::class, 'celarOpenStaseTask']);
Route::get('cmd/insert-accreditation', [\App\Http\Controllers\Api\AccreditationController::class, 'insertInitData']);

Route::middleware('jwt.auth')->group(function () {
    Route::get('auth', [AuthController::class, 'auth']);
    Route::get('menu', [AuthController::class, 'menu']);
    Route::post('log-as', [AuthController::class, 'logAs']);
    Route::post('logout-as', [AuthController::class, 'logoutAs']);

    Route::resource('users', 'Api\UserController');
    Route::resource('stases', 'Api\StaseController');
    Route::get('stase-list', [\App\Http\Controllers\Api\StaseController::class, 'list']);
    Route::get('student-stase', [\App\Http\Controllers\Api\StaseController::class, 'studentStase']);
    Route::post('student-stase', [\App\Http\Controllers\Api\StaseController::class, 'storeStudentStase']);
    Route::get('stase-option/{stase_id}', [\App\Http\Controllers\Api\LogbookController::class, 'staseOption']);
    Route::resource('stase-tasks', 'Api\StaseTaskController');
    Route::get('student-stase-task/{stase_id}', [\App\Http\Controllers\Api\StaseTaskController::class, 'studentStaseTask']);
    Route::resource('tasks', 'Api\TaskController');
    Route::resource('lectures', 'Api\LectureController');
    Route::get('lecture-list', [\App\Http\Controllers\Api\LectureController::class, 'list']);
    Route::get('lecture-profile', [LectureController::class, 'profile']);
    Route::patch('lecture-profile', [LectureController::class, 'updateProfile']);
    Route::get('student-profile', [\App\Http\Controllers\Api\StudentController::class, 'profile']);
    Route::patch('student-profile', [\App\Http\Controllers\Api\StudentController::class, 'updateProfile']);
    Route::get('activities-today', [ActivityController::class, 'activitiesToday']);
    Route::post('activity-presence/{activity_id}', [ActivityController::class, 'presence']);
    Route::get('release-note', [\App\Http\Controllers\Api\MarkdownController::class, 'releaseNote']);
    Route::get('open-stase-tasks', [OpenStaseTaskController::class, 'openStaseTask']);
    Route::post('open-stase-task', [OpenStaseTaskController::class, 'create']);
    Route::patch('open-stase-task/{id}', [OpenStaseTaskController::class, 'update']);
    Route::delete('open-stase-task/{id}', [OpenStaseTaskController::class, 'destroy']);
    Route::get('open-stase-task/{id}', [OpenStaseTaskController::class, 'show']);
    Route::post('files', [\App\Http\Controllers\Api\FileController::class, 'create']);
    Route::get('generate-task-log-detail', [ScoreController::class, 'generateTaskLogDetail']);
    Route::get('scoring-stat', [ScoreController::class, 'stat']);
    Route::post('stase-task-logs-update-score/{id}', [ScoreController::class, 'staseTaskLogUpdate']);
    Route::post('stase-task-logs-update-score-tesis/{id}', [ScoreController::class, 'staseTaskLogUpdateTesis']);
    Route::post('stase-task-logs-update-score-proposal/{id}', [ScoreController::class, 'staseTaskLogUpdateProposal']);
    Route::resource('activities', 'Api\ActivityController');
    Route::resource('students', 'Api\StudentController');
    Route::resource('accreditations', 'Api\AccreditationController');
    Route::get('accreditation-parent', [\App\Http\Controllers\Api\AccreditationController::class, 'getParent']);
    Route::get('accreditation-tree/{parent_idx}', [\App\Http\Controllers\Api\AccreditationController::class, 'dataTree'])
        ->where('parent_idx', '.*');
    Route::post('accreditation', [\App\Http\Controllers\Api\AccreditationController::class, 'storeEvidence']);
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
