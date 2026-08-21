<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Notifications</div>
                <h1 class="text-2xl font-semibold text-ink">All Notifications</h1>
            </div>
            <button
                v-if="store.unreadCount > 0"
                type="button"
                class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                @click="markAllRead"
            >
                Mark all as read ({{ store.unreadCount }})
            </button>
        </header>

        <section class="relative rounded-2xl border border-border bg-panel">
            <Loading :active="store.loading" :is-full-page="false" />

            <div v-if="!store.loading && store.items.length === 0" class="px-5 py-10 text-center text-sm text-muted">
                No notifications yet.
            </div>

            <div class="divide-y divide-border">
                <button
                    v-for="notification in store.items"
                    :key="notification.id"
                    type="button"
                    class="block w-full px-5 py-4 text-left hover:bg-slate-50"
                    :class="notification.is_read ? '' : 'bg-primary/5'"
                    @click="handleClick(notification)"
                >
                    <div class="flex items-start gap-3">
                        <span
                            class="mt-1.5 h-2 w-2 flex-shrink-0 rounded-full"
                            :class="notification.is_read ? 'bg-transparent' : 'bg-primary'"
                        ></span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <div class="font-semibold text-ink">{{ notification.title }}</div>
                                <div class="flex-shrink-0 text-xs text-muted">{{ formatTime(notification.created_at) }}</div>
                            </div>
                            <div class="mt-1 text-sm text-muted">{{ notification.content }}</div>
                        </div>
                    </div>
                </button>
            </div>

            <div v-if="canLoadMore" class="border-t border-border px-5 py-4 text-center">
                <button
                    type="button"
                    class="rounded-xl border border-border px-4 py-2 text-sm text-muted hover:bg-slate-50"
                    @click="loadMore"
                >
                    Load more
                </button>
            </div>
        </section>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import { useWebNotificationsStore } from '../../stores/webNotifications';

const PAGE_SIZE = 30;

export default {
    components: {
        Loading,
    },
    data() {
        return {
            store: useWebNotificationsStore(),
            limit: PAGE_SIZE,
        };
    },
    computed: {
        canLoadMore() {
            return !this.store.loading && this.store.items.length >= this.limit;
        },
    },
    created() {
        this.store.fetchList(this.limit);
    },
    methods: {
        loadMore() {
            this.limit += PAGE_SIZE;
            this.store.fetchList(this.limit);
        },
        markAllRead() {
            this.store.markAllRead();
        },
        handleClick(notification) {
            if (!notification.is_read) {
                this.store.markRead(notification.id);
            }
            if (notification.link) {
                window.location.href = notification.link;
            }
        },
        formatTime(value) {
            if (!value) {
                return '';
            }
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return '';
            }
            return date.toLocaleString();
        },
    },
};
</script>
