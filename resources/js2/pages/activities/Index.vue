<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Activity Management</div>
                <h1 class="text-2xl font-semibold text-ink">Activities</h1>
            </div>
            <div class="flex items-center gap-2">
                <button
                    class="rounded-xl border border-border px-4 py-2 text-sm font-medium text-ink"
                    type="button"
                    @click="openPrintModal"
                >
                    Print
                </button>
                <router-link
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    to="/blu/activities/create"
                >
                    Add Activity
                </router-link>
            </div>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="text-xs text-muted">Keyword</label>
                    <input
                        v-model.trim="filters.keyword"
                        type="text"
                        placeholder="Search name, title, or speaker..."
                        @keyup.enter="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="min-w-[160px]">
                    <label class="text-xs text-muted">Date From</label>
                    <input
                        v-model="filters.date_from"
                        type="date"
                        @change="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="min-w-[160px]">
                    <label class="text-xs text-muted">Date To</label>
                    <input
                        v-model="filters.date_to"
                        type="date"
                        @change="applyFilter"
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
                <div class="font-semibold">Activities</div>
                <div class="text-xs text-muted" v-if="pagination.total">
                    {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }}
                </div>
            </div>
            <div v-if="errorMessage" class="border-b border-rose-100 bg-rose-50 px-5 py-3 text-xs text-rose-600">
                {{ errorMessage }}
            </div>
            <div class="divide-y divide-border">
                <div v-if="!loading && activities.length === 0" class="px-5 py-6 text-sm text-muted">
                    No activities found.
                </div>
                <div
                    v-for="(activity, index) in activities"
                    :key="activity.id"
                    class="flex flex-wrap items-center gap-3 px-5 py-4"
                    :class="isToday(activity) ? 'bg-emerald-50' : ''"
                >
                    <div class="w-8 text-sm font-semibold text-muted">
                        {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="font-semibold text-ink">{{ activity.name }}</div>
                            <span v-if="activity.status" class="text-xs text-muted">
                                Status: {{ activity.status }}
                            </span>
                        </div>
                        <div class="text-xs text-muted">
                            <span v-if="activity.title">{{ activity.title }}</span>
                            <span v-if="activity.speaker">• Speaker: {{ activity.speaker }}</span>
                            <span v-if="activity.place">• {{ activity.place }}</span>
                        </div>
                        <div class="text-xs text-muted" v-if="activity.start_date">
                            Date: {{ formatDateRange(activity.start_date, activity.end_date) }}
                        </div>
                        <div class="text-xs text-muted">
                            Attendees: {{ activity.activity_students_count || 0 }} students, {{ activity.activity_lectures_count || 0 }} lecturers
                        </div>
                    </div>
                    <div class="relative action-dropdown">
                        <button
                            class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                            type="button"
                            @click.stop="toggleActionMenu(activity.id)"
                        >
                            Actions
                        </button>
                        <div
                            v-if="actionMenuOpenId === activity.id"
                            class="absolute right-0 z-10 mt-2 w-44 rounded-xl border border-border bg-white p-1 shadow-lg"
                        >
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                type="button"
                                @click="handleAction('view', activity)"
                            >
                                View
                            </button>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                type="button"
                                @click="handleAction('edit', activity)"
                            >
                                Edit
                            </button>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                type="button"
                                @click="handleAction('import-presence', activity)"
                            >
                                Import Presensi
                            </button>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-rose-600 hover:bg-rose-50"
                                type="button"
                                @click="handleAction('delete', activity)"
                            >
                                Delete
                            </button>
                        </div>
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

        <Modal
            :open="importModalOpen"
            :title="importActivity ? `Import Presensi - ${importActivity.name}` : 'Import Presensi'"
            eyebrow="Activities"
            size="xxl"
            @close="closeImportPresence"
        >
            <div class="grid gap-4">
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">File Presensi (.xls, .xlsx)</span>
                    <input
                        ref="importFileInput"
                        type="file"
                        accept=".xls,.xlsx"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        @change="onImportFileChange"
                    />
                </label>
                <div v-if="importError" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ importError }}
                </div>
                <div v-if="importResult" class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
                    Imported {{ importResult.students_created + importResult.lectures_created }} new presence record(s)
                    ({{ importResult.students_existing + importResult.lectures_existing }} already recorded,
                    {{ importResult.unmatched }} unmatched of {{ importResult.total }} rows).
                </div>
                <div v-if="importRows.length" class="text-xs text-muted">
                    {{ importMatchedCount }} of {{ importRows.length }} matched to an active student or lecture.
                </div>
                <div v-if="importRows.length" class="overflow-x-auto rounded-xl border border-border">
                    <table class="min-w-full text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-left text-muted">
                                <th class="px-3 py-2">No</th>
                                <th class="px-3 py-2">Nama</th>
                                <th class="px-3 py-2">Jenis Identitas</th>
                                <th class="px-3 py-2">Nomor Identitas</th>
                                <th class="px-3 py-2">Waktu Presensi</th>
                                <th class="px-3 py-2">Unit/Fakultas/Prodi</th>
                                <th class="px-3 py-2">Match</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr
                                v-for="(row, idx) in importRows"
                                :key="idx"
                                :class="row.matched ? 'bg-emerald-50' : ''"
                            >
                                <td class="px-3 py-2">{{ row.no }}</td>
                                <td class="px-3 py-2">{{ row.name }}</td>
                                <td class="px-3 py-2">{{ row.identity_type }}</td>
                                <td class="px-3 py-2">{{ row.identity_number }}</td>
                                <td class="px-3 py-2">{{ row.presence_time }}</td>
                                <td class="px-3 py-2">{{ row.unit }}</td>
                                <td class="px-3 py-2">
                                    <span
                                        class="rounded-lg px-2 py-0.5 text-xs font-medium"
                                        :class="row.matched ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-muted'"
                                    >
                                        {{ matchLabel(row) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <template #footer>
                <button
                    class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                    type="button"
                    :disabled="importSubmitting"
                    @click="closeImportPresence"
                >
                    {{ importResult ? 'Close' : 'Cancel' }}
                </button>
                <button
                    v-if="!importResult && !importRows.length"
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="button"
                    :disabled="importSubmitting || !importFile"
                    @click="previewImportPresence"
                >
                    {{ importSubmitting ? 'Loading...' : 'Preview' }}
                </button>
                <button
                    v-else-if="!importResult"
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="button"
                    :disabled="importSubmitting || importMatchedCount === 0"
                    @click="uploadImportPresence"
                >
                    {{ importSubmitting ? 'Uploading...' : 'Upload' }}
                </button>
            </template>
        </Modal>

        <Modal
            :open="printModalOpen"
            title="Print Laporan Kegiatan"
            eyebrow="Activities"
            size="md"
            @close="closePrintModal"
        >
            <div class="grid gap-4">
                <p class="text-xs text-muted">
                    Laporan menampilkan detail tiap kegiatan beserta kehadiran dosen (pembimbing, penguji, pengampu) dan
                    presensi peserta didik pada rentang tanggal yang dipilih.
                </p>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Tanggal Mulai</span>
                    <input
                        v-model="printForm.start_date"
                        type="date"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Tanggal Selesai</span>
                    <input
                        v-model="printForm.end_date"
                        type="date"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <div v-if="printError" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ printError }}
                </div>
            </div>
            <template #footer>
                <button
                    class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                    type="button"
                    @click="closePrintModal"
                >
                    Cancel
                </button>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="button"
                    @click="submitPrint"
                >
                    Print
                </button>
            </template>
        </Modal>

    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Modal from '../../components/Modal.vue';
