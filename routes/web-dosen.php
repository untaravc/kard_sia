<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Lecture\HomeController;
use App\Http\Controllers\Lecture\ProfileController;
use App\Http\Controllers\Lecture\StaseTaskController;
use App\Http\Controllers\Lecture\ResidentLogController;

//DOSEN
Route::get('dosen-login','Auth\LectureLoginController@showLoginForm');
Route::post('dosen-login', 'Auth\LectureLoginController@login');
Route::post('dosen-logout', 'Auth\LectureLoginController@logout');

Route::get('dosen', 'Lecture\HomeController@index')->middleware('auth:lecture');
Route::get('/dosen/{path}', 'Lecture\HomeController@index')->where( 'path' , '([A-z\d\-\/_.]+)?' )->middleware('auth:lecture');
Route::group(['prefix'=>'cmsd', 'middleware'=>'auth:lecture'], function (){
    Route::resource('profiles', 'Lecture\ProfileController');
    Route::resource('activities', 'Lecture\ActivityController');
    Route::resource('documents', 'Lecture\DocumentController');
    Route::resource('ksm-schedules', 'Lecture\KsmScheduleController');

    //Profile
    Route::get('/home', [HomeController::class, 'index']);
    Route::get('user', [ProfileController::class, 'user']);
    Route::post('update_password', 'Lecture\ProfileController@updatePassword');
    Route::post('update_profile', [ProfileController::class, 'update_profile']);

    //Function
    Route::post('stase-task-logs-update-score/{id}', 'Lecture\StaseTaskLogController@updateScore');
    Route::post('stase-task-logs-update-score-tesis/{id}', 'Lecture\StaseTaskLogController@updateScoreTesis');
    Route::post('stase-task-logs-update-score-proposal/{id}', 'Lecture\StaseTaskLogController@updateScoreProposal');
    Route::post('/presence', [HomeController::class, 'presence']);
    Route::get('stase-task-log/jurnal/{id}', 'Sadmin\StaseTaskLogController@showJurnal');
    Route::get('/get-open-stase-task', [HomeController::class, 'openStaseTask']);
    Route::get('/get-open-stase-task-all', [HomeController::class, 'openStaseTaskAll']);
    Route::get('/generate-task-log-detail', [StaseTaskController::class, 'generateTaskLogDetail']);
    Route::get('/get-schedule', [HomeController::class, 'getSchedule']);
    Route::get('/history', [HomeController::class,'history']);
    Route::get('/resident-log-list', [ResidentLogController::class,'resident_list']);
    Route::get('/resident-log-list/{id}', [ResidentLogController::class,'resident_log_list']);
    Route::post('/resident-log-list/{id}', [ResidentLogController::class,'resident_log_list_acc']);

    //Mirror
    Route::resource('students', 'Sadmin\StudentController');
    Route::resource('task-details', 'Sadmin\TaskDetailController');
    Route::get('/student-score/{resident_id}', 'Sadmin\StudentController@score');
    Route::get('/dashboard-stase', 'Sadmin\DashboardController@stase');
    Route::get('/resume-presences', 'Sadmin\PresenceController@show');
    Route::get('/presences', [\App\Http\Controllers\Sadmin\PresenceController::class, 'index']);
    Route::get('/presences_today', [\App\Http\Controllers\Sadmin\PresenceController::class, 'presence_today']);
    Route::get('/stase-plots', [\App\Http\Controllers\Sadmin\StasePlotController::class, 'index']);
    Route::get('get-stases', [\App\Http\Controllers\Sadmin\GlobalFunctionController::class, 'getStase']);
    Route::get('document_categories', [\App\Http\Controllers\Resident\DocumentController::class, 'document_categories']);
});
