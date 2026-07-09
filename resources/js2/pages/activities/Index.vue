<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Activity Management</div>
                <h1 class="text-2xl font-semibold text-ink">Activities</h1>
            </div>
            <router-link
                class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                to="/blu/activities/create"
            >
                Add Activity
            </router-link>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="text-xs text-muted">Keyword</label>
                    <input
                        v-model.trim="filters.keyword"
                        type="text"
                        placeholder="Search name, title, or speaker..."
                        @keyup.enter="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="min-w-[160px]">
                    <label class="text-xs text-muted">Date From</label>
                    <input
                        v-model="filters.date_from"
                        type="date"
                        @change="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="min-w-[160px]">
                    <label class="text-xs text-muted">Date To</label>
                    <input
                        v-model="filters.date_to"
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
                <div class="font-semibold">Activities</div>
                <div class="text-xs text-muted" v-if="pagination.total">
                    {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }}
                </div>
            </div>
            <div v-if="errorMessage" class="border-b border-rose-100 bg-rose-50 px-5 py-3 text-xs text-rose-600">
                {{ errorMessage }}
            </div>
            <div class="divide-y divide-border">
                <div v-if="!loading && activities.length === 0" class="px-5 py-6 text-sm text-muted">
                    No activities found.
                </div>
                <div
                    v-for="(activity, index) in activities"
                    :key="activity.id"
                    class="flex flex-wrap items-center gap-3 px-5 py-4"
                    :class="isToday(activity) ? 'bg-emerald-50' : ''"
                >
                    <div class="w-8 text-sm font-semibold text-muted">
                        {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="font-semibold text-ink">{{ activity.name }}</div>
                            <span v-if="activity.status" class="text-xs text-muted">
                                Status: {{ activity.status }}
                            </span>
                        </div>
                        <div class="text-xs text-muted">
                            <span v-if="activity.title">{{ activity.title }}</span>
                            <span v-if="activity.speaker">• Speaker: {{ activity.speaker }}</span>
                            <span v-if="activity.place">• {{ activity.place }}</span>
                        </div>
                        <div class="text-xs text-muted" v-if="activity.start_date">
                            Date: {{ formatDateRange(activity.start_date, activity.end_date) }}
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                            type="button"
                            @click="openView(activity)"
                        >
                            View
                        </button>
                        <button
                            class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                            type="button"
                            @click="openEdit(activity)"
                        >
                            Edit
                        </button>
                        <button
                            class="rounded-lg bg-rose-500/10 px-3 py-1.5 text-xs text-rose-600"
                            type="button"
                            @click="deleteActivity(activity)"
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

        <Modal
            :open="viewModalOpen"
            :title="viewActivity ? (viewActivity.name || 'Activity Detail') : 'Activity Detail'"
            eyebrow="Activity detail"
            size="md"
            @close="closeView"
        >
            <div v-if="viewActivity" class="grid gap-3 text-sm">
                <div v-if="isToday(viewActivity)" class="rounded-xl bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700">
                    Happening today
                </div>
                <div v-for="field in viewFields" :key="field.label" class="grid grid-cols-3 gap-3">
                    <div class="text-xs uppercase tracking-wide text-muted">{{ field.label }}</div>
                    <div class="col-span-2 break-words text-ink">
                        <a
                            v-if="field.link"
                            :href="field.value"
                            target="_blank"
                            rel="noopener"
                            class="text-primary underline"
                        >{{ field.value }}</a>
                        <span v-else>{{ field.value }}</span>
                    </div>
                </div>
            </div>
        </Modal>

    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Modal from '../../components/Modal.vue';
import Repository from '../../repository';

const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

const todayStr = () => {
    const now = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
};

const parseDateTime = (value) => {
    if (!value) {
        return null;
    }
    const match = /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/.exec(value);
    if (!match) {
        return null;
    }
    return {
        y: Number(match[1]),
        mo: Number(match[2]),
        d: Number(match[3]),
        hh: match[4],
        mm: match[5],
    };
};

export default {
    components: {
        Loading,
        Modal,
    },
    data() {
        return {
            baseUrl: '/api/activities',
            activities: [],
            pagination: {},
            filters: {
                keyword: '',
                date_from: todayStr(),
                date_to: '',
                page: 1,
            },
            loading: false,
            errorMessage: '',
            viewModalOpen: false,
            viewActivity: null,
        };
    },
    computed: {
        viewFields() {
            const a = this.viewActivity;
            if (!a) {
                return [];
            }
            const fields = [
                { label: 'Name', value: a.name },
                { label: 'Title', value: a.title },
                { label: 'Speaker', value: a.speaker },
                { label: 'Place', value: a.place },
                { label: 'Date', value: this.formatDateRange(a.start_date, a.end_date) },
                { label: 'Category', value: a.category },
                { label: 'Type', value: a.type },
                { label: 'Status', value: a.status },
                { label: 'Passcode', value: a.passcode },
                { label: 'Link', value: a.link, link: true },
                { label: 'Note', value: a.note },
                { label: 'Description', value: a.desc },
            ];
            return fields.filter((field) => field.value !== null && field.value !== undefined && field.value !== '');
        },
    },
    created() {
        this.fetchActivities();
    },
    methods: {
        formatDateRange(start, end) {
            const s = parseDateTime(start);
            if (!s) {
                return '';
            }
            const day = (p) => `${p.d} ${MONTHS[p.mo - 1]} ${p.y}`;
            const time = (p) => `${p.hh}:${p.mm}`;

            let out = `${day(s)} ${time(s)}`;
            const e = parseDateTime(end);
            if (e) {
                const sameDay = e.y === s.y && e.mo === s.mo && e.d === s.d;
                out += sameDay ? ` - ${time(e)}` : ` - ${day(e)} ${time(e)}`;
            }
            return out;
        },
        isToday(activity) {
            const s = parseDateTime(activity && activity.start_date);
            if (!s) {
                return false;
            }
            const now = new Date();
            const toNum = (p) => p.y * 10000 + p.mo * 100 + p.d;
            const today = toNum({ y: now.getFullYear(), mo: now.getMonth() + 1, d: now.getDate() });
            const startNum = toNum(s);
            const e = parseDateTime(activity.end_date);
            const endNum = e ? toNum(e) : startNum;
            return today >= startNum && today <= endNum;
        },
        fetchActivities() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(this.baseUrl, {
                params: this.filters,
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];

                    this.activities = data;
                    this.pagination = result || {};
                })
                .catch(() => {
                    this.activities = [];
                    this.pagination = {};
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        applyFilter() {
            this.filters.page = 1;
            this.fetchActivities();
        },
        resetFilter() {
            this.filters.keyword = '';
            this.filters.date_from = todayStr();
            this.filters.date_to = '';
            this.filters.page = 1;
            this.fetchActivities();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchActivities();
        },
        openView(activity) {
            this.viewActivity = activity;
            this.viewModalOpen = true;
        },
        closeView() {
            this.viewModalOpen = false;
            this.viewActivity = null;
        },
        openEdit(activity) {
            if (!activity || !activity.id) {
                return;
            }

            this.$router.push(`/blu/activities/${activity.id}`);
        },
        deleteActivity(activity) {
            if (!window.confirm(`Delete activity ${activity.name}?`)) {
                return;
            }

            Repository.delete(`${this.baseUrl}/${activity.id}`)
                .then(() => {
                    this.fetchActivities();
                    this.$showToast('Activity deleted successfully.');
                })
                .catch(() => {
                    this.errorMessage = 'Failed to delete activity.';
                });
        },
    },
};
</script>
