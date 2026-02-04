
require('./bootstrap');

import Vue from 'vue';
import VueCompositionApi from '@vue/composition-api';
import { PiniaVuePlugin, createPinia } from 'pinia';
import ToastPlugin from './toaster';
import { initWebFcm } from './firebase/messaging';

//VUE ROUTER
import VueRouter from 'vue-router'
Vue.use(VueRouter);

import './mask';

import routes from './routes';

Vue.use(VueCompositionApi);
Vue.use(PiniaVuePlugin);
Vue.use(ToastPlugin);

const router = new VueRouter({
    mode : 'history',
    routes
});

router.afterEach((to) => {
    const pageName = to && to.meta && to.meta.page_name ? to.meta.page_name : null;
    document.title = pageName ? `BLU | ${pageName}` : 'BLU';
});

const pinia = createPinia();

const app = new Vue({
    el: '#app',
    router,
    pinia,
    template: '<router-view />',
});

initWebFcm().catch(() => {
    // Optional: ignore FCM initialization errors.
});
