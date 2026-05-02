<template>
    <div class="rounded-2xl border border-border bg-panel p-6">
        <div class="flex items-center justify-between gap-4">
            <div class="text-lg font-semibold">Nilai Pendukung</div>
            <button v-if="canEdit" type="button" class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                @click="showAddModal">
                Tambah
            </button>
        </div>

        <div v-if="message" class="mt-4 rounded-xl border border-border bg-surface px-4 py-3 text-sm">
            {{ message }}
        </div>

        <div class="mt-4 rounded-xl border border-border bg-surface px-4 py-3 text-sm text-slate-700">
            Masukkan nilai sesuai hasil tes untuk Tes Potensi (PAPs UGM, TPDA PLTI), Kemampuan Bahasa Inggris (AcEPT,
            TOEP PLTI), dan UKDI (CBT, OSCE), lalu unggah berkas asli atau salinan legalisir dalam format gambar/PDF
            (maks. 1MB, gabungkan jika lebih dari satu), serta pastikan keaslian dan masa berlaku sebelum mengunggah.
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="text-xs uppercase text-muted">
                    <tr class="border-b border-border">
                        <th class="px-2 py-2 w-12">No</th>
                        <th class="px-2 py-2">Nama</th>
                        <th class="px-2 py-2 hidden lg:table-cell">Nilai</th>
                        <th class="px-2 py-2">Dokumen</th>
                        <th class="px-2 py-2 w-32 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="scores.length === 0">
                        <td colspan="5" class="px-2 py-6 text-center text-sm text-muted">Belum ada data</td>
                    </tr>
                    <tr v-for="(detail, index) in scores" :key="detail.id" class="border-b border-border">
                        <td class="px-2 py-2">{{ index + 1 }}</td>
                        <td class="px-2 py-2 font-semibold">{{ detail.name }}</td>
                        <td class="px-2 py-2 hidden lg:table-cell">{{ detail.desc }}</td>
                        <td class="px-2 py-2">
                            <a v-if="detail.file_url" :href="detail.file_url" target="_blank" rel="noreferrer"
                                class="inline-flex items-center rounded-lg border border-border bg-surface px-3 py-1.5 text-xs text-primary hover:bg-white">
                                Lampiran
                            </a>
                        </td>
                        <td class="px-2 py-2 text-right">
                            <div class="inline-flex gap-2">
                                <button v-if="canEdit" type="button"
                                    class="rounded-lg bg-primary px-3 py-1.5 text-xs font-medium text-white"
                                    @click="showEditModal(detail)">
                                    Edit
                                </button>
                                <button v-if="canEdit" type="button"
                                    class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white"
                                    @click="removeDetail(detail.id)">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Modal :open="modalOpen" title="Nilai Pendukung" :closeOnBackdrop="false" @close="closeModal">
            <div class="grid gap-4">
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Tipe</span>
                    <select class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                        :class="detailErrors.name ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                        v-model="form.name">
                        <option value="Tes Potensi PAPs UGM">Tes Potensi PAPs UGM</option>
                        <option value="Tes Potensi TPDA PLTI">Tes Potensi TPDA PLTI</option>
                        <option value="Kemampuan Bahasa Inggris AcEPT">Kemampuan Bahasa Inggris AcEPT</option>
                        <option value="Kemampuan Bahasa Inggris TOEP PLTI">Kemampuan Bahasa Inggris TOEP PLTI</option>
                        <option value="UKDI CBT">UKDI CBT</option>
                        <option value="UKDI OSCE">UKDI OSCE</option>
                    </select>
                    <span v-if="detailErrors.name" class="text-xs text-red-600">{{ detailErrors.name }}</span>
                </label>

                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Nilai</span>
                    <input class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                        :class="detailErrors.desc ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                        v-model.trim="form.desc" />
                    <span v-if="detailErrors.desc" class="text-xs text-red-600">{{ detailErrors.desc }}</span>
                </label>

                <div class="grid gap-2 text-sm">
                    <span class="text-muted">Dokumen Pendukung (image/pdf, max 1MB)</span>
                    <input type="file" accept="image/*,.pdf"
                        class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm"
                        :disabled="uploading" @change="uploadFile" />
                    <div v-if="uploading" class="text-xs text-muted">Uploading...</div>
                    <span v-if="detailErrors.file_url" class="text-xs text-red-600">{{ detailErrors.file_url }}</span>
                    <div v-if="form.file_url" class="text-xs">
                        <a class="text-primary underline" :href="form.file_url" target="_blank" rel="noreferrer">{{ truncate(form.file_url) }}</a>
                    </div>
                </div>
            </div>
            <template #footer>
                <button type="button"
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
                    :disabled="saving" @click="submit">
                    {{ saving ? 'Please wait...' : (editMode ? 'Simpan' : 'Tambah') }}
                </button>
            </template>
        </Modal>
    </div>
