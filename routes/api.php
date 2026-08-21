<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LectureController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\OpenStaseTaskController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\RegistrationStudentController;
use App\Http\Controllers\Api\RegistrationDetailController;
use App\Http\Controllers\Api\ScoreController;
use App\Http\Controllers\Api\CmdController;
use App\Http\Controllers\Api\LetterController;
use App\Http\Controllers\Api\UserController;

// AuthController
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
Route::get('app-config', [AuthController::class, 'appConfig']);

// SettingController (public, needed before login e.g. app name/logo/description)
Route::get('settings/label/{label}', [\App\Http\Controllers\Api\SettingController::class, 'showByLabel']);

// RegistrationStudentController
Route::post('register', [RegistrationStudentController::class, 'register']);

// CmdController
Route::get('cmd/clear-open-stase-task', [CmdController::class, 'celarOpenStaseTask']);

// AccreditationController
Route::get('cmd/insert-accreditation', [\App\Http\Controllers\Api\AccreditationController::class, 'insertInitData']);
Route::get('cmd-action-accreditation', [\App\Http\Controllers\Api\AccreditationController::class, 'cmdAction']);

// LetterController
Route::post('letters/{letter_token}/process-approval/{letter_participant_token}', [LetterController::class, 'processApproval']);

// AuthController (public magic-link scoring auth)
Route::post('pub-scoring-auth', [AuthController::class, 'pubScoringAuth']);

