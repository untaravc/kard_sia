<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Post Management</div>
                <h1 class="text-2xl font-semibold text-ink">
                    {{ isEdit ? 'Edit Post' : 'Add Post' }}
                </h1>
            </div>
            <div class="flex items-center gap-2">
                <router-link class="rounded-xl border border-border px-4 py-2 text-sm text-muted" to="/blu/posts">
                    Back
                </router-link>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="button"
                    :disabled="submitting || loading || uploadState.cover || uploadState.gallery || uploadState.attachments"
                    @click="submitForm"
                >
                    {{ submitting ? 'Saving...' : 'Save Post' }}
                </button>
            </div>
        </header>

        <section class="relative rounded-2xl border border-border bg-panel p-6">
            <Loading :active="loading" :is-full-page="false" />
            <div class="grid gap-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Title</span>
                        <input
                            v-model.trim="form.title"
                            type="text"
                            placeholder="Article title"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Section</span>
                        <select
                            v-model="form.section"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        >
                            <option value="">Select section</option>
                            <option v-for="option in sectionOptions" :key="option.id" :value="option.value || option.name">
                                {{ option.name }}
                            </option>
                        </select>
                    </label>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Category ID</span>
                        <input
                            v-model.trim="form.category_id"
                            type="number"
                            min="1"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Release At</span>
                        <input
                            v-model="form.release_at"
                            type="datetime-local"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                </div>

                <div class="grid gap-2 text-sm">
                    <span class="text-muted">Content</span>
                    <div class="rounded-2xl border border-border bg-white p-2">
                        <vue-editor
                            v-model="form.content"
                            :editor-toolbar="editorToolbar"
                            placeholder="Write post content..."
                        />
                    </div>
                </div>

                <div class="grid gap-4 rounded-2xl border border-border bg-white p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-medium text-ink">Cover Image</div>
                            <div class="text-xs text-muted">Upload one image and store the returned Firebase URL.</div>
                        </div>
                        <label class="rounded-xl border border-border px-4 py-2 text-sm text-muted cursor-pointer">
                            <input class="hidden" type="file" accept="image/*" @change="uploadCoverImage" />
                            {{ uploadState.cover ? 'Uploading...' : 'Upload Cover' }}
                        </label>
                    </div>
                    <div v-if="form.image_url" class="grid gap-3">
                        <img :src="form.image_url" alt="Cover preview" class="max-h-56 rounded-xl border border-border object-cover" />
                        <div class="flex items-center gap-2">
                            <a :href="form.image_url" target="_blank" rel="noopener" class="text-xs text-primary underline">
                                Open image
                            </a>
                            <button
                                class="rounded-lg bg-rose-500/10 px-3 py-1.5 text-xs text-rose-600"
                                type="button"
                                @click="form.image_url = ''"
                            >
                                Remove
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 rounded-2xl border border-border bg-white p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-medium text-ink">Gallery Images</div>
                            <div class="text-xs text-muted">Upload multiple images. URLs will be stored as an array.</div>
                        </div>
                        <label class="rounded-xl border border-border px-4 py-2 text-sm text-muted cursor-pointer">
                            <input class="hidden" type="file" accept="image/*" multiple @change="uploadGalleryImages" />
                            {{ uploadState.gallery ? 'Uploading...' : 'Upload Images' }}
                        </label>
                    </div>
                    <div v-if="galleryImages.length > 0" class="grid gap-4 md:grid-cols-3">
                        <div
                            v-for="(url, index) in galleryImages"
                            :key="`${url}-${index}`"
                            class="grid gap-2 rounded-xl border border-border p-3"
                        >
                            <img :src="url" alt="Gallery image" class="h-36 w-full rounded-lg object-cover" />
                            <div class="flex items-center justify-between gap-2">
                                <a :href="url" target="_blank" rel="noopener" class="truncate text-xs text-primary underline">
                                    View image
                                </a>
                                <button
                                    class="rounded-lg bg-rose-500/10 px-2 py-1 text-xs text-rose-600"
                                    type="button"
                                    @click="removeGalleryImage(index)"
                                >
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 rounded-2xl border border-border bg-white p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-medium text-ink">Attachments</div>
                            <div class="text-xs text-muted">Upload files. URLs will be stored as an array.</div>
                        </div>
                        <label class="rounded-xl border border-border px-4 py-2 text-sm text-muted cursor-pointer">
                            <input class="hidden" type="file" multiple @change="uploadAttachments" />
                            {{ uploadState.attachments ? 'Uploading...' : 'Upload Attachments' }}
                        </label>
                    </div>
                    <div v-if="attachments.length > 0" class="grid gap-2">
                        <div
                            v-for="(url, index) in attachments"
                            :key="`${url}-${index}`"
                            class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-border px-3 py-2"
                        >
                            <a :href="url" target="_blank" rel="noopener" class="truncate text-sm text-primary underline">
                                {{ fileNameFromUrl(url) }}
                            </a>
                            <button
                                class="rounded-lg bg-rose-500/10 px-2 py-1 text-xs text-rose-600"
                                type="button"
                                @click="removeAttachment(index)"
                            >
                                Remove
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    v-if="errorMessage"
                    class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600"
                >
                    {{ errorMessage }}
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import { VueEditor } from 'vue2-editor';
import Repository from '../../repository';
import { uploadFirebaseFile } from '../../upload';

