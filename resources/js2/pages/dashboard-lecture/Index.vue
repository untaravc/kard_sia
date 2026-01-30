<template>
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
            <div class="flex w-full flex-col gap-6 lg:max-w-[360px] lg:flex-none">
                <QuickLinkCard
                    title="Kurikulum & Standar Operasional"
                    subtitle="Akses dokumen resmi"
                    link-url="https://drive.google.com/drive/folders/1LoCN_taJgB37eHhdc9HY1IplL8N5jjBV?usp=sharing"
                />
                <AgendaCard
                    :schedules="schedules"
                    :loading="loadingSchedule"
                    :date="agendaDate"
                    @date-change="handleAgendaDateChange"
                />
            </div>

        </div>
    </div>
</template>

<script>
import Repository from '../../repository';
import QuickLinkCard from './QuickLinkCard.vue';
import AgendaCard from './AgendaCard.vue';

export default {
    components: {
        QuickLinkCard,
        AgendaCard,
    },
    data() {
        return {
            schedules: [],
            loadingSchedule: false,
            agendaDate: '',
        };
    },
    created() {
        this.agendaDate = this.getDateString(new Date());
        this.loadSchedule();
    },
    methods: {
        getDateString(date) {
            const year = date.getFullYear();
            const month = `${date.getMonth() + 1}`.padStart(2, '0');
            const day = `${date.getDate()}`.padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        loadSchedule(date = this.agendaDate) {
            this.loadingSchedule = true;
            return Repository.get('/api/activities-today', {
                params: {
                    date,
                },
            })
                .then((response) => {
                    const data = response && response.data ? response.data.result : [];
                    this.schedules = Array.isArray(data) ? data : [];
                })
                .catch(() => {
                    this.schedules = [];
                })
                .finally(() => {
                    this.loadingSchedule = false;
                });
        },
        handleAgendaDateChange(value) {
            this.agendaDate = value;
            this.loadSchedule(value);
        },
    },
};
</script>
