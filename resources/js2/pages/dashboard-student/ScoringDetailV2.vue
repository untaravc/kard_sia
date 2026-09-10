<template>
    <div class="grid gap-4">
        <section class="relative rounded-2xl border border-border bg-panel p-4 shadow-sm sm:p-6">
        <Loading :active="loading" :is-full-page="false" />
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="text-[10px] uppercase tracking-[0.18em] text-muted">Stase Tasks</div>
                <h1 class="mt-0.5 text-base font-semibold leading-snug text-ink break-words sm:text-lg">
                    {{ staseTitle }}
                </h1>
                <div v-if="stasePeriod" class="mt-1 text-xs text-muted">
                    {{ stasePeriod }}
                </div>
            </div>
            <router-link
                class="inline-flex min-h-[38px] shrink-0 items-center gap-1 rounded-xl border border-border pl-2 pr-3 text-sm text-muted transition active:bg-slate-100"
                to="/blu/dashboard-student/scoring"
            >
                <Icon icon="mdi:chevron-left" class="h-4 w-4" />
                Back
            </router-link>
        </div>

        <div
            v-if="errorMessage"
            class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600"
        >
            {{ errorMessage }}
        </div>

        <div class="mt-4 grid gap-2.5">
            <div
                v-for="task in tasks"
                :key="task.id"
                class="rounded-xl border border-border/60 bg-white p-3 sm:p-4"
            >
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold leading-snug text-ink break-words">
                            {{ (task.task && task.task.name) || task.name || 'Task' }}
                        </div>
                        <div v-if="task.name && task.task && task.task.name" class="mt-0.5 text-xs text-muted break-words">
                            {{ task.name }}
                        </div>
                        <div v-if="task.lecture" class="mt-0.5 text-xs text-muted break-words">
                            {{ task.lecture.name || task.lecture.email || 'Lecture' }}
                        </div>
                    </div>
                    <button
                        class="inline-flex min-h-[36px] shrink-0 items-center gap-1 whitespace-nowrap rounded-lg border border-primary/30 bg-primary/5 pl-2 pr-3 text-xs font-semibold text-primary transition active:bg-primary/10"
                        type="button"
                        @click="openScoringModal(task)"
                    >
                        <Icon icon="mdi:plus" class="h-4 w-4" />
                        Scoring
                    </button>
                </div>
                <div
                    v-if="task.openStaseTasks && task.openStaseTasks.length"
                    class="mt-3 divide-y divide-emerald-100 rounded-xl border border-emerald-100 bg-emerald-50/70"
                >
                    <div v-for="openTask in task.openStaseTasks" :key="openTask.id" class="p-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <div
                                    v-if="openTask.title"
                                    class="text-[13px] font-semibold leading-snug text-emerald-900 break-words"
                                >
                                    {{ openTask.title }}
                                </div>
                                <div
                                    class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] text-emerald-700"
                                    :class="openTask.title ? 'mt-1.5' : ''"
                                >
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 font-semibold">
                                        {{ openTask.lecture_name || (openTask.lecture_id ? `Lecture #${openTask.lecture_id}` : 'Lecture') }}
                                    </span>
                                    <span v-if="openTask.plan">{{ openTask.plan }}</span>
                                    <span v-if="openTask.score" class="rounded-full bg-white px-2 py-0.5 font-semibold">
                                        Avg {{ openTask.score }}
                                    </span>
                                    <span
                                        v-if="openTask.validated_at"
                                        class="inline-flex items-center gap-1 rounded-full bg-sky-100 px-2 py-0.5 font-semibold text-sky-700"
                                    >
                                        <Icon icon="mdi:check-decagram" class="h-3 w-3" />
                                        Terkonfirmasi
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 font-semibold text-amber-700"
                                    >
                                        <Icon icon="mdi:clock-alert-outline" class="h-3 w-3" />
                                        Belum dikonfirmasi
                                    </span>
                                </div>
                            </div>
                            <div class="relative action-dropdown shrink-0">
                                <button
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-emerald-200 bg-white text-emerald-700 transition active:bg-emerald-50"
                                    type="button"
                                    aria-label="Actions"
                                    @click.stop="toggleActionMenu(openTask.id)"
                                >
                                    <Icon icon="mdi:dots-horizontal" class="h-5 w-5" />
                                </button>
                                <div
                                    v-if="actionMenuOpenId === openTask.id"
                                    class="absolute right-0 z-30 mt-1 w-52 origin-top-right rounded-xl border border-border bg-white p-1 shadow-xl"
                                >
                                    <button
                                        v-if="!openTask.validated_at"
                                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-left text-[13px] font-semibold text-primary transition active:bg-slate-100"
                                        type="button"
                                        @click="handleOpenTaskAction('confirmAttendance', openTask, task)"
                                    >
                                        <Icon icon="mdi:qrcode-scan" class="h-4 w-4 shrink-0" />
                                        Konfirmasi Agenda
                                    </button>
                                    <button
                                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-left text-[13px] text-ink transition active:bg-slate-100"
                                        type="button"
                                        @click="handleOpenTaskAction('notify', openTask, task)"
                                    >
                                        <Icon icon="mdi:bell-outline" class="h-4 w-4 shrink-0 text-muted" />
                                        Notify Lecture
                                    </button>
                                    <button
                                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-left text-[13px] text-ink transition active:bg-slate-100"
                                        type="button"
                                        @click="handleOpenTaskAction('uploadScore', openTask, task)"
                                    >
                                        <Icon icon="mdi:upload-outline" class="h-4 w-4 shrink-0 text-muted" />
                                        Upload Score
                                    </button>
                                    <button
                                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-left text-[13px] text-ink transition active:bg-slate-100"
                                        type="button"
                                        @click="handleOpenTaskAction('uploadTask', openTask, task)"
                                    >
                                        <Icon icon="mdi:paperclip" class="h-4 w-4 shrink-0 text-muted" />
                                        Upload Task
                                    </button>
                                    <button
                                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-left text-[13px] text-ink transition active:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
                                        type="button"
                                        :disabled="Boolean(openTask.score)"
                                        @click="handleOpenTaskAction('update', openTask, task)"
                                    >
                                        <Icon icon="mdi:pencil-outline" class="h-4 w-4 shrink-0 text-muted" />
                                        Update
                                    </button>
                                    <button
                                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-left text-[13px] text-rose-600 transition active:bg-rose-50 disabled:cursor-not-allowed disabled:opacity-40"
                                        type="button"
                                        :disabled="Boolean(openTask.score)"
                                        @click="handleOpenTaskAction('delete', openTask, task)"
                                    >
                                        <Icon icon="mdi:trash-can-outline" class="h-4 w-4 shrink-0" />
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-if="openTask.files && openTask.files.length" class="mt-2 flex flex-wrap gap-1.5">
                            <button
                                v-for="file in openTask.files"
                                :key="file.id"
                                class="inline-flex min-h-[32px] max-w-full items-center gap-1 rounded-lg border border-emerald-200 bg-white px-2.5 text-[11px] font-semibold text-emerald-700 transition active:bg-emerald-50"
                                type="button"
                                @click="openPreview(file)"
                            >
                                <Icon icon="mdi:file-document-outline" class="h-3.5 w-3.5 shrink-0" />
                                <span class="truncate">{{ file.title || 'Document' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div
                    v-if="task.staseTaskLogs && task.staseTaskLogs.length"
                    class="mt-2 divide-y divide-border/60 overflow-hidden rounded-xl border border-border/60 bg-white"
                >
                    <div v-for="log in task.staseTaskLogs" :key="log.id" class="p-3">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] text-muted">
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 font-semibold text-amber-800">
                                Score {{ log.point_average || 0 }}
                            </span>
                            <span v-if="log.date">{{ log.date }}</span>
                            <span v-if="log.lecture_name">• {{ log.lecture_name }}</span>
                        </div>
                        <div v-if="log.openStaseTasks && log.openStaseTasks.length" class="mt-2 grid gap-2">
                            <div
                                v-for="openTask in log.openStaseTasks"
                                :key="openTask.id"
                                class="rounded-lg bg-slate-50 px-2.5 py-2"
                            >
                                <div
                                    v-if="openTask.title"
                                    class="text-[12px] font-medium leading-snug text-ink break-words"
                                >
                                    {{ openTask.title }}
                                </div>
                                <div v-if="openTask.plan" class="mt-0.5 text-[11px] text-muted">
                                    {{ openTask.plan }}
                                </div>
                                <div v-if="openTask.files && openTask.files.length" class="mt-2 flex flex-wrap gap-1.5">
                                    <button
                                        v-for="file in openTask.files"
                                        :key="file.id"
                                        class="inline-flex min-h-[32px] max-w-full items-center gap-1 rounded-lg border border-emerald-200 bg-white px-2.5 text-[11px] font-semibold text-emerald-700 transition active:bg-emerald-50"
                                        type="button"
                                        @click="openPreview(file)"
                                    >
                                        <Icon icon="mdi:file-document-outline" class="h-3.5 w-3.5 shrink-0" />
                                        <span class="truncate">{{ file.title || 'Document' }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div
                v-if="!loading && tasks.length === 0"
                class="rounded-xl border border-dashed border-border py-8 text-center text-xs text-muted"
            >
                No tasks available.
            </div>
        </div>
        </section>
        <Modal
            :open="scoringModalOpen"
            title="Open Scoring"
            eyebrow="Create scoring"
            size="md"
            @close="closeScoringModal"
        >
            <div class="grid gap-4 text-sm">
                <div class="rounded-xl border border-border bg-white px-4 py-3">
                    <div class="text-sm font-semibold text-ink">
                        {{ selectedTaskTitle }}
                    </div>
                    <div v-if="selectedTaskLecture" class="mt-1 text-xs text-muted">
                        Lecture: {{ selectedTaskLecture }}
                    </div>
                </div>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Title</span>
                    <input
                        v-model.trim="scoringForm.title"
                        type="text"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Plan date <span class="text-rose-600">*</span></span>
                    <input
                        v-model="scoringForm.plan_date"
                        type="date"
                        class="w-full rounded-xl border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        :class="scoringFormErrors.plan_date ? 'border-rose-300' : 'border-border'"
                        @change="scoringFormErrors.plan_date = ''"
                    />
                    <span v-if="scoringFormErrors.plan_date" class="text-xs text-rose-600">
                        {{ scoringFormErrors.plan_date }}
                    </span>
                </label>
                <div class="grid gap-2">
                    <span class="text-muted text-xs">Lectures</span>
                    <div v-if="lecturesLoading" class="text-xs text-muted">Loading lectures...</div>
                    <template v-else>
                        <input
                            v-model.trim="lectureSearch"
                            type="text"
                            placeholder="Search lecture..."
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                        <div class="grid max-h-56 gap-3 overflow-y-auto pr-1 sm:grid-cols-2">
                            <label
                                v-for="lecture in filteredLectures"
                                :key="lecture.id"
                                class="flex items-center gap-2 rounded-xl border border-border bg-white px-3 py-2 text-xs text-muted shadow-sm"
                            >
                                <input
                                    v-model="scoringForm.lecture_ids"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-border text-primary focus:ring-primary/30"
                                    :value="lecture.id"
                                />
                                <span class="text-ink">{{ lecture.name }}</span>
                            </label>
                            <div v-if="filteredLectures.length === 0" class="text-xs text-muted">
                                {{ lectureSearch ? 'No matching lectures.' : 'No lectures available.' }}
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            <template #footer>
                <button
                    class="min-h-[44px] flex-1 rounded-xl border border-border px-4 text-sm text-muted transition active:bg-slate-100 sm:flex-none"
                    type="button"
                    @click="closeScoringModal"
                >
                    Cancel
                </button>
                <button
                    class="min-h-[44px] flex-1 rounded-xl bg-primary px-4 text-sm font-medium text-white transition active:opacity-90 disabled:opacity-60 sm:flex-none"
                    type="button"
                    @click="submitScoring"
                >
                    Save
                </button>
            </template>
        </Modal>
        <Modal
            :open="updateModalOpen"
            title="Update Open Scoring"
            eyebrow="Edit scoring"
            size="md"
            @close="closeUpdateModal"
        >
            <div class="grid gap-4 text-sm">
                <div class="rounded-xl border border-border bg-white px-4 py-3">
                    <div class="text-sm font-semibold text-ink">
                        {{ updateForm.title || selectedOpenTaskTitle }}
                    </div>
                    <div v-if="selectedOpenTaskLecture" class="mt-1 text-xs text-muted">
                        Lecture: {{ selectedOpenTaskLecture }}
                    </div>
                </div>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Title</span>
                    <input
                        v-model.trim="updateForm.title"
                        type="text"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Plan date</span>
                    <input
                        v-model="updateForm.plan"
                        type="date"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
            </div>
            <template #footer>
                <button
                    class="min-h-[44px] flex-1 rounded-xl border border-border px-4 text-sm text-muted transition active:bg-slate-100 sm:flex-none"
                    type="button"
                    @click="closeUpdateModal"
                >
                    Cancel
                </button>
                <button
                    class="min-h-[44px] flex-1 rounded-xl bg-primary px-4 text-sm font-medium text-white transition active:opacity-90 disabled:opacity-60 sm:flex-none"
                    type="button"
                    :disabled="updateSubmitting || !selectedOpenTask"
                    @click="submitUpdateOpenTask"
                >
                    {{ updateSubmitting ? 'Saving...' : 'Save' }}
                </button>
            </template>
        </Modal>
        <Modal
            :open="uploadModalOpen"
            :title="uploadModalTitle"
            eyebrow="Attach file"
            size="md"
            @close="closeUploadModal"
        >
            <div class="grid gap-4 text-sm">
                <div class="rounded-xl border border-border bg-white px-4 py-3">
                    <div class="text-sm font-semibold text-ink">{{ selectedOpenTaskTitle }}</div>
                    <div v-if="selectedOpenTaskLecture" class="mt-1 text-xs text-muted">
                        Lecture: {{ selectedOpenTaskLecture }}
                    </div>
                </div>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Title</span>
                    <input
                        v-model.trim="uploadForm.title"
                        type="text"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Description</span>
                    <textarea
                        v-model.trim="uploadForm.desc"
                        rows="3"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    ></textarea>
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">File</span>
                    <input
                        type="file"
                        accept="image/*,application/pdf"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm"
                        @change="handleUploadFile"
                    />
                </label>
                <div v-if="uploadForm.link" class="text-xs text-muted break-all">
                    {{ uploadForm.link }}
                </div>
            </div>
            <template #footer>
                <button
                    class="min-h-[44px] flex-1 rounded-xl border border-border px-4 text-sm text-muted transition active:bg-slate-100 sm:flex-none"
                    type="button"
                    @click="closeUploadModal"
                >
                    Cancel
                </button>
                <button
                    class="min-h-[44px] flex-1 rounded-xl bg-primary px-4 text-sm font-medium text-white transition active:opacity-90 disabled:opacity-60 sm:flex-none"
                    type="button"
                    :disabled="uploadSubmitting || !uploadForm.link || !selectedOpenTask"
                    @click="submitUpload"
                >
                    {{ uploadSubmitting ? 'Saving...' : 'Save' }}
                </button>
            </template>
        </Modal>
        <Modal
            :open="notifyModalOpen"
            title="Notify Lecture"
            eyebrow="Send notification"
            size="sm"
            @close="closeNotifyModal"
        >
            <div class="grid gap-4 text-sm">
                <p class="text-muted">
                    Send notification a link to Lecture: <span class="font-semibold text-ink">{{ notifyLectureName }}</span>
                </p>
                <div v-if="notifyError" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ notifyError }}
                </div>
            </div>
            <template #footer>
                <button
                    class="min-h-[44px] flex-1 rounded-xl border border-border px-4 text-sm text-muted transition active:bg-slate-100 sm:flex-none"
                    type="button"
                    :disabled="notifySubmitting"
                    @click="sendNotifyEmail"
                >
                    {{ notifySubmitting ? 'Sending...' : 'Send to Email' }}
                </button>
                <button
                    class="min-h-[44px] flex-1 rounded-xl bg-primary px-4 text-sm font-medium text-white transition active:opacity-90 disabled:opacity-60 sm:flex-none"
                    type="button"
                    :disabled="notifySubmitting"
                    @click="sendNotifyWhatsapp"
                >
                    {{ notifySubmitting ? 'Sending...' : 'Send to Whatsapp' }}
                </button>
            </template>
        </Modal>
        <Modal
            :open="confirmModalOpen"
            title="Konfirmasi Agenda"
            eyebrow="Bukti kehadiran"
            size="sm"
            @close="closeConfirmModal"
        >
            <div class="grid gap-4 text-sm">
                <div class="rounded-xl border border-border bg-white px-4 py-3">
                    <div class="text-sm font-semibold text-ink">{{ selectedOpenTaskTitle }}</div>
                    <div v-if="selectedOpenTaskLectureName" class="mt-1 text-xs text-muted">
                        Dosen: {{ selectedOpenTaskLectureName }}
                    </div>
                </div>

                <p class="text-xs text-muted">
                    Scan QR pada layar dosen dengan kamera HP, atau masukkan 6 digit kode
                    yang ditampilkan.
                </p>

                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Kode dari dosen</span>
                    <input
                        v-model.trim="confirmCode"
                        type="tel"
                        inputmode="numeric"
                        maxlength="6"
                        placeholder="000000"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-center font-mono text-2xl tracking-[0.3em] focus:outline-none focus:ring-2 focus:ring-primary/30"
                        @keyup.enter="submitConfirm"
                    />
                </label>

                <div v-if="confirmError" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ confirmError }}
                </div>
                <div v-if="confirmInfo" class="text-xs text-muted">
                    {{ confirmInfo }}
                </div>
            </div>
            <template #footer>
                <button
                    class="min-h-[44px] flex-1 rounded-xl border border-border px-4 text-sm text-muted transition active:bg-slate-100 sm:flex-none"
                    type="button"
                    @click="closeConfirmModal"
                >
                    Batal
                </button>
                <button
                    class="min-h-[44px] flex-1 rounded-xl bg-primary px-4 text-sm font-medium text-white transition active:opacity-90 disabled:opacity-60 sm:flex-none"
                    type="button"
                    :disabled="confirmSubmitting || confirmCode.length !== 6"
                    @click="submitConfirm"
                >
                    {{ confirmSubmitting ? 'Memproses...' : 'Konfirmasi' }}
                </button>
            </template>
        </Modal>
        <Modal
            :open="previewModalOpen"
            :title="previewTitle"
            eyebrow="Document preview"
            size="full"
            @close="closePreview"
        >
            <div class="grid gap-3">
                <img
                    v-if="previewType === 'image'"
                    :src="previewSrc"
                    alt="Document preview"
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
import Repository from '../../repository';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Modal from '../../components/Modal.vue';
import { uploadFirebaseFile } from '../../upload';
import { Icon } from '../../icons';

