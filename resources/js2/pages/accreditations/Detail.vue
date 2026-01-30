<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Accreditation Detail</div>
                <h1 class="text-2xl font-semibold text-ink">
                    {{ accreditation.title || 'Accreditation' }}
                </h1>
            </div>
            <router-link
                class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                to="/cblu/accreditations"
            >
                Back
            </router-link>
        </header>

        <section class="relative rounded-2xl border border-border bg-panel p-6">
            <Loading :active="loading" :is-full-page="false" />
            <div
                v-if="errorMessage"
                class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600"
            >
                {{ errorMessage }}
            </div>
            <div v-if="!loading" class="grid gap-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs uppercase tracking-[0.2em] text-muted">Status</span>
                    <span
                        v-if="accreditation.is_complete"
                        class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700"
                    >
                        Complete
                    </span>
                    <span
                        v-else
                        class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-700"
                    >
                        Incomplete
                    </span>
                    <button
                        class="ml-auto rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90"
                        type="button"
                        @click="openDetail(accreditation)"
                    >
                        Detail
                    </button>
                </div>
                <div v-if="accreditation.description" class="rounded-xl border border-border bg-white px-4 py-3">
                    <div class="text-xs uppercase tracking-[0.2em] text-muted">Description</div>
                    <p class="mt-2 text-sm text-ink ">{{ accreditation.description }}</p>
                </div>
                <div v-if="accreditation.content" class="rounded-xl border border-border bg-white px-4 py-3">
                    <div class="text-xs uppercase tracking-[0.2em] text-muted">Content</div>
                    <p class="mt-2 text-sm text-ink ">{{ accreditation.content }}</p>
                </div>
                <div class="grid gap-3">
                    <div class="text-xs uppercase tracking-[0.2em] text-muted">Accreditation Tree</div>
                    <div v-if="treeLoading" class="text-sm text-muted">Loading tree...</div>
                    <div v-else-if="treeItems.length === 0" class="text-sm text-muted">
                        No accreditation details available.
                    </div>
                    <div v-else class="grid gap-2">
                        <TreeItem
                            v-for="item in treeItems"
                            :key="item.id || item.idx"
                            :item="item"
                            :is-open="item.__open"
                            @show-detail="openDetail"
                            @add-evidence="openEvidenceModal"
                            @edit-evidence="openEditEvidenceModal"
                            @delete-evidence="deleteEvidence"
                            @preview-attachment="openPreview"
                            @toggle-open="handleToggleOpen"
                        />
                    </div>
                </div>
            </div>
        </section>

        <Modal
            :open="detailModalOpen"
            :title="detailItem.title || detailItem.idx || 'Accreditation Detail'"
            eyebrow="Main element & fulfilment"
            size="full"
            @close="closeDetail"
        >
            <div class="grid gap-4 text-sm text-ink">
                <div v-if="detailItem.main_element">
                    <div class="text-xs uppercase tracking-[0.2em] text-muted">Main Element</div>
                    <p class="mt-2 whitespace-pre-line">{{ detailItem.main_element }}</p>
                </div>
                <div v-if="detailItem.main_element_fulfilment">
                    <div class="text-xs uppercase tracking-[0.2em] text-muted">Fulfilment</div>
                    <p class="mt-2 whitespace-pre-line">{{ detailItem.main_element_fulfilment }}</p>
                </div>
                <div v-if="!detailItem.main_element && !detailItem.main_element_fulfilment" class="text-muted">
                    No main element or fulfilment available.
                </div>
                <div class="rounded-xl border border-border bg-white p-4">
                    <div class="text-xs uppercase tracking-[0.2em] text-muted">Note</div>
                    <textarea
                        v-model.trim="detailNote"
                        rows="4"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        placeholder="Add a note..."
                    ></textarea>
                    <label class="mt-3 flex items-center gap-2 text-xs text-muted">
                        <input
                            v-model="detailIsComplete"
                            type="checkbox"
                            class="h-4 w-4 rounded border-border text-primary focus:ring-primary/30"
                            :disabled="!detailHasEvidence"
                        />
                        Mark as complete
                    </label>
                    <p v-if="!detailHasEvidence" class="mt-2 text-[11px] text-amber-600">
                        Add evidence to enable completion status.
                    </p>
                    <button
                        class="mt-4 rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                        type="button"
                        :disabled="detailSaving"
                        @click="submitDetailNote"
                    >
                        Save Note
                    </button>
                </div>
            </div>
        </Modal>

        <Modal
            :open="evidenceModalOpen"
            :title="`Add Evidence${evidenceTargetTitle ? `: ${evidenceTargetTitle}` : ''}`"
            eyebrow="Upload supporting files"
            size="full"
            @close="closeEvidenceModal"
        >
            <Loading :active="evidenceUploading" :is-full-page="false" />
            <form class="grid gap-4" @submit.prevent="submitEvidence">
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Title</span>
                    <input
                        v-model.trim="evidenceForm.title"
                        type="text"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Description</span>
                    <textarea
                        v-model.trim="evidenceForm.description"
                        rows="3"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    ></textarea>
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Attachment URLs</span>
                    <input
                        type="file"
                        accept="image/*,application/pdf"
                        multiple
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm"
                        @change="handleEvidenceFiles"
                    />
                </label>
                <div v-if="evidenceForm.attachments.length" class="grid gap-2">
                    <div class="text-xs uppercase tracking-[0.2em] text-muted">Preview</div>
                    <div class="grid gap-3 md:grid-cols-2">
                        <div
                            v-for="(attachment, index) in evidenceForm.attachments"
                            :key="`${attachment.url}-${index}`"
                            class="rounded-xl border border-border bg-white p-3"
                        >
                            <img
                                v-if="attachment.type === 'image'"
                                :src="attachment.url"
                                alt="Evidence preview"
                                class="h-40 w-full rounded-lg object-cover"
                            />
                            <div v-else class="flex items-center gap-2 rounded-lg border border-border bg-slate-50 px-3 py-2">
                                <span class="rounded-md bg-rose-100 px-2 py-1 text-xs font-semibold text-rose-600">PDF</span>
                                <span class="text-xs text-muted">{{ attachment.name }}</span>
                            </div>
                            <div class="mt-2 text-xs text-muted break-all">{{ attachment.url }}</div>
                        </div>
                    </div>
                </div>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="submit"
                    :disabled="evidenceUploading"
                >
                    Save Evidence
                </button>
            </form>
        </Modal>

        <Modal
            :open="editEvidenceModalOpen"
            :title="`Edit Evidence${editEvidenceTargetTitle ? `: ${editEvidenceTargetTitle}` : ''}`"
            eyebrow="Update evidence details"
            size="full"
            @close="closeEditEvidenceModal"
        >
            <Loading :active="editEvidenceUploading" :is-full-page="false" />
            <form class="grid gap-4" @submit.prevent="submitEditEvidence">
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Title</span>
                    <input
                        v-model.trim="editEvidenceForm.title"
                        type="text"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Description</span>
                    <textarea
                        v-model.trim="editEvidenceForm.description"
                        rows="3"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    ></textarea>
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Add Attachments</span>
                    <input
                        type="file"
                        accept="image/*,application/pdf"
                        multiple
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm"
                        @change="handleEditEvidenceFiles"
                    />
                </label>
                <div v-if="editEvidenceForm.attachment_urls.length" class="grid gap-2">
                    <div class="text-xs uppercase tracking-[0.2em] text-muted">Attachments</div>
                    <div class="grid gap-1 text-xs">
                        <a
                            v-for="(url, index) in editEvidenceForm.attachment_urls"
                            :key="`edit-evidence-${index}`"
                            :href="url"
                            target="_blank"
                            rel="noopener"
                            class="break-all text-primary underline"
                        >
                            {{ url }}
                        </a>
                    </div>
                </div>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="submit"
                    :disabled="editEvidenceUploading"
                >
                    Update Evidence
                </button>
            </form>
        </Modal>

        <Modal
            :open="previewModalOpen"
            :title="previewTitle"
            eyebrow="Evidence preview"
            size="full"
            @close="closePreviewModal"
        >
            <div class="grid gap-3">
                <img
                    v-if="previewType === 'image'"
                    :src="previewSrc"
                    alt="Evidence preview"
                    class="w-full rounded-xl border border-border object-contain"
                />
                <iframe
                    v-else-if="previewType === 'pdf'"
                    :src="previewSrc"
                    class="h-[70vh] w-full rounded-xl border border-border"
                ></iframe>
                <div v-else class="text-sm text-muted">No preview available.</div>
            </div>
        </Modal>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Modal from '../../components/Modal.vue';
