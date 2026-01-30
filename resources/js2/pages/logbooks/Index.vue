<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Logbook Management</div>
                <h1 class="text-2xl font-semibold text-ink">Logbooks</h1>
            </div>
            <button
                class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                type="button"
                @click="openCreate"
            >
                Add Logbook
            </button>
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
                    <label class="text-xs text-muted">Type</label>
                    <input
                        v-model.trim="filters.type"
                        type="text"
                        @keyup.enter="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="text-xs text-muted">Stase</label>
                    <select
                        v-model="filters.stase_id"
                        @change="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option :value="null">All stases</option>
                        <option v-for="staseLog in staseOptions" :key="staseLog.id" :value="staseLog.stase_id">
                            {{ staseLog.stase ? staseLog.stase.name : `Stase ${staseLog.stase_id}` }}
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
                        <div class="font-semibold text-ink">{{ logbook.type || 'Logbook' }}</div>
                        <div class="text-xs text-muted">
                            <span v-if="logbook.stase">{{ logbook.stase.name }}</span>
                            <span v-else-if="logbook.stase_id">Stase ID: {{ logbook.stase_id }}</span>
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
                    <div class="flex items-center gap-2">
                        <button
                            class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                            type="button"
                            @click="openEdit(logbook)"
                        >
                            Edit
                        </button>
                        <button
                            class="rounded-lg bg-rose-500/10 px-3 py-1.5 text-xs text-rose-600"
                            type="button"
                            @click="deleteLogbook(logbook)"
                        >
                            Delete
                        </button>
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
            :open="modalOpen"
            :title="editMode ? 'Edit Logbook' : 'Create Logbook'"
            :eyebrow="editMode ? 'Update entry' : 'New entry'"
            size="lg"
            @close="closeModal"
        >
            <form class="grid gap-4" @submit.prevent="submitForm">
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Stase</span>
                        <select
                            v-model="form.stase_id"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                            @change="handleStaseChange"
                        >
                            <option :value="null">Select stase</option>
                            <option v-for="staseLog in staseOptions" :key="staseLog.id" :value="staseLog.stase_id">
                                {{ staseLog.stase ? staseLog.stase.name : `Stase ${staseLog.stase_id}` }}
                            </option>
                        </select>
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Date</span>
                        <input
                            v-model="form.date"
                            type="date"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Type</span>
                        <select
                            v-model="form.type"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                            @change="handleTypeChange"
                        >
                            <option value="">Select type</option>
                            <option v-for="option in staseTypes" :key="option.id" :value="option.value">
                                {{ option.name }}
                            </option>
                        </select>
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Category</span>
                        <select
                            v-model="form.category"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        >
                            <option value="">Select category</option>
                            <option v-for="skill in staseSkills" :key="skill.id" :value="skill.value">
                                {{ skill.name }}
                            </option>
                        </select>
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Lecture</span>
                        <select
                            v-model="form.lecture_id"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        >
                            <option :value="null">Select lecture</option>
                            <option v-for="lecture in lectureOptions" :key="lecture.id" :value="lecture.id">
                                {{ lecture.name }}
                            </option>
                        </select>
                    </label>
                </div>
                <label
                    v-for="(fieldLabel, fieldIndex) in availableFields"
                    :key="fieldLabel + fieldIndex"
                    class="grid gap-2 text-sm"
                >
                    <span class="text-muted">{{ fieldLabel }}</span>
                    <textarea
                        v-model.trim="form[`field_${fieldIndex + 1}`]"
                        rows="2"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    ></textarea>
                </label>
                <div v-if="errorMessage" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ errorMessage }}
                </div>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="submit"
                    :disabled="submitting"
                >
                    {{ submitting ? 'Saving...' : editMode ? 'Update Logbook' : 'Create Logbook' }}
                </button>
            </form>
        </Modal>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Modal from '../../components/Modal.vue';
import Repository from '../../repository';

