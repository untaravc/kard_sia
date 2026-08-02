<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Letter Management</div>
                <h1 class="text-2xl font-semibold text-ink">
                    {{ isEdit ? 'Edit Letter' : 'Add Letter' }}
                </h1>
            </div>
            <div class="flex items-center gap-2">
                <router-link class="rounded-xl border border-border px-4 py-2 text-sm text-muted" to="/blu/letters">
                    Back
                </router-link>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="button"
                    :disabled="submitting || loading"
                    @click="submitForm"
                >
                    {{ submitting ? 'Saving...' : 'Save Letter' }}
                </button>
            </div>
        </header>

        <section class="relative rounded-2xl border border-border bg-panel p-6">
            <Loading :active="loading" :is-full-page="false" />

            <div class="grid gap-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Number</span>
                        <input
                            v-model.trim="form.number"
                            type="text"
                            placeholder="Leave blank to auto-generate"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 disabled:text-muted disabled:focus:ring-0"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Date</span>
                        <input
                            v-model="form.date"
                            type="date"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                </div>

                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Title</span>
                    <input
                        v-model.trim="form.title"
                        type="text"
                        placeholder="Letter title"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>

                <div class="grid gap-6 md:grid-cols-3">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Subtitle</span>
                        <input
                            v-model.trim="form.subtitle"
                            type="text"
                            placeholder="Optional subtitle"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>

                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Status</span>
                        <select
                            v-model.number="form.status"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        >
                            <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </label>

                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Study Program</span>
                        <select
                            v-model="form.study_program_code"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        >
                            <option value="">-</option>
                            <option v-for="option in studyPrograms" :key="option.id" :value="option.code">
                                {{ option.name }}
                            </option>
                        </select>
                    </label>
                </div>

                <div class="grid gap-2 text-sm">
                    <span class="text-muted">Intro</span>
                    <RichTextEditor v-model="form.intro" placeholder="Intro..." />
                </div>

                <div class="grid gap-2 text-sm">
                    <span class="text-muted">Body</span>
                    <RichTextEditor v-model="form.body" placeholder="Write letter body..." />
                </div>

                <div class="grid gap-2 text-sm">
                    <span class="text-muted">Outro</span>
                    <RichTextEditor v-model="form.outro" placeholder="Outro..." />
                </div>

                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Attachment Label</span>
                    <input
                        v-model.trim="form.attachment_label"
                        type="text"
                        placeholder="Attachment label"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>

                <div class="grid gap-2 text-sm">
                    <span class="text-muted">Attachment Content</span>
                    <RichTextEditor v-model="form.attachment_content" placeholder="Attachment content..." />
                </div>

                <div class="grid gap-2 text-sm">
                    <span class="text-muted">Custom Invitation</span>
                    <textarea
                        v-model.trim="form.custom_invitation"
                        rows="4"
                        placeholder="Custom invitation..."
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    ></textarea>
                </div>

                <div class="grid gap-3 rounded-2xl border border-border bg-white p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-medium text-ink">Undangan</div>
                            <div class="text-xs text-muted">Pilih user/dosen/residen yang diundang.</div>
                        </div>
                        <button
                            class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                            type="button"
                            @click="openInviteModal"
                        >
                            Tambah Undangan
                        </button>
                    </div>
                    <div v-if="inviteParticipants.length === 0" class="text-xs text-muted">
                        Belum ada undangan.
                    </div>
                    <div v-else class="flex flex-wrap gap-2">
                        <div
                            v-for="participant in inviteParticipants"
                            :key="inviteKey(participant)"
                            class="flex items-center gap-2 rounded-full border border-border bg-slate-50 px-3 py-1 text-xs"
                        >
                            <span class="text-muted">{{ participant.auth_type }}</span>
                            <span class="font-semibold text-ink">{{ participant.auth_name || participant.auth_id }}</span>
                            <button
                                class="rounded-full px-2 py-0.5 text-rose-600 hover:bg-rose-50"
                                type="button"
                                @click="removeInvite(participant)"
                            >
                                Remove
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid gap-3 rounded-2xl border border-border bg-white p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-medium text-ink">Approval</div>
                            <div class="text-xs text-muted">Pilih satu approver untuk surat.</div>
                        </div>
                        <button
                            class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                            type="button"
                            @click="openApprovalModal"
                        >
                            Pilih Approval
                        </button>
                    </div>
                    <div v-if="!approvalParticipant" class="text-xs text-muted">
                        Belum ada approval.
                    </div>
                    <div v-else class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="rounded-full border border-border bg-slate-50 px-3 py-1">
                            <span class="text-muted">{{ approvalParticipant.auth_type }}</span>
                            <span class="mx-1">•</span>
                            <span class="font-semibold text-ink">{{ approvalParticipant.auth_name || approvalParticipant.auth_id }}</span>
                        </span>
                        <span class="rounded-full border border-border bg-white px-3 py-1 text-muted">
                            Label: <span class="font-semibold text-ink">{{ approvalLabel }}</span>
                        </span>
                        <button
                            class="rounded-lg bg-rose-500/10 px-3 py-1.5 text-xs text-rose-600"
                            type="button"
                            @click="clearApproval"
                        >
                            Remove
                        </button>
                    </div>
                </div>

                <div
                    v-if="errorMessage"
                    class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600"
                >
                    {{ errorMessage }}
                </div>
            </div>
        </section>

        <Modal
            :open="inviteModalOpen"
            title="Tambah Undangan"
            eyebrow="Pilih peserta"
            size="xxl"
            @close="closeInviteModal"
        >
            <div class="grid gap-4">
                <div class="grid gap-2">
                    <label class="text-xs text-muted">Search</label>
                    <input
                        v-model.trim="inviteKeyword"
                        type="text"
                        placeholder="Search name..."
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>

                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="rounded-2xl border border-border p-4">
                        <div class="mb-3 text-sm font-semibold text-ink">Users</div>
                        <div v-if="inviteLoading.users" class="text-xs text-muted">Loading...</div>
                        <div v-else class="grid gap-2">
                            <label
                                v-for="user in filteredUsers"
                                :key="`user-${user.id}`"
                                class="flex items-start gap-2 text-sm"
                            >
                                <input
                                    type="checkbox"
                                    :checked="isInviteSelected('user', user.id)"
                                    @change="toggleInvite('user', user)"
                                />
                                <span>
                                    <span class="font-medium text-ink">{{ user.name }}</span>
                                    <span v-if="user.email" class="block text-xs text-muted">{{ user.email }}</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-border p-4">
                        <div class="mb-3 text-sm font-semibold text-ink">Lectures</div>
                        <div v-if="inviteLoading.lectures" class="text-xs text-muted">Loading...</div>
                        <div v-else class="grid gap-2">
                            <label
                                v-for="lecture in filteredLectures"
                                :key="`lecture-${lecture.id}`"
                                class="flex items-start gap-2 text-sm"
                            >
                                <input
                                    type="checkbox"
                                    :checked="isInviteSelected('lecture', lecture.id)"
                                    @change="toggleInvite('lecture', lecture)"
                                />
                                <span class="font-medium text-ink">{{ lecture.name }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-border p-4">
                        <div class="mb-3 text-sm font-semibold text-ink">Students</div>
                        <div v-if="inviteLoading.students" class="text-xs text-muted">Loading...</div>
                        <div v-else class="grid gap-2">
                            <label
                                v-for="student in filteredStudents"
                                :key="`student-${student.id}`"
                                class="flex items-start gap-2 text-sm"
                            >
                                <input
                                    type="checkbox"
                                    :checked="isInviteSelected('student', student.id)"
                                    @change="toggleInvite('student', student)"
                                />
                                <span>
                                    <span class="font-medium text-ink">{{ student.name }}</span>
                                    <span v-if="student.year" class="block text-xs text-muted">Year: {{ student.year }}</span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <template #footer>
                <button
                    class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                    type="button"
                    @click="closeInviteModal"
                >
                    Done
                </button>
            </template>
        </Modal>

        <Modal
            :open="approvalModalOpen"
            title="Pilih Approval"
            eyebrow="Pilih satu approver"
            size="xxl"
            @close="closeApprovalModal"
        >
            <div class="grid gap-4">
                <div class="grid gap-2">
                    <label class="text-xs text-muted">Label</label>
                    <input
                        v-model.trim="approvalLabel"
                        type="text"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                    <div class="text-[11px] text-muted">Default: Kepala Departemen</div>
                </div>

                <div class="grid gap-2">
                    <label class="text-xs text-muted">Search</label>
                    <input
                        v-model.trim="approvalKeyword"
                        type="text"
                        placeholder="Search name..."
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>

                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="rounded-2xl border border-border p-4">
                        <div class="mb-3 text-sm font-semibold text-ink">Users</div>
                        <div v-if="inviteLoading.users" class="text-xs text-muted">Loading...</div>
                        <div v-else class="grid gap-2">
                            <label
                                v-for="user in approvalFilteredUsers"
                                :key="`approval-user-${user.id}`"
                                class="flex items-start gap-2 text-sm"
                            >
                                <input
                                    type="radio"
                                    name="approval"
                                    :checked="isApprovalSelected('user', user.id)"
                                    @change="setApproval('user', user)"
                                />
                                <span>
                                    <span class="font-medium text-ink">{{ user.name }}</span>
                                    <span v-if="user.email" class="block text-xs text-muted">{{ user.email }}</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-border p-4">
                        <div class="mb-3 text-sm font-semibold text-ink">Lectures</div>
                        <div v-if="inviteLoading.lectures" class="text-xs text-muted">Loading...</div>
                        <div v-else class="grid gap-2">
                            <label
                                v-for="lecture in approvalFilteredLectures"
                                :key="`approval-lecture-${lecture.id}`"
                                class="flex items-start gap-2 text-sm"
                            >
                                <input
                                    type="radio"
                                    name="approval"
                                    :checked="isApprovalSelected('lecture', lecture.id)"
                                    @change="setApproval('lecture', lecture)"
                                />
                                <span class="font-medium text-ink">{{ lecture.name }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-border p-4">
                        <div class="mb-3 text-sm font-semibold text-ink">Students</div>
                        <div v-if="inviteLoading.students" class="text-xs text-muted">Loading...</div>
                        <div v-else class="grid gap-2">
                            <label
                                v-for="student in approvalFilteredStudents"
                                :key="`approval-student-${student.id}`"
                                class="flex items-start gap-2 text-sm"
                            >
                                <input
                                    type="radio"
                                    name="approval"
                                    :checked="isApprovalSelected('student', student.id)"
                                    @change="setApproval('student', student)"
                                />
                                <span>
                                    <span class="font-medium text-ink">{{ student.name }}</span>
                                    <span v-if="student.year" class="block text-xs text-muted">Year: {{ student.year }}</span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <template #footer>
                <button
                    class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                    type="button"
                    @click="closeApprovalModal"
                >
                    Done
                </button>
            </template>
        </Modal>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Repository from '../../repository';
import Modal from '../../components/Modal.vue';
import RichTextEditor from '../../components/RichTextEditor.vue';

export default {
    components: {
        Loading,
        Modal,
        RichTextEditor,
    },
    data() {
        return {
            baseUrl: '/api/letters',
            loading: false,
            submitting: false,
            errorMessage: '',
            inviteModalOpen: false,
            inviteKeyword: '',
            approvalModalOpen: false,
            approvalKeyword: '',
            approvalLabel: 'Kepala Departemen',
            inviteLoading: {
                users: false,
                lectures: false,
                students: false,
            },
            inviteSources: {
                users: [],
                lectures: [],
                students: [],
            },
            statusOptions: [
                { value: 0, label: 'Draft' },
                { value: 1, label: 'Published' },
            ],
            studyPrograms: [],
            form: {
                id: null,
                number: '',
                date: '',
                title: '',
                subtitle: '',
                intro: '',
                body: '',
                outro: '',
                attachment_content: '',
                attachment_label: '',
                custom_invitation: '',
                status: 1,
                study_program_code: '',
                participants: [],
                approval: null,
            },
        };
    },
    computed: {
        isEdit() {
            return Boolean(this.$route.params && this.$route.params.id);
        },
        letterId() {
            return this.$route.params ? this.$route.params.id : null;
        },
        inviteParticipants() {
            return Array.isArray(this.form.participants) ? this.form.participants : [];
        },
        filteredUsers() {
            return this.filterInviteList(this.inviteSources.users, 'name', 'email');
        },
        filteredLectures() {
            return this.filterInviteList(this.inviteSources.lectures, 'name');
        },
        filteredStudents() {
            return this.filterInviteList(this.inviteSources.students, 'name', 'year');
        },
        approvalParticipant() {
            return this.form && this.form.approval ? this.form.approval : null;
        },
        approvalFilteredUsers() {
            return this.filterApprovalList(this.inviteSources.users, 'name', 'email');
        },
        approvalFilteredLectures() {
            return this.filterApprovalList(this.inviteSources.lectures, 'name');
        },
        approvalFilteredStudents() {
            return this.filterApprovalList(this.inviteSources.students, 'name', 'year');
        },
    },
    created() {
        this.fetchInviteSources();
        this.fetchStudyPrograms();
        if (this.isEdit) {
            this.fetchLetter();
        }
    },
    methods: {
        fetchStudyPrograms() {
            return Repository.get('/api/study-program-list')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.studyPrograms = Array.isArray(result) ? result : [];
                })
                .catch(() => {
                    this.studyPrograms = [];
                });
        },
        filterInviteList(list, ...fields) {
            const items = Array.isArray(list) ? list : [];
            const keyword = (this.inviteKeyword || '').toLowerCase().trim();
            if (!keyword) {
                return items;
            }

            return items.filter((item) => {
                return fields.some((field) => {
                    const value = item && item[field] !== undefined && item[field] !== null ? String(item[field]) : '';
                    return value.toLowerCase().includes(keyword);
                });
            });
        },
        filterApprovalList(list, ...fields) {
            const items = Array.isArray(list) ? list : [];
            const keyword = (this.approvalKeyword || '').toLowerCase().trim();
            if (!keyword) {
                return items;
            }

            return items.filter((item) => {
                return fields.some((field) => {
                    const value = item && item[field] !== undefined && item[field] !== null ? String(item[field]) : '';
                    return value.toLowerCase().includes(keyword);
                });
            });
        },
        inviteKey(participant) {
            return `${participant.auth_type}:${participant.auth_id}`;
        },
        isInviteSelected(type, id) {
            return this.inviteParticipants.some((participant) => {
                return String(participant.auth_type) === String(type) && String(participant.auth_id) === String(id);
            });
        },
        toggleInvite(type, entity) {
            const id = entity && entity.id ? entity.id : null;
            if (!id) {
                return;
            }

            const key = `${type}:${id}`;
            const existsIndex = this.inviteParticipants.findIndex((participant) => this.inviteKey(participant) === key);
            if (existsIndex >= 0) {
                const copy = [...this.inviteParticipants];
                copy.splice(existsIndex, 1);
                this.form.participants = copy;
                return;
            }

            const name = entity && entity.name ? entity.name : '';
            const payload = {
                auth_type: type,
                auth_id: id,
                auth_name: name,
                email: entity && entity.email ? entity.email : null,
                phone: entity && entity.phone ? entity.phone : null,
            };

            this.form.participants = [...this.inviteParticipants, payload];
        },
        removeInvite(participant) {
            const key = this.inviteKey(participant);
            this.form.participants = this.inviteParticipants.filter((item) => this.inviteKey(item) !== key);
        },
        openInviteModal() {
            this.inviteModalOpen = true;
        },
        closeInviteModal() {
            this.inviteModalOpen = false;
        },
        openApprovalModal() {
            this.approvalModalOpen = true;
        },
        closeApprovalModal() {
            this.approvalModalOpen = false;
        },
        isApprovalSelected(type, id) {
            const approval = this.approvalParticipant;
            if (!approval) {
                return false;
            }
            return String(approval.auth_type) === String(type) && String(approval.auth_id) === String(id);
        },
        setApproval(type, entity) {
            const id = entity && entity.id ? entity.id : null;
            if (!id) {
                return;
            }

            const name = entity && entity.name ? entity.name : '';
            this.form.approval = {
                auth_type: type,
                auth_id: id,
                auth_name: name,
                email: entity && entity.email ? entity.email : null,
                phone: entity && entity.phone ? entity.phone : null,
                label: this.approvalLabel || 'Kepala Departemen',
            };
        },
        clearApproval() {
            this.form.approval = null;
        },
        fetchInviteSources() {
            this.inviteLoading = { users: true, lectures: true, students: true };

            const users = Repository.get('/api/user-list')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.inviteSources.users = Array.isArray(result) ? result : [];
                })
                .catch(() => {
                    this.inviteSources.users = [];
                })
                .finally(() => {
                    this.inviteLoading.users = false;
                });

            const lectures = Repository.get('/api/lecture-list')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.inviteSources.lectures = Array.isArray(result) ? result : [];
                })
                .catch(() => {
                    this.inviteSources.lectures = [];
                })
                .finally(() => {
                    this.inviteLoading.lectures = false;
                });

            const students = Repository.get('/api/student-list')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.inviteSources.students = Array.isArray(result) ? result : [];
                })
                .catch(() => {
                    this.inviteSources.students = [];
                })
                .finally(() => {
                    this.inviteLoading.students = false;
                });

            return Promise.all([users, lectures, students]);
        },
        fetchLetter() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(`${this.baseUrl}/${this.letterId}`)
                .then((response) => {
                    const letter = response && response.data ? response.data.result : null;
                    const participants = letter && Array.isArray(letter.participants) ? letter.participants : [];
                    const approval = participants.find((participant) => String(participant.type) === 'approval') || null;
                    this.form = {
                        id: letter ? letter.id : null,
                        number: letter && letter.number ? letter.number : '',
                        date: letter && letter.date ? letter.date : '',
                        title: letter && letter.title ? letter.title : '',
                        subtitle: letter && letter.subtitle ? letter.subtitle : '',
                        intro: letter && letter.intro ? letter.intro : '',
                        body: letter && letter.body ? letter.body : '',
                        outro: letter && letter.outro ? letter.outro : '',
                        attachment_content: letter && letter.attachment_content ? letter.attachment_content : '',
                        attachment_label: letter && letter.attachment_label ? letter.attachment_label : '',
                        custom_invitation: letter && letter.custom_invitation ? letter.custom_invitation : '',
                        status: typeof (letter && letter.status) !== 'undefined' && letter && letter.status !== null
                            ? Number(letter.status)
                            : 0,
                        study_program_code: letter && letter.study_program_code ? letter.study_program_code : '',
                        participants: participants
                            .filter((participant) => String(participant.type) === 'invite')
                            .map((participant) => ({
                                auth_type: participant.auth_type,
                                auth_id: participant.auth_id,
                                auth_name: participant.auth_name,
                                email: participant.email ?? null,
                                phone: participant.phone ?? null,
                            })),
                        approval: approval
                            ? {
                                auth_type: approval.auth_type,
                                auth_id: approval.auth_id,
                                auth_name: approval.auth_name,
                                email: approval.email ?? null,
                                phone: approval.phone ?? null,
                                label: approval.label || 'Kepala Departemen',
                            }
                            : null,
                    };
                    this.approvalLabel = approval && approval.label ? approval.label : this.approvalLabel;
                })
                .catch(() => {
                    this.errorMessage = 'Failed to load letter.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        payloadFromForm() {
            const payload = { ...this.form };
            delete payload.id;
            if (!payload.number) {
                delete payload.number;
            }
            if (payload.approval) {
                payload.approval = {
                    ...payload.approval,
                    label: this.approvalLabel || (payload.approval.label || 'Kepala Departemen'),
                };
            }
            return payload;
        },
        submitForm() {
            if (this.submitting || this.loading) {
                return;
            }

            if (this.isEdit) {
                return this.updateLetter();
            }

            return this.createLetter();
        },
        createLetter() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.post(this.baseUrl, this.payloadFromForm())
                .then((response) => {
                    const created = response && response.data ? response.data.result : null;
                    if (created && created.number) {
                        this.form.number = created.number;
                    }
                    this.$showToast('Letter created successfully.');
                    this.$router.push('/blu/letters');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to create letter.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        updateLetter() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.put(`${this.baseUrl}/${this.letterId}`, this.payloadFromForm())
                .then(() => {
                    this.$showToast('Letter updated successfully.');
                    this.$router.push('/blu/letters');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to update letter.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
    },
};
</script>
