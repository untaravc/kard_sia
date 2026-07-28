import { usePageFiltersStore } from '../stores/pageFilters';

/**
 * Restores the last-used filters for `pageKey` on create and keeps the
 * store in sync as `filterKey` changes, so navigating back to a page
 * (e.g. Students) re-applies the filters that were active when it was left.
 */
export default function persistFilters(pageKey, filterKey = 'filters') {
    return {
        created() {
            const store = usePageFiltersStore();
            const saved = store.getFilters(pageKey);
            if (saved) {
                this[filterKey] = { ...this[filterKey], ...saved };
            }
        },
        watch: {
            [filterKey]: {
                deep: true,
                handler(value) {
                    usePageFiltersStore().setFilters(pageKey, value);
                },
            },
        },
    };
}
