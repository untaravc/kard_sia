
require('./bootstrap');

import Vue from 'vue';
import VueCompositionApi from '@vue/composition-api';
import { PiniaVuePlugin, createPinia } from 'pinia';
import ToastPlugin from './toaster';
import { initWebFcm } from './firebase/messaging';
import { useAppSettingsStore } from './stores/appSettings';

//VUE ROUTER
import VueRouter from 'vue-router'
Vue.use(VueRouter);

import './mask';

import routes from './routes';

import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

import money from 'v-money';

Vue.use(VueCompositionApi);
Vue.use(PiniaVuePlugin);
Vue.use(ToastPlugin);
Vue.use(money, { precision: 0 });
Vue.component('v-select', vSelect);

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

router.afterEach((to) => {
    const pageName = to && to.meta && to.meta.page_name ? to.meta.page_name : null;

    useAppSettingsStore().fetchSetting('app.name').then((appName) => {
        const title = appName || 'BLU';
        document.title = pageName ? `${title} | ${pageName}` : title;
    });
});

initWebFcm().catch(() => {
    // Optional: ignore FCM initialization errors.
});
