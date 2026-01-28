<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Lecture Scoring</div>
                <h1 class="text-2xl font-semibold text-ink">Lembar Penilaian Jurnal</h1>
                <div v-if="dataDetail.stase_task" class="mt-1 text-sm text-muted">
                    {{ dataDetail.stase_task.name }}
                </div>
                <div v-if="dataDetail.title" class="text-xs text-muted">
                    Judul: {{ dataDetail.title }}
                </div>
            </div>
            <router-link class="rounded-xl border border-border px-4 py-2 text-sm text-muted" to="/cblu/dashboard-lecture">
                Back
            </router-link>
        </header>

        <section class="relative rounded-2xl border border-border bg-panel">
            <Loading :active="loading" :is-full-page="false" />
            <div class="border-b border-border px-5 py-4 text-sm font-semibold text-ink">
                Poin Penilaian
            </div>

            <div v-if="errorMessage" class="border-b border-rose-100 bg-rose-50 px-5 py-3 text-xs text-rose-600">
                {{ errorMessage }}
            </div>

            <div class="grid gap-4 px-5 py-6">
                <div
                    v-for="(point, index) in points"
                    :key="point.id"
                    class="rounded-2xl border border-border bg-white p-4"
                >
                    <div class="text-sm font-semibold text-ink">
                        {{ index + 1 }}. {{ point.task_detail ? point.task_detail.name : '-' }}
                    </div>
                    <div class="mt-3 grid gap-2 text-sm">
                        <label
                            v-for="option in scoreOptions"
                            :key="`${point.id}-${option.value}`"
                            class="flex items-center gap-2"
                        >
                            <input
                                v-model="form.no[index]"
                                type="radio"
                                :name="`score-${point.id}`"
                                :value="`${point.id},${option.value}`"
                                class="h-4 w-4 rounded border border-border"
                            />
                            <span>{{ option.label }} ({{ option.value }})</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button
                        class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                        type="button"
                        :disabled="submitting"
                        @click="loadData"
                    >
                        Refresh
                    </button>
                    <button
                        class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                        type="button"
                        :disabled="submitting"
                        @click="submitData"
                    >
                        {{ submitting ? 'Saving...' : 'Submit' }}
                    </button>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Repository from '../../repository';
import Swal from 'sweetalert2';

export default {
    components: {
        Loading,
    },
    data() {
        return {
            loading: false,
            submitting: false,
            errorMessage: '',
            dataDetail: {},
            form: {
                stase_task_log_id: '',
                open_stase_task_id: this.$route.params.id || this.$route.params.open_stase_task_id,
                no: [],
            },
            scoreOptions: [
                { label: 'Kurang', value: 75 },
                { label: 'Cukup', value: 80 },
                { label: 'Sesuai Harapan', value: 85 },
                { label: 'Diatas Harapan', value: 90 },
                { label: 'Istimewa', value: 95 },
            ],
        };
    },
    computed: {
        points() {
            return Array.isArray(this.dataDetail.stase_task_log_point)
                ? this.dataDetail.stase_task_log_point
                : [];
        },
    },
    created() {
        this.loadData();
    },
    methods: {
        loadData() {
            if (!this.form.open_stase_task_id) {
                this.errorMessage = 'Open stase task id is missing.';
                return;
            }
            this.loading = true;
            this.errorMessage = '';

            return Repository.get('/api/generate-task-log-detail', {
                params: {
                    open_stase_task_id: this.form.open_stase_task_id,
                },
            })
                .then((response) => {
                    const payload = response && response.data
                        ? (response.data.result || response.data)
                        : {};
                    this.dataDetail = payload || {};
                    this.form.stase_task_log_id = payload ? payload.id : '';

                    this.form.no = this.points.map((point) => {
                        const value = point.score === null || point.score === undefined ? 75 : point.score;
                        return `${point.id},${value}`;
                    });
                })
                .catch(() => {
                    this.dataDetail = {};
                    this.form.no = [];
                    this.errorMessage = 'Failed to load task detail.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        submitData() {
            if (!this.form.stase_task_log_id) {
                this.errorMessage = 'Task log id is missing.';
                return;
            }
            this.submitting = true;
            this.errorMessage = '';

            return Repository.post(`/api/stase-task-logs-update-score/${this.form.stase_task_log_id}`, {
                params: this.form,
            })
                .then(() => {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: 'Score saved',
                        showConfirmButton: false,
                        timer: 1200,
                    });
                    this.$router.push('/cblu/dashboard-lecture');
                })
                .catch(() => {
                    this.errorMessage = 'Failed to save score.';
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
    },
};
</script>
