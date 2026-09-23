<template>
    <div class="reg-page mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 sm:py-8">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[260px_minmax(0,1fr)]">
            <aside class="min-w-0 self-start rounded-2xl border border-border bg-panel p-4 lg:sticky lg:top-6">
                <div class="px-1">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-ink">Kelengkapan data utama</span>
                        <span class="text-muted">{{ completedCount }}/{{ requiredItems.length }}</span>
                    </div>
                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-surface-muted">
                        <div class="h-full rounded-full transition-all duration-500"
                            :class="completedCount === requiredItems.length ? 'bg-emerald-500' : 'bg-amber-500'"
                            :style="{ width: progressPercent + '%' }"></div>
                    </div>
                </div>

                <nav class="mt-4 hidden gap-4 text-sm lg:grid">
                    <div v-for="group in menuGroups" :key="group.title">
                        <div v-if="group.title" class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-muted">
                            {{ group.title }}
                        </div>
                        <div class="grid gap-0.5">
                            <router-link
                                v-for="item in group.items"
                                :key="item.to"
                                :to="item.to"
                                exact
                                class="flex items-center justify-between rounded-xl px-3 py-2 text-muted hover:bg-surface hover:text-ink"
                                active-class="bg-surface font-medium text-ink"
                            >
                                <span class="truncate">{{ item.label }}</span>
                                <span v-if="item.badgeType === 'boolean'" class="ml-3 flex-none"
                                    :title="item.badge ? 'Lengkap' : 'Belum lengkap'">
                                    <Icon v-if="item.badge" icon="mdi:check-circle" class="h-4 w-4 text-emerald-500" />
                                    <Icon v-else icon="mdi:alert-circle-outline" class="h-4 w-4 text-amber-500" />
                                </span>
                                <span v-else-if="item.badgeType === 'number'"
                                    class="ml-3 min-w-[1.5rem] flex-none rounded-full px-2 py-0.5 text-center text-xs"
                                    :class="item.badge ? 'bg-sky-100 text-sky-700' : 'bg-surface-muted text-muted'">
                                    {{ item.badge }}
                                </span>
                            </router-link>
                        </div>
                    </div>
                </nav>

                <nav class="-mx-4 mt-4 flex gap-2 overflow-x-auto px-4 pb-1 text-sm lg:hidden">
                    <router-link
                        v-for="item in flatMenu"
                        :key="item.to"
                        :to="item.to"
                        exact
                        class="flex flex-none items-center gap-1.5 whitespace-nowrap rounded-full border border-border px-3 py-1.5 text-muted"
                        active-class="border-primary bg-primary text-white"
                    >
                        {{ item.label }}
                        <span v-if="item.badgeType === 'boolean' && !item.badge" class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        <span v-else-if="item.badgeType === 'number' && item.badge" class="text-xs opacity-80">({{ item.badge }})</span>
                    </router-link>
                </nav>
            </aside>

            <main class="min-w-0">
                <div v-if="isLocked && !isHome"
                    class="mb-4 flex items-start gap-3 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                    <Icon icon="mdi:lock-outline" class="mt-0.5 h-4 w-4 flex-none" />
                    <span>Pengisian pendaftaran telah diselesaikan. Data hanya dapat dilihat dan tidak dapat diubah lagi.</span>
                </div>

                <div v-if="loading && !registration" class="grid gap-4">
                    <div class="h-40 animate-pulse rounded-2xl border border-border bg-panel"></div>
                    <div class="h-64 animate-pulse rounded-2xl border border-border bg-panel"></div>
                </div>

                <div v-else-if="loadError && !registration"
                    class="rounded-2xl border border-red-200 bg-red-50 p-6 text-sm text-red-700">
                    Gagal memuat data pendaftaran.
                    <button type="button" class="ml-2 font-medium underline" @click="loadRegistration">Coba lagi</button>
                </div>

                <router-view v-else :registration="registration" @refresh="loadRegistration" />
            </main>
        </div>
    </div>
</template>

