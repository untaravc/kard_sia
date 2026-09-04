<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Student Management</div>
                <h1 class="text-2xl font-semibold text-ink">Monitoring</h1>
            </div>
            <button
                class="rounded-xl border border-border px-4 py-2 text-sm font-medium text-ink hover:bg-slate-50"
                type="button"
                @click="printMonitoring"
            >
                Print
            </button>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="text-xs text-muted">Name</label>
                    <input
                        v-model.trim="filters.name"
                        type="text"
                        placeholder="Search student name..."
                        @keyup.enter="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="flex-1 min-w-[180px]">
                    <label class="text-xs text-muted">Phase</label>
                    <select
                        v-model="filters.stase_section"
                        @change="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">All</option>
                        <option value="tahap_1">Tahap 1</option>
                        <option value="tahap_2">Tahap 2</option>
                        <option value="tahap_3">Tahap 3</option>
                        <option value="referat">Referat</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[180px]">
                    <label class="text-xs text-muted">Stase</label>
                    <select
                        v-model="filters.current_stase_id"
                        @change="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">All</option>
                        <option v-for="stase in allStases" :key="stase.id" :value="stase.id">
                            {{ stase.alias || stase.name }}
                        </option>
                    </select>
                </div>
                <div class="flex-1 min-w-[180px]">
                    <label class="text-xs text-muted">Year</label>
                    <select
                        v-model="filters.year"
                        @change="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">All</option>
                        <option v-for="year in yearOptions" :key="year" :value="year">
                            {{ year }}
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
            <div class="mt-4 flex flex-wrap items-center gap-4 text-xs text-muted">
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Complete</span>
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span> In progress (≥ 50%)</span>
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span> Behind (&lt; 50%)</span>
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span> No task</span>
                <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded ring-2 ring-primary ring-inset"></span> Current stase</span>
            </div>
        </section>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div class="text-xs uppercase tracking-[0.2em] text-muted">Overall Fulfilment</div>
                    <div class="mt-1 text-3xl font-semibold text-ink">
                        {{ overall.percentage !== null ? overall.percentage + '%' : '—' }}
                    </div>
                    <div class="mt-1 text-sm font-medium text-muted">
                        {{ overall.done }}/{{ overall.total }} tasks across {{ overall.students }} student(s) · taken stases only
                    </div>
                </div>
                <div class="w-full sm:w-72">
                    <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                        <div
                            class="h-full rounded-full transition-all"
                            :class="fulfilmentBarClass(overall.percentage)"
                            :style="{ width: (overall.percentage || 0) + '%' }"
                        ></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="relative min-w-0 overflow-hidden rounded-2xl border border-border bg-panel">
            <Loading :active="loading" :is-full-page="false" />
            <div class="flex items-center justify-between border-b border-border px-5 py-4">
                <div class="font-semibold">Student Monitoring</div>
                <div class="text-xs text-muted" v-if="pagination.total">
                    {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }}
                </div>
            </div>

            <div class="w-full overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-border text-left text-xs uppercase tracking-wide text-muted">
                            <th class="sticky left-0 z-30 w-14 bg-panel px-3 py-3">#</th>
                            <th class="sticky left-14 z-30 min-w-[160px] bg-panel px-3 py-3">Name</th>
                            <th class="min-w-[110px] px-4 py-3 text-center">Fulfilment</th>
                            <th
                                v-for="stase in stases"
                                :key="stase.id"
                                class="min-w-[84px] px-4 py-3 text-center"
                                :title="stase.name"
                            >
                                {{ stase.alias || stase.name }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-if="!loading && rows.length === 0">
                            <td :colspan="stases.length + 3" class="px-5 py-6 text-sm text-muted">
                                No students found.
                            </td>
                        </tr>
                        <tr v-for="(row, index) in rows" :key="row.id" class="group">
                            <td class="sticky left-0 z-20 w-14 bg-panel px-3 py-3 text-sm font-semibold text-muted group-hover:bg-slate-50">
                                {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                            </td>
                            <td class="sticky left-14 z-20 min-w-[160px] bg-panel px-3 py-3 group-hover:bg-slate-50">
                                <div class="font-semibold text-ink">{{ row.name }}</div>
                                <div v-if="row.year" class="text-xs text-muted">Year: {{ row.year }}</div>
                            </td>
                            <td class="px-4 py-3 text-center group-hover:bg-slate-50">
                                <span
                                    v-if="row.fulfilment !== null && row.fulfilment !== undefined"
                                    class="inline-flex min-w-[44px] justify-center rounded-full px-2 py-1 text-xs font-semibold"
                                    :class="fulfilmentClass(row.fulfilment)"
                                >
                                    {{ row.fulfilment }}%
                                </span>
                                <span v-else class="text-xs text-muted">&mdash;</span>
                            </td>
                            <td
                                v-for="stase in stases"
                                :key="stase.id"
                                class="px-4 py-3 text-center group-hover:bg-slate-50"
                            >
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg px-2 py-1 text-sm font-medium transition-colors"
                                    :class="[
                                        cellTextClass(cell(row, stase.id)),
                                        cell(row, stase.id).total > 0 ? 'cursor-pointer hover:bg-slate-100' : 'cursor-default',
                                        cell(row, stase.id).ongoing ? 'bg-primary/10 ring-2 ring-primary ring-inset' : '',
                                    ]"
                                    :disabled="cell(row, stase.id).total === 0"
                                    :title="cell(row, stase.id).ongoing ? 'Current stase' : ''"
                                    @click="openDetail(row, stase)"
                                >
                                    <span class="h-2.5 w-2.5 rounded-full" :class="cellDotClass(cell(row, stase.id))"></span>
                                    {{ cell(row, stase.id).done }}/{{ cell(row, stase.id).total }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
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

        <Modal
            :open="detailModalOpen"
            :title="detailTitle"
            eyebrow="Task detail"
            size="md"
            @close="closeDetail"
        >
            <div class="relative min-h-[120px]">
                <Loading :active="detailLoading" :is-full-page="false" />

                <div v-if="detail" class="grid gap-4">
                    <div class="flex items-center justify-between rounded-xl border border-border bg-slate-50 px-4 py-3 text-sm">
                        <span class="text-muted">{{ detail.student.name }}</span>
                        <span class="font-semibold text-ink">
                            {{ detail.summary.done }}/{{ detail.summary.total }} done
                        </span>
                    </div>

                    <div
                        v-if="detail.stase_log"
                        class="flex items-center justify-between rounded-xl border border-border px-4 py-3 text-sm"
                    >
                        <span class="text-muted">Period</span>
                        <span class="font-medium text-ink">
                            {{ formatDate(detail.stase_log.start_date) }} &ndash; {{ formatDate(detail.stase_log.end_date) }}
                        </span>
                    </div>

                    <div
                        v-if="detail.attendance"
                        class="flex items-center justify-between rounded-xl border border-border px-4 py-3 text-sm"
                    >
                        <div>
                            <span class="text-muted">Kehadiran</span>
                            <div class="text-xs text-muted">
                                {{ detail.attendance.present }}/{{ detail.attendance.working_days }} working days
                            </div>
                        </div>
                        <span
                            class="rounded-full px-2.5 py-1 text-xs font-semibold"
                            :class="fulfilmentClass(detail.attendance.percentage)"
                        >
                            {{ detail.attendance.percentage }}%
                        </span>
                    </div>

                    <div v-if="detail.tasks.length === 0" class="px-1 py-4 text-sm text-muted">
                        No tasks configured for this stase.
                    </div>

                    <ul v-else class="divide-y divide-border rounded-xl border border-border">
                        <li
                            v-for="task in detail.tasks"
                            :key="task.stase_task_id"
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
                                    <div v-if="task.done && task.date" class="text-xs text-muted">{{ task.date }}</div>
                                </div>
                            </div>
                            <span
                                v-if="task.done"
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

                    <div v-if="reminderResult" class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
                        {{ reminderResult }}
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button
                            class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
                            type="button"
                            :disabled="previewLoading"
                            @click="openReminderPreview"
                        >
                            {{ previewLoading ? 'Loading preview...' : 'Send Reminder Email' }}
                        </button>
                        <button
                            class="rounded-xl border border-emerald-500 px-4 py-2 text-sm font-medium text-emerald-600 disabled:cursor-not-allowed disabled:border-border disabled:text-muted disabled:opacity-60"
                            type="button"
                            :disabled="!waPhoneDigits"
                            :title="!waPhoneDigits ? 'Nomor tidak dicantumkan' : ''"
                            @click="sendWhatsapp"
                        >
                            Send Reminder Whatsapp
                        </button>
                    </div>
                </div>
            </div>
        </Modal>

        <Modal
            :open="previewModalOpen"
            title="Email Preview"
            :eyebrow="preview ? preview.subject : ''"
            size="xl"
            @close="closePreview"
        >
            <div class="relative min-h-[200px]">
                <Loading :active="previewLoading" :is-full-page="false" />

                <div v-if="preview" class="grid gap-3">
                    <div class="text-xs text-muted">
                        To: <span class="font-medium text-ink">{{ preview.to }}</span>
                    </div>
                    <iframe
                        :srcdoc="preview.html"
                        sandbox=""
                        class="h-[420px] w-full rounded-xl border border-border bg-white"
                    ></iframe>

                    <div v-if="reminderError" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                        {{ reminderError }}
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <button
                            class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                            type="button"
                            @click="closePreview"
                        >
                            Cancel
                        </button>
                        <button
                            class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
                            type="button"
                            :disabled="sendingReminder"
                            @click="sendReminderEmail"
                        >
                            {{ sendingReminder ? 'Sending...' : 'Send Email' }}
                        </button>
                    </div>
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

const EMPTY_CELL = { done: 0, total: 0, status: 'empty' };

// Normalizes a local Indonesian phone number into wa.me's expected format:
// digits only, leading 0 replaced with the 62 country code.
function maskPhoneForWa(phone) {
    if (!phone) {
        return '';
    }

    let digits = String(phone).replace(/\D/g, '');
    if (!digits) {
        return '';
    }

    if (digits.startsWith('0')) {
        digits = `62${digits.slice(1)}`;
    } else if (!digits.startsWith('62')) {
        digits = `62${digits}`;
    }

    return digits;
}

export default {
    components: {
        Loading,
        Modal,
    },
    mixins: [persistFilters('students/monitoring')],
    data() {
        return {
            baseUrl: '/api/student-monitoring',
            rows: [],
            stases: [],
            allStases: [],
            overall: { percentage: null, done: 0, total: 0, students: 0 },
            pagination: {},
            filters: {
                name: '',
                stase_section: '',
                current_stase_id: '',
                year: '',
                page: 1,
            },
            loading: false,
            yearOptions: [],
            detailModalOpen: false,
            detailLoading: false,
            detail: null,
            sendingReminder: false,
            reminderResult: '',
            reminderError: '',
            previewModalOpen: false,
            previewLoading: false,
            preview: null,
        };
    },
    computed: {
        detailTitle() {
            if (!this.detail) {
                return 'Task Detail';
            }
            const stase = this.detail.stase;
            return stase.alias || stase.name || 'Task Detail';
        },
        waPhoneDigits() {
            if (!this.detail || !this.detail.student) {
                return '';
            }
            return maskPhoneForWa(this.detail.student.phone);
        },
        waMessage() {
            if (!this.detail) {
                return '';
            }
            const staseName = this.detail.stase ? (this.detail.stase.alias || this.detail.stase.name) : '';
            const done = this.detail.summary ? this.detail.summary.done : 0;
            const total = this.detail.summary ? this.detail.summary.total : 0;

            return `Halo ${this.detail.student.name}, kami informasikan bahwa tugas Anda pada stase ${staseName} `
                + `masih ${done}/${total} yang terselesaikan. Mohon segera dilengkapi ya. Terima kasih.`;
        },
    },
    created() {
        this.yearOptions = this.buildYearOptions();
        this.fetchAllStases();
        this.fetchData();
    },
    methods: {
        fetchAllStases() {
            return Repository.get('/api/stases', { params: { per_page: 500 } })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.allStases = result && Array.isArray(result.data) ? result.data : [];
                })
                .catch(() => {
                    this.allStases = [];
                });
        },
        buildYearOptions() {
            const options = [];
            const now = new Date();
            const currentYear = now.getFullYear();
            const currentMonth = now.getMonth() + 1;
            const availableMonths = [1, 7];

            for (let year = 2016; year <= currentYear; year += 1) {
                availableMonths.forEach((month) => {
                    if (year < currentYear || month <= currentMonth) {
                        const monthLabel = String(month).padStart(2, '0');
                        options.push(`${year}-${monthLabel}`);
                    }
                });
            }

            return options;
        },
        fetchData() {
            this.loading = true;

            return Repository.get(this.baseUrl, {
                params: this.filters,
            })
                .then((response) => {
                    const payload = response && response.data ? response.data : {};
                    const result = payload.result || {};

                    this.rows = Array.isArray(result.data) ? result.data : [];
                    this.pagination = result || {};
                    this.stases = Array.isArray(payload.stases) ? payload.stases : [];
                    this.overall = payload.overall || { percentage: null, done: 0, total: 0, students: 0 };
                })
                .catch(() => {
                    this.rows = [];
                    this.pagination = {};
                    this.overall = { percentage: null, done: 0, total: 0, students: 0 };
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        cell(row, staseId) {
            if (row && row.cells && row.cells[staseId]) {
                return row.cells[staseId];
            }
            return EMPTY_CELL;
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
        openDetail(row, stase) {
            if (this.cell(row, stase.id).total === 0) {
                return;
            }

            this.detail = null;
            this.detailLoading = true;
            this.detailModalOpen = true;
            this.reminderResult = '';
            this.reminderError = '';

            Repository.get('/api/student-monitoring-detail', {
                params: { student_id: row.id, stase_id: stase.id },
            })
                .then((response) => {
                    this.detail = response && response.data ? response.data.result : null;
                })
                .catch(() => {
                    this.detail = null;
                    this.closeDetail();
                })
                .finally(() => {
                    this.detailLoading = false;
                });
        },
        closeDetail() {
            this.detailModalOpen = false;
            this.detail = null;
            this.reminderResult = '';
            this.reminderError = '';
            this.closePreview();
        },
        openReminderPreview() {
            if (!this.detail) {
                return;
            }

            this.preview = null;
            this.previewLoading = true;
            this.reminderError = '';
            this.previewModalOpen = true;

            Repository.get('/api/notification/insufficient-score/preview', {
                params: {
                    student_id: this.detail.student.id,
                    stase_id: this.detail.stase.id,
                },
            })
                .then((response) => {
                    this.preview = response && response.data ? response.data.result : null;
                })
                .catch((error) => {
                    this.reminderError = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to load email preview.';
                    this.closePreview();
                })
                .finally(() => {
                    this.previewLoading = false;
                });
        },
        closePreview() {
            this.previewModalOpen = false;
            this.preview = null;
        },
        sendReminderEmail() {
            if (!this.detail) {
                return;
            }

            this.sendingReminder = true;
            this.reminderResult = '';
            this.reminderError = '';

            Repository.post('/api/notification/insufficient-score', {
                student_id: this.detail.student.id,
                stase_id: this.detail.stase.id,
            })
                .then(() => {
                    this.closePreview();
                    this.reminderResult = 'Reminder email sent successfully.';
                    this.$showToast('Reminder email sent.');
                })
                .catch((error) => {
                    this.reminderError = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to send reminder email.';
                })
                .finally(() => {
                    this.sendingReminder = false;
                });
        },
        sendWhatsapp() {
            if (!this.waPhoneDigits) {
                return;
            }

            const link = `https://wa.me/${this.waPhoneDigits}?text=${encodeURIComponent(this.waMessage)}`;
            window.open(link, '_blank');
        },
        cellDotClass(cell) {
            const map = {
                green: 'bg-emerald-500',
                yellow: 'bg-amber-400',
                red: 'bg-rose-500',
            };
            return map[cell.status] || 'bg-slate-300';
        },
        cellTextClass(cell) {
            const map = {
                green: 'text-emerald-600',
                yellow: 'text-amber-600',
                red: 'text-rose-600',
            };
            return map[cell.status] || 'text-slate-400';
        },
        fulfilmentClass(value) {
            if (value >= 100) {
                return 'bg-emerald-100 text-emerald-700';
            }
            if (value >= 50) {
                return 'bg-amber-100 text-amber-700';
            }
            return 'bg-rose-100 text-rose-700';
        },
        fulfilmentBarClass(value) {
            if (value >= 100) {
                return 'bg-emerald-500';
            }
            if (value >= 50) {
                return 'bg-amber-400';
            }
            return 'bg-rose-500';
        },
        applyFilter() {
            this.filters.page = 1;
            this.fetchData();
        },
        resetFilter() {
            this.filters.name = '';
            this.filters.stase_section = '';
            this.filters.current_stase_id = '';
            this.filters.year = '';
            this.filters.page = 1;
            this.fetchData();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchData();
        },
        printMonitoring() {
            const params = new URLSearchParams({
                token: localStorage.getItem('token') || '',
            });

            ['name', 'stase_section', 'current_stase_id', 'year'].forEach((key) => {
                if (this.filters[key]) {
                    params.append(key, this.filters[key]);
                }
            });

            window.open(`/print/student-monitoring?${params.toString()}`, '_blank');
        },
    },
};
</script>
