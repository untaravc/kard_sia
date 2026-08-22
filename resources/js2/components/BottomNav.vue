<template>
    <nav
        class="fixed bottom-0 left-0 right-0 z-40 border-t border-border bg-panel/95 px-2 pt-1.5 backdrop-blur pb-[calc(0.375rem+env(safe-area-inset-bottom))]"
    >
        <div class="mx-auto flex w-full max-w-md items-stretch justify-between">
            <router-link
                v-for="item in displayItems"
                :key="item.to"
                :to="item.to"
                class="flex min-w-0 flex-1 flex-col items-center justify-start gap-1 rounded-xl px-1 py-1.5 text-[10px] font-semibold transition-colors"
                :class="isActive(item) ? 'text-primary' : 'text-muted'"
                :aria-current="isActive(item) ? 'page' : null"
            >
                <span
                    class="relative grid h-9 w-9 place-items-center rounded-xl transition-colors"
                    :class="isActive(item) ? 'bg-primary text-white shadow-sm' : 'bg-slate-100 text-primary/80'"
                >
                    <Icon :icon="resolveIcon(item.icon)" class="h-[18px] w-[18px]" />
                    <span
                        v-if="badgeCount(item) > 0"
                        class="absolute -right-1 -top-1 min-w-[18px] rounded-full bg-rose-500 px-1 text-[9px] font-semibold leading-4 text-white ring-2 ring-panel"
                    >
                        {{ badgeCount(item) > 99 ? '99+' : badgeCount(item) }}
                    </span>
                </span>
                <span class="w-full truncate text-center leading-tight">{{ item.label }}</span>
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
    },
    data() {
        return {
            menuItems: [],
        };
    },
    computed: {
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
        /**
         * The API menu is authoritative: it already reflects what each role
         * may see, so the defaults are a fallback for an empty/failed fetch
         * only. Topping a short menu up to five would refill a deliberately
         * hidden tab with an unrelated (student) one.
         */
        displayItems() {
            const merged = this.normalizedMenu.length ? this.normalizedMenu : this.defaultItems;
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
        /**
         * Active state is derived here rather than via router-link's
         * active-class so the icon chip (not just the label) can respond,
         * while still keeping nested routes — e.g. scoring/:id — lit up
         * under their parent tab.
         */
        isActive(item) {
            if (!item || !item.to) {
                return false;
            }
            const path = this.$route.path.replace(/\/$/, '');
            const target = item.to.replace(/\/$/, '');
            return path === target || path.startsWith(`${target}/`);
        },
        badgeCount(item) {
            if (item && Number.isFinite(item.counter)) {
                return item.counter;
            }
            return 0;
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