export default {
    components: {
        Loading,
        Modal,
        Icon,
    },
    data() {
        return {
            tasks: [],
            loading: false,
            errorMessage: '',
            staseLog: null,
            scoringModalOpen: false,
            selectedTask: null,
            scoringForm: {
                title: '',
                plan_date: '',
                lecture_ids: [],
            },
            scoringFormErrors: {
                plan_date: '',
            },
            lectures: [],
            lecturesLoading: false,
            lectureSearch: '',
            updateModalOpen: false,
            updateSubmitting: false,
            selectedOpenTask: null,
            updateForm: {
                title: '',
                plan: '',
            },
            uploadModalOpen: false,
            uploadSubmitting: false,
            uploadForm: {
                title: '',
                desc: '',
                link: '',
                type: 'score',
            },
            previewModalOpen: false,
            previewType: '',
            previewSrc: '',
            notifyModalOpen: false,
            notifyTarget: null,
            notifySubmitting: false,
            notifyError: '',
            actionMenuOpenId: null,
            confirmModalOpen: false,
            confirmSubmitting: false,
            confirmCode: '',
            confirmError: '',
            confirmInfo: '',
            confirmCoords: { lat: null, lng: null },
        };
    },
    computed: {
        staseLogId() {
            return this.$route.params.stase_log_id;
        },
        staseTitle() {
            if (this.staseLog && this.staseLog.stase && this.staseLog.stase.name) {
                return this.staseLog.stase.name;
            }
            return 'Stase';
        },
        stasePeriod() {
            if (!this.staseLog) {
                return '';
            }
            if (this.staseLog.start_date || this.staseLog.end_date) {
                return `${this.staseLog.start_date || ''} - ${this.staseLog.end_date || ''}`.trim();
            }
            return '';
        },
        selectedTaskTitle() {
            if (!this.selectedTask) {
                return 'Task';
            }
            return (this.selectedTask.task && this.selectedTask.task.name) || this.selectedTask.name || 'Task';
        },
        selectedTaskLecture() {
            if (!this.selectedTask || !this.selectedTask.lecture) {
                return '';
            }
            return this.selectedTask.lecture.name || this.selectedTask.lecture.email || 'Lecture';
        },
        selectedOpenTaskTitle() {
            if (!this.selectedOpenTask) {
                return 'Open Scoring';
            }
            return this.selectedOpenTask.title || 'Open Scoring';
        },
        selectedOpenTaskLecture() {
            if (!this.selectedOpenTask || !this.selectedOpenTask.lecture) {
                return '';
            }
            return this.selectedOpenTask.lecture.name || this.selectedOpenTask.lecture.email || 'Lecture';
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
        uploadModalTitle() {
            return this.uploadForm.type === 'task' ? 'Upload Task Document' : 'Upload Score Document';
        },
        notifyLectureName() {
            if (!this.notifyTarget) {
                return '';
            }
            return this.notifyTarget.lecture_name
                || (this.notifyTarget.lecture_id ? `Lecture #${this.notifyTarget.lecture_id}` : 'Lecture');
        },
        filteredLectures() {
            const query = this.lectureSearch.toLowerCase();
            if (!query) {
                return this.lectures;
            }
            return this.lectures.filter((lecture) => (lecture.name || '').toLowerCase().includes(query));
        },
        // The open-task list arrives with a joined `lecture_name` column
        // rather than a nested relation, so fall back across both shapes.
        selectedOpenTaskLectureName() {
            if (!this.selectedOpenTask) {
                return '';
            }
            return this.selectedOpenTask.lecture_name
                || this.selectedOpenTaskLecture
                || '';
        },
    },
    created() {
        this.loadStaseTasks();
    },
    mounted() {
        document.addEventListener('click', this.handleDocumentClick);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.handleDocumentClick);
    },
    methods: {
        toggleActionMenu(openTaskId) {
            this.actionMenuOpenId = this.actionMenuOpenId === openTaskId ? null : openTaskId;
        },
        closeActionMenu() {
            this.actionMenuOpenId = null;
        },
        handleDocumentClick(event) {
            const target = event && event.target ? event.target : null;
            if (!target) {
                return;
            }
            if (target.closest && target.closest('.action-dropdown')) {
                return;
            }
            this.closeActionMenu();
        },
        handleOpenTaskAction(action, openTask, parentTask) {
            this.closeActionMenu();
            if (action === 'confirmAttendance') {
                this.openConfirmModal(openTask);
                return;
            }
            if (action === 'notify') {
                this.openNotifyModal(openTask);
                return;
            }
            if (action === 'uploadScore') {
                this.openUploadModal(openTask, 'score');
                return;
            }
            if (action === 'uploadTask') {
                this.openUploadModal(openTask, 'task');
                return;
            }
            if (action === 'update') {
                this.openUpdateModal(openTask, parentTask);
                return;
            }
            if (action === 'delete') {
                this.deleteOpenTask(openTask);
            }
        },
        isImageUrl(url) {
            if (!url || typeof url !== 'string') {
                return false;
            }
            return /\.(png|jpe?g|gif|webp|bmp|svg)(\?.*)?$/i.test(url);
        },
        openPreview(file) {
            if (!file || !file.link) {
                return;
            }
            this.previewSrc = file.link;
            this.previewType = this.isImageUrl(file.link) ? 'image' : 'pdf';
            this.previewModalOpen = true;
        },
        closePreview() {
            this.previewModalOpen = false;
            this.previewType = '';
            this.previewSrc = '';
        },
        loadStaseTasks() {
            this.loading = true;
            this.errorMessage = '';
            return Repository.get('/api/student-stase')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const taken = result && Array.isArray(result.taken_stase) ? result.taken_stase : [];
                    const log = taken.find((item) => String(item.id) === String(this.staseLogId));
                    if (!log) {
                        this.errorMessage = 'Stase log not found.';
                        this.tasks = [];
                        this.staseLog = null;
                        return null;
                    }
                    this.staseLog = log;
                    const staseId = log.stase_id;
                    if (!staseId) {
                        this.errorMessage = 'Stase not found.';
                        this.tasks = [];
                        return null;
                    }
                    return Repository.get(`/api/student-stase-task/${staseId}`)
                        .then((taskResponse) => {
                            const data = taskResponse && taskResponse.data ? taskResponse.data.result : [];
                            this.tasks = Array.isArray(data) ? data : [];
                        });
                })
                .catch(() => {
                    this.tasks = [];
                    this.errorMessage = 'Failed to load tasks.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        openScoringModal(task) {
            this.selectedTask = task || null;
            this.scoringForm = {
                title: this.selectedTaskTitle,
                plan_date: '',
                lecture_ids: [],
            };
            this.scoringFormErrors = { plan_date: '' };
            this.lectureSearch = '';
            this.scoringModalOpen = true;
            if (!this.lectures.length) {
                this.fetchLectures();
            }
        },
        closeScoringModal() {
            this.scoringModalOpen = false;
            this.selectedTask = null;
            this.scoringForm = {
                title: '',
                plan_date: '',
                lecture_ids: [],
            };
            this.scoringFormErrors = { plan_date: '' };
            this.lectureSearch = '';
        },
        submitScoring() {
            if (!this.selectedTask) {
                return;
            }
            if (!this.scoringForm.plan_date) {
                this.scoringFormErrors.plan_date = 'Plan date is required.';
                return;
            }
            const payload = {
                stase_task_id: this.selectedTask.id,
                title: this.scoringForm.title,
                plan: this.scoringForm.plan_date,
                lecture_ids: this.scoringForm.lecture_ids,
            };
            return Repository.post('/api/open-stase-task', payload)
                .then(() => {
                    this.closeScoringModal();
                    if (this.$showToast) {
                        this.$showToast('Scoring form saved.');
                    }
                    this.loadStaseTasks();
                })
                .catch(() => {
                    if (this.$showToast) {
                        this.$showToast('Failed to save scoring.');
                    }
                });
        },
        openUpdateModal(openTask, parentTask) {
            this.selectedTask = parentTask || null;
            this.selectedOpenTask = openTask || null;
            this.updateForm = {
                title: (openTask && openTask.title) || '',
                plan: (openTask && openTask.plan) || '',
            };
            this.updateModalOpen = true;
        },
        closeUpdateModal() {
            this.updateModalOpen = false;
            this.updateSubmitting = false;
            this.selectedOpenTask = null;
            this.updateForm = {
                title: '',
                plan: '',
            };
        },
        submitUpdateOpenTask() {
            if (!this.selectedOpenTask || this.updateSubmitting) {
                return;
            }
            this.updateSubmitting = true;
            return Repository.patch(`/api/open-stase-task/${this.selectedOpenTask.id}`, {
                title: this.updateForm.title,
                plan: this.updateForm.plan,
            })
                .then(() => {
                    this.closeUpdateModal();
                    if (this.$showToast) {
                        this.$showToast('Open scoring updated.');
                    }
                    this.loadStaseTasks();
                })
                .catch(() => {
                    if (this.$showToast) {
                        this.$showToast('Failed to update scoring.');
                    }
                })
                .finally(() => {
                    this.updateSubmitting = false;
                });
        },
        deleteOpenTask(openTask) {
            if (!openTask) {
                return;
            }
            const confirmed = window.confirm('Delete this open scoring? This action cannot be undone.');
            if (!confirmed) {
                return;
            }
            return Repository.delete(`/api/open-stase-task/${openTask.id}`)
                .then(() => {
                    if (this.$showToast) {
                        this.$showToast('Open scoring deleted.');
                    }
                    this.loadStaseTasks();
                })
                .catch(() => {
                    if (this.$showToast) {
                        this.$showToast('Failed to delete scoring.');
                    }
                });
        },
        openUploadModal(openTask, type) {
            this.selectedOpenTask = openTask || null;
            this.uploadForm = {
                title: type === 'task' ? 'Upload Task' : 'Upload Score',
                desc: '',
                link: '',
                type: type || 'score',
            };
            this.uploadModalOpen = true;
        },
        closeUploadModal() {
            this.uploadModalOpen = false;
            this.uploadSubmitting = false;
            this.uploadForm = {
                title: '',
                desc: '',
                link: '',
                type: 'score',
            };
        },
        async handleUploadFile(event) {
            const file = event && event.target ? event.target.files[0] : null;
            if (!file) {
                return;
            }
            this.uploadSubmitting = true;
            try {
                const prefix = 'Student/ScoreDocument';
                const url = await uploadFirebaseFile({ file, prefix });
                if (url) {
                    this.uploadForm.link = url;
                }
            } finally {
                this.uploadSubmitting = false;
            }
        },
        submitUpload() {
            if (!this.selectedOpenTask || !this.uploadForm.link) {
                return;
            }
            this.uploadSubmitting = true;
            return Repository.post('/api/files', {
                title: this.uploadForm.title,
                desc: this.uploadForm.desc,
                link: this.uploadForm.link,
                open_stase_task_id: this.selectedOpenTask.id,
                stase_task_log_id: this.selectedOpenTask.stase_task_log_id || null,
                type: this.uploadForm.type,
            })
                .then(() => {
                    this.closeUploadModal();
                    if (this.$showToast) {
                        this.$showToast('Document uploaded.');
                    }
                    this.loadStaseTasks();
                })
                .catch(() => {
                    if (this.$showToast) {
                        this.$showToast('Failed to upload document.');
                    }
                })
                .finally(() => {
                    this.uploadSubmitting = false;
                });
        },
        openConfirmModal(openTask) {
            this.selectedOpenTask = openTask || null;
            this.confirmCode = '';
            this.confirmError = '';
            this.confirmInfo = '';
            this.confirmCoords = { lat: null, lng: null };
            this.confirmModalOpen = true;

            // Location is captured opportunistically for the audit trail; a
            // denied or slow GPS must never block the confirmation itself.
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        this.confirmCoords = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                        };
                    },
                    () => {},
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            }
        },
        closeConfirmModal() {
            this.confirmModalOpen = false;
            this.confirmSubmitting = false;
            this.confirmCode = '';
            this.confirmError = '';
            this.confirmInfo = '';
            this.selectedOpenTask = null;
        },
        submitConfirm() {
            if (!this.selectedOpenTask || this.confirmSubmitting || this.confirmCode.length !== 6) {
                return;
            }

            this.confirmSubmitting = true;
            this.confirmError = '';

            return Repository.post('/api/attendance-confirm', {
                open_stase_task_id: this.selectedOpenTask.id,
                code: this.confirmCode,
                method: 'code',
                lat: this.confirmCoords.lat,
                lng: this.confirmCoords.lng,
            })
                .then(() => {
                    this.closeConfirmModal();
                    if (this.$showToast) {
                        this.$showToast('Agenda berhasil dikonfirmasi.');
                    }
                    this.loadStaseTasks();
                })
                .catch((error) => {
                    this.confirmError = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Gagal mengonfirmasi agenda.';
                })
                .finally(() => {
                    this.confirmSubmitting = false;
                });
        },
        openNotifyModal(openTask) {
            this.notifyTarget = openTask || null;
            this.notifyError = '';
            this.notifyModalOpen = true;
        },
        closeNotifyModal() {
            this.notifyModalOpen = false;
            this.notifyTarget = null;
            this.notifyError = '';
        },
        sendNotifyEmail() {
            if (!this.notifyTarget || this.notifySubmitting) {
                return;
            }

            this.notifySubmitting = true;
            this.notifyError = '';

            return Repository.post(`/api/open-stase-task/${this.notifyTarget.id}/notify-email`)
                .then(() => {
                    if (this.$showToast) {
                        this.$showToast('Notification email sent.');
                    }
                    this.closeNotifyModal();
                })
                .catch((error) => {
                    this.notifyError = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to send notification email.';
                })
                .finally(() => {
                    this.notifySubmitting = false;
                });
        },
        sendNotifyWhatsapp() {
            if (!this.notifyTarget || this.notifySubmitting) {
                return;
            }

            this.notifySubmitting = true;
            this.notifyError = '';

            return Repository.post(`/api/open-stase-task/${this.notifyTarget.id}/notify-whatsapp`)
                .then(() => {
                    if (this.$showToast) {
                        this.$showToast('Notification WhatsApp message sent.');
                    }
                    this.closeNotifyModal();
                })
                .catch((error) => {
                    this.notifyError = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to send WhatsApp notification.';
                })
                .finally(() => {
                    this.notifySubmitting = false;
                });
        },
        fetchLectures() {
            this.lecturesLoading = true;
            return Repository.get('/api/lecture-list')
                .then((response) => {
                    const result = response && response.data ? response.data.result : [];
                    this.lectures = Array.isArray(result) ? result : [];
                })
                .catch(() => {
                    this.lectures = [];
                })
                .finally(() => {
                    this.lecturesLoading = false;
                });
        },
    },
};
</script>
