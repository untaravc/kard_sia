<template>
    <div class="grid gap-6 overflow-x-hidden">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Registration Management</div>
                <h1 class="text-2xl font-semibold text-ink">Registration Scores</h1>
            </div>
            <div class="flex flex-wrap gap-2">
                <button
                    class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                    type="button"
                    @click="loadData"
                >
                    Refresh
                </button>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="button"
                    :disabled="loading"
                    @click="updateAll"
                >
                    Sync Changed
                </button>
                <button
                    class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-medium text-white"
                    type="button"
                    :disabled="loading"
                    @click="updateAllForce"
                >
                    Sync All
                </button>
            </div>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="text-xs text-muted">Name</label>
                    <input
                        v-model.trim="filters.name"
                        type="text"
                        placeholder="Search name..."
                        @keyup.enter="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="flex-1 min-w-[180px]">
                    <label class="text-xs text-muted">Period</label>
                    <select
                        v-model="filters.registration_period"
                        @change="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option v-for="period in periodOptions" :key="period" :value="period">
                            {{ period }}
                        </option>
                    </select>
                </div>
                <div class="flex-1 min-w-[220px]">
                    <label class="text-xs text-muted">Section</label>
                    <select
                        v-model="filters.section"
                        @change="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="administration">Administrasi</option>
                        <option value="journal">Journal</option>
                        <option value="interview">Interview</option>
                        <option value="score">Score</option>
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
                </div>
            </div>
        </section>

        <section class="relative rounded-2xl border border-border bg-panel overflow-x-hidden">
            <Loading :active="loading" :is-full-page="false" />
            <div
                v-if="errorMessage"
                class="border-b border-rose-100 bg-rose-50 px-5 py-3 text-xs text-rose-600"
            >
                {{ errorMessage }}
            </div>

            <div class="w-full max-w-full overflow-x-auto overflow-y-hidden">
                <table class="min-w-[1400px] w-full border-collapse">
                    <thead class="bg-slate-50 text-xs text-muted">
                        <tr>
                            <th class="px-3 py-3 text-left sticky left-0 bg-slate-50 z-10">No</th>
                            <th class="px-3 py-3 text-left sticky left-[52px] bg-slate-50 z-10">Name</th>
                            <th class="px-3 py-3 text-left">Lulusan</th>
                            <th class="px-3 py-3 text-left">Jalur Seleksi</th>
                            <th class="px-3 py-3 text-left">Status</th>
                            <th class="px-3 py-3 text-left">Ke-</th>
                            <th class="px-3 py-3 text-left">Point tambah</th>
                            <th class="px-3 py-3 text-left">PNS ke-2</th>
                            <th class="px-3 py-3 text-left">Status Univ</th>
                            <th class="px-3 py-3 text-left">Poin Univ</th>
                            <th class="px-3 py-3 text-left">IPK</th>
                            <th class="px-3 py-3 text-left">Bobot IPK</th>
                            <th class="px-3 py-3 text-left">B.Ing</th>
                            <th class="px-3 py-3 text-left">TOEFL</th>
                            <th class="px-3 py-3 text-left">Bobot B.Ing</th>
                            <th class="px-3 py-3 text-left">TPA</th>
                            <th class="px-3 py-3 text-left">Bobot TPA</th>
                            <th class="px-3 py-3 text-left">UTUL</th>
                            <th class="px-3 py-3 text-left">EKG</th>
                            <th class="px-3 py-3 text-left">Total UTUL</th>
                            <th class="px-3 py-3 text-left">Bobot UTUL</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <tr
                            v-for="(item, index) in rows"
                            :key="item.id || index"
                            :class="item.has_change ? 'bg-amber-50 hover:bg-amber-100' : 'hover:bg-slate-100'"
                            class="group border-t border-border transition-colors"
                        >
                            <td
                                class="px-3 py-2 sticky left-0 z-10 w-[52px]"
                                :class="item.has_change ? 'bg-amber-50 group-hover:bg-amber-100' : 'bg-white group-hover:bg-slate-100'"
                            >
                                {{ index + 1 }}
                            </td>
                            <td
                                class="px-3 py-2 sticky left-[52px] z-10 min-w-[220px] shadow-[2px_0_0_0_rgba(15,23,42,0.08)]"
                                :class="item.has_change ? 'bg-amber-50 group-hover:bg-amber-100' : 'bg-white group-hover:bg-slate-100'"
                            >
                                <div class="font-semibold text-ink">{{ item.name }}</div>
                            </td>
                            <td class="px-3 py-2 min-w-[220px] text-xs text-muted">
                                {{ item.origin_university }} <span v-if="item.origin_university_accreditation">[{{ item.origin_university_accreditation }}]</span>
                            </td>
                            <td class="px-3 py-2 text-xs text-muted">{{ item.selection_path }}</td>
                            <td class="px-3 py-2 text-xs text-muted">{{ item.working_status }}</td>
                            <td class="px-3 py-2 text-xs text-muted">{{ item.test_order }}</td>
                            <td class="px-3 py-2">
                                <select
                                    v-model.number="item.score.selection_path_multiplier"
                                    class="w-[96px] rounded-lg border border-border bg-white px-2 py-1 text-xs"
                                    @change="markChanged(item)"
                                >
                                    <option :value="0">0</option>
                                    <option v-for="i in 8" :key="i" :value="i * 25">{{ i * 25 }}</option>
                                </select>
                            </td>
                            <td class="px-3 py-2">
                                <select
                                    v-model.number="item.score.pns_multiplier"
                                    class="w-[96px] rounded-lg border border-border bg-white px-2 py-1 text-xs"
                                    @change="markChanged(item)"
                                >
                                    <option :value="0">0</option>
                                    <option v-for="i in 2" :key="i" :value="i * 25">{{ i * 25 }}</option>
                                </select>
                            </td>
                            <td class="px-3 py-2">
                                <select
                                    v-model="item.score.origin_university_type"
                                    class="w-[140px] rounded-lg border border-border bg-white px-2 py-1 text-xs"
                                    @change="markChanged(item)"
                                >
                                    <option v-for="type in univType" :key="type.name" :value="type.name">{{ type.name }}</option>
                                </select>
                            </td>
                            <td class="px-3 py-2 text-xs text-muted">
                                {{ item.score.origin_university_multiplier }}
                            </td>
                            <td class="px-3 py-2 text-xs text-muted">
                                {{ item.ip_commulative }}
                            </td>
                            <td class="px-3 py-2 text-xs text-muted">
                                {{ item.score.quality_ipk }}
                            </td>
                            <td class="px-3 py-2 text-xs text-muted min-w-[180px]">
                                <div v-if="item.english" class="flex items-center justify-between gap-2">
                                    <div>{{ item.english.desc }}</div>
                                    <div class="text-[10px] text-muted">[{{ item.english.name }}]</div>
                                </div>
                            </td>
                            <td class="px-3 py-2">
                                <input
                                    v-model.number="item.score.quality_toefl"
                                    type="number"
                                    class="w-[110px] rounded-lg border border-border bg-white px-2 py-1 text-xs"
                                    @change="markChanged(item)"
                                />
                            </td>
                            <td class="px-3 py-2 text-xs text-muted">
                                {{ item.score.quality_english }}
                            </td>
                            <td class="px-3 py-2 text-xs text-muted min-w-[180px]">
                                <div v-if="item.tpa" class="flex items-center justify-between gap-2">
                                    <div>{{ item.tpa.desc }}</div>
                                    <div class="text-[10px] text-muted">[{{ item.tpa.name }}]</div>
                                </div>
                            </td>
                            <td class="px-3 py-2 text-xs text-muted">
                                {{ item.score.quality_tpa }}
                            </td>
                            <td class="px-3 py-2">
                                <input
                                    v-model.number="item.score.score_written_exam"
                                    type="number"
                                    class="w-[110px] rounded-lg border border-border bg-white px-2 py-1 text-xs"
                                    @change="markChanged(item)"
                                />
                            </td>
                            <td class="px-3 py-2">
                                <input
                                    v-model.number="item.score.score_ecg"
                                    type="number"
                                    class="w-[110px] rounded-lg border border-border bg-white px-2 py-1 text-xs"
                                    @change="markChanged(item)"
                                />
                            </td>
                            <td class="px-3 py-2 text-xs text-muted">
                                {{ item.score.score_written_exam_total }}
                            </td>
                            <td class="px-3 py-2 text-xs text-muted">
                                {{ item.score.quality_written_exam }}
                            </td>
                        </tr>
                        <tr v-if="!loading && rows.length === 0">
                            <td colspan="21" class="px-5 py-6 text-sm text-muted">No data.</td>
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

