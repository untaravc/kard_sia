
require('./bootstrap');

import Vue from 'vue';
import VueCompositionApi from '@vue/composition-api';
import { PiniaVuePlugin, createPinia } from 'pinia';

//VUE ROUTER
import VueRouter from 'vue-router'
Vue.use(VueRouter);

import './mask';

import routes from './routes';

Vue.use(VueCompositionApi);
Vue.use(PiniaVuePlugin);

const router = new VueRouter({
    mode : 'history',
    routes
});

const pinia = createPinia();

const app = new Vue({
    el: '#app',
    router,
    pinia,
    template: '<router-view />',
});
