<template>
    <header
        class="flex items-center border-b border-border bg-panel px-6 py-3"
        :class="isLayoutNonUser ? 'justify-center' : 'justify-between'"
    >
        <button
            v-if="showToggle && !isLayoutNonUser"
            class="grid h-9 w-9 place-items-center rounded-xl border border-border text-ink transition hover:bg-surface/70"
            type="button"
            @click="$emit('toggle-sidebar')"
            title="Toggle sidebar"
        >
            <Icon :icon="collapsed ? 'mdi:menu-open' : 'mdi:menu'" class="h-5 w-5" />
        </button>
        <div
            class="flex w-full max-w-md items-center justify-between gap-2.5"
            :class="isLayoutNonUser ? '' : 'max-w-none justify-end'"
        >
            <button
                v-if="showToggle && isLayoutNonUser"
                class="grid h-9 w-9 place-items-center rounded-xl border border-border text-ink transition hover:bg-surface/70"
                type="button"
                @click="$emit('toggle-sidebar')"
                title="Toggle sidebar"
            >
                <Icon :icon="collapsed ? 'mdi:menu-open' : 'mdi:menu'" class="h-5 w-5" />
            </button>
            <button
                v-if="showLogoutAs"
                class="rounded-xl border border-amber-300/60 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700"
                type="button"
                @click="logoutAs"
            >
                Logout As
            </button>
            <div v-if="showNotificationBell" class="relative">
                <button
                    class="relative grid h-8 w-8 place-items-center rounded-full border border-border text-ink transition hover:bg-surface/70"
                    type="button"
                    @click.stop="toggleBell"
                    aria-label="Notifications"
                >
                    <Icon icon="mdi:bell-outline" class="h-4 w-4" />
                    <span
                        v-if="unreadCount > 0"
                        class="absolute -right-1 -top-1 grid h-4 min-w-[16px] place-items-center rounded-full bg-red-500 px-1 text-[10px] font-semibold text-white"
                    >
                        {{ unreadCount > 9 ? '9+' : unreadCount }}
                    </span>
                </button>
                <div
                    v-if="bellOpen"
                    class="absolute right-0 z-10 mt-2 w-80 rounded-xl border border-border bg-white shadow-md"
                >
                    <div class="flex items-center justify-between border-b border-border px-3 py-2">
                        <span class="text-xs font-semibold text-ink">Notifications</span>
                        <button
                            v-if="unreadCount > 0"
                            type="button"
                            class="text-[11px] font-medium text-primary hover:underline"
                            @click="markAllRead"
                        >
                            Mark all as read
                        </button>
                    </div>
                    <div class="max-h-80 overflow-y-auto divide-y divide-border">
                        <div v-if="notifications.length === 0" class="px-3 py-6 text-center text-xs text-muted">
                            No notifications yet.
                        </div>
                        <button
                            v-for="notification in notifications"
                            :key="notification.id"
                            type="button"
                            class="block w-full px-3 py-2.5 text-left hover:bg-slate-50"
                            :class="notification.is_read ? '' : 'bg-primary/5'"
                            @click="handleNotificationClick(notification)"
                        >
                            <div class="flex items-start gap-2">
                                <span
                                    class="mt-1 h-1.5 w-1.5 flex-shrink-0 rounded-full"
                                    :class="notification.is_read ? 'bg-transparent' : 'bg-primary'"
                                ></span>
                                <div class="min-w-0">
                                    <div class="truncate text-xs font-semibold text-ink">{{ notification.title }}</div>
                                    <div class="line-clamp-2 text-xs text-muted">{{ notification.content }}</div>
                                    <div class="mt-1 text-[10px] text-muted">{{ formatNotificationTime(notification.created_at) }}</div>
                                </div>
                            </div>
                        </button>
                    </div>
                    <div class="border-t border-border px-3 py-2 text-center">
                        <router-link
                            to="/blu/notifications"
                            class="text-[11px] font-medium text-primary hover:underline"
                            @click.native="closeBell"
                        >
                            View all notifications
                        </router-link>
                    </div>
                </div>
            </div>
            <div class="flex min-w-0 flex-1 items-center gap-2.5">
                <div class="grid h-8 w-8 place-items-center rounded-full bg-accent text-sm font-semibold text-ink">
                    {{ initials }}
                </div>
                <div class="min-w-0 leading-tight">
                    <div class="truncate text-sm font-semibold">{{ displayName }}</div>
                    <div class="truncate text-xs text-muted">{{ displaySubtitle }}</div>
                </div>
            </div>
            <div class="relative">
                <button
                    v-if="showMenuButton"
                    class="grid h-8 w-8 place-items-center rounded-full border border-border text-ink transition hover:bg-surface/70"
                    type="button"
                    @click.stop="toggleMenu"
                    aria-label="User menu"
                >
                    <Icon icon="mdi:dots-vertical" class="h-4 w-4" />
                </button>
                <!-- z-30 clears the sticky z-10 page headers that render after
                     this element in the DOM and would otherwise cover it. -->
                <div
                    v-if="menuOpen"
                    class="absolute right-0 z-30 mt-2 w-40 rounded-xl border border-border bg-white shadow-md"
                >
                    <button
                        type="button"
                        class="block w-full px-3 py-2.5 text-left text-xs text-ink hover:bg-slate-50"
                        @click="logout"
                    >
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </header>
</template>

