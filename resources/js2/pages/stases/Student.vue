<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Stase Management</div>
                <h1 class="text-2xl font-semibold text-ink">Stase Calendar</h1>
            </div>
            <div class="flex items-center gap-2">
                <button
                    class="rounded-xl border border-border px-3 py-2 text-xs font-medium text-muted"
                    type="button"
                    @click="shiftWindow(-halfWeeksCount)"
                >
                    &lsaquo; Prev
                </button>
                <button
                    class="rounded-xl border border-border px-3 py-2 text-xs font-medium text-muted"
                    type="button"
                    @click="resetToToday"
                >
                    Today
                </button>
                <button
                    class="rounded-xl border border-border px-3 py-2 text-xs font-medium text-muted"
                    type="button"
                    @click="shiftWindow(halfWeeksCount)"
                >
                    Next &rsaquo;
                </button>
            </div>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="text-xs text-muted">Student Name</label>
                    <input
                        v-model.trim="filters.keyword"
                        type="text"
                        placeholder="Search student name..."
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="min-w-[160px]">
                    <label class="text-xs text-muted">Year</label>
                    <select
                        v-model="filters.year"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">All</option>
                        <option v-for="year in yearOptions" :key="year" :value="year">
                            {{ year }}
                        </option>
                    </select>
                </div>
                <div class="min-w-[180px]">
                    <label class="text-xs text-muted">Phase</label>
                    <select
                        v-model="filters.stase_desc"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">All</option>
                        <option v-for="option in descOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button
                        class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                        type="button"
                        @click="resetFilters"
                    >
                        Reset
                    </button>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-border bg-panel">
            <button
                type="button"
                class="flex w-full items-center justify-between px-5 py-4 text-left"
                @click="summaryCollapsed = !summaryCollapsed"
            >
                <div>
                    <div class="text-xs uppercase tracking-[0.2em] text-muted">Overview</div>
                    <div class="text-sm font-semibold text-ink">Students per Stase</div>
                </div>
                <span class="text-xs text-muted">{{ summaryCollapsed ? 'Show' : 'Hide' }}</span>
            </button>
            <div v-show="!summaryCollapsed" class="border-t border-border px-5 py-4">
                <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
                    <div class="text-xs text-muted">
                        Students placed on each stase as of the selected date.
                    </div>
                    <div class="flex items-end gap-2">
                        <label class="grid gap-1 text-xs">
                            <span class="text-muted">Date</span>
                            <input
                                v-model="summaryDate"
                                type="date"
                                class="rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                            />
                        </label>
                        <button
                            class="rounded-xl border border-border px-3 py-2 text-xs font-medium text-muted"
                            type="button"
                            @click="summaryDate = toDateStr(new Date())"
                        >
                            Today
                        </button>
                    </div>
                </div>
                <div v-if="summaryLoading" class="mb-3 text-xs text-muted">Loading...</div>
                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    <button
                        v-for="entry in staseSummary"
                        :key="entry.stase.id"
                        type="button"
                        class="flex items-center justify-between rounded-xl border border-border px-3 py-2 text-left text-sm hover:bg-slate-50"
                        @click="openSummaryDetail(entry)"
                    >
                        <span class="flex min-w-0 items-center gap-2">
                            <span
                                class="h-2.5 w-2.5 shrink-0 rounded-full"
                                :style="{ backgroundColor: entry.stase.color || '#94a3b8' }"
                            ></span>
                            <span class="truncate text-ink">{{ entry.stase.name }}</span>
                        </span>
                        <span class="ml-2 shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-ink">
                            {{ entry.count }}
                        </span>
                    </button>
                    <div v-if="staseSummary.length === 0" class="text-sm text-muted">
                        No stases found.
                    </div>
                </div>
            </div>
        </section>

        <div
            v-if="errorMessage"
            class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600"
        >
            {{ errorMessage }}
        </div>

        <section class="relative overflow-hidden rounded-2xl border border-border bg-panel">
            <Loading :active="loading" :is-full-page="false" />

            <div ref="scrollArea" class="calendar-scroll" :style="{ maxHeight: '72vh' }">
                <div class="calendar-inner" :style="{ width: totalWidthPx + 'px' }">
                    <div class="calendar-row">
                        <div
                            class="calendar-name-cell flex items-end border-b border-r border-border bg-white px-4 py-3 text-xs font-semibold uppercase tracking-wide text-muted"
                            :style="{ width: nameWidth + 'px' }"
                        >
                            Student
                        </div>
                        <div
                            v-for="week in weeks"
                            :key="week.key"
                            class="calendar-week-cell flex flex-col items-center justify-center border-b border-r border-border py-2 text-center"
                            :class="week.isCurrent ? 'bg-primary/10' : 'bg-white'"
                            :style="{ width: weekWidth + 'px' }"
                        >
                            <div class="text-[11px] font-semibold text-ink">{{ week.label }}</div>
                            <div class="text-[10px] text-muted">Wk {{ week.weekNumber }}</div>
                        </div>
                    </div>

                    <div v-for="student in filteredStudents" :key="student.id" class="calendar-row">
                        <div
                            class="calendar-name-cell flex flex-col justify-center border-b border-r border-border bg-white px-4 py-2"
                            :style="{ width: nameWidth + 'px', height: rowHeight(student) + 'px' }"
                        >
                            <div class="truncate text-sm font-medium text-ink">{{ student.name }}</div>
                            <div class="text-xs text-muted">{{ student.year || '-' }}</div>
                        </div>
                        <div
                            class="calendar-timeline relative cursor-pointer border-b border-border bg-white"
                            :style="{
                                width: timelineWidth + 'px',
                                height: rowHeight(student) + 'px',
                                backgroundImage: timelineGridBackground,
                            }"
                            @click="handleTimelineClick(student, $event)"
                        >
                            <div
                                v-for="log in laneLogs(student)"
                                :key="log.id"
                                class="stase-box absolute rounded-lg px-2 py-1 text-xs font-medium shadow-sm"
                                :style="boxStyle(log)"
                                :title="boxTitle(log)"
                                @click.stop
                            >
                                <span
                                    class="resize-handle resize-handle-left"
                                    @mousedown.stop.prevent="startResize(log, 'start', $event)"
                                ></span>
                                <span class="truncate">{{ boxLabel(log) }}</span>
                                <span
                                    class="resize-handle resize-handle-right"
                                    @mousedown.stop.prevent="startResize(log, 'end', $event)"
                                ></span>
                            </div>
                        </div>
                    </div>

                    <div v-if="!loading && filteredStudents.length === 0" class="px-5 py-10 text-center text-sm text-muted">
                        No students match the current filters.
                    </div>
                </div>
            </div>
        </section>

        <Modal
            :open="createModalOpen"
            title="Add Stase"
            eyebrow="New assignment"
            size="sm"
            @close="closeCreateModal"
        >
            <form class="grid gap-4" @submit.prevent="submitCreate">
                <div class="text-sm">
                    <div class="text-xs text-muted">Student</div>
                    <div class="font-medium text-ink">{{ createForm.student_name }}</div>
                </div>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Stase</span>
                    <v-select
                        v-model="createForm.stase_id"
                        :options="stases"
                        label="name"
                        :reduce="(stase) => stase.id"
                        :selectable="(stase) => !isStaseTaken(stase.id)"
                        :searchable="true"
                        placeholder="Select stase"
                    >
                        <template #option="stase">
                            <span>{{ stase.name }}</span>
                            <span v-if="isStaseTaken(stase.id)" class="text-xs text-muted"> (already taken)</span>
                        </template>
                    </v-select>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Start Date</span>
                        <input
                            v-model="createForm.start_date"
                            type="date"
                            required
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">End Date</span>
                        <input
                            v-model="createForm.end_date"
                            type="date"
                            required
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                </div>
                <div v-if="createError" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ createError }}
                </div>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="submit"
                    :disabled="createSubmitting"
                >
                    {{ createSubmitting ? 'Saving...' : 'Create Stase Log' }}
                </button>
            </form>
        </Modal>

        <Modal
            :open="summaryModalOpen"
            :title="summaryModalStase ? summaryModalStase.stase.name : 'Students'"
            eyebrow="Students in this stase"
            size="sm"
            @close="closeSummaryDetail"
        >
            <div v-if="summaryModalStase" class="grid gap-2">
                <div v-if="summaryModalStudents.length === 0" class="text-sm text-muted">
                    No students in this stase for the current view.
                </div>
                <div
                    v-for="student in summaryModalStudents"
                    :key="student.id"
                    class="flex items-center justify-between rounded-xl border border-border px-3 py-2 text-sm"
                >
                    <span class="text-ink">{{ student.name }}</span>
                    <span class="text-xs text-muted">{{ student.year || '-' }}</span>
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

