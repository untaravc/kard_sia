<template>
    <div class="mx-auto flex w-full max-w-md flex-col items-center gap-6">
        <div class="relative w-full rounded-2xl border border-border bg-panel shadow-sm">
            <Loading :active="loadingProfile" :is-full-page="false" />
            <div class="h-16 rounded-t-2xl bg-gradient-to-br from-indigo-500 via-sky-500 to-emerald-400"></div>
            <div class="px-5 pb-5">
                <div class="-mt-8 flex items-center gap-3">
                    <div
                        class="h-16 w-16 rounded-xl border-2 border-white bg-cover bg-center"
                        :style="profileStyle"
                    ></div>
                    <div class="flex-1">
                        <div class="text-base font-semibold text-ink">{{ user.name }}</div>
                        <div class="text-xs text-muted">{{ user.email }}</div>
                        <div class="text-xs text-muted" v-if="user.phone">{{ user.phone }}</div>
                    </div>
                </div>

                <div class="mt-4 grid gap-2 text-xs text-ink">
                    <div v-if="user.name_alt">
                        <span class="font-semibold"></span>
                        <span class="text-muted">{{ user.name_alt }}</span>
                    </div>
                    <div>
                        <span class="font-semibold"></span>
                        <span class="text-muted">{{ user.email }}</span>
                    </div>
                    <div v-if="user.phone">
                        <span class="font-semibold"></span>
                        <span class="text-muted">{{ user.phone }}</span>
                    </div>
                    <div v-if="user.address">
                        <span class="font-semibold"></span>
                        <span class="text-muted">{{ user.address }}</span>
                    </div>
                </div>

                <div class="mt-4 grid gap-2">
                    <button
                        class="rounded-xl border border-border px-3 py-2 text-xs font-semibold text-ink"
                        type="button"
                        @click="openProfileModal"
                    >
                        <i class="fa fa-user-edit text-slate-500"></i>
                        Perbarui Profil
                    </button>
                </div>
            </div>
        </div>
        <div class="w-full rounded-2xl border border-border bg-panel px-5 py-4 shadow-sm">
            <button
                type="button"
                class="flex w-full items-center justify-between text-left"
                @click="toggleAttendanceCode"
            >
                <div>
                    <div class="text-xs uppercase tracking-[0.2em] text-muted">Kode Kehadiran</div>
                    <div class="mt-0.5 text-[11px] text-muted">
                        Tunjukkan ke PPDS untuk konfirmasi agenda
                    </div>
                </div>
                <Icon :icon="attendanceOpen ? 'mdi:chevron-up' : 'mdi:chevron-down'" class="h-5 w-5 text-muted" />
            </button>

            <div v-show="attendanceOpen" class="mt-4 grid gap-3">
                <div v-if="attendanceLoading && !attendance.code" class="text-xs text-muted">
                    Memuat kode...
                </div>
                <div v-else-if="attendanceError" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ attendanceError }}
                </div>
                <template v-else-if="attendance.code">
                    <div class="flex justify-center">
                        <img
                            :src="attendance.qr_svg"
                            alt="QR kode kehadiran"
                            class="h-44 w-44 rounded-xl border border-border bg-white p-2"
                        />
                    </div>
                    <div class="text-center">
                        <div class="text-[11px] text-muted">Atau sebutkan kode ini</div>
                        <div class="mt-1 font-mono text-3xl font-bold tracking-[0.3em] text-ink">
                            {{ attendance.code }}
                        </div>
                    </div>
                    <div class="grid gap-1">
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                            <div
                                class="h-full rounded-full transition-all duration-1000 ease-linear"
                                :class="secondsLeft <= 5 ? 'bg-rose-500' : 'bg-primary'"
                                :style="{ width: countdownWidth }"
                            ></div>
                        </div>
                        <div class="text-center text-[11px] text-muted">
                            Berlaku {{ secondsLeft }} detik lagi
                        </div>
                    </div>
                </template>
            </div>
        </div>
        <div class="w-full rounded-2xl border border-border bg-panel px-5 py-4 shadow-sm">
            <div class="text-xs uppercase tracking-[0.2em] text-muted">Menu</div>
            <router-link
                class="mt-3 block rounded-xl border border-border px-3 py-2 text-center text-xs font-semibold text-ink"
                to="/blu/dashboard-lecture/report"
            >
                PPDS Report
            </router-link>
            <router-link
                class="mt-2 block rounded-xl border border-border px-3 py-2 text-center text-xs font-semibold text-ink"
                to="/blu/students/monitoring"
            >
                Monitoring
            </router-link>
            <router-link
                class="mt-2 block rounded-xl border border-border px-3 py-2 text-center text-xs font-semibold text-ink"
                to="/blu/students/monitoring-logbook"
            >
                Monitoring Logbook
            </router-link>
        </div>
        <div class="w-full rounded-2xl border border-border bg-panel px-5 py-4 shadow-sm">
            <router-link
                class="block rounded-xl border border-border px-3 py-2 text-center text-xs font-semibold text-ink"
                to="/blu/release-note"
            >
                Release Notes
            </router-link>
            <router-link
                class="mt-2 block rounded-xl bg-primary px-3 py-2 text-center text-xs font-semibold text-white"
                to="/blu/tutorial"
            >
                Tutorial
            </router-link>
        </div>
        <ProfileModal
            :open="profileModalOpen"
            :form="user"
            :image-url="dataRaw.image_url"
            :submitting="updatingProfile"
            @close="closeProfileModal"
            @submit="updateProfile"
            @file-change="getImage"
        />
    </div>
</template>