export default {
    components: {
        Loading,
        Modal,
    },
    data() {
        return {
            baseUrl: '/api/logbooks',
            logbooks: [],
            pagination: {},
            filters: {
                keyword: '',
                stase_id: null,
                type: '',
                page: 1,
            },
            form: {
                id: null,
                stase_id: null,
                type: '',
                category: '',
                lecture_id: null,
                date: '',
                field_1: '',
                field_2: '',
                field_3: '',
                field_4: '',
                field_5: '',
                field_6: '',
            },
            editMode: false,
            modalOpen: false,
            loading: false,
            submitting: false,
            errorMessage: '',
            lectureOptions: [],
            staseOptions: [],
            staseTypes: [],
            staseSkills: [],
            availableFields: ['Field 1', 'Field 2', 'Field 3', 'Field 4', 'Field 5', 'Field 6'],
        };
    },
    created() {
        this.fetchLectures();
        this.fetchStases();
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
            this.filters.type = '';
            this.filters.page = 1;
            this.fetchLogbooks();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchLogbooks();
        },
        openCreate() {
            this.editMode = false;
            this.resetForm();
            this.errorMessage = '';
            this.staseTypes = [];
            this.staseSkills = [];
            this.availableFields = ['Field 1', 'Field 2', 'Field 3', 'Field 4', 'Field 5', 'Field 6'];
            this.modalOpen = true;
        },
        openEdit(logbook) {
            this.editMode = true;
            this.form = {
                id: logbook.id,
                stase_id: logbook.stase_id ?? null,
                type: logbook.type || '',
                category: logbook.category || '',
                lecture_id: logbook.lecture_id ?? null,
                date: logbook.date || '',
                field_1: logbook.field_1 || '',
                field_2: logbook.field_2 || '',
                field_3: logbook.field_3 || '',
                field_4: logbook.field_4 || '',
                field_5: logbook.field_5 || '',
                field_6: logbook.field_6 || '',
            };
            this.errorMessage = '';
            this.modalOpen = true;
            if (this.form.stase_id) {
                this.fetchLogbookOptions(this.form.id, this.form.stase_id);
            }
        },
        closeModal() {
            this.modalOpen = false;
            this.errorMessage = '';
            if (!this.editMode) {
                this.resetForm();
            }
        },
        resetForm() {
            this.form = {
                id: null,
                stase_id: null,
                type: '',
                category: '',
                lecture_id: null,
                date: '',
                field_1: '',
                field_2: '',
                field_3: '',
                field_4: '',
                field_5: '',
                field_6: '',
            };
        },
        handleStaseChange() {
            const staseId = this.form.stase_id;
            if (!staseId) {
                this.staseTypes = [];
                this.staseSkills = [];
                this.form.type = '';
                this.form.category = '';
                this.availableFields = ['Field 1', 'Field 2', 'Field 3', 'Field 4', 'Field 5', 'Field 6'];
                return;
            }
            if (this.editMode && this.form.id) {
                this.fetchLogbookOptions(this.form.id, staseId);
            } else {
                this.fetchStaseOptions(staseId);
            }
        },
        handleTypeChange() {
            const selected = this.staseTypes.find((option) => option.value === this.form.type);
            const parsed = selected ? this.normalizeParseDesc(selected.parse_desc) : [];
            if (parsed.length) {
                this.availableFields = parsed;
            } else {
                this.availableFields = ['Field 1', 'Field 2', 'Field 3', 'Field 4', 'Field 5', 'Field 6'];
            }
        },
        normalizeParseDesc(parseDesc) {
            if (!parseDesc) {
                return [];
            }
            if (Array.isArray(parseDesc)) {
                return parseDesc;
            }
            if (typeof parseDesc === 'object') {
                return ['field_1', 'field_2', 'field_3', 'field_4', 'field_5', 'field_6']
                    .map((key) => parseDesc[key])
                    .filter(Boolean);
            }
            return [];
        },
        submitForm() {
            if (this.editMode) {
                return this.updateLogbook();
            }

            return this.createLogbook();
        },
        createLogbook() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.post(this.baseUrl, this.form)
                .then(() => {
                    this.closeModal();
                    this.fetchLogbooks();
                    this.$showToast('Logbook created successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to create logbook.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        updateLogbook() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.put(`${this.baseUrl}/${this.form.id}`, this.form)
                .then(() => {
                    this.fetchLogbooks();
                    this.closeModal();
                    this.$showToast('Logbook updated successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to update logbook.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        deleteLogbook(logbook) {
            if (!window.confirm(`Delete logbook ${logbook.type}?`)) {
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
            return Repository.get('/api/stase-list')
                .then((response) => {
                    const data = response && response.data ? response.data.result : null;
                    this.staseOptions = Array.isArray(data) ? data : [];
                })
                .catch(() => {
                    this.staseOptions = [];
                });
        },
        fetchStaseOptions(staseId) {
            return Repository.get(`/api/stase-option/${staseId}`)
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.staseTypes = result && Array.isArray(result.types) ? result.types : [];
                    this.staseSkills = result && Array.isArray(result.skills) ? result.skills : [];
                    if (this.staseTypes.length && !this.form.type) {
                        this.form.type = this.staseTypes[0].value;
                    }
                    if (this.staseSkills.length && !this.form.category) {
                        this.form.category = this.staseSkills[0].value;
                    }
                    this.handleTypeChange();
                })
                .catch(() => {
                    this.staseTypes = [];
                    this.staseSkills = [];
                    this.availableFields = ['Field 1', 'Field 2', 'Field 3', 'Field 4', 'Field 5', 'Field 6'];
                });
        },
        fetchLogbookOptions(logbookId, staseId) {
            return Repository.get(`${this.baseUrl}/${logbookId}`, {
                params: {
                    stase_id: staseId,
                },
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const options = result && result.options ? result.options : null;
                    this.staseTypes = options && Array.isArray(options.types) ? options.types : [];
                    this.staseSkills = options && Array.isArray(options.skills) ? options.skills : [];
                    if (this.staseTypes.length && !this.form.type) {
                        this.form.type = this.staseTypes[0].value;
                    }
                    if (this.staseSkills.length && !this.form.category) {
                        this.form.category = this.staseSkills[0].value;
                    }
                    this.handleTypeChange();
                })
                .catch(() => {
                    this.staseTypes = [];
                    this.staseSkills = [];
                    this.availableFields = ['Field 1', 'Field 2', 'Field 3', 'Field 4', 'Field 5', 'Field 6'];
                });
        },
    },
};
</script>
