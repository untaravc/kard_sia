<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Post Management</div>
                <h1 class="text-2xl font-semibold text-ink">Posts</h1>
            </div>
            <router-link
                class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                to="/blu/posts/create"
            >
                Add Post
            </router-link>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="text-xs text-muted">Keyword</label>
                    <input
                        v-model.trim="filters.keyword"
                        @keyup.enter="applyFilter"
                        type="text"
                        placeholder="Search title, section, or content..."
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="min-w-[160px]">
                    <label class="text-xs text-muted">Section</label>
                    <input
                        v-model.trim="filters.section"
                        @keyup.enter="applyFilter"
                        type="text"
                        placeholder="Optional section"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="min-w-[160px]">
                    <label class="text-xs text-muted">Auth Type</label>
                    <input
                        v-model.trim="filters.auth_type"
                        @keyup.enter="applyFilter"
                        type="text"
                        placeholder="Optional author type"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
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
                <div class="font-semibold">Posts</div>
                <div v-if="pagination.total" class="text-xs text-muted">
                    {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }}
                </div>
            </div>
            <div v-if="errorMessage" class="border-b border-rose-100 bg-rose-50 px-5 py-3 text-xs text-rose-600">
                {{ errorMessage }}
            </div>
            <div class="divide-y divide-border">
                <div v-if="!loading && posts.length === 0" class="px-5 py-6 text-sm text-muted">
                    No posts found.
                </div>
                <div
                    v-for="(post, index) in posts"
                    :key="post.id"
                    class="flex flex-wrap items-start gap-3 px-5 py-4"
                >
                    <div class="w-8 pt-1 text-sm font-semibold text-muted">
                        {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="font-semibold text-ink">{{ post.title || 'Untitled Post' }}</div>
                            <span
                                v-if="post.section"
                                class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700"
                            >
                                {{ post.section }}
                            </span>
                            <span
                                v-if="post.auth_type"
                                class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700"
                            >
                                {{ post.auth_type }}
                            </span>
                        </div>
                        <div class="mt-1 text-xs text-muted">
                            <span v-if="post.category_id">Category: {{ post.category_id }}</span>
                            <span v-if="post.auth_id">• Author ID: {{ post.auth_id }}</span>
                            <span v-if="post.release_at">• Release: {{ formatDate(post.release_at) }}</span>
                        </div>
                        <div v-if="plainContent(post.content)" class="mt-2 text-sm text-muted">
                            {{ plainContent(post.content) }}
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                            type="button"
                            @click="openEdit(post)"
                        >
                            Edit
                        </button>
                        <button
                            class="rounded-lg bg-rose-500/10 px-3 py-1.5 text-xs text-rose-600"
                            type="button"
                            @click="deletePost(post)"
                        >
                            Delete
                        </button>
                    </div>
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
import persistFilters from '../../mixins/persistFilters';

export default {
    components: {
        Loading,
    },
    mixins: [persistFilters('posts')],
    data() {
        return {
            baseUrl: '/api/posts',
            posts: [],
            pagination: {},
            filters: {
                keyword: '',
                section: '',
                auth_type: '',
                page: 1,
            },
            loading: false,
            errorMessage: '',
        };
    },
    created() {
        this.fetchPosts();
    },
    methods: {
        fetchPosts() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(this.baseUrl, {
                params: this.filters,
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
                    this.errorMessage = 'Failed to load posts.';
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
            this.filters.section = '';
            this.filters.auth_type = '';
            this.filters.page = 1;
            this.fetchPosts();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchPosts();
        },
        openEdit(post) {
            if (!post || !post.id) {
                return;
            }

            this.$router.push(`/blu/posts/${post.id}`);
        },
        deletePost(post) {
            const label = post && post.title ? post.title : 'this post';
            if (!window.confirm(`Delete ${label}?`)) {
                return;
            }

            Repository.delete(`${this.baseUrl}/${post.id}`)
                .then(() => {
                    this.fetchPosts();
                    this.$showToast('Post deleted successfully.');
                })
                .catch(() => {
                    this.errorMessage = 'Failed to delete post.';
                });
        },
        plainContent(content) {
            if (!content) {
                return '';
            }

            const plain = String(content)
                .replace(/<[^>]*>/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();

            if (plain.length <= 180) {
                return plain;
            }

            return `${plain.slice(0, 180)}...`;
        },
        formatDate(value) {
            if (!value) {
                return '';
            }

            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return value;
            }

            return date.toLocaleString();
        },
    },
};
</script>
