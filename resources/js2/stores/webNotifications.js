import { defineStore } from 'pinia';
import Repository from '../repository';

export const useWebNotificationsStore = defineStore('webNotifications', {
    state: () => ({
        items: [],
        unreadCount: 0,
        loading: false,
        loaded: false,
    }),
    actions: {
        fetchList(limit = 8) {
            this.loading = true;

            return Repository.get('/api/web-notifications', { params: { limit } })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.items = (result && result.notifications) || [];
                    this.unreadCount = (result && result.unread_count) || 0;
                    this.loaded = true;
                    return this.items;
                })
                .catch(() => {
                    this.items = [];
                    return [];
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        markAllRead() {
            return Repository.post('/api/web-notifications/mark-all-read')
                .then(() => {
                    this.items = this.items.map((item) => ({ ...item, is_read: true }));
                    this.unreadCount = 0;
                })
                .catch(() => {});
        },
        markRead(id) {
            return Repository.post(`/api/web-notifications/${id}/mark-read`)
                .then(() => {
                    const item = this.items.find((notification) => notification.id === id);
                    if (item && !item.is_read) {
                        item.is_read = true;
                        this.unreadCount = Math.max(0, this.unreadCount - 1);
                    }
                })
                .catch(() => {});
        },
    },
});
