<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Activity Management</div>
                <h1 class="text-2xl font-semibold text-ink">
                    {{ isEdit ? 'Edit Activity' : 'Add Activity' }}
                </h1>
            </div>
            <div class="flex items-center gap-2">
                <router-link class="rounded-xl border border-border px-4 py-2 text-sm text-muted" to="/cblu/activities">
                    Back
                </router-link>
                <button class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white" type="button"
                    :disabled="submitting" @click="submitForm">
                    {{ submitting ? 'Saving...' : 'Save Activity' }}
                </button>
            </div>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-6">
            <div class="grid gap-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Nama</span>
                        <input v-model.trim="form.name" type="text" placeholder="ex: Seminar Hasil"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Pembicara</span>
                        <input v-model.trim="form.speaker" type="text" placeholder="dr. Don Joe"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
                    </label>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Judul</span>
                        <input v-model.trim="form.title"
                            placeholder="ex: Perbedaan Derajat Aliran Koroner TIMI 3 Antara..."
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Stase</span>
                        <select v-model="form.stase_id"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                            <option :value="null">Pilih Stase</option>
                            <option v-for="stase in stases" :key="stase.id" :value="stase.id">
                                {{ stase.name }}
                            </option>
                        </select>
                    </label>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Mulai</span>
                        <input v-model="form.start_date" type="datetime-local"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Selesai</span>
                        <input v-model="form.end_date" type="datetime-local"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
                    </label>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="grid gap-3 text-sm">
                        <span class="text-muted">Tipe</span>
                        <label class="flex items-center gap-2 rounded-xl border border-border bg-white px-3 py-2">
                            <input v-model="form.type" type="radio" value="0" class="h-4 w-4" />
                            <span>Umum</span>
                            <span class="text-xs text-muted">residen dan atau staff</span>
                        </label>
                        <label class="flex items-center gap-2 rounded-xl border border-border bg-white px-3 py-2">
                            <input v-model="form.type" type="radio" value="3" class="h-4 w-4" />
                            <span>Staff</span>
                            <span class="text-xs text-muted">hanya staff</span>
                        </label>
                        <label class="flex items-center gap-2 rounded-xl border border-border bg-white px-3 py-2">
                            <input v-model="form.type" type="radio" value="1" class="h-4 w-4" />
                            <span>Residen</span>
                            <span class="text-xs text-muted">hanya residen</span>
                        </label>
                        <label class="flex items-center gap-2 rounded-xl border border-border bg-white px-3 py-2">
                            <input v-model="form.type" type="radio" value="2" class="h-4 w-4" />
                            <span>Residen Wajib</span>
                            <span class="text-xs text-muted">(semua/sebagian besar residen harus mengikuti)</span>
                        </label>
                    </div>
                    <div class="grid gap-4">
                        <label class="grid gap-2 text-sm">
                            <span class="text-muted">Tempat</span>
                            <input v-model.trim="form.place" type="text"
                                placeholder="ex: Zoom id 123123, Ruang Konferensi..."
                                class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
                        </label>
                        <label class="grid gap-2 text-sm">
                            <span class="text-muted">Kategori</span>
                            <select v-model="form.category"
                                class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                                <option :value="null">Pilih Kategori</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.name }}
                                </option>
                            </select>
                        </label>
                        <label class="grid gap-2 text-sm">
                            <span class="text-muted">Status</span>
                            <select v-model="form.status"
                                class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                                <option value="active">Active</option>
                                <option value="draft">Draft</option>
                                <option value="nonactive">Nonactive</option>
                            </select>
                        </label>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Link</span>
                        <input v-model.trim="form.link" type="text" placeholder="https://meet.google.com/..."
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Passcode</span>
                        <input v-model.trim="form.passcode" type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
                    </label>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Catatan</span>
                        <textarea v-model.trim="form.note"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"></textarea>
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Deskripsi</span>
                        <textarea v-model.trim="form.desc"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"></textarea>
                    </label>
                </div>

                <div class="grid gap-3">
                    <div class="text-sm font-medium text-ink">Staff</div>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="grid gap-2 rounded-2xl border border-border bg-white p-4">
                            <div class="text-xs uppercase tracking-[0.2em] text-muted">Penguji</div>
                            <div class="flex flex-wrap gap-1">
                                <span v-if="selectedLectures('lecture_penguji').length === 0"
                                    class="text-xs text-muted">
                                    Belum dipilih
                                </span>
                                <span v-for="lecture in selectedLectures('lecture_penguji')"
                                    :key="`penguji-${lecture.id}`"
                                    class="rounded-xl bg-slate-100 px-2 py-1 text-xs text-slate-700">
                                    {{ lecture.name }}
                                </span>
                            </div>
                            <button class="mt-1 rounded-xl border border-border px-3 py-2 text-xs text-muted"
                                type="button" @click="openLectureModal('lecture_penguji')">
                                Pilih Penguji
                            </button>
                        </div>
                        <div class="grid gap-2 rounded-2xl border border-border bg-white p-4">
                            <div class="text-xs uppercase tracking-[0.2em] text-muted">Pembimbing</div>
                            <div class="flex flex-wrap gap-1">
                                <span v-if="selectedLectures('lecture_pembimbing').length === 0"
                                    class="text-xs text-muted">
                                    Belum dipilih
                                </span>
                                <span v-for="lecture in selectedLectures('lecture_pembimbing')"
                                    :key="`pembimbing-${lecture.id}`"
                                    class="rounded-lg bg-slate-100 px-2 py-1 text-xs text-slate-700">
                                    {{ lecture.name }}
                                </span>
                            </div>
                            <button class="mt-1 rounded-xl border border-border px-3 py-2 text-xs text-muted"
                                type="button" @click="openLectureModal('lecture_pembimbing')">
                                Pilih Pembimbing
                            </button>
                        </div>
                        <div class="grid gap-2 rounded-2xl border border-border bg-white p-4">
                            <div class="text-xs uppercase tracking-[0.2em] text-muted">Pengampu</div>
                            <div class="flex flex-wrap gap-1">
                                <span v-if="selectedLectures('lecture_pengampu').length === 0"
                                    class="text-xs text-muted">
                                    Belum dipilih
                                </span>
                                <span v-for="lecture in selectedLectures('lecture_pengampu')"
                                    :key="`pengampu-${lecture.id}`"
                                    class="rounded-lg bg-slate-100 px-2 py-1 text-xs text-slate-700">
                                    {{ lecture.name }}
                                </span>
                            </div>
                            <button class="mt-1 rounded-xl border border-border px-3 py-2 text-xs text-muted"
                                type="button" @click="openLectureModal('lecture_pengampu')">
                                Pilih Pengampu
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="errorMessage"
                    class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ errorMessage }}
                </div>
            </div>
        </section>

        <Modal :open="lectureModalOpen" title="Pilih Dosen" eyebrow="Lecture List" size="lg"
            @close="closeLectureModal">
            <div class="grid gap-3">
                <div class="text-xs text-muted">
                    Pilih staff untuk:
                    <span class="font-semibold text-ink">{{ lectureRoleLabel }}</span>
                </div>

                <div v-if="lectureLoading" class="rounded-xl border border-border bg-white px-3 py-6 text-center text-sm text-muted">
                    Loading lectures...
                </div>

                <div v-else-if="lectureOptions.length === 0"
                    class="rounded-xl border border-border bg-white px-3 py-6 text-center text-sm text-muted">
                    Tidak ada data dosen.
                </div>

                <div v-else class="grid max-h-[60vh] gap-2 overflow-auto rounded-xl border border-border bg-white p-3">
                    <label v-for="lecture in lectureOptions" :key="lecture.id"
                        class="flex cursor-pointer items-center gap-3 rounded-xl border border-transparent px-3 py-2 hover:border-border hover:bg-slate-50">
                        <input class="h-4 w-4" type="checkbox" :checked="isLectureSelected(lecture.id)"
                            @change="toggleLectureSelection(lecture.id, $event.target.checked)" />
                        <span class="text-sm text-ink">{{ lecture.name }}</span>
                    </label>
                </div>
            </div>

            <template #footer>
                <button class="rounded-xl border border-border px-4 py-2 text-sm text-muted" type="button"
                    @click="closeLectureModal">
                    Selesai
                </button>
            </template>
        </Modal>
    </div>
