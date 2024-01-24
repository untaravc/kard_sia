
require('./bootstrap');

import Vue from 'vue'

//VUE ROUTER
import VueRouter from 'vue-router'
Vue.use(VueRouter);

// import {createRouter, createWebHistory} from 'vue-router'

const admin_prefix = '/cmss';

const routes = [
    { path: admin_prefix + '/', component: require('./components/admin/dashboard/Index.vue').default  },
    { path: admin_prefix + '/analytics', component: require('./components/admin/dashboard-analytics/Index.vue').default  },
    { path: admin_prefix + '/timeline', component: require('./components/admin/timeline/Index.vue').default  },
    { path: admin_prefix + '/lecture', component: require('./components/admin/lecture/Index.vue').default  },
    { path: admin_prefix + '/lecture/:id', component: require('./components/admin/lecture/Show.vue').default  },
    { path: admin_prefix + '/lecture-file', component: require('./components/admin/lecture-file/Index.vue').default  },
    { path: admin_prefix + '/resident', component: require('./components/admin/resident/Index.vue').default  },
    { path: admin_prefix + '/resident/:id', component: require('./components/admin/resident/Show.vue').default  },
    { path: admin_prefix + '/letters', component: require('./components/admin/letters/Index.vue').default  },
    { path: admin_prefix + '/letter-records', component: require('./components/admin/letter-records/Index.vue').default  },
    { path: admin_prefix + '/resume-presence/:id', component: require('./components/admin/resident/Presences.vue').default  },
    { path: admin_prefix + '/resident-score/:id', component: require('./components/admin/resident/Score').default  },
    { path: admin_prefix + '/stase', component: require('./components/admin/stase/Index.vue').default  },
    { path: admin_prefix + '/stase/:id', component: require('./components/admin/stase/Show.vue').default  },
    { path: admin_prefix + '/task', component: require('./components/admin/task/Index.vue').default  },
    { path: admin_prefix + '/user', component: require('./components/admin/user/Index.vue').default  },
    { path: admin_prefix + '/task/:id', component: require('./components/admin/task/Show.vue').default  },
    { path: admin_prefix + '/student-regs', component: require('./components/admin/student-regs/Index.vue').default  },
    { path: admin_prefix + '/open-exam', component: require('./components/admin/open-exam/Index.vue').default  },
    { path: admin_prefix + '/activity', component: require('./components/admin/activity/Index.vue').default  },
    { path: admin_prefix + '/activity/edit-add/:id', component: require('./components/admin/activity/EditAdd.vue').default },
    { path: admin_prefix + '/presences', component: require('./components/admin/presences/Index.vue').default  },
    { path: admin_prefix + '/stase-plots', component: require('./components/admin/stase-plots/Index.vue').default  },
    { path: admin_prefix + '/exams', component: require('./components/admin/exams/Index.vue').default  },
    { path: admin_prefix + '/exams/:id', component: require('./components/admin/exams/Show.vue').default  },
    { path: admin_prefix + '/presence-resume', component: require('./components/admin/presence-resume/Index.vue').default  },
    { path: admin_prefix + '/download', component: require('./components/admin/download/Index.vue').default  },
    { path: admin_prefix + '/student-logs', component: require('./components/admin/student-logs/Index.vue').default  },
    { path: admin_prefix + '/presence-ilmiah-resume', component: require('./components/admin/presence-ilmiah-resume/Index.vue').default  },
    { path: admin_prefix + '/import', component: require('./components/object/import/Index.vue').default  },
    { path: admin_prefix + '/document-review', component: require('./components/admin/document-review/Index.vue').default  },
    { path: admin_prefix + '/daily-report', component: require('./components/admin/daily-report/Index.vue').default  },

    // Object
    { path: '/object/quizzes', component: require('./components/object/quizzes/Index.vue').default  },
    { path: '/object/categories', component: require('./components/object/categories/Index.vue').default  },
    { path: '/object/quiz-sections', component: require('./components/object/quiz-sections/Index.vue').default  },
    { path: '/object/quiz-sections/:id', component: require('./components/object/quiz-sections/EditAdd.vue').default },
    { path: '/object/quiz-student/:id', component: require('./components/object/quiz-student/Index.vue').default },

    //RESIDEN
    // { path: '/resident/', component: require('./components/resident/dashboard/Show.vue').default  },
    { path: '/resident/', component: require('./components/resident/dashboard/Index.vue').default  },
    { path: '/resident/score', component: require('./components/resident/score/Index.vue').default  },
    { path: '/resident/logbooks', component: require('./components/resident/log-books/Index.vue').default  },
    { path: '/resident/letters', component: require('./components/resident/latters/Index.vue').default  },
    { path: '/resident/resume-presences', component: require('./components/resident/dashboard/Presences.vue').default  },
    { path: '/resident/profile', component: require('./components/resident/profile/Index.vue').default },
    { path: '/resident/task-detail', component: require('./components/resident/dashboard/TaskDetail').default  },
    { path: '/resident/activity', component: require('./components/resident/activity/Index.vue').default  },
    { path: '/resident/activity/edit-add/:id', component: require('./components/resident/activity/EditAdd.vue').default  },

    //DOSEN
    { path: '/dosen/', component: require('./components/lecture/dashboard/Index.vue').default },
    { path: '/dosen/history', component: require('./components/lecture/dashboard/History.vue').default },
    { path: '/dosen/ksm-schedules', component: require('./components/lecture/ksm_schedules/Index.vue').default },
    { path: '/dosen/stase', component: require('./components/lecture/stase/Index.vue').default },
    { path: '/dosen/profile', component: require('./components/lecture/profile/Index.vue').default },
    { path: '/dosen/activities', component: require('./components/lecture/activities/Index.vue').default },
    { path: '/dosen/resident', component: require('./components/lecture/resident/Index2.vue').default },
    { path: '/dosen/letters', component: require('./components/lecture/letters/Index.vue').default },
    { path: '/dosen/stase-plots', component: require('./components/lecture/stase-plots/Index.vue').default  },
    { path: '/dosen/presences', component: require('./components/lecture/presences/Index.vue').default },
    { path: '/dosen/resume-presence/:id', component: require('./components/lecture/resident/Presences.vue').default  },
    { path: '/dosen/resident/:id', component: require('./components/lecture/resident/Show.vue').default  },
    { path: '/dosen/resident-score/:id', component: require('./components/lecture/resident/Score3').default  },
    { path: '/dosen/task/nilai-tesis/:open_stase_task_id', component: require('./components/lecture/task/NilaiTesis').default },
    { path: '/dosen/task/nilai-proposal/:open_stase_task_id', component: require('./components/lecture/task/Proposal').default },
    { path: '/dosen/task/scoring/:open_stase_task_id', component: require('./components/lecture/task/Index').default },
    { path: '/dosen/resident-logs', component: require('./components/lecture/resident-logs/Index').default },
    { path: '/dosen/student-logs', component: require('./components/lecture/student-logs/Index').default },

    //Form
    { path: '/f/quiz', component: require('./components/object/quiz/Index').default },
];

