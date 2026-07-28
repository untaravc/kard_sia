<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Logbook Management</div>
                <h1 class="text-2xl font-semibold text-ink">Logbooks</h1>
            </div>
        </header>

	        <section class="rounded-2xl border border-border bg-panel p-5">
	            <div class="flex flex-wrap items-center gap-3">
	                <div class="flex-1 min-w-[200px]">
	                    <label class="text-xs text-muted">Keyword</label>
	                    <input v-model.trim="filters.keyword" type="text" placeholder="Search fields..."
	                        @keyup.enter="applyFilter"
	                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
	                </div>
	                <div class="flex-1 min-w-[200px]">
		                    <label class="text-xs text-muted">Stase</label>
		                    <v-select v-model="filters.stase_id" class="mt-2 w-full" :options="staseOptions"
		                        :reduce="(stase) => stase.id" :get-option-label="getStaseOptionLabel" :clearable="true"
		                        placeholder="All stases" @input="applyFilter" />
		                </div>
	                <div class="flex-1 min-w-[200px]">
	                    <label class="text-xs text-muted">Lecture</label>
	                    <v-select v-model="filters.lecture_id" class="mt-2 w-full" :options="lectureOptions"
	                        :reduce="(lecture) => lecture.id" label="name" :clearable="true"
	                        placeholder="All lectures" @input="applyFilter" />
	                </div>
	                <div class="flex-1 min-w-[200px]">
	                    <label class="text-xs text-muted">Student</label>
	                    <v-select v-model="filters.student_id" class="mt-2 w-full" :options="studentOptions"
	                        :reduce="(student) => student.id" :get-option-label="getStudentOptionLabel" :clearable="true"
	                        placeholder="All students" @input="applyFilter" />
	                </div>

	            </div>
	            <div class="mt-4 flex justify-end">
	                <div class="flex items-end gap-2">
                    <button class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white" type="button"
                        @click="applyFilter">
                        Search
                    </button>
                    <button class="rounded-xl border border-border px-4 py-2 text-sm text-muted" type="button"
                        @click="resetFilter">
                        Reset
                    </button>
                </div>
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
            <div v-if="errorMessage && !modalOpen"
                class="border-b border-rose-100 bg-rose-50 px-5 py-3 text-xs text-rose-600">
                {{ errorMessage }}
            </div>
            <div class="divide-y divide-border">
                <div v-if="!loading && logbooks.length === 0" class="px-5 py-6 text-sm text-muted">
                    No logbooks found.
                </div>
                <div v-for="(logbook, index) in logbooks" :key="logbook.id"
                    class="flex flex-wrap items-center gap-3 px-5 py-4">
                    <div class="w-8 text-sm font-semibold text-muted">
                        {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                    </div>
	                    <div class="flex-1">
	                        <div class="font-semibold text-ink">{{ logbook.form_option_name || logbook.type || 'Logbook' }}</div>
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
                        <div class="text-xs text-muted" v-if="logbook.lecture">
                            Supervisor: {{ logbook.lecture.name }}
                        </div>
                    </div>
                    <div class="relative" @click.stop>
                        <button class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted" type="button"
                            @click="toggleActionMenu(logbook.id)">
                            Action
                        </button>
                        <div v-if="actionMenuLogbookId === logbook.id"
                            class="absolute right-0 top-full z-10 mt-2 w-40 overflow-hidden rounded-xl border border-border bg-white shadow-lg">
                            <button class="w-full px-4 py-2 text-left text-xs text-muted hover:bg-panel" type="button"
                                @click="openView(logbook)">
                                View
                            </button>
                            <button class="w-full px-4 py-2 text-left text-xs text-rose-600 hover:bg-rose-50"
                                type="button" @click="actionMenuLogbookId = null; deleteLogbook(logbook)">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between border-t border-border px-5 py-4 text-xs text-muted">
                <button class="rounded-lg border border-border px-3 py-1.5" type="button"
                    :disabled="pagination.current_page <= 1" @click="changePage(pagination.current_page - 1)">
                    Prev
                </button>
                <div>Page {{ pagination.current_page || 1 }} / {{ pagination.last_page || 1 }}</div>
                <button class="rounded-lg border border-border px-3 py-1.5" type="button"
                    :disabled="pagination.current_page >= pagination.last_page"
                    @click="changePage(pagination.current_page + 1)">
                    Next
                </button>
            </div>
        </section>

        <Modal :open="modalOpen" title="Logbook Detail" eyebrow="View entry" size="lg" @close="closeModal">
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
	                        <div class="font-medium text-ink">{{ selectedLogbook.form_option_name || selectedLogbook.type || '-' }}</div>
	                    </div>
                    <div class="grid gap-1">
                        <div class="text-xs text-muted">Category</div>
                        <div class="font-medium text-ink">{{ selectedLogbook.category || '-' }}</div>
                    </div>
	                    <div class="grid gap-1">
	                        <div class="text-xs text-muted">Supervisor</div>
	                        <div class="font-medium text-ink">
	                            {{ selectedLogbook.lecture ? selectedLogbook.lecture.name : '-' }}
	                        </div>
	                    </div>
	                    <div class="grid gap-1">
	                        <div class="text-xs text-muted">Student</div>
	                        <div class="font-medium text-ink">
	                            {{ selectedLogbook.student_name || selectedLogbook.student_id || '-' }}
	                        </div>
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
    mixins: [persistFilters('logbooks')],
	    data() {
	        return {
	            baseUrl: '/api/logbooks',
	            logbooks: [],
	            pagination: {},
	            actionMenuLogbookId: null,
	            filters: {
	                keyword: '',
	                stase_id: null,
	                lecture_id: null,
	                student_id: null,
	                type: '',
	                page: 1,
	            },
	            modalOpen: false,
	            loading: false,
	            errorMessage: '',
	            lectureOptions: [],
	            studentOptions: [],
	            staseOptions: [],
	            selectedLogbook: null,
	        };
	    },
	    created() {
	        this.fetchLectures();
	        this.fetchStases();
	        this.fetchStudents();
	        this.fetchLogbooks();
	    },
    mounted() {
        document.addEventListener('click', this.handleOutsideClick);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.handleOutsideClick);
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
	            this.filters.lecture_id = null;
	            this.filters.student_id = null;
	            this.filters.type = '';
	            this.filters.page = 1;
	            this.fetchLogbooks();
	        },
        changePage(page) {
            this.filters.page = page;
            this.fetchLogbooks();
        },
        handleOutsideClick() {
            this.actionMenuLogbookId = null;
        },
        toggleActionMenu(logbookId) {
            this.actionMenuLogbookId = this.actionMenuLogbookId === logbookId ? null : logbookId;
        },
        openView(logbook) {
            this.selectedLogbook = logbook || null;
            this.modalOpen = true;
            this.actionMenuLogbookId = null;
        },
        closeModal() {
            this.modalOpen = false;
            this.errorMessage = '';
            this.selectedLogbook = null;
        },
	        deleteLogbook(logbook) {
	            const label = logbook.form_option_name || logbook.type || 'Logbook';
	            if (!window.confirm(`Delete logbook ${label}?`)) {
	                return;
	            }

            Repository.delete(`${this.baseUrl}/${logbook.id}`)
                .then(() => {
                    this.fetchLogbooks();
                    this.$showToast('Logbook deleted successfully.');
                })
                .catch(() => {
                    this.errorMessage = 'Failed to delete logbook.';
	                });
	        },
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
	    },
	};
</script>
