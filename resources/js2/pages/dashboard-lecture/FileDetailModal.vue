<template>
    <Modal
        :open="open"
        title="Uploaded File"
        eyebrow="Detail File"
        size="md"
        @close="$emit('close')"
    >
        <div class="grid gap-3 text-center">
            <div class="text-base font-semibold text-ink">{{ fileTitle }}</div>
            <div class="text-xs text-muted">{{ fileTitle }}</div>
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
    },
};
</script>
