const admin_prefix = '/blu';

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

const routes = [
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
        path: admin_prefix,
        component: require('./pages/Layout.vue').default,
        beforeEnter: requireAuth,
        children: [
            { path: 'dashboard', component: require('./pages/dashboard/Index.vue').default, meta: { page_name: 'Dashboard' } },
            { path: 'dashboard-student', redirect: 'dashboard-student/profile', meta: { page_name: 'Student Dashboard' } },
            { path: 'dashboard-student/scoring', component: require('./pages/dashboard-student/Scoring.vue').default, meta: { page_name: 'Student Scoring' } },
            { path: 'dashboard-student/scoring/:stase_log_id', component: require('./pages/dashboard-student/ScoringDetail.vue').default, meta: { page_name: 'Student Scoring Detail' } },
            { path: 'dashboard-student/agenda', component: require('./pages/dashboard-student/Agenda.vue').default, meta: { page_name: 'Student Agenda' } },
            { path: 'dashboard-student/report', component: require('./pages/dashboard-student/Report.vue').default, meta: { page_name: 'Student Report' } },
            { path: 'dashboard-student/document', component: require('./pages/dashboard-student/Document.vue').default, meta: { page_name: 'Student Document' } },
            { path: 'dashboard-student/profile', component: require('./pages/dashboard-student/Profile.vue').default, meta: { page_name: 'Student Profile' } },
            { path: 'dashboard-lecture', redirect: 'dashboard-lecture/profile', meta: { page_name: 'Lecture Dashboard' } },
            { path: 'dashboard-lecture/scoring', component: require('./pages/dashboard-lecture/Scoring.vue').default, meta: { page_name: 'Lecture Scoring' } },
            { path: 'dashboard-lecture/agenda', component: require('./pages/dashboard-lecture/Agenda.vue').default, meta: { page_name: 'Lecture Agenda' } },
            { path: 'dashboard-lecture/report', component: require('./pages/dashboard-lecture/Report.vue').default, meta: { page_name: 'Lecture Report' } },
            { path: 'dashboard-lecture/document', component: require('./pages/dashboard-lecture/Document.vue').default, meta: { page_name: 'Lecture Document' } },
            { path: 'dashboard-lecture/profile', component: require('./pages/dashboard-lecture/Profile.vue').default, meta: { page_name: 'Lecture Profile' } },
            { path: 'release-note', component: require('./pages/markdown/ReleaseNote.vue').default, meta: { page_name: 'Release Note' } },
            { path: 'users', component: require('./pages/users/Index.vue').default, meta: { page_name: 'Users' } },
            { path: 'logbooks', component: require('./pages/logbooks/Index.vue').default, meta: { page_name: 'Logbooks' } },
            { path: 'logbooks/bulk', component: require('./pages/logbooks/AddBulk.vue').default, meta: { page_name: 'Logbooks Bulk' } },
            { path: 'stases', component: require('./pages/stases/Index.vue').default, meta: { page_name: 'Stases' } },
            { path: 'tasks', component: require('./pages/tasks/Index.vue').default, meta: { page_name: 'Tasks' } },
            { path: 'accreditations', component: require('./pages/accreditations/Index.vue').default, meta: { page_name: 'Accreditations' } },
            { path: 'accreditations/:id', component: require('./pages/accreditations/Detail.vue').default, meta: { page_name: 'Accreditation Detail' } },
            { path: 'stase-tasks/:stase_id', component: require('./pages/stases/Task.vue').default, meta: { page_name: 'Stase Tasks' } },
            { path: 'lectures', component: require('./pages/lectures/Index.vue').default, meta: { page_name: 'Lectures' } },
            { path: 'scores', component: require('./pages/scores/Index.vue').default, meta: { page_name: 'Scores' } },
            { path: 'students', component: require('./pages/students/Index.vue').default, meta: { page_name: 'Students' } },
            { path: 'students/:student_id/score', component: require('./pages/students/Score.vue').default, meta: { page_name: 'Student Score' } },
            { path: 'activities', component: require('./pages/activities/Index.vue').default, meta: { page_name: 'Activities' } },
            { path: 'activities/create', component: require('./pages/activities/AddCreate.vue').default, meta: { page_name: 'Create Activity' } },
            { path: 'activities/:id', component: require('./pages/activities/AddCreate.vue').default, meta: { page_name: 'Edit Activity' } },
            { path: 'presences', component: require('./pages/presences/Index.vue').default, meta: { page_name: 'Presences' } },
            { path: 'presences/daily', component: require('./pages/presences/Daily.vue').default, meta: { page_name: 'Daily Presences' } },
            { path: 'presences/monthly', component: require('./pages/presences/Monthly.vue').default, meta: { page_name: 'Monthly Presences' } },
            { path: 'presences/student/:student_id', component: require('./pages/presences/Student.vue').default, meta: { page_name: 'Student Presences' } },
            { path: 'task-scoring/:open_stase_task_id', component: require('./pages/scores/TaskScore.vue').default, meta: { page_name: 'Task Scoring' } },
            { path: 'task-scoring-thesis/:open_stase_task_id', component: require('./pages/scores/TaskScoreThesis.vue').default, meta: { page_name: 'Task Scoring Thesis' } },
            { path: 'task-scoring-proposal/:open_stase_task_id', component: require('./pages/scores/TaskScoreProposal.vue').default, meta: { page_name: 'Task Scoring Proposal' } },
        ],
    },
];

export default routes;
