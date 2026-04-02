<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Tutorial</div>
                <h1 class="mt-1 break-words text-2xl font-semibold text-ink">
                    {{ post.title || 'Untitled' }}
                </h1>
                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-muted">
                    <span v-if="post.release_at">Release: {{ formatDateTime(post.release_at) }}</span>
                    <span v-if="post.section" class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700">
                        {{ post.section }}
                    </span>
                </div>
            </div>
            <router-link
                class="rounded-xl border border-border bg-white/80 px-4 py-2 text-sm text-muted shadow-sm backdrop-blur"
                to="/blu/tutorial"
            >
                Back
            </router-link>
        </header>

        <section class="relative overflow-hidden rounded-2xl border border-border bg-panel">
            <Loading :active="loading" :is-full-page="false" />
            <div v-if="errorMessage" class="border-b border-rose-100 bg-rose-50 px-5 py-3 text-xs text-rose-600">
                {{ errorMessage }}
            </div>

            <div v-if="!loading" class="grid gap-6 p-5">
                <div v-if="post.image_url" class="overflow-hidden rounded-2xl border border-border bg-slate-50">
                    <img :src="post.image_url" alt="Cover" class="max-h-[360px] w-full object-cover" />
                </div>

                <article
                    v-if="post.content"
                    class="prose prose-slate max-w-none rounded-2xl border border-border bg-white p-5"
                    v-html="post.content"
                />
                <div v-else class="rounded-2xl border border-border bg-white p-5 text-sm text-muted">
                    No content.
                </div>

                <div v-if="galleryImages.length" class="rounded-2xl border border-border bg-white p-5">
                    <div class="text-xs uppercase tracking-[0.2em] text-muted">Images</div>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <a
                            v-for="(url, index) in galleryImages"
                            :key="`${url}-${index}`"
                            :href="url"
                            target="_blank"
                            rel="noreferrer"
                            class="group overflow-hidden rounded-2xl border border-border bg-slate-50"
                        >
                            <img :src="url" alt="Gallery" class="h-40 w-full object-cover transition group-hover:scale-[1.02]" />
                        </a>
                    </div>
                </div>

                <div v-if="attachments.length" class="rounded-2xl border border-border bg-white p-5">
                    <div class="text-xs uppercase tracking-[0.2em] text-muted">Attachments</div>
                    <div class="mt-4 grid gap-2">
                        <a
                            v-for="(url, index) in attachments"
                            :key="`${url}-${index}`"
                            class="flex items-center justify-between gap-3 rounded-xl border border-border bg-panel px-4 py-3 text-sm"
                            :href="url"
                            target="_blank"
                            rel="noreferrer"
                        >
                            <span class="truncate font-medium text-ink">{{ fileNameFromUrl(url) }}</span>
                            <span class="text-xs font-semibold text-primary">Download</span>
                        </a>
                    </div>
                </div>
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
            loading: false,
            errorMessage: '',
            post: {},
        };
    },
    computed: {
        galleryImages() {
            return this.normalizeUrlList(this.post.image_urls);
        },
        attachments() {
            return this.normalizeUrlList(this.post.attachment_urls);
        },
    },
    created() {
        this.fetchPost();
    },
    methods: {
        fetchPost() {
            const id = this.$route && this.$route.params ? this.$route.params.id : null;
            if (!id) {
                this.errorMessage = 'Missing post id.';
                return Promise.resolve();
            }

            this.loading = true;
            this.errorMessage = '';

            return Repository.get(`${this.baseUrl}/${id}`)
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.post = result || {};
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to load tutorial.';
                    this.errorMessage = message;
                    this.post = {};
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        normalizeUrlList(value) {
            if (!value) {
                return [];
            }

            if (Array.isArray(value)) {
                return value.filter(Boolean);
            }

            if (typeof value === 'string') {
                try {
                    const parsed = JSON.parse(value);
                    return Array.isArray(parsed) ? parsed.filter(Boolean) : (value ? [value] : []);
                } catch (error) {
                    return value ? [value] : [];
                }
            }

            return [];
        },
        fileNameFromUrl(url) {
            if (!url) {
                return '';
            }

            try {
                const decoded = decodeURIComponent(url);
                const lastSegment = decoded.split('/').pop() || decoded;
                return lastSegment.split('?')[0];
            } catch (error) {
                return url;
            }
        },
        formatDateTime(value) {
            if (!value) {
                return '';
            }
            const normalized = String(value).replace(' ', 'T');
            const date = new Date(normalized);
            if (Number.isNaN(date.getTime())) {
                return String(value);
            }
            return date.toLocaleString();
        },
    },
};
</script>

