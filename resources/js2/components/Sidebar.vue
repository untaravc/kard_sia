<template>
    <aside class="revamp-sidebar">
        <div class="brand">
            <span class="brand-mark">K</span>
            <div class="brand-text">
                <div class="brand-title">Kardio</div>
                <div class="brand-sub">Admin Suite</div>
            </div>
        </div>
        <nav class="nav">
            <div v-for="item in menuItems" :key="item.label">
                <router-link v-if="!item.children" class="nav-item" :to="item.to">
                    <span class="nav-icon">{{ item.icon }}</span>
                    <span class="nav-label">{{ item.label }}</span>
                </router-link>
                <div v-else class="nav-group">
                    <button
                        class="nav-item nav-group-title"
                        type="button"
                        @click="toggleGroup(item.label)"
                    >
                        <span class="nav-icon">{{ item.icon }}</span>
                        <span class="nav-label">{{ item.label }}</span>
                        <span class="nav-caret" :class="{ open: isOpen(item.label) }"></span>
                    </button>
                    <div class="nav-sub" v-show="isOpen(item.label)">
                        <router-link
                            v-for="child in item.children"
                            :key="child.label"
                            class="nav-item nav-sub-item"
                            :to="child.to"
                        >
                            {{ child.label }}
                        </router-link>
                    </div>
                </div>
            </div>
        </nav>
        <div class="sidebar-footer">
            <div class="status-pill">System Healthy</div>
            <div class="status-caption">Last sync 2m ago</div>
        </div>
    </aside>
</template>

<script>
import Repository from '../repository';

export default {
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
        };
    },
    created() {
        this.fetchMenu();
    },
    methods: {
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
