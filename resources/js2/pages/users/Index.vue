<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">User Management</div>
                <h1 class="text-2xl font-semibold text-ink">Admin Users</h1>
            </div>
            <button
                class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                type="button"
                @click="openCreate"
            >
                Add User
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
                        placeholder="Search name..."
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
                <div class="font-semibold">Users</div>
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
                <div v-if="!loading && users.length === 0" class="px-5 py-6 text-sm text-muted">
                    No users found.
                </div>
                <div
                    v-for="(user, index) in users"
                    :key="user.id"
                    class="flex flex-wrap items-center gap-3 px-5 py-4"
                >
                    <div class="w-8 text-sm font-semibold text-muted">
                        {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <div class="font-semibold text-ink">{{ user.name }}</div>
                            <span
                                v-if="roleName(user.role_id)"
                                class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700"
                            >
                                {{ roleName(user.role_id) }}
                            </span>
                        </div>
                        <div class="text-xs text-muted">{{ user.email }}</div>
                    </div>
                    <div class="flex w-24 flex-col items-center gap-1">
                        <button
                            type="button"
                            role="switch"
                            :aria-checked="user.status === 'active'"
                            :disabled="statusUpdating"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition disabled:opacity-50"
                            :class="user.status === 'active' ? 'bg-emerald-500' : 'bg-slate-300'"
                            @click.stop="handleStatusToggle(user)"
                        >
                            <span
                                class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition"
                                :class="user.status === 'active' ? 'translate-x-6' : 'translate-x-1'"
                            ></span>
                        </button>
                        <span class="text-[11px] text-muted">
                            {{ user.status === 'active' ? 'Active' : 'Nonactive' }}
                        </span>
                    </div>
                    <div class="relative action-dropdown">
                        <button
                            class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                            type="button"
                            @click.stop="toggleActionMenu(user.id)"
                        >
                            Actions
                        </button>
                        <div
                            v-if="actionMenuOpenId === user.id"
                            class="absolute right-0 z-10 mt-2 w-36 rounded-xl border border-border bg-white p-1 shadow-lg"
                        >
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                type="button"
                                @click="handleAction('edit', user)"
                            >
                                Edit
                            </button>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-rose-600 hover:bg-rose-50"
                                type="button"
                                @click="handleAction('delete', user)"
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
            :title="editMode ? 'Edit User' : 'Create User'"
            :eyebrow="editMode ? 'Update access' : 'New access'"
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
                        v-model="form.password"
                        type="password"
                        :placeholder="editMode ? 'Leave blank to keep current password' : 'Set a password'"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                    <span v-if="editMode" class="text-[11px] text-muted">
                        Leave blank to keep the current password.
                    </span>
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Role</span>
                    <select
                        v-model="form.role_id"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option :value="null">-</option>
                        <option v-for="option in roles" :key="option.id" :value="option.id">
                            {{ option.name }}
                        </option>
                    </select>
                </label>
                <div class="grid gap-2 text-sm">
                    <span class="text-muted">Study Programs</span>
                    <div class="grid gap-2 rounded-xl border border-border bg-white p-3 sm:grid-cols-2">
                        <span v-if="studyPrograms.length === 0" class="text-xs text-muted">
                            No study programs found.
                        </span>
                        <label
                            v-for="option in studyPrograms"
                            :key="option.id"
                            class="flex items-center gap-2 text-sm"
                        >
                            <input
                                type="checkbox"
                                class="h-4 w-4 rounded border border-border"
                                :checked="isStudyProgramSelected(option.code)"
                                @change="toggleStudyProgramSelection(option.code, $event.target.checked)"
                            />
                            <span class="text-ink">{{ option.name }}</span>
                        </label>
                    </div>
                </div>
                <div v-if="errorMessage" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ errorMessage }}
                </div>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="submit"
                    :disabled="submitting"
                >
                    {{ submitting ? 'Saving...' : editMode ? 'Update User' : 'Create User' }}
                </button>
            </form>
        </Modal>

        <Modal
            :open="statusModalOpen"
            title="Aktifkan Akun?"
            size="sm"
            @close="closeStatusModal"
        >
            <div class="grid gap-4 text-sm">
                <p class="text-muted">
                    Akun
                    <span class="font-semibold text-ink">{{ statusTargetUser ? statusTargetUser.name : '' }}</span>
                    akan diaktifkan.
                </p>
                <label class="flex items-center gap-3 text-sm">
                    <input
                        v-model="sendActivationEmail"
                        type="checkbox"
                        class="h-4 w-4 rounded border border-border"
                    />
                    <span class="text-muted">Kirim notifikasi email ke pengguna</span>
                </label>
            </div>
            <template #footer>
                <button
                    class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                    type="button"
                    @click="closeStatusModal"
                >
                    Batal
                </button>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="button"
                    :disabled="statusUpdating"
                    @click="confirmActivation"
                >
                    {{ statusUpdating ? 'Processing...' : 'Ya' }}
                </button>
            </template>
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
    mixins: [persistFilters('users')],
    data() {
        return {
            baseUrl: '/api/users',
            users: [],
            pagination: {},
            studyPrograms: [],
            roles: [],
            filters: {
                keyword: '',
                page: 1,
            },
            form: {
                id: null,
                name: '',
                email: '',
                password: '',
                role_id: null,
                study_program_codes: [],
            },
            editMode: false,
            modalOpen: false,
            loading: false,
            submitting: false,
            errorMessage: '',
            actionMenuOpenId: null,
            statusModalOpen: false,
            statusTargetUser: null,
            sendActivationEmail: true,
            statusUpdating: false,
        };
    },
    created() {
        this.fetchStudyPrograms();
        this.fetchRoles();
        this.fetchUsers();
    },
    mounted() {
        document.addEventListener('click', this.handleDocumentClick);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.handleDocumentClick);
    },
    methods: {
        toggleActionMenu(userId) {
            this.actionMenuOpenId = this.actionMenuOpenId === userId ? null : userId;
        },
        closeActionMenu() {
            this.actionMenuOpenId = null;
        },
        handleAction(action, user) {
            this.closeActionMenu();
            if (action === 'edit') {
                this.openEdit(user);
                return;
            }
            if (action === 'delete') {
                this.deleteUser(user);
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
        fetchRoles() {
            return Repository.get('/api/role-list')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.roles = Array.isArray(result) ? result : [];
                })
                .catch(() => {
                    this.roles = [];
                });
        },
        roleName(roleId) {
            if (!roleId) {
                return '';
            }
            const role = this.roles.find((option) => option.id === roleId);
            return role ? role.name : '';
        },
        isStudyProgramSelected(code) {
            const codes = Array.isArray(this.form.study_program_codes) ? this.form.study_program_codes : [];
            return codes.includes(code);
        },
        toggleStudyProgramSelection(code, checked) {
            const current = Array.isArray(this.form.study_program_codes) ? [...this.form.study_program_codes] : [];
            this.form.study_program_codes = checked
                ? Array.from(new Set([...current, code]))
                : current.filter((item) => item !== code);
        },
        fetchUsers() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(this.baseUrl, {
                params: this.filters,
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];

                    this.users = data;
                    this.pagination = result || {};
                })
                .catch(() => {
                    this.users = [];
                    this.pagination = {};
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        applyFilter() {
            this.filters.page = 1;
            this.fetchUsers();
        },
        resetFilter() {
            this.filters.keyword = '';
            this.filters.page = 1;
            this.fetchUsers();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchUsers();
        },
        openCreate() {
            this.editMode = false;
            this.resetForm();
            this.errorMessage = '';
            this.modalOpen = true;
        },
        openEdit(user) {
            this.editMode = true;
            this.form = {
                id: user.id,
                name: user.name || '',
                email: user.email || '',
                password: '',
                role_id: user.role_id ?? null,
                study_program_codes: Array.isArray(user.study_program_codes) ? user.study_program_codes : [],
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
                role_id: null,
                study_program_codes: [],
            };
        },
        submitForm() {
            if (this.editMode) {
                return this.updateUser();
            }

            return this.createUser();
        },
        createUser() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.post(this.baseUrl, this.form)
                .then(() => {
                    this.closeModal();
                    this.fetchUsers();
                    this.$showToast('User created successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to create user.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        updateUser() {
            this.submitting = true;
            this.errorMessage = '';

            const payload = { ...this.form };
            if (!payload.password) {
                delete payload.password;
            }

            return Repository.put(`${this.baseUrl}/${this.form.id}`, payload)
                .then(() => {
                    this.fetchUsers();
                    this.closeModal();
                    this.$showToast('User updated successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to update user.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        deleteUser(user) {
            if (!window.confirm(`Delete user ${user.name}?`)) {
                return;
            }

            Repository.delete(`${this.baseUrl}/${user.id}`)
                .then(() => {
                    this.fetchUsers();
                })
                .catch(() => {
                    this.errorMessage = 'Failed to delete user.';
                });
        },
        handleStatusToggle(user) {
            if (this.statusUpdating) {
                return;
            }

            if (user.status === 'active') {
                this.updateUserStatus(user, 'nonactive');
                return;
            }

            this.statusTargetUser = user;
            this.sendActivationEmail = true;
            this.statusModalOpen = true;
        },
        closeStatusModal() {
            this.statusModalOpen = false;
            this.statusTargetUser = null;
            this.sendActivationEmail = true;
        },
        confirmActivation() {
            if (!this.statusTargetUser) {
                return;
            }

            this.updateUserStatus(this.statusTargetUser, 'active', this.sendActivationEmail)
                .then(() => {
                    this.closeStatusModal();
                });
        },
        updateUserStatus(user, status, sendEmail = false) {
            this.statusUpdating = true;
            this.errorMessage = '';

            return Repository.patch(`${this.baseUrl}/${user.id}/status`, {
                status,
                send_email: sendEmail,
            })
                .then(() => {
                    user.status = status;
                    this.$showToast(status === 'active' ? 'User activated successfully.' : 'User deactivated successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to update user status.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.statusUpdating = false;
                });
        },
    },
};
</script>
