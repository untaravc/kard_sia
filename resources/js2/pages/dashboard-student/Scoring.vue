<template>
    <div class="grid gap-4">
        <section class="relative rounded-2xl border border-border bg-panel p-4 shadow-sm sm:p-6">
        <Loading :active="loading" :is-full-page="false" />
        <div>
            <h1 class="text-lg font-semibold text-ink">Stase</h1>
            <p class="mt-1 text-sm text-muted">Take a new stase, or open one you already have.</p>
        </div>
        <div v-if="errorMessage"
            class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
            {{ errorMessage }}
        </div>
        <div class="mt-4 flex gap-1 rounded-xl bg-slate-100 p-1">
            <button
                type="button"
                class="flex min-h-[40px] flex-1 items-center justify-center gap-1.5 rounded-lg text-sm font-semibold transition"
                :class="activeTab === 'available' ? 'bg-white text-ink shadow-sm' : 'text-muted active:bg-white/60'"
                @click="setActiveTab('available')"
            >
                Available
                <span
                    class="rounded-full px-1.5 text-[11px] font-semibold leading-5"
                    :class="activeTab === 'available' ? 'bg-primary/10 text-primary' : 'bg-white text-muted'"
                >
                    {{ availableStase.length }}
                </span>
            </button>
            <button
                type="button"
                class="flex min-h-[40px] flex-1 items-center justify-center gap-1.5 rounded-lg text-sm font-semibold transition"
                :class="activeTab === 'taken' ? 'bg-white text-ink shadow-sm' : 'text-muted active:bg-white/60'"
                @click="setActiveTab('taken')"
            >
                Taken
                <span
                    class="rounded-full px-1.5 text-[11px] font-semibold leading-5"
                    :class="activeTab === 'taken' ? 'bg-primary/10 text-primary' : 'bg-white text-muted'"
                >
                    {{ takenStase.length }}
                </span>
            </button>
        </div>
        <input
            v-model.trim="search"
            type="text"
            :placeholder="activeTab === 'available' ? 'Search available stase...' : 'Search taken stase...'"
            class="mt-3 w-full rounded-xl border border-border bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
        />
        <div v-if="activeTab === 'available'" class="mt-3 grid gap-2.5">
            <div v-for="stase in filteredAvailableStase" :key="stase.id"
                class="flex items-center justify-between gap-3 rounded-xl border border-border/60 bg-white p-3">
                <div class="flex min-w-0 items-start gap-2">
                    <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full"
                        :style="{ backgroundColor: stase.color || '#cbd5f5' }"></span>
                    <div class="min-w-0">
                        <div class="text-sm font-semibold leading-snug text-ink break-words">{{ stase.name }}</div>
                        <div v-if="stase.alias" class="mt-0.5 text-xs text-muted break-words">{{ stase.alias }}</div>
                    </div>
                </div>
                <button
                    class="inline-flex min-h-[38px] shrink-0 items-center gap-1 whitespace-nowrap rounded-lg border border-primary/30 bg-primary/5 pl-2 pr-3 text-xs font-semibold text-primary transition active:bg-primary/10"
                    type="button" @click="openTakeModal(stase)">
                    <Icon icon="mdi:plus" class="h-4 w-4" />
                    Take
                </button>
            </div>
            <div v-if="!loading && filteredAvailableStase.length === 0"
                class="rounded-xl border border-dashed border-border py-8 text-center text-xs text-muted">
                {{ search ? 'No matching stase.' : 'No available stase.' }}
            </div>
        </div>
        <div v-else class="mt-3 grid gap-2.5">
            <div v-for="log in filteredTakenStase" :key="log.id"
                class="rounded-xl border border-border/60 bg-white p-3">
                <div class="min-w-0">
                    <div class="text-sm font-semibold leading-snug text-ink break-words">
                        {{ (log.stase && log.stase.name) || 'Stase' }}
                    </div>
                    <div v-if="log.start_date || log.end_date" class="mt-0.5 text-xs text-muted">
                        <span v-if="log.start_date">{{ log.start_date }}</span>
                        <span v-if="log.start_date && log.end_date"> - </span>
                        <span v-if="log.end_date">{{ log.end_date }}</span>
                    </div>
                </div>
                <div class="mt-2.5 flex gap-2 sm:justify-end">
                    <button
                        class="inline-flex min-h-[38px] flex-1 items-center justify-center gap-1 rounded-lg border border-border text-xs font-semibold text-muted transition active:bg-slate-100 sm:flex-none sm:px-4"
                        type="button"
                        @click="openEditModal(log)"
                    >
                        <Icon icon="mdi:calendar-edit" class="h-4 w-4" />
                        Edit
                    </button>
                    <router-link
                        class="inline-flex min-h-[38px] flex-1 items-center justify-center gap-1 rounded-lg bg-primary text-xs font-semibold text-white transition active:opacity-90 sm:flex-none sm:px-4"
                        :to="`${detailBasePath}/${log.id}`"
                    >
                        Detail
                        <Icon icon="mdi:chevron-right" class="h-4 w-4" />
                    </router-link>
                </div>
            </div>
            <div v-if="!loading && filteredTakenStase.length === 0"
                class="rounded-xl border border-dashed border-border py-8 text-center text-xs text-muted">
                {{ search ? 'No matching stase.' : 'No taken stase.' }}
            </div>
        </div>
        </section>
        <Modal :open="takeModalOpen" title="Take Stase" eyebrow="Set schedule" size="md" @close="closeTakeModal">
        <div class="grid gap-4 text-sm">
            <div class="rounded-xl border border-border bg-white px-4 py-3">
                <div class="text-sm font-semibold text-ink">{{ selectedStase ? selectedStase.name : 'Stase' }}</div>
                <div v-if="selectedStase && selectedStase.alias" class="text-xs text-muted">
                    {{ selectedStase.alias }}
                </div>
            </div>
            <label class="grid gap-2 text-sm">
                <span class="text-muted">Start date</span>
                <input v-model="takeForm.start_date" type="date"
                    class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
            </label>
            <label class="grid gap-2 text-sm">
                <span class="text-muted">End date</span>
                <input v-model="takeForm.end_date" type="date"
                    class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
            </label>
            <div v-if="takeError" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                {{ takeError }}
            </div>
        </div>
        <template #footer>
            <button class="min-h-[44px] flex-1 rounded-xl border border-border px-4 text-sm text-muted transition active:bg-slate-100 sm:flex-none" type="button"
                @click="closeTakeModal">
                Cancel
            </button>
            <button class="min-h-[44px] flex-1 rounded-xl bg-primary px-4 text-sm font-medium text-white transition active:opacity-90 disabled:opacity-60 sm:flex-none" type="button"
                :disabled="takeSubmitting || !selectedStase" @click="submitTakeStase">
                {{ takeSubmitting ? 'Saving...' : 'Save' }}
            </button>
        </template>
        </Modal>
        <Modal :open="editModalOpen" title="Edit Stase" eyebrow="Update schedule" size="md" @close="closeEditModal">
        <div class="grid gap-4 text-sm">
            <div class="rounded-xl border border-border bg-white px-4 py-3">
                <div class="text-sm font-semibold text-ink">
                    {{ selectedStaseLog && selectedStaseLog.stase ? selectedStaseLog.stase.name : 'Stase' }}
                </div>
                <div v-if="selectedStaseLog && selectedStaseLog.stase && selectedStaseLog.stase.alias" class="text-xs text-muted">
                    {{ selectedStaseLog.stase.alias }}
                </div>
            </div>
            <label class="grid gap-2 text-sm">
                <span class="text-muted">Start date</span>
                <input v-model="editForm.start_date" type="date"
                    class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
            </label>
            <label class="grid gap-2 text-sm">
                <span class="text-muted">End date</span>
                <input v-model="editForm.end_date" type="date"
                    class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
            </label>
            <div v-if="editError" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                {{ editError }}
            </div>
        </div>
        <template #footer>
            <button class="min-h-[44px] flex-1 rounded-xl border border-border px-4 text-sm text-muted transition active:bg-slate-100 sm:flex-none" type="button"
                @click="closeEditModal">
                Cancel
            </button>
            <button class="min-h-[44px] flex-1 rounded-xl bg-primary px-4 text-sm font-medium text-white transition active:opacity-90 disabled:opacity-60 sm:flex-none" type="button"
                :disabled="editSubmitting || !selectedStaseLog" @click="submitEditStase">
                {{ editSubmitting ? 'Saving...' : 'Save' }}
            </button>
        </template>
        </Modal>
    </div>
