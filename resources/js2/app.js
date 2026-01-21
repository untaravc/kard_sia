
require('./bootstrap');

import Vue from 'vue'

//VUE ROUTER
import VueRouter from 'vue-router'
Vue.use(VueRouter);

const admin_prefix = '/cblu';

const routes = [
    {
        path: admin_prefix,
        component: require('./pages/Layout.vue').default,
        children: [
            { path: '', component: require('./pages/dashboard/Index.vue').default },
        ],
    },
];

const router = new VueRouter({
    mode : 'history',
    routes
});

const app = new Vue({
    el: '#app',
    router,
    template: '<router-view />',
});
