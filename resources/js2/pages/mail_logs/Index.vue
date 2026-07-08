<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Mail Log Management</div>
                <h1 class="text-2xl font-semibold text-ink">Mail Logs</h1>
            </div>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="text-xs text-muted">Keyword</label>
                    <input
                        v-model.trim="filters.keyword"
                        type="text"
                        @keyup.enter="applyFilter"
                        placeholder="Search name, email or title..."
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="min-w-[160px]">
                    <label class="text-xs text-muted">Status</label>
                    <select
                        v-model="filters.status"
                        @change="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">All</option>
                        <option v-for="option in statusOptions" :key="option" :value="option">
                            {{ option }}
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
                <div class="font-semibold">Mail Logs</div>
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
                <div v-if="!loading && mailLogs.length === 0" class="px-5 py-6 text-sm text-muted">
                    No mail logs found.
                </div>
                <div
                    v-for="(mailLog, index) in mailLogs"
                    :key="mailLog.id"
                    class="flex flex-wrap items-center gap-3 px-5 py-4"
                >
                    <div class="w-8 text-sm font-semibold text-muted">
                        {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                    </div>
                    <div class="flex-1 min-w-[220px]">
                        <div class="flex items-center gap-2">
                            <div class="font-semibold text-ink">{{ mailLog.title || mailLog.name || '-' }}</div>
                            <span
                                v-if="mailLog.status"
                                class="rounded-full px-2 py-0.5 text-[11px] font-medium"
                                :class="statusBadgeClass(mailLog.status)"
                            >
                                {{ mailLog.status }}
                            </span>
                        </div>
                        <div class="text-xs text-muted">
                            <span v-if="mailLog.destination_email">{{ mailLog.destination_email }}</span>
                            <span v-if="mailLog.destination_phone"> · {{ mailLog.destination_phone }}</span>
                            <span v-if="mailLog.label"> · {{ mailLog.label }}</span>
                        </div>
                        <div class="text-xs text-muted">
                            <span v-if="mailLog.template">Template: {{ mailLog.template }}</span>
                            <span v-if="mailLog.sent_at"> · Sent {{ formatDateTime(mailLog.sent_at) }}</span>
                        </div>
                    </div>
                    <div class="relative action-dropdown">
                        <button
                            class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                            type="button"
                            @click.stop="toggleActionMenu(mailLog.id)"
                        >
                            Actions
                        </button>
                        <div
                            v-if="actionMenuOpenId === mailLog.id"
                            class="absolute right-0 z-10 mt-2 w-40 rounded-xl border border-border bg-white p-1 shadow-lg"
                        >
                            <a
                                v-if="mailLog.document_url"
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                :href="mailLog.document_url"
                                target="_blank"
                                rel="noopener"
                                @click="closeActionMenu"
                            >
                                View Document
                            </a>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                type="button"
                                @click="handleAction('edit-status', mailLog)"
                            >
                                Edit Status
                            </button>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-rose-600 hover:bg-rose-50"
                                type="button"
                                @click="handleAction('delete', mailLog)"
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
            title="Edit Status"
            eyebrow="Update mail log status"
            size="md"
            @close="closeModal"
        >
            <form class="grid gap-4" @submit.prevent="submitForm">
                <div class="rounded-xl border border-border bg-slate-50 px-3 py-2 text-xs text-muted">
                    {{ form.title || form.name || '-' }}
                    <span v-if="form.destination_email"> · {{ form.destination_email }}</span>
                </div>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Status</span>
                    <select
                        v-model="form.status"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option v-for="option in statusOptions" :key="option" :value="option">
                            {{ option }}
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
                    {{ submitting ? 'Saving...' : 'Update Status' }}
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
            baseUrl: '/api/mail-logs',
            mailLogs: [],
            pagination: {},
            statusOptions: ['pending', 'sent', 'failed'],
            filters: {
                keyword: '',
                status: '',
                page: 1,
            },
            form: {
                id: null,
                name: '',
                title: '',
                destination_email: '',
                status: '',
            },
            modalOpen: false,
            loading: false,
            submitting: false,
            errorMessage: '',
            actionMenuOpenId: null,
        };
    },
    created() {
        this.fetchMailLogs();
    },
    mounted() {
        document.addEventListener('click', this.handleDocumentClick);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.handleDocumentClick);
    },
    methods: {
        fetchMailLogs() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(this.baseUrl, {
                params: this.filters,
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];

                    this.mailLogs = data;
                    this.pagination = result || {};
                })
                .catch(() => {
                    this.mailLogs = [];
                    this.pagination = {};
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        statusBadgeClass(status) {
            const value = String(status || '').toLowerCase();
            if (value === 'sent') {
                return 'bg-emerald-50 text-emerald-600';
            }
            if (value === 'failed') {
                return 'bg-rose-50 text-rose-600';
            }
            return 'bg-amber-50 text-amber-600';
        },
        formatDateTime(value) {
            if (!value) {
                return '';
            }
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return value;
            }
            return date.toLocaleString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        },
        applyFilter() {
            this.filters.page = 1;
            this.fetchMailLogs();
        },
        resetFilter() {
            this.filters.keyword = '';
            this.filters.status = '';
            this.filters.page = 1;
            this.fetchMailLogs();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchMailLogs();
        },
        toggleActionMenu(mailLogId) {
            this.actionMenuOpenId = this.actionMenuOpenId === mailLogId ? null : mailLogId;
        },
        closeActionMenu() {
            this.actionMenuOpenId = null;
        },
        handleAction(action, mailLog) {
            this.closeActionMenu();
            if (action === 'edit-status') {
                this.openEditStatus(mailLog);
                return;
            }
            if (action === 'delete') {
                this.deleteMailLog(mailLog);
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
        openEditStatus(mailLog) {
            this.form = {
                id: mailLog.id,
                name: mailLog.name || '',
                title: mailLog.title || '',
                destination_email: mailLog.destination_email || '',
                status: mailLog.status || this.statusOptions[0],
            };
            this.errorMessage = '';
            this.modalOpen = true;
        },
        closeModal() {
            this.modalOpen = false;
            this.errorMessage = '';
        },
        submitForm() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.put(`${this.baseUrl}/${this.form.id}`, { status: this.form.status })
                .then(() => {
                    this.fetchMailLogs();
                    this.closeModal();
                    this.$showToast('Mail log status updated successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to update mail log status.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        deleteMailLog(mailLog) {
            const label = mailLog.title || mailLog.name || mailLog.destination_email || mailLog.id;
            if (!window.confirm(`Delete mail log ${label}?`)) {
                return;
            }

            Repository.delete(`${this.baseUrl}/${mailLog.id}`)
                .then(() => {
                    this.fetchMailLogs();
                    this.$showToast('Mail log deleted successfully.');
                })
                .catch(() => {
                    this.errorMessage = 'Failed to delete mail log.';
                });
        },
    },
};
</script>
