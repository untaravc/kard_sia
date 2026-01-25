<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Stase Tasks</div>
                <h1 class="text-2xl font-semibold text-ink">Stase Tasks</h1>
            </div>
            <button
                class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                type="button"
                @click="openCreate"
            >
                Add Stase Task
            </button>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-full border border-border">
                    <span
                        class="h-4 w-4 rounded-full"
                        :style="{ backgroundColor: stase.color || '#e2e8f0' }"
                    ></span>
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <div class="text-lg font-semibold text-ink">{{ stase.name || 'Stase' }}</div>
                        <span class="text-xs text-muted" v-if="stase.alias">{{ stase.alias }}</span>
                    </div>
                    <div class="text-xs text-muted" v-if="stase.desc">{{ stase.desc }}</div>
                </div>
            </div>
        </section>

        <section class="relative rounded-2xl border border-border bg-panel">
            <Loading :active="loading" :is-full-page="false" />
            <div class="flex items-center justify-between border-b border-border px-5 py-4">
                <div class="font-semibold">Tasks for this Stase</div>
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
                <div v-if="!loading && staseTasks.length === 0" class="px-5 py-6 text-sm text-muted">
                    No stase tasks found.
                </div>
                <div
                    v-for="(staseTask, index) in staseTasks"
                    :key="staseTask.id"
                    class="flex flex-wrap items-center gap-3 px-5 py-4"
                >
                    <div class="w-8 text-sm font-semibold text-muted">
                        {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold text-ink">{{ staseTask.name }}</div>
                        <div class="text-xs text-muted">
                            <span v-if="staseTask.status">Status: {{ staseTask.status }}</span>
                            <span v-if="staseTask.task">• Task: {{ staseTask.task.name }}</span>
                            <span v-if="staseTask.lecture">• Lecture: {{ staseTask.lecture.name }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                            type="button"
                            @click="openEdit(staseTask)"
                        >
                            Edit
                        </button>
                        <button
                            class="rounded-lg bg-rose-500/10 px-3 py-1.5 text-xs text-rose-600"
                            type="button"
                            @click="deleteStaseTask(staseTask)"
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
            :title="editMode ? 'Edit Stase Task' : 'Create Stase Task'"
            :eyebrow="editMode ? 'Update stase task' : 'New stase task'"
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
                    <span class="text-muted">Status</span>
                    <input
                        v-model.trim="form.status"
                        type="text"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Task ID</span>
                    <input
                        v-model.number="form.task_id"
                        type="number"
                        min="1"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Lecture ID</span>
                    <input
                        v-model.number="form.lecture_id"
                        type="number"
                        min="1"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <div v-if="errorMessage" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ errorMessage }}
                </div>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="submit"
                    :disabled="submitting"
                >
                    {{ submitting ? 'Saving...' : editMode ? 'Update Stase Task' : 'Create Stase Task' }}
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
import Swal from 'sweetalert2';

export default {
    components: {
        Loading,
        Modal,
    },
    data() {
        return {
            baseUrl: '/api/stase-tasks',
            staseTasks: [],
            stase: {},
            pagination: {},
            filters: {
                keyword: '',
                page: 1,
            },
            form: {
                id: null,
                stase_id: null,
                task_id: null,
                lecture_id: null,
                name: '',
                status: '',
            },
            editMode: false,
            modalOpen: false,
            loading: false,
            submitting: false,
            errorMessage: '',
            toast: null,
        };
    },
    created() {
        this.initToast();
        this.fetchStase();
        this.fetchStaseTasks();
    },
    watch: {
        '$route.params.stase_id'() {
            this.filters.page = 1;
            this.fetchStase();
            this.fetchStaseTasks();
        },
    },
    methods: {
        initToast() {
            this.toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        },
        showToast(title, icon = 'success') {
            if (!this.toast) {
                this.initToast();
            }
            this.toast.fire({ title, icon });
        },
        fetchStase() {
            const staseId = this.$route.params.stase_id;
            if (!staseId) {
                this.stase = {};
                return;
            }

            return Repository.get(`/api/stases/${staseId}`)
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.stase = result || {};
                })
                .catch(() => {
                    this.stase = {};
                });
        },
        fetchStaseTasks() {
            const staseId = this.$route.params.stase_id;
            if (!staseId) {
                this.staseTasks = [];
                this.pagination = {};
                return;
            }

            this.loading = true;
            this.errorMessage = '';

            return Repository.get(this.baseUrl, {
                params: {
                    ...this.filters,
                    stase_id: staseId,
                },
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];

                    this.staseTasks = data;
                    this.pagination = result || {};
                })
                .catch(() => {
                    this.staseTasks = [];
                    this.pagination = {};
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        applyFilter() {
            this.filters.page = 1;
            this.fetchStaseTasks();
        },
        resetFilter() {
            this.filters.keyword = '';
            this.filters.page = 1;
            this.fetchStaseTasks();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchStaseTasks();
        },
        openCreate() {
            this.editMode = false;
            this.resetForm();
            this.errorMessage = '';
            this.modalOpen = true;
        },
        openEdit(staseTask) {
            this.editMode = true;
            this.form = {
                id: staseTask.id,
                stase_id: staseTask.stase_id,
                task_id: staseTask.task_id ?? null,
                lecture_id: staseTask.lecture_id ?? null,
                name: staseTask.name || '',
                status: staseTask.status || '',
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
                stase_id: Number(this.$route.params.stase_id),
                task_id: null,
                lecture_id: null,
                name: '',
                status: '',
            };
        },
        submitForm() {
            if (this.editMode) {
                return this.updateStaseTask();
            }

            return this.createStaseTask();
        },
        createStaseTask() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.post(this.baseUrl, this.form)
                .then(() => {
                    this.closeModal();
                    this.fetchStaseTasks();
                    this.showToast('Stase task created successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to create stase task.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        updateStaseTask() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.put(`${this.baseUrl}/${this.form.id}`, this.form)
                .then(() => {
                    this.fetchStaseTasks();
                    this.closeModal();
                    this.showToast('Stase task updated successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to update stase task.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        deleteStaseTask(staseTask) {
            if (!window.confirm(`Delete stase task ${staseTask.name}?`)) {
                return;
            }

            Repository.delete(`${this.baseUrl}/${staseTask.id}`)
                .then(() => {
                    this.fetchStaseTasks();
                    this.showToast('Stase task deleted successfully.');
                })
                .catch(() => {
                    this.errorMessage = 'Failed to delete stase task.';
                });
        },
    },
};
</script>
