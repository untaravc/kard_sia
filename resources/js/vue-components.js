<<<<<<< HEAD
import Vue from 'vue'
=======
>>>>>>> 3a0ad237c4a32c5d3821fb143edff98da043fa9c

Vue.component('pagination', require('laravel-vue-pagination'));
Vue.component('datepicker', require('vuejs-datepicker').default);

Vue.component('file-preview', require('./components/items/FilePreview.vue').default);
Vue.component('presence-label', require('./components/admin/presence-resume/PresenceLabel.vue').default);

Vue.mixin({
    methods:{
        popGlobalSuccess({title = 'Berhasil'}){
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: title,
                toast:true,
                showConfirmButton: false,
                timer: 1500,
            })
        },
        changeImgUrl(url){
            event.target.src = "/storage/"+url
        },
        changeAuth(){
            console.log('hai')
            this.headers.Authorization = 'hai';
        }
    },
});
