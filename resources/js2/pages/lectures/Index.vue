<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Lecture Management</div>
                <h1 class="text-2xl font-semibold text-ink">Lectures</h1>
            </div>
            <button
                class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                type="button"
                @click="openCreate"
            >
                Add Lecture
            </button>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="text-xs text-muted">Keyword</label>
                    <input
                        v-model.trim="filters.keyword"
                        type="text"
                        @keyup.enter="applyFilter"
                        placeholder="Search name or email..."
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="min-w-[200px]">
                    <label class="text-xs text-muted">Study Program</label>
                    <select
                        v-model="filters.study_program_code"
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >
                        <option value="">All</option>
                        <option v-for="option in studyPrograms" :key="option.id" :value="option.code">
                            {{ option.name }}
                        </option>
                    </select>
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
                <div class="font-semibold">Lectures</div>
                <div class="text-xs text-muted" v-if="pagination.total">
                    {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }}
                </div>
            </div>
            <div
                v-if="errorMessage && !modalOpen"
                class="border-b border-rose-100 bg-rose-50 px-5 py-3 text-xs text-rose-600"
            >
                {{ errorMessage }}
            </div>
            <div class="divide-y divide-border">
                <div v-if="!loading && lectures.length === 0" class="px-5 py-6 text-sm text-muted">
                    No lectures found.
                </div>
                <div
                    v-for="(lecture, index) in lectures"
                    :key="lecture.id"
                    class="flex flex-wrap items-center gap-3 px-5 py-4"
                >
                    <div class="w-8 text-sm font-semibold text-muted">
                        {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                    </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <div class="font-semibold text-ink">{{ lecture.name }}</div>
                        <span v-if="lecture.is_in_house" class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                            In House
                        </span>
                    </div>
                    <div class="text-xs text-muted">
                        <span v-if="lecture.number">No: {{ lecture.number }}</span>
                        <span v-if="lecture.email && lecture.number">•</span>
                        <span v-if="lecture.email">{{ lecture.email }}</span>
                        <span v-if="lecture.phone">• {{ lecture.phone }}</span>
                        <span v-if="lecture.name_alt">• {{ lecture.name_alt }}</span>
                    </div>
                </div>
                    <div class="flex w-24 flex-col items-center gap-1">
                        <button
                            type="button"
                            role="switch"
                            :aria-checked="lecture.status === 'active'"
                            :disabled="statusUpdating"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition disabled:opacity-50"
                            :class="lecture.status === 'active' ? 'bg-emerald-500' : 'bg-slate-300'"
                            @click.stop="handleStatusToggle(lecture)"
                        >
                            <span
                                class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition"
                                :class="lecture.status === 'active' ? 'translate-x-6' : 'translate-x-1'"
                            ></span>
                        </button>
                        <span class="text-[11px] text-muted">
                            {{ lecture.status === 'active' ? 'Active' : 'Nonactive' }}
                        </span>
                    </div>
                    <div class="relative action-dropdown">
                        <button
                            class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                            type="button"
                            @click.stop="toggleActionMenu(lecture.id)"
                        >
                            Actions
                        </button>
                        <div
                            v-if="actionMenuOpenId === lecture.id"
                            class="absolute right-0 z-10 mt-2 w-36 rounded-xl border border-border bg-white p-1 shadow-lg"
                        >
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                type="button"
                                @click="handleAction('logAs', lecture)"
                            >
                                Log As
                            </button>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-ink hover:bg-slate-50"
                                type="button"
                                @click="handleAction('edit', lecture)"
                            >
                                Edit
                            </button>
                            <button
                                class="flex w-full items-center rounded-lg px-3 py-2 text-left text-xs text-rose-600 hover:bg-rose-50"
                                type="button"
                                @click="handleAction('delete', lecture)"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
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

        <Modal
            :open="modalOpen"
            :title="editMode ? 'Edit Lecture' : 'Create Lecture'"
            :eyebrow="editMode ? 'Update lecture' : 'New lecture'"
            size="xl"
            @close="closeModal"
        >
            <div class="flex gap-1 border-b border-border">
                <button
                    type="button"
                    class="border-b-2 px-4 py-2 text-sm font-medium"
                    :class="activeTab === 'account' ? 'border-primary text-primary' : 'border-transparent text-muted'"
                    @click="activeTab = 'account'"
                >
                    Account
                </button>
                <button
                    type="button"
                    class="border-b-2 px-4 py-2 text-sm font-medium"
                    :class="activeTab === 'information' ? 'border-primary text-primary' : 'border-transparent text-muted'"
                    @click="activeTab = 'information'"
                >
                    Information
                </button>
            </div>

            <form class="grid gap-4 pt-4" @submit.prevent="submitForm">
                <div v-show="activeTab === 'account'" class="grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Number</span>
                        <input
                            v-model.trim="form.number"
                            type="text"
                            placeholder="Lecture number"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Name with Title</span>
                        <input
                            v-model.trim="form.name_alt"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Name</span>
                        <input
                            v-model.trim="form.name"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Email</span>
                        <input
                            v-model.trim="form.email"
                            type="email"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Password</span>
                        <input
                            v-model.trim="form.password"
                            type="password"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Status</span>
                        <select
                            v-model="form.status"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        >
                            <option value="active">Active</option>
                            <option value="nonactive">Nonactive</option>
                        </select>
                    </label>
                    <label class="flex items-center gap-3 text-sm">
                        <input
                            v-model.number="form.is_in_house"
                            type="checkbox"
                            :true-value="1"
                            :false-value="0"
                            class="h-4 w-4 rounded border border-border"
                        />
                        <span class="text-muted">In House</span>
                    </label>
                    <div class="grid gap-2 text-sm md:col-span-2">
                        <span class="text-muted">Study Programs</span>
                        <div class="grid gap-2 rounded-xl border border-border bg-white p-3 sm:grid-cols-2">
                            <span v-if="studyPrograms.length === 0" class="text-xs text-muted">
                                No study programs found.
                            </span>
                            <label
                                v-for="option in studyPrograms"
                                :key="option.id"
                                class="flex items-center gap-2 text-sm"
                            >
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 rounded border border-border"
                                    :checked="isStudyProgramSelected(option.code)"
                                    @change="toggleStudyProgramSelection(option.code, $event.target.checked)"
                                />
                                <span class="text-ink">{{ option.name }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div v-show="activeTab === 'information'" class="grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Code</span>
                        <input
                            v-model.trim="form.code"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Degree</span>
                        <input
                            v-model.trim="form.degree"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Place of Birth</span>
                        <input
                            v-model.trim="form.pob"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Date of Birth</span>
                        <input
                            v-model="form.dob"
                            type="date"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Phone</span>
                        <input
                            v-model.trim="form.phone"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Register Date</span>
                        <input
                            v-model="form.register_date"
                            type="date"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm md:col-span-2">
                        <span class="text-muted">Address</span>
                        <textarea
                            v-model.trim="form.address"
                            rows="3"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        ></textarea>
                    </label>
                    <div class="grid gap-2 text-sm md:col-span-2">
                        <span class="text-muted">Photo</span>
                        <div class="flex items-center gap-4">
                            <img
                                v-if="imagePreviewUrl"
                                :src="imagePreviewUrl"
                                alt="Preview"
                                class="h-16 w-16 rounded-xl border border-border object-cover"
                            />
                            <div
                                v-else
                                class="flex h-16 w-16 items-center justify-center rounded-xl border border-dashed border-border text-center text-[10px] text-muted"
                            >
                                No photo
                            </div>
                            <div class="grid gap-1">
                                <input
                                    type="file"
                                    accept="image/*"
                                    class="text-sm"
                                    :disabled="uploadingImage"
                                    @change="handleImageChange"
                                />
                                <span v-if="uploadingImage" class="text-xs text-muted">Uploading...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="errorMessage" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ errorMessage }}
                </div>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="submit"
                    :disabled="submitting"
                >
                    {{ submitting ? 'Saving...' : editMode ? 'Update Lecture' : 'Create Lecture' }}
                </button>
            </form>
        </Modal>

        <Modal
            :open="statusModalOpen"
            title="Aktifkan Akun?"
            size="sm"
            @close="closeStatusModal"
        >
            <div class="grid gap-4 text-sm">
                <p class="text-muted">
                    Akun
                    <span class="font-semibold text-ink">{{ statusTargetLecture ? statusTargetLecture.name : '' }}</span>
                    akan diaktifkan.
                </p>
                <label class="flex items-center gap-3 text-sm">
                    <input
                        v-model="sendActivationEmail"
                        type="checkbox"
                        class="h-4 w-4 rounded border border-border"
                    />
                    <span class="text-muted">Kirim notifikasi email ke pengguna</span>
                </label>
            </div>
            <template #footer>
                <button
                    class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                    type="button"
                    @click="closeStatusModal"
                >
                    Batal
                </button>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="button"
                    :disabled="statusUpdating"
                    @click="confirmActivation"
                >
                    {{ statusUpdating ? 'Processing...' : 'Ya' }}
                </button>
            </template>
        </Modal>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Modal from '../../components/Modal.vue';
