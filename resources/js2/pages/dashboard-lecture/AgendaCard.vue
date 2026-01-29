<template>
    <div class="relative rounded-2xl border border-border bg-panel">
        <Loading :active="loading" :is-full-page="false" />
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-border px-5 py-4">
            <div class="text-sm font-semibold text-ink">{{ title }}</div>
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="rounded-lg border border-border px-2 py-1 text-xs text-muted"
                    @click="shiftDate(-1)"
                >
                    ‹
                </button>
                <div class="rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                    {{ formattedDate }}
                </div>
                <button
                    type="button"
                    class="rounded-lg border border-border px-2 py-1 text-xs text-muted"
                    @click="shiftDate(1)"
                >
                    ›
                </button>
                <router-link
                    to="/dosen/activities"
                    class="rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"
                >
                    Semua agenda
                </router-link>
            </div>
        </div>
        <div class="divide-y divide-border">
            <div
                v-for="(schedule, index) in schedules"
                :key="index"
                class="flex items-center justify-between gap-4 px-5 py-4 text-sm"
            >
                <div class="flex-1">
                    <div class="font-semibold text-ink">{{ schedule.name }}</div>
                    <div class="text-xs text-muted">
                        <span v-if="schedule.speaker"><b>{{ schedule.speaker }}:</b> </span>
                        <span>{{ schedule.title }}</span>
                    </div>
                    <div v-if="schedule.start_date || schedule.end_date" class="mt-1 text-xs text-muted">
                        <span v-if="schedule.start_date">Mulai: {{ formatDateTime(schedule.start_date) }}</span>
                        <span v-if="schedule.end_date"> • Selesai: {{ formatDateTime(schedule.end_date) }}</span>
                    </div>
                </div>
            </div>
            <div v-if="!schedules.length" class="px-5 py-6 text-sm text-muted">
                Tidak ada agenda.
            </div>
        </div>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
    components: {
        Loading,
    },
    props: {
        schedules: {
            type: Array,
            default: () => [],
        },
        loading: {
            type: Boolean,
            default: false,
        },
        date: {
            type: String,
            default: '',
        },
    },
    computed: {
        title() {
            return this.isToday ? 'Agenda Hari Ini' : 'Agenda';
        },
        formattedDate() {
            const date = this.parseDate(this.date) || new Date();
            return date.toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'short',
                day: '2-digit',
            });
        },
        isToday() {
            if (!this.date) {
                return true;
            }
            const today = this.getDateString(new Date());
            return this.date === today;
        },
    },
    methods: {
        parseDate(value) {
            if (!value) {
                return null;
            }
            const date = new Date(`${value}T00:00:00`);
            if (Number.isNaN(date.getTime())) {
                return null;
            }
            return date;
        },
        getDateString(date) {
            const year = date.getFullYear();
            const month = `${date.getMonth() + 1}`.padStart(2, '0');
            const day = `${date.getDate()}`.padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        shiftDate(amount) {
            const base = this.parseDate(this.date) || new Date();
            base.setDate(base.getDate() + amount);
            this.$emit('date-change', this.getDateString(base));
        },
        formatDateTime(value) {
            if (!value) {
                return '';
            }
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return value;
            }
            return date.toLocaleString('id-ID', {
                year: 'numeric',
                month: 'short',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
            });
        },
    },
};
</script>
