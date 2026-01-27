<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Student Management</div>
                <h1 class="text-2xl font-semibold text-ink">Student Score</h1>
            </div>
            <router-link
                class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                to="/cblu/students"
            >
                Back
            </router-link>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
                <aside class="flex w-full flex-col gap-4 lg:w-72 lg:shrink-0">
                    <div class="rounded-2xl border border-border bg-white p-4">
                        <div class="text-sm font-semibold text-ink">{{ student.name || 'Student' }}</div>
                        <div class="text-xs text-muted">{{ student.email || '-' }}</div>
                        <ul class="mt-3 list-disc space-y-1 pl-5 text-xs text-ink">
                            <li>
                                <button class="underline hover:text-primary" type="button" @click="printScore('tahap_1')">
                                    Nilai Stase Tahap 1
                                </button>
                            </li>
                            <li>
                                <button class="underline hover:text-primary" type="button" @click="printScore('tahap_2')">
                                    Nilai Stase Tahap 2
                                </button>
                            </li>
                            <li>
                                <button class="underline hover:text-primary" type="button" @click="printScore('tahap_3')">
                                    Nilai Stase Tahap 3
                                </button>
                            </li>
                            <li>
                                <button class="underline hover:text-primary" type="button" @click="printScore('rekap')">
                                    Rekap Nilai
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-2xl border border-border bg-white">
                        <div class="border-b border-border px-4 py-3 text-sm font-semibold text-ink">
                            Stase
                        </div>
                        <div v-if="staseLoading" class="px-4 py-6 text-xs text-muted">
                            Loading stase list...
                        </div>
                        <div v-else-if="staseLogs.length === 0" class="px-4 py-6 text-xs text-muted">
                            No stase logs found.
                        </div>
                        <div v-else class="grid gap-1 p-2">
                            <button
                                v-for="staseLog in staseLogs"
                                :key="staseLog.id"
                                type="button"
                                class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-left text-sm"
                                :class="staseLog.id === filters.stase_log_id ? 'bg-primary/10 text-primary' : 'text-ink hover:bg-slate-50'"
                                @click="selectStaseLog(staseLog.id)"
                            >
                                <span>{{ staseLog.stase ? staseLog.stase.name : `Stase ${staseLog.id}` }}</span>
                                <span class="text-[10px] uppercase tracking-[0.2em] text-muted">
                                    {{ staseLog.status || '-' }}
                                </span>
                            </button>
                        </div>
                    </div>
                </aside>

                <div class="flex min-w-0 flex-1 flex-col gap-4">
                    <div class="relative rounded-2xl border border-border bg-white">
                        <div class="flex items-center justify-between border-b border-border px-4 py-3">
                            <div class="text-sm font-semibold text-ink">
                                Score Detail
                            </div>
                            <div class="text-xs text-muted">
                                {{ staseTitle }}
                            </div>
                        </div>

                        <Loading :active="loading" :is-full-page="false" />

                        <div v-if="errorMessage" class="border-b border-rose-100 bg-rose-50 px-4 py-3 text-xs text-rose-600">
                            {{ errorMessage }}
                        </div>

                        <div v-if="!loading && scoreGroups.length === 0" class="px-4 py-6 text-sm text-muted">
                            Belum ada data.
                        </div>

                        <div v-else class="divide-y divide-border">
                            <div v-for="group in scoreGroups" :key="group.id" class="flex flex-wrap items-start gap-3 px-4 py-4">
                                <div class="flex-1">
                                    <div class="font-semibold text-ink">
                                        {{ group.stase_task ? group.stase_task.name : 'Task' }}
                                    </div>
                                    <div class="text-xs text-muted">
                                        {{ group.title || '-' }}
                                    </div>

                                    <div v-if="group.scores && group.scores.length" class="mt-2 grid gap-1 text-xs">
                                        <button
                                            v-for="score in group.scores"
                                            :key="score.id"
                                            type="button"
                                            class="flex w-full items-center justify-between rounded-lg bg-slate-50 px-2 py-1 text-left text-slate-700 hover:bg-slate-100"
                                            @click="openPointModal(score)"
                                        >
                                            <span class="truncate">
                                                {{ score.lecture ? score.lecture.name : 'Lecture' }}
                                            </span>
                                            <span v-if="score.point_average > 0" class="ml-2 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                                {{ score.point_average }}
                                            </span>
                                        </button>
                                    </div>

                                    <div v-else-if="group.status === 'pending'" class="mt-2 text-xs text-muted">
                                        Belum ada data penilaian.
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button
                                        v-if="group.files && group.files[0]"
                                        type="button"
                                        class="rounded-xl border border-border px-3 py-1.5 text-xs text-muted"
                                        @click="openFileModal(group)"
                                    >
                                        File
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <Modal :open="pointModalOpen" title="Poin Penilaian" eyebrow="Score Detail" size="md" @close="closePointModal">
            <div class="grid gap-3">
                <div v-if="pointDetails.length === 0" class="rounded-xl border border-border bg-white px-3 py-4 text-sm text-muted">
                    Tidak ada detail poin.
                </div>
                <div v-else class="overflow-hidden rounded-xl border border-border">
                    <table class="min-w-full divide-y divide-border text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-muted">
                            <tr>
                                <th class="px-3 py-2 text-left">Poin</th>
                                <th class="px-3 py-2 text-left">Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border bg-white">
                            <tr v-for="point in pointDetails" :key="point.id">
                                <td class="px-3 py-2">{{ point.task_detail ? point.task_detail.name : '-' }}</td>
                                <td class="px-3 py-2">{{ point.score }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="text-xs text-muted">
                    <div class="mb-1 font-semibold text-ink">Catatan</div>
                    <div>{{ pointNote || '-' }}</div>
                </div>
            </div>
            <template #footer>
                <button class="rounded-xl border border-border px-4 py-2 text-sm text-muted" type="button" @click="closePointModal">
                    Tutup
                </button>
            </template>
        </Modal>

        <Modal :open="fileModalOpen" title="Uploaded File" eyebrow="File Detail" size="md" @close="closeFileModal">
            <div class="grid gap-3 text-center">
                <div class="text-sm font-semibold text-ink">{{ fileDetail.title || '-' }}</div>
                <div class="text-xs text-muted">{{ fileDetail.title || '-' }}</div>
                <div class="pt-2">
                    <a
                        v-if="fileDetail.link"
                        class="inline-flex items-center rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                        :href="fileDetail.link"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Download
                    </a>
                    <button
                        v-else
                        class="inline-flex items-center rounded-xl border border-border px-4 py-2 text-sm text-muted"
                        type="button"
                        disabled
                    >
                        No file
                    </button>
                </div>
            </div>
            <template #footer>
                <button class="rounded-xl border border-border px-4 py-2 text-sm text-muted" type="button" @click="closeFileModal">
                    Close
                </button>
            </template>
        </Modal>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Modal from '../../components/Modal.vue';
import Repository from '../../repository';

export default {
    components: {
        Loading,
        Modal,
    },
    data() {
        return {
            student: {},
            staseLogs: [],
            staseLoading: false,
            loading: false,
            errorMessage: '',
            scoreGroups: [],
            filters: {
                stase_log_id: null,
            },
            pointModalOpen: false,
            pointDetails: [],
            pointNote: '',
            fileModalOpen: false,
            fileDetail: {},
        };
    },
    computed: {
        studentId() {
            return this.$route.params.student_id;
        },
        staseTitle() {
            const active = this.staseLogs.find((log) => log.id === this.filters.stase_log_id);
            return active && active.stase ? active.stase.name : 'No stase selected';
        },
    },
    created() {
        this.fetchStudent();
        this.fetchStaseLogs();
    },
    methods: {
        fetchStudent() {
            if (!this.studentId) {
                return;
            }
            return Repository.get(`/api/students/${this.studentId}`)
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.student = result || {};
                })
                .catch(() => {
                    this.student = {};
                });
        },
        fetchStaseLogs() {
            this.staseLoading = true;
            return Repository.get('/api/stase-list', {
                params: {
                    student_id: this.studentId,
                },
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.staseLogs = Array.isArray(result) ? result : [];

                    if (!this.filters.stase_log_id && this.staseLogs.length > 0) {
                        const ongoing = this.staseLogs.find((log) => log.status === 'ongoing');
                        this.filters.stase_log_id = ongoing ? ongoing.id : this.staseLogs[0].id;
                    }

                    this.fetchScores();
                })
                .catch(() => {
                    this.staseLogs = [];
                    this.fetchScores();
                })
                .finally(() => {
                    this.staseLoading = false;
                });
        },
        fetchScores() {
            if (!this.studentId) {
                this.errorMessage = 'Student ID is missing.';
                this.scoreGroups = [];
                return;
            }

            this.loading = true;
            this.errorMessage = '';

            return Repository.get(`/api/student-score/${this.studentId}`, {
                params: this.filters,
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.scoreGroups = Array.isArray(result) ? result : [];

                    const metaStaseLogId = response && response.data && response.data.meta
                        ? response.data.meta.stase_log_id
                        : null;
                    if (!this.filters.stase_log_id && metaStaseLogId) {
                        this.filters.stase_log_id = metaStaseLogId;
                    }
                })
                .catch(() => {
                    this.scoreGroups = [];
                    this.errorMessage = 'Failed to load student score.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        selectStaseLog(staseLogId) {
            if (this.filters.stase_log_id === staseLogId) {
                return;
            }
            this.filters.stase_log_id = staseLogId;
            this.fetchScores();
        },
        openPointModal(score) {
            if (!score || score.admin !== 0) {
                return;
            }
            this.pointDetails = Array.isArray(score.stase_task_log_point) ? score.stase_task_log_point : [];
            this.pointNote = score.note || '';
            this.pointModalOpen = true;
        },
        closePointModal() {
            this.pointModalOpen = false;
        },
        openFileModal(group) {
            this.fileDetail = group || {};
            this.fileModalOpen = true;
        },
        closeFileModal() {
            this.fileModalOpen = false;
        },
        printScore(stage) {
            if (!this.studentId) {
                return;
            }
            const base = '/resident-score-export';
            const url = stage === 'rekap'
                ? `${base}?student_id=${this.studentId}`
                : `${base}?student_id=${this.studentId}&tahap=${stage}`;
            window.open(url, '_blank');
        },
    },
};
</script>
