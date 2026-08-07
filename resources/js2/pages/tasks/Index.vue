<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Task Management</div>
                <h1 class="text-2xl font-semibold text-ink">Tasks</h1>
            </div>
            <button
                class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                type="button"
                @click="openCreate"
            >
                Add Task
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
                <div class="font-semibold">Tasks</div>
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
                <div v-if="!loading && tasks.length === 0" class="px-5 py-6 text-sm text-muted">
                    No tasks found.
                </div>
                <div
                    v-for="(task, index) in tasks"
                    :key="task.id"
                    class="flex flex-wrap items-center gap-3 px-5 py-4"
                >
                    <div class="w-8 text-sm font-semibold text-muted">
                        {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <div class="font-semibold text-ink">{{ task.name }}</div>
                            <span
                                v-if="task.is_latter"
                                class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-700"
                            >
                                Latter
                            </span>
                        </div>
                        <div class="text-xs text-muted" v-if="task.desc">{{ task.desc }}</div>
                    </div>
                    <div class="relative action-dropdown">
                        <button
                            class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                            type="button"
                            @click.stop="toggleActionMenu(task.id)"
                        >
                            Actions
                        </button>
                        <div
                            v-if="actionMenuOpenId === task.id"
                            class="absolute right-0 z-10 mt-2 w-44 rounded-xl border border-border bg-white p-1 shadow-lg"
                        >
                            <router-link
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                :to="`/blu/tasks/${task.id}/scoring-components`"
                                @click.native="closeActionMenu"
                            >
                                Scoring Components
                            </router-link>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                type="button"
                                @click="handleAction('edit', task)"
                            >
                                Edit
                            </button>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-rose-600 hover:bg-rose-50"
                                type="button"
                                @click="handleAction('delete', task)"
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
            :title="editMode ? 'Edit Task' : 'Create Task'"
            :eyebrow="editMode ? 'Update task' : 'New task'"
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
                    <span class="text-muted">Description</span>
                    <input
                        v-model.trim="form.desc"
                        type="text"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="flex items-center gap-3 text-sm">
                    <input
                        v-model.number="form.is_latter"
                        type="checkbox"
                        :true-value="1"
                        :false-value="0"
                        class="h-4 w-4 rounded border border-border"
                    />
                    <span class="text-muted">Latter task</span>
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
                <div v-if="errorMessage" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ errorMessage }}
                </div>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="submit"
                    :disabled="submitting"
                >
                    {{ submitting ? 'Saving...' : editMode ? 'Update Task' : 'Create Task' }}
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
    mixins: [persistFilters('tasks')],
    data() {
        return {
            baseUrl: '/api/tasks',
            tasks: [],
            pagination: {},
            studyPrograms: [],
            filters: {
                keyword: '',
                study_program_code: '',
                page: 1,
            },
            form: {
                id: null,
                name: '',
                desc: '',
                is_latter: 0,
                study_program_code: '',
            },
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
        this.fetchTasks();
    },
    mounted() {
        document.addEventListener('click', this.handleDocumentClick);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.handleDocumentClick);
    },
    methods: {
        toggleActionMenu(taskId) {
            this.actionMenuOpenId = this.actionMenuOpenId === taskId ? null : taskId;
        },
        closeActionMenu() {
            this.actionMenuOpenId = null;
        },
        handleAction(action, task) {
            this.closeActionMenu();
            if (action === 'edit') {
                this.openEdit(task);
                return;
            }
            if (action === 'delete') {
                this.deleteTask(task);
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
        fetchTasks() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(this.baseUrl, {
                params: this.filters,
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];

                    this.tasks = data;
                    this.pagination = result || {};
                })
                .catch(() => {
                    this.tasks = [];
                    this.pagination = {};
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        applyFilter() {
            this.filters.page = 1;
            this.fetchTasks();
        },
        resetFilter() {
            this.filters.keyword = '';
            this.filters.study_program_code = '';
            this.filters.page = 1;
            this.fetchTasks();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchTasks();
        },
        openCreate() {
            this.editMode = false;
            this.resetForm();
            this.errorMessage = '';
            this.modalOpen = true;
        },
        openEdit(task) {
            this.editMode = true;
            this.form = {
                id: task.id,
                name: task.name || '',
                desc: task.desc || '',
                is_latter: task.is_latter ? 1 : 0,
                study_program_code: task.study_program_code || '',
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
                desc: '',
                is_latter: 0,
                study_program_code: '',
            };
        },
        submitForm() {
            if (this.editMode) {
                return this.updateTask();
            }

            return this.createTask();
        },
        createTask() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.post(this.baseUrl, this.form)
                .then(() => {
                    this.closeModal();
                    this.fetchTasks();
                    this.$showToast('Task created successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to create task.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        updateTask() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.put(`${this.baseUrl}/${this.form.id}`, this.form)
                .then(() => {
                    this.fetchTasks();
                    this.closeModal();
                    this.$showToast('Task updated successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to update task.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        deleteTask(task) {
            if (!window.confirm(`Delete task ${task.name}?`)) {
                return;
            }

            Repository.delete(`${this.baseUrl}/${task.id}`)
                .then(() => {
                    this.fetchTasks();
                    this.$showToast('Task deleted successfully.');
                })
                .catch(() => {
                    this.errorMessage = 'Failed to delete task.';
                });
        },
    },
};
</script>
