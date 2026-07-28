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

        <div class="grid gap-6">
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
                            <div class="flex items-start gap-3">
                                <div class="text-right text-xs text-muted">
                                    <div>{{ item.stase_name || 'Unknown Stase' }}</div>
                                    <div>{{ item.stase_alias || '-' }}</div>
                                </div>
                                <div class="relative action-dropdown">
                                    <button
                                        class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                                        type="button"
                                        @click.stop="toggleActionMenu(item.id)"
                                    >
                                        Actions
                                    </button>
                                    <div
                                        v-if="actionMenuOpenId === item.id"
                                        class="absolute right-0 z-10 mt-2 w-36 rounded-xl border border-border bg-white p-1 shadow-lg"
                                    >
                                        <button
                                            class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                            type="button"
                                            @click="openActionModal('presence', item)"
                                        >
                                            Presence
                                        </button>
                                        <button
                                            class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                            type="button"
                                            @click="openActionModal('task', item)"
                                        >
                                            Task
                                        </button>
                                        <button
                                            class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                            type="button"
                                            @click="openActionModal('logbook', item)"
                                        >
                                            Logbook
                                        </button>
                                    </div>
                                </div>
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
                        <section class="rounded-2xl border border-border bg-panel p-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-xs uppercase tracking-[0.2em] text-muted">Attention</div>
                        <h2 class="mt-2 text-xl font-semibold text-ink">Students Without Stase</h2>
                    </div>
                    <div class="text-xs text-muted">
                        {{ missingStaseStudents.length }} students
                    </div>
                </div>

                <div
                    v-if="missingStaseError"
                    class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600"
                >
                    {{ missingStaseError }}
                </div>

                <div
                    v-else-if="loadingMissingStase"
                    class="mt-6 rounded-2xl border border-dashed border-border bg-white/60 px-5 py-10 text-center text-sm text-muted"
                >
                    Loading students without stase...
                </div>

                <div
                    v-else-if="missingStaseStudents.length === 0"
                    class="mt-6 rounded-2xl border border-dashed border-border bg-white/60 px-5 py-10 text-center text-sm text-muted"
                >
                    All active students already have an active stase.
                </div>

                <div v-else class="mt-6 grid gap-3 md:grid-cols-2">
                    <div
                        v-for="student in missingStaseStudents"
                        :key="student.id"
                        class="rounded-2xl border border-amber-200 bg-amber-50/70 px-5 py-4"
                    >
                        <div class="font-semibold text-ink">{{ student.name || 'Unknown Student' }}</div>
                        <div class="mt-3 flex flex-wrap gap-3 text-xs text-muted">
                            <span>Year: {{ student.year || '-' }}</span>
                            <span>Status: {{ student.status || '-' }}</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <Modal
            :open="actionModalOpen"
            :title="actionModalTitle"
            :eyebrow="actionModalEyebrow"
            size="xxxl"
            @close="closeActionModal"
        >
            <div v-if="selectedReportItem" class="grid gap-4 text-sm">
                <div class="rounded-2xl border border-border bg-slate-50 px-4 py-4">
                    <div class="font-semibold text-ink">
                        {{ selectedReportItem.student_name || 'Unknown Student' }}
                    </div>
                    <div class="mt-1 text-xs text-muted">
                        {{ selectedReportItem.student_email || '-' }}
                    </div>
                    <div class="mt-3 flex flex-wrap gap-3 text-xs text-muted">
                        <span>Stase: {{ selectedReportItem.stase_name || '-' }}</span>
                        <span>Alias: {{ selectedReportItem.stase_alias || '-' }}</span>
                        <span>Start: {{ selectedReportItem.start_date || '-' }}</span>
                        <span>End: {{ selectedReportItem.end_date || '-' }}</span>
                    </div>
                </div>

                <div class="rounded-2xl border border-dashed border-border bg-white px-4 py-6 text-sm text-muted">
                    <template v-if="actionModalType === 'presence'">
                        <div class="relative">
                            <Loading :active="presenceSummaryLoading" :is-full-page="false" />

                            <div class="grid gap-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-semibold text-ink">
                                            {{
                                                (presenceSummary.resident && presenceSummary.resident.name)
                                                    || selectedReportItem.student_name
                                                    || 'Student'
                                            }}
                                        </div>
                                        <div class="text-xs text-muted">
                                            Student ID: {{ selectedReportItem.student_id || '-' }}
                                        </div>
                                    </div>
                                    <div class="text-right text-xs text-muted">
                                        <div>Start: {{ presenceSummary.start_date || selectedReportItem.start_date || '-' }}</div>
                                        <div>End: {{ presenceSummary.end_date || selectedReportItem.end_date || '-' }}</div>
                                    </div>
                                </div>

                                <div
                                    v-if="presenceSummaryError"
                                    class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600"
                                >
                                    {{ presenceSummaryError }}
                                </div>

                                <div
                                    v-else-if="!presenceSummaryLoading && (!presenceSummary.data || presenceSummary.data.length === 0)"
                                    class="rounded-xl border border-border bg-white px-4 py-6 text-sm text-muted"
                                >
                                    No presence data found for this stase date range.
                                </div>

                                <div v-else class="grid gap-4">
                                    <div
                                        v-for="(week, weekIndex) in presenceSummary.data || []"
                                        :key="`presence-week-${weekIndex}`"
                                        class="grid gap-2"
                                    >
                                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-muted">
                                            Week {{ weekIndex + 1 }}
                                        </div>
                                        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                                            <div
                                                v-for="(day, dayIndex) in week"
                                                :key="`${day.date}-${dayIndex}`"
                                                class="grid min-h-[150px] gap-2 rounded-2xl border border-border bg-slate-50 p-3"
                                            >
                                                <div class="text-xs font-semibold text-muted">
                                                    {{ formatDay(day.date) }}
                                                </div>

                                                <div v-if="day.presence" class="text-xs text-emerald-700">
                                                    <div class="font-semibold">Checkin</div>
                                                    <div>{{ formatTime(day.presence.checkin) }}</div>
                                                    <div v-if="day.presence.checkout" class="mt-1 text-slate-600">
                                                        Checkout: {{ formatTime(day.presence.checkout) }}
                                                    </div>
                                                    <div v-if="day.presence.duration" class="mt-1 text-slate-600">
                                                        Duration: {{ day.presence.duration }}
                                                    </div>
                                                </div>
                                                <div v-else class="text-xs text-muted">
                                                    No checkin
                                                </div>

                                                <div class="grid gap-1 text-xs">
                                                    <div
                                                        v-for="(activity, activityIndex) in day.activities || []"
                                                        :key="`presence-activity-${activityIndex}-${day.date}`"
                                                        class="rounded-lg bg-white px-2 py-1 text-slate-700"
                                                        :title="activityName(activity)"
                                                    >
                                                        {{ truncate(activityName(activity), 28) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template v-else-if="actionModalType === 'task'">
                        Task detail workspace for this stase log.
                    </template>
                    <template v-else-if="actionModalType === 'logbook'">
                        Logbook detail workspace for this stase log.
                    </template>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Modal from '../../components/Modal.vue';
import Repository from '../../repository';
import persistFilters from '../../mixins/persistFilters';

export default {
    components: {
        Loading,
        Modal,
    },
    mixins: [persistFilters('report/stase-log')],
    computed: {
        allStasesSelected() {
            return this.stases.length > 0 && this.selectedStases.length === this.stases.length;
        },
        actionModalTitle() {
            if (this.actionModalType === 'presence') {
                return 'Presence';
            }

            if (this.actionModalType === 'task') {
                return 'Task';
            }

            if (this.actionModalType === 'logbook') {
                return 'Logbook';
            }

            return 'Detail';
        },
        actionModalEyebrow() {
            return this.selectedReportItem && this.selectedReportItem.stase_name
                ? this.selectedReportItem.stase_name
                : 'Report action';
        },
    },
    data() {
        return {
            students: [],
            stases: [],
            loadingStudents: false,
            loadingStases: false,
            loadingReport: false,
            loadingMissingStase: false,
            filters: {
                date: this.getTodayDate(),
            },
            selectedStudents: [],
            selectedStases: [],
            missingStaseStudents: [],
            missingStaseError: '',
            actionMenuOpenId: null,
            actionModalOpen: false,
            actionModalType: '',
            selectedReportItem: null,
            presenceSummaryLoading: false,
            presenceSummaryError: '',
            presenceSummary: {
                data: [],
                resident: null,
                period: '',
                start_date: '',
                end_date: '',
            },
            reportItems: [],
            reportError: '',
        };
    },
    created() {
        this.fetchStudents();
        this.fetchStases();
        this.fetchMissingStaseStudents();
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

            return Repository.get('/api/stase-list-all')
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
        fetchMissingStaseStudents() {
            this.loadingMissingStase = true;
            this.missingStaseError = '';

            return Repository.get('/api/stase-log-check')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.missingStaseStudents = Array.isArray(result) ? result : [];
                })
                .catch(() => {
                    this.missingStaseStudents = [];
                    this.missingStaseError = 'Failed to load students without stase.';
                })
                .finally(() => {
                    this.loadingMissingStase = false;
                });
        },
        checkAllStases() {
            this.selectedStases = this.stases.map((stase) => stase.id);
        },
        toggleAllStases(checked) {
            this.selectedStases = checked ? this.stases.map((stase) => stase.id) : [];
        },
        toggleActionMenu(reportId) {
            this.actionMenuOpenId = this.actionMenuOpenId === reportId ? null : reportId;
        },
        openActionModal(type, item) {
            this.actionMenuOpenId = null;
            this.actionModalType = type;
            this.selectedReportItem = item;
            this.actionModalOpen = true;

            if (type === 'presence') {
                this.fetchPresenceSummary();
            }
        },
        closeActionModal() {
            this.actionModalOpen = false;
            this.actionModalType = '';
            this.selectedReportItem = null;
            this.presenceSummaryLoading = false;
            this.presenceSummaryError = '';
            this.presenceSummary = {
                data: [],
                resident: null,
                period: '',
                start_date: '',
                end_date: '',
            };
        },
        fetchPresenceSummary() {
            if (!this.selectedReportItem || !this.selectedReportItem.student_id) {
                this.presenceSummaryError = 'Student ID is missing.';
                this.presenceSummary = { data: [], resident: null, period: '', start_date: '', end_date: '' };
                return Promise.resolve();
            }

            this.presenceSummaryLoading = true;
            this.presenceSummaryError = '';

            return Repository.get(`/api/presences/student/${this.selectedReportItem.student_id}`, {
                params: {
                    start_date: this.selectedReportItem.start_date || null,
                    end_date: this.selectedReportItem.end_date || null,
                },
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.presenceSummary = result || { data: [], resident: null, period: '', start_date: '', end_date: '' };
                })
                .catch(() => {
                    this.presenceSummary = { data: [], resident: null, period: '', start_date: '', end_date: '' };
                    this.presenceSummaryError = 'Failed to load student presence summary.';
                })
                .finally(() => {
                    this.presenceSummaryLoading = false;
                });
        },
        formatDay(dateString) {
            if (!dateString) {
                return '-';
            }

            const date = new Date(dateString);
            if (Number.isNaN(date.getTime())) {
                return dateString;
            }

            return date.toLocaleDateString('id-ID', {
                weekday: 'short',
                day: '2-digit',
                month: 'short',
                year: 'numeric',
            });
        },
        formatTime(dateTimeString) {
            if (!dateTimeString) {
                return '-';
            }

            const date = new Date(dateTimeString);
            if (Number.isNaN(date.getTime())) {
                return dateTimeString;
            }

            return date.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
            });
        },
        activityName(activityStudent) {
            return activityStudent && activityStudent.activity && activityStudent.activity.name
                ? activityStudent.activity.name
                : '-';
        },
        truncate(value, maxLength) {
            if (!value || value.length <= maxLength) {
                return value;
            }

            return `${value.slice(0, maxLength - 1)}...`;
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
