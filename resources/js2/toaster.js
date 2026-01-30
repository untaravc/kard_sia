import Swal from 'sweetalert2';

const toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
});

export default {
    install(Vue) {
        Vue.prototype.$toast = toast;
        Vue.prototype.$showToast = (title, icon = 'success') => {
            toast.fire({ title, icon });
        };
    },
};
