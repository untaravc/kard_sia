<template>
    <div class="flex flex-col gap-7">
        <section class="grid grid-cols-1 gap-6 lg:grid-cols-5">
            <div class="rounded-3xl bg-gradient-to-br from-surface to-ext p-6 lg:col-span-3">
                <div class="text-xl font-semibold">Good afternoon, team.</div>
                <p class="mt-3 text-muted leading-relaxed">
                    The internship rotation pipeline is steady and residents are on track.
                    Use the quick actions to review today's check-ins or add a new schedule.
                </p>
                <div class="mt-5 flex gap-3">
                    <button class="rounded-xl bg-primary px-5 py-2.5 text-sm font-medium text-white">Review Check-ins</button>
                    <button class="rounded-xl border border-primary px-5 py-2.5 text-sm font-medium text-primary">Create Schedule</button>
                </div>
            </div>
            <div class="rounded-3xl border border-border bg-panel p-6 lg:col-span-2">
                <div class="font-semibold">Today at a glance</div>
                <div class="mt-4 grid gap-3.5">
                    <div class="flex items-center justify-between rounded-2xl bg-ext px-3 py-2.5">
                        <div class="text-muted">Open exams</div>
                        <div class="font-semibold">6</div>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl bg-ext px-3 py-2.5">
                        <div class="text-muted">Active rotations</div>
                        <div class="font-semibold">24</div>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl bg-ext px-3 py-2.5">
                        <div class="text-muted">Overdue logs</div>
                        <div class="font-semibold">4</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div v-for="card in cards" :key="card.label" class="rounded-2xl border border-border bg-panel px-5 py-4">
                <div class="text-xs text-muted">{{ card.label }}</div>
                <div class="mt-2 text-2xl font-semibold">{{ card.value }}</div>
                <div class="mt-1.5 text-xs text-muted">{{ card.meta }}</div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-border bg-panel px-5 py-4">
                <div class="mb-3.5 flex items-center justify-between">
                    <div class="font-semibold">Stase progress</div>
                    <button class="text-primary">View all</button>
                </div>
                <div class="grid gap-3.5">
                    <div v-for="item in staseProgress" :key="item.name">
                        <div class="mb-1.5 flex justify-between text-sm">
                            <span>{{ item.name }}</span>
                            <span>{{ item.percent }}%</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-surface-muted">
                            <span class="block h-full rounded-full bg-primary" :style="{ width: item.percent + '%' }"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-border bg-panel px-5 py-4">
                <div class="mb-3.5 flex items-center justify-between">
                    <div class="font-semibold">Recent activity</div>
                    <button class="text-primary">Refresh</button>
                </div>
                <div class="grid gap-3.5">
                    <div v-for="activity in activities" :key="activity.title" class="flex items-start gap-2.5">
                        <div class="mt-1.5 h-2.5 w-2.5 rounded-full" :class="activity.tone"></div>
                        <div>
                            <div class="font-semibold">{{ activity.title }}</div>
                            <div class="text-xs text-muted">{{ activity.time }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
export default {
    data() {
        return {
            cards: [
                { label: 'Active residents', value: '128', meta: '12 on rotation' },
                { label: 'Lecturers', value: '42', meta: '3 new profiles' },
                { label: 'Open exams', value: '9', meta: '2 scheduled today' },
                { label: 'Daily activities', value: '214', meta: '14 pending review' },
            ],
            staseProgress: [
                { name: 'Tahap 1', percent: 78 },
                { name: 'Tahap 2', percent: 61 },
                { name: 'Tahap 3', percent: 46 },
                { name: 'Referat', percent: 88 },
            ],
            activities: [
                { title: 'Resident check-in verified', time: '5 min ago', tone: 'bg-emerald-500' },
                { title: 'Rotation schedule updated', time: '18 min ago', tone: 'bg-blue-500' },
                { title: 'New logbook submitted', time: '45 min ago', tone: 'bg-amber-500' },
                { title: 'Exam request approved', time: '1 hour ago', tone: 'bg-emerald-500' },
            ],
        };
    },
};
</script>
