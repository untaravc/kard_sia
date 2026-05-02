<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Registration Management</div>
                <h1 class="text-2xl font-semibold text-ink">Registrations</h1>
            </div>
            <div class="flex flex-wrap gap-2">
                <button
                    class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                    type="button"
                    @click="openPrintProfiles"
                >
                    Print Profiles
                </button>
                <button
                    class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                    type="button"
                    @click="openResume"
                >
                    Resume
                </button>
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
	            <div v-if="errorMessage" class="border-b border-rose-100 bg-rose-50 px-5 py-3 text-xs text-rose-600">
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
                                @click="openPrintRegistration(registration)"
                            >
                                Print
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

	export default {
	    components: {
	        Loading,
	    },
    watch: {
        $route(to, from) {
            if (!to || !from || to.fullPath === from.fullPath) {
                return;
            }

            this.filters.page = 1;
            this.fetchRegistrations();
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
	            registrations: [],
	            pagination: {},
            filters: {
                keyword: '',
                status: '',
                registration_period: '',
                page: 1,
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
	            loading: false,
	            errorMessage: '',
	            actionMenuOpenId: null,
	        };
	    },
    created() {
        if (!this.filters.registration_period) {
            this.filters.registration_period = this.periodOptions && this.periodOptions.length ? this.periodOptions[0] : '';
        }
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

            const section = this.$route && this.$route.query ? this.$route.query.section : null;
            if (section) {
                params.section = section;
            }

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
            this.filters.registration_period = this.periodOptions && this.periodOptions.length ? this.periodOptions[0] : '';
            this.filters.page = 1;
            this.fetchRegistrations();
        },
	        changePage(page) {
	            this.filters.page = page;
	            this.fetchRegistrations();
	        },
        openDetail(registration) {
            this.closeActionMenu();
            if (!registration || !registration.id) {
                return;
            }
            this.$router.push(`/blu/registrations/${registration.id}`);
        },
	        openPrintRegistration(registration) {
	            this.closeActionMenu();
	            if (!registration || !registration.id) {
	                return;
	            }

	            const token = localStorage.getItem('token');
	            const params = new URLSearchParams();
	            if (token) {
	                params.set('token', token);
	            }

	            const query = params.toString() ? `?${params.toString()}` : '';
	            window.open(`/print/registration/${registration.id}${query}`, '_blank');
	        },
	        openPrintProfiles() {
	            const period = this.filters && this.filters.registration_period ? this.filters.registration_period : '';
	            const token = localStorage.getItem('token');
	            const params = new URLSearchParams();
	            if (token) {
	                params.set('token', token);
	            }
	            if (period) {
	                params.set('registration_period', period);
	            }

	            const query = params.toString() ? `?${params.toString()}` : '';
	            window.open(`/print/registrations-profiles${query}`, '_blank');
	        },
	        openResume() {
	            const period = this.filters && this.filters.registration_period ? this.filters.registration_period : '';
	            const token = localStorage.getItem('token');
	            const params = new URLSearchParams();
	            if (token) {
	                params.set('token', token);
	            }
	            if (period) {
	                params.set('registration_period', period);
	            }

	            const query = params.toString() ? `?${params.toString()}` : '';
	            window.open(`/print/registrations-resume${query}`, '_blank');
	        },
	    },
	};
	</script>
