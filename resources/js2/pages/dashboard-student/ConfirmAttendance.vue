<template>
    <div class="mx-auto flex w-full max-w-md flex-col gap-6">
        <div class="relative rounded-2xl border border-border bg-panel p-6 shadow-sm">
            <Loading :active="submitting" :is-full-page="false" />

            <div class="text-xs uppercase tracking-[0.2em] text-muted">Konfirmasi Agenda</div>
            <h1 class="mt-1 text-lg font-semibold text-ink">Bukti Kehadiran</h1>

            <div v-if="state === 'success'" class="mt-5 grid gap-3">
                <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                    <Icon icon="mdi:check-decagram" class="h-5 w-5 text-emerald-600" />
                    <div class="text-sm font-semibold text-emerald-700">Agenda berhasil dikonfirmasi</div>
                </div>
                <div v-if="confirmed" class="rounded-xl border border-border bg-white px-4 py-3 text-sm">
                    <div class="font-semibold text-ink">{{ confirmed.title || 'Agenda' }}</div>
                    <div v-if="confirmed.plan" class="mt-1 text-xs text-muted">Plan: {{ confirmed.plan }}</div>
                </div>
                <router-link
                    class="rounded-xl bg-primary px-4 py-2 text-center text-sm font-medium text-white"
                    to="/blu/dashboard-student/scoring"
                >
                    Kembali ke Stase
                </router-link>
            </div>

            <div v-else-if="state === 'select'" class="mt-5 grid gap-3">
                <p class="text-xs text-muted">
                    Kamu punya beberapa agenda dengan dosen ini. Pilih yang ingin dikonfirmasi.
                </p>
                <button
                    v-for="candidate in candidates"
                    :key="candidate.id"
                    type="button"
                    class="rounded-xl border border-border bg-white px-4 py-3 text-left hover:bg-slate-50"
                    @click="confirm(candidate.id)"
                >
                    <div class="text-sm font-semibold text-ink">{{ candidate.title || 'Agenda' }}</div>
                    <div class="mt-1 text-xs text-muted">
                        <span v-if="candidate.lecture_name">{{ candidate.lecture_name }}</span>
                        <span v-if="candidate.plan"> • Plan: {{ candidate.plan }}</span>
                    </div>
                </button>
            </div>

            <div v-else class="mt-5 grid gap-4">
                <p class="text-xs text-muted">
                    Masukkan 6 digit kode yang ditampilkan pada layar dosen.
                </p>
                <input
                    v-model.trim="code"
                    type="tel"
                    inputmode="numeric"
                    maxlength="6"
                    placeholder="000000"
                    class="w-full rounded-xl border border-border bg-white px-3 py-3 text-center font-mono text-2xl tracking-[0.3em] focus:outline-none focus:ring-2 focus:ring-primary/30"
                    @keyup.enter="confirm()"
                />
                <div v-if="errorMessage" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ errorMessage }}
                </div>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
                    type="button"
                    :disabled="submitting || code.length !== 6"
                    @click="confirm()"
                >
                    {{ submitting ? 'Memproses...' : 'Konfirmasi' }}
                </button>
                <router-link
                    class="text-center text-xs text-muted hover:underline"
                    to="/blu/dashboard-student/scoring"
                >
                    Batal
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Repository from '../../repository';
import { Icon } from '../../icons';

export default {
    components: {
        Loading,
        Icon,
    },
    data() {
        return {
            code: '',
            state: 'input',
            candidates: [],
            confirmed: null,
            submitting: false,
            errorMessage: '',
            coords: { lat: null, lng: null },
        };
    },
    created() {
        this.captureLocation();

        // Arriving from a scanned QR: the code rides in the query string, so
        // confirm straight away instead of making the student retype it.
        const scanned = this.$route.query.c;
        if (scanned) {
            this.code = String(scanned).replace(/\D/g, '').slice(0, 6);
            if (this.code.length === 6) {
                this.confirm(null, 'qr');
            }
        }
    },
    methods: {
        captureLocation() {
            if (!navigator.geolocation) {
                return;
            }
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.coords = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };
                },
                () => {},
                { enableHighAccuracy: true, timeout: 10000 }
            );
        },
        confirm(openStaseTaskId = null, method = 'code') {
            if (this.submitting || this.code.length !== 6) {
                return;
            }

            this.submitting = true;
            this.errorMessage = '';

            return Repository.post('/api/attendance-confirm', {
                code: this.code,
                open_stase_task_id: openStaseTaskId,
                method,
                lat: this.coords.lat,
                lng: this.coords.lng,
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;

                    if (result && result.needs_selection) {
                        this.candidates = result.candidates || [];
                        this.state = 'select';
                        return;
                    }

                    this.confirmed = result ? result.open_stase_task : null;
                    this.state = 'success';
                    if (this.$showToast) {
                        this.$showToast('Agenda berhasil dikonfirmasi.');
                    }
                })
                .catch((error) => {
                    this.state = 'input';
                    this.errorMessage = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Gagal mengonfirmasi agenda.';
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
    },
};
</script>