import Repository from '../../repository';
import { uploadFirebaseFile } from '../../upload';

const TreeItem = {
    name: 'TreeItem',
    props: {
        item: {
            type: Object,
            required: true,
        },
        isOpen: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            open: this.isOpen,
        };
    },
    watch: {
        isOpen(next) {
            this.open = next;
        },
    },
    computed: {
        hasChildren() {
            return Array.isArray(this.item.children) && this.item.children.length > 0;
        },
    },
    methods: {
        isImageUrl(url) {
            if (!url || typeof url !== 'string') {
                return false;
            }
            return /\.(png|jpe?g|gif|webp|bmp|svg)(\?.*)?$/i.test(url);
        },
        normalizeAttachments(evidence) {
            if (!evidence || evidence.attachment_urls == null) {
                return [];
            }
            const urls = evidence.attachment_urls;
            if (Array.isArray(urls)) {
                return urls.filter(Boolean);
            }
            if (typeof urls === 'string') {
                try {
                    const parsed = JSON.parse(urls);
                    if (Array.isArray(parsed)) {
                        return parsed.filter(Boolean);
                    }
                } catch (error) {
                    // Ignore JSON parse errors and fall back to raw string.
                }
                return urls ? [urls] : [];
            }
            return [];
        },
    },
    template: `
        <div class="rounded-xl border border-border bg-white">
            <button
                class="flex w-full items-start gap-3 px-4 py-3 text-left"
                type="button"
                @click="open = !open; $emit('toggle-open', item, open)"
            >
                <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-primary/70"></span>
                <span class="flex-1">
                    <div class="text-sm font-semibold text-ink">{{ item.title || item.idx || 'Accreditation' }}</div>
                    <div v-if="item.description" class="mt-1 text-xs text-muted">{{ item.description }}</div>
                    <div class="mt-1 text-[11px] text-muted">
                        <span v-if="item.idx">Idx: {{ item.idx }}</span>
                        <span v-if="item.parent_idx"> · Parent: {{ item.parent_idx }}</span>
                    </div>
                </span>
                <div class="flex items-center gap-2">
                    <span
                        class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                        :class="item.is_complete ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                    >
                        {{ item.is_complete ? 'Complete' : 'Incomplete' }}
                    </span>
                    <button
                        class="rounded-lg border border-border bg-white px-3 py-1.5 text-[11px] font-semibold text-muted transition hover:bg-slate-50"
                        type="button"
                        @click.stop="$emit('show-detail', item)"
                    >
                        Detail
                    </button>
                    <button
                        class="rounded-lg bg-primary/10 px-3 py-1.5 text-[11px] font-semibold text-primary transition hover:bg-primary/15"
                        type="button"
                        @click.stop="$emit('add-evidence', item)"
                    >
                        Add Evidence
                    </button>
                    <span v-if="hasChildren" class="text-xs text-muted">{{ open ? 'Hide' : 'Show' }}</span>
                </div>
            </button>
            <div v-if="open" class="border-t border-border px-4 py-3">
                <div v-if="item.content" class="mt-3 text-xs text-muted">Content</div>
                <p v-if="item.content" class="mt-1 text-sm text-ink ">{{ item.content }}</p>
                <div v-if="item.evidences && item.evidences.length" class="mt-4">
                    <div class="text-xs uppercase tracking-[0.2em] text-muted">Evidence</div>
                    <div class="mt-2 grid gap-2">
                        <div
                            v-for="(evidence, evidenceIndex) in item.evidences"
                            :key="evidence.id || evidenceIndex"
                            class="rounded-lg border border-border bg-slate-50 px-3 py-2"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="text-sm font-semibold text-ink">
                                    {{ evidence.title || 'Evidence' }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        class="rounded-md border border-border bg-white px-2 py-1 text-[11px] font-semibold text-muted"
                                        type="button"
                                        @click.stop="$emit('edit-evidence', evidence)"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        class="rounded-md bg-rose-100 px-2 py-1 text-[11px] font-semibold text-rose-600"
                                        type="button"
                                        @click.stop="$emit('delete-evidence', evidence)"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                            <div v-if="evidence.description" class="mt-1 text-xs text-muted">
                                {{ evidence.description }}
                            </div>
                            <div v-if="normalizeAttachments(evidence).length" class="mt-2 grid gap-1 text-xs">
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="(url, urlIndex) in normalizeAttachments(evidence)"
                                        :key="\`\${evidence.id || evidenceIndex}-\${urlIndex}\`"
                                        type="button"
                                        class="group rounded-lg border border-border bg-white p-2 text-left"
                                        @click.stop="$emit('preview-attachment', url)"
                                    >
                                        <img
                                            v-if="isImageUrl(url)"
                                            :src="url"
                                            alt="Evidence thumbnail"
                                            class="h-16 w-20 rounded-md object-cover"
                                        />
                                        <div
                                            v-else
                                            class="flex h-16 w-20 items-center justify-center rounded-md bg-rose-50 text-xs font-semibold text-rose-600"
                                        >
                                            PDF
                                        </div>
                                        <div class="mt-1 max-w-[5rem] truncate text-[10px] text-muted">
                                            {{ url }}
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="mt-4 text-xs text-muted">
                    No evidence available.
                </div>
                <div v-if="hasChildren" class="mt-4 grid gap-2">
                    <TreeItem
                        v-for="child in item.children"
                        :key="child.id || child.idx"
                        :item="child"
                        :is-open="child.__open"
                        @show-detail="$emit('show-detail', $event)"
                        @add-evidence="$emit('add-evidence', $event)"
                        @edit-evidence="$emit('edit-evidence', $event)"
                        @delete-evidence="$emit('delete-evidence', $event)"
                        @preview-attachment="$emit('preview-attachment', $event)"
                        @toggle-open="$emit('toggle-open', $event)"
                    />
                </div>
            </div>
        </div>
    `,
};

