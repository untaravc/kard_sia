<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Presence Monitoring</div>
                <h1 class="text-2xl font-semibold text-ink">Presences</h1>
            </div>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="text-xs text-muted">Name</label>
                    <input
                        v-model.trim="filters.name"
                        type="text"
                        placeholder="Search student..."
                        @keyup.enter="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="flex-1 min-w-[180px]">
                    <label class="text-xs text-muted">Date</label>
                    <input
                        v-model="filters.date"
                        type="date"
                        @change="applyFilter"
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
                <div class="font-semibold">Presences</div>
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
                <div v-if="!loading && presences.length === 0" class="px-5 py-6 text-sm text-muted">
                    No presences found.
                </div>
                <div
                    v-for="(presence, index) in presences"
                    :key="presence.id"
                    class="flex flex-wrap items-center gap-3 px-5 py-4"
                >
                    <div class="w-8 text-sm font-semibold text-muted">
                        {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold text-ink">{{ presence.student ? presence.student.name : '-' }}</div>
                        <div class="text-xs text-muted">
                            <span v-if="presence.checkin">Checkin: {{ presence.checkin }}</span>
                            <span v-if="presence.checkout">• Checkout: {{ presence.checkout }}</span>
                            <span v-if="presence.duration">• {{ presence.duration }}</span>
                        </div>
                    </div>
                    <div class="text-xs text-muted">
                        <span v-if="presence.status !== null && presence.status !== undefined">
                            Status: {{ presence.status }}
                        </span>
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
            baseUrl: '/api/presences',
            presences: [],
            pagination: {},
            filters: {
                name: '',
                date: '',
                page: 1,
            },
            loading: false,
            errorMessage: '',
        };
    },
    created() {
        this.filters.date = this.getToday();
        this.fetchPresences();
    },
    methods: {
        getToday() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        fetchPresences() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(this.baseUrl, {
                params: this.filters,
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];

                    this.presences = data;
                    this.pagination = result || {};
                })
                .catch(() => {
                    this.presences = [];
                    this.pagination = {};
                    this.errorMessage = 'Failed to load presences.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        applyFilter() {
            this.filters.page = 1;
            this.fetchPresences();
        },
        resetFilter() {
            this.filters.name = '';
            this.filters.date = this.getToday();
            this.filters.page = 1;
            this.fetchPresences();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchPresences();
        },
    },
};
</script>
