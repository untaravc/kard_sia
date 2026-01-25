const admin_prefix = '/cblu';

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
    },
    {
        path: admin_prefix + '/not-found',
        component: require('./pages/auth/NotFound.vue').default,
    },
    {
        path: admin_prefix + '/forgot-password',
        component: require('./pages/auth/ForgotPassword.vue').default,
        beforeEnter: redirectIfAuth,
    },
    {
        path: admin_prefix + '/reset-password',
        component: require('./pages/auth/ResetPassword.vue').default,
        beforeEnter: redirectIfAuth,
    },
    {
        path: admin_prefix,
        component: require('./pages/Layout.vue').default,
        beforeEnter: requireAuth,
        children: [
            { path: 'dashboard', component: require('./pages/dashboard/Index.vue').default },
            { path: 'dashboard-student', component: require('./pages/dashboard-student/Index.vue').default },
            { path: 'users', component: require('./pages/users/Index.vue').default },
            { path: 'logbooks', component: require('./pages/logbooks/Submit.vue').default },
            { path: 'logbooks/bulk', component: require('./pages/logbooks/AddBulk.vue').default },
            { path: 'stases', component: require('./pages/stases/Index.vue').default },
            { path: 'tasks', component: require('./pages/tasks/Index.vue').default },
            { path: 'stase-tasks/:stase_id', component: require('./pages/stases/Task.vue').default },
            { path: 'lectures', component: require('./pages/lectures/Index.vue').default },
            { path: 'students', component: require('./pages/students/Index.vue').default },
            { path: 'presences', component: require('./pages/presences/Index.vue').default },
            { path: 'presences/daily', component: require('./pages/presences/Daily.vue').default },
            { path: 'presences/monthly', component: require('./pages/presences/Monthly.vue').default },
        ],
    },
];

export default routes;