const WEEK_WIDTH = 140;
const NAME_WIDTH = 220;
const WEEKS_COUNT = 16;
const LANE_HEIGHT = 34;
const LANE_GAP = 6;
const ROW_PADDING = 16;

export default {
    components: {
        Loading,
        Modal,
    },
    data() {
        return {
            weekWidth: WEEK_WIDTH,
            nameWidth: NAME_WIDTH,
            weeksCount: WEEKS_COUNT,
            windowStart: this.getMonday(this.addDays(new Date(), -28)),
            students: [],
            stases: [],
            staseLogs: [],
            loading: false,
            errorMessage: '',
            filters: {
                keyword: '',
                year: '',
                stase_desc: '',
            },
            createModalOpen: false,
            createSubmitting: false,
            createError: '',
            createForm: {
                student_id: null,
                student_name: '',
                stase_id: '',
                start_date: '',
                end_date: '',
            },
            createTakenStaseIds: [],
            resizing: null,
            summaryCollapsed: false,
            summaryModalOpen: false,
            summaryModalStase: null,
            summaryDate: this.toDateStr(new Date()),
            summaryLogs: [],
            summaryLoading: false,
        };
    },
    computed: {
        halfWeeksCount() {
            return Math.round(this.weeksCount / 2);
        },
        timelineWidth() {
            return this.weeksCount * this.weekWidth;
        },
        totalWidthPx() {
            return this.nameWidth + this.timelineWidth;
        },
        windowEnd() {
            return this.addDays(this.windowStart, this.weeksCount * 7 - 1);
        },
        weeks() {
            const today = this.toDateStr(new Date());
            const result = [];
            for (let i = 0; i < this.weeksCount; i += 1) {
                const start = this.addDays(this.windowStart, i * 7);
                const end = this.addDays(start, 6);
                const startStr = this.toDateStr(start);
                const endStr = this.toDateStr(end);
                result.push({
                    key: startStr,
                    start,
                    end,
                    startStr,
                    endStr,
                    label: `${this.formatShort(start)} - ${this.formatShort(end)}`,
                    weekNumber: this.isoWeekNumber(start),
                    isCurrent: today >= startStr && today <= endStr,
                });
            }
            return result;
        },
        filteredStudents() {
            const keyword = this.filters.keyword.toLowerCase();
            return this.students.filter((student) => {
                if (keyword && !(student.name || '').toLowerCase().includes(keyword)) {
                    return false;
                }
                if (this.filters.year && String(student.year) !== String(this.filters.year)) {
                    return false;
                }
                return true;
            });
        },
        filteredStaseLogs() {
            if (!this.filters.stase_desc) {
                return this.staseLogs;
            }
            return this.staseLogs.filter((log) => log.stase_desc === this.filters.stase_desc);
        },
        logsByStudent() {
            const grouped = {};
            this.filteredStaseLogs.forEach((log) => {
                const key = log.student_id;
                if (!grouped[key]) {
                    grouped[key] = [];
                }
                grouped[key].push(log);
            });
            return grouped;
        },
        yearOptions() {
            const years = new Set();
            this.students.forEach((student) => {
                if (student.year) {
                    years.add(student.year);
                }
            });
            return Array.from(years).sort((a, b) => String(b).localeCompare(String(a)));
        },
        descOptions() {
            const seen = new Set();
            const options = [];
            this.stases.forEach((stase) => {
                if (stase.desc && !seen.has(stase.desc)) {
                    seen.add(stase.desc);
                    options.push({ value: stase.desc, label: this.formatDescLabel(stase.desc) });
                }
            });
            return options;
        },
        timelineGridBackground() {
            const w = this.weekWidth;
            return `repeating-linear-gradient(to right, transparent 0, transparent ${w - 1}px, rgba(148, 163, 184, 0.35) ${w - 1}px, rgba(148, 163, 184, 0.35) ${w}px)`;
        },
        studentsById() {
            const map = {};
            this.students.forEach((student) => {
                map[student.id] = student;
            });
            return map;
        },
        summaryFilteredLogs() {
            if (!this.filters.stase_desc) {
                return this.summaryLogs;
            }
            return this.summaryLogs.filter((log) => log.stase_desc === this.filters.stase_desc);
        },
        staseSummary() {
            const filteredStudentIds = new Set(this.filteredStudents.map((student) => student.id));
            const studentIdsByStase = {};

            this.summaryFilteredLogs.forEach((log) => {
                if (!filteredStudentIds.has(log.student_id)) {
                    return;
                }
                if (!studentIdsByStase[log.stase_id]) {
                    studentIdsByStase[log.stase_id] = new Set();
                }
                studentIdsByStase[log.stase_id].add(log.student_id);
            });

            return this.stases.map((stase) => {
                const studentIds = Array.from(studentIdsByStase[stase.id] || []);
                return {
                    stase,
                    count: studentIds.length,
                    studentIds,
                };
            });
        },
        summaryModalStudents() {
            if (!this.summaryModalStase) {
                return [];
            }
            return this.summaryModalStase.studentIds
                .map((id) => this.studentsById[id])
                .filter(Boolean);
        },
    },
    watch: {
        summaryDate() {
            this.fetchSummaryLogs();
        },
    },
    created() {
        this.fetchStudents();
        this.fetchStases();
        this.fetchStaseLogs();
        this.fetchSummaryLogs();
    },
    beforeDestroy() {
        window.removeEventListener('mousemove', this.onResizeMove);
        window.removeEventListener('mouseup', this.onResizeEnd);
    },
    methods: {
        fetchStudents() {
            return Repository.get('/api/student-list')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.students = Array.isArray(result) ? result : [];
                })
                .catch(() => {
                    this.students = [];
                });
        },
        fetchStases() {
            return Repository.get('/api/stase-list-all')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.stases = Array.isArray(result) ? result : [];
                })
                .catch(() => {
                    this.stases = [];
                });
        },
        fetchStaseLogs() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get('/api/stase-logs', {
                params: {
                    date_from: this.toDateStr(this.windowStart),
                    date_to: this.toDateStr(this.windowEnd),
                    per_page: 1000,
                },
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.staseLogs = result && Array.isArray(result.data) ? result.data : [];
                })
                .catch(() => {
                    this.staseLogs = [];
                    this.errorMessage = 'Failed to load stase logs.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        fetchSummaryLogs() {
            this.summaryLoading = true;

            return Repository.get('/api/stase-logs', {
                params: {
                    date: this.summaryDate,
                    per_page: 1000,
                },
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.summaryLogs = result && Array.isArray(result.data) ? result.data : [];
                })
                .catch(() => {
                    this.summaryLogs = [];
                })
                .finally(() => {
                    this.summaryLoading = false;
                });
        },
        shiftWindow(weeksDelta) {
            this.windowStart = this.addDays(this.windowStart, weeksDelta * 7);
            this.fetchStaseLogs();
        },
        resetToToday() {
            this.windowStart = this.getMonday(this.addDays(new Date(), -28));
            this.fetchStaseLogs();
        },
        laneLogs(student) {
            const logs = (this.logsByStudent[student.id] || [])
                .slice()
                .sort((a, b) => this.parseDateStr(a.start_date) - this.parseDateStr(b.start_date));

            const laneEnds = [];
            logs.forEach((log) => {
                const start = this.parseDateStr(log.start_date);
                let laneIndex = laneEnds.findIndex((end) => end <= start);
                if (laneIndex === -1) {
                    laneIndex = laneEnds.length;
                }
                laneEnds[laneIndex] = this.parseDateStr(log.end_date);
                log.__lane = laneIndex;
            });

            return logs;
        },
        laneCount(student) {
            const logs = this.laneLogs(student);
            if (logs.length === 0) {
                return 1;
            }
            return Math.max(...logs.map((log) => log.__lane)) + 1;
        },
        rowHeight(student) {
            const lanes = this.laneCount(student);
            return lanes * LANE_HEIGHT + (lanes - 1) * LANE_GAP + ROW_PADDING;
        },
        boxStyle(log) {
            const dayWidth = this.weekWidth / 7;
            const start = this.parseDateStr(log.start_date);
            const end = this.parseDateStr(log.end_date);
            const offsetDays = (start - this.windowStart) / 86400000;
            const durationDays = ((end - start) / 86400000) + 1;
            const left = offsetDays * dayWidth;
            const width = Math.max(durationDays * dayWidth - 4, dayWidth - 4);
            const top = (log.__lane || 0) * (LANE_HEIGHT + LANE_GAP) + (ROW_PADDING / 2);

            return {
                left: `${left + 2}px`,
                width: `${width}px`,
                top: `${top}px`,
                height: `${LANE_HEIGHT}px`,
                backgroundColor: log.stase_color || '#94a3b8',
                color: log.stase_font_color || '#ffffff',
            };
        },
        boxLabel(log) {
            return log.stase_alias || log.stase_name || 'Stase';
        },
        boxTitle(log) {
            const name = log.stase_name || 'Stase';
            return `${name} (${log.start_date} - ${log.end_date})`;
        },
        handleTimelineClick(student, event) {
            const rect = event.currentTarget.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const dayWidth = this.weekWidth / 7;
            const dayIndex = Math.floor(x / dayWidth);
            const weekIndex = Math.min(Math.max(Math.floor(dayIndex / 7), 0), this.weeks.length - 1);
            const week = this.weeks[weekIndex];
            this.openCreateModal(student, week);
        },
        openCreateModal(student, week) {
            this.createForm = {
                student_id: student.id,
                student_name: student.name,
                stase_id: '',
                start_date: week.startStr,
                end_date: week.endStr,
            };
            this.createError = '';
            this.createTakenStaseIds = [];
            this.createModalOpen = true;
            this.fetchTakenStase(student.id);
        },
        closeCreateModal() {
            this.createModalOpen = false;
        },
        fetchTakenStase(studentId) {
            return Repository.get('/api/student-stase', {
                params: { student_id: studentId },
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const taken = result && Array.isArray(result.taken_stase) ? result.taken_stase : [];
                    this.createTakenStaseIds = taken
                        .map((log) => log.stase_id)
                        .filter((id) => id !== null && id !== undefined);
                })
                .catch(() => {
                    this.createTakenStaseIds = [];
                });
        },
        isStaseTaken(staseId) {
            return this.createTakenStaseIds.includes(staseId);
        },
        submitCreate() {
            if (!this.createForm.stase_id) {
                this.createError = 'Please select a stase.';
                return null;
            }

            this.createSubmitting = true;
            this.createError = '';

            return Repository.post('/api/student-stase', {
                student_id: this.createForm.student_id,
                stase_id: this.createForm.stase_id,
                start_date: this.createForm.start_date,
                end_date: this.createForm.end_date,
            })
                .then(() => {
                    this.closeCreateModal();
                    this.fetchStaseLogs();
                    this.fetchSummaryLogs();
                    this.$showToast('Stase log created.');
                })
                .catch((error) => {
                    this.createError = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to create stase log.';
                })
                .finally(() => {
                    this.createSubmitting = false;
                });
        },
        startResize(log, edge, event) {
            this.resizing = {
                log,
                edge,
                startX: event.clientX,
                originalStart: log.start_date,
                originalEnd: log.end_date,
                dayWidth: this.weekWidth / 7,
            };
            document.body.style.userSelect = 'none';
            window.addEventListener('mousemove', this.onResizeMove);
            window.addEventListener('mouseup', this.onResizeEnd);
        },
        onResizeMove(event) {
            if (!this.resizing) {
                return;
            }

            const { log, edge, startX, originalStart, originalEnd, dayWidth } = this.resizing;
            const deltaDays = Math.round((event.clientX - startX) / dayWidth);

            if (edge === 'start') {
                const newStart = this.addDays(this.parseDateStr(originalStart), deltaDays);
                if (newStart < this.parseDateStr(originalEnd)) {
                    log.start_date = this.toDateStr(newStart);
                }
            } else {
                const newEnd = this.addDays(this.parseDateStr(originalEnd), deltaDays);
                if (newEnd > this.parseDateStr(originalStart)) {
                    log.end_date = this.toDateStr(newEnd);
                }
            }
        },
        onResizeEnd() {
            window.removeEventListener('mousemove', this.onResizeMove);
            window.removeEventListener('mouseup', this.onResizeEnd);
            document.body.style.userSelect = '';

            if (!this.resizing) {
                return;
            }

            const { log, originalStart, originalEnd } = this.resizing;
            this.resizing = null;

            if (log.start_date === originalStart && log.end_date === originalEnd) {
                return;
            }

            Repository.patch(`/api/student-stase/${log.id}`, {
                student_id: log.student_id,
                start_date: log.start_date,
                end_date: log.end_date,
            })
                .then(() => {
                    this.$showToast('Stase log updated.');
                    this.fetchStaseLogs();
                    this.fetchSummaryLogs();
                })
                .catch(() => {
                    this.$showToast('Failed to update stase log.');
                    this.fetchStaseLogs();
                    this.fetchSummaryLogs();
                });
        },
        getMonday(date) {
            const d = new Date(date);
            d.setHours(0, 0, 0, 0);
            const day = d.getDay();
            const diff = (day === 0 ? -6 : 1) - day;
            d.setDate(d.getDate() + diff);
            return d;
        },
        addDays(date, days) {
            const result = new Date(date);
            result.setDate(result.getDate() + days);
            return result;
        },
        parseDateStr(value) {
            if (!value) {
                return new Date(NaN);
            }
            const parts = String(value).slice(0, 10).split('-').map(Number);
            return new Date(parts[0], parts[1] - 1, parts[2]);
        },
        toDateStr(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        formatShort(date) {
            return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
        },
        isoWeekNumber(date) {
            const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
            const dayNum = d.getUTCDay() || 7;
            d.setUTCDate(d.getUTCDate() + 4 - dayNum);
            const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
            return Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
        },
        formatDescLabel(desc) {
            return String(desc)
                .split('_')
                .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
        },
        resetFilters() {
            this.filters = {
                keyword: '',
                year: '',
                stase_desc: '',
            };
        },
        openSummaryDetail(entry) {
            this.summaryModalStase = entry;
            this.summaryModalOpen = true;
        },
        closeSummaryDetail() {
            this.summaryModalOpen = false;
            this.summaryModalStase = null;
        },
    },
};
</script>

