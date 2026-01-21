const admin_prefix = '/cblu';

const routes = [
    {
        path: admin_prefix + '/login',
        component: require('./pages/auth/Login.vue').default,
    },
    {
        path: admin_prefix + '/not-found',
        component: require('./pages/auth/NotFound.vue').default,
    },
    {
        path: admin_prefix + '/forgot-password',
        component: require('./pages/auth/ForgotPassword.vue').default,
    },
    {
        path: admin_prefix + '/reset-password',
        component: require('./pages/auth/ResetPassword.vue').default,
    },
    {
        path: admin_prefix,
        component: require('./pages/Layout.vue').default,
        children: [
            { path: 'dashboard', component: require('./pages/dashboard/Index.vue').default },
        ],
    },
];

export default routes;
