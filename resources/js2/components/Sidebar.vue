<template>
    <aside
        class="flex w-full flex-col gap-8 bg-[radial-gradient(circle_at_top,_theme(colors.sidebar-accent)_0%,_theme(colors.sidebar)_65%)] px-2.5 py-4 text-sidebar-text lg:shrink-0"
        :class="collapsed ? 'lg:w-20' : 'lg:w-64'"
    >
        <div class="flex items-center gap-3">
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-accent text-lg font-bold text-ink">K</span>
            <div v-if="!collapsed" class="leading-tight">
                <div class="font-semibold tracking-wide">Kardio</div>
                <div class="text-xs text-sidebar-text/70">Admin Suite</div>
            </div>
            <button
                class="ml-auto rounded-lg border border-sidebar-text/20 px-2 py-1 text-xs text-sidebar-text/80 hover:text-sidebar-text"
                type="button"
                @click="toggleCollapsed"
            >
                {{ collapsed ? 'Expand' : 'Collapse' }}
            </button>
        </div>
        <nav class="flex flex-col gap-2.5" v-show="!isMobile || !collapsed">
            <div v-for="item in menuItems" :key="item.label">
                <router-link
                    v-if="!item.children"
                    class="flex w-full items-center gap-2.5 rounded-xl bg-transparent text-sm text-sidebar-text no-underline transition-colors hover:bg-sidebar-accent"
                    :class="collapsed ? 'justify-center px-2.5 py-2.5' : 'px-3.5 py-2.5'"
                    :to="item.to"
                    active-class="bg-sidebar-accent"
                    exact-active-class="bg-sidebar-accent"
                >
                    <span class="grid h-6 w-6 place-items-center rounded-lg bg-white/10 text-sm">
                        <Icon v-if="item.icon" :icon="resolveIcon(item.icon)" class="h-4 w-4" />
                    </span>
                    <span v-if="!collapsed" class="flex-1">{{ item.label }}</span>
                </router-link>
                <div v-else class="flex flex-col gap-1.5">
                    <button
                        class="flex w-full cursor-pointer items-center gap-2.5 rounded-xl border-0 bg-transparent text-left text-sm text-sidebar-text transition-colors hover:bg-sidebar-accent"
                        :class="collapsed ? 'justify-center px-2.5 py-2.5' : 'px-3.5 py-2.5'"
                        type="button"
                        @click="toggleGroup(item.label)"
                    >
                        <span class="grid h-6 w-6 place-items-center rounded-lg bg-white/10 text-sm">
                            <Icon v-if="item.icon" :icon="resolveIcon(item.icon)" class="h-4 w-4" />
                        </span>
                        <span v-if="!collapsed" class="flex-1">{{ item.label }}</span>
                        <span
                            v-if="!collapsed"
                            class="ml-auto h-2 w-2 border-b-2 border-r-2 border-sidebar-text/70 transition-transform duration-200"
                            :class="isOpen(item.label) ? 'rotate-45' : '-rotate-45'"
                        ></span>
                    </button>
                    <div class="grid gap-1.5 pl-2.5" v-show="!collapsed && isOpen(item.label)">
                        <router-link
                            v-for="child in item.children"
                            :key="child.label"
                            class="flex w-full items-center gap-2.5 rounded-xl bg-white/5 px-3.5 py-2.5 text-sm text-sidebar-text no-underline transition-colors hover:bg-white/15"
                            :to="child.to"
                            active-class="bg-white/15"
                            exact-active-class="bg-white/15"
                        >
                            {{ child.label }}
                        </router-link>
                    </div>
                </div>
            </div>
        </nav>
        <div class="mt-auto max-lg:hidden" v-if="!collapsed">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-xs">System Healthy</div>
            <div class="mt-2 text-xs text-sidebar-text/60">Last sync 2m ago</div>
        </div>
    </aside>
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
            default: '/cblu',
        },
    },
    data() {
        return {
            openGroups: {},
            menuItems: [],
            collapsed: false,
            isMobile: false,
        };
    },
    created() {
        this.fetchMenu();
    },
    mounted() {
        this.handleResize();
        window.addEventListener('resize', this.handleResize);
    },
    beforeDestroy() {
        window.removeEventListener('resize', this.handleResize);
    },
    methods: {
        resolveIcon(iconKey) {
            return ICONS[iconKey] || iconKey || '';
        },
        handleResize() {
            const isMobile = window.innerWidth < 1024;
            this.isMobile = isMobile;
            this.collapsed = isMobile;
        },
        toggleCollapsed() {
            this.collapsed = !this.collapsed;
        },
        fetchMenu() {
            return Repository.get('/api/menu', {
                params: { basePath: this.basePath },
            })
                .then((response) => {
                    const menu = response && response.data && response.data.data
                        ? response.data.data.menu
                        : [];
                    this.menuItems = Array.isArray(menu) ? menu : [];
                })
                .catch(() => {
                    this.menuItems = [];
                });
        },
        toggleGroup(label) {
            this.$set(this.openGroups, label, !this.isOpen(label));
        },
        isOpen(label) {
            return !!this.openGroups[label];
        },
    },
};
</script>