</template>

<script>
import Modal from '../../components/Modal.vue';
import Repository from '../../repository';
import Swal from 'sweetalert2';

export default {
    components: {
        Modal,
    },
    data() {
        return {
            baseUrl: '/api/activities',
            submitting: false,
            loading: false,
            errorMessage: '',
            toast: null,
            stases: [],
            lectureOptions: [],
            lectureLoading: false,
            lectureModalOpen: false,
            lectureModalRole: '',
            categories: [
                { id: 0, name: 'Lain-lain' },
                { id: 1, name: 'Stase' },
                { id: 2, name: 'Tesis' },
                { id: 3, name: 'Laporan Jaga' },
                { id: 4, name: 'Laporan Kasus' },
                { id: 5, name: 'Club' },
                { id: 6, name: 'Ilmiah Divisi' },
            ],
            form: {
                id: null,
                name: '',
                title: '',
                place: '',
                desc: '',
                note: '',
                speaker: '',
                link: '',
                passcode: '',
                status: 'active',
                start_date: '',
                end_date: '',
                stase_id: null,
                type: null,
                category: null,
                lecture_penguji: [],
                lecture_pembimbing: [],
                lecture_pengampu: [],
            },
        };
    },
    computed: {
        isEdit() {
            return this.$route.params && this.$route.params.id && this.$route.params.id !== 'create';
        },
        lectureById() {
            const map = {};
            this.lectureOptions.forEach((lecture) => {
                map[lecture.id] = lecture;
            });
            return map;
        },
        lectureRoleLabel() {
            if (this.lectureModalRole === 'lecture_penguji') {
                return 'Penguji';
            }
            if (this.lectureModalRole === 'lecture_pembimbing') {
                return 'Pembimbing';
            }
            if (this.lectureModalRole === 'lecture_pengampu') {
                return 'Pengampu';
            }
            return '-';
        },
    },
    created() {
        this.initToast();
        this.fetchStases();
        this.fetchLectures();
        if (this.isEdit) {
            this.fetchActivity();
        }
    },
    methods: {
        initToast() {
            this.toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        },
        showToast(title, icon = 'success') {
            if (!this.toast) {
                this.initToast();
            }
            this.toast.fire({ title, icon });
        },
        fetchStases() {
            return Repository.get('/api/stases', {
                params: {
                    per_page: 100,
                },
            })
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const list = result && Array.isArray(result.data) ? result.data : [];
                    this.stases = list;
                })
                .catch(() => {
                    this.stases = [];
                });
        },
        fetchLectures() {
            this.lectureLoading = true;
            return Repository.get('/api/lecture-list')
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.lectureOptions = Array.isArray(result) ? result : [];
                })
                .catch(() => {
                    this.lectureOptions = [];
                })
                .finally(() => {
                    this.lectureLoading = false;
                });
        },
        normalizeLectureIds(value) {
            if (Array.isArray(value)) {
                return value.map((id) => Number(id)).filter((id) => !Number.isNaN(id));
            }
            if (typeof value === 'string' && value.trim() !== '') {
                try {
                    const parsed = JSON.parse(value);
                    if (Array.isArray(parsed)) {
                        return parsed.map((id) => Number(id)).filter((id) => !Number.isNaN(id));
                    }
                } catch (error) {
                    return [];
                }
            }
            return [];
        },
        fetchActivity() {
            this.loading = true;
            return Repository.get(`${this.baseUrl}/${this.$route.params.id}`)
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    if (!result) {
                        return;
                    }
                    const merged = {
                        ...this.form,
                        ...result,
                    };
                    merged.lecture_penguji = this.normalizeLectureIds(merged.lecture_penguji);
                    merged.lecture_pembimbing = this.normalizeLectureIds(merged.lecture_pembimbing);
                    merged.lecture_pengampu = this.normalizeLectureIds(merged.lecture_pengampu);
                    this.form = merged;
                })
                .catch(() => {
                    this.errorMessage = 'Failed to load activity.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        selectedLectures(roleKey) {
            const ids = Array.isArray(this.form[roleKey]) ? this.form[roleKey] : [];
            return ids
                .map((id) => this.lectureById[id])
                .filter((lecture) => lecture && lecture.name);
        },
        openLectureModal(roleKey) {
            this.lectureModalRole = roleKey;
            this.lectureModalOpen = true;
            if (!this.lectureOptions.length && !this.lectureLoading) {
                this.fetchLectures();
            }
        },
        closeLectureModal() {
            this.lectureModalOpen = false;
        },
        isLectureSelected(lectureId) {
            const roleKey = this.lectureModalRole;
            if (!roleKey) {
                return false;
            }
            const ids = Array.isArray(this.form[roleKey]) ? this.form[roleKey] : [];
            return ids.includes(Number(lectureId));
        },
        toggleLectureSelection(lectureId, checked) {
            const roleKey = this.lectureModalRole;
            if (!roleKey) {
                return;
            }

            const current = Array.isArray(this.form[roleKey]) ? [...this.form[roleKey]] : [];
            const idNum = Number(lectureId);
            if (Number.isNaN(idNum)) {
                return;
            }

            const next = checked
                ? Array.from(new Set([...current, idNum]))
                : current.filter((id) => id !== idNum);

            this.form = {
                ...this.form,
                [roleKey]: next,
            };
        },
        submitForm() {
            if (this.submitting) {
                return;
            }

            this.submitting = true;
            this.errorMessage = '';

            const payload = {
                name: this.form.name,
                title: this.form.title,
                place: this.form.place,
                desc: this.form.desc,
                note: this.form.note,
                speaker: this.form.speaker,
                link: this.form.link,
                passcode: this.form.passcode,
                status: this.form.status,
                start_date: this.form.start_date,
                end_date: this.form.end_date,
                stase_id: this.form.stase_id,
                type: this.form.type,
                category: this.form.category,
                lecture_penguji: JSON.stringify(this.form.lecture_penguji || []),
                lecture_pembimbing: JSON.stringify(this.form.lecture_pembimbing || []),
                lecture_pengampu: JSON.stringify(this.form.lecture_pengampu || []),
            };

            const request = this.isEdit
                ? Repository.put(`${this.baseUrl}/${this.form.id}`, payload)
                : Repository.post(this.baseUrl, payload);

            request
                .then(() => {
                    this.showToast(this.isEdit ? 'Activity updated successfully.' : 'Activity created successfully.');
                    this.$router.push('/cblu/activities');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to save activity.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
    },
};
</script>
