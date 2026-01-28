<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Lecture Management</div>
                <h1 class="text-2xl font-semibold text-ink">Lectures</h1>
            </div>
            <button
                class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                type="button"
                @click="openCreate"
            >
                Add Lecture
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
                        placeholder="Search name or email..."
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
                <div class="font-semibold">Lectures</div>
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
                <div v-if="!loading && lectures.length === 0" class="px-5 py-6 text-sm text-muted">
                    No lectures found.
                </div>
                <div
                    v-for="(lecture, index) in lectures"
                    :key="lecture.id"
                    class="flex flex-wrap items-center gap-3 px-5 py-4"
                >
                    <div class="w-8 text-sm font-semibold text-muted">
                        {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <div class="font-semibold text-ink">{{ lecture.name }}</div>
                            <span v-if="lecture.status !== null && lecture.status !== undefined" class="text-xs text-muted">
                                Status: {{ lecture.status }}
                            </span>
                            <span v-if="lecture.is_in_house" class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                In House
                            </span>
                        </div>
                        <div class="text-xs text-muted">
                            <span v-if="lecture.email">{{ lecture.email }}</span>
                            <span v-if="lecture.name_alt">• {{ lecture.name_alt }}</span>
                        </div>
                    </div>
                    <div class="relative action-dropdown">
                        <button
                            class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                            type="button"
                            @click.stop="toggleActionMenu(lecture.id)"
                        >
                            Actions
                        </button>
                        <div
                            v-if="actionMenuOpenId === lecture.id"
                            class="absolute right-0 z-10 mt-2 w-36 rounded-xl border border-border bg-white p-1 shadow-lg"
                        >
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                type="button"
                                @click="handleAction('logAs', lecture)"
                            >
                                Log As
                            </button>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                type="button"
                                @click="handleAction('edit', lecture)"
                            >
                                Edit
                            </button>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-rose-600 hover:bg-rose-50"
                                type="button"
                                @click="handleAction('delete', lecture)"
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
            :title="editMode ? 'Edit Lecture' : 'Create Lecture'"
            :eyebrow="editMode ? 'Update lecture' : 'New lecture'"
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
                    <span class="text-muted">Email</span>
                    <input
                        v-model.trim="form.email"
                        type="email"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Password</span>
                    <input
                        v-model.trim="form.password"
                        type="password"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Alternate Name</span>
                    <input
                        v-model.trim="form.name_alt"
                        type="text"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Status</span>
                    <select
                        v-model="form.status"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="active">Active</option>
                        <option value="nonactive">Nonactive</option>
                    </select>
                </label>
                <label class="flex items-center gap-3 text-sm">
                    <input
                        v-model.number="form.is_in_house"
                        type="checkbox"
                        :true-value="1"
                        :false-value="0"
                        class="h-4 w-4 rounded border border-border"
                    />
                    <span class="text-muted">In House</span>
                </label>
                <div v-if="errorMessage" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ errorMessage }}
                </div>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="submit"
                    :disabled="submitting"
                >
                    {{ submitting ? 'Saving...' : editMode ? 'Update Lecture' : 'Create Lecture' }}
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
            baseUrl: '/api/lectures',
            lectures: [],
            pagination: {},
            filters: {
                keyword: '',
                page: 1,
            },
            form: {
                id: null,
                name: '',
                email: '',
                password: '',
                name_alt: '',
                last_act: '',
                status: null,
                is_in_house: 0,
            },
            editMode: false,
            modalOpen: false,
            loading: false,
            submitting: false,
            errorMessage: '',
            toast: null,
            actionMenuOpenId: null,
        };
    },
    created() {
        this.initToast();
        this.fetchLectures();
    },
    mounted() {
        document.addEventListener('click', this.handleDocumentClick);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.handleDocumentClick);
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
        fetchLectures() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(this.baseUrl, {
                params: this.filters,
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];

                    this.lectures = data;
                    this.pagination = result || {};
                })
                .catch(() => {
                    this.lectures = [];
                    this.pagination = {};
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        applyFilter() {
            this.filters.page = 1;
            this.fetchLectures();
        },
        resetFilter() {
            this.filters.keyword = '';
            this.filters.page = 1;
            this.fetchLectures();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchLectures();
        },
        toggleActionMenu(lectureId) {
            this.actionMenuOpenId = this.actionMenuOpenId === lectureId ? null : lectureId;
        },
        closeActionMenu() {
            this.actionMenuOpenId = null;
        },
        handleAction(action, lecture) {
            this.closeActionMenu();
            if (action === 'logAs') {
                this.logAs(lecture);
                return;
            }
            if (action === 'edit') {
                this.openEdit(lecture);
                return;
            }
            if (action === 'delete') {
                this.deleteLecture(lecture);
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
        openCreate() {
            this.editMode = false;
            this.resetForm();
            this.errorMessage = '';
            this.modalOpen = true;
        },
        openEdit(lecture) {
            this.editMode = true;
            this.form = {
                id: lecture.id,
                name: lecture.name || '',
                email: lecture.email || '',
                password: '',
                name_alt: lecture.name_alt || '',
                last_act: lecture.last_act || '',
                status: lecture.status ?? null,
                is_in_house: lecture.is_in_house ? 1 : 0,
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
                email: '',
                password: '',
                name_alt: '',
                last_act: '',
                status: null,
                is_in_house: 0,
            };
        },
        submitForm() {
            if (this.editMode) {
                return this.updateLecture();
            }

            return this.createLecture();
        },
        createLecture() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.post(this.baseUrl, this.form)
                .then(() => {
                    this.closeModal();
                    this.fetchLectures();
                    this.showToast('Lecture created successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to create lecture.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        updateLecture() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.put(`${this.baseUrl}/${this.form.id}`, this.form)
                .then(() => {
                    this.fetchLectures();
                    this.closeModal();
                    this.showToast('Lecture updated successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to update lecture.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        deleteLecture(lecture) {
            if (!window.confirm(`Delete lecture ${lecture.name}?`)) {
                return;
            }

            Repository.delete(`${this.baseUrl}/${lecture.id}`)
                .then(() => {
                    this.fetchLectures();
                    this.showToast('Lecture deleted successfully.');
                })
                .catch(() => {
                    this.errorMessage = 'Failed to delete lecture.';
                });
        },
        logAs(lecture) {
            if (!lecture || !lecture.id) {
                return;
            }

            Repository.post('/api/log-as', {
                auth_type: 'lecture',
                auth_id: lecture.id,
            })
                .then((response) => {
                    const token = response && response.data && response.data.result
                        ? response.data.result.token
                        : null;
                    if (!token) {
                        this.errorMessage = 'Failed to log as lecture.';
                        return;
                    }
                    localStorage.setItem('token', token);
                    window.open('/cblu/dashboard', '_blank');
                })
                .catch(() => {
                    this.errorMessage = 'Failed to log as lecture.';
                });
        },
    },
};
</script>