<style scoped>
.calendar-scroll {
    overflow: auto;
}

.calendar-inner {
    display: flex;
    flex-direction: column;
}

.calendar-row {
    display: flex;
    flex-shrink: 0;
}

.calendar-name-cell {
    position: sticky;
    left: 0;
    z-index: 10;
    flex-shrink: 0;
}

.calendar-row:first-child .calendar-name-cell,
.calendar-row:first-child .calendar-week-cell {
    position: sticky;
    top: 0;
    z-index: 20;
}

.calendar-row:first-child .calendar-name-cell {
    z-index: 30;
}

.calendar-week-cell {
    flex-shrink: 0;
}

.calendar-timeline {
    flex-shrink: 0;
    overflow: hidden;
}

.stase-box {
    display: flex;
    align-items: center;
    overflow: hidden;
    white-space: nowrap;
}

.resize-handle {
    position: absolute;
    top: 0;
    bottom: 0;
    width: 8px;
    cursor: ew-resize;
    opacity: 0;
}

.stase-box:hover .resize-handle {
    opacity: 1;
    background-color: rgba(255, 255, 255, 0.35);
}

.resize-handle-left {
    left: 0;
    border-radius: 0.5rem 0 0 0.5rem;
}

.resize-handle-right {
    right: 0;
    border-radius: 0 0.5rem 0.5rem 0;
}
</style>
