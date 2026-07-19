<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Student Management</div>
                <h1 class="text-2xl font-semibold text-ink">Monitoring Logbook</h1>
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
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Has logbook (count shown)</span>
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span> No logbook</span>
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Logbook total ≥ weekdays in range</span>
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-rose-400"></span> Logbook total &lt; weekdays in range</span>
            </div>
        </section>

        <section class="relative min-w-0 overflow-hidden rounded-2xl border border-border bg-panel">
            <Loading :active="loading" :is-full-page="false" />
            <div class="flex items-center justify-between border-b border-border px-5 py-4">
                <div class="font-semibold">Student Logbook Calendar</div>
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
                            <th class="sticky left-[216px] z-30 min-w-[110px] bg-panel px-3 py-2"></th>
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
                            <th class="sticky left-[216px] z-30 min-w-[110px] bg-panel px-3 py-3 text-center">Logbook</th>
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
                            <td class="sticky left-[216px] z-20 min-w-[110px] bg-panel px-3 py-3 text-center group-hover:bg-slate-50">
                                <button
                                    type="button"
                                    class="inline-flex min-w-[64px] justify-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="row.sufficient ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 'bg-rose-100 text-rose-700 hover:bg-rose-200'"
                                    @click="openSummary(row)"
                                >
                                    {{ row.total_logbook || 0 }} / {{ row.weekdays || 0 }}
                                </button>
                            </td>
                            <td
                                v-for="date in dates"
                                :key="date"
                                class="px-1 py-3 text-center group-hover:bg-slate-50"
                            >
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1 rounded-lg px-1.5 py-1 text-xs font-medium transition-colors"
                                    :class="cellCount(row, date) > 0 ? 'cursor-pointer text-emerald-600 hover:bg-emerald-50' : 'cursor-default text-slate-400'"
                                    :disabled="cellCount(row, date) === 0"
                                    @click="openDetail(row, date)"
                                >
                                    <span
                                        class="h-2.5 w-2.5 rounded-full"
                                        :class="cellCount(row, date) > 0 ? 'bg-emerald-500' : 'bg-slate-300'"
                                    ></span>
                                    <span v-if="cellCount(row, date) > 0">{{ cellCount(row, date) }}</span>
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
            eyebrow="Logbook entries"
            size="lg"
            @close="closeDetail"
        >
            <div class="relative min-h-[120px]">
                <Loading :active="detailLoading" :is-full-page="false" />

                <div v-if="!detailLoading && detailLogs.length === 0" class="px-1 py-4 text-sm text-muted">
                    No logbook entries found.
                </div>

                <ul v-else class="grid gap-3">
                    <li
                        v-for="logbook in detailLogs"
                        :key="logbook.id"
                        class="rounded-xl border border-border px-4 py-3"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <div class="font-semibold text-ink">
                                {{ logbook.form_option_name || logbook.type || 'Logbook' }}
                            </div>
                            <span v-if="logbook.category" class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-muted">
                                {{ logbook.category }}
                            </span>
                        </div>
                        <div class="mt-1 text-xs text-muted">
                            <span v-if="logbook.stase">{{ logbook.stase.name }}</span>
                            <span v-if="logbook.lecture"> • Supervisor: {{ logbook.lecture.name }}</span>
                        </div>
                        <div class="mt-2 grid gap-1 text-sm text-ink">
                            <div v-for="i in 6" :key="`field-${i}`" v-if="logbook[`field_${i}`]">
                                {{ logbook[`field_${i}`] }}
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </Modal>

        <Modal
            :open="summaryModalOpen"
            title="Student Logbook Summary"
            eyebrow="Logbook status"
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
                    <div class="flex items-center justify-between rounded-xl border border-border px-4 py-3">
                        <span class="text-muted">Logbook entries ({{ summary.date_from }} &ndash; {{ summary.date_to }})</span>
                        <span
                            class="rounded-full px-2.5 py-1 text-xs font-semibold"
                            :class="summary.sufficient ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'"
                        >
                            {{ summary.logbook_count }} / {{ summary.weekdays }}
                        </span>
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
                            {{ previewLoading ? 'Memuat preview...' : 'Kirim notifikasi Email' }}
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

const MONTH_LABELS = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December',
];

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
    data() {
        return {
            baseUrl: '/api/student-monitoring-logbook',
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
            detailLogs: [],
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
                return 'Logbook Detail';
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
            return `Halo ${this.summary.student.name}, kami informasikan bahwa pencatatan logbook Anda dalam periode `
                + `${this.summary.date_from} s/d ${this.summary.date_to} baru ${this.summary.logbook_count}/${this.summary.weekdays} hari kerja. `
                + `Mohon segera dilengkapi ya. Terima kasih.`;
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
        cellCount(row, date) {
            return (row && row.cells && row.cells[date]) || 0;
        },
        dayOfMonth(date) {
            return Number(date.slice(8, 10));
        },
        isToday(date) {
            return date === new Date().toISOString().slice(0, 10);
        },
        openDetail(row, date) {
            if (this.cellCount(row, date) === 0) {
                return;
            }

            this.detailStudent = row;
            this.detailDate = date;
            this.detailLogs = [];
            this.detailLoading = true;
            this.detailModalOpen = true;

            Repository.get('/api/logbooks', {
                params: { student_id: row.id, date, per_page: 50 },
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.detailLogs = result && Array.isArray(result.data) ? result.data : [];
                })
                .catch(() => {
                    this.detailLogs = [];
                })
                .finally(() => {
                    this.detailLoading = false;
                });
        },
        closeDetail() {
            this.detailModalOpen = false;
            this.detailLogs = [];
            this.detailStudent = null;
            this.detailDate = null;
        },
        openSummary(row) {
            this.summary = null;
            this.sendResult = '';
            this.sendError = '';
            this.summaryLoading = true;
            this.summaryModalOpen = true;

            Repository.get('/api/student-monitoring-logbook-summary', {
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

            Repository.get('/api/notification/insufficient-logbook/preview', {
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
                        : 'Gagal memuat preview email.';
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

            Repository.post('/api/notification/insufficient-logbook', {
                student_id: this.summary.student.id,
                date_from: this.summary.date_from,
                date_to: this.summary.date_to,
            })
                .then(() => {
                    this.closePreview();
                    this.sendResult = 'Email pengingat berhasil dikirim.';
                    this.$showToast('Reminder email sent.');
                })
                .catch((error) => {
                    this.sendError = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Gagal mengirim email pengingat.';
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