const router = new VueRouter({
    mode : 'history',
    routes
});

//---- VForm ----
import { Form, HasError, AlertError } from 'vform'
window.form = Form;
Vue.component(HasError.name, HasError);
Vue.component(AlertError.name, AlertError);

//---- PROGRESS BAR ----
import VueProgressBar from 'vue-progressbar'
Vue.use(VueProgressBar, {
    color: 'rgb(255,28,25)',
    failedColor: 'red',
    height: '20px'
});

import vueDebounce from 'vue-debounce'
Vue.use(vueDebounce)

//Gate
import Gate from "./vue-gate";
Vue.prototype.$gate = new Gate(window.mewnesia);

//---- SWEET ALERT ----
import Swal from 'sweetalert2'
window.Swal = Swal;

//Slider
import VueSlider from 'vue-slider-component'
import 'vue-slider-component/theme/antd.css'
Vue.component('vue-slider', VueSlider);

//---- Fire Global Event ----
window.Fire = new Vue();

// AUTO Complete
// import Autocomplete from 'vue2-autocomplete-js'
// Vue.component('autocomplete', Autocomplete);

//---- MAP ----
// import * as VueGoogleMaps from 'vue2-google-maps'
// Vue.use(VueGoogleMaps, {
//     load: {
//         key: 'AIzaSyDuLAIKVzdMdIfMVSxZkU5ZQMJYmPHDiZU',
//         libraries: 'places',
//     },
// });

//---- SELECT OPTIONS ----
import vSelect from 'vue-select'
Vue.component('v-select', vSelect);

//MULTI SELECT
import Multiselect from 'vue-multiselect'
Vue.component('multiselect', Multiselect);

//---- FILTER ----
import moment from 'moment';
Vue.filter('formatDate', function(value) {
    if (value) {
        return moment(value).format("DD MMM YYYY");
    }
});

Vue.filter('currency', function(value) {
    if (value) {
        let val = (value/1).toFixed(0).replace('.', ',');
        return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
    }
});

import VueCtkDateTimePicker from 'vue-ctk-date-time-picker';
import 'vue-ctk-date-time-picker/dist/vue-ctk-date-time-picker.css';

//Loader
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
Vue.component('loading', Loading);

import LoadingOverlay from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
Vue.component('vue-loader', LoadingOverlay);

Vue.component('VueCtkDateTimePicker', VueCtkDateTimePicker);

require('./vue-components');
require('./vue-filter');

const app = new Vue({
    el: '#app',
    router,
    data: {
        headers: {
            Authorization: 's' },
        loginHeader:{
            'Content-Type' : 'aplication/json',
            'X-Requested-With' : 'XMLHttpRequest',
        },
        baseApiUrl: 'http://127.0.0.1:8000/api/',
    }
});

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))


