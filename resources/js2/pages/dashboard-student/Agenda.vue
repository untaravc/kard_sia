<template>
    <div class="flex flex-col gap-6">
        <AgendaCard
            :schedules="schedules"
            :loading="loadingSchedule"
            :date="agendaDate"
            @date-change="handleAgendaDateChange"
            @open-presence="openPresenceModal"
        />
        <Modal
            :open="presenceModalOpen"
            title="Agenda Details"
            eyebrow="Check In"
            size="md"
            @close="closePresenceModal"
        >
            <div v-if="selectedSchedule" class="grid gap-3 text-sm">
                <div class="rounded-xl border border-border bg-white px-4 py-3">
                    <div class="font-semibold text-ink">{{ selectedSchedule.name }}</div>
                    <div class="text-xs text-muted">
                        <span v-if="selectedSchedule.speaker"><b>{{ selectedSchedule.speaker }}:</b> </span>
                        <span>{{ selectedSchedule.title }}</span>
                    </div>
                </div>
                <div v-if="selectedSchedule.start_date || selectedSchedule.end_date" class="text-xs text-muted">
                    <span v-if="selectedSchedule.start_date">Start: {{ formatDateTime(selectedSchedule.start_date) }}</span>
                    <span v-if="selectedSchedule.end_date"> • End: {{ formatDateTime(selectedSchedule.end_date) }}</span>
                </div>
                <div v-if="isLatePresence" class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
                    A late check-in will send a notification to the agenda creator.
                </div>
                <div v-if="selectedSchedule.absence" class="text-xs text-emerald-600">
                    You checked in at {{ formatDateTime(selectedSchedule.absence.created_at) }}
                </div>
                <div v-if="presenceError" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ presenceError }}
                </div>
            </div>
            <template #footer>
                <button
                    class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                    type="button"
                    @click="closePresenceModal"
                >
                    Cancel
                </button>
                <button
                    class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white"
                    type="button"
                    :disabled="presenceSubmitting || !selectedSchedule"
                    @click="submitPresence(selectedSchedule && selectedSchedule.id)"
                >
                    {{ presenceSubmitting ? 'Saving...' : 'Check In' }}
                </button>
            </template>
        </Modal>
    </div>
</template>

<script>
import Repository from '../../repository';
import AgendaCard from './AgendaCard.vue';
import Modal from '../../components/Modal.vue';

export default {
    components: {
        AgendaCard,
        Modal,
    },
    computed: {
        isLatePresence() {
            if (!this.selectedSchedule || !this.selectedSchedule.end_date) {
                return false;
            }
            if (this.selectedSchedule.absence) {
                return false;
            }
            return this.isAfterDate(this.selectedSchedule.end_date);
        },
    },
    data() {
        return {
            schedules: [],
            loadingSchedule: false,
            agendaDate: '',
            presenceModalOpen: false,
            presenceSubmitting: false,
            presenceError: '',
            selectedSchedule: null,
        };
    },
    created() {
        this.agendaDate = this.getDateString(new Date());
        this.loadSchedule();
    },
    methods: {
        isAfterDate(value) {
            if (!value) {
                return false;
            }
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return false;
            }
            return Date.now() > date.getTime();
        },
        getDateString(date) {
            const year = date.getFullYear();
            const month = `${date.getMonth() + 1}`.padStart(2, '0');
            const day = `${date.getDate()}`.padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        loadSchedule(date = this.agendaDate) {
            this.loadingSchedule = true;
            return Repository.get('/api/activities-today', {
                params: {
                    date,
                },
            })
                .then((response) => {
                    const data = response && response.data ? response.data.result : [];
                    this.schedules = Array.isArray(data) ? data : [];
                })
                .catch(() => {
                    this.schedules = [];
                })
                .finally(() => {
                    this.loadingSchedule = false;
                });
        },
        handleAgendaDateChange(value) {
            this.agendaDate = value;
            this.loadSchedule(value);
        },
        openPresenceModal(schedule) {
            this.selectedSchedule = schedule || null;
            this.presenceError = '';
            this.presenceModalOpen = true;
        },
        closePresenceModal() {
            this.presenceModalOpen = false;
            this.presenceError = '';
            this.presenceSubmitting = false;
            this.selectedSchedule = null;
        },
        submitPresence(activityId) {
            if (!activityId || this.presenceSubmitting) {
                return;
            }
            this.presenceSubmitting = true;
            this.presenceError = '';
            return Repository.post(`/api/activity-presence/${activityId}`)
                .then((response) => {
                    const presence = response && response.data ? response.data.result : null;
                    if (presence) {
                        this.selectedSchedule.absence = presence;
                        const index = this.schedules.findIndex((item) => item && item.id === this.selectedSchedule.id);
                        if (index !== -1) {
                            this.schedules[index].absence = presence;
                        }
                    }
                    this.presenceModalOpen = false;
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to check in.';
                    this.presenceError = message;
                })
                .finally(() => {
                    this.presenceSubmitting = false;
                });
        },
        formatDateTime(value) {
            if (!value) {
                return '';
            }
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return value;
            }
            return date.toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
            });
        },
    },
};
</script>
