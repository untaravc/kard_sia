<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Student Management</div>
                <h1 class="text-2xl font-semibold text-ink">Students</h1>
            </div>
            <button
                class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                type="button"
                @click="openCreate"
            >
                Add Student
            </button>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="text-xs text-muted">Keyword</label>
                    <input
                        v-model.trim="filters.keyword"
                        type="text"
                        placeholder="Search name or email..."
                        @keyup.enter="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="flex-1 min-w-[180px]">
                    <label class="text-xs text-muted">Status</label>
                    <select
                        v-model="filters.status"
                        @change="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option :value="null">All</option>
                        <option :value="1">Active</option>
                        <option :value="0">Nonactive</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[180px]">
                    <label class="text-xs text-muted">Year</label>
                    <select
                        v-model="filters.year"
                        @change="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">All</option>
                        <option v-for="year in yearOptions" :key="year" :value="year">
                            {{ year }}
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
                <div class="font-semibold">Students</div>
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
                <div v-if="!loading && students.length === 0" class="px-5 py-6 text-sm text-muted">
                    No students found.
                </div>
                <div
                    v-for="(student, index) in students"
                    :key="student.id"
                    class="flex flex-wrap items-center gap-3 px-5 py-4"
                >
                    <div class="w-8 text-sm font-semibold text-muted">
                        {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <div class="font-semibold text-ink">{{ student.name }}</div>
                            <span v-if="student.status !== null && student.status !== undefined" class="text-xs text-muted">
                                Status: {{ student.status }}
                            </span>
                        </div>
                        <div class="text-xs text-muted">
                            <span v-if="student.email">{{ student.email }}</span>
                            <span v-if="student.year">• Year: {{ student.year }}</span>
                        </div>
                    </div>
                    <div class="relative action-dropdown">
                        <button
                            class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                            type="button"
                            @click.stop="toggleActionMenu(student.id)"
                        >
                            Actions
                        </button>
                        <div
                            v-if="actionMenuOpenId === student.id"
                            class="absolute right-0 z-10 mt-2 w-36 rounded-xl border border-border bg-white p-1 shadow-lg"
                        >
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                type="button"
                                @click="handleAction('score', student)"
                            >
                                Score
                            </button>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                type="button"
                                @click="handleAction('presence', student)"
                            >
                                Presences
                            </button>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                type="button"
                                @click="handleAction('logAs', student)"
                            >
                                Log As
                            </button>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                type="button"
                                @click="handleAction('edit', student)"
                            >
                                Edit
                            </button>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-rose-600 hover:bg-rose-50"
                                type="button"
                                @click="handleAction('delete', student)"
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
            :title="editMode ? 'Edit Student' : 'Create Student'"
            :eyebrow="editMode ? 'Update student' : 'New student'"
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
                    <span class="text-muted">Year</span>
                    <input
                        v-model.trim="form.year"
                        type="text"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Status</span>
                    <select
                        v-model.number="form.status"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="active">Active</option>
                        <option value="nonactive">Nonactive</option>
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
                    {{ submitting ? 'Saving...' : editMode ? 'Update Student' : 'Create Student' }}
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
            baseUrl: '/api/students',
            students: [],
            pagination: {},
            filters: {
                keyword: '',
                status: null,
                year: '',
                page: 1,
            },
            form: {
                id: null,
                name: '',
                email: '',
                password: '',
                year: '',
                status: null,
            },
            editMode: false,
            modalOpen: false,
            loading: false,
            submitting: false,
            errorMessage: '',
            yearOptions: [],
            actionMenuOpenId: null,
        };
    },
    created() {
        this.yearOptions = this.buildYearOptions();
        this.fetchStudents();
    },
    mounted() {
        document.addEventListener('click', this.handleDocumentClick);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.handleDocumentClick);
    },
    methods: {
        buildYearOptions() {
            const options = [];
            const now = new Date();
            const currentYear = now.getFullYear();
            const currentMonth = now.getMonth() + 1;
            const availableMonths = [1, 7];

            for (let year = 2016; year <= currentYear; year += 1) {
                availableMonths.forEach((month) => {
                    if (year < currentYear || month <= currentMonth) {
                        const monthLabel = String(month).padStart(2, '0');
                        options.push(`${year}-${monthLabel}`);
                    }
                });
            }

            return options;
        },
        fetchStudents() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(this.baseUrl, {
                params: this.filters,
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];

                    this.students = data;
                    this.pagination = result || {};
                })
                .catch(() => {
                    this.students = [];
                    this.pagination = {};
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        toggleActionMenu(studentId) {
            this.actionMenuOpenId = this.actionMenuOpenId === studentId ? null : studentId;
        },
        closeActionMenu() {
            this.actionMenuOpenId = null;
        },
        handleAction(action, student) {
            this.closeActionMenu();
            if (action === 'score') {
                this.$router.push(`/blu/students/${student.id}/score`);
                return;
            }
            if (action === 'presence') {
                this.$router.push(`/blu/presences/student/${student.id}`);
                return;
            }
            if (action === 'logAs') {
                this.logAs(student);
                return;
            }
            if (action === 'edit') {
                this.openEdit(student);
                return;
            }
            if (action === 'delete') {
                this.deleteStudent(student);
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
        applyFilter() {
            this.filters.page = 1;
            this.fetchStudents();
        },
        resetFilter() {
            this.filters.keyword = '';
            this.filters.status = null;
            this.filters.year = '';
            this.filters.page = 1;
            this.fetchStudents();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchStudents();
        },
        openCreate() {
            this.editMode = false;
            this.resetForm();
            this.errorMessage = '';
            this.modalOpen = true;
        },
        openEdit(student) {
            this.editMode = true;
            this.form = {
                id: student.id,
                name: student.name || '',
                email: student.email || '',
                password: '',
                year: student.year || '',
                status: student.status ?? null,
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
                year: '',
                status: null,
            };
        },
        submitForm() {
            if (this.editMode) {
                return this.updateStudent();
            }

            return this.createStudent();
        },
        createStudent() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.post(this.baseUrl, this.form)
                .then(() => {
                    this.closeModal();
                    this.fetchStudents();
                    this.$showToast('Student created successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to create student.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        updateStudent() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.put(`${this.baseUrl}/${this.form.id}`, this.form)
                .then(() => {
                    this.fetchStudents();
                    this.closeModal();
                    this.$showToast('Student updated successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to update student.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        deleteStudent(student) {
            if (!window.confirm(`Delete student ${student.name}?`)) {
                return;
            }

            Repository.delete(`${this.baseUrl}/${student.id}`)
                .then(() => {
                    this.fetchStudents();
                    this.$showToast('Student deleted successfully.');
                })
                .catch(() => {
                    this.errorMessage = 'Failed to delete student.';
                });
        },
        logAs(student) {
            if (!student || !student.id) {
                return;
            }

            Repository.post('/api/log-as', {
                auth_type: 'student',
                auth_id: student.id,
            })
                .then((response) => {
                    const token = response && response.data && response.data.result
                        ? response.data.result.token
                        : null;
                    if (!token) {
                        this.errorMessage = 'Failed to log as student.';
                        return;
                    }
                    localStorage.setItem('token', token);
                    window.open('/blu/dashboard', '_blank');
                })
                .catch(() => {
                    this.errorMessage = 'Failed to log as student.';
                });
        },
    },
};
</script>
