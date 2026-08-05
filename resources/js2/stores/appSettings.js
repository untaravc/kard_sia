import { defineStore } from 'pinia';
import Repository from '../repository';

export const useAppSettingsStore = defineStore('appSettings', {
    state: () => ({
        settings: {},
        error: null,
    }),
    actions: {
        fetchSetting(label, force = false) {
            if (Object.prototype.hasOwnProperty.call(this.settings, label) && !force) {
                return Promise.resolve(this.settings[label]);
            }

            return Repository.get(`/api/settings/label/${label}`)
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const value = result ? result.value : null;
                    this.settings = { ...this.settings, [label]: value };
                    return value;
                })
                .catch((error) => {
                    this.error = error;
                    return null;
                });
        },
    },
});
