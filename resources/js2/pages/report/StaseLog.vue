<template>
    <div class="grid gap-6 xl:grid-cols-[340px_minmax(0,1fr)]">
        <aside class="xl:sticky xl:top-7 xl:self-start">
            <section class="rounded-2xl border border-border bg-panel p-5">
                <div class="mb-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-xs uppercase tracking-[0.2em] text-muted">Report Filter</div>
                            <h1 class="mt-2 text-xl font-semibold text-ink">Stase Log</h1>
                        </div>
                        <button
                            class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                            type="button"
                            :disabled="loadingReport"
                            @click="fetchReport"
                        >
                            {{ loadingReport ? 'Loading...' : 'View Report' }}
                        </button>
                    </div>
                </div>

                <div class="grid gap-5">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Date</span>
                        <input
                            v-model="filters.date"
                            type="date"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>

                    <div class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-ink">Students</span>
                            <span class="text-xs text-muted">{{ selectedStudents.length }} selected</span>
                        </div>
                        <div class="max-h-80 overflow-auto rounded-xl border border-border bg-white p-2">
                            <label
                                v-for="student in students"
                                :key="student.id"
                                class="flex cursor-pointer items-start gap-3 rounded-lg px-3 py-2 hover:bg-slate-50"
                            >
                                <input
                                    v-model="selectedStudents"
                                    type="checkbox"
                                    :value="student.id"
                                    class="mt-0.5 h-4 w-4 rounded border-border"
                                />
                                <span class="min-w-0">
                                    <span class="block text-sm text-ink">{{ student.name }}</span>
                                    <span class="block text-xs text-muted">
                                        Year: {{ student.year || '-' }}
                                    </span>
                                </span>
                            </label>
                            <div v-if="!loadingStudents && students.length === 0" class="px-3 py-4 text-sm text-muted">
                                No active students found.
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-ink">Stases</span>
                            <div class="flex items-center gap-3">
                                <label class="flex cursor-pointer items-center gap-2 text-xs text-muted">
                                    <input
                                        :checked="allStasesSelected"
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-border"
                                        @change="toggleAllStases($event.target.checked)"
                                    />
                                    <span>Select all</span>
                                </label>
                                <span class="text-xs text-muted">{{ selectedStases.length }} selected</span>
                            </div>
                        </div>
                        <div class="max-h-80 overflow-auto rounded-xl border border-border bg-white p-2">
                            <label
                                v-for="stase in stases"
                                :key="stase.id"
                                class="flex cursor-pointer items-start gap-3 rounded-lg px-3 py-2 hover:bg-slate-50"
                            >
                                <input
                                    v-model="selectedStases"
                                    type="checkbox"
                                    :value="stase.id"
                                    class="mt-0.5 h-4 w-4 rounded border-border"
                                />
                                <span class="min-w-0">
                                    <span class="block text-sm text-ink">{{ stase.name }}</span>
                                    <span class="block truncate text-xs text-muted">
                                        {{ stase.desc || 'No description' }}
                                    </span>
                                </span>
                            </label>
                            <div v-if="!loadingStases && stases.length === 0" class="px-3 py-4 text-sm text-muted">
                                No stases found.
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </aside>

        <section class="rounded-2xl border border-border bg-panel p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <div class="text-xs uppercase tracking-[0.2em] text-muted">Preview</div>
                    <h2 class="mt-2 text-xl font-semibold text-ink">Report Workspace</h2>
                </div>
                <div class="text-xs text-muted">
                    {{ selectedStudents.length }} students • {{ selectedStases.length }} stases
                </div>
            </div>

            <div v-if="reportError" class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600">
                {{ reportError }}
            </div>

            <div v-if="!loadingReport && reportItems.length === 0" class="mt-6 rounded-2xl border border-dashed border-border bg-white/60 px-5 py-10 text-center text-sm text-muted">
                No stase logs found for the selected filters.
            </div>

            <div v-else class="mt-6 grid gap-3">
                <div
                    v-for="item in reportItems"
                    :key="item.id"
                    class="rounded-2xl border border-border bg-white px-5 py-4"
                >
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <div class="font-semibold text-ink">{{ item.student_name || 'Unknown Student' }}</div>
                            <div class="text-xs text-muted">{{ item.student_email || '-' }}</div>
                        </div>
                        <div class="text-right text-xs text-muted">
                            <div>{{ item.stase_name || 'Unknown Stase' }}</div>
                            <div>{{ item.stase_alias || '-' }}</div>
                        </div>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-3 text-xs text-muted">
                        <span>Start: {{ item.start_date || '-' }}</span>
                        <span>End: {{ item.end_date || '-' }}</span>
                        <span v-if="item.student_status">Status: {{ item.student_status }}</span>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import Repository from '../../repository';

export default {
    computed: {
        allStasesSelected() {
            return this.stases.length > 0 && this.selectedStases.length === this.stases.length;
        },
    },
    data() {
        return {
            students: [],
            stases: [],
            loadingStudents: false,
            loadingStases: false,
            loadingReport: false,
            filters: {
                date: this.getTodayDate(),
            },
            selectedStudents: [],
            selectedStases: [],
            reportItems: [],
            reportError: '',
        };
    },
    created() {
        this.fetchStudents();
        this.fetchStases();
    },
    methods: {
        fetchStudents() {
            this.loadingStudents = true;

            return Repository.get('/api/student-list')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.students = Array.isArray(result) ? result : [];
                })
                .catch(() => {
                    this.students = [];
                })
                .finally(() => {
                    this.loadingStudents = false;
                });
        },
        fetchStases() {
            this.loadingStases = true;

            return Repository.get('/api/stase-list')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.stases = Array.isArray(result) ? result : [];
                    this.checkAllStases();
                })
                .catch(() => {
                    this.stases = [];
                    this.selectedStases = [];
                })
                .finally(() => {
                    this.loadingStases = false;
                });
        },
        checkAllStases() {
            this.selectedStases = this.stases.map((stase) => stase.id);
        },
        toggleAllStases(checked) {
            this.selectedStases = checked ? this.stases.map((stase) => stase.id) : [];
        },
        getTodayDate() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        fetchReport() {
            this.loadingReport = true;
            this.reportError = '';

            return Repository.get('/api/stase-logs', {
                params: {
                    date: this.filters.date || null,
                    student_ids: this.selectedStudents,
                    stase_ids: this.selectedStases,
                    per_page: 1000,
                },
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.reportItems = result && Array.isArray(result.data) ? result.data : [];
                })
                .catch(() => {
                    this.reportItems = [];
                    this.reportError = 'Failed to load stase log report.';
                })
                .finally(() => {
                    this.loadingReport = false;
                });
        },
    },
};
</script>