export default {
    components: {
        Loading,
        Modal,
        TreeItem,
    },
    data() {
        return {
            accreditation: {},
            loading: false,
            errorMessage: '',
        treeItems: [],
        treeLoading: false,
        openStates: {},
        detailModalOpen: false,
            detailItem: {},
            detailNote: '',
            detailIsComplete: false,
            detailSaving: false,
            previewModalOpen: false,
            previewType: '',
            previewSrc: '',
            evidenceModalOpen: false,
            evidenceUploading: false,
            evidenceTarget: null,
            evidenceForm: {
                title: '',
                description: '',
                attachments: [],
                attachment_urls: [],
            },
            editEvidenceModalOpen: false,
            editEvidenceUploading: false,
            editEvidenceTarget: null,
            editEvidenceForm: {
                title: '',
                description: '',
                attachment_urls: [],
                attachments: [],
            },
        };
    },
    computed: {
        accreditationId() {
            return this.$route.params.id;
        },
        detailHasEvidence() {
            return Array.isArray(this.detailItem.evidences) && this.detailItem.evidences.length > 0;
        },
        evidenceTargetTitle() {
            if (!this.evidenceTarget) {
                return '';
            }
            return this.evidenceTarget.title || this.evidenceTarget.idx || '';
        },
        editEvidenceTargetTitle() {
            if (!this.editEvidenceTarget) {
                return '';
            }
            return this.editEvidenceTarget.title || `#${this.editEvidenceTarget.id || ''}`;
        },
        previewTitle() {
            if (this.previewType === 'image') {
                return 'Image preview';
            }
            if (this.previewType === 'pdf') {
                return 'PDF preview';
            }
            return 'Preview';
        },
    },
    created() {
        this.fetchAccreditation();
    },
    methods: {
        isImageUrl(url) {
            if (!url || typeof url !== 'string') {
                return false;
            }
            return /\.(png|jpe?g|gif|webp|bmp|svg)(\?.*)?$/i.test(url);
        },
        openPreview(url) {
            if (!url) {
                return;
            }
            this.previewSrc = url;
            this.previewType = this.isImageUrl(url) ? 'image' : 'pdf';
            this.previewModalOpen = true;
        },
        closePreviewModal() {
            this.previewModalOpen = false;
            this.previewType = '';
            this.previewSrc = '';
        },
        normalizeEvidenceAttachments(evidence) {
            if (!evidence || evidence.attachment_urls == null) {
                return [];
            }
            if (Array.isArray(evidence.attachment_urls)) {
                return evidence.attachment_urls.filter(Boolean);
            }
            if (typeof evidence.attachment_urls === 'string') {
                try {
                    const parsed = JSON.parse(evidence.attachment_urls);
                    if (Array.isArray(parsed)) {
                        return parsed.filter(Boolean);
                    }
                } catch (error) {
                    // Ignore JSON parse errors and fall back to raw string.
                }
                return evidence.attachment_urls ? [evidence.attachment_urls] : [];
            }
            return [];
        },
        openDetail(item) {
            this.detailItem = item || {};
            this.detailNote = this.detailItem.content || '';
            this.detailIsComplete = Boolean(this.detailItem.is_complete);
            this.detailModalOpen = true;
        },
        closeDetail() {
            this.detailModalOpen = false;
            this.detailItem = {};
            this.detailNote = '';
            this.detailIsComplete = false;
        },
        openEvidenceModal(item) {
            this.evidenceTarget = item || null;
            this.evidenceForm = {
                title: '',
                description: '',
                attachments: [],
                attachment_urls: [],
            };
            this.evidenceModalOpen = true;
        },
        closeEvidenceModal() {
            this.evidenceModalOpen = false;
            this.evidenceTarget = null;
        },
        openEditEvidenceModal(evidence) {
            if (!evidence) {
                return;
            }
            this.editEvidenceTarget = evidence;
            this.editEvidenceForm = {
                title: evidence.title || '',
                description: evidence.description || '',
                attachment_urls: this.normalizeEvidenceAttachments(evidence),
                attachments: [],
            };
            this.editEvidenceModalOpen = true;
        },
        closeEditEvidenceModal() {
            this.editEvidenceModalOpen = false;
            this.editEvidenceTarget = null;
            this.editEvidenceForm = {
                title: '',
                description: '',
                attachment_urls: [],
                attachments: [],
            };
        },
        async handleEditEvidenceFiles(event) {
            const files = event && event.target ? Array.from(event.target.files || []) : [];
            if (!files.length) {
                return;
            }

            this.editEvidenceUploading = true;
            try {
                const prefix = 'KardiologiFkkmk/Accreditation/Evidence';
                for (const file of files) {
                    const url = await uploadFirebaseFile({ file, prefix });
                    if (!url) {
                        continue;
                    }
                    const type = file.type && file.type.startsWith('image/') ? 'image' : 'pdf';
                    this.editEvidenceForm.attachments.push({
                        url,
                        type,
                        name: file.name || 'document.pdf',
                    });
                    this.editEvidenceForm.attachment_urls.push(url);
                }
            } finally {
                this.editEvidenceUploading = false;
            }
        },
        async handleEvidenceFiles(event) {
            const files = event && event.target ? Array.from(event.target.files || []) : [];
            if (!files.length) {
                return;
            }

            this.evidenceUploading = true;
            try {
                const prefix = 'KardiologiFkkmk/Accreditation/Evidence';
                for (const file of files) {
                    const url = await uploadFirebaseFile({ file, prefix });
                    if (!url) {
                        continue;
                    }
                    const type = file.type && file.type.startsWith('image/') ? 'image' : 'pdf';
                    this.evidenceForm.attachments.push({
                        url,
                        type,
                        name: file.name || 'document.pdf',
                    });
                    this.evidenceForm.attachment_urls.push(url);
                }
            } finally {
                this.evidenceUploading = false;
            }
        },
        submitEvidence() {
            if (!this.evidenceTarget) {
                this.closeEvidenceModal();
                return;
            }

            const parentIdx = this.evidenceTarget.idx || this.evidenceTarget.parent_idx || null;
            const parentId = this.evidenceTarget.id || null;

            this.evidenceUploading = true;
            return Repository.post('/api/accreditation', {
                parent_id: parentId,
                parent_idx: parentIdx,
                type: 'evidence',
                title: this.evidenceForm.title,
                description: this.evidenceForm.description,
                attachment_urls: this.evidenceForm.attachment_urls,
            })
                .then(() => {
                    this.closeEvidenceModal();
                    this.fetchTree(this.accreditation.idx);
                    this.$showToast('Evidence saved.');
                })
                .catch(() => {
                    this.$showToast('Failed to save evidence.');
                })
                .finally(() => {
                    this.evidenceUploading = false;
                });
        },
        submitEditEvidence() {
            if (!this.editEvidenceTarget || !this.editEvidenceTarget.id) {
                this.closeEditEvidenceModal();
                return;
            }

            this.editEvidenceUploading = true;
            return Repository.put(`/api/accreditations/${this.editEvidenceTarget.id}`, {
                title: this.editEvidenceForm.title,
                description: this.editEvidenceForm.description,
                attachment_urls: this.editEvidenceForm.attachment_urls,
            })
                .then(() => {
                    this.closeEditEvidenceModal();
                    this.fetchTree(this.accreditation.idx);
                    this.$showToast('Evidence updated.');
                })
                .catch(() => {
                    this.$showToast('Failed to update evidence.');
                })
                .finally(() => {
                    this.editEvidenceUploading = false;
                });
        },
        submitDetailNote() {
            if (!this.detailItem || !this.detailItem.id) {
                this.closeDetail();
                return;
            }

            this.detailSaving = true;
            return Repository.put(`/api/accreditations/${this.detailItem.id}`, {
                content: this.detailNote,
                is_complete: this.detailIsComplete ? 1 : 0,
            })
                .then(() => {
                    this.detailModalOpen = false;
                    this.fetchTree(this.accreditation.idx);
                    this.$showToast('Note saved.');
                })
                .catch(() => {
                    this.$showToast('Failed to save note.');
                })
                .finally(() => {
                    this.detailSaving = false;
                });
        },
        deleteEvidence(evidence) {
            if (!evidence || !evidence.id) {
                return;
            }

            const confirmed = window.confirm('Delete this evidence? This action cannot be undone.');
            if (!confirmed) {
                return;
            }

            this.editEvidenceUploading = true;
            Repository.delete(`/api/accreditations/${evidence.id}`)
                .then(() => {
                    this.fetchTree(this.accreditation.idx);
                    this.$showToast('Evidence deleted.');
                })
                .catch(() => {
                    this.$showToast('Failed to delete evidence.');
                })
                .finally(() => {
                    this.editEvidenceUploading = false;
                });
        },
        fetchAccreditation() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(`/api/accreditations/${this.accreditationId}`)
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.accreditation = result || {};
                    const parentIdx = this.accreditation ? this.accreditation.idx : null;
                    if (parentIdx) {
                        this.fetchTree(parentIdx);
                    } else {
                        this.treeItems = [];
                    }
                })
                .catch(() => {
                    this.accreditation = {};
                    this.errorMessage = 'Failed to load accreditation detail.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        fetchTree(parentIdx) {
            this.treeLoading = true;
            return Repository.get(`/api/accreditation-tree/${parentIdx}`)
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const items = Array.isArray(result) ? result : [];
                    this.treeItems = this.applyOpenState(items);
                })
                .catch(() => {
                    this.treeItems = [];
                })
                .finally(() => {
                    this.treeLoading = false;
                });
        },
        applyOpenState(items) {
            if (!Array.isArray(items)) {
                return [];
            }
            return items.map((item) => {
                const key = this.getItemKey(item);
                const next = {
                    ...item,
                    __open: Boolean(this.openStates[key]),
                };
                if (Array.isArray(item.children)) {
                    next.children = this.applyOpenState(item.children);
                }
                return next;
            });
        },
        getItemKey(item) {
            return item && (item.id || item.idx || item.parent_idx);
        },
        handleToggleOpen(item, isOpen) {
            const key = this.getItemKey(item);
            if (!key) {
                return;
            }
            this.openStates = {
                ...this.openStates,
                [key]: Boolean(isOpen),
            };
        },
    },
};
</script>
