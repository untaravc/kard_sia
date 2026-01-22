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
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="text-xs text-muted">Keyword</label>
                    <input
                        v-model.trim="filters.keyword"
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

        <section class="grid gap-4 lg:grid-cols-[1.3fr_0.7fr]">
            <div class="rounded-2xl border border-border bg-panel">
                <div class="flex items-center justify-between border-b border-border px-5 py-4">
                    <div class="font-semibold">Users</div>
                    <div class="text-xs text-muted" v-if="pagination.total">
                        {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }}
                    </div>
                </div>
                <div class="divide-y divide-border">
                    <div v-if="loading" class="px-5 py-6 text-sm text-muted">Loading users...</div>
                    <div v-else-if="users.length === 0" class="px-5 py-6 text-sm text-muted">
                        No users found.
                    </div>
                    <div
                        v-for="user in users"
                        :key="user.id"
                        class="flex flex-wrap items-center gap-3 px-5 py-4"
                    >
                        <div class="flex-1">
                            <div class="font-semibold text-ink">{{ user.name }}</div>
                            <div class="text-xs text-muted">{{ user.email }}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                                type="button"
                                @click="openEdit(user)"
                            >
                                Edit
                            </button>
                            <button
                                class="rounded-lg bg-rose-500/10 px-3 py-1.5 text-xs text-rose-600"
                                type="button"
                                @click="deleteUser(user)"
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
            </div>

            <div class="rounded-2xl border border-border bg-panel p-5">
                <div class="flex items-center justify-between">
                    <div class="font-semibold">{{ editMode ? 'Edit User' : 'Create User' }}</div>
                    <button
                        v-if="editMode"
                        class="text-xs text-muted"
                        type="button"
                        @click="openCreate"
                    >
                        New user
                    </button>
                </div>
                <form class="mt-4 grid gap-4" @submit.prevent="submitForm">
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
            </div>
        </section>
    </div>
</template>

<script>
import Repository from '../../repository';

export default {
    data() {
        return {
            baseUrl: '/api/users',
            users: [],
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
            },
            editMode: false,
            loading: false,
            submitting: false,
            errorMessage: '',
        };
    },
    created() {
        this.fetchUsers();
    },
    methods: {
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
            this.form = {
                id: null,
                name: '',
                email: '',
                password: '',
            };
            this.errorMessage = '';
        },
        openEdit(user) {
            this.editMode = true;
            this.form = {
                id: user.id,
                name: user.name || '',
                email: user.email || '',
                password: '',
            };
            this.errorMessage = '';
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
                    this.openCreate();
                    this.fetchUsers();
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
    },
};
</script>