</template>

<script>
import Repository from '../../repository';
import Modal from '../../components/Modal.vue';
import { uploadFirebaseFile } from '../../upload';

export default {
    name: 'RegistrationScores',
    components: { Modal },
    props: { registration: { type: Object, default: null } },
    data() {
        return {
            message: '',
            modalOpen: false,
            editMode: false,
            saving: false,
            uploading: false,
            detailErrors: {},
            form: {
                id: null,
                label: 'score',
                name: 'Tes Potensi PAPs UGM',
                desc: '',
                file_url: '',
            },
        };
    },
    computed: {
        canEdit() {
            return this.registration && this.registration.status === 100;
        },
        scores() {
            const details = this.registration && Array.isArray(this.registration.details) ? this.registration.details : [];
            return details.filter((item) => item && item.label === 'score');
        },
    },
    methods: {
        truncate(url) {
            const text = String(url || '');
            if (text.length <= 70) return text;
            return `${text.slice(0, 40)}...${text.slice(-20)}`;
        },
        normalizeErrors(error) {
            const responseData = error && error.response && error.response.data ? error.response.data : null;
            const laravelErrors = responseData && responseData.errors ? responseData.errors : null;

            if (laravelErrors && typeof laravelErrors === 'object') {
                const mapped = {};
                Object.keys(laravelErrors).forEach((key) => {
                    const messages = laravelErrors[key];
                    mapped[key] = Array.isArray(messages) ? messages[0] : String(messages);
                });
                return mapped;
            }

            return {};
        },
        validateFile(file) {
            if (!file) return 'File tidak ditemukan.';
            const allowed = ['image/jpeg', 'image/png', 'application/pdf'];
            if (!allowed.includes(file.type)) return 'Format file harus gambar (jpg/png) atau pdf.';
            if (file.size > 1024 * 1024) return 'Ukuran file maksimal 1MB.';
            return '';
        },
        showAddModal() {
            this.message = '';
            this.detailErrors = {};
            this.editMode = false;
            this.form = { id: null, label: 'score', name: 'Tes Potensi PAPs UGM', desc: '', file_url: '' };
            this.modalOpen = true;
        },
        showEditModal(detail) {
            this.message = '';
            this.detailErrors = {};
            this.editMode = true;
            this.form = {
                id: detail.id,
                label: detail.label || 'score',
                name: detail.name || '',
                desc: detail.desc || '',
                file_url: detail.file_url || '',
            };
            this.modalOpen = true;
        },
        closeModal() {
            this.modalOpen = false;
        },
        uploadFile(event) {
            const input = event && event.target ? event.target : null;
            const file = input && input.files && input.files.length ? input.files[0] : null;
            this.message = '';
            this.detailErrors = {};

            const validationMessage = this.validateFile(file);
            if (validationMessage) {
                this.message = validationMessage;
                if (input) input.value = '';
                return;
            }

            this.uploading = true;
            uploadFirebaseFile({ file, prefix: 'Registration/Score' })
                .then((url) => {
                    if (url) {
                        this.form.file_url = url;
                    } else {
                        this.message = 'Upload failed';
                    }
                })
                .catch(() => {
                    this.message = 'Upload failed';
                })
                .finally(() => {
                    this.uploading = false;
                });
        },
        submit() {
            this.saving = true;
            this.message = '';
            this.detailErrors = {};

            const payload = {
                label: this.form.label,
                name: this.form.name,
                desc: this.form.desc,
                file_url: this.form.file_url,
            };

            const request = this.editMode
                ? Repository.patch(`/api/registration-details/${this.form.id}`, payload)
                : Repository.post('/api/registration-details', payload);

            return request
                .then(() => {
                    this.closeModal();
                    this.$emit('refresh');
                })
                .catch((error) => {
                    this.detailErrors = this.normalizeErrors(error);
                })
                .finally(() => {
                    this.saving = false;
                });
        },
        removeDetail(id) {
            if (!confirm('Hapus data?')) return;
            return Repository.delete(`/api/registration-details/${id}`)
                .then(() => {
                    this.$emit('refresh');
                })
                .catch(() => {
                    // ignore
                });
        },
    },
};
</script>

