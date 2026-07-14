<template>
    <div class="grid gap-6">
        <header>
            <div class="text-xs uppercase tracking-[0.2em] text-muted">Student Dashboard</div>
            <h1 class="text-2xl font-semibold text-ink">Checklist</h1>
        </header>

        <!-- Overall progress -->
        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div class="text-xs uppercase tracking-[0.2em] text-muted">Overall Progress</div>
                    <div class="mt-1 text-3xl font-semibold text-ink">
                        {{ summary.percentage !== null ? summary.percentage + '%' : '—' }}
                    </div>
                    <div class="mt-1 text-xs text-muted">
                        {{ summary.done }}/{{ summary.total }} tasks done · {{ summary.stases }} stase(s) taken
                    </div>
                </div>
                <div class="w-full sm:w-72">
                    <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                        <div
                            class="h-full rounded-full transition-all"
                            :class="barClass(summary.percentage)"
                            :style="{ width: (summary.percentage || 0) + '%' }"
                        ></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Per-stase checklist -->
        <section class="relative min-w-0 rounded-2xl border border-border bg-panel">
            <Loading :active="loading" :is-full-page="false" />

            <div class="border-b border-border px-5 py-4 font-semibold">Taken Stases</div>

            <div v-if="!loading && stases.length === 0" class="px-5 py-6 text-sm text-muted">
                No taken stase yet.
            </div>

            <div v-else class="divide-y divide-border">
                <div v-for="stase in stases" :key="stase.stase_log_id">
                    <button
                        type="button"
                        class="flex w-full items-center gap-3 px-5 py-4 text-left transition-colors hover:bg-slate-50"
                        @click="toggle(stase.stase_log_id)"
                    >
                        <span
                            class="grid h-8 w-8 shrink-0 place-items-center rounded-lg text-xs font-bold"
                            :class="pillClass(stase.percentage)"
                        >
                            {{ stase.percentage !== null ? stase.percentage + '%' : '—' }}
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-semibold text-ink">{{ stase.stase_name }}</span>
                            </div>
                            <div v-if="stase.start_date || stase.end_date" class="mt-0.5 text-xs text-muted">
                                {{ formatDate(stase.start_date) }} &ndash; {{ formatDate(stase.end_date) }}
                            </div>
                        </div>

                        <div class="shrink-0 text-sm font-semibold text-muted">
                            {{ stase.done }}/{{ stase.total }}
                        </div>
                        <svg
                            class="h-4 w-4 shrink-0 text-muted transition-transform"
                            :class="isOpen(stase.stase_log_id) ? 'rotate-180' : ''"
                            viewBox="0 0 20 20" fill="currentColor"
                        >
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div v-if="isOpen(stase.stase_log_id)" class="px-5 pb-4">
                        <div v-if="stase.tasks.length === 0" class="py-2 text-sm text-muted">
                            No tasks configured for this stase.
                        </div>
                        <ul v-else class="divide-y divide-border rounded-xl border border-border">
                            <li
                                v-for="(task, taskIndex) in stase.tasks"
                                :key="task.stase_task_id || `attendance-${taskIndex}`"
                                class="flex items-center justify-between gap-3 px-4 py-3"
                            >
                                <div class="flex items-center gap-2.5">
                                    <span
                                        class="grid h-6 w-6 place-items-center rounded-full text-xs font-bold"
                                        :class="task.done ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-400'"
                                    >
                                        {{ task.done ? '✓' : '•' }}
                                    </span>
                                    <div>
                                        <div class="text-sm font-medium text-ink">{{ task.name }}</div>
                                        <div v-if="task.type === 'attendance'" class="text-xs text-muted">
                                            {{ task.present }}/{{ task.working_days }} working days
                                        </div>
                                        <div v-else-if="task.done && task.date" class="text-xs text-muted">
                                            {{ formatDate(task.date) }}
                                        </div>
                                    </div>
                                </div>
                                <span
                                    v-if="task.type === 'attendance'"
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="pillClass(task.percentage)"
                                >
                                    {{ task.percentage }}%
                                </span>
                                <span
                                    v-else-if="task.done"
                                    class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-600"
                                >
                                    {{ task.point_average }}
                                </span>
                                <span
                                    v-else
                                    class="rounded-full bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-400"
                                >
                                    Not yet
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import Repository from '../../repository';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
    components: {
        Loading,
    },
    data() {
        return {
            loading: false,
            summary: { done: 0, total: 0, percentage: null, stases: 0 },
            stases: [],
            openIds: {},
        };
    },
    created() {
        this.fetchData();
    },
    methods: {
        fetchData() {
            this.loading = true;

            return Repository.get('/api/student-checklist')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.summary = (result && result.summary) || { done: 0, total: 0, percentage: null, stases: 0 };
                    this.stases = result && Array.isArray(result.stases) ? result.stases : [];

                    // Expand the ongoing stase by default (first one if none ongoing).
                    const ongoing = this.stases.find((stase) => stase.ongoing);
                    const target = ongoing || this.stases[0];
                    this.openIds = target ? { [target.stase_log_id]: true } : {};
                })
                .catch(() => {
                    this.summary = { done: 0, total: 0, percentage: null, stases: 0 };
                    this.stases = [];
                    this.openIds = {};
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        toggle(id) {
            this.$set(this.openIds, id, !this.openIds[id]);
        },
        isOpen(id) {
            return Boolean(this.openIds[id]);
        },
        pillClass(value) {
            if (value === null || value === undefined) {
                return 'bg-slate-100 text-slate-400';
            }
            if (value >= 100) {
                return 'bg-emerald-100 text-emerald-700';
            }
            if (value >= 50) {
                return 'bg-amber-100 text-amber-700';
            }
            return 'bg-rose-100 text-rose-700';
        },
        barClass(value) {
            if (value >= 100) {
                return 'bg-emerald-500';
            }
            if (value >= 50) {
                return 'bg-amber-400';
            }
            return 'bg-rose-500';
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
    },
};
</script>
