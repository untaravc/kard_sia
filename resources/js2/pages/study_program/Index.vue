<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Data Master</div>
                <h1 class="text-2xl font-semibold text-ink">Study Programs</h1>
            </div>
            <button
                class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                type="button"
                @click="openCreate"
            >
                Add Study Program
            </button>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="text-xs text-muted">Keyword</label>
                    <input
                        v-model.trim="filters.keyword"
                        @keyup.enter="applyFilter"
                        type="text"
                        placeholder="Search name, code, or head..."
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
                <div class="font-semibold">Study Programs</div>
                <div v-if="pagination.total" class="text-xs text-muted">
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
                <div v-if="!loading && studyPrograms.length === 0" class="px-5 py-6 text-sm text-muted">
                    No study programs found.
                </div>
                <div
                    v-for="(item, index) in studyPrograms"
                    :key="item.id"
                    class="flex flex-wrap items-start gap-3 px-5 py-4"
                >
                    <div class="w-8 pt-1 text-sm font-semibold text-muted">
                        {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="font-semibold text-ink">{{ item.name }}</div>
                            <span
                                v-if="item.code"
                                class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700"
                            >
                                {{ item.code }}
                            </span>
                        </div>
                        <div class="mt-1 text-xs text-muted">
                            <span v-if="item.head_name">Head: {{ item.head_name }}</span>
                            <span v-if="item.deputy_head_name"> • Deputy: {{ item.deputy_head_name }}</span>
                        </div>
                        <div v-if="item.address" class="mt-1 text-xs text-muted">{{ item.address }}</div>
                        <div v-if="item.desc" class="mt-2 text-sm text-muted">{{ formatDesc(item.desc) }}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                            type="button"
                            @click="openEdit(item)"
                        >
                            Edit
                        </button>
                        <button
                            class="rounded-lg bg-rose-500/10 px-3 py-1.5 text-xs text-rose-600"
                            type="button"
                            @click="deleteStudyProgram(item)"
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
            :title="editMode ? 'Edit Study Program' : 'Create Study Program'"
            :eyebrow="editMode ? 'Update study program' : 'New study program'"
            size="lg"
            @close="closeModal"
        >
            <form class="grid gap-4" @submit.prevent="submitForm">
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Code</span>
                        <input
                            v-model.trim="form.code"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Name</span>
                        <input
                            v-model.trim="form.name"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Head Name</span>
                        <input
                            v-model.trim="form.head_name"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Deputy Head Name</span>
                        <input
                            v-model.trim="form.deputy_head_name"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                </div>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Address</span>
                    <textarea
                        v-model.trim="form.address"
                        rows="3"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    ></textarea>
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Description</span>
                    <textarea
                        v-model.trim="form.desc"
                        rows="5"
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
                    {{ submitting ? 'Saving...' : editMode ? 'Update Study Program' : 'Create Study Program' }}
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
    mixins: [persistFilters('study-programs')],
    data() {
        return {
            baseUrl: '/api/study-programs',
            studyPrograms: [],
            pagination: {},
            filters: {
                keyword: '',
                page: 1,
            },
            form: {
                id: null,
                code: '',
                name: '',
                desc: '',
                address: '',
                head_name: '',
                deputy_head_name: '',
            },
            editMode: false,
            modalOpen: false,
            loading: false,
            submitting: false,
            errorMessage: '',
        };
    },
    created() {
        this.fetchStudyPrograms();
    },
    methods: {
        fetchStudyPrograms() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(this.baseUrl, {
                params: this.filters,
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];

                    this.studyPrograms = data;
                    this.pagination = result || {};
                })
                .catch(() => {
                    this.studyPrograms = [];
                    this.pagination = {};
                    this.errorMessage = 'Failed to load study programs.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        applyFilter() {
            this.filters.page = 1;
            this.fetchStudyPrograms();
        },
        resetFilter() {
            this.filters.keyword = '';
            this.filters.page = 1;
            this.fetchStudyPrograms();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchStudyPrograms();
        },
        openCreate() {
            this.editMode = false;
            this.resetForm();
            this.errorMessage = '';
            this.modalOpen = true;
        },
        openEdit(item) {
            this.editMode = true;
            this.form = {
                id: item.id,
                code: item.code || '',
                name: item.name || '',
                desc: item.desc || '',
                address: item.address || '',
                head_name: item.head_name || '',
                deputy_head_name: item.deputy_head_name || '',
            };
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
                code: '',
                name: '',
                desc: '',
                address: '',
                head_name: '',
                deputy_head_name: '',
            };
        },
        submitForm() {
            if (this.editMode) {
                return this.updateStudyProgram();
            }

            return this.createStudyProgram();
        },
        createStudyProgram() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.post(this.baseUrl, this.buildPayload())
                .then(() => {
                    this.closeModal();
                    this.fetchStudyPrograms();
                    this.$showToast('Study program created successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to create study program.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        updateStudyProgram() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.put(`${this.baseUrl}/${this.form.id}`, this.buildPayload())
                .then(() => {
                    this.fetchStudyPrograms();
                    this.closeModal();
                    this.$showToast('Study program updated successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to update study program.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        deleteStudyProgram(item) {
            if (!window.confirm(`Delete study program ${item.name}?`)) {
                return;
            }

            Repository.delete(`${this.baseUrl}/${item.id}`)
                .then(() => {
                    this.fetchStudyPrograms();
                    this.$showToast('Study program deleted successfully.');
                })
                .catch(() => {
                    this.errorMessage = 'Failed to delete study program.';
                });
        },
        buildPayload() {
            return {
                code: this.form.code || null,
                name: this.form.name,
                desc: this.form.desc || null,
                address: this.form.address || null,
                head_name: this.form.head_name || null,
                deputy_head_name: this.form.deputy_head_name || null,
            };
        },
        formatDesc(value) {
            if (!value) {
                return '';
            }

            const normalized = String(value).replace(/\s+/g, ' ').trim();
            if (normalized.length <= 140) {
                return normalized;
            }

            return `${normalized.slice(0, 140)}...`;
        },
    },
};
</script>
