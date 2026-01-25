<template>
    <div class="grid gap-6">
        <div class="grid gap-6 lg:grid-cols-[320px_1fr]">
            <div class="grid gap-6">
                <ProfileCard
                    :user="user"
                    :stats="stats"
                    :today-presence="todayPresence"
                />
                <QuickLinkCard
                    title="Kurikulum & SOP"
                    subtitle="Akses dokumen resmi"
                    link-url="https://drive.google.com/drive/folders/1LoCN_taJgB37eHhdc9HY1IplL8N5jjBV?usp=sharing"
                />
                <QuickAccessCard />
                <StaseTakenCard :stases="stasesTaken" />
            </div>

            <div class="grid gap-6">
                <AgendaCard />
                <ActiveAssignmentsCard
                    :stase-name="activeAssignments.staseName"
                    :tasks="activeAssignments.tasks"
                />
            </div>
        </div>
    </div>
</template>

<script>
import ProfileCard from './ProfileCard.vue';
import QuickLinkCard from './QuickLinkCard.vue';
import QuickAccessCard from './QuickAccessCard.vue';
import StaseTakenCard from './StaseTakenCard.vue';
import AgendaCard from './AgendaCard.vue';
import ActiveAssignmentsCard from './ActiveAssignmentsCard.vue';

export default {
    components: {
        ProfileCard,
        QuickLinkCard,
        QuickAccessCard,
        StaseTakenCard,
        AgendaCard,
        ActiveAssignmentsCard,
    },
    data() {
        return {
            user: {
                name: 'Resident Student',
                email: 'student@example.com',
                image: '',
            },
            stats: {
                avg_point: '88.5',
                stases: '4',
                presences: '128',
            },
            todayPresence: {
                checkin: '08:12',
                checkout: '16:45',
            },
            stasesTaken: [
                {
                    id: 1,
                    name: 'Cardiology',
                    status: 'ongoing',
                    period: '2024-01-08 - 2024-03-08',
                    evaluation_link: true,
                    evaluated: false,
                },
                {
                    id: 2,
                    name: 'Emergency',
                    status: 'finish',
                    period: '2023-10-01 - 2023-12-01',
                    evaluation_link: true,
                    evaluated: true,
                },
            ],
            activeAssignments: {
                staseName: 'Cardiology',
                tasks: [
                    {
                        id: 1,
                        name: 'Case Presentation',
                        plan: '2024-03-12',
                        title: 'Case report on arrhythmia',
                        open: true,
                        reviewers: [
                            { name: 'Dr. Ahmad', score: 85 },
                            { name: 'Dr. Lina', score: 0 },
                        ],
                    },
                    {
                        id: 2,
                        name: 'Skill Assessment',
                        plan: '',
                        title: 'Procedure checklist',
                        open: false,
                        reviewers: [],
                    },
                ],
            },
        };
    },
};
</script>
