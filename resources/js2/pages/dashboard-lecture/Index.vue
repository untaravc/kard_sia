<template>
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
            <div class="flex w-full flex-col gap-6 lg:max-w-[360px] lg:flex-none">
                <ProfileCard
                    :user="user"
                    :info-cards="dataRaw.info_cards"
                    :schedules-count="schedules.length"
                    :score-pending="scorePending"
                    :loading="loadingProfile"
                    @edit-profile="openProfileModal"
                />
                <QuickLinkCard
                    title="Kurikulum & Standar Operasional"
                    subtitle="Akses dokumen resmi"
                    link-url="https://drive.google.com/drive/folders/1LoCN_taJgB37eHhdc9HY1IplL8N5jjBV?usp=sharing"
                />
                <AgendaCard
                    :schedules="schedules"
                    :loading="loadingSchedule"
                />
            </div>

            <div class="flex min-w-0 flex-1 flex-col gap-6">
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-border bg-white p-4">
                        <div class="text-xs uppercase tracking-[0.2em] text-muted">Tunda</div>
                        <div class="mt-2 text-2xl font-semibold text-ink">{{ scoreStats.pending }}</div>
                        <div class="text-xs text-muted">belum dinilai</div>
                    </div>
                    <div class="rounded-2xl border border-border bg-white p-4">
                        <div class="text-xs uppercase tracking-[0.2em] text-muted">Selesai</div>
                        <div class="mt-2 text-2xl font-semibold text-emerald-600">{{ scoreStats.done_this_month }}</div>
                        <div class="text-xs text-muted">dinilai bulan ini</div>
                    </div>
                    <div class="rounded-2xl border border-border bg-white p-4">
                        <div class="text-xs uppercase tracking-[0.2em] text-muted">Riwayat</div>
                        <div class="mt-2 text-2xl font-semibold text-slate-700">{{ scoreStats.total }}</div>
                        <div class="text-xs text-muted">riwayat seluruh penilaian</div>
                    </div>
                </div>
                <ScoringCard
                    :items="dataContent"
                    :loading="loadingOpenTasks"
                    :filter-value="scoreFilter"
                    @filter-change="handleScoreFilterChange"
                    @open-file="openFileModal"
                />
                <div
                    v-if="dataContentPagination && dataContentPagination.total"
                    class="flex items-center justify-between rounded-2xl border border-border bg-panel px-5 py-3 text-xs text-muted"
                >
                    <button
                        class="rounded-lg border border-border px-3 py-1.5"
                        type="button"
                        :disabled="dataContentPagination.current_page <= 1"
                        @click="loadData(dataContentPagination.current_page - 1)"
                    >
                        Prev
                    </button>
                    <div>
                        Page {{ dataContentPagination.current_page || 1 }}
                        / {{ dataContentPagination.last_page || 1 }}
                    </div>
                    <button
                        class="rounded-lg border border-border px-3 py-1.5"
                        type="button"
                        :disabled="dataContentPagination.current_page >= dataContentPagination.last_page"
                        @click="loadData(dataContentPagination.current_page + 1)"
                    >
                        Next
                    </button>
                </div>
                <ExamScoringCard
                    v-if="dataContentAll.length"
                    :items="dataContentAll"
                    :loading="loadingOpenTasksAll"
                    @open-file="openFileModal"
                />
            </div>
        </div>

        <FileDetailModal
            :open="detailModalOpen"
            :file="detailFile"
            @close="detailModalOpen = false"
        />
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
import { getApps, initializeApp } from 'firebase/app';
import { getDownloadURL, getStorage, ref, uploadBytes } from 'firebase/storage';
import { useFirebaseConfigStore } from '../../stores/firebaseConfig';
import Swal from 'sweetalert2';
import ProfileCard from './ProfileCard.vue';
import QuickLinkCard from './QuickLinkCard.vue';
import AgendaCard from './AgendaCard.vue';
import ScoringCard from './ScoringCard.vue';
import ExamScoringCard from './ExamScoringCard.vue';
import FileDetailModal from './FileDetailModal.vue';
import ProfileModal from './ProfileModal.vue';

