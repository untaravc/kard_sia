const admin_prefix = '/blu';
const reg_prefix = '/reg';

const requireAuth = (to, from, next) => {
    const token = localStorage.getItem('token');
    if (!token) {
        next(admin_prefix + '/login');
        return;
    }

    next();
};

const redirectIfAuth = (to, from, next) => {
    const token = localStorage.getItem('token');
    if (token) {
        next(admin_prefix + '/dashboard');
        return;
    }

    next();
};

const requireRegAuth = (to, from, next) => {
    const token = localStorage.getItem('token');
    if (!token) {
        next(reg_prefix + '/login');
        return;
    }

    next();
};

const redirectIfRegAuth = (to, from, next) => {
    const token = localStorage.getItem('token');
    if (token) {
        next(reg_prefix + '/index');
        return;
    }

    next();
};

const routes = [
    {
        path: reg_prefix,
        component: require('./pages/registration/Layout.vue').default,
        children: [
            {
                path: 'login',
                component: require('./pages/registration/Login.vue').default,
                beforeEnter: redirectIfRegAuth,
                meta: { page_name: 'Registration Login' },
            },
            {
                path: 'index',
                component: require('./pages/registration/Index.vue').default,
                beforeEnter: requireRegAuth,
                meta: { page_name: 'Registration' },
                children: [
                    { path: '', component: require('./pages/registration/Home.vue').default, meta: { page_name: 'Registration Home' } },
                    { path: 'account', component: require('./pages/registration/Account.vue').default, meta: { page_name: 'Registration Account' } },
                    { path: 'identity', component: require('./pages/registration/Identity.vue').default, meta: { page_name: 'Registration Identity' } },
                    { path: 'reg', component: require('./pages/registration/Reg.vue').default, meta: { page_name: 'Registration Form' } },
                    { path: 'education-bg', component: require('./pages/registration/EducationBg.vue').default, meta: { page_name: 'Education Background' } },
                    { path: 'institution', component: require('./pages/registration/Institution.vue').default, meta: { page_name: 'Institution' } },
                    { path: 'family', component: require('./pages/registration/Family.vue').default, meta: { page_name: 'Family' } },
                    { path: 'scores', component: require('./pages/registration/Scores.vue').default, meta: { page_name: 'Scores' } },
                    { path: 'jobs', component: require('./pages/registration/Jobs.vue').default, meta: { page_name: 'Jobs' } },
                    { path: 'upload', component: require('./pages/registration/Upload.vue').default, meta: { page_name: 'Upload' } },
                    { path: 'educations', component: require('./pages/registration/Educations.vue').default, meta: { page_name: 'Educations' } },
                    { path: 'scientifics', component: require('./pages/registration/Scientifics.vue').default, meta: { page_name: 'Scientifics' } },
                    { path: 'organisations', component: require('./pages/registration/Organisations.vue').default, meta: { page_name: 'Organisations' } },
                    { path: 'interenships', component: require('./pages/registration/Interenships.vue').default, meta: { page_name: 'Interenships' } },
                    { path: 'recomendations', component: require('./pages/registration/Recomendations.vue').default, meta: { page_name: 'Recomendations' } },
                    { path: 'achievements', component: require('./pages/registration/Achievements.vue').default, meta: { page_name: 'Achievements' } },
                    { path: 'logbook', component: require('./pages/registration/LogBook.vue').default, meta: { page_name: 'Log Book' } },
                ],
            },
        ],
    },
    {
        path: admin_prefix + '/register',
        component: require('./pages/Register.vue').default,
        beforeEnter: redirectIfAuth,
        meta: { page_name: 'Register' },
    },
    {
        path: admin_prefix + '/login',
        component: require('./pages/auth/Login.vue').default,
        beforeEnter: redirectIfAuth,
        meta: { page_name: 'Login' },
    },
    {
        path: admin_prefix + '/not-found',
        component: require('./pages/auth/NotFound.vue').default,
        meta: { page_name: 'Not Found' },
    },
    {
        path: admin_prefix + '/forgot-password',
        component: require('./pages/auth/ForgotPassword.vue').default,
        beforeEnter: redirectIfAuth,
        meta: { page_name: 'Forgot Password' },
    },
    {
        path: admin_prefix + '/reset-password',
        component: require('./pages/auth/ResetPassword.vue').default,
        beforeEnter: redirectIfAuth,
        meta: { page_name: 'Reset Password' },
    },
    {
        path: admin_prefix + '/login-email',
        component: require('./pages/auth/LoginEmail.vue').default,
        beforeEnter: redirectIfAuth,
        meta: { page_name: 'Login Email' },
    },
    {
        path: admin_prefix + '/login-phone',
        component: require('./pages/auth/LoginPhone.vue').default,
        beforeEnter: redirectIfAuth,
        meta: { page_name: 'Login Phone' },
    },
    {
        path: admin_prefix + '/pub/scoring',
        component: require('./pages/public/Scoring.vue').default,
        meta: { page_name: 'Scoring Link' },
    },
    {
        path: admin_prefix,
        component: require('./pages/Layout.vue').default,
        beforeEnter: requireAuth,
        children: [
            { path: 'dashboard', component: require('./pages/dashboard/Index.vue').default, meta: { page_name: 'Dashboard' } },
            { path: 'dashboard/matric', component: require('./pages/dashboard/Matric.vue').default, meta: { page_name: 'Matric' } },
            { path: 'dashboard-student', redirect: 'dashboard-student/profile', meta: { page_name: 'Student Dashboard' } },
            { path: 'dashboard-student/scoring', component: require('./pages/dashboard-student/Scoring.vue').default, meta: { page_name: 'Student Scoring' } },
            { path: 'dashboard-student/checklist', component: require('./pages/dashboard-student/Checklist.vue').default, meta: { page_name: 'Student Checklist' } },
            // Must stay before the :stase_log_id route below, which would
            // otherwise match 'confirm' as a stase log id.
            { path: 'dashboard-student/scoring/confirm', component: require('./pages/dashboard-student/ConfirmAttendance.vue').default, meta: { page_name: 'Konfirmasi Agenda' } },
            { path: 'dashboard-student/scoring/:stase_log_id', component: require('./pages/dashboard-student/ScoringDetail.vue').default, meta: { page_name: 'Student Scoring Detail' } },
            { path: 'dashboard-student/scoring-v2/:stase_log_id', component: require('./pages/dashboard-student/ScoringDetailV2.vue').default, meta: { page_name: 'Student Scoring Detail V2' } },
            { path: 'dashboard-student/agenda', component: require('./pages/dashboard-student/Agenda.vue').default, meta: { page_name: 'Student Agenda' } },
            { path: 'dashboard-student/report', component: require('./pages/dashboard-student/Report.vue').default, meta: { page_name: 'Student Report' } },
            { path: 'dashboard-student/document', component: require('./pages/dashboard-student/Document.vue').default, meta: { page_name: 'Student Document' } },
            { path: 'dashboard-student/profile', component: require('./pages/dashboard-student/Profile.vue').default, meta: { page_name: 'Student Profile' } },
            { path: 'tutorial', component: require('./pages/posts/List.vue').default, meta: { page_name: 'Tutorial' } },
            { path: 'tutorial/:id', component: require('./pages/posts/ListDetail.vue').default, meta: { page_name: 'Tutorial Detail' } },
            { path: 'dashboard-lecture', redirect: 'dashboard-lecture/profile', meta: { page_name: 'Lecture Dashboard' } },
            { path: 'dashboard-lecture/scoring', component: require('./pages/dashboard-lecture/Scoring.vue').default, meta: { page_name: 'Lecture Scoring' } },
            { path: 'dashboard-lecture/agenda', component: require('./pages/activities/LectureActivities.vue').default, meta: { page_name: 'Lecture Agenda' } },
            { path: 'dashboard-lecture/report', component: require('./pages/dashboard-lecture/Report.vue').default, meta: { page_name: 'Lecture Report' } },
            { path: 'dashboard-lecture/logbook', component: require('./pages/logbooks/LectureLogbook.vue').default, meta: { page_name: 'Logbook Approval' } },
            { path: 'dashboard-lecture/document', component: require('./pages/dashboard-lecture/Document.vue').default, meta: { page_name: 'Lecture Document' } },
            { path: 'dashboard-lecture/profile', component: require('./pages/dashboard-lecture/Profile.vue').default, meta: { page_name: 'Lecture Profile' } },
            { path: 'notifications', component: require('./pages/notifications/Index.vue').default, meta: { page_name: 'Notifications' } },
            { path: 'release-note', component: require('./pages/markdown/ReleaseNote.vue').default, meta: { page_name: 'Release Note' } },
            { path: 'users', component: require('./pages/users/Index.vue').default, meta: { page_name: 'Users' } },
            { path: 'roles', component: require('./pages/roles/Index.vue').default, meta: { page_name: 'Roles' } },
            { path: 'menus', component: require('./pages/menus/Index.vue').default, meta: { page_name: 'Menus' } },
            { path: 'menu-roles', component: require('./pages/menu-roles/Index.vue').default, meta: { page_name: 'Menu Roles' } },
            { path: 'action-logs', component: require('./pages/action-logs/Index.vue').default, meta: { page_name: 'Action Logs' } },
            { path: 'settings', component: require('./pages/settings/Index.vue').default, meta: { page_name: 'Settings' } },
            { path: 'form-options', component: require('./pages/form-options/Index.vue').default, meta: { page_name: 'Form Options' } },
            { path: 'study-programs', component: require('./pages/study_program/Index.vue').default, meta: { page_name: 'Study Programs' } },
            { path: 'posts', component: require('./pages/posts/Index.vue').default, meta: { page_name: 'Posts' } },
            { path: 'posts/create', component: require('./pages/posts/AddEdit.vue').default, meta: { page_name: 'Create Post' } },
            { path: 'posts/:id', component: require('./pages/posts/AddEdit.vue').default, meta: { page_name: 'Edit Post' } },
            { path: 'report/stase-log', component: require('./pages/report/StaseLog.vue').default, meta: { page_name: 'Stase Log Report' } },
            { path: 'logbooks', component: require('./pages/logbooks/Index.vue').default, meta: { page_name: 'Logbooks' } },
            { path: 'logbook-student', component: require('./pages/logbooks/Student.vue').default, meta: { page_name: 'Logbooks' } },
            { path: 'logbook-student-add', component: require('./pages/logbooks/Add.vue').default, meta: { page_name: 'Add Logbook' } },
            { path: 'logbook-student-add/:id', component: require('./pages/logbooks/Add.vue').default, meta: { page_name: 'Edit Logbook' } },
            { path: 'logbook-student-daily', component: require('./pages/logbooks/IndexV2.vue').default, meta: { page_name: 'Logbook Daily' } },
            { path: 'stases', component: require('./pages/stases/Index.vue').default, meta: { page_name: 'Stases' } },
            { path: 'stases/calendar', component: require('./pages/stases/Student.vue').default, meta: { page_name: 'Stase Calendar' } },
            { path: 'tasks', component: require('./pages/tasks/Index.vue').default, meta: { page_name: 'Tasks' } },
            { path: 'tasks/:task_id/scoring-components', component: require('./pages/tasks/ScoringComponents.vue').default, meta: { page_name: 'Scoring Components' } },
            { path: 'accreditations', component: require('./pages/accreditations/Index.vue').default, meta: { page_name: 'Accreditations' } },
            { path: 'accreditations/:id', component: require('./pages/accreditations/Detail.vue').default, meta: { page_name: 'Accreditation Detail' } },
            { path: 'stase-tasks/:stase_id', component: require('./pages/stases/Task.vue').default, meta: { page_name: 'Stase Tasks' } },
            { path: 'lectures', component: require('./pages/lectures/Index.vue').default, meta: { page_name: 'Lectures' } },
            { path: 'lectures/:lecture_id/scoring', component: require('./pages/lectures/Scoring.vue').default, meta: { page_name: 'Lecture Scoring History' } },
            { path: 'off-days', component: require('./pages/off-days/Index.vue').default, meta: { page_name: 'Off Days' } },
            { path: 'mail-logs', component: require('./pages/mail_logs/Index.vue').default, meta: { page_name: 'Mail Logs' } },
            { path: 'scores', component: require('./pages/scores/Index.vue').default, meta: { page_name: 'Scores' } },
            { path: 'students', component: require('./pages/students/Index.vue').default, meta: { page_name: 'Students' } },
            { path: 'students/monitoring', component: require('./pages/students/Monitoring.vue').default, meta: { page_name: 'Student Monitoring' } },
            { path: 'students/monitoring-logbook', component: require('./pages/students/MonitoringLogbook.vue').default, meta: { page_name: 'Student Monitoring Logbook' } },
            { path: 'students/monitoring-presence', component: require('./pages/students/MonitoringPresence.vue').default, meta: { page_name: 'Student Monitoring Presence' } },
            { path: 'assets', component: require('./pages/assets/Index.vue').default, meta: { page_name: 'Assets' } },
            { path: 'students/:student_id/score', component: require('./pages/students/Score.vue').default, meta: { page_name: 'Student Score' } },
            { path: 'registrations', component: require('./pages/registrations/Index.vue').default, meta: { page_name: 'Registrations' } },
            { path: 'registrations/score', component: require('./pages/registrations/Score.vue').default, meta: { page_name: 'Registration Score' } },
            { path: 'registrations/:id', component: require('./pages/registrations/Detail.vue').default, meta: { page_name: 'Registration Detail' } },
            { path: 'letters', component: require('./pages/letters/Index.vue').default, meta: { page_name: 'Letters' } },
            { path: 'letters/create', component: require('./pages/letters/AddEdit.vue').default, meta: { page_name: 'Create Letter' } },
            { path: 'letters/:id', component: require('./pages/letters/AddEdit.vue').default, meta: { page_name: 'Edit Letter' } },
            { path: 'activities', component: require('./pages/activities/Index.vue').default, meta: { page_name: 'Activities' } },
            { path: 'activities/create', component: require('./pages/activities/AddCreate.vue').default, meta: { page_name: 'Create Activity' } },
            { path: 'activities/:id/view', component: require('./pages/activities/View.vue').default, meta: { page_name: 'Activity Detail' } },
            { path: 'activities/:id', component: require('./pages/activities/AddCreate.vue').default, meta: { page_name: 'Edit Activity' } },
            { path: 'presences', component: require('./pages/presences/Index.vue').default, meta: { page_name: 'Presences' } },
            { path: 'presences/daily', component: require('./pages/presences/Daily.vue').default, meta: { page_name: 'Daily Presences' } },
            { path: 'presences/student-daily', component: require('./pages/presences/StudentDaily.vue').default, meta: { page_name: 'Student Daily Presence' } },
            { path: 'presences/monthly', component: require('./pages/presences/Monthly.vue').default, meta: { page_name: 'Monthly Presences' } },
            { path: 'presences/student/:student_id', component: require('./pages/presences/Student.vue').default, meta: { page_name: 'Student Presences' } },
            { path: 'task-scoring/:open_stase_task_id', component: require('./pages/scores/TaskScore.vue').default, meta: { page_name: 'Task Scoring' } },
            { path: 'task-scoring-thesis/:open_stase_task_id', component: require('./pages/scores/TaskScoreThesis.vue').default, meta: { page_name: 'Task Scoring Thesis' } },
            { path: 'task-scoring-proposal/:open_stase_task_id', component: require('./pages/scores/TaskScoreProposal.vue').default, meta: { page_name: 'Task Scoring Proposal' } },
            { path: 'forms', component: require('./pages/forms/Index.vue').default, meta: { page_name: 'Forms' } },
            { path: 'forms/create', component: require('./pages/forms/Editor.vue').default, meta: { page_name: 'Create Form' } },
            { path: 'forms/:id/responses', component: require('./pages/forms/Responses.vue').default, meta: { page_name: 'Form Responses' } },
            { path: 'forms/:id', component: require('./pages/forms/Editor.vue').default, meta: { page_name: 'Edit Form' } },
        ],
    },
];

export default routes;
