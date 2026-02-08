<template>
    <div class="grid gap-6">
        <section class="rounded-2xl border border-border bg-panel p-6 shadow-sm">
        <Loading :active="loading" :is-full-page="false" />
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold text-ink">Stase</h1>
                <p class="mt-1 text-sm text-muted">Available stase on the left, taken stase on the right.</p>
            </div>
        </div>
        <div v-if="errorMessage"
            class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
            {{ errorMessage }}
        </div>
        <div class="mt-5 grid gap-6 lg:grid-cols-2">
            <div>
                <div class="text-sm font-semibold text-ink">Available Stase</div>
                <div class="mt-3 grid max-h-[60vh] gap-3 overflow-y-auto pr-1">
                    <div v-for="stase in availableStase" :key="stase.id"
                        class="rounded-xl border border-border/60 bg-white px-4 py-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full"
                                    :style="{ backgroundColor: stase.color || '#cbd5f5' }"></span>
                                <div class="text-sm font-semibold text-ink">{{ stase.name }}</div>
                            </div>
                            <button
                                class="rounded-lg border border-border px-2 py-1 text-[11px] font-semibold text-muted"
                                type="button" @click="openTakeModal(stase)">
                                Take Stase
                            </button>
                        </div>
                        <div v-if="stase.alias" class="mt-1 text-xs text-muted">{{ stase.alias }}</div>
                    </div>
                    <div v-if="!loading && availableStase.length === 0" class="text-xs text-muted">
                        No available stase.
                    </div>
                </div>
            </div>
            <div>
                <div class="text-sm font-semibold text-ink">Taken Stase</div>
                <div class="mt-3 grid max-h-[60vh] gap-3 overflow-y-auto pr-1">
                    <div v-for="log in takenStase" :key="log.id"
                        class="rounded-xl border border-border/60 bg-white px-4 py-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="text-sm font-semibold text-ink">
                                {{ (log.stase && log.stase.name) || 'Stase' }}
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    class="rounded-lg border border-border px-2 py-1 text-[11px] font-semibold text-muted"
                                    type="button"
                                    @click="openEditModal(log)"
                                >
                                    Edit
                                </button>
                                <router-link
                                    class="rounded-lg border border-border px-2 py-1 text-[11px] font-semibold text-muted"
                                    :to="`/blu/dashboard-student/scoring/${log.id}`"
                                >
                                    Detail
                                </router-link>
                            </div>
                        </div>
                        <div v-if="log.start_date || log.end_date" class="mt-1 text-xs text-muted">
                            <span v-if="log.start_date">Start: {{ log.start_date }}</span>
                            <span v-if="log.end_date"> - End: {{ log.end_date }}</span>
                        </div>
                    </div>
                    <div v-if="!loading && takenStase.length === 0" class="text-xs text-muted">
                        No taken stase.
                    </div>
                </div>
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
            <button class="rounded-xl border border-border px-4 py-2 text-sm text-muted" type="button"
                @click="closeTakeModal">
                Cancel
            </button>
            <button class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white" type="button"
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
            <button class="rounded-xl border border-border px-4 py-2 text-sm text-muted" type="button"
                @click="closeEditModal">
                Cancel
            </button>
            <button class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white" type="button"
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

export default {
    components: {
        Loading,
        Modal,
    },
    data() {
        return {
            availableStase: [],
            takenStase: [],
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
    created() {
        this.fetchStudentStase();
    },
    methods: {
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
