<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Presence Resume</div>
                <h1 class="text-2xl font-semibold text-ink">Monthly Presences</h1>
            </div>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex-1 min-w-[180px]">
                    <label class="text-xs text-muted">Month</label>
                    <input
                        v-model="filters.date"
                        type="month"
                        @keyup.enter="applyFilter"
                        @change="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="text-xs text-muted">Type</label>
                    <select
                        v-model="filters.key"
                        @change="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="laporan jaga">Laporan Jaga</option>
                        <option value="presensi">Presensi Harian</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[180px] text-sm text-muted">
                    <div class="mt-6">Total: {{ query.activity_count || 0 }}</div>
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

        <section class="relative rounded-2xl border border-border bg-panel p-5">
            <Loading :active="loading" :is-full-page="false" />
            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="text-left text-muted">
                                <th class="w-10">No</th>
                                <th>Name</th>
                                <th class="w-2/5 text-right">Ilmiah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(student, index) in leftStudents"
                                :key="student.id"
                                class="border-b border-border/60"
                            >
                                <td class="py-2">{{ index + 1 }}</td>
                                <td class="py-2">{{ student.name }}</td>
                                <td class="py-2 text-right">
                                    {{ student.lapjag }}
                                    <div class="mt-2 h-1 w-full rounded bg-slate-200">
                                        <div
                                            class="h-1 rounded"
                                            :class="progressClass(student)"
                                            :style="{ width: progressPercent(student) }"
                                        ></div>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!loading && leftStudents.length === 0">
                                <td colspan="3" class="py-4 text-center text-sm text-muted">No data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="text-left text-muted">
                                <th class="w-10">No</th>
                                <th>Name</th>
                                <th class="w-2/5 text-right">Ilmiah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(student, index) in rightStudents"
                                :key="student.id"
                                class="border-b border-border/60"
                            >
                                <td class="py-2">{{ index + leftStudents.length + 1 }}</td>
                                <td class="py-2">{{ student.name }}</td>
                                <td class="py-2 text-right">
                                    {{ student.lapjag }}
                                    <div class="mt-2 h-1 w-full rounded bg-slate-200">
                                        <div
                                            class="h-1 rounded"
                                            :class="progressClass(student)"
                                            :style="{ width: progressPercent(student) }"
                                        ></div>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!loading && rightStudents.length === 0 && leftStudents.length === 0">
                                <td colspan="3" class="py-4 text-center text-sm text-muted">No data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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
            baseUrl: '/api/presences/monthly',
            students: [],
            query: {
                date: '',
                key: 'laporan jaga',
                activity_count: 0,
            },
            filters: {
                date: '',
                key: 'laporan jaga',
            },
            loading: false,
        };
    },
    computed: {
        leftStudents() {
            const half = Math.ceil(this.students.length / 2);
            return this.students.slice(0, half);
        },
        rightStudents() {
            const half = Math.ceil(this.students.length / 2);
            return this.students.slice(half);
        },
    },
    created() {
        this.filters.date = this.getCurrentMonth();
        this.fetchMonthlyPresences();
    },
    methods: {
        getCurrentMonth() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            return `${year}-${month}`;
        },
        progressPercent(student) {
            const total = Number(this.query.activity_count || 0);
            if (!total) {
                return '0%';
            }
            const value = Number(student.lapjag || 0);
            const percent = Math.min(100, Math.round((value / total) * 100));
            return `${percent}%`;
        },
        progressClass(student) {
            const total = Number(this.query.activity_count || 0);
            if (!total) {
                return 'bg-slate-300';
            }
            const value = Number(student.lapjag || 0);
            const ratio = value / total;
            return ratio > 0.79 ? 'bg-emerald-500' : 'bg-amber-500';
        },
        fetchMonthlyPresences() {
            this.loading = true;

            return Repository.get(this.baseUrl, {
                params: this.filters,
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const query = response && response.data ? response.data.query : null;
                    this.students = Array.isArray(result) ? result : [];
                    this.query = query || {
                        date: this.filters.date,
                        key: this.filters.key,
                        activity_count: 0,
                    };
                })
                .catch(() => {
                    this.students = [];
                    this.query = {
                        date: this.filters.date,
                        key: this.filters.key,
                        activity_count: 0,
                    };
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        applyFilter() {
            this.fetchMonthlyPresences();
        },
        resetFilter() {
            this.filters.date = this.getCurrentMonth();
            this.filters.key = 'laporan jaga';
            this.fetchMonthlyPresences();
        },
    },
};
</script>
