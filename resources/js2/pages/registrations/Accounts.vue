<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Registration Management</div>
                <h1 class="text-2xl font-semibold text-ink">Login Accounts</h1>
            </div>
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
                    <label class="text-xs text-muted">Email Verified</label>
                    <select
                        v-model="filters.email_verified"
                        @change="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">All</option>
                        <option value="1">Verified</option>
                        <option value="0">Not Verified</option>
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
                <div class="font-semibold">Login Accounts</div>
                <div class="text-xs text-muted" v-if="pagination.total">
                    {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }}
                </div>
            </div>
            <div v-if="errorMessage" class="border-b border-rose-100 bg-rose-50 px-5 py-3 text-xs text-rose-600">
                {{ errorMessage }}
            </div>
            <div class="divide-y divide-border">
                <div v-if="!loading && accounts.length === 0" class="px-5 py-6 text-sm text-muted">
                    No login accounts found.
                </div>
                <div
                    v-for="(account, index) in accounts"
                    :key="account.id"
                    class="flex flex-wrap items-center gap-3 px-5 py-4"
                >
                    <div class="w-8 text-sm font-semibold text-muted">
                        {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="font-semibold text-ink">{{ account.name }}</div>
                            <span
                                class="rounded-lg px-2 py-0.5 text-xs"
                                :class="account.email_confirmed_at ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600'"
                            >
                                {{ account.email_confirmed_at ? 'Email Verified' : 'Email Not Verified' }}
                            </span>
                            <span v-if="account.status_label" class="rounded-lg bg-slate-100 px-2 py-0.5 text-xs text-muted">
                                {{ account.status_label }}
                            </span>
                        </div>
                        <div class="text-xs text-muted">
                            <span v-if="account.email">Login: {{ account.email }}</span>
                            <span v-if="account.phone">• {{ account.phone }}</span>
                            <span v-if="account.registration_period">• Period: {{ account.registration_period }}</span>
                            <span v-if="account.created_at">• Registered: {{ formatDate(account.created_at) }}</span>
                        </div>
                    </div>
                    <div class="relative action-dropdown">
                        <button
                            class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                            type="button"
                            @click.stop="toggleActionMenu(account.id)"
                        >
                            Actions
                        </button>
                        <div
                            v-if="actionMenuOpenId === account.id"
                            class="absolute right-0 z-10 mt-2 w-36 rounded-xl border border-border bg-white p-1 shadow-lg"
                        >
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                type="button"
                                @click="openDetail(account)"
                            >
                                Detail
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
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Repository from '../../repository';
import persistFilters from '../../mixins/persistFilters';

export default {
    components: {
        Loading,
    },
    mixins: [persistFilters('registrations-accounts')],
    watch: {
        $route(to, from) {
            if (!to || !from || to.fullPath === from.fullPath) {
                return;
            }

            this.filters.page = 1;
            this.fetchAccounts();
        },
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
            accounts: [],
            pagination: {},
            filters: {
                keyword: '',
                email_verified: '',
                registration_period: '',
                page: 1,
            },
            loading: false,
            errorMessage: '',
            actionMenuOpenId: null,
        };
    },
    created() {
        if (!this.filters.registration_period) {
            this.filters.registration_period = this.periodOptions && this.periodOptions.length ? this.periodOptions[0] : '';
        }
        this.fetchAccounts();
    },
    mounted() {
        document.addEventListener('click', this.handleDocumentClick);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.handleDocumentClick);
    },
    methods: {
        fetchAccounts() {
            this.loading = true;
            this.errorMessage = '';

            const params = {
                keyword: this.filters.keyword,
                registration_period: this.filters.registration_period,
                page: this.filters.page,
            };

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
                    let data = result && Array.isArray(result.data) ? result.data : [];

                    if (this.filters.email_verified === '1') {
                        data = data.filter((item) => !!item.email_confirmed_at);
                    } else if (this.filters.email_verified === '0') {
                        data = data.filter((item) => !item.email_confirmed_at);
                    }

                    this.accounts = data;
                    this.pagination = result || {};
                })
                .catch(() => {
                    this.accounts = [];
                    this.pagination = {};
                    this.errorMessage = 'Failed to load login accounts.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        formatDate(value) {
            if (!value) {
                return '';
            }

            return new Date(value).toLocaleDateString();
        },
        toggleActionMenu(accountId) {
            this.actionMenuOpenId = this.actionMenuOpenId === accountId ? null : accountId;
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
            this.fetchAccounts();
        },
        resetFilter() {
            this.filters.keyword = '';
            this.filters.email_verified = '';
            this.filters.registration_period = this.periodOptions && this.periodOptions.length ? this.periodOptions[0] : '';
            this.filters.page = 1;
            this.fetchAccounts();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchAccounts();
        },
        openDetail(account) {
            this.closeActionMenu();
            if (!account || !account.id) {
                return;
            }
            this.$router.push(`/blu/registrations/${account.id}`);
        },
    },
};
</script>
