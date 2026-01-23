<template>
    <aside
        class="fixed left-0 top-0 z-50 flex h-full w-72 flex-col border-r border-slate-200 bg-white shadow-sm transition-transform lg:translate-x-0"
        :class="open ? 'translate-x-0' : '-translate-x-full'"
    >
        <div class="flex h-16 items-center justify-between border-b border-slate-200 px-6">
            <div class="flex items-center gap-2 text-lg font-semibold">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-900 text-white">A</span>
                <span>Admin Panel</span>
            </div>
            <button
                class="rounded-lg border border-slate-200 p-2 text-slate-600 lg:hidden"
                type="button"
                aria-label="Close sidebar"
                @click="$emit('close')"
            >
                <VIcon name="fa-times" class="h-4 w-4" />
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-6">
            <p class="px-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Main</p>
            <nav class="mt-4 text-sm font-medium">
                <div v-if="loading" class="px-3 py-2 text-xs text-slate-400">Loading menu...</div>
                <ul class="space-y-1">
                    <li v-for="item in menus" :key="item.id">
                        <RouterLink
                            v-if="item.route && (!item.children || item.children.length === 0)"
                            class="flex items-center gap-3 rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100"
                            :class="normalizeRoute(item.route) === currentRoute ? 'bg-primary text-white hover:bg-primary/90' : ''"
                            :to="normalizeRoute(item.route)"
                        >
                            <VIcon v-if="item.icon" :name="item.icon" class="h-4 w-4" />
                            <span>{{ item.name }}</span>
                        </RouterLink>

                        <div v-else>
                            <button
                                class="flex w-full items-center justify-between px-3 py-2 text-slate-700"
                                type="button"
                                @click="toggleSection(item.id)"
                            >
                                <span class="flex items-center gap-3">
                                    <VIcon v-if="item.icon" :name="item.icon" class="h-4 w-4" />
                                    <span>{{ item.name }}</span>
                                </span>
                                <VIcon
                                    :name="isOpen(item.id) ? 'fa-chevron-up' : 'fa-chevron-down'"
                                    class="h-3 w-3 text-slate-500"
                                />
                            </button>
                            <ul v-show="isOpen(item.id)" class="space-y-1 px-3 py-2 text-slate-600">
                                <li v-for="child in item.children" :key="child.id">
                                    <RouterLink
                                        v-if="child.route"
                                        class="block rounded-md px-2 py-1 hover:bg-slate-100"
                                        :class="normalizeRoute(child.route) === currentRoute ? 'bg-slate-100 font-semibold text-slate-900' : ''"
                                        :to="normalizeRoute(child.route)"
                                    >
                                        {{ child.name }}
                                    </RouterLink>
                                    <span v-else class="block rounded-md px-2 py-1 text-slate-500">{{ child.name }}</span>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </nav>
        </div>

        <div class="border-t border-slate-200 px-6 py-4 text-xs text-slate-500">
            Signed in as <span class="font-semibold text-slate-700">admin@example.com</span>
        </div>
    </aside>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { apiGet } from '../../fetch_api.js';

defineProps({
    open: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['close']);

const menus = ref([]);
const loading = ref(false);
const openSections = ref({});

const route = useRoute();
const currentRoute = computed(() => route.path);

const normalizeRoute = (path) => {
    if (!path) {
        return '';
    }
    if (path === '/bo') {
        return '/';
    }
    if (path.startsWith('/bo/')) {
        return path.replace('/bo', '');
    }
    return path;
};

const isOpen = (id) => {
    return Boolean(openSections.value[id]);
};

const toggleSection = (id) => {
    openSections.value = {
        ...openSections.value,
        [id]: !openSections.value[id],
    };
};

const fetchMenu = async () => {
    loading.value = true;
    try {
        const data = await apiGet('/api/menu');
        menus.value = data?.result ?? data ?? [];
        const defaults = {};
        menus.value.forEach((item) => {
            if (item.children && item.children.length) {
                defaults[item.id] = true;
            }
        });
        openSections.value = defaults;
    } catch (error) {
        menus.value = [];
    } finally {
        loading.value = false;
    }
};

onMounted(fetchMenu);
</script>
