<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Config</div>
                <h1 class="text-2xl font-semibold text-ink">Menus</h1>
            </div>
            <button
                class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                type="button"
                @click="openCreate"
            >
                Add Menu
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
                        placeholder="Search name, title, or url..."
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
                <div class="font-semibold">Menus</div>
                <div v-if="pagination.total" class="text-xs text-muted">
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
                <div v-if="!loading && menus.length === 0" class="px-5 py-6 text-sm text-muted">
                    No menus found.
                </div>
                <div
                    v-for="(item, index) in menus"
                    :key="item.id"
                    class="flex flex-wrap items-start gap-3 px-5 py-4"
                >
                    <div class="w-8 pt-1 text-sm font-semibold text-muted">
                        {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="font-semibold text-ink">{{ item.title }}</div>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700">
                                {{ item.type }}
                            </span>
                            <span
                                class="rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                :class="item.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'"
                            >
                                {{ item.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="mt-1 text-xs text-muted">
                            <span>{{ item.url }}</span>
                            <span v-if="parentName(item.parent_id)"> • Parent: {{ parentName(item.parent_id) }}</span>
                            <span v-if="item.order !== null && item.order !== undefined"> • Order: {{ item.order }}</span>
                        </div>
                    </div>
                    <div class="relative action-dropdown">
                        <button
                            class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                            type="button"
                            @click.stop="toggleActionMenu(item.id)"
                        >
                            Actions
                        </button>
                        <div
                            v-if="actionMenuOpenId === item.id"
                            class="absolute right-0 z-10 mt-2 w-36 rounded-xl border border-border bg-white p-1 shadow-lg"
                        >
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                type="button"
                                @click="handleAction('edit', item)"
                            >
                                Edit
                            </button>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-rose-600 hover:bg-rose-50"
                                type="button"
                                @click="handleAction('delete', item)"
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
            :title="editMode ? 'Edit Menu' : 'Create Menu'"
            :eyebrow="editMode ? 'Update menu' : 'New menu'"
            size="lg"
            @close="closeModal"
        >
            <form class="grid gap-4" @submit.prevent="submitForm">
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Name</span>
                        <input
                            v-model.trim="form.name"
                            type="text"
                            placeholder="unique-slug"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Title</span>
                        <input
                            v-model.trim="form.title"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                </div>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">URL</span>
                    <input
                        v-model.trim="form.url"
                        type="text"
                        placeholder="/blu/example"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <div class="grid gap-4 md:grid-cols-3">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Type</span>
                        <select
                            v-model="form.type"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        >
                            <option value="menu">Menu</option>
                            <option value="title">Title</option>
                            <option value="submenu">Submenu</option>
                        </select>
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Order</span>
                        <input
                            v-model.number="form.order"
                            type="number"
                            min="0"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Icon</span>
                        <input
                            v-model.trim="form.icon"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                </div>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Parent Menu</span>
                    <select
                        v-model="form.parent_id"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option :value="null">-</option>
                        <option
                            v-for="option in parentOptions"
                            :key="option.id"
                            :value="option.id"
                        >
                            {{ option.title }}
                        </option>
                    </select>
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        class="h-4 w-4 rounded border-border text-primary focus:ring-2 focus:ring-primary/30"
                    />
                    <span class="text-muted">Active</span>
                </label>
                <div v-if="errorMessage" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ errorMessage }}
                </div>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="submit"
                    :disabled="submitting"
                >
                    {{ submitting ? 'Saving...' : editMode ? 'Update Menu' : 'Create Menu' }}
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
    mixins: [persistFilters('menus')],
    data() {
        return {
            baseUrl: '/api/menus',
            menus: [],
            allMenus: [],
            pagination: {},
            filters: {
                keyword: '',
                page: 1,
            },
            form: {
                id: null,
                parent_id: null,
                order: null,
                type: 'menu',
                url: '',
                name: '',
                title: '',
                icon: '',
                is_active: true,
            },
            editMode: false,
            modalOpen: false,
            loading: false,
            submitting: false,
            errorMessage: '',
            actionMenuOpenId: null,
        };
    },
    computed: {
        parentOptions() {
            return this.allMenus.filter((item) => !this.form.id || item.id !== this.form.id);
        },
    },
    created() {
        this.fetchAllMenus();
        this.fetchMenus();
    },
    mounted() {
        document.addEventListener('click', this.handleDocumentClick);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.handleDocumentClick);
    },
    methods: {
        toggleActionMenu(itemId) {
            this.actionMenuOpenId = this.actionMenuOpenId === itemId ? null : itemId;
        },
        closeActionMenu() {
            this.actionMenuOpenId = null;
        },
        handleAction(action, item) {
            this.closeActionMenu();
            if (action === 'edit') {
                this.openEdit(item);
                return;
            }
            if (action === 'delete') {
                this.deleteMenu(item);
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
        parentName(parentId) {
            if (!parentId) {
                return '';
            }
            const parent = this.allMenus.find((item) => item.id === parentId);
            return parent ? parent.title : '';
        },
        fetchAllMenus() {
            return Repository.get('/api/menu-list')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.allMenus = Array.isArray(result) ? result : [];
                })
                .catch(() => {
                    this.allMenus = [];
                });
        },
        fetchMenus() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(this.baseUrl, {
                params: this.filters,
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];

                    this.menus = data;
                    this.pagination = result || {};
                })
                .catch(() => {
                    this.menus = [];
                    this.pagination = {};
                    this.errorMessage = 'Failed to load menus.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        applyFilter() {
            this.filters.page = 1;
            this.fetchMenus();
        },
        resetFilter() {
            this.filters.keyword = '';
            this.filters.page = 1;
            this.fetchMenus();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchMenus();
        },
        openCreate() {
            this.editMode = false;
            this.resetForm();
            this.errorMessage = '';
            this.modalOpen = true;
        },
        openEdit(item) {
            this.editMode = true;
            this.form = {
                id: item.id,
                parent_id: item.parent_id ?? null,
                order: item.order ?? null,
                type: item.type || 'menu',
                url: item.url || '',
                name: item.name || '',
                title: item.title || '',
                icon: item.icon || '',
                is_active: item.is_active === undefined ? true : !!item.is_active,
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
                parent_id: null,
                order: null,
                type: 'menu',
                url: '',
                name: '',
                title: '',
                icon: '',
                is_active: true,
            };
        },
        submitForm() {
            if (this.editMode) {
                return this.updateMenu();
            }

            return this.createMenu();
        },
        createMenu() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.post(this.baseUrl, this.form)
                .then(() => {
                    this.closeModal();
                    this.fetchAllMenus();
                    this.fetchMenus();
                    this.$showToast('Menu created successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to create menu.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        updateMenu() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.put(`${this.baseUrl}/${this.form.id}`, this.form)
                .then(() => {
                    this.fetchAllMenus();
                    this.fetchMenus();
                    this.closeModal();
                    this.$showToast('Menu updated successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to update menu.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        deleteMenu(item) {
            if (!window.confirm(`Delete menu ${item.title}?`)) {
                return;
            }

            Repository.delete(`${this.baseUrl}/${item.id}`)
                .then(() => {
                    this.fetchAllMenus();
                    this.fetchMenus();
                    this.$showToast('Menu deleted successfully.');
                })
                .catch(() => {
                    this.errorMessage = 'Failed to delete menu.';
                });
        },
    },
};
</script>