import Repository from '../../repository';
import persistFilters from '../../mixins/persistFilters';

const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

const todayStr = () => {
    const now = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
};

const parseDateTime = (value) => {
    if (!value) {
        return null;
    }
    const match = /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/.exec(value);
    if (!match) {
        return null;
    }
    return {
        y: Number(match[1]),
        mo: Number(match[2]),
        d: Number(match[3]),
        hh: match[4],
        mm: match[5],
    };
};

export default {
    components: {
        Loading,
        Modal,
    },
    mixins: [persistFilters('activities')],
    data() {
        return {
            baseUrl: '/api/activities',
            activities: [],
            pagination: {},
            filters: {
                keyword: '',
                date_from: todayStr(),
                date_to: '',
                page: 1,
            },
            loading: false,
            errorMessage: '',
            actionMenuOpenId: null,
            printModalOpen: false,
            printError: '',
            printForm: {
                start_date: todayStr(),
                end_date: todayStr(),
            },
            importModalOpen: false,
            importActivity: null,
            importFile: null,
            importSubmitting: false,
            importError: '',
            importRows: [],
            importResult: null,
        };
    },
    computed: {
        importMatchedCount() {
            return this.importRows.filter((row) => row.matched).length;
        },
    },
    created() {
        this.fetchActivities();
    },
    mounted() {
        document.addEventListener('click', this.handleDocumentClick);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.handleDocumentClick);
    },
    methods: {
        formatDateRange(start, end) {
            const s = parseDateTime(start);
            if (!s) {
                return '';
            }
            const day = (p) => `${p.d} ${MONTHS[p.mo - 1]} ${p.y}`;
            const time = (p) => `${p.hh}:${p.mm}`;

            let out = `${day(s)} ${time(s)}`;
            const e = parseDateTime(end);
            if (e) {
                const sameDay = e.y === s.y && e.mo === s.mo && e.d === s.d;
                out += sameDay ? ` - ${time(e)}` : ` - ${day(e)} ${time(e)}`;
            }
            return out;
        },
        isToday(activity) {
            const s = parseDateTime(activity && activity.start_date);
            if (!s) {
                return false;
            }
            const now = new Date();
            const toNum = (p) => p.y * 10000 + p.mo * 100 + p.d;
            const today = toNum({ y: now.getFullYear(), mo: now.getMonth() + 1, d: now.getDate() });
            const startNum = toNum(s);
            const e = parseDateTime(activity.end_date);
            const endNum = e ? toNum(e) : startNum;
            return today >= startNum && today <= endNum;
        },
        fetchActivities() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(this.baseUrl, {
                params: this.filters,
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];

                    this.activities = data;
                    this.pagination = result || {};
                })
                .catch(() => {
                    this.activities = [];
                    this.pagination = {};
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        applyFilter() {
            this.filters.page = 1;
            this.fetchActivities();
        },
        resetFilter() {
            this.filters.keyword = '';
            this.filters.date_from = todayStr();
            this.filters.date_to = '';
            this.filters.page = 1;
            this.fetchActivities();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchActivities();
        },
        openPrintModal() {
            this.printError = '';
            this.printForm = {
                start_date: this.filters.date_from || todayStr(),
                end_date: this.filters.date_to || this.filters.date_from || todayStr(),
            };
            this.printModalOpen = true;
        },
        closePrintModal() {
            this.printModalOpen = false;
            this.printError = '';
        },
        submitPrint() {
            const { start_date, end_date } = this.printForm;
            if (!start_date || !end_date) {
                this.printError = 'Tanggal mulai dan selesai wajib diisi.';
                return;
            }
            if (start_date > end_date) {
                this.printError = 'Tanggal mulai tidak boleh setelah tanggal selesai.';
                return;
            }

            const params = new URLSearchParams({
                token: localStorage.getItem('token') || '',
                start_date,
                end_date,
            });
            window.open(`/print/activities?${params.toString()}`, '_blank');
            this.closePrintModal();
        },
        toggleActionMenu(activityId) {
            this.actionMenuOpenId = this.actionMenuOpenId === activityId ? null : activityId;
        },
        closeActionMenu() {
            this.actionMenuOpenId = null;
        },
        handleDocumentClick(event) {
            const target = event && event.target ? event.target : null;
            if (!target) {
                return;
            }
            if (target.closest && target.closest('.action-dropdown')) {
                return;
            }
            this.closeActionMenu();
        },
        handleAction(action, activity) {
            this.closeActionMenu();

            if (action === 'view') {
                this.openView(activity);
                return;
            }
            if (action === 'edit') {
                this.openEdit(activity);
                return;
            }
            if (action === 'import-presence') {
                this.openImportPresence(activity);
                return;
            }
            if (action === 'delete') {
                this.deleteActivity(activity);
            }
        },
        openView(activity) {
            if (!activity || !activity.id) {
                return;
            }

            this.$router.push(`/blu/activities/${activity.id}/view`);
        },
        openEdit(activity) {
            if (!activity || !activity.id) {
                return;
            }

            this.$router.push(`/blu/activities/${activity.id}`);
        },
        deleteActivity(activity) {
            if (!window.confirm(`Delete activity ${activity.name}?`)) {
                return;
            }

            Repository.delete(`${this.baseUrl}/${activity.id}`)
                .then(() => {
                    this.fetchActivities();
                    this.$showToast('Activity deleted successfully.');
                })
                .catch(() => {
                    this.errorMessage = 'Failed to delete activity.';
                });
        },
        openImportPresence(activity) {
            this.importActivity = activity || null;
            this.importFile = null;
            this.importError = '';
            this.importRows = [];
            this.importResult = null;
            this.importModalOpen = true;
        },
        closeImportPresence(force = false) {
            if (this.importSubmitting && !force) {
                return;
            }
            const hadResult = Boolean(this.importResult);

            this.importModalOpen = false;
            this.importActivity = null;
            this.importFile = null;
            this.importError = '';
            this.importRows = [];
            this.importResult = null;
            if (this.$refs.importFileInput) {
                this.$refs.importFileInput.value = '';
            }
            if (hadResult) {
                this.fetchActivities();
            }
        },
        onImportFileChange(event) {
            const files = event && event.target ? event.target.files : null;
            this.importFile = files && files.length ? files[0] : null;
            this.importError = '';
            this.importRows = [];
            this.importResult = null;
        },
        previewImportPresence() {
            if (this.importSubmitting) {
                return;
            }
            if (!this.importFile) {
                this.importError = 'File is required.';
                return;
            }

            this.importSubmitting = true;
            this.importError = '';

            const formData = new FormData();
            formData.append('file', this.importFile);

            return Repository.post('/api/activities/import-presence/preview', formData)
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.importRows = Array.isArray(result) ? result : [];
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to preview presence file.';
                    this.importError = message;
                })
                .finally(() => {
                    this.importSubmitting = false;
                });
        },
        uploadImportPresence() {
            if (this.importSubmitting) {
                return;
            }
            if (!this.importFile || !this.importActivity || !this.importActivity.id) {
                this.importError = 'File and activity are required.';
                return;
            }

            this.importSubmitting = true;
            this.importError = '';

            const formData = new FormData();
            formData.append('file', this.importFile);

            return Repository.post(`/api/activities/${this.importActivity.id}/import-presence`, formData)
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    if (result && Array.isArray(result.rows)) {
                        this.importRows = result.rows;
                    }
                    this.importResult = result;
                    this.$showToast('Presence imported successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to import presence file.';
                    this.importError = message;
                })
                .finally(() => {
                    this.importSubmitting = false;
                });
        },
        matchLabel(row) {
            if (row.student_matched && row.lecture_matched) {
                return 'Matched (Student & Lecture)';
            }
            if (row.student_matched) {
                return 'Matched (Student)';
            }
            if (row.lecture_matched) {
                return 'Matched (Lecture)';
            }
            return 'No match';
        },
    },
};
</script>
