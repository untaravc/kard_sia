<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Sadmin\GlobalFunctionController;
use App\Http\Controllers\Sadmin\PresenceController;
use App\Http\Controllers\Sadmin\StudentController;
use App\Http\Controllers\Sadmin\DashboardController;
use App\Http\Controllers\Sadmin\StaseLogController;
use App\Http\Controllers\Sadmin\StasePlotController;
use App\Http\Controllers\Sadmin\ExamController;
use App\Http\Controllers\Sadmin\LetterController;
use App\Http\Controllers\Sadmin\StudentLogController;
use \App\Http\Controllers\Sadmin\LectureController;
use App\Http\Controllers\Sadmin\DownloadController;
use App\Http\Controllers\Sadmin\LetterRecordController;
use App\Http\Controllers\Sadmin\StudentRegController;

//ADMIN
//Auth::routes();
Route::get('/cmss/{path}', 'Sadmin\DashboardController@index')->where( 'path' , '([A-z\d\-\/_.]+)?' )->middleware('auth');
Route::get('/cmss/', 'Sadmin\DashboardController@index')->middleware('auth');
Route::group(['prefix'=>'sadmin', 'middleware'=>'auth'], function (){
    Route::resource('lectures', 'Sadmin\LectureController');
    Route::resource('users', 'Sadmin\UserController');
    Route::resource('students', 'Sadmin\StudentController');
    Route::resource('stases', 'Sadmin\StaseController');
    Route::resource('stase-logs', 'Sadmin\StaseLogController');
    Route::resource('stase-tasks', 'Sadmin\StaseTaskController');
    Route::resource('tasks', 'Sadmin\TaskController');
    Route::resource('task-details', 'Sadmin\TaskDetailController');
    Route::resource('open-stase-tasks', 'Sadmin\OpenStaseTaskController');
    Route::resource('activities', 'Sadmin\ActivityController');
    Route::resource('documents', 'Sadmin\DocumentController');
    Route::resource('presences', 'Sadmin\PresenceController');
    Route::resource('exams', 'Sadmin\ExamController');
    Route::resource('letters', 'Sadmin\LetterController');
    Route::resource('letter-records', 'Sadmin\LetterRecordController');
    Route::resource('student-logs', 'Sadmin\StudentLogController');
    Route::resource('student-regs', 'Sadmin\StudentRegController');

    Route::get('student-logs-properties', [StudentLogController::class, 'properties']);

    //Getter
    Route::get('get-stases', [GlobalFunctionController::class, 'getStase']);
    Route::get('get-stase-tasks/{id}', [GlobalFunctionController::class, 'getStaseTask']);
    Route::get('get-tasks', [GlobalFunctionController::class, 'getTasks']);
    Route::get('get-lectures', [GlobalFunctionController::class, 'getLectures']);
    Route::get('get-stases/{id}', [GlobalFunctionController::class, 'getStudentStase']);
    Route::get('task-detail/{id}', 'Sadmin\TaskDetailController@taskDetail');
    Route::get('lecture/{id}', 'Sadmin\GlobalFunctionController@lecture');
    Route::get('lecture_scoring/{id}', 'Sadmin\LectureController@lectureScoring');
    Route::get('stase-plots', [StasePlotController::class, 'index']);
    Route::get('presence-ilmiah-resume', [PresenceController::class, 'ilmiah']);
    Route::get('student-list', [StudentController::class, 'list']);
    Route::get('lecture-list', [LectureController::class, 'list']);

    //Exam
    Route::get('exam-available-students/{id}', [ExamController::class, 'exam_available_students']);
    Route::get('exam-active-students/{id}', [ExamController::class, 'exam_active_students']);
    Route::get('exam-done-students/{id}', [ExamController::class, 'exam_done_students']);
    Route::get('exam-activate-students', [ExamController::class, 'activate_student']);
    Route::get('exam-finish-students', [ExamController::class, 'finish_student']);
    Route::get('exam-cancel-score', [ExamController::class, 'cancel_student']);
    Route::get('exam-add-score', [ExamController::class, 'add_score']);

    Route::get('/student-score/{resident_id}', [StudentController::class, 'score']);
    Route::get('/student-profile/{resident_id}', 'Sadmin\StudentController@profile');
    Route::post('/add-score', [StudentController::class, 'addScore']);
    Route::post('/reset-score/{stase_task_log_id}', 'Sadmin\StudentController@resetScore');
    Route::post('/letter-records/{id}', [LetterRecordController::class, 'update']);

    // Dasboard
    Route::get('/dashboard-stase', [DashboardController::class, 'stase']);
    Route::get('/dashboard-card', [DashboardController::class, 'card']);
    Route::get('/dashboard-presence', [DashboardController::class, 'presence']);
    Route::get('/dashboard-agenda', [DashboardController::class, 'agenda']);
    Route::get('/dashboard-chart', [DashboardController::class, 'chart']);
//    Route::get('/monthly-report', [AnaliticsController::class, 'monthly_report']);
//    Route::get('/presence-graph', [AnaliticsController::class, 'presence_graph']);

    Route::get('/login-lecture/{id}', 'Sadmin\LectureController@loginLecture');
    Route::get('/login-student/{id}', 'Sadmin\StudentController@loginStudent');
    Route::get('/presences_today', [PresenceController::class, 'presence_today']);

    // Miror
    Route::post('/presence', 'Resident\DashboardController@presence');
    Route::post('/activities/init-activity', 'Resident\ActivityController@init');
    Route::post('/activities/add-participants/{id}', 'Resident\ActivityController@addParticipans');
    Route::get('/resume-presences', [PresenceController::class, 'show']);
    Route::get('/document_categories', 'Resident\DocumentController@document_categories');

    Route::get('/stase_print', [StaseLogController::class, 'print']);

    //Export
    Route::get('/resident-score-export', [StudentController::class, 'score_export']);
    Route::get('/download/penilaian', [DownloadController::class, 'penilaian']);
    Route::get('/download/activities', [DownloadController::class, 'activities']);
    Route::get('/pdf/sk-referat-lapsus/{activity_id}', [DownloadController::class, 'sk_referat_lapsus']);
    Route::get('/pdf/undangan-referat-lapsus/{activity_id}', [DownloadController::class, 'undangan_referat_lapsus']);
    Route::get('/pdf/sk-stase', [DownloadController::class, 'sk_stase']);
    Route::get('/pdf/permohonan-stase', [DownloadController::class, 'permohonan_stase']);
    Route::get('/pdf/pembimbing-stase', [DownloadController::class, 'pembimbing_stase']);
    Route::get('/pdf/logbook-stase', [DownloadController::class, 'logbook_stase']);
    Route::get('/pdf/agenda_by_lecture', [DownloadController::class, 'agenda_by_lecture']);

    // Import
    Route::post('/import-soal', [\App\Http\Controllers\Object\ImportController::class, 'import']);

    //Post
    Route::post('/letters/{id}', [LetterController::class, 'update']);
    Route::post('/student-regs-upload', [StudentRegController::class, 'upload']);
});