export default {
    components: {
        Loading,
        VueEditor,
    },
    data() {
        return {
            baseUrl: '/api/posts',
            loading: false,
            submitting: false,
            errorMessage: '',
            sectionOptions: [],
            uploadState: {
                cover: false,
                gallery: false,
                attachments: false,
            },
            editorToolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                [{ header: 1 }, { header: 2 }],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ align: [] }],
                ['blockquote', 'code-block'],
                ['link', 'image'],
                ['clean'],
            ],
            form: {
                id: null,
                title: '',
                content: '',
                category_id: '',
                section: '',
                image_url: '',
                image_urls: [],
                release_at: '',
                attachment_urls: [],
            },
        };
    },
    computed: {
        isEdit() {
            return this.$route.params && this.$route.params.id && this.$route.params.id !== 'create';
        },
        galleryImages() {
            return Array.isArray(this.form.image_urls) ? this.form.image_urls : [];
        },
        attachments() {
            return Array.isArray(this.form.attachment_urls) ? this.form.attachment_urls : [];
        },
    },
    created() {
        this.fetchSectionOptions();
        if (this.isEdit) {
            this.fetchPost();
        }
    },
    methods: {
        fetchSectionOptions() {
            return Repository.get('/api/form-options', {
                params: {
                    type: 'section',
                },
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];
                    this.sectionOptions = data;
                })
                .catch(() => {
                    this.sectionOptions = [];
                });
        },
        fetchPost() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(`${this.baseUrl}/${this.$route.params.id}`)
                .then((response) => {
                    const post = response && response.data ? response.data.result : null;
                    if (!post) {
                        throw new Error('Post not found');
                    }

                    this.form = {
                        id: post.id,
                        title: post.title || '',
                        content: post.content || '',
                        category_id: this.normalizeNumberField(post.category_id),
                        section: post.section || '',
                        image_url: post.image_url || '',
                        image_urls: this.normalizeUrlList(post.image_urls),
                        release_at: this.normalizeDateTimeLocal(post.release_at),
                        attachment_urls: this.normalizeUrlList(post.attachment_urls),
                    };
                })
                .catch(() => {
                    this.errorMessage = 'Failed to load post.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        async uploadCoverImage(event) {
            const file = event && event.target && event.target.files ? event.target.files[0] : null;
            this.resetFileInput(event);
            if (!file) {
                return;
            }

            this.uploadState.cover = true;
            this.errorMessage = '';

            try {
                const url = await uploadFirebaseFile({
                    file,
                    prefix: 'posts/cover-images',
                });

                if (!url) {
                    throw new Error('Upload failed');
                }

                this.form.image_url = url;
                this.$showToast('Cover image uploaded successfully.');
            } catch (error) {
                this.errorMessage = 'Failed to upload cover image.';
            } finally {
                this.uploadState.cover = false;
            }
        },
        async uploadGalleryImages(event) {
            const files = event && event.target && event.target.files ? Array.from(event.target.files) : [];
            this.resetFileInput(event);
            if (files.length === 0) {
                return;
            }

            this.uploadState.gallery = true;
            this.errorMessage = '';

            try {
                const uploads = await Promise.all(
                    files.map((file) => uploadFirebaseFile({
                        file,
                        prefix: 'posts/gallery-images',
                    }))
                );

                const urls = uploads.filter(Boolean);
                if (urls.length !== files.length) {
                    throw new Error('Upload failed');
                }

                this.form.image_urls = [...this.galleryImages, ...urls];
                this.$showToast('Gallery images uploaded successfully.');
            } catch (error) {
                this.errorMessage = 'Failed to upload gallery images.';
            } finally {
                this.uploadState.gallery = false;
            }
        },
        async uploadAttachments(event) {
            const files = event && event.target && event.target.files ? Array.from(event.target.files) : [];
            this.resetFileInput(event);
            if (files.length === 0) {
                return;
            }

            this.uploadState.attachments = true;
            this.errorMessage = '';

            try {
                const uploads = await Promise.all(
                    files.map((file) => uploadFirebaseFile({
                        file,
                        prefix: 'posts/attachments',
                    }))
                );

                const urls = uploads.filter(Boolean);
                if (urls.length !== files.length) {
                    throw new Error('Upload failed');
                }

                this.form.attachment_urls = [...this.attachments, ...urls];
                this.$showToast('Attachments uploaded successfully.');
            } catch (error) {
                this.errorMessage = 'Failed to upload attachments.';
            } finally {
                this.uploadState.attachments = false;
            }
        },
        removeGalleryImage(index) {
            this.form.image_urls = this.galleryImages.filter((_, itemIndex) => itemIndex !== index);
        },
        removeAttachment(index) {
            this.form.attachment_urls = this.attachments.filter((_, itemIndex) => itemIndex !== index);
        },
        submitForm() {
            if (!this.form.title) {
                this.errorMessage = 'Title is required.';
                return;
            }

            this.submitting = true;
            this.errorMessage = '';

            const payload = this.buildPayload();
            const request = this.isEdit
                ? Repository.put(`${this.baseUrl}/${this.form.id}`, payload)
                : Repository.post(this.baseUrl, payload);

            return request
                .then(() => {
                    this.$showToast(this.isEdit ? 'Post updated successfully.' : 'Post created successfully.');
                    this.$router.push('/blu/posts');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to save post.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        buildPayload() {
            return {
                title: this.form.title,
                content: this.form.content,
                category_id: this.normalizeInteger(this.form.category_id),
                section: this.form.section || null,
                image_url: this.form.image_url || null,
                image_urls: this.galleryImages.length ? JSON.stringify(this.galleryImages) : null,
                release_at: this.form.release_at ? this.form.release_at.replace('T', ' ') : null,
                attachment_urls: this.attachments.length ? JSON.stringify(this.attachments) : null,
            };
        },
        normalizeInteger(value) {
            if (value === '' || value === null || typeof value === 'undefined') {
                return null;
            }

            const parsed = Number(value);
            return Number.isNaN(parsed) ? null : parsed;
        },
        normalizeNumberField(value) {
            if (value === null || typeof value === 'undefined' || value === '') {
                return '';
            }

            return String(value);
        },
        normalizeDateTimeLocal(value) {
            if (!value) {
                return '';
            }

            const normalized = String(value).replace(' ', 'T');
            return normalized.slice(0, 16);
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
        resetFileInput(event) {
            if (event && event.target) {
                event.target.value = '';
            }
        },
    },
};
</script>
