<template>
    <div class="min-h-screen bg-surface text-ink">
        <header class="border-b border-border bg-panel">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
                <div class="min-w-0">
                    <div class="text-sm font-semibold tracking-wide text-primary">Pendaftaran PPDS</div>
                    <div class="truncate text-xs text-muted">Program Studi Jantung dan Pembuluh Darah</div>
                </div>
                <div v-if="isLoggedIn" class="flex min-w-0 items-center gap-3 text-sm">
                    <span v-if="userName" class="hidden truncate text-muted sm:inline">{{ userName }}</span>
                    <button type="button"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-border px-3 py-1.5 text-muted hover:bg-surface hover:text-ink"
                        @click="logout">
                        <Icon icon="mdi:logout" class="h-4 w-4" />
                        <span>Keluar</span>
                    </button>
                </div>
            </div>
        </header>
        <router-view />
    </div>
</template>

<script>
import Repository from '../../repository';
import { Icon } from '../../icons';

function readTokenName() {
    const token = localStorage.getItem('token');
    if (!token) return '';
    try {
        const payload = token.split('.')[1].replace(/-/g, '+').replace(/_/g, '/');
        const json = decodeURIComponent(atob(payload).split('').map((c) => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)).join(''));
        return JSON.parse(json).name || '';
    } catch (e) {
        return '';
    }
}

export default {
    name: 'RegistrationLayout',
    components: { Icon },
    data() {
        return {
            userName: '',
            isLoggedIn: false,
        };
    },
    watch: {
        $route: {
            immediate: true,
            handler() {
                this.isLoggedIn = Boolean(localStorage.getItem('token'));
                this.userName = readTokenName();
            },
        },
    },
    methods: {
        logout() {
            Repository.post('/api/logout').catch(() => {
                // ignore
            }).finally(() => {
                localStorage.removeItem('token');
                this.$router.push('/reg/login');
            });
        },
    },
};
</script>
