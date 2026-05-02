<template>
    <div class="mx-auto w-full max-w-6xl px-6 py-8">
        <div class="grid gap-6 lg:grid-cols-[260px_1fr]">
            <aside class="sticky top-6 self-start rounded-2xl border border-border bg-panel p-4">
                <div class="text-sm font-semibold">Menu</div>
                <div class="mt-3 grid gap-1 text-sm">
                    <router-link
                        v-for="item in menu"
                        :key="item.to"
                        :to="item.to"
                        class="flex items-center justify-between rounded-xl px-3 py-2 text-muted hover:bg-surface hover:text-ink"
                        active-class="bg-surface text-ink"
                    >
                        <span class="truncate">{{ item.label }}</span>
                        <span v-if="item.badgeType === 'boolean'" class="ml-3 text-xs">
                            <span v-if="item.badge" class="text-emerald-600">✓</span>
                            <span v-else class="text-amber-600">!</span>
                        </span>
                        <span v-else-if="item.badgeType === 'number'" class="ml-3 rounded-full bg-sky-100 px-2 py-0.5 text-xs text-sky-700">
                            {{ item.badge }}
                        </span>
                    </router-link>
                </div>
            </aside>

            <main class="min-w-0">
                <router-view :registration="registration" @refresh="loadRegistration" />
            </main>
        </div>
    </div>
</template>

<script>
import Repository from '../../repository';

export default {
    name: 'RegistrationIndex',
    data() {
        return {
            registration: null,
        };
    },
    created() {
        this.loadRegistration();
    },
    computed: {
        menu() {
            const reg = this.registration || {};
            const details = Array.isArray(reg.details) ? reg.details : [];

            const countByLabel = (label) => details.filter((item) => item && item.label === label).length;

            const profileBadge = Boolean(reg.phone && reg.image_uri);
            const identityBadge = Boolean(reg.nik && reg.gender && reg.birth_date && reg.birth_place && reg.origin_address);
            const registrationBadge = Boolean(reg.test_order && reg.selection_path && reg.working_status);
            const educationBgBadge = Boolean(reg.origin_university && reg.origin_university_accreditation && reg.ip_s1 && reg.ip_profession && reg.ip_commulative);
            const familyBadge = Boolean(reg.father_name && reg.mother_name);

            return [
                { label: 'Home', to: '/reg/index' },
                { label: 'Profile', to: '/reg/index/account', badgeType: 'boolean', badge: profileBadge },
                { label: 'Identitas', to: '/reg/index/identity', badgeType: 'boolean', badge: identityBadge },
                { label: 'Pendaftaran', to: '/reg/index/reg', badgeType: 'boolean', badge: registrationBadge },
                { label: 'Riwayat Pendidikan', to: '/reg/index/education-bg', badgeType: 'boolean', badge: educationBgBadge },
                { label: 'Keluarga', to: '/reg/index/family', badgeType: 'boolean', badge: familyBadge },
                { label: 'Institusi Asal', to: '/reg/index/institution' },
                { label: 'Pendidikan', to: '/reg/index/educations', badgeType: 'number', badge: countByLabel('education') },
                { label: 'Pekerjaan', to: '/reg/index/jobs', badgeType: 'number', badge: countByLabel('job') },
                { label: 'Agenda Ilmiah', to: '/reg/index/scientifics', badgeType: 'number', badge: countByLabel('scientific') },
                { label: 'Organisasi', to: '/reg/index/organisations', badgeType: 'number', badge: countByLabel('organisation') },
                { label: 'Interenship', to: '/reg/index/interenships', badgeType: 'number', badge: countByLabel('interenship') },
                { label: 'Rekomendasi', to: '/reg/index/recomendations', badgeType: 'number', badge: countByLabel('recommendation') },
                { label: 'Prestasi', to: '/reg/index/achievements', badgeType: 'number', badge: countByLabel('achievement') },
                { label: 'Nilai Pendukung', to: '/reg/index/scores', badgeType: 'number', badge: countByLabel('score') },
                { label: 'Log Book', to: '/reg/index/logbook', badgeType: 'number', badge: countByLabel('logbook') },
            ];
        },
    },
    methods: {
        loadRegistration() {
            return Repository.get('/api/registration')
                .then((response) => {
                    const data = response && response.data ? response.data : {};
                    this.registration = data.result || null;
                })
                .catch(() => {
                    this.registration = null;
                });
        },
    },
};
</script>
