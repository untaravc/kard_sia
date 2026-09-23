export default {
    data() {
        return {
            messageType: 'error',
        };
    },
    computed: {
        messageClass() {
            return this.messageType === 'success'
                ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                : 'border-red-200 bg-red-50 text-red-700';
        },
    },
    methods: {
        notifySuccess(text) {
            this.messageType = 'success';
            this.message = text || 'Data berhasil disimpan.';
            this.revealNotice();
        },
        notifyError(text) {
            this.messageType = 'error';
            this.message = text || 'Terjadi kesalahan, silakan coba lagi.';
            this.revealNotice();
        },
        revealNotice() {
            this.$nextTick(() => {
                const el = this.$refs.notice;
                if (el && el.scrollIntoView) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });
        },
    },
};
