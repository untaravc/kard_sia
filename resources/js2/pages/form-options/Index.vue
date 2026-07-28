<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Form Option Management</div>
                <h1 class="text-2xl font-semibold text-ink">Form Options</h1>
            </div>
            <button
                class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                type="button"
                @click="openCreate"
            >
                Add Form Option
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
                        placeholder="Search name, type, or value..."
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="min-w-[180px]">
                    <label class="text-xs text-muted">Type</label>
                    <input
                        v-model.trim="filters.type"
                        @keyup.enter="applyFilter"
                        type="text"
                        placeholder="Optional type"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="min-w-[140px]">
                    <label class="text-xs text-muted">Status</label>
                    <select
                        v-model="filters.status"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">All</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
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
                <div class="font-semibold">Form Options</div>
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
                <div v-if="!loading && formOptions.length === 0" class="px-5 py-6 text-sm text-muted">
                    No form options found.
                </div>
                <div
                    v-for="(option, index) in formOptions"
                    :key="option.id"
                    class="flex flex-wrap items-start gap-3 px-5 py-4"
                >
                    <div class="w-8 pt-1 text-sm font-semibold text-muted">
                        {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="font-semibold text-ink">{{ option.name }}</div>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700">
                                {{ option.type }}
                            </span>
                            <span
                                class="rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                :class="option.status == 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'"
                            >
                                {{ option.status == 1 ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="mt-1 text-xs text-muted">
                            <span v-if="option.value">Value: {{ option.value }}</span>
                            <span v-if="option.relation_id">• Relation ID: {{ option.relation_id }}</span>
                        </div>
                        <div v-if="option.desc" class="mt-2 text-sm text-muted">
                            {{ formatDesc(option.desc) }}
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                            type="button"
                            @click="openEdit(option)"
                        >
                            Edit
                        </button>
                        <button
                            class="rounded-lg bg-rose-500/10 px-3 py-1.5 text-xs text-rose-600"
                            type="button"
                            @click="deleteFormOption(option)"
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
            :title="editMode ? 'Edit Form Option' : 'Create Form Option'"
            :eyebrow="editMode ? 'Update option' : 'New option'"
            size="lg"
            @close="closeModal"
        >
            <form class="grid gap-4" @submit.prevent="submitForm">
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Name</span>
                        <input
                            v-model.trim="form.name"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Type</span>
                        <input
                            v-model.trim="form.type"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Value</span>
                        <input
                            v-model.trim="form.value"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Relation ID</span>
                        <input
                            v-model.trim="form.relation_id"
                            type="number"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Status</span>
                        <select
                            v-model="form.status"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        >
                            <option :value="1">Active</option>
                            <option :value="0">Inactive</option>
                        </select>
                    </label>
                </div>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Description</span>
                    <textarea
                        v-model.trim="form.desc"
                        rows="5"
                        placeholder='Plain text or JSON, for example {"key":"value"}'
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
                    {{ submitting ? 'Saving...' : editMode ? 'Update Form Option' : 'Create Form Option' }}
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
    mixins: [persistFilters('form-options')],
    data() {
        return {
            baseUrl: '/api/form-options',
            formOptions: [],
            pagination: {},
            filters: {
                keyword: '',
                type: '',
                status: '',
                page: 1,
            },
            form: {
                id: null,
                name: '',
                type: '',
                value: '',
                relation_id: '',
                desc: '',
                status: 1,
            },
            editMode: false,
            modalOpen: false,
            loading: false,
            submitting: false,
            errorMessage: '',
        };
    },
    created() {
        this.fetchFormOptions();
    },
    methods: {
        fetchFormOptions() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(this.baseUrl, {
                params: this.filters,
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];

                    this.formOptions = data;
                    this.pagination = result || {};
                })
                .catch(() => {
                    this.formOptions = [];
                    this.pagination = {};
                    this.errorMessage = 'Failed to load form options.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        applyFilter() {
            this.filters.page = 1;
            this.fetchFormOptions();
        },
        resetFilter() {
            this.filters.keyword = '';
            this.filters.type = '';
            this.filters.status = '';
            this.filters.page = 1;
            this.fetchFormOptions();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchFormOptions();
        },
        openCreate() {
            this.editMode = false;
            this.resetForm();
            this.errorMessage = '';
            this.modalOpen = true;
        },
        openEdit(option) {
            this.editMode = true;
            this.form = {
                id: option.id,
                name: option.name || '',
                type: option.type || '',
                value: option.value || '',
                relation_id: option.relation_id == null ? '' : String(option.relation_id),
                desc: this.normalizeDesc(option),
                status: option.status == null ? 1 : Number(option.status),
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
                name: '',
                type: '',
                value: '',
                relation_id: '',
                desc: '',
                status: 1,
            };
        },
        submitForm() {
            if (this.editMode) {
                return this.updateFormOption();
            }

            return this.createFormOption();
        },
        createFormOption() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.post(this.baseUrl, this.buildPayload())
                .then(() => {
                    this.closeModal();
                    this.fetchFormOptions();
                    this.$showToast('Form option created successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to create form option.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        updateFormOption() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.put(`${this.baseUrl}/${this.form.id}`, this.buildPayload())
                .then(() => {
                    this.fetchFormOptions();
                    this.closeModal();
                    this.$showToast('Form option updated successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to update form option.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        deleteFormOption(option) {
            if (!window.confirm(`Delete form option ${option.name}?`)) {
                return;
            }

            Repository.delete(`${this.baseUrl}/${option.id}`)
                .then(() => {
                    this.fetchFormOptions();
                    this.$showToast('Form option deleted successfully.');
                })
                .catch(() => {
                    this.errorMessage = 'Failed to delete form option.';
                });
        },
        buildPayload() {
            return {
                name: this.form.name,
                type: this.form.type,
                value: this.form.value || null,
                relation_id: this.form.relation_id === '' ? null : Number(this.form.relation_id),
                desc: this.form.desc || null,
                status: Number(this.form.status),
            };
        },
        normalizeDesc(option) {
            if (option.desc) {
                return option.desc;
            }

            if (option.parse_desc) {
                try {
                    return JSON.stringify(option.parse_desc, null, 2);
                } catch (error) {
                    return String(option.parse_desc);
                }
            }

            return '';
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
