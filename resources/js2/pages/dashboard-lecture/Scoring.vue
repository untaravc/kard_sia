<template>
    <div class="flex flex-col gap-6">
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
            @open-direct-score="openDirectScoreModal"
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

        <FileDetailModal
            :open="detailModalOpen"
            :file="detailFile"
            @close="detailModalOpen = false"
        />
        <Modal
            :open="directScoreModalOpen"
            title="Nilai Langsung"
            eyebrow="Score Form"
            size="md"
            @close="closeDirectScoreModal"
        >
            <div class="grid gap-4">
                <div class="rounded-xl border border-border bg-white px-4 py-3 text-sm">
                    <div class="font-semibold text-ink">
                        {{ directScoreDetail ? (directScoreDetail.student ? directScoreDetail.student.name : 'Student') : '-' }}
                    </div>
                    <div class="text-xs text-muted">
                        {{ directScoreDetail && directScoreDetail.stase_task ? directScoreDetail.stase_task.name : '-' }}
                        <span v-if="directScoreDetail && directScoreDetail.title">• {{ directScoreDetail.title }}</span>
                    </div>
                </div>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Point Average</span>
                    <input
                        v-model.number="directScoreForm.point_average"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        required
                    />
                </label>
                <div v-if="directScoreError" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ directScoreError }}
                </div>
            </div>
            <template #footer>
                <button
                    class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                    type="button"
                    @click="closeDirectScoreModal"
                >
                    Cancel
                </button>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="button"
                    :disabled="directScoreSubmitting"
                    @click="submitDirectScore"
                >
                    {{ directScoreSubmitting ? 'Saving...' : 'Submit' }}
                </button>
            </template>
        </Modal>
    </div>
</template>

<script>
import Repository from '../../repository';
import ScoringCard from './ScoringCard.vue';
import ExamScoringCard from './ExamScoringCard.vue';
import FileDetailModal from './FileDetailModal.vue';
import Modal from '../../components/Modal.vue';

export default {
    components: {
        ScoringCard,
        ExamScoringCard,
        FileDetailModal,
        Modal,
    },
    data() {
        return {
            dataContent: [],
            dataContentPagination: {},
            dataContentAll: [],
            detailFile: {},
            detailModalOpen: false,
            directScoreModalOpen: false,
            directScoreSubmitting: false,
            directScoreError: '',
            directScoreDetail: null,
            directScoreForm: {
                point_average: '',
                stase_task_id: null,
                student_id: null,
                stase_id: null,
                task_id: null,
            },
            loadingOpenTasks: false,
            loadingOpenTasksAll: false,
            scoreFilter: '',
            scoreFilterTimer: null,
            scoreStats: {
                pending: 0,
                done_this_month: 0,
                total: 0,
            },
        };
    },
    created() {
        this.loadData();
        this.loadDataAll();
        this.loadScoreStats();
    },
    beforeDestroy() {
        if (this.scoreFilterTimer) {
            clearTimeout(this.scoreFilterTimer);
        }
    },
    methods: {
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
        openDirectScoreModal(item) {
            this.directScoreDetail = item || null;
            this.directScoreForm = {
                point_average: item && item.data ? item.data.point_average : '',
                stase_task_id: item ? item.stase_task_id : null,
                student_id: item ? item.student_id : null,
                stase_id: item && item.stase_task && item.stase_task.stase ? item.stase_task.stase.id : null,
                task_id: item && item.stase_task && item.stase_task.task ? item.stase_task.task.id : null,
            };
            this.directScoreError = '';
            this.directScoreModalOpen = true;
        },
        closeDirectScoreModal() {
            this.directScoreModalOpen = false;
        },
        submitDirectScore() {
            if (this.directScoreSubmitting) {
                return;
            }
            if (!this.directScoreForm.stase_task_id || !this.directScoreForm.student_id || !this.directScoreForm.stase_id || !this.directScoreForm.task_id) {
                this.directScoreError = 'Detail score is incomplete.';
                return;
            }
            this.directScoreSubmitting = true;
            this.directScoreError = '';

            return Repository.post('/api/lecture-add-score', this.directScoreForm)
                .then(() => {
                    this.closeDirectScoreModal();
                    this.loadData();
                    this.loadScoreStats();
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to save score.';
                    this.directScoreError = message;
                })
                .finally(() => {
                    this.directScoreSubmitting = false;
                });
        },
        openFileModal(file) {
            this.detailFile = file || {};
            this.detailModalOpen = true;
        },
    },
};
</script>
