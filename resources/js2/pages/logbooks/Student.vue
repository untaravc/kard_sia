<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-sky-600">Logbook Monitoring</div>
                <h1 class="text-2xl font-semibold text-ink">Student Logbook</h1>
            </div>
            <router-link
                class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-2 text-sm text-sky-700 hover:bg-sky-100"
                to="/blu/dashboard-student"
            >
                Back
            </router-link>
        </header>

        <section class="rounded-2xl border border-sky-100 bg-gradient-to-br from-sky-50 via-white to-emerald-50 p-5">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
                <aside class="flex max-h-[75vh] w-full flex-col gap-4 overflow-y-auto lg:w-80 lg:shrink-0">
                    <div class="rounded-2xl border border-border bg-white">
                        <div class="border-b border-sky-100 bg-sky-50/60 px-4 py-3 text-sm font-semibold text-sky-900">
                            Log Book
                        </div>
                        <div class="p-4">
                            <input
                                v-model.trim="filterStase"
                                type="text"
                                placeholder="Cari stase.."
                                class="w-full rounded-xl border border-sky-200 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-300/50"
                            />
                        </div>
                        <div v-if="errorMessage" class="px-4 pb-3 text-xs text-rose-600">
                            {{ errorMessage }}
                        </div>
                        <div v-if="loading" class="px-4 pb-4 text-xs text-muted">
                            Loading stase list...
                        </div>
                        <div v-else-if="filteredStaseLogs.length === 0" class="px-4 pb-4 text-xs text-muted">
                            Tidak ada stase.
                        </div>
                        <div v-else class="grid gap-2 px-4 pb-4">
                            <div
                                v-for="staseLog in filteredStaseLogs"
                                :key="staseLog.id"
                                class="rounded-xl border border-border px-3 py-2"
                                :class="selectedStaseId === staseLog.stase_id ? 'bg-primary/10 border-primary/20' : 'bg-white'"
                            >
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-ink truncate">
                                        {{ staseLabel(staseLog) }}
                                    </div>
                                    <div class="mt-1 text-xs text-muted">
                                        {{ formatDate(staseLog.start_date) }} - {{ formatDate(staseLog.end_date) }}
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    class="mt-2 w-full rounded-lg bg-sky-100 px-3 py-1.5 text-xs font-semibold text-sky-800 hover:bg-sky-200"
                                    @click="openLogbook(staseLog)"
                                >
                                    Logbook
                                </button>
                            </div>
                        </div>
                    </div>
                </aside>

                <div class="flex min-w-0 flex-1 flex-col gap-4 overflow-y-auto max-h-[75vh]">
                    <section class="relative rounded-2xl border border-sky-100 bg-white">
                        <div class="flex items-center justify-between border-b border-sky-100 bg-sky-50/60 px-4 py-3">
                            <div class="text-sm font-semibold text-sky-900">
                                Skill Achievements
                            </div>
                            <div class="text-xs text-muted">
                                {{ selectedStaseName || '-' }}
                            </div>
                        </div>
                        <div v-if="optionError" class="border-b border-rose-100 bg-rose-50 px-4 py-3 text-xs text-rose-600">
                            {{ optionError }}
                        </div>
                        <div v-if="optionLoading" class="px-4 py-6 text-xs text-muted">
                            Loading logbook options...
                        </div>
                        <div v-else-if="!selectedStaseId" class="px-4 py-6 text-sm text-muted">
                            Pilih stase untuk melihat logbook.
                        </div>
                        <div v-else-if="staseSkills.length === 0" class="px-4 py-6 text-sm text-muted">
                            Tidak ada data logbook.
                        </div>
                        <div v-else class="grid gap-3 p-4 sm:grid-cols-2 xl:grid-cols-3">
                            <div v-for="skill in staseSkills" :key="skill.id" class="rounded-xl border border-sky-100 bg-gradient-to-br from-sky-50 to-white p-3">
                                <div class="text-sm font-semibold text-sky-900">{{ skill.name }}</div>
                                <div class="text-xs text-muted">
                                    {{ skill.count || 0 }} / {{ skill.desc || '-' }}
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="relative rounded-2xl border border-emerald-100 bg-white">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-emerald-100 bg-emerald-50/60 px-4 py-3">
                            <div class="text-sm font-semibold text-emerald-900">
                                {{ selectedStaseName || '-' }}
                            </div>
                            <div class="text-xs text-muted">
                                <a
                                    v-if="printLogbookUrl"
                                    :href="printLogbookUrl"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-emerald-700 hover:bg-emerald-100"
                                >
                                    <Icon icon="mdi:printer" class="h-4 w-4" />
                                    Print
                                </a>
                            </div>
                        </div>
                        <div v-if="logbookError" class="border-b border-rose-100 bg-rose-50 px-4 py-3 text-xs text-rose-600">
                            {{ logbookError }}
                        </div>
                        <div v-if="logbookLoading" class="px-4 py-6 text-xs text-muted">
                            Loading logbook entries...
                        </div>
                        <div v-else-if="!selectedStaseId" class="px-4 py-6 text-sm text-muted">
                            Pilih stase untuk melihat logbook.
                        </div>
                        <div v-else-if="logbookGroups.length === 0" class="px-4 py-6 text-sm text-muted">
                            Belum ada data logbook.
                        </div>
                        <div v-else class="divide-y divide-border">
                            <div v-for="book in logbookGroups" :key="book.id" class="p-4">
                                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                                    <div class="text-sm font-semibold text-emerald-900">
                                        {{ book.name || 'Logbook' }}
                                    </div>
                                    <button
                                        v-if="parseDescKeys(book).length"
                                        type="button"
                                        class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs text-emerald-700 hover:bg-emerald-100"
                                        @click="openBulkModal(book)"
                                    >
                                        Add New
                                    </button>
                                </div>
                                <div v-if="book.logbook && book.logbook.length && parseDescKeys(book).length" class="overflow-x-auto">
                                    <table class="min-w-full text-xs">
                                        <thead class="bg-slate-50 text-left text-[11px] uppercase tracking-[0.2em] text-muted">
                                            <tr>
                                                <th class="px-3 py-2">Tanggal</th>
                                                <th v-for="(desc, idx) in parseDescLabels(book)" :key="`${book.id}-desc-${idx}`" class="px-3 py-2">
                                                    {{ desc }}
                                                </th>
                                                <th class="px-3 py-2">Mengetahui</th>
                                                <th class="px-3 py-2">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-border">
                                            <tr v-for="log in book.logbook" :key="log.id">
                                                <td class="px-3 py-2">{{ formatDate(log.date) }}</td>
                                                <td v-for="(key, idx) in parseDescKeys(book)" :key="`${log.id}-field-${idx}`" class="px-3 py-2">
                                                    {{ log[key] || '-' }}
                                                </td>
                                                <td class="px-3 py-2">
                                                    {{ log.lecture ? log.lecture.name : '-' }}
                                                </td>
                                                <td class="px-3 py-2">
                                                    <div class="relative">
                                                        <button
                                                            type="button"
                                                            class="rounded-lg border border-emerald-200 bg-emerald-50 px-2 py-1 text-[11px] text-emerald-700 hover:bg-emerald-100"
                                                            @click.stop="toggleMenu(log.id)"
                                                        >
                                                            Actions
                                                        </button>
                                                        <div
                                                            v-if="openMenuId === log.id"
                                                            class="absolute right-0 z-10 mt-2 w-32 rounded-lg border border-emerald-100 bg-white shadow-md"
                                                        >
                                                            <button
                                                                type="button"
                                                                class="block w-full px-3 py-2 text-left text-xs text-emerald-800 hover:bg-emerald-50"
                                                                @click="openEditLog(book, log)"
                                                            >
                                                                Edit
                                                            </button>
                                                            <button
                                                                type="button"
                                                                class="block w-full px-3 py-2 text-left text-xs text-rose-600 hover:bg-rose-50"
                                                                @click="deleteLog(log)"
                                                            >
                                                                Delete
                                                            </button>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-else class="text-xs text-muted">
                                    Belum ada data.
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </section>

        <Modal
            :open="bulkModalOpen"
            title="Tambah Log Book"
            eyebrow="Bulk Entry"
            size="xxxl"
            :close-on-backdrop="false"
            @close="closeBulkModal"
        >
            <form class="grid gap-4" @submit.prevent="submitBulkLog">
                <div class="grid gap-4 md:grid-cols-3">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Tanggal</span>
                        <input
                            v-model="bulkForm.date"
                            type="date"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Tipe</span>
                        <input
                            v-model="bulkForm.type"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                            readonly
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Supervisor</span>
                        <select
                            v-model="bulkForm.lecture_id"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        >
                            <option value="">Diluar Kardiologi</option>
                            <option v-for="lecture in lectureOptions" :key="lecture.id" :value="lecture.id">
                                {{ lecture.name }}
                            </option>
                        </select>
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Kategori</span>
                        <select
                            v-model="bulkForm.category"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        >
                            <option value="">Pilih kategori</option>
                            <option v-for="cat in logbookCategories" :key="cat.id" :value="cat.value">
                                {{ cat.name }}
                            </option>
                        </select>
                    </label>
                </div>

                <div class="grid gap-3">
                    <div class="flex items-center justify-between">
                        <div class="text-xs uppercase tracking-[0.2em] text-muted">Detail</div>
                        <button
                            type="button"
                            class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted hover:bg-slate-50"
                            @click="addBulkRow"
                        >
                            Add Row
                        </button>
                    </div>

                    <div v-for="(row, rowIndex) in bulkRows" :key="`bulk-row-${rowIndex}`" class="rounded-xl border border-border p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <div class="text-xs text-muted">Row {{ rowIndex + 1 }}</div>
                            <button
                                v-if="bulkRows.length > 1"
                                type="button"
                                class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs text-rose-600 hover:bg-rose-50"
                                @click="removeBulkRow(rowIndex)"
                            >
                                Remove
                            </button>
                        </div>
                        <div class="grid gap-3 md:grid-cols-3">
                            <label
                                v-for="(label, idx) in bulkLabels"
                                :key="`bulk-${rowIndex}-${idx}`"
                                class="grid gap-2 text-sm"
                            >
                                <span class="text-muted">{{ label }}</span>
                                <textarea
                                    v-model.trim="bulkRows[rowIndex][bulkKeys[idx]]"
                                    rows="2"
                                    class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                                ></textarea>
                            </label>
                        </div>
                        <div v-if="staseSkills.length" class="mt-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-muted">
                                Ketrampilan
                            </div>
                            <div class="mt-2 grid gap-2 text-xs sm:grid-cols-2">
                                <label
                                    v-for="skill in staseSkills"
                                    :key="`skill-${rowIndex}-${skill.id}`"
                                    class="flex items-center gap-2"
                                >
                                    <input
                                        v-model="bulkRows[rowIndex].skills[skill.id]"
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-border text-primary focus:ring-primary/30"
                                    />
                                    <span>{{ skill.name }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="bulkError" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ bulkError }}
                </div>

                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="submit"
                    :disabled="bulkSubmitting"
                >
                    {{ bulkSubmitting ? 'Saving...' : 'Simpan' }}
                </button>
            </form>
        </Modal>

        <Modal
            :open="editModalOpen"
            title="Edit Log Book"
            eyebrow="Update Entry"
            size="lg"
            :close-on-backdrop="false"
            @close="closeEditModal"
        >
            <form class="grid gap-4" @submit.prevent="submitEditLog">
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Tanggal</span>
                        <input
                            v-model="editForm.date"
                            type="date"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Tipe</span>
                        <input
                            v-model="editForm.type"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                            readonly
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Supervisor</span>
                        <select
                            v-model="editForm.lecture_id"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        >
                            <option value="">Diluar Kardiologi</option>
                            <option v-for="lecture in lectureOptions" :key="lecture.id" :value="lecture.id">
                                {{ lecture.name }}
                            </option>
                        </select>
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Kategori</span>
                        <select
                            v-model="editForm.category"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        >
                            <option value="">Pilih kategori</option>
                            <option v-for="cat in logbookCategories" :key="cat.id" :value="cat.value">
                                {{ cat.name }}
                            </option>
                        </select>
                    </label>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <label
                        v-for="(label, idx) in editLabels"
                        :key="`edit-${idx}`"
                        class="grid gap-2 text-sm"
                    >
                        <span class="text-muted">{{ label }}</span>
                        <textarea
                            v-model.trim="editForm[editKeys[idx]]"
                            rows="2"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        ></textarea>
                    </label>
                </div>

                <div v-if="staseSkills.length" class="mt-1">
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-muted">
                        Ketrampilan
                    </div>
                    <div class="mt-2 grid gap-2 text-xs sm:grid-cols-2">
                        <label
                            v-for="skill in staseSkills"
                            :key="`edit-skill-${skill.id}`"
                            class="flex items-center gap-2"
                        >
                            <input
                                v-model="editForm.skills[skill.id]"
                                type="checkbox"
                                class="h-4 w-4 rounded border-border text-primary focus:ring-primary/30"
                            />
                            <span>{{ skill.name }}</span>
                        </label>
                    </div>
                </div>

                <div v-if="editError" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ editError }}
                </div>

                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="submit"
                    :disabled="editSubmitting"
                >
                    {{ editSubmitting ? 'Saving...' : 'Update' }}
                </button>
            </form>
        </Modal>
    </div>