import Repository from '../../repository';
import persistFilters from '../../mixins/persistFilters';
import { uploadFirebaseFile } from '../../upload';

export default {
    components: {
        Loading,
        Modal,
    },
    mixins: [persistFilters('lectures')],
    data() {
        return {
            baseUrl: '/api/lectures',
            lectures: [],
            pagination: {},
            studyPrograms: [],
            filters: {
                keyword: '',
                study_program_code: '',
                page: 1,
            },
            form: {
                id: null,
                number: '',
                name: '',
                email: '',
                password: '',
                name_alt: '',
                last_act: '',
                status: null,
                is_in_house: 0,
                study_program_codes: [],
                code: '',
                degree: '',
                pob: '',
                dob: '',
                phone: '',
                address: '',
                image: '',
                register_date: '',
            },
            activeTab: 'account',
            editMode: false,
            modalOpen: false,
            loading: false,
            submitting: false,
            uploadingImage: false,
            errorMessage: '',
            actionMenuOpenId: null,
            statusModalOpen: false,
            statusTargetLecture: null,
            sendActivationEmail: true,
            statusUpdating: false,
        };
    },
    computed: {
        imagePreviewUrl() {
            if (!this.form.image) {
                return '';
            }

            return this.form.image.startsWith('http') ? this.form.image : `/storage/${this.form.image}`;
        },
    },
    created() {
        this.fetchStudyPrograms();
        this.fetchLectures();
    },
    mounted() {
        document.addEventListener('click', this.handleDocumentClick);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.handleDocumentClick);
    },
    methods: {
        async handleImageChange(event) {
            const file = event && event.target ? event.target.files[0] : null;
            if (!file) {
                return;
            }

            if (!file.type || !file.type.startsWith('image/')) {
                this.errorMessage = 'Please select an image file.';
                event.target.value = '';
                return;
            }

            if (file.size > 4200000) {
                this.errorMessage = 'Image must be smaller than 4MB.';
                event.target.value = '';
                return;
            }

            this.uploadingImage = true;
            this.errorMessage = '';

            try {
                const url = await uploadFirebaseFile({ file, prefix: 'Lecture/Profile' });
                if (url) {
                    this.form.image = url;
                } else {
                    this.errorMessage = 'Failed to upload image.';
                }
            } catch (error) {
                this.errorMessage = 'Failed to upload image.';
            } finally {
                this.uploadingImage = false;
                event.target.value = '';
            }
        },
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
        isStudyProgramSelected(code) {
            const codes = Array.isArray(this.form.study_program_codes) ? this.form.study_program_codes : [];
            return codes.includes(code);
        },
        toggleStudyProgramSelection(code, checked) {
            const current = Array.isArray(this.form.study_program_codes) ? [...this.form.study_program_codes] : [];
            this.form.study_program_codes = checked
                ? Array.from(new Set([...current, code]))
                : current.filter((item) => item !== code);
        },
        fetchLectures() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(this.baseUrl, {
                params: this.filters,
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const data = result && Array.isArray(result.data) ? result.data : [];

                    this.lectures = data;
                    this.pagination = result || {};
                })
                .catch(() => {
                    this.lectures = [];
                    this.pagination = {};
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        applyFilter() {
            this.filters.page = 1;
            this.fetchLectures();
        },
        resetFilter() {
            this.filters.keyword = '';
            this.filters.study_program_code = '';
            this.filters.page = 1;
            this.fetchLectures();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchLectures();
        },
        toggleActionMenu(lectureId) {
            this.actionMenuOpenId = this.actionMenuOpenId === lectureId ? null : lectureId;
        },
        closeActionMenu() {
            this.actionMenuOpenId = null;
        },
        handleAction(action, lecture) {
            this.closeActionMenu();
            if (action === 'logAs') {
                this.logAs(lecture);
                return;
            }
            if (action === 'edit') {
                this.openEdit(lecture);
                return;
            }
            if (action === 'delete') {
                this.deleteLecture(lecture);
            }
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
        openCreate() {
            this.editMode = false;
            this.resetForm();
            this.activeTab = 'account';
            this.errorMessage = '';
            this.modalOpen = true;
        },
        openEdit(lecture) {
            this.editMode = true;
            this.form = {
                id: lecture.id,
                number: lecture.number || '',
                name: lecture.name || '',
                email: lecture.email || '',
                password: '',
                name_alt: lecture.name_alt || '',
                last_act: lecture.last_act || '',
                status: lecture.status ?? null,
                is_in_house: lecture.is_in_house ? 1 : 0,
                study_program_codes: Array.isArray(lecture.study_program_codes) ? lecture.study_program_codes : [],
                code: lecture.code || '',
                degree: lecture.degree || '',
                pob: lecture.pob || '',
                dob: this.formatDateInput(lecture.dob),
                phone: lecture.phone || '',
                address: lecture.address || '',
                image: lecture.image || '',
                register_date: this.formatDateInput(lecture.register_date),
            };
            this.activeTab = 'account';
            this.errorMessage = '';
            this.modalOpen = true;
        },
        closeModal() {
            this.modalOpen = false;
            this.errorMessage = '';
            if (!this.editMode) {
                this.resetForm();
            }
        },
        resetForm() {
            this.form = {
                id: null,
                number: '',
                name: '',
                email: '',
                password: '',
                name_alt: '',
                last_act: '',
                status: null,
                is_in_house: 0,
                study_program_codes: [],
                code: '',
                degree: '',
                pob: '',
                dob: '',
                phone: '',
                address: '',
                image: '',
                register_date: '',
            };
        },
        formatDateInput(value) {
            if (!value) {
                return '';
            }

            return String(value).slice(0, 10);
        },
        submitForm() {
            if (this.editMode) {
                return this.updateLecture();
            }

            return this.createLecture();
        },
        createLecture() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.post(this.baseUrl, this.form)
                .then(() => {
                    this.closeModal();
                    this.fetchLectures();
                    this.$showToast('Lecture created successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to create lecture.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        updateLecture() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.put(`${this.baseUrl}/${this.form.id}`, this.form)
                .then(() => {
                    this.fetchLectures();
                    this.closeModal();
                    this.$showToast('Lecture updated successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to update lecture.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        deleteLecture(lecture) {
            if (!window.confirm(`Delete lecture ${lecture.name}?`)) {
                return;
            }

            Repository.delete(`${this.baseUrl}/${lecture.id}`)
                .then(() => {
                    this.fetchLectures();
                    this.$showToast('Lecture deleted successfully.');
                })
                .catch(() => {
                    this.errorMessage = 'Failed to delete lecture.';
                });
        },
        handleStatusToggle(lecture) {
            if (this.statusUpdating) {
                return;
            }

            if (lecture.status === 'active') {
                this.updateLectureStatus(lecture, 'nonactive');
                return;
            }

            this.statusTargetLecture = lecture;
            this.sendActivationEmail = true;
            this.statusModalOpen = true;
        },
        closeStatusModal() {
            this.statusModalOpen = false;
            this.statusTargetLecture = null;
            this.sendActivationEmail = true;
        },
        confirmActivation() {
            if (!this.statusTargetLecture) {
                return;
            }

            this.updateLectureStatus(this.statusTargetLecture, 'active', this.sendActivationEmail)
                .then(() => {
                    this.closeStatusModal();
                });
        },
        updateLectureStatus(lecture, status, sendEmail = false) {
            this.statusUpdating = true;
            this.errorMessage = '';

            return Repository.patch(`${this.baseUrl}/${lecture.id}/status`, {
                status,
                send_email: sendEmail,
            })
                .then(() => {
                    lecture.status = status;
                    this.$showToast(status === 'active' ? 'Lecture activated successfully.' : 'Lecture deactivated successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to update lecture status.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.statusUpdating = false;
                });
        },
        logAs(lecture) {
            if (!lecture || !lecture.id) {
                return;
            }

            Repository.post('/api/log-as', {
                auth_type: 'lecture',
                auth_id: lecture.id,
            })
                .then((response) => {
                    const token = response && response.data && response.data.result
                        ? response.data.result.token
                        : null;
                    if (!token) {
                        this.errorMessage = 'Failed to log as lecture.';
                        return;
                    }
                    localStorage.setItem('token', token);
                    window.open('/blu/dashboard', '_blank');
                })
                .catch(() => {
                    this.errorMessage = 'Failed to log as lecture.';
                });
        },
    },
};
</script>