<script>
import Repository from '../../repository';
import { Icon } from '../../icons';

export default {
    name: 'RegistrationIndex',
    components: { Icon },
    data() {
        return {
            registration: null,
            loading: false,
            loadError: false,
        };
    },
    created() {
        this.loadRegistration();
    },
    computed: {
        isHome() {
            return this.$route.path.replace(/\/$/, '') === '/reg/index';
        },
        isLocked() {
            return Boolean(this.registration) && this.registration.status !== 100;
        },
        requiredItems() {
            const reg = this.registration || {};
            return [
                { label: 'Profile', to: '/reg/index/account', badgeType: 'boolean', badge: Boolean(reg.phone && reg.image_uri) },
                { label: 'Identitas', to: '/reg/index/identity', badgeType: 'boolean', badge: Boolean(reg.nik && reg.gender && reg.birth_date && reg.birth_place && reg.origin_address) },
                { label: 'Pendaftaran', to: '/reg/index/reg', badgeType: 'boolean', badge: Boolean(reg.test_order && reg.selection_path && reg.working_status) },
                { label: 'Riwayat Pendidikan', to: '/reg/index/education-bg', badgeType: 'boolean', badge: Boolean(reg.origin_university && reg.origin_university_accreditation && reg.ip_s1 && reg.ip_profession && reg.ip_commulative) },
                { label: 'Keluarga', to: '/reg/index/family', badgeType: 'boolean', badge: Boolean(reg.father_name && reg.mother_name) },
            ];
        },
        completedCount() {
            return this.requiredItems.filter((item) => item.badge).length;
        },
        progressPercent() {
            return Math.round((this.completedCount / this.requiredItems.length) * 100);
        },
        menuGroups() {
            const reg = this.registration || {};
            const details = Array.isArray(reg.details) ? reg.details : [];
            const countByLabel = (label) => details.filter((item) => item && item.label === label).length;

            return [
                { title: '', items: [{ label: 'Beranda', to: '/reg/index' }] },
                {
                    title: 'Data Utama',
                    items: [...this.requiredItems, { label: 'Institusi Asal', to: '/reg/index/institution' }],
                },
                {
                    title: 'Riwayat & Pendukung',
                    items: [
                        { label: 'Pendidikan', to: '/reg/index/educations', badgeType: 'number', badge: countByLabel('education') },
                        { label: 'Pekerjaan', to: '/reg/index/jobs', badgeType: 'number', badge: countByLabel('job') },
                        { label: 'Agenda Ilmiah', to: '/reg/index/scientifics', badgeType: 'number', badge: countByLabel('scientific') },
                        { label: 'Organisasi', to: '/reg/index/organisations', badgeType: 'number', badge: countByLabel('organisation') },
                        { label: 'Interenship', to: '/reg/index/interenships', badgeType: 'number', badge: countByLabel('interenship') },
                        { label: 'Rekomendasi', to: '/reg/index/recomendations', badgeType: 'number', badge: countByLabel('recommendation') },
                        { label: 'Prestasi', to: '/reg/index/achievements', badgeType: 'number', badge: countByLabel('achievement') },
                        { label: 'Nilai Pendukung', to: '/reg/index/scores', badgeType: 'number', badge: countByLabel('score') },
                        { label: 'Log Book', to: '/reg/index/logbook', badgeType: 'number', badge: countByLabel('logbook') },
                    ],
                },
            ];
        },
        flatMenu() {
            return this.menuGroups.reduce((all, group) => all.concat(group.items), []);
        },
    },
    methods: {
        loadRegistration() {
            this.loading = true;
            this.loadError = false;
            return Repository.get('/api/registration')
                .then((response) => {
                    const data = response && response.data ? response.data : {};
                    this.registration = data.result || null;
                })
                .catch(() => {
                    this.loadError = true;
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
};
</script>

<style>
.reg-page input:disabled,
.reg-page select:disabled,
.reg-page textarea:disabled {
    background-color: #f7f8fc;
    color: #6b7280;
    cursor: not-allowed;
}
</style>