</template>

<script>
import { Icon } from '../../icons';
import Modal from '../../components/Modal.vue';
import Repository from '../../repository';
import { useAuthStore } from '../../stores/auth';

export default {
    components: {
        Icon,
        Modal,
    },
    data() {
        return {
            loading: false,
            errorMessage: '',
            staseLogs: [],
            filterStase: '',
            selectedStaseId: null,
            selectedStaseName: '',
            optionLoading: false,
            optionError: '',
            staseTypes: [],
            staseSkills: [],
            logbookLoading: false,
            logbookError: '',
            logbookGroups: [],
            logbookCategories: [],
            bulkModalOpen: false,
            bulkSubmitting: false,
            bulkError: '',
            bulkForm: {
                date: '',
                type: '',
                category: '',
                lecture_id: '',
            },
            bulkRows: [],
            bulkBook: null,
            lectureOptions: [],
            openMenuId: null,
            editModalOpen: false,
            editSubmitting: false,
            editError: '',
            editForm: {
                id: null,
                stase_id: null,
                type: '',
                category: '',
                lecture_id: '',
                date: '',
                skills: {},
            },
            editBook: null,
            authStore: null,
        };
    },
    computed: {
        studentId() {
            const storeUser = this.authStore ? this.authStore.user : null;
            if (storeUser && storeUser.log_as_auth_id) {
                return storeUser.log_as_auth_id;
            }
            if (storeUser && storeUser.auth_id) {
                return storeUser.auth_id;
            }
            return this.$route.params.student_id || null;
        },
        printLogbookUrl() {
            if (!this.studentId || !this.selectedStaseId) {
                return null;
            }
            return `/print/logbook/${this.studentId}/${this.selectedStaseId}`;
        },
        filteredStaseLogs() {
            if (!this.filterStase) {
                return this.staseLogs;
            }
            const keyword = this.filterStase.toLowerCase();
            return this.staseLogs.filter((staseLog) => this.staseLabel(staseLog).toLowerCase().includes(keyword));
        },
        bulkKeys() {
            return this.bulkBook ? this.parseDescKeys(this.bulkBook) : [];
        },
        bulkLabels() {
            return this.bulkBook ? this.parseDescLabels(this.bulkBook) : [];
        },
        editKeys() {
            return this.editBook ? this.parseDescKeys(this.editBook) : [];
        },
        editLabels() {
            return this.editBook ? this.parseDescLabels(this.editBook) : [];
        },
    },
    created() {
        this.fetchStaseList();
        this.fetchLectures();
    },
    mounted() {
        this.authStore = useAuthStore();
        document.addEventListener('click', this.closeMenu);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.closeMenu);
    },
    methods: {
        fetchLectures() {
            return Repository.get('/api/lecture-list')
                .then((response) => {
                    const data = response && response.data ? response.data.result : null;
                    this.lectureOptions = Array.isArray(data) ? data : [];
                })
                .catch(() => {
                    this.lectureOptions = [];
                });
        },
        toggleMenu(logId) {
            this.openMenuId = this.openMenuId === logId ? null : logId;
        },
        closeMenu() {
            this.openMenuId = null;
        },
        fetchStaseList() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get('/api/stase-list', {
                params: this.studentId ? { student_id: this.studentId } : {},
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.staseLogs = Array.isArray(result) ? result : [];
                })
                .catch(() => {
                    this.staseLogs = [];
                    this.errorMessage = 'Failed to load stase list.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        staseLabel(staseLog) {
            if (staseLog && staseLog.stase && staseLog.stase.name) {
                return staseLog.stase.name;
            }
            if (staseLog && staseLog.stase_id) {
                return `Stase ${staseLog.stase_id}`;
            }
            return 'Stase';
        },
        openLogbook(staseLog) {
            this.selectedStaseId = staseLog ? staseLog.stase_id : null;
            this.selectedStaseName = staseLog ? this.staseLabel(staseLog) : '';
            if (this.selectedStaseId) {
                this.fetchStaseOptions(this.selectedStaseId);
                this.fetchStudentLogs(this.selectedStaseId);
            }
        },
        fetchStaseOptions(staseId) {
            this.optionLoading = true;
            this.optionError = '';

            return Repository.get(`/api/stase-option/${staseId}`, {
                params: this.studentId ? { student_id: this.studentId } : {},
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.staseTypes = result && Array.isArray(result.types) ? result.types : [];
                    this.staseSkills = result && Array.isArray(result.skills) ? result.skills : [];
                })
                .catch(() => {
                    this.staseTypes = [];
                    this.staseSkills = [];
                    this.optionError = 'Failed to load logbook options.';
                })
                .finally(() => {
                    this.optionLoading = false;
                });
        },
        fetchStudentLogs(staseId) {
            this.logbookLoading = true;
            this.logbookError = '';

            return Repository.get(`/api/student-logs/${staseId}`, {
                params: this.studentId ? { student_id: this.studentId } : {},
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.logbookGroups = Array.isArray(result) ? result : [];
                    const categories = response && response.data ? response.data.categories : null;
                    this.logbookCategories = Array.isArray(categories) ? categories : [];
                })
                .catch(() => {
                    this.logbookGroups = [];
                    this.logbookCategories = [];
                    this.logbookError = 'Failed to load logbook entries.';
                })
                .finally(() => {
                    this.logbookLoading = false;
                });
        },
        parseDescKeys(book) {
            const desc = book ? book.parse_desc : null;
            if (desc && typeof desc === 'object' && !Array.isArray(desc)) {
                return Object.keys(desc);
            }
            return [];
        },
        parseDescLabels(book) {
            const desc = book ? book.parse_desc : null;
            if (desc && typeof desc === 'object' && !Array.isArray(desc)) {
                return Object.values(desc);
            }
            return [];
        },
        openBulkModal(book) {
            this.bulkBook = book;
            this.bulkModalOpen = true;
            this.bulkError = '';
            this.bulkSubmitting = false;
            this.bulkForm.type = book && book.value ? book.value : '';
            this.bulkForm.category = '';
            this.bulkForm.lecture_id = '';
            this.bulkForm.date = this.getToday();
            this.bulkRows = [this.createBulkRow(book)];
        },
        closeBulkModal() {
            this.bulkModalOpen = false;
            this.bulkBook = null;
            this.bulkRows = [];
        },
        createBulkRow(book) {
            const keys = this.parseDescKeys(book);
            return keys.reduce((acc, key) => {
                acc[key] = '';
                return acc;
            }, { skills: {} });
        },
        addBulkRow() {
            if (!this.bulkBook) {
                return;
            }
            this.bulkRows.push(this.createBulkRow(this.bulkBook));
        },
        removeBulkRow(index) {
            this.bulkRows.splice(index, 1);
        },
        getToday() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        submitBulkLog() {
            if (!this.selectedStaseId || !this.bulkForm.type) {
                this.bulkError = 'Stase atau tipe logbook belum dipilih.';
                return Promise.resolve();
            }

            this.bulkSubmitting = true;
            this.bulkError = '';

            const payload = {
                stase_id: this.selectedStaseId,
                type: this.bulkForm.type,
                category: this.bulkForm.category,
                lecture_id: this.bulkForm.lecture_id,
                date: this.bulkForm.date,
                data: this.bulkRows.map((row) => ({ ...row })),
            };

            return Repository.post('/api/logbooks/bulk', payload)
                .then(() => {
                    this.closeBulkModal();
                    this.fetchStudentLogs(this.selectedStaseId);
                    this.fetchStaseOptions(this.selectedStaseId);
                })
                .catch(() => {
                    this.bulkError = 'Failed to save logbook entries.';
                })
                .finally(() => {
                    this.bulkSubmitting = false;
                });
        },
        openEditLog(book, log) {
            this.openMenuId = null;
            this.editBook = book;
            this.editModalOpen = true;
            this.editSubmitting = false;
            this.editError = '';
            this.editForm.id = log.id;
            this.editForm.stase_id = log.stase_id;
            this.editForm.type = log.type || '';
            this.editForm.category = log.category || '';
            this.editForm.lecture_id = log.lecture_id || '';
            this.editForm.date = log.date || this.getToday();
            this.editForm.skills = log.skills ? { ...log.skills } : {};
            this.editKeys.forEach((key) => {
                this.editForm[key] = log[key] || '';
            });
        },
        closeEditModal() {
            this.editModalOpen = false;
            this.editBook = null;
        },
        submitEditLog() {
            if (!this.editForm.id) {
                return Promise.resolve();
            }

            this.editSubmitting = true;
            this.editError = '';

            const payload = {
                stase_id: this.editForm.stase_id,
                type: this.editForm.type,
                category: this.editForm.category,
                lecture_id: this.editForm.lecture_id,
                date: this.editForm.date,
                skills: this.editForm.skills,
            };

            this.editKeys.forEach((key) => {
                payload[key] = this.editForm[key] || '';
            });

            return Repository.put(`/api/logbooks/${this.editForm.id}`, payload)
                .then(() => {
                    this.closeEditModal();
                    this.fetchStudentLogs(this.selectedStaseId);
                    this.fetchStaseOptions(this.selectedStaseId);
                })
                .catch(() => {
                    this.editError = 'Failed to update logbook entry.';
                })
                .finally(() => {
                    this.editSubmitting = false;
                });
        },
        deleteLog(log) {
            this.openMenuId = null;
            if (!window.confirm('Delete this logbook entry?')) {
                return;
            }

            Repository.delete(`/api/logbooks/${log.id}`)
                .then(() => {
                    this.fetchStudentLogs(this.selectedStaseId);
                    this.fetchStaseOptions(this.selectedStaseId);
                })
                .catch(() => {
                    this.logbookError = 'Failed to delete logbook entry.';
                });
        },
        formatDate(value) {
            if (!value) {
                return '-';
            }
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return value;
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
