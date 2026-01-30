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
                        class="ml-auto rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                        type="button"
                        @click="openDetail(accreditation)"
                    >
                        Detail
                    </button>
                </div>
                <div class="grid gap-2 text-sm text-ink">
                    <div><span class="text-muted">Type:</span> {{ accreditation.type || '-' }}</div>
                    <div><span class="text-muted">Index:</span> {{ accreditation.idx || '-' }}</div>
                    <div><span class="text-muted">Parent Index:</span> {{ accreditation.parent_idx || '-' }}</div>
                    <div><span class="text-muted">Auth Type:</span> {{ accreditation.auth_type || '-' }}</div>
                    <div><span class="text-muted">Auth ID:</span> {{ accreditation.auth_id || '-' }}</div>
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
                            @show-detail="openDetail"
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
            </div>
        </Modal>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Modal from '../../components/Modal.vue';
import Repository from '../../repository';

const TreeItem = {
    name: 'TreeItem',
    props: {
        item: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            open: false,
        };
    },
    computed: {
        hasChildren() {
            return Array.isArray(this.item.children) && this.item.children.length > 0;
        },
    },
    template: `
        <div class="rounded-xl border border-border bg-white">
            <button
                class="flex w-full items-start gap-3 px-4 py-3 text-left"
                type="button"
                @click="open = !open"
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
                    <button
                        class="rounded-md border border-border px-2 py-1 text-[11px] text-muted"
                        type="button"
                        @click.stop="$emit('show-detail', item)"
                    >
                        Detail
                    </button>
                    <span v-if="hasChildren" class="text-xs text-muted">{{ open ? 'Hide' : 'Show' }}</span>
                </div>
            </button>
            <div v-if="open" class="border-t border-border px-4 py-3">
                <div v-if="item.content" class="mt-3 text-xs text-muted">Content</div>
                <p v-if="item.content" class="mt-1 text-sm text-ink ">{{ item.content }}</p>
                <div v-if="hasChildren" class="mt-4 grid gap-2">
                    <TreeItem
                        v-for="child in item.children"
                        :key="child.id || child.idx"
                        :item="child"
                        @show-detail="$emit('show-detail', $event)"
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
            detailModalOpen: false,
            detailItem: {},
        };
    },
    computed: {
        accreditationId() {
            return this.$route.params.id;
        },
    },
    created() {
        this.fetchAccreditation();
    },
    methods: {
        openDetail(item) {
            this.detailItem = item || {};
            this.detailModalOpen = true;
        },
        closeDetail() {
            this.detailModalOpen = false;
            this.detailItem = {};
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
                    this.treeItems = Array.isArray(result) ? result : [];
                })
                .catch(() => {
                    this.treeItems = [];
                })
                .finally(() => {
                    this.treeLoading = false;
                });
        },
    },
};
</script>
