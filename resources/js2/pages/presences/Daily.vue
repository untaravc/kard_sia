<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Presence Resume</div>
                <h1 class="text-2xl font-semibold text-ink">Daily Presences</h1>
            </div>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex-1 min-w-[180px]">
                    <label class="text-xs text-muted">Date</label>
                    <input
                        v-model="filters.date"
                        type="date"
                        @change="applyFilter"
                        @keyup.enter="applyFilter"
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

        <section class="grid gap-4 lg:grid-cols-2">
            <div class="relative rounded-2xl border border-border bg-panel">
                <Loading :active="loading" :is-full-page="false" />
                <div class="flex items-center justify-between border-b border-border px-5 py-4">
                    <div class="font-semibold">Belum Presensi</div>
                    <div class="text-xs text-muted" v-if="dataContent.no_presence_count !== undefined">
                        {{ dataContent.no_presence_count }} students
                    </div>
                </div>
                <div class="divide-y divide-border">
                    <div v-if="!loading && noPresence.length === 0" class="px-5 py-6 text-sm text-muted">
                        No students missing presence.
                    </div>
                    <div
                        v-for="student in noPresence"
                        :key="student.id"
                        class="px-5 py-4"
                    >
                        <div class="font-semibold text-ink">{{ student.name }}</div>
                        <div class="text-xs text-muted" v-if="student.last_presence && student.last_presence.checkin">
                            Last presence: {{ student.last_presence.checkin }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative rounded-2xl border border-border bg-panel">
                <Loading :active="loading" :is-full-page="false" />
                <div class="flex items-center justify-between border-b border-border px-5 py-4">
                    <div class="font-semibold">Sudah Presensi</div>
                    <div class="text-xs text-muted" v-if="dataContent.presence_count !== undefined">
                        {{ dataContent.presence_count }} students
                    </div>
                </div>
                <div class="grid gap-4 p-5 md:grid-cols-2">
                    <div>
                        <div
                            v-for="student in presenceLeft"
                            :key="student.id"
                            class="mb-3"
                        >
                            <div class="font-semibold text-ink">{{ student.name }}</div>
                            <div class="text-xs text-muted">
                                <span v-if="student.presence">Checkin: {{ student.presence.checkin }}</span>
                                <span v-if="student.presence && student.presence.checkout">• Checkout: {{ student.presence.checkout }}</span>
                                <span v-if="student.presence && student.presence.duration">• {{ student.presence.duration }}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div
                            v-for="student in presenceRight"
                            :key="student.id"
                            class="mb-3"
                        >
                            <div class="font-semibold text-ink">{{ student.name }}</div>
                            <div class="text-xs text-muted">
                                <span v-if="student.presence">Checkin: {{ student.presence.checkin }}</span>
                                <span v-if="student.presence && student.presence.checkout">• Checkout: {{ student.presence.checkout }}</span>
                                <span v-if="student.presence && student.presence.duration">• {{ student.presence.duration }}</span>
                            </div>
                        </div>
                    </div>
                    <div v-if="!loading && presenceList.length === 0" class="text-sm text-muted">
                        No presences for this date.
                    </div>
                </div>
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
            baseUrl: '/api/presences/daily',
            dataContent: {
                no_presence: [],
                presence: [],
                no_presence_count: 0,
                presence_count: 0,
            },
            filters: {
                date: '',
            },
            loading: false,
            errorMessage: '',
        };
    },
    computed: {
        noPresence() {
            return Array.isArray(this.dataContent.no_presence) ? this.dataContent.no_presence : [];
        },
        presenceList() {
            return Array.isArray(this.dataContent.presence) ? this.dataContent.presence : [];
        },
        presenceLeft() {
            const half = Math.ceil(this.presenceList.length / 2);
            return this.presenceList.slice(0, half);
        },
        presenceRight() {
            const half = Math.ceil(this.presenceList.length / 2);
            return this.presenceList.slice(half);
        },
    },
    created() {
        this.filters.date = this.getToday();
        this.fetchDailyPresences();
    },
    methods: {
        getToday() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        fetchDailyPresences() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(this.baseUrl, {
                params: this.filters,
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.dataContent = result || {
                        no_presence: [],
                        presence: [],
                        no_presence_count: 0,
                        presence_count: 0,
                    };

                    if (response && response.data && response.data.date) {
                        this.filters.date = response.data.date;
                    }
                })
                .catch(() => {
                    this.dataContent = {
                        no_presence: [],
                        presence: [],
                        no_presence_count: 0,
                        presence_count: 0,
                    };
                    this.errorMessage = 'Failed to load daily presences.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        applyFilter() {
            this.fetchDailyPresences();
        },
        resetFilter() {
            this.filters.date = this.getToday();
            this.fetchDailyPresences();
        },
    },
};
</script>
