<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Stase Management</div>
                <h1 class="text-2xl font-semibold text-ink">Stases</h1>
            </div>
            <button
                class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                type="button"
                @click="openCreate"
            >
                Add Stase
            </button>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="text-xs text-muted">Keyword</label>
                    <input
                        v-model.trim="filters.keyword"
                        type="text"
                        @keyup.enter="applyFilter"
                        placeholder="Search name..."
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="min-w-[200px]">
                    <label class="text-xs text-muted">Study Program</label>
                    <select
                        v-model="filters.study_program_code"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">All</option>
                        <option v-for="option in studyPrograms" :key="option.id" :value="option.code">
                            {{ option.name }}
                        </option>
                    </select>
                </div>
                <div class="min-w-[160px]">
                    <label class="text-xs text-muted">Section</label>
                    <select
                        v-model="filters.section"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">All</option>
                        <option v-for="option in sectionOptions" :key="option" :value="option">
                            {{ option }}
                        </option>
                    </select>
                </div>
                <div class="min-w-[140px]">
                    <label class="text-xs text-muted">Semester</label>
                    <select
                        v-model="filters.semester"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">All</option>
                        <option v-for="option in semesterOptions" :key="option" :value="option">
                            {{ option }}
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
                <div class="font-semibold">Stases</div>
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
                <div v-if="!loading && stases.length === 0" class="px-5 py-6 text-sm text-muted">
                    No stases found.
                </div>
                <div
                    v-for="(stase, index) in stases"
                    :key="stase.id"
                    class="flex flex-wrap items-center gap-3 px-5 py-4"
                >
                    <div class="w-8 text-sm font-semibold text-muted">
                        {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                    </div>
                    <div class="flex h-8 w-8 items-center justify-center rounded-full border border-border">
                        <span
                            class="h-3 w-3 rounded-full"
                            :style="{ backgroundColor: stase.color || '#e2e8f0' }"
                        ></span>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <div class="font-semibold text-ink">{{ stase.name }}</div>
                            <span
                                v-if="stase.is_mandatory"
                                class="rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide text-primary"
                            >
                                Mandatory
                            </span>
                        </div>
                        <div class="text-xs text-muted">
                            {{ stase.alias }}
                            <span v-if="stase.desc">• {{ stase.desc }}</span>
                        </div>
                    </div>
                    <div class="text-xs text-muted" v-if="stase.stase_tasks_count !== undefined">
                        {{ stase.stase_tasks_count }} tasks
                    </div>
                    <div class="relative action-dropdown">
                        <button
                            class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                            type="button"
                            @click.stop="toggleActionMenu(stase.id)"
                        >
                            Actions
                        </button>
                        <div
                            v-if="actionMenuOpenId === stase.id"
                            class="absolute right-0 z-10 mt-2 w-44 rounded-xl border border-border bg-white p-1 shadow-lg"
                        >
                            <router-link
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                :to="`/blu/stase-tasks/${stase.id}`"
                                @click.native="closeActionMenu"
                            >
                                Tasks
                            </router-link>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                type="button"
                                @click="handleAction('edit', stase)"
                            >
                                Edit
                            </button>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-rose-600 hover:bg-rose-50"
                                type="button"
                                @click="handleAction('delete', stase)"
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
            :open="modalOpen"
            :title="editMode ? 'Edit Stase' : 'Create Stase'"
            :eyebrow="editMode ? 'Update rotation' : 'New rotation'"
            size="xl"
            @close="closeModal"
        >
            <div class="flex gap-1 border-b border-border">
                <button
                    type="button"
                    class="border-b-2 px-4 py-2 text-sm font-medium"
                    :class="activeTab === 'information' ? 'border-primary text-primary' : 'border-transparent text-muted'"
                    @click="activeTab = 'information'"
                >
                    Information
                </button>
                <button
                    type="button"
                    class="border-b-2 px-4 py-2 text-sm font-medium"
                    :class="activeTab === 'attribute' ? 'border-primary text-primary' : 'border-transparent text-muted'"
                    @click="activeTab = 'attribute'"
                >
                    Attribute
                </button>
            </div>

            <form class="grid gap-4 pt-4" @submit.prevent="submitForm">
                <div v-show="activeTab === 'information'" class="grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Name</span>
                        <input
                            v-model.trim="form.name"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Alias</span>
                        <input
                            v-model.trim="form.alias"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm md:col-span-2">
                        <span class="text-muted">Description</span>
                        <input
                            v-model.trim="form.desc"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Order</span>
                        <input
                            v-model.number="form.stase_order"
                            type="number"
                            min="0"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">SKS</span>
                        <input
                            v-model.number="form.sks"
                            type="number"
                            min="0"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Semester</span>
                        <input
                            v-model.trim="form.semester"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Duration (week)</span>
                        <input
                            v-model.trim="form.duration"
                            type="number"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Section</span>
                        <input
                            v-model.trim="form.section"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Study Program</span>
                        <select
                            v-model="form.study_program_code"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        >
                            <option value="">-</option>
                            <option v-for="option in studyPrograms" :key="option.id" :value="option.code">
                                {{ option.name }}
                            </option>
                        </select>
                    </label>
                    <label class="flex items-center gap-2 text-sm md:col-span-2">
                        <input
                            v-model="form.is_mandatory"
                            type="checkbox"
                            class="h-4 w-4 rounded border-border text-primary focus:ring-2 focus:ring-primary/30"
                        />
                        <span class="text-muted">Mandatory</span>
                    </label>
                </div>

                <div v-show="activeTab === 'attribute'" class="grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Color</span>
                        <input
                            v-model="form.color"
                            type="color"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Font Color</span>
                        <input
                            v-model="form.font_color"
                            type="color"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm md:col-span-2">
                        <span class="text-muted">Lecture in Charge</span>
                        <select
                            v-model="form.lecture_name"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        >
                            <option value="">-</option>
                            <option v-for="lecture in lectures" :key="lecture.id" :value="lecture.name">
                                {{ lecture.name }}
                            </option>
                        </select>
                    </label>
                    <div class="grid gap-2 text-sm md:col-span-2">
                        <span class="text-muted">Lecture Names</span>
                        <div class="grid gap-2 rounded-xl border border-border bg-white p-3 sm:grid-cols-2">
                            <span v-if="lectures.length === 0" class="text-xs text-muted">
                                No lectures found.
                            </span>
                            <label
                                v-for="lecture in lectures"
                                :key="lecture.id"
                                class="flex items-center gap-2 text-sm"
                            >
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 rounded border border-border"
                                    :checked="isLectureNameSelected(lecture.name)"
                                    @change="toggleLectureNameSelection(lecture.name, $event.target.checked)"
                                />
                                <span class="text-ink">{{ lecture.name }}</span>
                            </label>
                        </div>
                    </div>
                    <label class="grid gap-2 text-sm md:col-span-2">
                        <span class="text-muted">Evaluation Link</span>
                        <input
                            v-model.trim="form.evaluation_link"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                </div>

                <div v-if="errorMessage" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ errorMessage }}
                </div>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="submit"
                    :disabled="submitting"
                >
                    {{ submitting ? 'Saving...' : editMode ? 'Update Stase' : 'Create Stase' }}
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
import persistFilters from '../../mixins/persistFilters';

