<template>
    <div class="min-h-screen bg-surface text-ink font-sans flex flex-col lg:flex-row">
        <Sidebar v-if="showSidebar" :base-path="basePath" :collapsed="collapsed" :is-mobile="isMobile" />
        <div class="flex flex-1 flex-col">
            <Topbar
                :title="title"
                :subtitle="subtitle"
                :collapsed="collapsed"
                :show-toggle="!isStudent"
                @toggle-sidebar="toggleSidebar"
            />
            <main
                class="flex flex-col gap-7"
                :class="contentPaddingClass"
            >
                <router-view />
            </main>
        </div>
        <BottomNav v-if="hasBottomNav" :base-path="basePath" :auth-type="authType" />
    </div>
</template>

<script>
import Sidebar from '../components/Sidebar.vue';
import Topbar from '../components/Topbar.vue';
import BottomNav from '../components/BottomNav.vue';
import Repository from '../repository';

export default {
    components: {
        Sidebar,
        Topbar,
        BottomNav,
    },
    data() {
        return {
            basePath: '/cblu',
            title: 'Dashboard',
            subtitle: 'Overview for this week',
            collapsed: false,
            isMobile: false,
            authType: localStorage.getItem('auth_type') || '',
        };
    },
    mounted() {
        this.handleResize();
        window.addEventListener('resize', this.handleResize);
        this.fetchAuthType();
    },
    beforeDestroy() {
        window.removeEventListener('resize', this.handleResize);
    },
    computed: {
        isStudent() {
            return this.authType === 'student';
        },
        isLecture() {
            return this.authType === 'lecture';
        },
        showSidebar() {
            return !this.isStudent && !this.isLecture;
        },
        hasBottomNav() {
            return this.authType === 'student' || this.authType === 'lecture';
        },
        contentPaddingClass() {
            const base = this.isStudent ? 'px-5 pt-5' : 'px-9 pt-7';
            const bottom = this.hasBottomNav ? 'pb-24' : 'pb-10';
            return `${base} ${bottom}`;
        },
    },
    methods: {
        handleResize() {
            const wasMobile = this.isMobile;
            const isMobile = window.innerWidth < 1024;
            this.isMobile = isMobile;
            if (isMobile) {
                this.collapsed = true;
            } else if (wasMobile) {
                this.collapsed = false;
            }
        },
        toggleSidebar() {
            this.collapsed = !this.collapsed;
        },
        fetchAuthType() {
            return Repository.get('/api/auth')
                .then((response) => {
                    const payload = response && response.data ? response.data.result : null;
                    const authType = payload ? payload.log_as_auth_type || payload.auth_type : '';
                    this.authType = authType || '';
                    if (this.authType) {
                        localStorage.setItem('auth_type', this.authType);
                    }
                })
                .catch(() => {
                    this.authType = this.authType || '';
                });
        },
    },
};
</script>
