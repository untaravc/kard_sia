import { defineStore } from 'pinia';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
    }),
    actions: {
        setUser(payload) {
            this.user = payload || null;
        },
        clearUser() {
            this.user = null;
        },
    },
});
