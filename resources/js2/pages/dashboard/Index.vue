<template>
    <div class="dashboard-page">
        <section class="hero">
            <div class="hero-copy">
                <div class="hero-title">Good afternoon, team.</div>
                <p class="hero-text">
                    The internship rotation pipeline is steady and residents are on track.
                    Use the quick actions to review today's check-ins or add a new schedule.
                </p>
                <div class="hero-actions">
                    <button class="btn-primary">Review Check-ins</button>
                    <button class="btn-ghost">Create Schedule</button>
                </div>
            </div>
            <div class="hero-panel">
                <div class="panel-title">Today at a glance</div>
                <div class="panel-list">
                    <div class="panel-item">
                        <div class="panel-label">Open exams</div>
                        <div class="panel-value">6</div>
                    </div>
                    <div class="panel-item">
                        <div class="panel-label">Active rotations</div>
                        <div class="panel-value">24</div>
                    </div>
                    <div class="panel-item">
                        <div class="panel-label">Overdue logs</div>
                        <div class="panel-value">4</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="cards">
            <div v-for="card in cards" :key="card.label" class="card">
                <div class="card-label">{{ card.label }}</div>
                <div class="card-value">{{ card.value }}</div>
                <div class="card-meta">{{ card.meta }}</div>
            </div>
        </section>

        <section class="grid">
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">Stase progress</div>
                    <button class="panel-action">View all</button>
                </div>
                <div class="progress-list">
                    <div v-for="item in staseProgress" :key="item.name" class="progress-item">
                        <div class="progress-row">
                            <span>{{ item.name }}</span>
                            <span>{{ item.percent }}%</span>
                        </div>
                        <div class="progress-bar">
                            <span :style="{ width: item.percent + '%' }"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">Recent activity</div>
                    <button class="panel-action">Refresh</button>
                </div>
                <div class="activity-list">
                    <div v-for="activity in activities" :key="activity.title" class="activity-item">
                        <div class="activity-dot" :class="activity.tone"></div>
                        <div>
                            <div class="activity-title">{{ activity.title }}</div>
                            <div class="activity-meta">{{ activity.time }}</div>
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
                { title: 'Resident check-in verified', time: '5 min ago', tone: 'tone-ok' },
                { title: 'Rotation schedule updated', time: '18 min ago', tone: 'tone-info' },
                { title: 'New logbook submitted', time: '45 min ago', tone: 'tone-warn' },
                { title: 'Exam request approved', time: '1 hour ago', tone: 'tone-ok' },
            ],
        };
    },
};
</script>