<script>
import Repository from '../../repository';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import ProfileModal from './ProfileModal.vue';
import { uploadFirebaseFile } from '../../upload';
import { Icon } from '../../icons';

export default {
    components: {
        Loading,
        ProfileModal,
        Icon,
    },
    data() {
        return {
            profileModalOpen: false,
            updatingProfile: false,
            uploadingImage: false,
            loadingProfile: false,
            attendanceOpen: false,
            attendanceLoading: false,
            attendanceError: '',
            attendance: {
                code: '',
                qr_svg: '',
                period: 30,
            },
            secondsLeft: 0,
            countdownTimer: null,
            dataRaw: {
                image_url: '',
                info_cards: {
                    avg_scoring: 0,
                    scoring: 0,
                    act_lect: 0,
                    log_pending: 0,
                },
            },
            user: {
                id: '',
                name: '',
                name_alt: '',
                email: '',
                password: '',
                password_confirmation: '',
                image: '',
                phone: '',
                address: '',
            },
        };
    },
    created() {
        this.loadUser();
    },
    beforeDestroy() {
        this.stopCountdown();
    },
    computed: {
        countdownWidth() {
            const period = this.attendance.period || 30;
            return `${Math.max(0, Math.min(100, (this.secondsLeft / period) * 100))}%`;
        },
        profileStyle() {
            if (this.user && this.user.image) {
                if (this.user.image.startsWith('http')) {
                    return `background-image: url(${this.user.image})`;
                }
                return `background-image: url(/storage/${this.user.image})`;
            }
            return 'background-image: url(/assets/images/dr_default.jpeg)';
        },
    },
    methods: {
        toggleAttendanceCode() {
            this.attendanceOpen = !this.attendanceOpen;

            if (this.attendanceOpen) {
                this.fetchAttendanceCode();
                return;
            }

            // Stop polling the moment the card is collapsed — no reason to
            // keep a code live on a screen nobody is showing.
            this.stopCountdown();
        },
        fetchAttendanceCode() {
            this.attendanceLoading = true;
            this.attendanceError = '';

            return Repository.get('/api/attendance-code')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    if (!result) {
                        this.attendanceError = 'Gagal memuat kode kehadiran.';
                        return;
                    }
                    this.attendance = {
                        code: result.code,
                        qr_svg: result.qr_svg,
                        period: result.period || 30,
                    };
                    this.startCountdown(result.expires_in);
                })
                .catch(() => {
                    this.attendanceError = 'Gagal memuat kode kehadiran.';
                    this.stopCountdown();
                })
                .finally(() => {
                    this.attendanceLoading = false;
                });
        },
        startCountdown(secondsRemaining) {
            this.stopCountdown();
            this.secondsLeft = secondsRemaining || this.attendance.period;

            this.countdownTimer = setInterval(() => {
                this.secondsLeft -= 1;
                if (this.secondsLeft > 0) {
                    return;
                }
                // Window elapsed: pull the next code rather than deriving it
                // client-side, so the server clock stays the only authority.
                this.stopCountdown();
                if (this.attendanceOpen) {
                    this.fetchAttendanceCode();
                }
            }, 1000);
        },
        stopCountdown() {
            if (this.countdownTimer) {
                clearInterval(this.countdownTimer);
                this.countdownTimer = null;
            }
        },
        loadUser() {
            this.loadingProfile = true;
            return Repository.get('/api/lecture-profile')
                .then((response) => {
                    const payload = response && response.data && response.data.result
                        ? response.data.result
                        : {};
                    const lecture = payload.lecture || {};
                    const profile = payload.profile || {};
                    this.user = {
                        ...this.user,
                        ...lecture,
                        address: profile.address || '',
                        phone: profile.phone || '',
                        image: profile.image || '',
                    };
                    if (profile.image && profile.image.startsWith('http')) {
                        this.dataRaw.image_url = profile.image;
                    } else {
                        this.dataRaw.image_url = profile.image ? `/storage/${profile.image}` : '';
                    }
                })
                .catch(() => {
                    this.user = {
                        id: '',
                        name: '',
                        name_alt: '',
                        email: '',
                        password: '',
                        password_confirmation: '',
                        image: '',
                        phone: '',
                        address: '',
                    };
                })
                .finally(() => {
                    this.loadingProfile = false;
                });
        },
        openProfileModal() {
            this.profileModalOpen = true;
        },
        closeProfileModal() {
            this.profileModalOpen = false;
        },
        updateProfile() {
            this.updatingProfile = true;

            const payload = {
                email: this.user.email,
                name: this.user.name,
                password: this.user.password,
                password_confirmation: this.user.password_confirmation,
                image: this.user.image,
                phone: this.user.phone,
                address: this.user.address,
            };

            return Repository.patch('/api/lecture-profile', payload)
                .then(() => {
                    this.profileModalOpen = false;
                    this.loadUser();
                    this.user.password = '';
                    this.user.password_confirmation = '';
                    this.$showToast('Profile updated successfully.');
                })
                .finally(() => {
                    this.updatingProfile = false;
                });
        },
        async getImage(event) {
            const file = event && event.target ? event.target.files[0] : null;
            if (!file) {
                return;
            }

            if (file.size > 4200000) {
                this.user.image = '';
                return;
            }

            this.uploadingImage = true;
            try {
                const prefix = 'Lecture/Profile';
                const url = await uploadFirebaseFile({ file, prefix });
                if (url) {
                    this.user.image = url;
                    this.dataRaw.image_url = url;
                }
            } finally {
                this.uploadingImage = false;
            }
        },
    },
};
</script>
