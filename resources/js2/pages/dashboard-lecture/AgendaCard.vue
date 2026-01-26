<template>
    <div class="rounded-2xl border border-border bg-panel">
        <div class="flex items-center justify-between border-b border-border px-5 py-4">
            <div class="text-sm font-semibold text-ink">Agenda Hari Ini</div>
            <router-link
                to="/dosen/activities"
                class="rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"
            >
                Semua agenda
            </router-link>
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
export default {
    props: {
        schedules: {
            type: Array,
            default: () => [],
        },
    },
    methods: {
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
