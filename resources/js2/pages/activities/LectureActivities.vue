<template>
    <div class="flex flex-col gap-6">
        <div class="relative rounded-2xl border border-border bg-panel">
            <Loading :active="loadingSchedule" :is-full-page="false" />
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-border px-5 py-4">
                <div class="text-sm font-semibold text-ink">{{ title }}</div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="rounded-lg border border-border px-2 py-1 text-xs text-muted"
                        @click="shiftDate(-1)"
                    >
                        ‹
                    </button>
                    <div class="rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                        {{ formattedDate }}
                    </div>
                    <button
                        type="button"
                        class="rounded-lg border border-border px-2 py-1 text-xs text-muted"
                        @click="shiftDate(1)"
                    >
                        ›
                    </button>
                    <router-link
                        to="/blu/activities"
                        class="rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"
                    >
                        Semua agenda
                    </router-link>
                </div>
            </div>
            <div class="divide-y divide-border">
                <div
                    v-for="(schedule, index) in schedules"
                    :key="index"
                    class="flex items-center justify-between gap-4 px-5 py-4 text-sm"
                >
                    <div class="flex-1">
                        <div class="font-semibold text-ink">{{ schedule.name }}</div>
                        <div class="text-xs text-muted">
                            <span v-if="schedule.speaker"><b>{{ schedule.speaker }}:</b> </span>
                            <span>{{ schedule.title }}</span>
                        </div>
                        <div v-if="schedule.absence" class="mt-1 text-xs text-emerald-600">
                            Anda check-in pada {{ formatDateTime(schedule.absence.created_at) }}
                        </div>
                        <div v-if="schedule.start_date || schedule.end_date" class="mt-1 text-xs text-muted">
                            <span v-if="schedule.start_date">Mulai: {{ formatDateTime(schedule.start_date) }}</span>
                            <span v-if="schedule.end_date"> • Selesai: {{ formatDateTime(schedule.end_date) }}</span>
                        </div>
                    </div>
                    <button
                        v-if="!schedule.absence"
                        type="button"
                        class="rounded-lg bg-emerald-600 px-3 py-1 text-xs font-semibold text-white"
                        @click="openPresenceModal(schedule)"
                    >
                        Check In
                    </button>
                </div>
                <div v-if="!schedules.length && !loadingSchedule" class="px-5 py-6 text-sm text-muted">
                    Tidak ada agenda.
                </div>
            </div>
        </div>
        <div class="relative rounded-2xl border border-border bg-panel">
            <Loading :active="loadingTasks" :is-full-page="false" />
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-border px-5 py-4">
                <div class="text-sm font-semibold text-ink">{{ taskTitle }}</div>
                <div class="rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                    {{ tasks.length }} kegiatan
                </div>
            </div>
            <div class="divide-y divide-border">
                <div
                    v-for="task in tasks"
                    :key="task.id"
                    class="flex flex-wrap items-start justify-between gap-4 px-5 py-4 text-sm"
                >
                    <div class="flex-1">
                        <div
                            v-if="task.stase_task && task.stase_task.stase"
                            class="flex flex-wrap items-center gap-2 text-xs"
                        >
                            <span class="rounded-full bg-sky-100 px-2 py-0.5 font-semibold text-sky-600">
                                #{{ task.stase_task.stase.name }}
                            </span>
                        </div>
                        <div v-if="task.student" class="mt-1 font-semibold text-ink">
                            {{ task.student.name }}
                        </div>
                        <div class="text-xs text-muted">
                            <span v-if="task.stase_task">{{ task.stase_task.name }}</span>
                            <span v-if="task.title">: {{ task.title }}</span>
                        </div>
                        <div v-if="task.validated_at" class="mt-1 text-xs text-emerald-600">
                            Tervalidasi pada {{ formatDateTime(task.validated_at) }}
                        </div>
                    </div>
                    <div class="flex min-w-[110px] flex-col items-end gap-2 text-right">
                        <div v-if="task.data" class="text-2xl font-semibold text-emerald-600">
                            {{ task.data.point_average }}
                        </div>
                        <span
                            v-if="!isValidated(task)"
                            class="cursor-not-allowed rounded-lg border border-border bg-slate-100 px-3 py-1.5 text-xs font-semibold text-muted"
                        >
                            Belum terverifikasi
                        </span>
                        <router-link
                            v-else-if="!task.data"
                            :to="`/blu/task-scoring/${task.id}`"
                            class="rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white"
                        >
                            Nilai
                        </router-link>
                    </div>
                </div>
                <div v-if="!tasks.length && !loadingTasks" class="px-5 py-6 text-sm text-muted">
                    Tidak ada kegiatan stase.
                </div>
            </div>
        </div>
        <Modal
            :open="presenceModalOpen"
            title="Detail Agenda"
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
                    <span v-if="selectedSchedule.start_date">Mulai: {{ formatDateTime(selectedSchedule.start_date) }}</span>
                    <span v-if="selectedSchedule.end_date"> • Selesai: {{ formatDateTime(selectedSchedule.end_date) }}</span>
                </div>
                <div v-if="isLatePresence" class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
                    Check-in terlambat akan mengirim notifikasi ke pembuat agenda.
                </div>
                <div v-if="selectedSchedule.absence" class="text-xs text-emerald-600">
                    Anda check-in pada {{ formatDateTime(selectedSchedule.absence.created_at) }}
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
                    Batal
                </button>
                <button
                    class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white"
                    type="button"
                    :disabled="presenceSubmitting || !selectedSchedule"
                    @click="submitPresence(selectedSchedule && selectedSchedule.id)"
                >
                    {{ presenceSubmitting ? 'Menyimpan...' : 'Check In' }}
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
    computed: {
        title() {
            return this.isToday ? 'Agenda Hari Ini' : 'Agenda';
        },
        taskTitle() {
            return this.isToday ? 'Kegiatan Stase Hari Ini' : 'Kegiatan Stase';
        },
        formattedDate() {
            const date = this.parseDate(this.agendaDate) || new Date();
            return date.toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'short',
                day: '2-digit',
            });
        },
        isToday() {
            if (!this.agendaDate) {
                return true;
            }
            const today = this.getDateString(new Date());
            return this.agendaDate === today;
        },
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
            tasks: [],
            loadingTasks: false,
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
        this.loadTasks();
    },
    methods: {
        /**
         * Scoring stays locked until the student's attendance on the task has
         * been verified (QR/code/manual), which is what stamps validated_at.
         */
        isValidated(task) {
            return !!(task && task.validated_at);
        },
        parseDate(value) {
            if (!value) {
                return null;
            }
            const date = new Date(`${value}T00:00:00`);
            if (Number.isNaN(date.getTime())) {
                return null;
            }
            return date;
        },
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
        shiftDate(amount) {
            const base = this.parseDate(this.agendaDate) || new Date();
            base.setDate(base.getDate() + amount);
            this.handleAgendaDateChange(this.getDateString(base));
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
        loadTasks(date = this.agendaDate) {
            this.loadingTasks = true;
            // per_page is generous because a single planned day is short and
            // the card is not paginated; the endpoint scopes to this lecture.
            return Repository.get('/api/open-stase-tasks', {
                params: {
                    date,
                    per_page: 100,
                },
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.tasks = result && Array.isArray(result.data) ? result.data : [];
                })
                .catch(() => {
                    this.tasks = [];
                })
                .finally(() => {
                    this.loadingTasks = false;
                });
        },
        handleAgendaDateChange(value) {
            this.agendaDate = value;
            this.loadSchedule(value);
            this.loadTasks(value);
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
                        : 'Gagal check-in.';
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
            return date.toLocaleString('id-ID', {
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
