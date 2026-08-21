<template>
    <div class="grid gap-6">
        <header class="print:hidden flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Lecture Management</div>
                <h1 class="text-2xl font-semibold text-ink">
                    Scoring History{{ lecture ? ' — ' + lecture.name : '' }}
                </h1>
            </div>
            <div class="flex items-center gap-2">
                <router-link class="rounded-xl border border-border px-4 py-2 text-sm text-muted" to="/blu/lectures">
                    Back
                </router-link>
                <button
                    class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                    type="button"
                    @click="printPage"
                >
                    Print
                </button>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
                    type="button"
                    :disabled="exporting"
                    @click="exportExcel"
                >
                    {{ exporting ? 'Exporting...' : 'Export Excel' }}
                </button>
            </div>
        </header>

        <div class="hidden print:block mb-2">
            <h1 class="text-xl font-semibold">Scoring History — {{ lecture ? lecture.name : '' }}</h1>
            <div class="text-xs text-muted">
                {{ filters.date_from || 'Awal' }} &ndash; {{ filters.date_to || 'Sekarang' }}
                <span v-if="selectedTaskName"> • Task: {{ selectedTaskName }}</span>
            </div>
        </div>

        <section class="print:hidden rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-end gap-3">
                <div class="min-w-[160px]">
                    <label class="text-xs text-muted">Date Start</label>
                    <input
                        v-model="filters.date_from"
                        type="date"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="min-w-[160px]">
                    <label class="text-xs text-muted">Date End</label>
                    <input
                        v-model="filters.date_to"
                        type="date"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="min-w-[220px]">
                    <label class="text-xs text-muted">Task</label>
                    <select
                        v-model="filters.task_id"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">All</option>
                        <option v-for="task in taskOptions" :key="task.id" :value="task.id">
                            {{ task.name }}
                        </option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button
                        class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                        type="button"
                        @click="applyFilter"
                    >
                        Search
                    </button>
                    <button
                        class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                        type="button"
                        @click="resetFilter"
                    >
                        Reset
                    </button>
                </div>
            </div>
        </section>

        <section class="relative rounded-2xl border border-border bg-panel">
            <Loading :active="loading" :is-full-page="false" />
            <div class="print:hidden flex items-center justify-between border-b border-border px-5 py-4">
                <div class="font-semibold">Scoring Records</div>
                <div class="text-xs text-muted" v-if="pagination.total">
                    {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }}
                </div>
            </div>
            <div v-if="errorMessage" class="print:hidden border-b border-rose-100 bg-rose-50 px-5 py-3 text-xs text-rose-600">
                {{ errorMessage }}
            </div>
            <div v-if="!loading && rows.length === 0" class="px-5 py-6 text-sm text-muted">
                No scoring records found.
            </div>

            <div v-if="rows.length" class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-[11px] uppercase tracking-[0.2em] text-muted">
                        <tr>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Student</th>
                            <th class="px-5 py-3">Stase</th>
                            <th class="px-5 py-3">Task</th>
                            <th class="px-5 py-3 text-center">Score</th>
                            <th class="px-5 py-3 text-center">Symbol</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="row in rows" :key="row.id">
                            <td class="px-5 py-3 text-ink">{{ formatDate(row.date) }}</td>
                            <td class="px-5 py-3 text-ink">{{ row.student ? row.student.name : '-' }}</td>
                            <td class="px-5 py-3 text-muted">{{ staseName(row) }}</td>
                            <td class="px-5 py-3 text-muted">{{ taskName(row) }}</td>
                            <td class="px-5 py-3 text-center font-semibold text-ink">{{ row.point_average ?? '-' }}</td>
                            <td class="px-5 py-3 text-center text-muted">{{ row.symbol || '-' }}</td>
                            <td class="px-5 py-3 text-muted">{{ row.status || '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="print:hidden flex items-center justify-between border-t border-border px-5 py-4 text-xs text-muted">
                <button
                    class="rounded-lg border border-border px-3 py-1.5"
                    type="button"
                    :disabled="pagination.current_page <= 1"
                    @click="changePage(pagination.current_page - 1)"
                >
                    Prev
                </button>
                <div>Page {{ pagination.current_page || 1 }} / {{ pagination.last_page || 1 }}</div>
                <button
                    class="rounded-lg border border-border px-3 py-1.5"
                    type="button"
                    :disabled="pagination.current_page >= pagination.last_page"
                    @click="changePage(pagination.current_page + 1)"
                >
                    Next
                </button>
            </div>
        </section>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Repository from '../../repository';
import persistFilters from '../../mixins/persistFilters';

export default {
    components: {
        Loading,
    },
    mixins: [persistFilters('lectures/scoring')],
    data() {
        return {
            baseUrl: '/api/stase-task-logs',
            lecture: null,
            rows: [],
            pagination: {},
            taskOptions: [],
            filters: {
                date_from: '',
                date_to: '',
                task_id: '',
                page: 1,
            },
            loading: false,
            exporting: false,
            errorMessage: '',
        };
    },
    computed: {
        lectureId() {
            return this.$route.params.lecture_id;
        },
        selectedTaskName() {
            if (!this.filters.task_id) {
                return '';
            }
            const task = this.taskOptions.find((item) => String(item.id) === String(this.filters.task_id));
            return task ? task.name : '';
        },
    },
    created() {
        this.fetchLecture();
        this.fetchTaskOptions();
        this.fetchData();
    },
    methods: {
        fetchLecture() {
            return Repository.get(`/api/lectures/${this.lectureId}`)
                .then((response) => {
                    this.lecture = response && response.data ? response.data.result : null;
                })
                .catch(() => {
                    this.lecture = null;
                });
        },
        fetchTaskOptions() {
            return Repository.get('/api/task-list')
                .then((response) => {
                    const data = response && response.data ? response.data.result : null;
                    this.taskOptions = Array.isArray(data) ? data : [];
                })
                .catch(() => {
                    this.taskOptions = [];
                });
        },
        exportParams() {
            return {
                lecture_id: this.lectureId,
                date_from: this.filters.date_from || undefined,
                date_to: this.filters.date_to || undefined,
                task_id: this.filters.task_id || undefined,
            };
        },
        fetchData() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(this.baseUrl, {
                params: { ...this.exportParams(), page: this.filters.page, per_page: 20 },
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.rows = result && Array.isArray(result.data) ? result.data : [];
                    this.pagination = result || {};
                })
                .catch(() => {
                    this.rows = [];
                    this.pagination = {};
                    this.errorMessage = 'Failed to load scoring history.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        staseName(row) {
            return row.stase_task && row.stase_task.stase ? row.stase_task.stase.name : '-';
        },
        taskName(row) {
            if (!row.stase_task) {
                return '-';
            }
            if (row.stase_task.task) {
                return row.stase_task.task.name;
            }
            return row.stase_task.name || '-';
        },
        formatDate(value) {
            if (!value) {
                return '-';
            }
            const date = new Date(String(value).replace(' ', 'T'));
            if (Number.isNaN(date.getTime())) {
                return String(value).slice(0, 10);
            }
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
            });
        },
        applyFilter() {
            this.filters.page = 1;
            this.fetchData();
        },
        resetFilter() {
            this.filters.date_from = '';
            this.filters.date_to = '';
            this.filters.task_id = '';
            this.filters.page = 1;
            this.fetchData();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchData();
        },
        printPage() {
            window.print();
        },
        exportExcel() {
            this.exporting = true;
            this.errorMessage = '';

            return Repository.get('/api/stase-task-logs-export', {
                params: this.exportParams(),
                responseType: 'blob',
            })
                .then((response) => {
                    const blob = new Blob([response.data], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    });
                    const url = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    const lectureName = this.lecture ? this.lecture.name : 'Lecture';
                    link.href = url;
                    link.download = `Scoring History - ${lectureName}.xlsx`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);
                })
                .catch(() => {
                    this.errorMessage = 'Failed to export Excel.';
                })
                .finally(() => {
                    this.exporting = false;
                });
        },
    },
};
</script>