export default {
    components: {
        ProfileCard,
        QuickLinkCard,
        AgendaCard,
        ScoringCard,
        ExamScoringCard,
        FileDetailModal,
        ProfileModal,
    },
    data() {
        return {
            dataContent: [],
            dataContentPagination: {},
            dataContentAll: [],
            schedules: [],
            detailFile: {},
            detailModalOpen: false,
            profileModalOpen: false,
            updatingProfile: false,
            uploadingImage: false,
            firebaseConfigStore: null,
            loadingProfile: false,
            loadingSchedule: false,
            loadingOpenTasks: false,
            loadingOpenTasksAll: false,
            toast: null,
            scoreFilter: '',
            scoreFilterTimer: null,
            scoreStats: {
                pending: 0,
                done_this_month: 0,
                total: 0,
            },
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
                email: '',
                password: '',
                password_confirmation: '',
                image: '',
                phone: '',
                address: '',
            },
        };
    },
    computed: {
        scorePending() {
            if (!Array.isArray(this.dataContent)) {
                return 0;
            }
            return this.dataContent.filter((item) => item && item.score == null).length;
        },
    },
    created() {
        this.firebaseConfigStore = useFirebaseConfigStore();
        this.initToast();
        this.loadData();
        this.loadDataAll();
        this.loadSchedule();
        this.loadUser();
        this.loadScoreStats();
    },
    methods: {
        initToast() {
            this.toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        },
        showToast(title, icon = 'success') {
            if (!this.toast) {
                this.initToast();
            }
            this.toast.fire({ title, icon });
        },
        loadData(page = 1) {
            this.loadingOpenTasks = true;
            return Repository.get('/api/open-stase-tasks', {
                params: {
                    page,
                    keyword: this.scoreFilter,
                },
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];
                    this.dataContent = data;
                    this.dataContentPagination = result || {};
                })
                .catch(() => {
                    this.dataContent = [];
                    this.dataContentPagination = {};
                })
                .finally(() => {
                    this.loadingOpenTasks = false;
                });
        },
        handleScoreFilterChange(value) {
            this.scoreFilter = value;
            if (this.scoreFilterTimer) {
                clearTimeout(this.scoreFilterTimer);
            }
            this.scoreFilterTimer = setTimeout(() => {
                this.loadData(1);
            }, 350);
        },
        loadDataAll() {
            this.loadingOpenTasksAll = true;
            return Repository.get('/cmsd/get-open-stase-task-all')
                .then((response) => {
                    const data = response && response.data ? response.data : [];
                    this.dataContentAll = Array.isArray(data) ? data : [];
                })
                .catch(() => {
                    this.dataContentAll = [];
                })
                .finally(() => {
                    this.loadingOpenTasksAll = false;
                });
        },
        loadSchedule() {
            this.loadingSchedule = true;
            return Repository.get('/api/activities-today')
                .then((response) => {
                    const data = response && response.data ? response.data.result : [];
                    this.schedules = Array.isArray(data) ? data : [];
                })
                .catch(() => {
                    this.schedules = [];
                })
                .finally(() => {
                    this.loadingSchedule = false;
                });
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
        loadScoreStats() {
            return Repository.get('/api/scoring-stat')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.scoreStats = {
                        pending: result && result.pending ? result.pending : 0,
                        done_this_month: result && result.done_this_month ? result.done_this_month : 0,
                        total: result && result.total ? result.total : 0,
                    };
                })
                .catch(() => {
                    this.scoreStats = {
                        pending: 0,
                        done_this_month: 0,
                        total: 0,
                    };
                });
        },
        openFileModal(file) {
            this.detailFile = file || {};
            this.detailModalOpen = true;
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
                    this.showToast('Profile updated successfully.');
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
                const config = await this.firebaseConfigStore.fetchConfig();
                if (!config) {
                    return;
                }

                if (!getApps().length) {
                    initializeApp(config);
                }

                const storage = getStorage();
                const prefix = 'KardiologiFkkmk/Lecture/Profile';
                const safeName = `${Date.now()}-${file.name}`.replace(/\s+/g, '-');
                const storageRef = ref(storage, `${prefix}/${safeName}`);
                await uploadBytes(storageRef, file);
                const url = await getDownloadURL(storageRef);
                this.user.image = url;
                this.dataRaw.image_url = url;
            } finally {
                this.uploadingImage = false;
            }
        },
    },
};
</script>
