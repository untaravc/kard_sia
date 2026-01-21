<template>
    <div class="min-h-screen bg-surface text-ink font-sans flex flex-col lg:flex-row">
        <Sidebar :base-path="basePath" />
        <div class="flex flex-1 flex-col">
            <Topbar :title="title" :subtitle="subtitle" />
            <main class="flex flex-col gap-7 px-9 pt-7 pb-10">
                <router-view />
            </main>
        </div>
    </div>
</template>

<script>
import Sidebar from '../components/Sidebar.vue';
import Topbar from '../components/Topbar.vue';

export default {
    components: {
        Sidebar,
        Topbar,
    },
    data() {
        return {
            basePath: '/cblu',
            title: 'Dashboard',
            subtitle: 'Overview for this week',
        };
    },
};
</script>