export default {
    components: {
        Loading,
        Modal,
    },
    mixins: [persistFilters('stases')],
    data() {
        return {
            baseUrl: '/api/stases',
            stases: [],
            pagination: {},
            studyPrograms: [],
            lectures: [],
            filters: {
                keyword: '',
                study_program_code: '',
                section: '',
                semester: '',
                page: 1,
            },
            sectionOptions: ['Tahap I', 'Tahap II', 'Tahap III'],
            semesterOptions: ['1', '2', '3', '4', '5', '6', '7', '8', '9'],
            form: {
                id: null,
                name: '',
                alias: '',
                color: '',
                font_color: '',
                desc: '',
                stase_order: null,
                is_mandatory: false,
                lecture_name: '',
                lecture_names: [],
                evaluation_link: '',
                study_program_code: '',
                section: '',
                semester: '',
                sks: null,
                duration: '',
            },
            activeTab: 'information',
            editMode: false,
            modalOpen: false,
            loading: false,
            submitting: false,
            errorMessage: '',
            actionMenuOpenId: null,
        };
    },
    created() {
        this.fetchStudyPrograms();
        this.fetchLectures();
        this.fetchStases();
    },
    mounted() {
        document.addEventListener('click', this.handleDocumentClick);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.handleDocumentClick);
    },
    methods: {
        toggleActionMenu(staseId) {
            this.actionMenuOpenId = this.actionMenuOpenId === staseId ? null : staseId;
        },
        closeActionMenu() {
            this.actionMenuOpenId = null;
        },
        handleAction(action, stase) {
            this.closeActionMenu();
            if (action === 'edit') {
                this.openEdit(stase);
                return;
            }
            if (action === 'delete') {
                this.deleteStase(stase);
            }
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
        fetchStudyPrograms() {
            return Repository.get('/api/study-program-list')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.studyPrograms = Array.isArray(result) ? result : [];
                })
                .catch(() => {
                    this.studyPrograms = [];
                });
        },
        fetchLectures() {
            return Repository.get('/api/lecture-list')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.lectures = Array.isArray(result) ? result : [];
                })
                .catch(() => {
                    this.lectures = [];
                });
        },
        fetchStases() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(this.baseUrl, {
                params: this.filters,
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];

                    this.stases = data;
                    this.pagination = result || {};
                })
                .catch(() => {
                    this.stases = [];
                    this.pagination = {};
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        applyFilter() {
            this.filters.page = 1;
            this.fetchStases();
        },
        resetFilter() {
            this.filters.keyword = '';
            this.filters.study_program_code = '';
            this.filters.section = '';
            this.filters.semester = '';
            this.filters.page = 1;
            this.fetchStases();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchStases();
        },
        openCreate() {
            this.editMode = false;
            this.resetForm();
            this.activeTab = 'information';
            this.errorMessage = '';
            this.modalOpen = true;
        },
        openEdit(stase) {
            this.editMode = true;
            this.form = {
                id: stase.id,
                name: stase.name || '',
                alias: stase.alias || '',
                color: stase.color || '',
                font_color: stase.font_color || '',
                desc: stase.desc || '',
                stase_order: stase.stase_order ?? null,
                is_mandatory: !!stase.is_mandatory,
                lecture_name: stase.lecture_name || '',
                lecture_names: this.parseLectureNames(stase.lecture_names),
                evaluation_link: stase.evaluation_link || '',
                study_program_code: stase.study_program_code || '',
                section: stase.section || '',
                semester: stase.semester || '',
                sks: stase.sks ?? null,
                duration: stase.duration || '',
            };
            this.activeTab = 'information';
            this.errorMessage = '';
            this.modalOpen = true;
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
                name: '',
                alias: '',
                color: '',
                font_color: '',
                desc: '',
                stase_order: null,
                is_mandatory: false,
                lecture_name: '',
                lecture_names: [],
                evaluation_link: '',
                study_program_code: '',
                section: '',
                semester: '',
                sks: null,
                duration: '',
            };
        },
        parseLectureNames(value) {
            if (Array.isArray(value)) {
                return value;
            }

            if (!value) {
                return [];
            }

            return String(value)
                .split(',')
                .map((name) => name.trim())
                .filter((name) => name.length > 0);
        },
        isLectureNameSelected(name) {
            return Array.isArray(this.form.lecture_names) && this.form.lecture_names.includes(name);
        },
        toggleLectureNameSelection(name, checked) {
            const current = Array.isArray(this.form.lecture_names) ? [...this.form.lecture_names] : [];
            this.form.lecture_names = checked
                ? Array.from(new Set([...current, name]))
                : current.filter((item) => item !== name);
        },
        buildPayload() {
            return {
                ...this.form,
                lecture_names: Array.isArray(this.form.lecture_names)
                    ? this.form.lecture_names.join(', ')
                    : this.form.lecture_names,
            };
        },
        submitForm() {
            if (this.editMode) {
                return this.updateStase();
            }

            return this.createStase();
        },
        createStase() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.post(this.baseUrl, this.buildPayload())
                .then(() => {
                    this.closeModal();
                    this.fetchStases();
                    this.$showToast('Stase created successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to create stase.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        updateStase() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.put(`${this.baseUrl}/${this.form.id}`, this.buildPayload())
                .then(() => {
                    this.fetchStases();
                    this.closeModal();
                    this.$showToast('Stase updated successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to update stase.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        deleteStase(stase) {
            if (!window.confirm(`Delete stase ${stase.name}?`)) {
                return;
            }

            Repository.delete(`${this.baseUrl}/${stase.id}`)
                .then(() => {
                    this.fetchStases();
                    this.$showToast('Stase deleted successfully.');
                })
                .catch(() => {
                    this.errorMessage = 'Failed to delete stase.';
                });
        },
    },
};
</script>
