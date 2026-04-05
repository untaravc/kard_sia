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
                    <div class="flex items-center gap-2">
                        <a
                            class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                            :href="`/letters/${letter.id}/preview`"
                            target="_blank"
                            rel="noopener"
                        >
                            Preview
                        </a>
                        <router-link
                            class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                            :to="`/blu/letters/${letter.id}`"
                        >
                            Edit
                        </router-link>
                        <button
                            class="rounded-lg bg-rose-500/10 px-3 py-1.5 text-xs text-rose-600"
                            type="button"
                            @click="deleteLetter(letter)"
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
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Repository from '../../repository';

export default {
    components: {
        Loading,
    },
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
        };
    },
    created() {
        this.fetchLetters();
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
