<template>
    <div class="grid gap-6">
        <header>
            <div class="text-xs uppercase tracking-[0.2em] text-muted">Dashboard</div>
            <h1 class="text-2xl font-semibold text-ink">Matric</h1>
        </header>

        <div
            v-if="errorMessage"
            class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600"
        >
            {{ errorMessage }}
        </div>

        <section class="grid grid-cols-1 gap-6">
            <div class="relative rounded-2xl border border-border bg-panel p-5">
                <Loading :active="loading" :is-full-page="false" />
                <div class="mb-4">
                    <div class="text-xs uppercase tracking-[0.2em] text-muted">Scoring</div>
                    <h2 class="text-lg font-semibold text-ink">Open Stase Tasks by Intake Year</h2>
                    <div class="mt-1 text-xs text-muted">
                        Scored vs. no-score open stase tasks, grouped by the student's intake year.
                    </div>
                </div>
                <div v-if="!loading && matricData.length === 0" class="py-10 text-center text-sm text-muted">
                    No open stase tasks found.
                </div>
                <div v-else class="h-80">
                    <BarChart :chart-data="chartData" :options="chartOptions" />
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import BarChart from '../../components/BarChart.vue';
import Repository from '../../repository';

export default {
    components: {
        Loading,
        BarChart,
    },
    data() {
        return {
            matricData: [],
            loading: false,
            errorMessage: '',
        };
    },
    computed: {
        chartData() {
            return {
                labels: this.matricData.map((row) => row.year),
                datasets: [
                    {
                        label: 'Scored',
                        backgroundColor: '#22c55e',
                        data: this.matricData.map((row) => row.scored),
                    },
                    {
                        label: 'No Score',
                        backgroundColor: '#e11d48',
                        data: this.matricData.map((row) => row.no_score),
                    },
                ],
            };
        },
        chartOptions() {
            return {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'bottom',
                },
                scales: {
                    xAxes: [
                        {
                            stacked: false,
                        },
                    ],
                    yAxes: [
                        {
                            stacked: false,
                            ticks: {
                                beginAtZero: true,
                                precision: 0,
                            },
                        },
                    ],
                },
            };
        },
    },
    created() {
        this.fetchMatric();
    },
    methods: {
        fetchMatric() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get('/api/dashboard-open-stase-task-matric')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.matricData = Array.isArray(result) ? result : [];
                })
                .catch(() => {
                    this.matricData = [];
                    this.errorMessage = 'Failed to load scoring matric.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
};
</script>
