<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\HomeController;
use \App\Http\Controllers\Sadmin\GlobalFunctionController;
use \App\Http\Controllers\Sadmin\DashboardController;
use \App\Http\Controllers\Object\PublicController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/usulan-kurikulum', function () {
    return redirect('https://forms.gle/EFjV8fowe3hkQ8z69');
});

//ADMIN
Auth::routes();
require('web-admin.php');
require('web-dosen.php');
require('web-resident.php');

// Object
Route::get('/object/{path}', 'Sadmin\DashboardController@index')
    ->where( 'path' , '([A-z\d\-\/_.]+)?' )
    ->middleware('shared');
Route::group(['prefix'=>'objects', 'middleware'=>'shared'], function () {
    Route::resource('quizzes', 'Object\QuizController');
    Route::resource('categories', 'Object\CategoryController');
    Route::resource('sections', 'Object\SectionController');
    Route::resource('quiz-students', 'Object\QuizStudentController');

    Route::get('/get-categories', [\App\Http\Controllers\Object\CategoryController::class, 'list']);
    Route::get('/quiz-list', [\App\Http\Controllers\Object\QuizController::class, 'list']);
    Route::post('/image-upload', [\App\Http\Controllers\Object\ImageController::class, 'upload']);
    Route::post('/sections-quiz/{section_id}', [\App\Http\Controllers\Object\SectionController::class, 'sections_quiz']);
    Route::get('/section-quiz-reorder', [\App\Http\Controllers\Object\SectionController::class, 'ordering_quiz']);
    Route::get('/section-quiz-delete', [\App\Http\Controllers\Object\SectionController::class, 'delete_quiz']);
});

// Form
Route::get('/f/{path}', [DashboardController::class, 'form'])
    ->where( 'path' , '([A-z\d\-\/_.]+)?' )->middleware('auth');
Route::get('/pub/quiz', [PublicController::class, 'quiz']);
Route::post('/pub/select_option', [PublicController::class, 'select_option']);
Route::post('/pub/quiz-finish', [PublicController::class, 'quiz_finish']);

//OPEN
Route::get('l/{slug}', 'Sadmin\GlobalFunctionController@openLink');
Route::get('get-lectures', 'Sadmin\GlobalFunctionController@getLectures');
Route::get('get-stase', [GlobalFunctionController::class, 'staseList']);
Route::get('get-categories', [GlobalFunctionController::class, 'getCategories']);
Route::get('document-categories', [GlobalFunctionController::class, 'documentCategories']);
Route::get('stase-option/{id}', [GlobalFunctionController::class, 'staseOption']);
Route::get('upload', function (){ return view('upload-excel');});
Route::get('get-activity-cats', [GlobalFunctionController::class, 'getActCats']);
Route::post('upload', 'Sadmin\GlobalFunctionController@uploadExcel');
Route::get('email-confirmation', [GlobalFunctionController::class, 'email_confirmation']);

//Presensi
Route::get('presensi/{id}', [PresensiController::class, 'view_presence']);
Route::post('presensi/{id}', [PresensiController::class, 'presence']);

Route::get('daily', [PresensiController::class, 'view_daily']);
Route::get('daily_beta', [PresensiController::class, 'view_daily_beta']);
Route::post('daily', [PresensiController::class, 'daily']);
Route::post('daily_beta', [PresensiController::class, 'daily_beta']);

Route::get('tendik', [PresensiController::class, 'view_tendik']);
Route::post('tendik', [PresensiController::class, 'daily']);
Route::get('daily_resume', [PresensiController::class, 'daily_resume']);

Route::get('test-pdf', [HomeController::class,'test_pdf']);
Route::get('agenda', 'HomeController@agenda');
Route::post('page', 'HomeController@upload');
Route::get('test', [HomeController::class, 'test']);
Route::get('make-token', 'HomeController@make_token');

Route::get('123logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index')->middleware('auth');

Route::get('timer', [\App\Http\Controllers\TimerController::class, 'index']);
Route::get('timer-data', [\App\Http\Controllers\TimerController::class, 'timer']);
Route::get('timer_setting', [\App\Http\Controllers\TimerController::class, 'timer_setting']);
Route::post('timer_setting', [\App\Http\Controllers\TimerController::class, 'timer_setting_update']);

Route::group(['prefix'=>'test'], function(){
    Route::get('cek', [\App\Http\Controllers\TestController::class, 'test']);
    Route::get('get_form_option', [\App\Http\Controllers\TestController::class, 'get_form_option']);
});