Route::middleware('jwt.auth')->group(function () {
    // AuthController
    Route::get('auth', [AuthController::class, 'auth']);
    Route::get('check-availability', [AuthController::class, 'checkAvailability']);
    Route::post('log-as', [AuthController::class, 'logAs']);
    Route::post('logout-as', [AuthController::class, 'logoutAs']);
    Route::post('logout', [AuthController::class, 'logout']);

    // MenuController
    Route::get('menu', [MenuController::class, 'menu']);
    Route::resource('menus', 'Api\MenuController');
    Route::get('menu-list', [MenuController::class, 'list']);

    // RoleController
    Route::resource('roles', 'Api\RoleController');
    Route::get('role-list', [\App\Http\Controllers\Api\RoleController::class, 'list']);

    // MenuRoleController
    Route::resource('menu-roles', 'Api\MenuRoleController');
    Route::post('menu-roles-sync', [\App\Http\Controllers\Api\MenuRoleController::class, 'sync']);

    // DashboardController
    Route::get('dashboard-stats', [DashboardController::class, 'stat']);
    Route::get('dashboard-open-stase-task-matric', [DashboardController::class, 'openStaseTaskScoreMatric']);

    // RegistrationStudentController
    Route::post('process-copy', [RegistrationStudentController::class, 'processCopy']);
    Route::get('registration', [RegistrationStudentController::class, 'show']);
    Route::patch('registration/profile', [RegistrationStudentController::class, 'setProfile']);
    Route::patch('registration/identity', [RegistrationStudentController::class, 'setIdentity']);
    Route::patch('registration/register', [RegistrationStudentController::class, 'setRegistration']);
    Route::patch('registration/education-bg', [RegistrationStudentController::class, 'setEducationBackground']);
    Route::patch('registration/institution', [RegistrationStudentController::class, 'setInstitution']);
    Route::patch('registration/family', [RegistrationStudentController::class, 'setFamily']);
    Route::patch('registration/score', [RegistrationStudentController::class, 'setScore']);
    Route::patch('registration/status', [RegistrationStudentController::class, 'setStatus']);

    // RegistrationDetailController
    Route::resource('registration-details', RegistrationDetailController::class)->only(['store', 'update', 'destroy']);

    // UserController
    Route::resource('users', 'Api\UserController');
    Route::patch('users/{id}/status', [UserController::class, 'updateStatus']);
    Route::get('user-list', [UserController::class, 'list']);

    // SettingController
    Route::resource('settings', 'Api\SettingController');

    // FormOptionController
    Route::resource('form-options', 'Api\FormOptionController');
    Route::get('form-option-list', [\App\Http\Controllers\Api\FormOptionController::class, 'list']);

    // StudyProgramController
    Route::resource('study-programs', 'Api\StudyProgramController');
    Route::get('study-program-list', [\App\Http\Controllers\Api\StudyProgramController::class, 'list']);

    // StaseController
    Route::resource('stases', 'Api\StaseController');
    Route::get('stase-list', [\App\Http\Controllers\Api\StaseController::class, 'list']);
    Route::get('stase-list-all', [\App\Http\Controllers\Api\StaseController::class, 'listAll']);
    Route::get('student-stase', [\App\Http\Controllers\Api\StaseController::class, 'studentStase']);
    Route::get('student-checklist', [\App\Http\Controllers\Api\StaseController::class, 'studentChecklist']);
    Route::post('student-stase', [\App\Http\Controllers\Api\StaseController::class, 'storeStudentStase']);
    Route::patch('student-stase/{id}', [\App\Http\Controllers\Api\StaseController::class, 'updateStudentStase']);

    // StaseTaskController
    Route::resource('stase-tasks', 'Api\StaseTaskController');
    Route::get('student-stase-task/{stase_id}', [\App\Http\Controllers\Api\StaseTaskController::class, 'studentStaseTask2']);
    Route::get('student-stase-task2/{stase_id}', [\App\Http\Controllers\Api\StaseTaskController::class, 'studentStaseTask2']);

    // LogbookController
    Route::get('stase-option/{stase_id}', [\App\Http\Controllers\Api\LogbookController::class, 'staseOption']);
    Route::get('student-logs/{stase_id}', [\App\Http\Controllers\Api\LogbookController::class, 'studentLog']);
    Route::post('logbooks/bulk', [\App\Http\Controllers\Api\LogbookController::class, 'bulk']);
    Route::post('logbooks/approve', [\App\Http\Controllers\Api\LogbookController::class, 'approve']);
    Route::post('logbook-student-add', [\App\Http\Controllers\Api\LogbookController::class, 'storeDaily']);
    Route::put('logbook-student-add/{id}', [\App\Http\Controllers\Api\LogbookController::class, 'updateDaily']);
    Route::get('logbook-student-competence', [\App\Http\Controllers\Api\LogbookController::class, 'competenceOptions']);
    Route::resource('logbooks', 'Api\LogbookController');

    // TaskController
    Route::resource('tasks', 'Api\TaskController');
    Route::get('task-list', [\App\Http\Controllers\Api\TaskController::class, 'list']);

    // TaskDetailController
    Route::resource('task-details', 'Api\TaskDetailController');

    // LectureController
    Route::resource('lectures', 'Api\LectureController');
    Route::patch('lectures/{id}/status', [LectureController::class, 'updateStatus']);
    Route::get('lecture-list', [\App\Http\Controllers\Api\LectureController::class, 'list']);
    Route::get('lecture-profile', [LectureController::class, 'profile']);
    Route::patch('lecture-profile', [LectureController::class, 'updateProfile']);

    // StudentController
    Route::resource('students', 'Api\StudentController');
    Route::patch('students/{id}/status', [\App\Http\Controllers\Api\StudentController::class, 'updateStatus']);
    Route::get('student-status-counts', [\App\Http\Controllers\Api\StudentController::class, 'statusCounts']);
    Route::get('student-oldest-year', [\App\Http\Controllers\Api\StudentController::class, 'oldestYear']);
    Route::get('student-list', [\App\Http\Controllers\Api\StudentController::class, 'studentList']);
    Route::get('student-profile', [\App\Http\Controllers\Api\StudentController::class, 'profile']);
    Route::patch('student-profile', [\App\Http\Controllers\Api\StudentController::class, 'updateProfile']);
    Route::get('student-score/{student_id}', [\App\Http\Controllers\Api\StudentController::class, 'score']);

    // PresenceController
    Route::get('student-presence-check', [\App\Http\Controllers\Api\PresenceController::class, 'studentPresenceCheck']);
    Route::get('presences', [\App\Http\Controllers\Api\PresenceController::class, 'index']);
    Route::get('presences/daily', [\App\Http\Controllers\Api\PresenceController::class, 'daily']);
    Route::get('presences/monthly', [\App\Http\Controllers\Api\PresenceController::class, 'monthly']);
    Route::get('presences/student/{student_id}', [\App\Http\Controllers\Api\PresenceController::class, 'student']);
    Route::get('student-daily-check', [\App\Http\Controllers\Api\PresenceController::class, 'studentDailyCheck']);
    Route::post('student-daily', [\App\Http\Controllers\Api\PresenceController::class, 'studentDaily']);

    // ActivityController
    Route::resource('activities', 'Api\ActivityController');
    Route::get('activities-today', [ActivityController::class, 'activitiesToday']);
    Route::post('activity-presence/{activity_id}', [ActivityController::class, 'presence']);
    Route::post('activities/import-presence/preview', [ActivityController::class, 'previewImportPresence']);
    Route::post('activities/{activity_id}/import-presence', [ActivityController::class, 'importPresence']);

    // MarkdownController
    Route::get('release-note', [\App\Http\Controllers\Api\MarkdownController::class, 'releaseNote']);

    // PostController
    Route::resource('posts', 'Api\PostController');

    // OpenStaseTaskController
    Route::get('open-stase-tasks', [OpenStaseTaskController::class, 'openStaseTask']);
    Route::post('open-stase-task', [OpenStaseTaskController::class, 'create']);
    Route::patch('open-stase-task/{id}', [OpenStaseTaskController::class, 'update']);
    Route::delete('open-stase-task/{id}', [OpenStaseTaskController::class, 'destroy']);
    Route::get('open-stase-task/{id}', [OpenStaseTaskController::class, 'show']);
    Route::post('open-stase-task/{id}/notify-email', [OpenStaseTaskController::class, 'notifyEmail']);
    Route::post('open-stase-task/{id}/notify-whatsapp', [OpenStaseTaskController::class, 'notifyWhatsapp']);

    // AttendanceCodeController (rotating co-presence code for open stase tasks)
    Route::get('attendance-code', [\App\Http\Controllers\Api\AttendanceCodeController::class, 'lectureCode']);
    Route::post('attendance-confirm', [\App\Http\Controllers\Api\AttendanceCodeController::class, 'confirm']);

    // FileController
    Route::post('files', [\App\Http\Controllers\Api\FileController::class, 'create']);

    // ScoreController
    Route::get('generate-task-log-detail', [ScoreController::class, 'generateTaskLogDetail']);
    Route::get('scoring-stat', [ScoreController::class, 'stat']);
    Route::post('stase-task-logs-update-score/{id}', [ScoreController::class, 'staseTaskLogUpdate']);
    Route::post('stase-task-logs-update-score-tesis/{id}', [ScoreController::class, 'staseTaskLogUpdateTesis']);
    Route::post('stase-task-logs-update-score-proposal/{id}', [ScoreController::class, 'staseTaskLogUpdateProposal']);

    // StudentMonitoringController
    Route::get('student-monitoring', [\App\Http\Controllers\Api\StudentMonitoringController::class, 'index']);
    Route::get('student-monitoring-detail', [\App\Http\Controllers\Api\StudentMonitoringController::class, 'detail']);
    Route::get('student-monitoring-logbook', [\App\Http\Controllers\Api\StudentMonitoringController::class, 'logbook']);
    Route::get('student-monitoring-logbook-summary', [\App\Http\Controllers\Api\StudentMonitoringController::class, 'summary']);
    Route::get('student-monitoring-presence', [\App\Http\Controllers\Api\StudentMonitoringController::class, 'presence']);
    Route::get('student-monitoring-presence-detail', [\App\Http\Controllers\Api\StudentMonitoringController::class, 'presenceDetail']);
    Route::get('student-monitoring-presence-summary', [\App\Http\Controllers\Api\StudentMonitoringController::class, 'presenceSummary']);

    // StaseLogController
    Route::get('stase-logs', [\App\Http\Controllers\Api\StaseLogController::class, 'index']);
    Route::get('stase-log-check', [\App\Http\Controllers\Api\StaseLogController::class, 'staseLogCheck']);

    // AccreditationController
    Route::resource('accreditations', 'Api\AccreditationController');
    Route::get('accreditation-parent', [\App\Http\Controllers\Api\AccreditationController::class, 'getParent']);
    Route::get('accreditation-tree/{parent_idx}', [\App\Http\Controllers\Api\AccreditationController::class, 'dataTree'])
        ->where('parent_idx', '.*');
    Route::post('accreditation', [\App\Http\Controllers\Api\AccreditationController::class, 'storeEvidence']);

    // StaseTaskLogController
    Route::get('stase-task-logs', [\App\Http\Controllers\Api\StaseTaskLogController::class, 'index']);
    Route::get('stase-task-logs-export', [\App\Http\Controllers\Api\StaseTaskLogController::class, 'exportExcel']);
    Route::post('update-score', [\App\Http\Controllers\Api\StaseTaskLogController::class, 'updateScore']);
    Route::post('add-score', [\App\Http\Controllers\Api\StaseTaskLogController::class, 'createScore']);
    Route::post('lecture-add-score', [\App\Http\Controllers\Api\StaseTaskLogController::class, 'lectureAddScore']);
    Route::post('delete-score', [\App\Http\Controllers\Api\StaseTaskLogController::class, 'deleteScore']);

    // DeviceTokenController
    Route::resource('device-tokens', 'Api\DeviceTokenController');

    // NotificationController
    Route::resource('notifications', 'Api\NotificationController');
    Route::post('push-notifications', [\App\Http\Controllers\Api\NotificationController::class, 'pushNotif']);

    // WebNotificationController (Firestore-backed in-app notifications)
    Route::get('web-notifications', [\App\Http\Controllers\Api\WebNotificationController::class, 'index']);
    Route::post('web-notifications/mark-all-read', [\App\Http\Controllers\Api\WebNotificationController::class, 'markAllRead']);
    Route::post('web-notifications/{id}/mark-read', [\App\Http\Controllers\Api\WebNotificationController::class, 'markRead']);

    Route::get('notification/insufficient-logbook/preview', [\App\Http\Controllers\Api\NotificationController::class, 'insufficientLogbookPreview']);
    Route::post('notification/insufficient-logbook', [\App\Http\Controllers\Api\NotificationController::class, 'insufficientLogbook']);
    Route::get('notification/insufficient-score/preview', [\App\Http\Controllers\Api\NotificationController::class, 'insufficientScorePreview']);
    Route::post('notification/insufficient-score', [\App\Http\Controllers\Api\NotificationController::class, 'insufficientScore']);
    Route::get('notification/insufficient-presence/preview', [\App\Http\Controllers\Api\NotificationController::class, 'insufficientPresencePreview']);
    Route::post('notification/insufficient-presence', [\App\Http\Controllers\Api\NotificationController::class, 'insufficientPresence']);

    // RegistrationController
    // Literal routes must stay before the resource() call below, otherwise
    // its PATCH registrations/{registration} route would swallow them.
    Route::patch('registrations/score-administration', [RegistrationController::class, 'setScoreAdministration']);
    Route::patch('registrations/score-administration-all', [RegistrationController::class, 'setScoreAdministrationAll']);
    Route::patch('registrations/{id}/status', [RegistrationController::class, 'updateStatus']);
    Route::resource('registrations', 'Api\RegistrationController');

    // OffDayController
    Route::resource('off-days', 'Api\OffDayController');

    // AssetController
    Route::resource('assets', 'Api\AssetController');
    Route::get('assets-properties', [\App\Http\Controllers\Api\AssetController::class, 'properties']);

    // AssetLogController
    Route::get('assets-logs/{asset_id}', [\App\Http\Controllers\Api\AssetLogController::class, 'index']);
    Route::post('assets-logs/{asset_id}', [\App\Http\Controllers\Api\AssetLogController::class, 'store']);
    Route::get('assets-logs/{asset_id}/{id}', [\App\Http\Controllers\Api\AssetLogController::class, 'show']);
    Route::patch('assets-logs/{asset_id}/{id}', [\App\Http\Controllers\Api\AssetLogController::class, 'update']);
    Route::delete('assets-logs/{asset_id}/{id}', [\App\Http\Controllers\Api\AssetLogController::class, 'destroy']);

    // MailLogController
    Route::resource('mail-logs', 'Api\MailLogController');

    // ActionLogController
    Route::get('action-logs', [\App\Http\Controllers\Api\ActionLogController::class, 'index']);
    Route::delete('action-logs-cleanup', [\App\Http\Controllers\Api\ActionLogController::class, 'cleanup']);

    // LetterController
    Route::resource('letters', 'Api\LetterController');
    Route::post('letters/{id}/propose-approval', [LetterController::class, 'proposeApproval']);
    Route::post('letters/{id}/notify-approver', [LetterController::class, 'notifyApprover']);
    Route::post('letter-clone/{id}', [LetterController::class, 'cloneLetter']);

    // Form Builder (Google Form style)
    // Form\ResponseController
    Route::get('forms/{form}/responses', [\App\Http\Controllers\Api\Form\ResponseController::class, 'index']);
    Route::get('form-responses/{id}', [\App\Http\Controllers\Api\Form\ResponseController::class, 'show']);
    Route::delete('form-responses/{id}', [\App\Http\Controllers\Api\Form\ResponseController::class, 'destroy']);

    // Form\FormController
    Route::resource('forms', 'Api\Form\FormController');
});
