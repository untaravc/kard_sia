import { defineStore } from 'pinia';

export const usePageFiltersStore = defineStore('pageFilters', {
    state: () => ({
        filtersByPage: {},
    }),
    getters: {
        getFilters: (state) => (page) => state.filtersByPage[page] || null,
    },
    actions: {
        setFilters(page, filters) {
            this.filtersByPage = {
                ...this.filtersByPage,
                [page]: { ...filters },
            };
        },
        clearFilters(page) {
            const next = { ...this.filtersByPage };
            delete next[page];
            this.filtersByPage = next;
        },
    },
});
