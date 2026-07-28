<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Logbook Approval</div>
                <h1 class="text-2xl font-semibold text-ink">Logbooks</h1>
            </div>
            <router-link
                class="rounded-xl border border-border bg-white/80 px-4 py-2 text-sm text-muted shadow-sm backdrop-blur"
                to="/blu/dashboard-lecture"
            >
                Back
            </router-link>
        </header>

	        <section class="rounded-2xl border border-border bg-panel p-5">
	            <div class="flex flex-wrap items-center gap-3">
	                <div class="flex-1 min-w-[200px]">
	                    <label class="text-xs text-muted">Keyword</label>
                    <input
                        v-model.trim="filters.keyword"
                        type="text"
                        placeholder="Search fields..."
                        @keyup.enter="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="text-xs text-muted">Stase</label>
                    <v-select
                        v-model="filters.stase_id"
                        class="mt-2 w-full"
                        :options="staseOptions"
                        :reduce="(stase) => stase.id"
                        :get-option-label="getStaseOptionLabel"
                        :clearable="true"
                        placeholder="All stases"
                        @input="applyFilter"
                    />
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="text-xs text-muted">Student</label>
                    <v-select
                        v-model="filters.student_id"
                        class="mt-2 w-full"
                        :options="studentOptions"
                        :reduce="(student) => student.id"
                        :get-option-label="getStudentOptionLabel"
                        :clearable="true"
                        placeholder="All students"
                        @input="applyFilter"
                    />
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="text-xs text-muted">Status</label>
                    <select
                        v-model.number="filters.status"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        @change="applyFilter"
                    >
                        <option value="">All</option>
                        <option :value="0">Pending</option>
                        <option :value="1">Approved</option>
                        <option :value="2">Rejected</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 flex justify-end">
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

	        <section class="rounded-2xl border border-border bg-panel px-5 py-4">
	            <div class="flex flex-wrap items-center gap-3">
	                <div class="text-sm text-ink">
	                    Approval All Filtered Logbook ({{ filteredCount }})
	                </div>
	                <button
	                    class="ml-auto rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
	                    type="button"
	                    :disabled="approving || filteredCount === 0"
	                    @click="openApproveModal"
	                >
	                    {{ approving ? 'Approving...' : 'Approve' }}
	                </button>
	            </div>
	            <div v-if="approveError" class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
	                {{ approveError }}
	            </div>
	        </section>

	        <section class="relative rounded-2xl border border-border bg-panel">
	            <Loading :active="loading" :is-full-page="false" />
	            <div class="flex items-center justify-between border-b border-border px-5 py-4">
	                <div class="font-semibold">Logbooks</div>
                <div class="text-xs text-muted" v-if="pagination.total">
                    {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }}
                </div>
            </div>
            <div
                v-if="errorMessage && !modalOpen"
                class="border-b border-rose-100 bg-rose-50 px-5 py-3 text-xs text-rose-600"
            >
                {{ errorMessage }}
            </div>
            <div class="divide-y divide-border">
                <div v-if="!loading && logbooks.length === 0" class="px-5 py-6 text-sm text-muted">
                    No logbooks found.
                </div>
                <div
                    v-for="(logbook, index) in logbooks"
                    :key="logbook.id"
                    class="flex flex-wrap items-center gap-3 px-5 py-4"
                >
                    <div class="w-8 text-sm font-semibold text-muted">
                        {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="font-semibold text-ink">{{ logbook.form_option_name || logbook.type || 'Logbook' }}</div>
                            <span
                                class="rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                :class="statusBadgeClass(logbook.status)"
                            >
                                {{ statusLabel(logbook.status) }}
                            </span>
                        </div>
                        <div class="text-xs text-muted">
                            <span v-if="logbook.stase">{{ logbook.stase.name }}</span>
                            <span v-else-if="logbook.stase_id">Stase ID: {{ logbook.stase_id }}</span>
                            <span v-if="logbook.student_name">• Student: {{ logbook.student_name }}</span>
                            <span v-else-if="logbook.student_id">• Student ID: {{ logbook.student_id }}</span>
                            <span v-if="logbook.category">• Category: {{ logbook.category }}</span>
                            <span v-if="logbook.date">• {{ logbook.date }}</span>
                        </div>
                        <div class="text-xs text-muted" v-if="logbook.field_1">
                            {{ logbook.field_1 }}
                        </div>
                    </div>
                    <button
                        class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                        type="button"
                        @click="openView(logbook)"
                    >
                        View
                    </button>
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
            :open="modalOpen"
            title="Logbook Detail"
            eyebrow="View entry"
            size="lg"
            @close="closeModal"
        >
            <div v-if="selectedLogbook" class="grid gap-4 text-sm">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="grid gap-1">
                        <div class="text-xs text-muted">Stase</div>
                        <div class="font-medium text-ink">
                            <span v-if="selectedLogbook.stase">{{ selectedLogbook.stase.name }}</span>
                            <span v-else-if="selectedLogbook.stase_id">Stase {{ selectedLogbook.stase_id }}</span>
                            <span v-else>-</span>
                        </div>
                    </div>
                    <div class="grid gap-1">
                        <div class="text-xs text-muted">Date</div>
                        <div class="font-medium text-ink">{{ selectedLogbook.date || '-' }}</div>
                    </div>
                    <div class="grid gap-1">
                        <div class="text-xs text-muted">Type</div>
                        <div class="font-medium text-ink">
                            {{ selectedLogbook.form_option_name || selectedLogbook.type || '-' }}
                        </div>
                    </div>
                    <div class="grid gap-1">
                        <div class="text-xs text-muted">Category</div>
                        <div class="font-medium text-ink">{{ selectedLogbook.category || '-' }}</div>
                    </div>
                    <div class="grid gap-1">
                        <div class="text-xs text-muted">Student</div>
                        <div class="font-medium text-ink">
                            {{ selectedLogbook.student_name || selectedLogbook.student_id || '-' }}
                        </div>
                    </div>
                    <div class="grid gap-1">
                        <div class="text-xs text-muted">Status</div>
                        <div class="font-medium text-ink">{{ statusLabel(selectedLogbook.status) }}</div>
                    </div>
                </div>

                <div v-for="i in 6" :key="`field-${i}`" class="grid gap-1" v-if="selectedLogbook[`field_${i}`]">
                    <div class="text-xs text-muted">Field {{ i }}</div>
                    <div class="whitespace-pre-wrap rounded-xl border border-border bg-panel px-3 py-2 text-ink">
                        {{ selectedLogbook[`field_${i}`] }}
                    </div>
                </div>
	            </div>
	        </Modal>

	        <Modal
	            :open="approveModalOpen"
	            title="Approve Logbooks"
	            eyebrow="Confirmation"
	            size="md"
	            @close="closeApproveModal"
	        >
	            <div class="text-sm text-ink">
	                Approve all filtered logbooks ({{ filteredCount }})?
	            </div>
	            <template #footer>
	                <button
	                    class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
	                    type="button"
	                    :disabled="approving"
	                    @click="closeApproveModal"
	                >
	                    Cancel
	                </button>
	                <button
	                    class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
	                    type="button"
	                    :disabled="approving || filteredCount === 0"
	                    @click="approveFiltered"
	                >
	                    {{ approving ? 'Approving...' : 'Approve' }}
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

export default {
    components: {
        Loading,
        Modal,
    },
    mixins: [persistFilters('dashboard-lecture/logbook')],
	    data() {
	        return {
	            baseUrl: '/api/logbooks',
	            logbooks: [],
            pagination: {},
            filters: {
                keyword: '',
                stase_id: null,
                student_id: null,
                status: 0,
                type: '',
                page: 1,
            },
            modalOpen: false,
            approveModalOpen: false,
	            loading: false,
	            errorMessage: '',
	            approving: false,
	            approveError: '',
	            studentOptions: [],
	            staseOptions: [],
	            selectedLogbook: null,
	        };
	    },
	    computed: {
	        filteredCount() {
	            return this.pagination && this.pagination.total ? this.pagination.total : 0;
	        },
	    },
	    created() {
	        this.fetchStases();
	        this.fetchStudents();
	        this.fetchLogbooks();
	    },
    methods: {
        fetchLogbooks() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(this.baseUrl, {
                params: this.filters,
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];

                    this.logbooks = data;
                    this.pagination = result || {};
                })
                .catch(() => {
                    this.logbooks = [];
                    this.pagination = {};
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        applyFilter() {
            this.filters.page = 1;
            this.fetchLogbooks();
        },
        resetFilter() {
            this.filters.keyword = '';
            this.filters.stase_id = null;
            this.filters.student_id = null;
            this.filters.status = 0;
            this.filters.type = '';
            this.filters.page = 1;
            this.fetchLogbooks();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchLogbooks();
        },
        openView(logbook) {
            this.selectedLogbook = logbook || null;
            this.modalOpen = true;
        },
	        closeModal() {
	            this.modalOpen = false;
	            this.errorMessage = '';
	            this.selectedLogbook = null;
	        },
	        openApproveModal() {
	            this.approveError = '';
	            this.approveModalOpen = true;
	        },
	        closeApproveModal() {
	            if (this.approving) {
	                return;
	            }
	            this.approveModalOpen = false;
	            this.approveError = '';
	        },
	        approveFiltered() {
	            if (this.approving || this.filteredCount === 0) {
	                return;
	            }

	            this.approving = true;
	            this.approveError = '';

            const payload = {
                keyword: this.filters.keyword,
                stase_id: this.filters.stase_id,
                student_id: this.filters.student_id,
                status: this.filters.status,
                type: this.filters.type,
            };

	            return Repository.post('/api/logbooks/approve', payload)
	                .then((response) => {
	                    const result = response && response.data ? response.data.result : null;
	                    const updated = result && result.updated ? result.updated : 0;
	                    this.$showToast(`Approved ${updated} logbook(s).`);
	                    this.approveModalOpen = false;
	                    this.fetchLogbooks();
	                })
	                .catch((error) => {
	                    const message = error && error.response && error.response.data
	                        ? error.response.data.text
	                        : 'Failed to approve logbooks.';
	                    this.approveError = message;
	                })
	                .finally(() => {
	                    this.approving = false;
	                });
	        },
	        fetchStases() {
	            return Repository.get('/api/stase-list-all')
	                .then((response) => {
	                    const data = response && response.data ? response.data.result : null;
                    this.staseOptions = Array.isArray(data) ? data : [];
                })
                .catch(() => {
                    this.staseOptions = [];
                });
        },
        fetchStudents() {
            return Repository.get('/api/student-list')
                .then((response) => {
                    const data = response && response.data ? response.data.result : null;
                    this.studentOptions = Array.isArray(data) ? data : [];
                })
                .catch(() => {
                    this.studentOptions = [];
                });
        },
        getStaseOptionLabel(staseLog) {
            if (!staseLog) {
                return '';
            }
            if (staseLog.name) {
                return staseLog.name;
            }
            return 'Stase';
        },
        getStudentOptionLabel(student) {
            if (!student) {
                return '';
            }
            if (student.year && student.name) {
                return `${student.year} - ${student.name}`;
            }
            return student.name || 'Student';
        },
        statusLabel(status) {
            const value = Number(status);
            if (value === 1) {
                return 'Approved';
            }
            if (value === 2) {
                return 'Rejected';
            }
            return 'Pending';
        },
        statusBadgeClass(status) {
            const value = Number(status);
            if (value === 1) {
                return 'bg-emerald-50 text-emerald-700 border border-emerald-100';
            }
            if (value === 2) {
                return 'bg-rose-50 text-rose-700 border border-rose-100';
            }
            return 'bg-amber-50 text-amber-700 border border-amber-100';
        },
    },
};
</script>
