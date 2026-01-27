<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Presence Monitoring</div>
                <h1 class="text-2xl font-semibold text-ink">Student Presence Summary</h1>
            </div>
            <router-link
                class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                to="/cblu/presences"
            >
                Back
            </router-link>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <div class="text-sm font-semibold text-ink">
                        {{ residentName }}
                    </div>
                    <div class="text-xs text-muted">
                        Student ID: {{ studentId }}
                    </div>
                </div>
                <div class="min-w-[200px]">
                    <label class="text-xs text-muted">Period</label>
                    <input
                        v-model="filters.period"
                        type="month"
                        @change="fetchSummary"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
            </div>
        </section>

        <section class="relative rounded-2xl border border-border bg-panel p-5">
            <Loading :active="loading" :is-full-page="false" />

            <div v-if="errorMessage" class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                {{ errorMessage }}
            </div>

            <div v-if="!loading && weeks.length === 0" class="rounded-xl border border-border bg-white px-4 py-6 text-sm text-muted">
                No presence data found for this period.
            </div>

            <div v-for="(week, weekIndex) in weeks" :key="`week-${weekIndex}`" class="mb-4">
                <div class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-muted">
                    Week {{ weekIndex + 1 }}
                </div>
                <div class="grid gap-3 md:grid-cols-3 xl:grid-cols-6">
                    <div
                        v-for="(day, dayIndex) in week"
                        v-if="dayIndex < 6"
                        :key="`${day.date}-${dayIndex}`"
                        class="grid min-h-[150px] gap-2 rounded-2xl border border-border bg-white p-3"
                    >
                        <div class="text-xs font-semibold text-muted">
                            {{ formatDay(day.date) }}
                        </div>

                        <div v-if="day.presence" class="text-xs text-emerald-700">
                            <div class="font-semibold">Checkin</div>
                            <div>{{ formatTime(day.presence.checkin) }}</div>
                        </div>
                        <div v-else class="text-xs text-muted">
                            No checkin
                        </div>

                        <div class="grid gap-1 text-xs">
                            <div
                                v-for="(activity, activityIndex) in day.activities || []"
                                :key="`activity-${activityIndex}-${day.date}`"
                                class="rounded-lg bg-slate-50 px-2 py-1 text-slate-700"
                                :title="activityName(activity)"
                            >
                                {{ truncate(activityName(activity), 28) }}
                            </div>
                        </div>
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
            baseUrl: '/api/presences/student',
            loading: false,
            errorMessage: '',
            dataContent: {
                data: [],
                resident: null,
            },
            filters: {
                period: '',
            },
        };
    },
    computed: {
        studentId() {
            return this.$route.params.student_id;
        },
        weeks() {
            return Array.isArray(this.dataContent.data) ? this.dataContent.data : [];
        },
        residentName() {
            return this.dataContent.resident && this.dataContent.resident.name
                ? this.dataContent.resident.name
                : 'Student';
        },
    },
    created() {
        this.filters.period = this.getCurrentPeriod();
        this.fetchSummary();
    },
    methods: {
        getCurrentPeriod() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            return `${year}-${month}`;
        },
        fetchSummary() {
            if (!this.studentId) {
                this.errorMessage = 'Student ID is missing.';
                return;
            }

            this.loading = true;
            this.errorMessage = '';

            return Repository.get(`${this.baseUrl}/${this.studentId}`, {
                params: this.filters,
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.dataContent = result || { data: [], resident: null };
                })
                .catch(() => {
                    this.dataContent = { data: [], resident: null };
                    this.errorMessage = 'Failed to load student presence summary.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        formatDay(dateString) {
            if (!dateString) {
                return '-';
            }
            const date = new Date(dateString);
            if (Number.isNaN(date.getTime())) {
                return dateString;
            }
            return date.toLocaleDateString('id-ID', {
                weekday: 'short',
                day: '2-digit',
                month: 'short',
                year: 'numeric',
            });
        },
        formatTime(dateTimeString) {
            if (!dateTimeString) {
                return '-';
            }
            const date = new Date(dateTimeString);
            if (Number.isNaN(date.getTime())) {
                return dateTimeString;
            }
            return date.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
            });
        },
        activityName(activityStudent) {
            return activityStudent && activityStudent.activity && activityStudent.activity.name
                ? activityStudent.activity.name
                : '-';
        },
        truncate(value, maxLength) {
            if (!value || value.length <= maxLength) {
                return value;
            }
            return `${value.slice(0, maxLength - 1)}…`;
        },
    },
};
</script>

