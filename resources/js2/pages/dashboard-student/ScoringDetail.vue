<template>
    <div class="grid gap-6">
        <section class="rounded-2xl border border-border bg-panel p-6 shadow-sm">
        <Loading :active="loading" :is-full-page="false" />
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Stase Tasks</div>
                <h1 class="text-lg font-semibold text-ink">{{ staseTitle }}</h1>
                <div v-if="stasePeriod" class="mt-1 text-xs text-muted">
                    {{ stasePeriod }}
                </div>
            </div>
            <router-link
                class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                to="/cblu/dashboard-student/scoring"
            >
                Back
            </router-link>
        </div>

        <div
            v-if="errorMessage"
            class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600"
        >
            {{ errorMessage }}
        </div>

        <div class="mt-5 grid gap-3">
            <div
                v-for="task in tasks"
                :key="task.id"
                class="rounded-xl border border-border/60 bg-white px-4 py-3"
            >
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="text-sm font-semibold text-ink">
                            {{ (task.task && task.task.name) || task.name || 'Task' }}
                        </div>
                        <div v-if="task.name && task.task && task.task.name" class="mt-1 text-xs text-muted">
                            {{ task.name }}
                        </div>
                        <div v-if="task.lecture" class="mt-1 text-xs text-muted">
                            Lecture: {{ task.lecture.name || task.lecture.email || 'Lecture' }}
                        </div>
                    </div>
                    <button
                        class="rounded-lg border border-border px-3 py-1 text-[11px] font-semibold text-muted"
                        type="button"
                        @click="openScoringModal(task)"
                    >
                        Open Scoring
                    </button>
                </div>
                <div v-if="task.open_stase_tasks && task.open_stase_tasks.length" class="mt-3 rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2">
                    <div class="text-[11px] font-semibold text-emerald-700">Open scoring already created</div>
                    <div class="mt-2 grid gap-1 text-xs text-emerald-700">
                        <div
                            v-for="(openTask, openIndex) in task.open_stase_tasks"
                            :key="openTask.id"
                            class="flex flex-wrap items-start gap-3 border-b border-emerald-100 pb-2"
                            :class="openIndex ? 'mt-2' : ''"
                        >
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                                        {{ openTask.lecture ? openTask.lecture.name : 'Lecture' }}
                                    </span>
                                    <span v-if="openTask.plan">Plan: {{ openTask.plan }}</span>
                                    <span v-if="openTask.title">• {{ openTask.title }}</span>
                                    <span v-if="openTask.score" class="rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                                        Avg: {{ openTask.score }}
                                    </span>
                                </div>
                                <div v-if="openTask.stase_task_log" class="mt-2 flex flex-wrap items-center gap-2 text-[11px] text-emerald-700">
                                    <span class="rounded-full bg-white px-2 py-0.5 font-semibold">
                                        Score: {{ openTask.stase_task_log.point_average || 0 }}
                                    </span>
                                    <span v-if="openTask.stase_task_log.date">Date: {{ openTask.stase_task_log.date }}</span>
                                    <span v-if="openTask.stase_task_log.lecture">
                                        Scorer: {{ openTask.stase_task_log.lecture.name || openTask.stase_task_log.lecture.email }}
                                    </span>
                                </div>
                                <div v-if="openTask.files && openTask.files.length" class="mt-2 flex flex-wrap gap-2">
                                    <button
                                        v-for="file in openTask.files"
                                        :key="file.id"
                                        class="rounded-md border border-emerald-200 bg-white px-2 py-1 text-[10px] font-semibold text-emerald-700"
                                        type="button"
                                        @click="openPreview(file)"
                                    >
                                        {{ file.title || 'Document' }}
                                    </button>
                                </div>
                            </div>
                            <div class="ml-auto flex shrink-0 items-center gap-2">
                                <button
                                    class="rounded-lg border border-emerald-200 bg-white px-2 py-0.5 text-[10px] font-semibold text-emerald-700"
                                    type="button"
                                    @click="openUploadModal(openTask, 'score')"
                                >
                                    Upload Score
                                </button>
                                <button
                                    class="rounded-lg border border-emerald-200 bg-white px-2 py-0.5 text-[10px] font-semibold text-emerald-700"
                                    type="button"
                                    @click="openUploadModal(openTask, 'task')"
                                >
                                    Upload Task
                                </button>
                                <button
                                    class="rounded-lg border border-emerald-200 bg-white px-2 py-0.5 text-[10px] font-semibold text-emerald-700"
                                    type="button"
                                    :disabled="Boolean(openTask.score)"
                                    @click="openUpdateModal(openTask, task)"
                                >
                                    Update
                                </button>
                                <button
                                    class="rounded-lg border border-rose-200 bg-white px-2 py-0.5 text-[10px] font-semibold text-rose-600"
                                    type="button"
                                    :disabled="Boolean(openTask.score)"
                                    @click="deleteOpenTask(openTask)"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    v-else-if="task.stase_task_logs && task.stase_task_logs.length"
                    class="mt-3 rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"
                >
                    <div class="text-[11px] font-semibold text-emerald-700">Score available</div>
                    <div class="mt-2 grid gap-2">
                        <div
                            v-for="log in task.stase_task_logs"
                            :key="log.id"
                            class="flex flex-wrap items-center gap-2 border-b border-emerald-100 pb-2 last:border-b-0 last:pb-0"
                        >
                            <span class="rounded-full bg-white px-2 py-0.5 font-semibold">
                                Score: {{ log.point_average || 0 }}
                            </span>
                            <span v-if="log.date">Date: {{ log.date }}</span>
                            <span v-if="log.lecture">
                                Scorer: {{ log.lecture.name || log.lecture.email }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="!loading && tasks.length === 0" class="text-xs text-muted">
                No tasks available.
            </div>
        </div>
        </section>
        <Modal
            :open="scoringModalOpen"
            title="Open Scoring"
            eyebrow="Create scoring"
            size="md"
            @close="closeScoringModal"
        >
            <div class="grid gap-4 text-sm">
                <div class="rounded-xl border border-border bg-white px-4 py-3">
                    <div class="text-sm font-semibold text-ink">
                        {{ selectedTaskTitle }}
                    </div>
                    <div v-if="selectedTaskLecture" class="mt-1 text-xs text-muted">
                        Lecture: {{ selectedTaskLecture }}
                    </div>
                </div>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Title</span>
                    <input
                        v-model.trim="scoringForm.title"
                        type="text"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Plan date</span>
                    <input
                        v-model="scoringForm.plan_date"
                        type="date"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <div class="grid gap-2">
                    <span class="text-muted text-xs">Lectures</span>
                    <div v-if="lecturesLoading" class="text-xs text-muted">Loading lectures...</div>
                    <div v-else class="grid max-h-56 gap-3 overflow-y-auto pr-1 sm:grid-cols-2">
                        <label
                            v-for="lecture in lectures"
                            :key="lecture.id"
                            class="flex items-center gap-2 rounded-xl border border-border bg-white px-3 py-2 text-xs text-muted shadow-sm"
                        >
                            <input
                                v-model="scoringForm.lecture_ids"
                                type="checkbox"
                                class="h-4 w-4 rounded border-border text-primary focus:ring-primary/30"
                                :value="lecture.id"
                            />
                            <span class="text-ink">{{ lecture.name }}</span>
                        </label>
                        <div v-if="lectures.length === 0" class="text-xs text-muted">
                            No lectures available.
                        </div>
                    </div>
                </div>
            </div>
            <template #footer>
                <button
                    class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                    type="button"
                    @click="closeScoringModal"
                >
                    Cancel
                </button>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="button"
                    @click="submitScoring"
                >
                    Save
                </button>
            </template>
        </Modal>
        <Modal
            :open="updateModalOpen"
            title="Update Open Scoring"
            eyebrow="Edit scoring"
            size="md"
            @close="closeUpdateModal"
        >
            <div class="grid gap-4 text-sm">
                <div class="rounded-xl border border-border bg-white px-4 py-3">
                    <div class="text-sm font-semibold text-ink">
                        {{ updateForm.title || selectedOpenTaskTitle }}
                    </div>
                    <div v-if="selectedOpenTaskLecture" class="mt-1 text-xs text-muted">
                        Lecture: {{ selectedOpenTaskLecture }}
                    </div>
                </div>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Title</span>
                    <input
                        v-model.trim="updateForm.title"
                        type="text"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Plan date</span>
                    <input
                        v-model="updateForm.plan"
                        type="date"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
            </div>
            <template #footer>
                <button
                    class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                    type="button"
                    @click="closeUpdateModal"
                >
                    Cancel
                </button>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="button"
                    :disabled="updateSubmitting || !selectedOpenTask"
                    @click="submitUpdateOpenTask"
                >
                    {{ updateSubmitting ? 'Saving...' : 'Save' }}
                </button>
            </template>
        </Modal>
        <Modal
            :open="uploadModalOpen"
            :title="uploadModalTitle"
            eyebrow="Attach file"
            size="md"
            @close="closeUploadModal"
        >
            <div class="grid gap-4 text-sm">
                <div class="rounded-xl border border-border bg-white px-4 py-3">
                    <div class="text-sm font-semibold text-ink">{{ selectedOpenTaskTitle }}</div>
                    <div v-if="selectedOpenTaskLecture" class="mt-1 text-xs text-muted">
                        Lecture: {{ selectedOpenTaskLecture }}
                    </div>
                </div>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Title</span>
                    <input
                        v-model.trim="uploadForm.title"
                        type="text"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Description</span>
                    <textarea
                        v-model.trim="uploadForm.desc"
                        rows="3"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    ></textarea>
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">File</span>
                    <input
                        type="file"
                        accept="image/*,application/pdf"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm"
                        @change="handleUploadFile"
                    />
                </label>
                <div v-if="uploadForm.link" class="text-xs text-muted break-all">
                    {{ uploadForm.link }}
                </div>
            </div>
            <template #footer>
                <button
                    class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                    type="button"
                    @click="closeUploadModal"
                >
                    Cancel
                </button>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="button"
                    :disabled="uploadSubmitting || !uploadForm.link || !selectedOpenTask"
                    @click="submitUpload"
                >
                    {{ uploadSubmitting ? 'Saving...' : 'Save' }}
                </button>
            </template>
        </Modal>
        <Modal
            :open="previewModalOpen"
            :title="previewTitle"
            eyebrow="Document preview"
            size="full"
            @close="closePreview"
        >
            <div class="grid gap-3">
                <img
                    v-if="previewType === 'image'"
                    :src="previewSrc"
                    alt="Document preview"
                    class="w-full rounded-xl border border-border object-contain"
                />
                <iframe
                    v-else-if="previewType === 'pdf'"
                    :src="previewSrc"
                    class="h-[70vh] w-full rounded-xl border border-border"
                ></iframe>
                <div v-else class="text-sm text-muted">No preview available.</div>
            </div>
        </Modal>
    </div>
</template>

<script>
import Repository from '../../repository';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Modal from '../../components/Modal.vue';
import { uploadFirebaseFile } from '../../upload';

export default {
    components: {
        Loading,
        Modal,
    },
    data() {
        return {
            tasks: [],
            loading: false,
            errorMessage: '',
            staseLog: null,
            scoringModalOpen: false,
            selectedTask: null,
            scoringForm: {
                title: '',
                plan_date: '',
                lecture_ids: [],
            },
            lectures: [],
            lecturesLoading: false,
            updateModalOpen: false,
            updateSubmitting: false,
            selectedOpenTask: null,
            updateForm: {
                title: '',
                plan: '',
            },
            uploadModalOpen: false,
            uploadSubmitting: false,
            uploadForm: {
                title: '',
                desc: '',
                link: '',
                type: 'score',
            },
            previewModalOpen: false,
            previewType: '',
            previewSrc: '',
        };
    },
    computed: {
        staseLogId() {
            return this.$route.params.stase_log_id;
        },
        staseTitle() {
            if (this.staseLog && this.staseLog.stase && this.staseLog.stase.name) {
                return this.staseLog.stase.name;
            }
            return 'Stase';
        },
        stasePeriod() {
            if (!this.staseLog) {
                return '';
            }
            if (this.staseLog.start_date || this.staseLog.end_date) {
                return `${this.staseLog.start_date || ''} - ${this.staseLog.end_date || ''}`.trim();
            }
            return '';
        },
        selectedTaskTitle() {
            if (!this.selectedTask) {
                return 'Task';
            }
            return (this.selectedTask.task && this.selectedTask.task.name) || this.selectedTask.name || 'Task';
        },
        selectedTaskLecture() {
            if (!this.selectedTask || !this.selectedTask.lecture) {
                return '';
            }
            return this.selectedTask.lecture.name || this.selectedTask.lecture.email || 'Lecture';
        },
        selectedOpenTaskTitle() {
            if (!this.selectedOpenTask) {
                return 'Open Scoring';
            }
            return this.selectedOpenTask.title || 'Open Scoring';
        },
        selectedOpenTaskLecture() {
            if (!this.selectedOpenTask || !this.selectedOpenTask.lecture) {
                return '';
            }
            return this.selectedOpenTask.lecture.name || this.selectedOpenTask.lecture.email || 'Lecture';
        },
        previewTitle() {
            if (this.previewType === 'image') {
                return 'Image preview';
            }
            if (this.previewType === 'pdf') {
                return 'PDF preview';
            }
            return 'Preview';
        },
        uploadModalTitle() {
            return this.uploadForm.type === 'task' ? 'Upload Task Document' : 'Upload Score Document';
        },
    },
    created() {
        this.loadStaseTasks();
    },
    methods: {
        isImageUrl(url) {
            if (!url || typeof url !== 'string') {
                return false;
            }
            return /\.(png|jpe?g|gif|webp|bmp|svg)(\?.*)?$/i.test(url);
        },
        openPreview(file) {
            if (!file || !file.link) {
                return;
            }
            this.previewSrc = file.link;
            this.previewType = this.isImageUrl(file.link) ? 'image' : 'pdf';
            this.previewModalOpen = true;
        },
        closePreview() {
            this.previewModalOpen = false;
            this.previewType = '';
            this.previewSrc = '';
        },
        loadStaseTasks() {
            this.loading = true;
            this.errorMessage = '';
            return Repository.get('/api/student-stase')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const taken = result && Array.isArray(result.taken_stase) ? result.taken_stase : [];
                    const log = taken.find((item) => String(item.id) === String(this.staseLogId));
                    if (!log) {
                        this.errorMessage = 'Stase log not found.';
                        this.tasks = [];
                        this.staseLog = null;
                        return null;
                    }
                    this.staseLog = log;
                    const staseId = log.stase_id;
                    if (!staseId) {
                        this.errorMessage = 'Stase not found.';
                        this.tasks = [];
                        return null;
                    }
                    return Repository.get(`/api/student-stase-task/${staseId}`)
                        .then((taskResponse) => {
                            const data = taskResponse && taskResponse.data ? taskResponse.data.result : [];
                            this.tasks = Array.isArray(data) ? data : [];
                        });
                })
                .catch(() => {
                    this.tasks = [];
                    this.errorMessage = 'Failed to load tasks.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        openScoringModal(task) {
            this.selectedTask = task || null;
            this.scoringForm = {
                title: this.selectedTaskTitle,
                plan_date: '',
                lecture_ids: [],
            };
            this.scoringModalOpen = true;
            if (!this.lectures.length) {
                this.fetchLectures();
            }
        },
        closeScoringModal() {
            this.scoringModalOpen = false;
            this.selectedTask = null;
            this.scoringForm = {
                title: '',
                plan_date: '',
                lecture_ids: [],
            };
        },
        submitScoring() {
            if (!this.selectedTask) {
                return;
            }
            const payload = {
                stase_task_id: this.selectedTask.id,
                title: this.scoringForm.title,
                plan: this.scoringForm.plan_date,
                lecture_ids: this.scoringForm.lecture_ids,
            };
            return Repository.post('/api/open-stase-task', payload)
                .then(() => {
                    this.closeScoringModal();
                    if (this.$showToast) {
                        this.$showToast('Scoring form saved.');
                    }
                    this.loadStaseTasks();
                })
                .catch(() => {
                    if (this.$showToast) {
                        this.$showToast('Failed to save scoring.');
                    }
                });
        },
        openUpdateModal(openTask, parentTask) {
            this.selectedTask = parentTask || null;
            this.selectedOpenTask = openTask || null;
            this.updateForm = {
                title: (openTask && openTask.title) || '',
                plan: (openTask && openTask.plan) || '',
            };
            this.updateModalOpen = true;
        },
        closeUpdateModal() {
            this.updateModalOpen = false;
            this.updateSubmitting = false;
            this.selectedOpenTask = null;
            this.updateForm = {
                title: '',
                plan: '',
            };
        },
        submitUpdateOpenTask() {
            if (!this.selectedOpenTask || this.updateSubmitting) {
                return;
            }
            this.updateSubmitting = true;
            return Repository.patch(`/api/open-stase-task/${this.selectedOpenTask.id}`, {
                title: this.updateForm.title,
                plan: this.updateForm.plan,
            })
                .then(() => {
                    this.closeUpdateModal();
                    if (this.$showToast) {
                        this.$showToast('Open scoring updated.');
                    }
                    this.loadStaseTasks();
                })
                .catch(() => {
                    if (this.$showToast) {
                        this.$showToast('Failed to update scoring.');
                    }
                })
                .finally(() => {
                    this.updateSubmitting = false;
                });
        },
        deleteOpenTask(openTask) {
            if (!openTask) {
                return;
            }
            const confirmed = window.confirm('Delete this open scoring? This action cannot be undone.');
            if (!confirmed) {
                return;
            }
            return Repository.delete(`/api/open-stase-task/${openTask.id}`)
                .then(() => {
                    if (this.$showToast) {
                        this.$showToast('Open scoring deleted.');
                    }
                    this.loadStaseTasks();
                })
                .catch(() => {
                    if (this.$showToast) {
                        this.$showToast('Failed to delete scoring.');
                    }
                });
        },
        openUploadModal(openTask, type) {
            this.selectedOpenTask = openTask || null;
            this.uploadForm = {
                title: type === 'task' ? 'Upload Task' : 'Upload Score',
                desc: '',
                link: '',
                type: type || 'score',
            };
            this.uploadModalOpen = true;
        },
        closeUploadModal() {
            this.uploadModalOpen = false;
            this.uploadSubmitting = false;
            this.uploadForm = {
                title: '',
                desc: '',
                link: '',
                type: 'score',
            };
        },
        async handleUploadFile(event) {
            const file = event && event.target ? event.target.files[0] : null;
            if (!file) {
                return;
            }
            this.uploadSubmitting = true;
            try {
                const prefix = 'KardiologiFkkmk/Student/ScoreDocument';
                const url = await uploadFirebaseFile({ file, prefix });
                if (url) {
                    this.uploadForm.link = url;
                }
            } finally {
                this.uploadSubmitting = false;
            }
        },
        submitUpload() {
            if (!this.selectedOpenTask || !this.uploadForm.link) {
                return;
            }
            this.uploadSubmitting = true;
            return Repository.post('/api/files', {
                title: this.uploadForm.title,
                desc: this.uploadForm.desc,
                link: this.uploadForm.link,
                open_stase_task_id: this.selectedOpenTask.id,
                stase_task_log_id: this.selectedOpenTask.stase_task_log_id || null,
                type: this.uploadForm.type,
            })
                .then(() => {
                    this.closeUploadModal();
                    if (this.$showToast) {
                        this.$showToast('Document uploaded.');
                    }
                    this.loadStaseTasks();
                })
                .catch(() => {
                    if (this.$showToast) {
                        this.$showToast('Failed to upload document.');
                    }
                })
                .finally(() => {
                    this.uploadSubmitting = false;
                });
        },
        fetchLectures() {
            this.lecturesLoading = true;
            return Repository.get('/api/lecture-list')
                .then((response) => {
                    const result = response && response.data ? response.data.result : [];
                    this.lectures = Array.isArray(result) ? result : [];
                })
                .catch(() => {
                    this.lectures = [];
                })
                .finally(() => {
                    this.lecturesLoading = false;
                });
        },
    },
};
</script>
