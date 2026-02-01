<template>
    <nav class="fixed bottom-0 left-0 right-0 z-40 border-t border-border bg-panel/95 px-4 py-2 backdrop-blur">
        <div class="mx-auto flex w-full max-w-md items-center justify-between">
            <router-link
                v-for="item in displayItems"
                :key="item.to"
                :to="item.to"
                class="flex min-w-0 flex-1 flex-col items-center gap-1 rounded-xl px-2 py-1 text-[10px] font-semibold text-muted transition-colors"
                active-class="text-primary"
                exact-active-class="text-primary"
            >
                <span class="grid h-8 w-8 place-items-center rounded-xl bg-slate-100 text-primary/80">
                    <Icon :icon="resolveIcon(item.icon)" class="h-4 w-4" />
                </span>
                <span class="truncate">{{ item.label }}</span>
            </router-link>
        </div>
    </nav>
</template>

<script>
import Repository from '../repository';
import { Icon, ICONS } from '../icons';

export default {
    components: {
        Icon,
    },
    props: {
        basePath: {
            type: String,
            default: '/blu',
        },
        authType: {
            type: String,
            default: '',
        },
    },
    data() {
        return {
            menuItems: [],
        };
    },
    computed: {
        studentItems() {
            const base = `${this.basePath}/dashboard-student`;
            return [
                { label: 'Scoring', icon: 'mdi:clipboard-check-outline', to: `${base}/scoring` },
                { label: 'Agenda', icon: 'mdi:calendar-month-outline', to: `${base}/agenda` },
                { label: 'Logbooks', icon: 'mdi:notebook-outline', to: `${this.basePath}/logbooks` },
                { label: 'Accreditations', icon: 'mdi:certificate-outline', to: `${this.basePath}/accreditations` },
                { label: 'Profile', icon: 'mdi:account-outline', to: `${base}/profile` },
            ];
        },
        lectureItems() {
            const base = `${this.basePath}/dashboard-lecture`;
            return [
                { label: 'Scoring', icon: 'mdi:clipboard-check-outline', to: `${base}/scoring` },
                { label: 'Agenda', icon: 'mdi:calendar-month-outline', to: `${base}/agenda` },
                { label: 'Report', icon: 'mdi:file-chart-outline', to: `${base}/report` },
                { label: 'Accreditations', icon: 'mdi:certificate-outline', to: `${this.basePath}/accreditations` },
                { label: 'Profile', icon: 'mdi:account-outline', to: `${base}/profile` },
            ];
        },
        roleItems() {
            if (this.authType === 'student') {
                return this.studentItems;
            }
            if (this.authType === 'lecture') {
                return this.lectureItems;
            }
            return [];
        },
        normalizedMenu() {
            const flattened = [];
            this.menuItems.forEach((item) => {
                if (item.children && item.children.length) {
                    item.children.forEach((child) => {
                        flattened.push({
                            ...child,
                            icon: child.icon || item.icon,
                        });
                    });
                } else {
                    flattened.push(item);
                }
            });
            return flattened;
        },
        defaultItems() {
            return [
                { label: 'Dashboard', icon: 'dashboard', to: `${this.basePath}/dashboard-student` },
                { label: 'Activities', icon: 'agenda', to: `${this.basePath}/activities` },
                { label: 'Presences', icon: 'resident', to: `${this.basePath}/presences` },
                { label: 'Daily', icon: 'agenda', to: `${this.basePath}/presences/daily` },
                { label: 'Monthly', icon: 'agenda', to: `${this.basePath}/presences/monthly` },
            ];
        },
        displayItems() {
            if (this.roleItems.length) {
                return this.roleItems;
            }
            const merged = [...this.normalizedMenu];
            if (merged.length < 5) {
                this.defaultItems.forEach((item) => {
                    if (!merged.find((existing) => existing.to === item.to)) {
                        merged.push(item);
                    }
                });
            }
            return merged.slice(0, 5);
        },
    },
    created() {
        this.fetchMenu();
    },
    methods: {
        resolveIcon(iconKey) {
            return ICONS[iconKey] || iconKey || 'mdi:circle';
        },
        fetchMenu() {
            return Repository.get('/api/menu', {
                params: { basePath: this.basePath },
            })
                .then((response) => {
                    const menu = response && response.data && response.data.result
                        ? response.data.result.menu
                        : [];
                    this.menuItems = Array.isArray(menu) ? menu : [];
                })
                .catch(() => {
                    this.menuItems = [];
                });
        },
    },
};
</script>
