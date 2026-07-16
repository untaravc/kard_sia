<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Activity Detail</div>
                <h1 class="text-2xl font-semibold text-ink">{{ activity ? activity.name : 'Activity' }}</h1>
            </div>
            <div class="flex items-center gap-2">
                <router-link
                    v-if="activity"
                    class="rounded-xl border border-border px-4 py-2 text-sm font-medium text-muted"
                    :to="`/blu/activities/${activity.id}`"
                >
                    Edit
                </router-link>
                <router-link
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    to="/blu/activities"
                >
                    Back
                </router-link>
            </div>
        </header>

        <section class="relative rounded-2xl border border-border bg-panel p-5">
            <Loading :active="loading" :is-full-page="false" />
            <div v-if="errorMessage" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                {{ errorMessage }}
            </div>
            <div v-if="!loading && activity" class="grid gap-3 text-sm">
                <div v-if="isToday(activity)" class="rounded-xl bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700">
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
        </section>

        <section class="rounded-2xl border border-border bg-panel">
            <div class="flex items-center justify-between border-b border-border px-5 py-4">
                <div class="font-semibold">Lecturers ({{ lectures.length }})</div>
            </div>
            <div class="divide-y divide-border">
                <div v-if="!loading && lectures.length === 0" class="px-5 py-6 text-sm text-muted">
                    No lecturer presence recorded.
                </div>
                <div
                    v-for="item in lectures"
                    :key="item.id"
                    class="flex flex-wrap items-center justify-between gap-3 px-5 py-3"
                >
                    <div>
                        <div class="font-medium text-ink">{{ item.lecture ? item.lecture.name : '(Unknown lecture)' }}</div>
                        <div class="text-xs text-muted" v-if="item.lecture && item.lecture.univ_number">
                            No: {{ item.lecture.univ_number }}
                        </div>
                    </div>
                    <div class="text-xs text-muted">{{ formatDateTime(item.created_at) }}</div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-border bg-panel">
            <div class="flex items-center justify-between border-b border-border px-5 py-4">
                <div class="font-semibold">Students ({{ students.length }})</div>
            </div>
            <div class="divide-y divide-border">
                <div v-if="!loading && students.length === 0" class="px-5 py-6 text-sm text-muted">
                    No student presence recorded.
                </div>
                <div
                    v-for="item in students"
                    :key="item.id"
                    class="flex flex-wrap items-center justify-between gap-3 px-5 py-3"
                >
                    <div>
                        <div class="font-medium text-ink">{{ item.student ? item.student.name : '(Unknown student)' }}</div>
                        <div class="text-xs text-muted" v-if="item.student && item.student.univ_number">
                            No: {{ item.student.univ_number }}
                        </div>
                    </div>
                    <div class="text-xs text-muted">{{ formatDateTime(item.created_at) }}</div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Repository from '../../repository';

const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

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
    },
    data() {
        return {
            baseUrl: '/api/activities',
            activity: null,
            loading: false,
            errorMessage: '',
        };
    },
    computed: {
        lectures() {
            return this.activity && Array.isArray(this.activity.activity_lectures)
                ? this.activity.activity_lectures
                : [];
        },
        students() {
            return this.activity && Array.isArray(this.activity.activity_students)
                ? this.activity.activity_students
                : [];
        },
        viewFields() {
            const a = this.activity;
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
        this.fetchActivity();
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
        formatDateTime(value) {
            const p = parseDateTime(value);
            if (!p) {
                return '';
            }
            return `${p.d} ${MONTHS[p.mo - 1]} ${p.y} ${p.hh}:${p.mm}`;
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
        fetchActivity() {
            const id = this.$route.params.id;
            if (!id) {
                return;
            }

            this.loading = true;
            this.errorMessage = '';

            return Repository.get(`${this.baseUrl}/${id}`)
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.activity = result || null;
                })
                .catch(() => {
                    this.activity = null;
                    this.errorMessage = 'Failed to load activity.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
};
</script>
