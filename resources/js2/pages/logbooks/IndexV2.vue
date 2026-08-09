<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Logbook Monitoring</div>
                <h1 class="text-2xl font-semibold text-ink">Logbook Daily</h1>
            </div>
            <router-link
                class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                to="/blu/logbook-student-add"
            >
                Add Logbook
            </router-link>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <label class="text-xs text-muted">Tanggal</label>
                    <input
                        v-model="filters.date"
                        type="date"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        @change="applyFilter"
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
                <div class="font-semibold">Logbooks</div>
                <div class="text-xs text-muted" v-if="pagination.total">
                    {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }}
                </div>
            </div>
            <div v-if="errorMessage" class="border-b border-rose-100 bg-rose-50 px-5 py-3 text-xs text-rose-600">
                {{ errorMessage }}
            </div>
            <div v-if="!loading && logbooks.length === 0" class="px-5 py-6 text-sm text-muted">
                No logbooks found.
            </div>

            <div v-if="logbooks.length" class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-[11px] uppercase tracking-[0.2em] text-muted">
                        <tr>
                            <th class="w-12 px-5 py-3">No</th>
                            <th class="px-5 py-3">Tanggal / No Catatan Medik</th>
                            <th class="px-5 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="(logbook, index) in logbooks" :key="logbook.id">
                            <td class="px-5 py-3 text-muted">
                                {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                            </td>
                            <td class="px-5 py-3">
                                <div class="font-semibold text-ink">{{ logbook.date || '-' }}</div>
                                <div class="text-xs text-muted">{{ logbook.field_1 || '-' }}</div>
                            </td>
                            <td class="px-5 py-3">
                                <div class="relative" @click.stop>
                                    <button
                                        class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted hover:bg-slate-50"
                                        type="button"
                                        @click="toggleActionMenu(logbook.id)"
                                    >
                                        Action
                                    </button>
                                    <div
                                        v-if="actionMenuLogbookId === logbook.id"
                                        class="absolute right-0 top-full z-10 mt-2 w-36 overflow-hidden rounded-xl border border-border bg-white shadow-lg"
                                    >
                                        <button
                                            class="block w-full px-4 py-2 text-left text-xs text-muted hover:bg-panel"
                                            type="button"
                                            @click="openView(logbook)"
                                        >
                                            View
                                        </button>
                                        <router-link
                                            class="block w-full px-4 py-2 text-left text-xs text-muted hover:bg-panel"
                                            :to="`/blu/logbook-student-add/${logbook.id}`"
                                        >
                                            Edit
                                        </router-link>
                                        <button
                                            class="block w-full px-4 py-2 text-left text-xs text-rose-600 hover:bg-rose-50"
                                            type="button"
                                            @click="actionMenuLogbookId = null; deleteLogbook(logbook)"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
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

        <section class="rounded-2xl border border-border bg-panel">
            <button
                type="button"
                class="flex w-full items-center justify-between px-6 py-4 text-left"
                @click="competenceOpen = !competenceOpen"
            >
                <span class="text-sm font-semibold text-ink">Kompetensi</span>
                <Icon :icon="competenceOpen ? 'mdi:chevron-up' : 'mdi:chevron-down'" class="h-5 w-5 text-muted" />
            </button>
            <div v-show="competenceOpen" class="border-t border-border px-6 py-4">
                <div v-if="competenceLoading" class="text-xs text-muted">Loading...</div>
                <div v-else-if="!competenceOptions.length" class="text-xs text-muted">Tidak ada data kompetensi.</div>
                <div v-else class="grid gap-6">
                    <div v-for="(items, category) in groupedCompetence" :key="category">
                        <div class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-muted">
                            {{ category }}
                        </div>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <div
                                v-for="option in items"
                                :key="option.id"
                                class="flex items-center justify-between gap-2 text-sm"
                            >
                                <span>{{ option.name }}</span>
                                <span class="text-xs text-muted">{{ option.count || 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <Modal :open="modalOpen" title="Logbook Detail" eyebrow="View entry" size="lg" @close="closeModal">
            <div v-if="selectedLogbook" class="grid gap-4 text-sm">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="grid gap-1">
                        <div class="text-xs text-muted">Tanggal</div>
                        <div class="font-medium text-ink">{{ selectedLogbook.date || '-' }}</div>
                    </div>
                    <div class="grid gap-1">
                        <div class="text-xs text-muted">Dosen</div>
                        <div class="font-medium text-ink">
                            {{ selectedLogbook.lecture ? selectedLogbook.lecture.name : '-' }}
                        </div>
                    </div>
                </div>
                <div class="grid gap-1">
                    <div class="text-xs text-muted">No Catatan Medik</div>
                    <div class="whitespace-pre-wrap rounded-xl border border-border bg-panel px-3 py-2 text-ink">
                        {{ selectedLogbook.field_1 || '-' }}
                    </div>
                </div>
                <div class="grid gap-1">
                    <div class="text-xs text-muted">Rawat Inap</div>
                    <div class="whitespace-pre-wrap rounded-xl border border-border bg-panel px-3 py-2 text-ink">
                        {{ selectedLogbook.field_2 || '-' }}
                    </div>
                </div>
                <div class="grid gap-1">
                    <div class="text-xs text-muted">Rawat Jalan Poli/UGD</div>
                    <div class="whitespace-pre-wrap rounded-xl border border-border bg-panel px-3 py-2 text-ink">
                        {{ selectedLogbook.field_3 || '-' }}
                    </div>
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
import persistFilters from '../../mixins/persistFilters';
import { Icon } from '../../icons';

export default {
    components: {
        Modal,
        Loading,
        Icon,
    },
    mixins: [persistFilters('logbooks-daily')],
    data() {
        return {
            baseUrl: '/api/logbooks',
            logbooks: [],
            pagination: {},
            loading: false,
            errorMessage: '',
            filters: {
                type: 'logbook-daily',
                date: '',
                page: 1,
            },
            modalOpen: false,
            selectedLogbook: null,
            actionMenuLogbookId: null,
            competenceOpen: true,
            competenceLoading: false,
            competenceOptions: [],
        };
    },
    computed: {
        groupedCompetence() {
            const groups = {};
            this.competenceOptions.forEach((option) => {
                const category = option.desc || 'Lainnya';
                if (!groups[category]) {
                    groups[category] = [];
                }
                groups[category].push(option);
            });
            return groups;
        },
    },
    created() {
        this.fetchLogbooks();
        this.fetchCompetenceOptions();
    },
    mounted() {
        document.addEventListener('click', this.handleOutsideClick);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.handleOutsideClick);
    },
    methods: {
        handleOutsideClick() {
            this.actionMenuLogbookId = null;
        },
        toggleActionMenu(logbookId) {
            this.actionMenuLogbookId = this.actionMenuLogbookId === logbookId ? null : logbookId;
        },
        fetchCompetenceOptions() {
            this.competenceLoading = true;

            return Repository.get('/api/logbook-student-competence')
                .then((response) => {
                    const data = response && response.data ? response.data.result : null;
                    this.competenceOptions = Array.isArray(data) ? data : [];
                })
                .catch(() => {
                    this.competenceOptions = [];
                })
                .finally(() => {
                    this.competenceLoading = false;
                });
        },
        fetchLogbooks() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(this.baseUrl, { params: this.filters })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];

                    this.logbooks = data;
                    this.pagination = result || {};
                })
                .catch(() => {
                    this.logbooks = [];
                    this.pagination = {};
                    this.errorMessage = 'Failed to load logbooks.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        applyFilter() {
            this.filters.page = 1;
            this.fetchLogbooks();
        },
        resetFilter() {
            this.filters.date = '';
            this.filters.page = 1;
            this.fetchLogbooks();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchLogbooks();
        },
        openView(logbook) {
            this.actionMenuLogbookId = null;
            this.selectedLogbook = logbook || null;
            this.modalOpen = true;
        },
        closeModal() {
            this.modalOpen = false;
            this.selectedLogbook = null;
        },
        deleteLogbook(logbook) {
            if (!window.confirm(`Delete logbook entry for ${logbook.date || 'this date'}?`)) {
                return;
            }

            Repository.delete(`${this.baseUrl}/${logbook.id}`)
                .then(() => {
                    this.$showToast('Logbook deleted successfully.');
                    this.fetchLogbooks();
                })
                .catch(() => {
                    this.errorMessage = 'Failed to delete logbook.';
                });
        },
    },
};
</script>
