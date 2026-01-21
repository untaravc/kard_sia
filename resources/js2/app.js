
require('./bootstrap');

import Vue from 'vue'

//VUE ROUTER
import VueRouter from 'vue-router'
Vue.use(VueRouter);

import routes from './routes';

const router = new VueRouter({
    mode : 'history',
    routes
});

const app = new Vue({
    el: '#app',
    router,
    template: '<router-view />',
});
