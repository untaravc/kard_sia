<template>
    <Modal
        :open="open"
        title="Uploaded File"
        eyebrow="Detail File"
        size="xl"
        @close="$emit('close')"
    >
        <div class="max-h-[100vh] overflow-y-auto">
            <div class="grid gap-3 text-center">
                <div class="text-base font-semibold text-ink">{{ fileTitle }}</div>
                <div class="text-xs text-muted">{{ fileTitle }}</div>
                <div v-if="fileLink" class="overflow-hidden rounded-xl border border-border bg-white">
                    <img
                        v-if="fileIsImage"
                        :src="fileLink"
                        :alt="fileTitle"
                        class="h-auto w-full object-contain"
                    />
                    <iframe
                        v-else-if="fileIsPdf"
                        :src="fileLink"
                        class="h-[32rem] w-full"
                        title="File preview"
                    ></iframe>
                    <div v-else class="px-3 py-6 text-xs text-muted">
                        Preview not available.
                    </div>
                </div>
                <div>
                    <a
                        v-if="fileLink"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-xs font-semibold text-white"
                        :href="fileLink"
                        target="_blank"
                        rel="noopener"
                        download
                    >
                        <i class="fa fa-download"></i>
                        Download
                    </a>
                    <button
                        v-else
                        class="inline-flex items-center gap-2 rounded-lg border border-border px-4 py-2 text-xs text-muted"
                        type="button"
                        disabled
                    >
                        No file
                    </button>
                </div>
            </div>
        </div>
    </Modal>
</template>

<script>
import Modal from '../../components/Modal.vue';

export default {
    components: {
        Modal,
    },
    props: {
        open: {
            type: Boolean,
            default: false,
        },
        file: {
            type: Object,
            default: () => ({}),
        },
    },
    computed: {
        fileTitle() {
            return this.file && this.file.title ? this.file.title : 'Lampiran';
        },
        fileLink() {
            return this.file && this.file.link ? this.file.link : '';
        },
        fileIsImage() {
            return this.isImageFile(this.fileLink);
        },
        fileIsPdf() {
            return this.isPdfFile(this.fileLink);
        },
    },
    methods: {
        isImageFile(link) {
            if (!link) {
                return false;
            }
            const clean = String(link).split('?')[0].split('#')[0].toLowerCase();
            return /\.(png|jpe?g|gif|webp|bmp|svg)$/.test(clean);
        },
        isPdfFile(link) {
            if (!link) {
                return false;
            }
            const clean = String(link).split('?')[0].split('#')[0].toLowerCase();
            return clean.endsWith('.pdf');
        },
    },
};
</script>
