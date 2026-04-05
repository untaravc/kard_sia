<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Registration Management</div>
                <h1 class="text-2xl font-semibold text-ink">Registrations</h1>
            </div>
            <button
                class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                type="button"
                @click="openCreate"
            >
                Add Registration
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
                        <option value="">All</option>
                        <option v-for="option in statusOptions" :key="option.value" :value="String(option.value)">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
                <div class="flex-1 min-w-[180px]">
                    <label class="text-xs text-muted">Period</label>
                    <select
                        v-model="filters.registration_period"
                        @change="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">All</option>
                        <option v-for="period in periodOptions" :key="period" :value="period">
                            {{ period }}
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
                <div class="font-semibold">Registrations</div>
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
                <div v-if="!loading && registrations.length === 0" class="px-5 py-6 text-sm text-muted">
                    No registrations found.
                </div>
                <div
                    v-for="(registration, index) in registrations"
                    :key="registration.id"
                    class="flex flex-wrap items-center gap-3 px-5 py-4"
                >
                    <div class="w-8 text-sm font-semibold text-muted">
                        {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="font-semibold text-ink">{{ registration.name }}</div>
                            <span v-if="registration.status_label" class="rounded-lg bg-slate-100 px-2 py-0.5 text-xs text-muted">
                                {{ registration.status_label }}
                            </span>
                        </div>
                        <div class="text-xs text-muted">
                            <span v-if="registration.email">{{ registration.email }}</span>
                            <span v-if="registration.phone">• {{ registration.phone }}</span>
                            <span v-if="registration.registration_period">• Period: {{ registration.registration_period }}</span>
                            <span v-if="registration.age !== null && registration.age !== undefined">• Age: {{ registration.age }}</span>
                        </div>
                    </div>
                    <div class="relative action-dropdown">
                        <button
                            class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                            type="button"
                            @click.stop="toggleActionMenu(registration.id)"
                        >
                            Actions
                        </button>
                        <div
                            v-if="actionMenuOpenId === registration.id"
                            class="absolute right-0 z-10 mt-2 w-36 rounded-xl border border-border bg-white p-1 shadow-lg"
                        >
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                type="button"
                                @click="openDetail(registration)"
                            >
                                Detail
                            </button>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                type="button"
                                @click="openEdit(registration)"
                            >
                                Edit
                            </button>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-rose-600 hover:bg-rose-50"
                                type="button"
                                @click="deleteRegistration(registration)"
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
            :title="editMode ? 'Edit Registration' : 'Create Registration'"
            :eyebrow="editMode ? 'Update registration' : 'New registration'"
            size="lg"
            @close="closeModal"
        >
            <form class="grid gap-4" @submit.prevent="submitForm">
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Registration Period</span>
                    <input
                        v-model.trim="form.registration_period"
                        type="text"
                        placeholder="e.g. 2026-01"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Name</span>
                        <input
                            v-model.trim="form.name"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                            required
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Phone</span>
                        <input
                            v-model.trim="form.phone"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Email</span>
                        <input
                            v-model.trim="form.email"
                            type="email"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                            required
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Status</span>
                        <select
                            v-model.number="form.status"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        >
                            <option :value="null">Not Set</option>
                            <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </label>
                </div>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">
                        Password
                        <span v-if="editMode" class="text-xs text-muted">(leave empty to keep current)</span>
                    </span>
                    <input
                        v-model.trim="form.password"
                        type="password"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        :required="!editMode"
                        minlength="6"
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
                    {{ submitting ? 'Saving...' : editMode ? 'Update Registration' : 'Create Registration' }}
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
    computed: {
        periodOptions() {
            const start = new Date(Date.UTC(2025, 0, 1));

            const now = new Date();
            const nowYear = now.getUTCFullYear();
            const nowMonth = now.getUTCMonth(); // 0-11
            const end = nowMonth < 6
                ? new Date(Date.UTC(nowYear, 6, 1)) // July 1 (next upcoming boundary)
                : new Date(Date.UTC(nowYear + 1, 0, 1)); // Jan 1 next year

            const periods = [];
            const cursor = new Date(start.getTime());
            while (cursor.getTime() <= end.getTime()) {
                periods.push(cursor.toISOString().slice(0, 10));
                cursor.setUTCMonth(cursor.getUTCMonth() + 6);
            }

            return periods.reverse();
        },
    },
    data() {
        return {
            baseUrl: '/api/registrations',
            registrations: [],
            pagination: {},
            filters: {
                keyword: '',
                status: '',
                registration_period: '',
                page: 1,
            },
            form: {
                id: null,
                registration_period: '',
                name: '',
                email: '',
                phone: '',
                status: null,
                password: '',
            },
            statusOptions: [
                { value: 100, label: 'Pengisian Pendaftaran' },
                { value: 101, label: 'Pengisian Pendaftaran Selesai' },
                { value: 200, label: 'Lolos Administrasi' },
                { value: 201, label: 'Tidak Lolos Administrasi' },
                { value: 300, label: 'Lolos Ujian Tulis - Jurnal' },
                { value: 301, label: 'Tidak Lolos Ujian Tulis - Jurnal' },
                { value: 400, label: 'Diterima' },
                { value: 401, label: 'Tidak Lolos Ujian Wawancara' },
                { value: 500, label: 'Dibatalkan' },
            ],
            editMode: false,
            modalOpen: false,
            loading: false,
            submitting: false,
            errorMessage: '',
            actionMenuOpenId: null,
        };
    },
    created() {
        this.fetchRegistrations();
    },
    mounted() {
        document.addEventListener('click', this.handleDocumentClick);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.handleDocumentClick);
    },
    methods: {
        fetchRegistrations() {
            this.loading = true;
            this.errorMessage = '';

            const params = {
                ...this.filters,
            };

            if (!params.status) {
                delete params.status;
            }
            if (!params.registration_period) {
                delete params.registration_period;
            }
            if (!params.keyword) {
                delete params.keyword;
            }

            return Repository.get(this.baseUrl, {
                params,
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];
                    this.registrations = data;
                    this.pagination = result || {};
                })
                .catch(() => {
                    this.registrations = [];
                    this.pagination = {};
                    this.errorMessage = 'Failed to load registrations.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        toggleActionMenu(registrationId) {
            this.actionMenuOpenId = this.actionMenuOpenId === registrationId ? null : registrationId;
        },
        closeActionMenu() {
            this.actionMenuOpenId = null;
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
            this.fetchRegistrations();
        },
        resetFilter() {
            this.filters.keyword = '';
            this.filters.status = '';
            this.filters.registration_period = '';
            this.filters.page = 1;
            this.fetchRegistrations();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchRegistrations();
        },
        openCreate() {
            this.editMode = false;
            this.resetForm();
            this.errorMessage = '';
            this.modalOpen = true;
        },
        openEdit(registration) {
            this.editMode = true;
            this.form = {
                id: registration.id,
                registration_period: registration.registration_period || '',
                name: registration.name || '',
                email: registration.email || '',
                phone: registration.phone || '',
                status: registration.status ?? null,
                password: '',
            };
            this.errorMessage = '';
            this.modalOpen = true;
        },
        openDetail(registration) {
            this.closeActionMenu();
            if (!registration || !registration.id) {
                return;
            }
            this.$router.push(`/blu/registrations/${registration.id}`);
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
                registration_period: '',
                name: '',
                email: '',
                phone: '',
                status: null,
                password: '',
            };
        },
        submitForm() {
            this.submitting = true;
            this.errorMessage = '';

            const payload = {
                registration_period: this.form.registration_period || null,
                name: this.form.name,
                email: this.form.email,
                phone: this.form.phone || null,
                status: this.form.status ?? null,
                password: this.form.password || '',
            };

            const request = this.editMode
                ? Repository.put(`${this.baseUrl}/${this.form.id}`, payload)
                : Repository.post(this.baseUrl, payload);

            return request
                .then(() => {
                    this.fetchRegistrations();
                    this.closeModal();
                    this.$showToast(this.editMode ? 'Registration updated successfully.' : 'Registration created successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : this.editMode
                            ? 'Failed to update registration.'
                            : 'Failed to create registration.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        deleteRegistration(registration) {
            this.closeActionMenu();
            if (!window.confirm(`Delete registration ${registration.name}?`)) {
                return;
            }

            Repository.delete(`${this.baseUrl}/${registration.id}`)
                .then(() => {
                    this.fetchRegistrations();
                    this.$showToast('Registration deleted successfully.');
                })
                .catch(() => {
                    this.errorMessage = 'Failed to delete registration.';
                });
        },
    },
};
</script>
