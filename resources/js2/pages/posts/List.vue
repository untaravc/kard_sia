<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Learning Center</div>
                <h1 class="text-2xl font-semibold text-ink">Tutorial</h1>
            </div>
            <router-link
                class="rounded-xl border border-border bg-white/80 px-4 py-2 text-sm text-muted shadow-sm backdrop-blur"
                to="/blu/dashboard-student/profile"
            >
                Back
            </router-link>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="text-xs text-muted">Keyword</label>
                    <input
                        v-model.trim="filters.keyword"
                        type="text"
                        placeholder="Search tutorial..."
                        @keyup.enter="applyFilter"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="min-w-[200px]">
                    <label class="text-xs text-muted">Release</label>
                    <select
                        v-model="filters.release_mode"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        @change="applyFilter"
                    >
                        <option value="released">Released</option>
                        <option value="upcoming">Upcoming</option>
                        <option value="all">All</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button
                        class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                        type="button"
                        @click="applyFilter"
                    >
                        Search
                    </button>
                    <button
                        class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                        type="button"
                        @click="resetFilter"
                    >
                        Reset
                    </button>
                </div>
            </div>
        </section>

        <section class="relative rounded-2xl border border-border bg-panel">
            <Loading :active="loading" :is-full-page="false" />
            <div class="flex items-center justify-between border-b border-border px-5 py-4">
                <div class="font-semibold">Tutorial Posts</div>
                <div v-if="pagination.total" class="text-xs text-muted">
                    {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }}
                </div>
            </div>
            <div v-if="errorMessage" class="border-b border-rose-100 bg-rose-50 px-5 py-3 text-xs text-rose-600">
                {{ errorMessage }}
            </div>
            <div class="p-5">
                <div v-if="!loading && posts.length === 0" class="py-8 text-center text-sm text-muted">
                    No tutorial posts found.
                </div>
                <div v-else class="grid gap-4 md:grid-cols-2">
                    <button
                        v-for="post in posts"
                        :key="post.id"
                        type="button"
                        class="group flex cursor-pointer flex-col overflow-hidden rounded-2xl border border-border bg-white text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                        @click="openDetail(post)"
                    >
                        <div class="relative h-40 w-full bg-slate-100">
                            <img
                                v-if="post.image_url"
                                :src="post.image_url"
                                alt="Cover"
                                class="h-full w-full object-cover"
                            />
                            <div v-else class="grid h-full w-full place-items-center text-xs text-muted">
                                No cover image
                            </div>
                            <div class="absolute left-3 top-3 flex items-center gap-2">
                                <span class="rounded-full bg-white/90 px-2 py-0.5 text-[11px] font-semibold text-slate-700">
                                    Tutorial
                                </span>
                                <span
                                    v-if="post.release_at"
                                    class="rounded-full bg-white/90 px-2 py-0.5 text-[11px] font-semibold text-slate-700"
                                >
                                    {{ formatDate(post.release_at) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex flex-1 flex-col gap-2 p-4">
                            <div class="text-base font-semibold text-ink group-hover:text-primary">
                                {{ post.title || 'Untitled' }}
                            </div>
                            <div v-if="plainContent(post.content)" class="line-clamp-3 text-sm text-muted">
                                {{ plainContent(post.content) }}
                            </div>
                            <div class="mt-auto pt-2 text-xs font-semibold text-primary">
                                Read tutorial →
                            </div>
                        </div>
                    </button>
                </div>
            </div>
            <div class="flex items-center justify-between border-t border-border px-5 py-4 text-xs text-muted">
                <button
                    class="rounded-lg border border-border px-3 py-1.5"
                    type="button"
                    :disabled="pagination.current_page <= 1"
                    @click="changePage(pagination.current_page - 1)"
                >
                    Prev
                </button>
                <div>Page {{ pagination.current_page || 1 }} / {{ pagination.last_page || 1 }}</div>
                <button
                    class="rounded-lg border border-border px-3 py-1.5"
                    type="button"
                    :disabled="pagination.current_page >= pagination.last_page"
                    @click="changePage(pagination.current_page + 1)"
                >
                    Next
                </button>
            </div>
        </section>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Repository from '../../repository';

export default {
    components: {
        Loading,
    },
    data() {
        return {
            baseUrl: '/api/posts',
            posts: [],
            pagination: {},
            filters: {
                keyword: '',
                page: 1,
                release_mode: 'released',
            },
            loading: false,
            errorMessage: '',
        };
    },
    created() {
        this.fetchPosts();
    },
    methods: {
        todayDate() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        buildParams() {
            const today = this.todayDate();
            const params = {
                section: 'tutorial',
                keyword: this.filters.keyword || undefined,
                page: this.filters.page,
            };

            if (this.filters.release_mode === 'upcoming') {
                params.release_at_gt = today;
            } else if (this.filters.release_mode === 'released') {
                params.release_at_lte = today;
            }

            return params;
        },
        fetchPosts() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(this.baseUrl, {
                params: this.buildParams(),
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];

                    this.posts = data;
                    this.pagination = result || {};
                })
                .catch(() => {
                    this.posts = [];
                    this.pagination = {};
                    this.errorMessage = 'Failed to load tutorial posts.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        applyFilter() {
            this.filters.page = 1;
            this.fetchPosts();
        },
        resetFilter() {
            this.filters.keyword = '';
            this.filters.page = 1;
            this.filters.release_mode = 'released';
            this.fetchPosts();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchPosts();
        },
        openDetail(post) {
            if (!post || !post.id) {
                return;
            }
            this.$router.push(`/blu/tutorial/${post.id}`);
        },
        plainContent(content) {
            if (!content) {
                return '';
            }
            const text = String(content).replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
            return text.length > 140 ? `${text.slice(0, 140)}...` : text;
        },
        formatDate(value) {
            if (!value) {
                return '';
            }
            const normalized = String(value).replace(' ', 'T');
            const date = new Date(normalized);
            if (Number.isNaN(date.getTime())) {
                return String(value);
            }
            return date.toLocaleDateString();
        },
    },
};
</script>

