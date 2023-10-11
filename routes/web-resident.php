<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Resident\ResidentController;
use App\Http\Controllers\Resident\ProctorshipController;
use App\Http\Controllers\Resident\ProfileController;
use App\Http\Controllers\Resident\DashboardController;
use App\Http\Controllers\Resident\DocumentController;
use App\Http\Controllers\Resident\StaseController;
use App\Http\Controllers\Resident\StudentLogController;

//Resident
Route::get('resident-login','Auth\StudentLoginController@showLoginForm');
Route::post('resident-login', 'Auth\StudentLoginController@login');
Route::post('resident-logout', 'Auth\StudentLoginController@logout');

Route::get('/resident', 'Resident\DashboardController@index')->middleware('auth:student');
Route::get('/resident/{path}', 'Resident\DashboardController@index')->where( 'path' , '([A-z\d\-\/_.]+)?' )->middleware('auth:student');
Route::group(['prefix'=>'cmsr', 'middleware'=>'auth:student'], function (){
    Route::resource('profiles', 'Resident\ProfileController');
    Route::resource('activities', 'Resident\ActivityController');
    Route::resource('residents', 'Resident\ResidentController');
    Route::resource('documents', 'Resident\DocumentController');
    Route::resource('exams', 'Resident\ExamController');
    Route::resource('student-logs', 'Resident\StudentLogController');

    Route::post('student-logs/{id}', [StudentLogController::class, 'update']);

    Route::get('/stase', [StaseController::class, 'index']);
    Route::get('/take-evaluation-stase/{stase_log_id}', [StaseController::class, 'evaluation_link']);
    Route::get('/stase_active', 'Resident\StaseController@active');
    Route::post('/open-stase-task', [StaseController::class, 'openStaseTask']);
    Route::get('/close-stase-task', 'Resident\StaseController@closeStaseTask');
    Route::get('/get-schedule', 'Resident\DashboardController@getSchedule');
    Route::post('/presence', 'Resident\DashboardController@presence');
    Route::post('/activities/init-activity', 'Resident\ActivityController@init');
    Route::post('/activities/add-participants/{id}', 'Resident\ActivityController@addParticipans');

    Route::get('user', 'Resident\ProfileController@user');
    Route::get('delete-file/{id}', 'Resident\ResidentController@deleteFile');
    Route::post('upload-file', [ResidentController::class,'uploadFile']);
    Route::post('update_profile', [ProfileController::class, 'updateProfile']);
    Route::post('student-logs-bulk', [StudentLogController::class, 'create_bulk']);

    Route::get('select-active-stase', [DashboardController::class,'select_active_stase']);
    Route::get('get-stases/{id}', 'Sadmin\GlobalFunctionController@getStudentStase');
    Route::get('add-stase', 'Resident\DashboardController@add_stase');
    Route::get('info-cards', 'Resident\DashboardController@info_cards');
    Route::get('get-stase-log', [DashboardController::class, 'stase_log']);
    Route::get('document_categories', [DocumentController::class, 'document_categories']);

    Route::get('stase-score/{id}', [StaseController::class, 'staseScore']);

    // Log book
    Route::get('log-category/{id}', [StudentLogController::class, 'log_category']);
    Route::get('stase-list', [StudentLogController::class, 'staseList']);
    Route::get('logbook-pdf/{id}', [StudentLogController::class, 'pdf']);
    Route::get('compile-logbook-pdf', [StudentLogController::class, 'compile_pdf']);
//    Route::get('identity-pdf/{id}', [StudentLogController::class, 'identity']);
    Route::get('identity-pdf', [StudentLogController::class, 'identity']);

    //Mirror
    Route::get('/student-score/{resident_id}', 'Sadmin\StudentController@score');
    Route::get('/students/{resident_id}', 'Sadmin\StudentController@show');
    Route::get('/resume-presences', 'Sadmin\PresenceController@show');
});

Route::get('/e/{token}', [ProctorshipController::class, 'exam']);
