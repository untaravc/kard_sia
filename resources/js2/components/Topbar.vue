<template>
    <header class="flex items-center justify-between border-b border-border bg-panel px-9 py-3">
        <button
            class="grid h-9 w-9 place-items-center rounded-xl border border-border text-ink transition hover:bg-surface/70"
            type="button"
            @click="$emit('toggle-sidebar')"
            title="Toggle sidebar"
        >
            <Icon :icon="collapsed ? 'mdi:menu-open' : 'mdi:menu'" class="h-5 w-5" />
        </button>
        <div class="flex items-center gap-2.5">
            <button
                v-if="user && user.log_as_auth_type"
                class="rounded-xl border border-amber-300/60 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700"
                type="button"
                @click="logoutAs"
            >
                Logout As
            </button>
            <div class="grid h-8 w-8 place-items-center rounded-full bg-accent text-sm font-semibold text-ink">
                {{ initials }}
            </div>
            <div class="leading-tight">
                <div class="text-sm font-semibold">{{ displayName }}</div>
                <div class="text-xs text-muted">{{ displaySubtitle }}</div>
            </div>
        </div>
    </header>
</template>

<script>
import { Icon } from '../icons';
import Repository from '../repository';
import { useFirebaseConfigStore } from '../stores/firebaseConfig';

export default {
    components: {
        Icon,
    },
    props: {
        collapsed: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            user: null,
            firebaseConfigStore: null,
        };
    },
    computed: {
        displayName() {
            return this.user && this.user.name ? this.user.name : 'User';
        },
        authType() {
            if (this.user && this.user.log_as_auth_type) {
                return this.user.log_as_auth_type;
            }
            return this.user && this.user.auth_type ? this.user.auth_type : '';
        },
        displaySubtitle() {
            if (this.user && this.user.email) {
                return this.user.email;
            }
            if (this.user && this.user.auth_type) {
                return this.user.auth_type;
            }
            return 'Loading...';
        },
        initials() {
            const source = this.displayName || '';
            const parts = source.trim().split(/\s+/).filter(Boolean);
            if (parts.length === 0) {
                return 'U';
            }
            if (parts.length === 1) {
                return parts[0].slice(0, 2).toUpperCase();
            }
            return (parts[0][0] + parts[1][0]).toUpperCase();
        },
    },
    mounted() {
        this.firebaseConfigStore = useFirebaseConfigStore();
        this.firebaseConfigStore.fetchConfig();
        this.fetchUser();
    },
    methods: {
        fetchUser() {
            return Repository.get('/api/auth')
                .then((response) => {
                    const payload = response && response.data ? response.data.result : null;
                    this.user = payload || null;
                    this.handleAuthRedirect();
                })
                .catch(() => {
                    this.user = null;
                });
        },
        handleAuthRedirect() {
            const authType = this.authType;
            if (!authType) {
                return;
            }

            const path = window.location.pathname || '';
            const isDashboard = path === '/dashboard' || path.endsWith('/dashboard');
            if (!isDashboard) {
                return;
            }

            if (authType === 'lecture') {
                window.location.href = path.replace(/\/dashboard$/, '/dashboard-lecture');
                return;
            }

            if (authType === 'student') {
                window.location.href = path.replace(/\/dashboard$/, '/dashboard-student');
            }
        },
        logoutAs() {
            return Repository.post('/api/logout-as')
                .then((response) => {
                    const token = response && response.data && response.data.result
                        ? response.data.result.token
                        : null;
                    if (!token) {
                        return;
                    }
                    localStorage.setItem('token', token);
                    window.location.href = '/cblu/dashboard';
                })
                .catch(() => {});
        },
    },
};
</script>
