import { defineStore } from 'pinia';
import Repository from '../repository';

export const useFirebaseConfigStore = defineStore('firebaseConfig', {
    state: () => ({
        config: null,
        loading: false,
        error: null,
    }),
    actions: {
        fetchConfig(force = false) {
            if (this.config && !force) {
                return Promise.resolve(this.config);
            }

            this.loading = true;
            this.error = null;

            return Repository.get('/api/firebase-config')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.config = result || null;
                    return this.config;
                })
                .catch((error) => {
                    this.error = error;
                    this.config = null;
                    return null;
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
});
