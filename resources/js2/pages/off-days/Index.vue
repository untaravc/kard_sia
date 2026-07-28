<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Off Day Management</div>
                <h1 class="text-2xl font-semibold text-ink">Off Days</h1>
            </div>
            <button
                class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                type="button"
                @click="openCreate"
            >
                Add Off Day
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
                        placeholder="Search name or status..."
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="min-w-[160px]">
                    <label class="text-xs text-muted">From</label>
                    <input
                        v-model="filters.date_gte"
                        type="date"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="min-w-[160px]">
                    <label class="text-xs text-muted">To</label>
                    <input
                        v-model="filters.date_lte"
                        type="date"
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
                <div class="font-semibold">Off Days</div>
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
                <div v-if="!loading && offDays.length === 0" class="px-5 py-6 text-sm text-muted">
                    No off days found.
                </div>
                <div
                    v-for="(offDay, index) in offDays"
                    :key="offDay.id"
                    class="flex flex-wrap items-center gap-3 px-5 py-4"
                >
                    <div class="w-8 text-sm font-semibold text-muted">
                        {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <div class="font-semibold text-ink">{{ offDay.name }}</div>
                            <span v-if="offDay.status" class="text-xs text-muted">
                                Status: {{ offDay.status }}
                            </span>
                        </div>
                        <div class="text-xs text-muted">
                            <span v-if="offDay.date">{{ formatDate(offDay.date) }}</span>
                        </div>
                    </div>
                    <div class="relative action-dropdown">
                        <button
                            class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                            type="button"
                            @click.stop="toggleActionMenu(offDay.id)"
                        >
                            Actions
                        </button>
                        <div
                            v-if="actionMenuOpenId === offDay.id"
                            class="absolute right-0 z-10 mt-2 w-36 rounded-xl border border-border bg-white p-1 shadow-lg"
                        >
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                type="button"
                                @click="handleAction('edit', offDay)"
                            >
                                Edit
                            </button>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-rose-600 hover:bg-rose-50"
                                type="button"
                                @click="handleAction('delete', offDay)"
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
            :title="editMode ? 'Edit Off Day' : 'Create Off Day'"
            :eyebrow="editMode ? 'Update off day' : 'New off day'"
            size="md"
            @close="closeModal"
        >
            <form class="grid gap-4" @submit.prevent="submitForm">
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Name</span>
                    <input
                        v-model.trim="form.name"
                        type="text"
                        placeholder="Holiday name"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Date</span>
                    <input
                        v-model="form.date"
                        type="date"
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
                <div v-if="errorMessage" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ errorMessage }}
                </div>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="submit"
                    :disabled="submitting"
                >
                    {{ submitting ? 'Saving...' : editMode ? 'Update Off Day' : 'Create Off Day' }}
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
    mixins: [persistFilters('off-days')],
    data() {
        return {
            baseUrl: '/api/off-days',
            offDays: [],
            pagination: {},
            filters: {
                keyword: '',
                date_gte: '',
                date_lte: '',
                page: 1,
            },
            form: {
                id: null,
                name: '',
                date: '',
                status: 'active',
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
        this.fetchOffDays();
    },
    mounted() {
        document.addEventListener('click', this.handleDocumentClick);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.handleDocumentClick);
    },
    methods: {
        fetchOffDays() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(this.baseUrl, {
                params: this.filters,
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];

                    this.offDays = data;
                    this.pagination = result || {};
                })
                .catch(() => {
                    this.offDays = [];
                    this.pagination = {};
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        formatDate(value) {
            if (!value) {
                return '';
            }
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return value;
            }
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
            });
        },
        applyFilter() {
            this.filters.page = 1;
            this.fetchOffDays();
        },
        resetFilter() {
            this.filters.keyword = '';
            this.filters.date_gte = '';
            this.filters.date_lte = '';
            this.filters.page = 1;
            this.fetchOffDays();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchOffDays();
        },
        toggleActionMenu(offDayId) {
            this.actionMenuOpenId = this.actionMenuOpenId === offDayId ? null : offDayId;
        },
        closeActionMenu() {
            this.actionMenuOpenId = null;
        },
        handleAction(action, offDay) {
            this.closeActionMenu();
            if (action === 'edit') {
                this.openEdit(offDay);
                return;
            }
            if (action === 'delete') {
                this.deleteOffDay(offDay);
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
        openEdit(offDay) {
            this.editMode = true;
            this.form = {
                id: offDay.id,
                name: offDay.name || '',
                date: offDay.date ? String(offDay.date).slice(0, 10) : '',
                status: offDay.status || 'active',
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
                date: '',
                status: 'active',
            };
        },
        submitForm() {
            if (this.editMode) {
                return this.updateOffDay();
            }

            return this.createOffDay();
        },
        createOffDay() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.post(this.baseUrl, this.form)
                .then(() => {
                    this.closeModal();
                    this.fetchOffDays();
                    this.$showToast('Off day created successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to create off day.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        updateOffDay() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.put(`${this.baseUrl}/${this.form.id}`, this.form)
                .then(() => {
                    this.fetchOffDays();
                    this.closeModal();
                    this.$showToast('Off day updated successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to update off day.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        deleteOffDay(offDay) {
            if (!window.confirm(`Delete off day ${offDay.name}?`)) {
                return;
            }

            Repository.delete(`${this.baseUrl}/${offDay.id}`)
                .then(() => {
                    this.fetchOffDays();
                    this.$showToast('Off day deleted successfully.');
                })
                .catch(() => {
                    this.errorMessage = 'Failed to delete off day.';
                });
        },
    },
};
</script>