export default {
    components: {
        Loading,
    },
    computed: {
        periodOptions() {
            const start = new Date(Date.UTC(2025, 0, 1));

            const now = new Date();
            const nowYear = now.getUTCFullYear();
            const nowMonth = now.getUTCMonth(); // 0-11
            const end = nowMonth < 6
                ? new Date(Date.UTC(nowYear, 6, 1))
                : new Date(Date.UTC(nowYear + 1, 0, 1));

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
            updateUrl: '/api/registrations/score-administration',
            loading: false,
            errorMessage: '',
            rows: [],
            filters: {
                page: 1,
                name: '',
                registration_period: '',
                per_page: 50,
                section: 'administration',
            },
            univType: [
                { name: 'UGM', point: 3.5 },
                { name: 'UN-A-JAWA', point: 3 },
                { name: 'UN-A-LUAR', point: 2.75 },
                { name: 'UN-B-JAWA', point: 2.5 },
                { name: 'UN-B-LUAR', point: 2.25 },
                { name: 'UN-C-JAWA', point: 2.25 },
                { name: 'UN-C-LUAR', point: 2 },
                { name: 'US-A-JAWA', point: 2.75 },
                { name: 'US-A-LUAR', point: 2.5 },
                { name: 'US-B-JAWA', point: 2.25 },
                { name: 'US-B-LUAR', point: 2 },
                { name: 'US-C-JAWA', point: 1.5 },
                { name: 'US-C-LUAR', point: 1.25 },
            ],
        };
    },
    created() {
        this.filters.registration_period = this.periodOptions && this.periodOptions.length ? this.periodOptions[0] : '';
        this.loadData();
    },
    methods: {
        normalizeScore(score) {
            const defaults = {
                registration_id: 0,
                period: 0,
                selection_path_multiplier: 0,
                pns_multiplier: 0,
                origin_university_type: this.univType[0] ? this.univType[0].name : null,
                origin_university_multiplier: 0,
                quality_ipk: 0,
                quality_toefl: 0,
                quality_english: 0,
                quality_tpa: 0,
                score_written_exam: 0,
                score_ecg: 0,
                score_written_exam_total: 0,
                quality_written_exam: 0,
                quality_journal: 0,
                subtotal_score: 0,
            };

            return {
                ...defaults,
                ...(score || {}),
            };
        },
        markChanged(item) {
            item.has_change = 1;
        },
        loadData() {
            this.loading = true;
            this.errorMessage = '';

            const params = {
                ...this.filters,
            };

            if (!params.name) {
                delete params.name;
            }
            if (!params.registration_period) {
                delete params.registration_period;
            }

            return Repository.get(this.baseUrl, { params })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];

                    data.forEach((item) => {
                        item.score = this.normalizeScore(item.score);
                        item.has_change = item.has_change ? 1 : 0;
                    });

                    this.rows = data;
                })
                .catch(() => {
                    this.rows = [];
                    this.errorMessage = 'Failed to load registration scores.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        applyFilter() {
            this.filters.page = 1;
            this.loadData();
        },
        updateScore(item) {
            return Repository.patch(this.updateUrl, item);
        },
        updateAll() {
            this.loading = true;

            const queue = this.rows.filter((row) => row.has_change);
            return queue.reduce((promise, row) => {
                return promise.then(() => this.updateScore(row));
            }, Promise.resolve())
                .then(() => this.loadData())
                .catch(() => {
                    this.errorMessage = 'Failed to sync scores.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        updateAllForce() {
            this.loading = true;

            return this.rows.reduce((promise, row) => {
                return promise.then(() => this.updateScore(row));
            }, Promise.resolve())
                .then(() => this.loadData())
                .catch(() => {
                    this.errorMessage = 'Failed to sync scores.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
};
</script>
