<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Score Management</div>
                <h1 class="text-2xl font-semibold text-ink">Scores</h1>
            </div>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="text-xs text-muted">Keyword</label>
                    <input
                        v-model.trim="filters.keyword"
                        type="text"
                        @keyup.enter="applyFilter"
                        placeholder="Search student, lecture, or task..."
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
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
            <div class="flex items-center justify-between border-b border-border px-5 py-4">
                <div class="font-semibold">Stase Task Logs</div>
                <div class="text-xs text-muted" v-if="pagination.total">
                    {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }}
                </div>
            </div>
            <div
                v-if="errorMessage"
                class="border-b border-rose-100 bg-rose-50 px-5 py-3 text-xs text-rose-600"
            >
                {{ errorMessage }}
            </div>
            <div class="divide-y divide-border">
                <div v-if="!loading && logs.length === 0" class="px-5 py-6 text-sm text-muted">
                    No score logs found.
                </div>
                <div
                    v-for="(log, index) in logs"
                    :key="log.id"
                    class="flex flex-wrap items-center gap-3 px-5 py-4"
                >
                    <div class="w-8 text-sm font-semibold text-muted">
                        {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold text-ink">
                            {{ log.student ? log.student.name : 'Student' }}
                        </div>
                        <div class="text-xs text-muted">
                            <span v-if="log.stase_task">{{ log.stase_task.name }}</span>
                            <span v-if="log.title">• {{ log.title }}</span>
                        </div>
                        <div class="text-xs text-muted">
                            <span v-if="log.lecture">Lecture: {{ log.lecture.name }}</span>
                            <span v-if="log.date">• {{ formatDate(log.date) }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                            {{ log.point_average || 0 }}
                        </span>
                        <span class="text-xs text-muted">{{ log.status || '-' }}</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between border-t border-border px-5 py-4 text-xs text-muted">
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

export default {
    components: {
        Loading,
    },
    data() {
        return {
            baseUrl: '/api/stase-task-logs',
            logs: [],
            pagination: {},
            filters: {
                keyword: '',
                page: 1,
            },
            loading: false,
            errorMessage: '',
        };
    },
    created() {
        this.fetchLogs();
    },
    methods: {
        fetchLogs() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(this.baseUrl, {
                params: this.filters,
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];
                    this.logs = data;
                    this.pagination = result || {};
                })
                .catch(() => {
                    this.logs = [];
                    this.pagination = {};
                    this.errorMessage = 'Failed to load score logs.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        applyFilter() {
            this.filters.page = 1;
            this.fetchLogs();
        },
        resetFilter() {
            this.filters.keyword = '';
            this.filters.page = 1;
            this.fetchLogs();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchLogs();
        },
        formatDate(value) {
            if (!value) {
                return '-';
            }
            return String(value).slice(0, 10);
        },
    },
};
</script>
