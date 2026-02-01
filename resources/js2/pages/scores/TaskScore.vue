<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Lecture Scoring</div>
                <h1 class="text-2xl font-semibold text-ink">Lembar Penilaian</h1>
                <div v-if="dataDetail.stase_task" class="mt-1 text-sm text-muted">
                    {{ dataDetail.stase_task.name }}
                </div>
                <div v-if="dataDetail.title" class="text-xs text-muted">
                    Judul: {{ dataDetail.title }}
                </div>
            </div>
            <router-link class="rounded-xl border border-border px-4 py-2 text-sm text-muted" to="/blu/dashboard-lecture/scoring">
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

            <div class="grid gap-6 px-5 py-6">
                <div
                    v-if="dataDetail.task && dataDetail.task.desc === 'chief'"
                    class="grid gap-2"
                >
                    <label class="text-sm text-muted">Pertanyaan</label>
                    <textarea
                        v-model="form.note"
                        rows="6"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    ></textarea>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="grid gap-4">
                        <div
                            v-for="point in leftPoints"
                            :key="point.id"
                            class="rounded-2xl border border-border bg-white p-4"
                        >
                            <div class="text-sm font-semibold text-ink">
                                {{ point.order }}. {{ point.task_detail ? point.task_detail.name : '-' }}
                            </div>
                            <div v-if="point.task_detail && point.task_detail.type !== 'bool'" class="mt-3 mb-5">
                                <div class="flex items-center justify-between text-xs text-muted">
                                    <span>Score</span>
                                    <span class="font-semibold text-ink">{{ formRaw.no[point.order - 1] || 0 }}</span>
                                </div>
                                <VueSlider
                                    v-model="formRaw.no[point.order - 1]"
                                    v-bind="options"
                                    class="mt-2"
                                    @change="processPoint(point.order, point.id, formRaw.no[point.order - 1])"
                                />
                            </div>
                            <div v-else class="mt-3 flex items-center gap-4 text-sm">
                                <label class="flex items-center gap-2">
                                    <input
                                        v-model="formRaw.no[point.order - 1]"
                                        type="radio"
                                        :value="1"
                                        class="h-4 w-4 rounded border border-border"
                                        @change="processPoint(point.order, point.id, formRaw.no[point.order - 1])"
                                    />
                                    <span>Ya</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input
                                        v-model="formRaw.no[point.order - 1]"
                                        type="radio"
                                        :value="0"
                                        class="h-4 w-4 rounded border border-border"
                                        @change="processPoint(point.order, point.id, formRaw.no[point.order - 1])"
                                    />
                                    <span>Tidak</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4">
                        <div
                            v-for="point in rightPoints"
                            :key="point.id"
                            class="rounded-2xl border border-border bg-white p-4"
                        >
                            <div class="text-sm font-semibold text-ink">
                                {{ point.order }}. {{ point.task_detail ? point.task_detail.name : '-' }}
                            </div>
                            <div v-if="point.task_detail && point.task_detail.type !== 'bool'" class="mt-3 mb-5">
                                <div class="flex items-center justify-between text-xs text-muted">
                                    <span>Score</span>
                                    <span class="font-semibold text-ink">{{ formRaw.no[point.order - 1] || 0 }}</span>
                                </div>
                                <VueSlider
                                    v-model="formRaw.no[point.order - 1]"
                                    v-bind="options"
                                    class="mt-2"
                                    @change="processPoint(point.order, point.id, formRaw.no[point.order - 1])"
                                />
                            </div>
                            <div v-else class="mt-3 flex items-center gap-4 text-sm">
                                <label class="flex items-center gap-2">
                                    <input
                                        v-model="formRaw.no[point.order - 1]"
                                        type="radio"
                                        :value="1"
                                        class="h-4 w-4 rounded border border-border"
                                        @change="processPoint(point.order, point.id, formRaw.no[point.order - 1])"
                                    />
                                    <span>Ya</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input
                                        v-model="formRaw.no[point.order - 1]"
                                        type="radio"
                                        :value="0"
                                        class="h-4 w-4 rounded border border-border"
                                        @change="processPoint(point.order, point.id, formRaw.no[point.order - 1])"
                                    />
                                    <span>Tidak</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="dataDetail.task && dataDetail.task.desc !== 'chief'"
                    class="grid gap-2"
                >
                    <label class="text-sm text-muted">Catatan</label>
                    <textarea
                        v-model="form.note"
                        rows="4"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    ></textarea>
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
import VueSlider from 'vue-slider-component';
import 'vue-slider-component/theme/default.css';
import Repository from '../../repository';
import Swal from 'sweetalert2';

export default {
    components: {
        Loading,
        VueSlider,
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
                note: '',
            },
            formRaw: {
                no: [],
            },
            options: {
                dotSize: 18,
                min: 78,
                max: 100,
                interval: 2,
                marks: true,
            },
        };
    },
    computed: {
        points() {
            return Array.isArray(this.dataDetail.stase_task_log_point)
                ? this.dataDetail.stase_task_log_point
                : [];
        },
        leftPoints() {
            const mid = Math.ceil(this.points.length / 2);
            return this.points.slice(0, mid);
        },
        rightPoints() {
            const mid = Math.ceil(this.points.length / 2);
            return this.points.slice(mid);
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
                    this.form.note = payload ? payload.note : '';
                    this.form.stase_task_log_id = payload ? payload.id : '';

                    const scores = [];
                    this.form.no = [];

                    this.points.forEach((point) => {
                        const index = point.order ? point.order - 1 : 0;
                        const value = point.score === null || point.score === undefined ? 0 : point.score;
                        scores[index] = value;
                        this.form.no[point.order] = `${point.id},${value}`;
                    });

                    this.formRaw.no = scores;
                })
                .catch(() => {
                    this.dataDetail = {};
                    this.formRaw.no = [];
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
                    this.$router.push('/blu/dashboard-lecture/scoring');
                })
                .catch(() => {
                    this.errorMessage = 'Failed to save score.';
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        processPoint(order, id, value) {
            this.form.no[order] = `${id},${value}`;
        },
    },
};
</script>
