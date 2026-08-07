<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Scoring Components</div>
                <h1 class="text-2xl font-semibold text-ink">{{ task.name || 'Task' }}</h1>
            </div>
            <button
                class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                type="button"
                @click="openCreate"
            >
                Add Scoring Component
            </button>
        </header>

        <section class="relative rounded-2xl border border-border bg-panel">
            <Loading :active="loading" :is-full-page="false" />
            <div class="flex items-center justify-between border-b border-border px-5 py-4">
                <div class="font-semibold">Scoring Components for this Task</div>
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
                <div v-if="!loading && taskDetails.length === 0" class="px-5 py-6 text-sm text-muted">
                    No scoring components found.
                </div>
                <div
                    v-for="(detail, index) in taskDetails"
                    :key="detail.id"
                    class="flex flex-wrap items-center gap-3 px-5 py-4"
                >
                    <div class="w-8 text-sm font-semibold text-muted">
                        {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold text-ink">{{ detail.name }}</div>
                        <div class="text-xs text-muted">
                            <span v-if="detail.order !== null && detail.order !== undefined">Order: {{ detail.order }}</span>
                            <span> • Type: {{ detail.type || 'option' }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                            type="button"
                            @click="openEdit(detail)"
                        >
                            Edit
                        </button>
                        <button
                            class="rounded-lg bg-rose-500/10 px-3 py-1.5 text-xs text-rose-600"
                            type="button"
                            @click="deleteTaskDetail(detail)"
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
            :title="editMode ? 'Edit Scoring Component' : 'Create Scoring Component'"
            :eyebrow="editMode ? 'Update scoring component' : 'New scoring component'"
            size="md"
            @close="closeModal"
        >
            <form class="grid gap-4" @submit.prevent="submitForm">
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Name</span>
                    <input
                        v-model.trim="form.name"
                        type="text"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Order</span>
                    <input
                        v-model.number="form.order"
                        type="number"
                        min="0"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Type</span>
                    <select
                        v-model="form.type"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="option">Option</option>
                        <option value="text">Text</option>
                        <option value="bool">Bool</option>
                        <option value="score">Score</option>
                    </select>
                </label>
                <div v-if="errorMessage" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ errorMessage }}
                </div>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="submit"
                    :disabled="submitting"
                >
                    {{ submitting ? 'Saving...' : editMode ? 'Update Scoring Component' : 'Create Scoring Component' }}
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
    mixins: [persistFilters('task-scoring-components')],
    data() {
        return {
            baseUrl: '/api/task-details',
            taskDetails: [],
            task: {},
            pagination: {},
            filters: {
                page: 1,
            },
            form: {
                id: null,
                task_id: null,
                name: '',
                order: null,
                type: 'option',
            },
            editMode: false,
            modalOpen: false,
            loading: false,
            submitting: false,
            errorMessage: '',
        };
    },
    created() {
        this.fetchTask();
        this.fetchTaskDetails();
    },
    watch: {
        '$route.params.task_id'() {
            this.filters.page = 1;
            this.fetchTask();
            this.fetchTaskDetails();
        },
    },
    methods: {
        fetchTask() {
            const taskId = this.$route.params.task_id;
            if (!taskId) {
                this.task = {};
                return;
            }

            return Repository.get(`/api/tasks/${taskId}`)
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.task = result || {};
                })
                .catch(() => {
                    this.task = {};
                });
        },
        fetchTaskDetails() {
            const taskId = this.$route.params.task_id;
            if (!taskId) {
                this.taskDetails = [];
                this.pagination = {};
                return;
            }

            this.loading = true;
            this.errorMessage = '';

            return Repository.get(this.baseUrl, {
                params: {
                    ...this.filters,
                    task_id: taskId,
                },
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];

                    this.taskDetails = data;
                    this.pagination = result || {};
                })
                .catch(() => {
                    this.taskDetails = [];
                    this.pagination = {};
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchTaskDetails();
        },
        openCreate() {
            this.editMode = false;
            this.resetForm();
            this.errorMessage = '';
            this.modalOpen = true;
        },
        openEdit(detail) {
            this.editMode = true;
            this.form = {
                id: detail.id,
                task_id: detail.task_id,
                name: detail.name || '',
                order: detail.order ?? null,
                type: detail.type || 'option',
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
                task_id: Number(this.$route.params.task_id),
                name: '',
                order: null,
                type: 'option',
            };
        },
        submitForm() {
            if (this.editMode) {
                return this.updateTaskDetail();
            }

            return this.createTaskDetail();
        },
        createTaskDetail() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.post(this.baseUrl, this.form)
                .then(() => {
                    this.closeModal();
                    this.fetchTaskDetails();
                    this.$showToast('Scoring component created successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to create scoring component.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        updateTaskDetail() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.put(`${this.baseUrl}/${this.form.id}`, this.form)
                .then(() => {
                    this.fetchTaskDetails();
                    this.closeModal();
                    this.$showToast('Scoring component updated successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to update scoring component.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        deleteTaskDetail(detail) {
            if (!window.confirm(`Delete scoring component ${detail.name}?`)) {
                return;
            }

            Repository.delete(`${this.baseUrl}/${detail.id}`)
                .then(() => {
                    this.fetchTaskDetails();
                    this.$showToast('Scoring component deleted successfully.');
                })
                .catch(() => {
                    this.errorMessage = 'Failed to delete scoring component.';
                });
        },
    },
};
</script>
