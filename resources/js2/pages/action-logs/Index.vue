<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Audit Trail</div>
                <h1 class="text-2xl font-semibold text-ink">Action Logs</h1>
            </div>
            <div class="flex items-end gap-2">
                <button
                    class="rounded-xl border border-rose-200 px-4 py-2 text-sm text-rose-600"
                    type="button"
                    :disabled="cleaning"
                    @click="cleanup(7)"
                >
                    Delete logs older than 7 days
                </button>
                <button
                    class="rounded-xl border border-rose-200 px-4 py-2 text-sm text-rose-600"
                    type="button"
                    :disabled="cleaning"
                    @click="cleanup(30)"
                >
                    Delete logs older than 30 days
                </button>
            </div>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-end gap-3">
                <div class="min-w-[180px]">
                    <label class="text-xs text-muted">Date</label>
                    <input
                        v-model="filters.date"
                        type="date"
                        @change="fetchLogs"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="min-w-[160px]">
                    <label class="text-xs text-muted">Response Code</label>
                    <select
                        v-model="filters.status"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">All</option>
                        <option v-for="option in statusOptions" :key="option" :value="option">
                            {{ option }}
                        </option>
                    </select>
                </div>
                <div class="flex-1 min-w-[220px]">
                    <label class="text-xs text-muted">Actor</label>
                    <input
                        v-model.trim="filters.actor"
                        type="text"
                        placeholder="Search name, email or id..."
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="flex items-end gap-2">
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
                <div class="font-semibold">{{ filters.date }}</div>
                <div class="text-xs text-muted">
                    {{ filteredLogs.length }} / {{ logs.length }} entries
                </div>
            </div>
            <div
                v-if="errorMessage"
                class="border-b border-rose-100 bg-rose-50 px-5 py-3 text-xs text-rose-600"
            >
                {{ errorMessage }}
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border text-left text-xs uppercase tracking-wide text-muted">
                            <th class="px-5 py-3">Time</th>
                            <th class="px-5 py-3">Method</th>
                            <th class="px-5 py-3">Route</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Actor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-if="!loading && filteredLogs.length === 0">
                            <td colspan="5" class="px-5 py-6 text-center text-sm text-muted">
                                No action logs found for this filter.
                            </td>
                        </tr>
                        <tr v-for="(log, index) in filteredLogs" :key="index">
                            <td class="whitespace-nowrap px-5 py-3 text-xs text-muted">{{ log.time }}</td>
                            <td class="whitespace-nowrap px-5 py-3">
                                <span class="rounded-full px-2 py-0.5 text-[11px] font-medium" :class="methodBadgeClass(log.method)">
                                    {{ log.method }}
                                </span>
                            </td>
                            <td class="px-5 py-3 font-mono text-xs text-ink">{{ log.route }}</td>
                            <td class="whitespace-nowrap px-5 py-3">
                                <span class="rounded-full px-2 py-0.5 text-[11px] font-medium" :class="statusBadgeClass(log.status)">
                                    {{ log.status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-xs">
                                <div class="font-semibold text-ink">{{ (log.actor && log.actor.name) || '-' }}</div>
                                <div class="text-muted">
                                    <span v-if="log.actor && log.actor.email">{{ log.actor.email }}</span>
                                    <span v-if="log.actor && log.actor.id"> · #{{ log.actor.id }}</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
    mixins: [persistFilters('action-logs')],
    data() {
        return {
            baseUrl: '/api/action-logs',
            logs: [],
            filters: {
                date: '',
                status: '',
                actor: '',
            },
            loading: false,
            cleaning: false,
            errorMessage: '',
        };
    },
    computed: {
        statusOptions() {
            const codes = this.logs
                .map((log) => log.status)
                .filter((status) => status !== undefined && status !== null);
            return Array.from(new Set(codes)).sort((a, b) => a - b);
        },
        filteredLogs() {
            const actorQuery = this.filters.actor.toLowerCase();

            return this.logs.filter((log) => {
                if (this.filters.status && String(log.status) !== String(this.filters.status)) {
                    return false;
                }

                if (actorQuery) {
                    const actor = log.actor || {};
                    const haystack = [actor.name, actor.email, actor.id]
                        .filter(Boolean)
                        .join(' ')
                        .toLowerCase();
                    if (!haystack.includes(actorQuery)) {
                        return false;
                    }
                }

                return true;
            });
        },
    },
    created() {
        if (!this.filters.date) {
            this.filters.date = this.getToday();
        }
        this.fetchLogs();
    },
    methods: {
        getToday() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        fetchLogs() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(this.baseUrl, {
                params: { date: this.filters.date },
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.logs = result && Array.isArray(result.data) ? result.data : [];
                })
                .catch(() => {
                    this.logs = [];
                    this.errorMessage = 'Failed to load action logs.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        resetFilter() {
            this.filters.date = this.getToday();
            this.filters.status = '';
            this.filters.actor = '';
            this.fetchLogs();
        },
        methodBadgeClass(method) {
            const value = String(method || '').toUpperCase();
            if (value === 'POST') {
                return 'bg-emerald-50 text-emerald-600';
            }
            if (value === 'DELETE') {
                return 'bg-rose-50 text-rose-600';
            }
            if (value === 'PUT' || value === 'PATCH') {
                return 'bg-amber-50 text-amber-600';
            }
            return 'bg-slate-100 text-slate-600';
        },
        statusBadgeClass(status) {
            const code = Number(status);
            if (code >= 200 && code < 300) {
                return 'bg-emerald-50 text-emerald-600';
            }
            if (code >= 400 && code < 500) {
                return 'bg-amber-50 text-amber-600';
            }
            if (code >= 500) {
                return 'bg-rose-50 text-rose-600';
            }
            return 'bg-slate-100 text-slate-600';
        },
        cleanup(days) {
            if (!window.confirm(`Delete action logs older than ${days} days?`)) {
                return;
            }

            this.cleaning = true;
            this.errorMessage = '';

            return Repository.delete('/api/action-logs-cleanup', { params: { days } })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const count = result ? result.deleted_files : 0;
                    this.$showToast(`Deleted ${count} log file(s) older than ${days} days.`);
                    this.fetchLogs();
                })
                .catch(() => {
                    this.errorMessage = 'Failed to clean up action logs.';
                })
                .finally(() => {
                    this.cleaning = false;
                });
        },
    },
};
</script>
