<template>
    <div class="min-h-screen bg-surface text-ink">
        <div class="border-b border-border bg-panel">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <div class="text-sm font-semibold tracking-wide text-primary">Registration</div>
                <div class="flex items-center gap-3 text-sm">
                    <button type="button" class="text-muted hover:text-ink" @click="logout">Logout</button>
                </div>
            </div>
        </div>
        <router-view />
    </div>
</template>

<script>
import Repository from '../../repository';

export default {
    name: 'RegistrationLayout',
    methods: {
        logout() {
            localStorage.removeItem('token');
            Repository.post('/api/logout').catch(() => {
                // ignore
            }).finally(() => {
                this.$router.push('/reg/login');
            });
        },
    },
};
</script>

