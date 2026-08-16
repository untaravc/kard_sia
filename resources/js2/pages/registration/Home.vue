<template>
    <div class="grid gap-4">
        <div class="rounded-2xl border border-border bg-panel p-6">
            <div class="text-lg font-semibold">
                Selamat Datang<span v-if="registration && registration.name">, {{ registration.name }}</span>
            </div>
            <p class="mt-2 text-sm text-muted leading-relaxed">
                Semua syarat yang tercantum di laman
                <a class="text-primary underline" href="https://um.ugm.ac.id" target="_blank" rel="noreferrer">https://um.ugm.ac.id</a>
                merupakan persyaratan wajib yang harus dipenuhi. Sementara itu, persyaratan yang terdapat dalam borang laman ini
                bersifat pelengkap dan khusus untuk keperluan tes wawancara program studi.
            </p>
            <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-muted">
                <li>
                    Selesaikan pendaftaran di
                    <a class="text-primary underline" href="https://um.ugm.ac.id" target="_blank" rel="noreferrer">https://um.ugm.ac.id</a>
                    sebelum <span class="font-semibold text-ink">7 April 2026</span>.
                </li>
                <li>
                    Isi dan lengkapi dokumen tambahan di ugm.id/RegistrasiPPDS---UGM sebelum
                    <span class="font-semibold text-ink">14 April 2025</span>.
                </li>
            </ul>
        </div>

        <div class="rounded-2xl border border-border bg-panel p-6">
            <div class="text-sm">
                Status:
                <span class="font-semibold" :class="statusColorClass">{{ statusLabel }}</span>
            </div>

            <div v-if="registration && registration.status === 100" class="mt-4 text-sm text-muted">
                Selesaikan pengisian pendaftaran untuk mencetak form pendaftaran. Setelah menyelesaikan pengisian pendaftaran,
                anda tidak dapat lagi memperbarui data.
                <div class="mt-4 flex justify-end">
                    <button type="button"
                        class="rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-medium text-white disabled:opacity-60"
                        :disabled="loading" @click="updateStatus(101)">
                        {{ loading ? 'Please wait...' : 'Selesaikan Pengisian Pendaftaran' }}
                    </button>
                </div>
            </div>

            <div v-else class="mt-4 flex justify-end">
                <a v-if="printUrl" class="rounded-xl bg-sky-600 px-4 py-2 text-sm font-medium text-white"
                    target="_blank" :href="printUrl">
                    Cetak Form Pendaftaran
                </a>
            </div>
        </div>

        <Modal :open="copyModalOpen" title="Pendaftar Ulang" :closeOnBackdrop="false" @close="copyModalOpen = false">
            <div class="text-center text-base text-slate-700">
                Anda telah mendaftar periode <span class="font-semibold">{{ archivePeriodStr }}</span>. Lanjutkan untuk mendaftar periode ini?
            </div>
            <template #footer>
                <button type="button"
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
                    :disabled="copyLoading" @click="processCopyData">
                    {{ copyLoading ? 'Please wait...' : 'Lanjutkan' }}
                </button>
            </template>
        </Modal>
    </div>
</template>

<script>
import Repository from '../../repository';
import Modal from '../../components/Modal.vue';

export default {
    name: 'RegistrationHome',
    components: { Modal },
    props: {
        registration: { type: Object, default: null },
    },
    data() {
        return {
            loading: false,
            copyModalOpen: false,
            copyLoading: false,
            archivePeriodStr: '',
        };
    },
    computed: {
        statusLabel() {
            const status = this.registration ? this.registration.status : null;
            switch (status) {
                case 100:
                    return 'Pengisian Pendaftaran';
                case 101:
                    return 'Pengisian Pendaftaran Selesai';
                case 200:
                    return 'Lolos Administrasi';
                case 201:
                    return 'Tidak Lolos Administrasi';
                case 300:
                    return 'Lolos Ujian Tulis';
                case 301:
                    return 'Tidak Lolos Ujian Tulis';
                case 400:
                    return 'Lolos Ujian Wawancara';
                case 401:
                    return 'Tidak Loloas Ujian Wawancara';
                case 500:
                    return 'Dibatalkan';
                default:
                    return '-';
            }
        },
        statusColorClass() {
            const status = this.registration ? this.registration.status : null;
            if ([201, 301, 401, 500].includes(status)) {
                return 'text-red-600';
            }
            return 'text-primary';
        },
        printUrl() {
            const token = localStorage.getItem('token');
            const registrationId = this.registration ? this.registration.id : null;
            if (!token || !registrationId) {
                return '';
            }
            return `/print/registration/${registrationId}?token=${encodeURIComponent(token)}`;
        },
    },
    mounted() {
        this.checkAvailability();
    },
    methods: {
        updateStatus(status) {
            if (!confirm('Lanjutkan proses?')) {
                return;
            }
            this.loading = true;
            return Repository.patch('/api/registration/status', { status })
                .then(() => {
                    this.$emit('refresh');
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        checkAvailability() {
            return Repository.get('/api/check-availability')
                .then((response) => {
                    const data = response && response.data ? response.data : {};
                    const result = data.result || {};
                    if (result.copy_available === 1) {
                        this.archivePeriodStr = result.period_str || '';
                        this.copyModalOpen = true;
                    }
                })
                .catch(() => {
                    // ignore (endpoint may not be configured in some env)
                });
        },
        processCopyData() {
            this.copyLoading = true;
            return Repository.post('/api/process-copy')
                .then((response) => {
                    const data = response && response.data ? response.data : {};
                    if (data.success && data.result && data.result.token) {
                        localStorage.setItem('token', data.result.token);
                        this.copyModalOpen = false;
                        this.$router.push('/reg/index');
                    }
                })
                .finally(() => {
                    this.copyLoading = false;
                });
        },
    },
};
</script>
