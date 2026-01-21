<template>
    <div class="revamp-shell">
        <Sidebar :base-path="basePath" />
        <div class="revamp-main">
            <Topbar :title="title" :subtitle="subtitle" />
            <main class="content">
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
