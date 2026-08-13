<template>
    <div class="flex flex-col gap-7">
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div v-for="card in cards" :key="card.label" class="rounded-2xl border border-border bg-panel px-5 py-4">
                <div class="text-xs text-muted">{{ card.label }}</div>
                <div class="mt-2 text-2xl font-semibold">{{ stats[card.key] }}</div>
                <div class="mt-1.5 text-xs text-muted">{{ card.meta }}</div>
            </div>
        </section>
    </div>
</template>

<script>
import Repository from '../../repository';

export default {
    data() {
        return {
            stats: {
                student_active: 0,
                lecture_active: 0,
            },
            cards: [
                { label: 'Active students', key: 'student_active', meta: 'Students with active status' },
                { label: 'Active lecturers', key: 'lecture_active', meta: 'Lecturers with active status' },
            ],
        };
    },
    created() {
        this.fetchStats();
    },
    methods: {
        fetchStats() {
            return Repository.get('/api/dashboard-stats')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.stats = {
                        ...this.stats,
                        ...(result || {}),
                    };
                })
                .catch(() => {
                    this.stats = {
                        student_active: 0,
                        lecture_active: 0,
                    };
                });
        },
    },
};
</script>
