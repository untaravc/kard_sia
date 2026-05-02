<template>
    <div class="rounded-2xl border border-border bg-panel p-6">
        <div class="text-lg font-semibold">Pendaftaran</div>
        <p class="mt-1 text-sm text-muted">Lengkapi data pendaftaran anda.</p>

        <div v-if="message" class="mt-4 rounded-xl border border-border bg-surface px-4 py-3 text-sm">
            {{ message }}
        </div>

        <form class="mt-6 grid gap-4 sm:grid-cols-2" @submit.prevent="save">
            <label class="grid gap-2 text-sm">
                <span class="text-muted">Tes Ke-</span>
                <select class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                    :class="fieldErrors.test_order ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                    v-model.number="form.test_order" :disabled="!canEdit">
                    <option :value="1">1</option>
                    <option :value="2">2</option>
                    <option :value="3">3</option>
                    <option :value="4">4</option>
                </select>
                <span v-if="fieldErrors.test_order" class="text-xs text-red-600">{{ fieldErrors.test_order }}</span>
            </label>

            <label class="grid gap-2 text-sm">
                <span class="text-muted">Jalur Seleksi</span>
                <select class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                    :class="fieldErrors.selection_path ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                    v-model="form.selection_path" :disabled="!canEdit">
                    <option value="mandiri">Mandiri</option>
                    <option value="kemitraan">Kemitraan</option>
                    <option value="pendaftar_beasiswa">Pendaftar Beasiswa</option>
                    <option value="beasiswa">Beasiswa</option>
                </select>
                <span v-if="fieldErrors.selection_path" class="text-xs text-red-600">{{ fieldErrors.selection_path }}</span>
            </label>

            <label class="grid gap-2 text-sm">
                <span class="text-muted">Status Kerja</span>
                <select class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                    :class="fieldErrors.working_status ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                    v-model="form.working_status" :disabled="!canEdit">
                    <option value="pns">PNS</option>
                    <option value="non_pns">Non PNS</option>
                </select>
                <span v-if="fieldErrors.working_status" class="text-xs text-red-600">{{ fieldErrors.working_status }}</span>
            </label>

            <div class="sm:col-span-2 mt-2 border-t border-border pt-4">
                <div class="text-sm font-semibold">Surat Pernyataan</div>
                <div class="mt-2 grid gap-3 sm:grid-cols-2">
                    <div>
                        <div class="text-xs text-muted">Download template</div>
                        <a :href="statementTemplateUrl" target="_blank" rel="noreferrer"
                            class="mt-2 inline-flex w-full items-center justify-between rounded-xl border border-border bg-surface px-4 py-3 text-sm hover:bg-white">
                            <span class="truncate">template_surat_pernyataan_tes_ppds_2025.docx</span>
                            <span class="text-primary">Download</span>
                        </a>
                    </div>
                    <div>
                        <div class="text-xs text-muted">Upload Surat Pernyataan (image/pdf, max 1MB)</div>
                        <input type="file" accept="image/*,.pdf"
                            class="mt-2 w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm"
                            :disabled="!canEdit || statementUploading" @change="uploadStatementLetter"
                            ref="statementInput" />
                        <span v-if="fieldErrors.statement_letter_url" class="mt-1 block text-xs text-red-600">{{ fieldErrors.statement_letter_url }}</span>
                        <div v-if="statementUploading" class="mt-1 text-xs text-muted">Uploading...</div>
                        <div v-if="form.statement_letter_url" class="mt-2 text-xs">
                            <a class="text-primary underline" :href="form.statement_letter_url" target="_blank" rel="noreferrer">
                                {{ truncate(form.statement_letter_url) }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sm:col-span-2 mt-2 border-t border-border pt-4">
                <div class="text-sm font-semibold">Surat Keterangan Penempatan Kerja Setelah Lulus</div>
                <div class="mt-2 grid gap-3 sm:grid-cols-2">
                    <div>
                        <div class="text-xs text-muted">Contoh dokumen</div>
                        <a :href="referenceDocumentUrl" target="_blank" rel="noreferrer"
                            class="mt-2 inline-flex w-full items-center justify-between rounded-xl border border-border bg-surface px-4 py-3 text-sm hover:bg-white">
                            <span class="truncate">{{ truncate(referenceDocumentUrl) }}</span>
                            <span class="text-primary">Open</span>
                        </a>
                    </div>
                    <div>
                        <div class="text-xs text-muted">Upload dokumen (image/pdf, max 1MB)</div>
                        <input type="file" accept="image/*,.pdf"
                            class="mt-2 w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm"
                            :disabled="!canEdit || graduateUploading" @change="uploadGraduateLetter" ref="graduateInput" />
                        <span v-if="fieldErrors.graduate_url" class="mt-1 block text-xs text-red-600">{{ fieldErrors.graduate_url }}</span>
                        <div v-if="graduateUploading" class="mt-1 text-xs text-muted">Uploading...</div>
                        <div v-if="form.graduate_url" class="mt-2 text-xs">
                            <a class="text-primary underline" :href="form.graduate_url" target="_blank" rel="noreferrer">
                                {{ truncate(form.graduate_url) }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sm:col-span-2 flex justify-end" v-if="canEdit">
                <button class="rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-white disabled:opacity-60"
                    type="submit" :disabled="loading || statementUploading || graduateUploading">
                    {{ loading ? 'Please wait...' : 'Simpan' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script>
import Repository from '../../repository';
import { uploadFirebaseFile } from '../../upload';

export default {
    name: 'RegistrationReg',
    props: { registration: { type: Object, default: null } },
    data() {
        return {
            form: {
                test_order: 1,
                selection_path: 'mandiri',
                working_status: 'non_pns',
                video_url: '',
                statement_letter_url: '',
                graduate_url: '',
            },
            loading: false,
            message: '',
            fieldErrors: {},
            statementUploading: false,
            graduateUploading: false,
            statementTemplateUrl:
                'https://firebasestorage.googleapis.com/v0/b/unt-dev.firebasestorage.app/o/KardiologiFkkmk%2FAssets%2Ftemplate_surat_pernyataan_tes_ppds_2025.docx?alt=media&token=0c7fd3f0-9694-44fd-a381-bc52c45a465d',
            referenceDocumentUrl:
                'https://firebasestorage.googleapis.com/v0/b/unt-dev.firebasestorage.app/o/KardiologiFkkmk%2FAssets%2Ftemplate_surat_keterangan_penempatan_kerja_setelah_lulus.docx?alt=media&token=bf9cb6e1-35ce-49be-a8a6-851aaa380c26',
        };
    },
    computed: {
        canEdit() {
            return this.registration && this.registration.status === 100;
        },
    },
    watch: {
        registration: {
            immediate: true,
            handler(value) {
                if (!value) return;
                this.form = {
                    test_order: value.test_order ?? 1,
                    selection_path: value.selection_path || 'mandiri',
                    working_status: value.working_status || 'non_pns',
                    video_url: value.video_url || '',
                    statement_letter_url: value.statement_letter_url || '',
                    graduate_url: value.graduate_url || '',
                };
            },
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
            if (!file) {
                return 'File tidak ditemukan.';
            }

            const allowed = ['image/jpeg', 'image/png', 'application/pdf'];
            if (!allowed.includes(file.type)) {
                return 'Format file harus gambar (jpg/png) atau pdf.';
            }

            const maxBytes = 1024 * 1024;
            if (file.size > maxBytes) {
                return 'Ukuran file maksimal 1MB.';
            }

            return '';
        },
        uploadStatementLetter(event) {
            const input = event && event.target ? event.target : null;
            const file = input && input.files && input.files.length ? input.files[0] : null;
            this.message = '';
            this.fieldErrors = {};

            const validationMessage = this.validateFile(file);
            if (validationMessage) {
                this.message = validationMessage;
                if (input) input.value = '';
                return;
            }

            this.statementUploading = true;
            uploadFirebaseFile({ file, prefix: 'Registration/StatementLetter' })
                .then((url) => {
                    if (url) {
                        this.form.statement_letter_url = url;
                    } else {
                        this.message = 'Upload failed';
                    }
                })
                .catch(() => {
                    this.message = 'Upload failed';
                })
                .finally(() => {
                    this.statementUploading = false;
                });
        },
        uploadGraduateLetter(event) {
            const input = event && event.target ? event.target : null;
            const file = input && input.files && input.files.length ? input.files[0] : null;
            this.message = '';
            this.fieldErrors = {};

            const validationMessage = this.validateFile(file);
            if (validationMessage) {
                this.message = validationMessage;
                if (input) input.value = '';
                return;
            }

            this.graduateUploading = true;
            uploadFirebaseFile({ file, prefix: 'Registration/Graduation' })
                .then((url) => {
                    if (url) {
                        this.form.graduate_url = url;
                    } else {
                        this.message = 'Upload failed';
                    }
                })
                .catch(() => {
                    this.message = 'Upload failed';
                })
                .finally(() => {
                    this.graduateUploading = false;
                });
        },
        save() {
            this.loading = true;
            this.message = '';
            this.fieldErrors = {};

            return Repository.patch('/api/registration/register', this.form)
                .then((response) => {
                    const data = response && response.data ? response.data : {};
                    this.message = data.text || 'Saved';
                    this.$emit('refresh');
                })
                .catch((error) => {
                    this.message = error && error.response && error.response.data ? error.response.data.text : 'Save failed';
                    this.fieldErrors = this.normalizeErrors(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
};
</script>

