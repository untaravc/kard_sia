<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Student Management</div>
                <h1 class="text-2xl font-semibold text-ink">Monitoring Presence</h1>
            </div>
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
                <div class="min-w-[160px]">
                    <label class="text-xs text-muted">Status</label>
                    <select
                        v-model="filters.status"
                        @change="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">All</option>
                        <option value="active">Active</option>
                        <option value="nonactive">Nonactive</option>
                    </select>
                </div>
                <div class="min-w-[160px]">
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
                <div class="min-w-[160px]">
                    <label class="text-xs text-muted">From</label>
                    <input
                        v-model="filters.date_from"
                        type="date"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="min-w-[160px]">
                    <label class="text-xs text-muted">To</label>
                    <input
                        v-model="filters.date_to"
                        type="date"
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
            <div class="mt-4 flex flex-wrap items-center gap-4 text-xs text-muted">
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Presence &amp; activity both recorded</span>
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span> Only one of presence / activity recorded</span>
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span> No presence and no activity</span>
            </div>
        </section>

        <section class="relative min-w-0 overflow-hidden rounded-2xl border border-border bg-panel">
            <Loading :active="loading" :is-full-page="false" />
            <div class="flex items-center justify-between border-b border-border px-5 py-4">
                <div class="font-semibold">Student Presence Calendar</div>
                <div class="text-xs text-muted" v-if="pagination.total">
                    {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }}
                </div>
            </div>

            <div class="w-full overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-border text-left text-xs uppercase tracking-wide text-muted">
                            <th class="sticky left-0 z-30 w-14 bg-panel px-3 py-2"></th>
                            <th class="sticky left-14 z-30 min-w-[160px] bg-panel px-3 py-2"></th>
                            <th class="sticky left-[216px] z-30 min-w-[150px] bg-panel px-3 py-2"></th>
                            <th
                                v-for="group in monthGroups"
                                :key="group.label"
                                class="px-2 py-2 text-center"
                                :colspan="group.dates.length"
                            >
                                {{ group.label }}
                            </th>
                        </tr>
                        <tr class="border-b border-border text-left text-xs uppercase tracking-wide text-muted">
                            <th class="sticky left-0 z-30 w-14 bg-panel px-3 py-3">No</th>
                            <th class="sticky left-14 z-30 min-w-[160px] bg-panel px-3 py-3">Name</th>
                            <th class="sticky left-[216px] z-30 min-w-[150px] bg-panel px-3 py-3 text-center">Presence / Activity</th>
                            <th
                                v-for="date in dates"
                                :key="date"
                                class="min-w-[36px] px-1 py-3 text-center"
                                :class="isToday(date) ? 'text-primary font-semibold' : ''"
                            >
                                {{ dayOfMonth(date) }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-if="!loading && rows.length === 0">
                            <td :colspan="dates.length + 3" class="px-5 py-6 text-sm text-muted">
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
                            <td class="sticky left-[216px] z-20 min-w-[150px] bg-panel px-3 py-3 text-center group-hover:bg-slate-50">
                                <button
                                    type="button"
                                    class="inline-flex flex-col items-center gap-1 rounded-xl px-2 py-1 hover:bg-slate-100"
                                    @click="openSummary(row)"
                                >
                                    <span
                                        class="inline-flex min-w-[56px] justify-center rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                        :class="(row.presence_count || 0) >= (row.weekdays || 0) ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'"
                                    >
                                        {{ row.presence_count || 0 }} / {{ row.weekdays || 0 }}
                                    </span>
                                    <span
                                        class="inline-flex min-w-[56px] justify-center rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                        :class="(row.activity_student_total || 0) >= (row.activity_total || 0) && (row.activity_total || 0) > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'"
                                    >
                                        {{ row.activity_student_total || 0 }} / {{ row.activity_total || 0 }}
                                    </span>
                                </button>
                            </td>
                            <td
                                v-for="date in dates"
                                :key="date"
                                class="px-1 py-3 text-center group-hover:bg-slate-50"
                            >
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1 rounded-lg px-1.5 py-1 text-xs font-medium transition-colors hover:bg-slate-100"
                                    @click="openDetail(row, date)"
                                >
                                    <span class="h-2.5 w-2.5 rounded-full" :class="cellDotClass(cell(row, date).status)"></span>
                                    <span v-if="cell(row, date).activity_total > 0">
                                        {{ cell(row, date).activity_done }}/{{ cell(row, date).activity_total }}
                                    </span>
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
            eyebrow="Presence & activity"
            size="md"
            @close="closeDetail"
        >
            <div class="relative min-h-[160px]">
                <Loading :active="detailLoading" :is-full-page="false" />

                <div v-if="detail" class="grid gap-4 text-sm">
                    <div class="rounded-xl border border-border px-4 py-3">
                        <div class="text-xs text-muted">Presence</div>
                        <div v-if="detail.presence" class="mt-1 text-ink">
                            Check-in: {{ formatTime(detail.presence.checkin) }}
                            <span v-if="detail.presence.checkout"> &middot; Check-out: {{ formatTime(detail.presence.checkout) }}</span>
                        </div>
                        <div v-else class="mt-1 text-muted">No presence recorded on this date.</div>
                    </div>

                    <div>
                        <div class="mb-2 text-xs text-muted">Activities</div>
                        <div v-if="detail.activities.length === 0" class="px-1 py-2 text-sm text-muted">
                            No activities scheduled on this date.
                        </div>
                        <ul v-else class="divide-y divide-border rounded-xl border border-border">
                            <li
                                v-for="activity in detail.activities"
                                :key="activity.id"
                                class="flex items-center justify-between gap-3 px-4 py-3"
                            >
                                <div class="flex items-center gap-2.5">
                                    <span
                                        class="grid h-6 w-6 place-items-center rounded-full text-xs font-bold"
                                        :class="activity.attended ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-400'"
                                    >
                                        {{ activity.attended ? '✓' : '•' }}
                                    </span>
                                    <div class="text-sm font-medium text-ink">{{ activity.name }}</div>
                                </div>
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="activity.attended ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-50 text-slate-400'"
                                >
                                    {{ activity.attended ? 'Attended' : 'Not yet' }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </Modal>

        <Modal
            :open="summaryModalOpen"
            title="Student Presence Summary"
            eyebrow="Presence & activity status"
            size="md"
            @close="closeSummary"
        >
            <div class="relative min-h-[160px]">
                <Loading :active="summaryLoading" :is-full-page="false" />

                <div v-if="summary" class="grid gap-4 text-sm">
                    <div class="grid gap-1">
                        <div class="text-xs text-muted">Name</div>
                        <div class="font-semibold text-ink">{{ summary.student.name }}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-1">
                            <div class="text-xs text-muted">Year</div>
                            <div class="font-medium text-ink">{{ summary.student.year || '-' }}</div>
                        </div>
                        <div class="grid gap-1">
                            <div class="text-xs text-muted">Email</div>
                            <div class="font-medium text-ink break-all">{{ summary.student.email || '-' }}</div>
                        </div>
                    </div>
                    <div class="grid gap-1">
                        <div class="text-xs text-muted">Current / Last Stase</div>
                        <div class="font-medium text-ink">
                            {{ summary.stase ? (summary.stase.alias || summary.stase.name) : 'No stase recorded' }}
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <div class="flex items-center justify-between rounded-xl border border-border px-4 py-3">
                            <span class="text-muted">Presence ({{ summary.date_from }} &ndash; {{ summary.date_to }})</span>
                            <span
                                class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                :class="summary.presence_count >= summary.weekdays ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'"
                            >
                                {{ summary.presence_count }} / {{ summary.weekdays }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between rounded-xl border border-border px-4 py-3">
                            <span class="text-muted">Activity attendance</span>
                            <span
                                class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                :class="summary.activity_student_total >= summary.activity_total && summary.activity_total > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'"
                            >
                                {{ summary.activity_student_total }} / {{ summary.activity_total }}
                            </span>
                        </div>
                    </div>

                    <div v-if="sendResult" class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
                        {{ sendResult }}
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

                    <div v-if="sendError" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                        {{ sendError }}
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
                            :disabled="sending"
                            @click="sendReminderEmail"
                        >
                            {{ sending ? 'Sending...' : 'Send Email' }}
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

const MONTH_LABELS = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December',
];

const EMPTY_CELL = { presence: false, activity_done: 0, activity_total: 0, status: 'gray' };

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
    mixins: [persistFilters('students/monitoring-presence')],
    data() {
        return {
            baseUrl: '/api/student-monitoring-presence',
            rows: [],
            dates: [],
            pagination: {},
            filters: {
                name: '',
                status: 'active',
                year: '',
                date_from: '',
                date_to: '',
                page: 1,
            },
            loading: false,
            yearOptions: [],
            detailModalOpen: false,
            detailLoading: false,
            detail: null,
            detailStudent: null,
            detailDate: null,
            summaryModalOpen: false,
            summaryLoading: false,
            summary: null,
            sending: false,
            sendResult: '',
            sendError: '',
            previewModalOpen: false,
            previewLoading: false,
            preview: null,
        };
    },
    computed: {
        monthGroups() {
            const groups = [];
            this.dates.forEach((date) => {
                const monthKey = date.slice(0, 7);
                const last = groups[groups.length - 1];
                if (last && last.key === monthKey) {
                    last.dates.push(date);
                    return;
                }
                const [year, month] = date.split('-');
                groups.push({
                    key: monthKey,
                    label: `${MONTH_LABELS[Number(month) - 1]} ${year}`,
                    dates: [date],
                });
            });
            return groups;
        },
        detailTitle() {
            if (!this.detailStudent || !this.detailDate) {
                return 'Presence Detail';
            }
            return `${this.detailStudent.name} — ${this.detailDate}`;
        },
        waPhoneDigits() {
            if (!this.summary || !this.summary.student) {
                return '';
            }
            return maskPhoneForWa(this.summary.student.phone);
        },
        waMessage() {
            if (!this.summary) {
                return '';
            }
            return `Halo ${this.summary.student.name}, kami informasikan bahwa presensi harian Anda baru ${this.summary.presence_count}/${this.summary.weekdays} hari kerja `
                + `dan kehadiran kegiatan Anda baru ${this.summary.activity_student_total}/${this.summary.activity_total} kegiatan dalam periode `
                + `${this.summary.date_from} s/d ${this.summary.date_to}. Mohon segera dilengkapi ya. Terima kasih.`;
        },
    },
    created() {
        this.yearOptions = this.buildYearOptions();
        this.fetchData();
    },
    methods: {
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
                    this.dates = Array.isArray(payload.dates) ? payload.dates : [];
                })
                .catch(() => {
                    this.rows = [];
                    this.pagination = {};
                    this.dates = [];
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        cell(row, date) {
            if (row && row.cells && row.cells[date]) {
                return row.cells[date];
            }
            return EMPTY_CELL;
        },
        cellDotClass(status) {
            const map = {
                green: 'bg-emerald-500',
                yellow: 'bg-amber-400',
                gray: 'bg-slate-300',
            };
            return map[status] || 'bg-slate-300';
        },
        dayOfMonth(date) {
            return Number(date.slice(8, 10));
        },
        isToday(date) {
            return date === new Date().toISOString().slice(0, 10);
        },
        formatTime(value) {
            if (!value) {
                return '-';
            }
            const date = new Date(String(value).replace(' ', 'T'));
            if (Number.isNaN(date.getTime())) {
                return value;
            }
            return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        },
        openDetail(row, date) {
            this.detailStudent = row;
            this.detailDate = date;
            this.detail = null;
            this.detailLoading = true;
            this.detailModalOpen = true;

            Repository.get('/api/student-monitoring-presence-detail', {
                params: { student_id: row.id, date },
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
            this.detailStudent = null;
            this.detailDate = null;
        },
        openSummary(row) {
            this.summary = null;
            this.sendResult = '';
            this.sendError = '';
            this.summaryLoading = true;
            this.summaryModalOpen = true;

            Repository.get('/api/student-monitoring-presence-summary', {
                params: {
                    student_id: row.id,
                    date_from: this.filters.date_from,
                    date_to: this.filters.date_to,
                },
            })
                .then((response) => {
                    this.summary = response && response.data ? response.data.result : null;
                })
                .catch(() => {
                    this.summary = null;
                    this.closeSummary();
                })
                .finally(() => {
                    this.summaryLoading = false;
                });
        },
        closeSummary() {
            this.summaryModalOpen = false;
            this.summary = null;
            this.sendResult = '';
            this.sendError = '';
            this.closePreview();
        },
        openReminderPreview() {
            if (!this.summary) {
                return;
            }

            this.preview = null;
            this.previewLoading = true;
            this.sendError = '';
            this.previewModalOpen = true;

            Repository.get('/api/notification/insufficient-presence/preview', {
                params: {
                    student_id: this.summary.student.id,
                    date_from: this.summary.date_from,
                    date_to: this.summary.date_to,
                },
            })
                .then((response) => {
                    this.preview = response && response.data ? response.data.result : null;
                })
                .catch((error) => {
                    this.sendError = error && error.response && error.response.data
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
            if (!this.summary) {
                return;
            }

            this.sending = true;
            this.sendResult = '';
            this.sendError = '';

            Repository.post('/api/notification/insufficient-presence', {
                student_id: this.summary.student.id,
                date_from: this.summary.date_from,
                date_to: this.summary.date_to,
            })
                .then(() => {
                    this.closePreview();
                    this.sendResult = 'Reminder email sent successfully.';
                    this.$showToast('Reminder email sent.');
                })
                .catch((error) => {
                    this.sendError = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to send reminder email.';
                })
                .finally(() => {
                    this.sending = false;
                });
        },
        sendWhatsapp() {
            if (!this.waPhoneDigits) {
                return;
            }

            const link = `https://wa.me/${this.waPhoneDigits}?text=${encodeURIComponent(this.waMessage)}`;
            window.open(link, '_blank');
        },
        applyFilter() {
            this.filters.page = 1;
            this.fetchData();
        },
        resetFilter() {
            this.filters.name = '';
            this.filters.status = 'active';
            this.filters.year = '';
            this.filters.date_from = '';
            this.filters.date_to = '';
            this.filters.page = 1;
            this.fetchData();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchData();
        },
    },
};
</script>
