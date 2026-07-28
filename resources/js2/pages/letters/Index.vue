<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Letter Management</div>
                <h1 class="text-2xl font-semibold text-ink">Letters</h1>
            </div>
            <router-link
                class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                to="/blu/letters/create"
            >
                Add Letter
            </router-link>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="text-xs text-muted">Keyword</label>
                    <input
                        v-model.trim="filters.keyword"
                        @keyup.enter="applyFilter"
                        type="text"
                        placeholder="Search title or number..."
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
                <div class="font-semibold">Letters</div>
                <div class="text-xs text-muted" v-if="pagination.total">
                    {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }}
                </div>
            </div>
            <div
                v-if="errorMessage"
                class="border-b border-rose-100 bg-rose-50 px-5 py-3 text-xs text-rose-600"
            >
                {{ errorMessage }}
            </div>
            <div class="divide-y divide-border">
                <div v-if="!loading && letters.length === 0" class="px-5 py-6 text-sm text-muted">
                    No letters found.
                </div>
                <div
                    v-for="(letter, index) in letters"
                    :key="letter.id"
                    class="flex flex-wrap items-center gap-3 px-5 py-4"
                >
                    <div class="w-8 text-sm font-semibold text-muted">
                        {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="font-semibold text-ink">
                                {{ letter.title || '(Untitled)' }}
                            </div>
                            <span
                                v-if="typeof letter.status !== 'undefined' && letter.status !== null"
                                class="rounded-lg bg-slate-100 px-2 py-0.5 text-xs text-muted"
                            >
                                {{ statusLabel(letter.status) }}
                            </span>
                    </div>
                    <div class="text-xs text-muted">
                        <span v-if="letter.number">No: {{ letter.number }}</span>
                        <span v-if="letter.date">• Date: {{ letter.date }}</span>
                    </div>
                </div>
                    <div class="relative action-dropdown">
                        <button
                            class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                            type="button"
                            @click.stop="toggleActionMenu(letter.id)"
                        >
                            Actions
                        </button>
                        <div
                            v-if="actionMenuOpenId === letter.id"
                            class="absolute right-0 z-10 mt-2 w-44 rounded-xl border border-border bg-white p-1 shadow-lg"
                        >
                            <a
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                :href="letter && Number(letter.status) === 1 && letter.token ? `/letters/${letter.token}` : `/letters/${letter.id}/preview`"
                                target="_blank"
                                rel="noopener"
                                @click="closeActionMenu"
                            >
                                Preview
                            </a>
                            <router-link
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                :to="`/blu/letters/${letter.id}`"
                                @click.native="closeActionMenu"
                            >
                                Edit
                            </router-link>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                type="button"
                                @click="handleAction(letter && Number(letter.status) === 1 ? 'notify' : 'propose', letter)"
                            >
                                {{ letter && Number(letter.status) === 1 ? 'Notify' : 'Propose' }}
                            </button>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                type="button"
                                @click="handleAction('clone', letter)"
                            >
                                Clone
                            </button>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-rose-600 hover:bg-rose-50"
                                type="button"
                                @click="handleAction('delete', letter)"
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
            :open="proposeModalOpen"
            title="Kirim Pengajuan"
            eyebrow="WhatsApp"
            size="sm"
            @close="closeProposeApproval"
        >
            <div class="text-sm text-ink">
                Kirim pengajuan tandatangan melalui link whatsapp?
            </div>
            <div v-if="proposeError" class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                {{ proposeError }}
            </div>
            <template #footer>
                <button
                    class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                    type="button"
                    :disabled="proposeSubmitting"
                    @click="closeProposeApproval"
                >
                    Cancel
                </button>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="button"
                    :disabled="proposeSubmitting"
                    @click="submitProposeApproval"
                >
                    {{ proposeSubmitting ? 'Sending...' : 'Send' }}
                </button>
            </template>
        </Modal>

        <Modal
            :open="notifyModalOpen"
            title="Send Notification"
            eyebrow="WhatsApp"
            size="sm"
            @close="closeNotifyApprover"
        >
            <div class="text-sm text-ink">
                Send notification to {{ notifyApproverName }}?
            </div>
            <div v-if="notifyError" class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                {{ notifyError }}
            </div>
            <template #footer>
                <button
                    class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                    type="button"
                    :disabled="notifySubmitting"
                    @click="closeNotifyApprover"
                >
                    Cancel
                </button>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="button"
                    :disabled="notifySubmitting"
                    @click="submitNotifyApprover"
                >
                    {{ notifySubmitting ? 'Sending...' : 'Send' }}
                </button>
            </template>
        </Modal>

        <Modal
            :open="cloneModalOpen"
            title="Clone Letter"
            eyebrow="Duplicate"
            size="sm"
            @close="closeCloneLetter"
        >
            <div class="grid gap-4">
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Title</span>
                    <input
                        v-model.trim="cloneForm.title"
                        type="text"
                        placeholder="New title"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Date</span>
                    <input
                        v-model="cloneForm.date"
                        type="date"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
            </div>
            <div v-if="cloneError" class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                {{ cloneError }}
            </div>
            <template #footer>
                <button
                    class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                    type="button"
                    :disabled="cloneSubmitting"
                    @click="closeCloneLetter"
                >
                    Cancel
                </button>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="button"
                    :disabled="cloneSubmitting"
                    @click="submitCloneLetter"
                >
                    {{ cloneSubmitting ? 'Cloning...' : 'Clone' }}
                </button>
            </template>
        </Modal>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Repository from '../../repository';
import Modal from '../../components/Modal.vue';
import persistFilters from '../../mixins/persistFilters';

export default {
    components: {
        Loading,
        Modal,
    },
    mixins: [persistFilters('letters')],
    data() {
        return {
            baseUrl: '/api/letters',
            letters: [],
            pagination: {},
            filters: {
                keyword: '',
                status: '',
                page: 1,
            },
            statusOptions: [
                { value: 0, label: 'Draft' },
                { value: 1, label: 'Published' },
            ],
            loading: false,
            errorMessage: '',
            proposeModalOpen: false,
            proposeSubmitting: false,
            proposeLetter: null,
            proposeError: '',
            notifyModalOpen: false,
            notifySubmitting: false,
            notifyLetter: null,
            notifyError: '',
            cloneModalOpen: false,
            cloneSubmitting: false,
            cloneLetter: null,
            cloneError: '',
            cloneForm: {
                title: '',
                date: '',
            },
            actionMenuOpenId: null,
        };
    },
    computed: {
        notifyApproverName() {
            const participants = this.notifyLetter && Array.isArray(this.notifyLetter.participants)
                ? this.notifyLetter.participants
                : [];
            const approver = participants.length ? participants[0] : null;
            return (approver && approver.auth_name) ? approver.auth_name : 'approver';
        },
    },
    created() {
        this.fetchLetters();
    },
    mounted() {
        document.addEventListener('click', this.handleDocumentClick);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.handleDocumentClick);
    },
    methods: {
        statusLabel(status) {
            const matched = this.statusOptions.find((option) => String(option.value) === String(status));
            return matched ? matched.label : String(status);
        },
        fetchLetters() {
            this.loading = true;
            this.errorMessage = '';

            const params = {
                ...this.filters,
            };

            if (!params.keyword) {
                delete params.keyword;
            }
            if (!params.status) {
                delete params.status;
            }

            return Repository.get(this.baseUrl, { params })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];
                    this.letters = data;
                    this.pagination = result || {};
                })
                .catch(() => {
                    this.letters = [];
                    this.pagination = {};
                    this.errorMessage = 'Failed to load letters.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        applyFilter() {
            this.filters.page = 1;
            this.fetchLetters();
        },
        resetFilter() {
            this.filters.keyword = '';
            this.filters.status = '';
            this.filters.page = 1;
            this.fetchLetters();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchLetters();
        },
        toggleActionMenu(letterId) {
            this.actionMenuOpenId = this.actionMenuOpenId === letterId ? null : letterId;
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
        handleAction(action, letter) {
            this.closeActionMenu();

            if (action === 'propose') {
                this.openProposeApproval(letter);
                return;
            }
            if (action === 'notify') {
                this.openNotifyApprover(letter);
                return;
            }
            if (action === 'clone') {
                this.openCloneLetter(letter);
                return;
            }
            if (action === 'delete') {
                this.deleteLetter(letter);
            }
        },
        openProposeApproval(letter) {
            this.proposeLetter = letter || null;
            this.proposeError = '';
            this.proposeModalOpen = true;
        },
        closeProposeApproval(force = false) {
            if (this.proposeSubmitting && !force) {
                return;
            }
            this.proposeModalOpen = false;
            this.proposeLetter = null;
            this.proposeError = '';
        },
        submitProposeApproval() {
            if (this.proposeSubmitting) {
                return;
            }
            if (!this.proposeLetter || !this.proposeLetter.id) {
                this.proposeError = 'Letter is invalid.';
                return;
            }

            this.proposeSubmitting = true;
            this.proposeError = '';

            return Repository.post(`${this.baseUrl}/${this.proposeLetter.id}/propose-approval`)
                .then(() => {
                    this.closeProposeApproval(true);
                    this.$showToast('Pengajuan tandatangan berhasil dikirim.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to send propose approval.';
                    this.proposeError = message;
                })
                .finally(() => {
                    this.proposeSubmitting = false;
                });
        },
        openNotifyApprover(letter) {
            this.notifyLetter = letter || null;
            this.notifyError = '';
            this.notifyModalOpen = true;
        },
        closeNotifyApprover(force = false) {
            if (this.notifySubmitting && !force) {
                return;
            }
            this.notifyModalOpen = false;
            this.notifyLetter = null;
            this.notifyError = '';
        },
        submitNotifyApprover() {
            if (this.notifySubmitting) {
                return;
            }
            if (!this.notifyLetter || !this.notifyLetter.id) {
                this.notifyError = 'Letter is invalid.';
                return;
            }

            this.notifySubmitting = true;
            this.notifyError = '';

            return Repository.post(`${this.baseUrl}/${this.notifyLetter.id}/notify-approver`)
                .then(() => {
                    this.closeNotifyApprover(true);
                    this.$showToast('Notification sent successfully.');
                    this.fetchLetters();
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to send notification.';
                    this.notifyError = message;
                })
                .finally(() => {
                    this.notifySubmitting = false;
                });
        },
        openCloneLetter(letter) {
            const title = letter && letter.title ? `Copy of ${letter.title}` : '';
            const date = letter && letter.date ? letter.date : '';
            this.cloneLetter = letter || null;
            this.cloneError = '';
            this.cloneForm = {
                title,
                date,
            };
            this.cloneModalOpen = true;
        },
        closeCloneLetter(force = false) {
            if (this.cloneSubmitting && !force) {
                return;
            }
            this.cloneModalOpen = false;
            this.cloneLetter = null;
            this.cloneError = '';
            this.cloneForm = {
                title: '',
                date: '',
            };
        },
        submitCloneLetter() {
            if (this.cloneSubmitting) {
                return;
            }
            if (!this.cloneLetter || !this.cloneLetter.id) {
                this.cloneError = 'Letter is invalid.';
                return;
            }
            if (!this.cloneForm.title) {
                this.cloneError = 'Title is required.';
                return;
            }

            this.cloneSubmitting = true;
            this.cloneError = '';

            return Repository.post(`/api/letter-clone/${this.cloneLetter.id}`, {
                title: this.cloneForm.title,
                date: this.cloneForm.date || null,
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const newId = result && result.id ? result.id : null;
                    if (!newId) {
                        throw new Error('Invalid response.');
                    }
                    this.closeCloneLetter(true);
                    this.$showToast('Letter cloned successfully.');
                    this.$router.push(`/blu/letters/${newId}`);
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : (error && error.message ? error.message : 'Failed to clone letter.');
                    this.cloneError = message;
                })
                .finally(() => {
                    this.cloneSubmitting = false;
                });
        },
        deleteLetter(letter) {
            const title = letter && letter.title ? letter.title : 'this letter';
            if (!window.confirm(`Delete ${title}?`)) {
                return;
            }

            Repository.delete(`${this.baseUrl}/${letter.id}`)
                .then(() => {
                    this.fetchLetters();
                    this.$showToast('Letter deleted successfully.');
                })
                .catch(() => {
                    this.errorMessage = 'Failed to delete letter.';
                });
        },
    },
};
</script>