<script>
import { Icon } from '../icons';
import Repository from '../repository';
import { useFirebaseConfigStore } from '../stores/firebaseConfig';
import { useAuthStore } from '../stores/auth';
import { useWebNotificationsStore } from '../stores/webNotifications';

export default {
    components: {
        Icon,
    },
    props: {
        collapsed: {
            type: Boolean,
            default: false,
        },
        showToggle: {
            type: Boolean,
            default: true,
        },
    },
    data() {
        return {
            user: null,
            firebaseConfigStore: null,
            authStore: null,
            notificationsStore: null,
            menuOpen: false,
            bellOpen: false,
        };
    },
    computed: {
        showNotificationBell() {
            return this.authType === 'lecture';
        },
        notifications() {
            return this.notificationsStore ? this.notificationsStore.items : [];
        },
        unreadCount() {
            return this.notificationsStore ? this.notificationsStore.unreadCount : 0;
        },
        displayName() {
            return this.user && this.user.name ? this.user.name : 'User';
        },
        authType() {
            if (this.user && this.user.log_as_auth_type) {
                return this.user.log_as_auth_type;
            }
            return this.user && this.user.auth_type ? this.user.auth_type : '';
        },
        isNonUser() {
            return this.authType && this.authType !== 'user';
        },
        isLayoutNonUser() {
            const type = this.user && this.user.auth_type ? this.user.auth_type : '';
            return type && type !== 'user';
        },
        showLogoutAs() {
            return !!(this.user && this.user.log_as_auth_type && this.user.auth_type === 'user');
        },
        // Every signed-in role needs the menu, not just student/lecture: admins
        // have no bottom nav and the sidebar's logout is desktop-only, so
        // excluding them here left them with no way to log out on mobile.
        showMenuButton() {
            const type = this.user && this.user.auth_type ? this.user.auth_type : '';
            return Boolean(type);
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
        this.authStore = useAuthStore();
        this.notificationsStore = useWebNotificationsStore();
        this.firebaseConfigStore.fetchConfig();
        this.fetchUser();
        document.addEventListener('click', this.closeMenus);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.closeMenus);
    },
    methods: {
        fetchUser() {
            return Repository.get('/api/auth')
                .then((response) => {
                    const payload = response && response.data ? response.data.result : null;
                    this.user = payload || null;
                    if (this.authStore) {
                        this.authStore.setUser(this.user);
                    }
                    this.handleAuthRedirect();
                    if (this.showNotificationBell && this.notificationsStore) {
                        this.notificationsStore.fetchList();
                    }
                })
                .catch(() => {
                    this.user = null;
                    if (this.authStore) {
                        this.authStore.clearUser();
                    }
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
                    if (this.authStore) {
                        this.authStore.clearUser();
                    }
                    window.location.href = '/blu/dashboard';
                })
                .catch(() => {});
        },
        toggleMenu() {
            this.menuOpen = !this.menuOpen;
            this.bellOpen = false;
        },
        closeMenus() {
            this.menuOpen = false;
            this.bellOpen = false;
        },
        toggleBell() {
            this.bellOpen = !this.bellOpen;
            this.menuOpen = false;
            if (this.bellOpen && this.notificationsStore) {
                this.notificationsStore.fetchList();
            }
        },
        closeBell() {
            this.bellOpen = false;
        },
        markAllRead() {
            if (this.notificationsStore) {
                this.notificationsStore.markAllRead();
            }
        },
        handleNotificationClick(notification) {
            if (this.notificationsStore && !notification.is_read) {
                this.notificationsStore.markRead(notification.id);
            }
            this.bellOpen = false;
            if (notification.link) {
                window.location.href = notification.link;
            }
        },
        formatNotificationTime(value) {
            if (!value) {
                return '';
            }
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return '';
            }

            const diffMs = Date.now() - date.getTime();
            const diffMin = Math.floor(diffMs / 60000);
            if (diffMin < 1) {
                return 'Just now';
            }
            if (diffMin < 60) {
                return `${diffMin}m ago`;
            }
            const diffHour = Math.floor(diffMin / 60);
            if (diffHour < 24) {
                return `${diffHour}h ago`;
            }
            const diffDay = Math.floor(diffHour / 24);
            if (diffDay < 7) {
                return `${diffDay}d ago`;
            }
            return date.toLocaleDateString();
        },
        logout() {
            this.menuOpen = false;
            return Repository.post('/api/logout')
                .then(() => {
                    localStorage.removeItem('token');
                    if (this.authStore) {
                        this.authStore.clearUser();
                    }
                    window.location.href = '/blu/login';
                })
                .catch(() => {
                    localStorage.removeItem('token');
                    if (this.authStore) {
                        this.authStore.clearUser();
                    }
                    window.location.href = '/blu/login';
                });
        },
    },
};
</script>