</template>

<script>
import Repository from '../../repository';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Modal from '../../components/Modal.vue';
import { Icon } from '../../icons';
import { useAppSettingsStore } from '../../stores/appSettings';

export default {
    components: {
        Loading,
        Modal,
        Icon,
    },
    data() {
        return {
            availableStase: [],
            takenStase: [],
            activeTab: 'available',
            search: '',
            scoringListVersion: '1',
            loading: false,
            errorMessage: '',
            takeModalOpen: false,
            takeSubmitting: false,
            takeError: '',
            selectedStase: null,
            takeForm: {
                start_date: '',
                end_date: '',
            },
            editModalOpen: false,
            editSubmitting: false,
            editError: '',
            selectedStaseLog: null,
            editForm: {
                start_date: '',
                end_date: '',
            },
        };
    },
    computed: {
        // 'app.version-scoring-list' picks which stase detail page the list
        // links to: 2 is the variant that carries attendance confirmation.
        detailBasePath() {
            return String(this.scoringListVersion) === '2'
                ? '/blu/dashboard-student/scoring-v2'
                : '/blu/dashboard-student/scoring';
        },
        filteredAvailableStase() {
            const query = this.search.toLowerCase();
            if (!query) {
                return this.availableStase;
            }
            return this.availableStase.filter((stase) => (stase.name || '').toLowerCase().includes(query));
        },
        filteredTakenStase() {
            const query = this.search.toLowerCase();
            if (!query) {
                return this.takenStase;
            }
            return this.takenStase.filter((log) => {
                const name = log.stase && log.stase.name ? log.stase.name : '';
                return name.toLowerCase().includes(query);
            });
        },
    },
    created() {
        this.fetchScoringListVersion();
        this.fetchStudentStase();
    },
    methods: {
        setActiveTab(tab) {
            if (this.activeTab === tab) {
                return;
            }
            this.activeTab = tab;
            this.search = '';
        },
        fetchScoringListVersion() {
            const appSettingsStore = useAppSettingsStore();
            return appSettingsStore.fetchSetting('app.version-scoring-list').then((value) => {
                this.scoringListVersion = value || '1';
                return value;
            });
        },
        fetchStudentStase() {
            this.loading = true;
            this.errorMessage = '';
            return Repository.get('/api/student-stase')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.availableStase = result && Array.isArray(result.available_stase)
                        ? result.available_stase
                        : [];
                    this.takenStase = result && Array.isArray(result.taken_stase) ? result.taken_stase : [];
                })
                .catch(() => {
                    this.availableStase = [];
                    this.takenStase = [];
                    this.errorMessage = 'Failed to load stase list.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        openTakeModal(stase) {
            this.selectedStase = stase || null;
            this.takeForm = {
                start_date: '',
                end_date: '',
            };
            this.takeError = '';
            this.takeModalOpen = true;
        },
        closeTakeModal() {
            this.takeModalOpen = false;
            this.takeSubmitting = false;
            this.takeError = '';
            this.selectedStase = null;
        },
        submitTakeStase() {
            if (!this.selectedStase || this.takeSubmitting) {
                return;
            }
            this.takeSubmitting = true;
            this.takeError = '';
            return Repository.post('/api/student-stase', {
                stase_id: this.selectedStase.id,
                start_date: this.takeForm.start_date,
                end_date: this.takeForm.end_date,
            })
                .then(() => {
                    this.closeTakeModal();
                    this.fetchStudentStase();
                    this.setActiveTab('taken');
                    this.$showToast('Stase saved.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to save stase.';
                    this.takeError = message;
                })
                .finally(() => {
                    this.takeSubmitting = false;
                });
        },
        openEditModal(log) {
            this.selectedStaseLog = log || null;
            this.editForm = {
                start_date: log && log.start_date ? log.start_date : '',
                end_date: log && log.end_date ? log.end_date : '',
            };
            this.editError = '';
            this.editSubmitting = false;
            this.editModalOpen = true;
        },
        closeEditModal() {
            this.editModalOpen = false;
            this.editSubmitting = false;
            this.editError = '';
            this.selectedStaseLog = null;
        },
        submitEditStase() {
            if (!this.selectedStaseLog || this.editSubmitting) {
                return;
            }
            this.editSubmitting = true;
            this.editError = '';
            return Repository.patch(`/api/student-stase/${this.selectedStaseLog.id}`, {
                start_date: this.editForm.start_date,
                end_date: this.editForm.end_date,
            })
                .then(() => {
                    this.closeEditModal();
                    this.fetchStudentStase();
                    this.$showToast('Stase updated.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to update stase.';
                    this.editError = message;
                })
                .finally(() => {
                    this.editSubmitting = false;
                });
        },
    },
};
</script>
