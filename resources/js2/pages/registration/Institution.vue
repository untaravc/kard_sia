<template>
    <div class="grid gap-4">
        <div class="rounded-2xl border border-border bg-panel p-6">
            <div class="text-lg font-semibold">Asal Institusi</div>
            <p class="mt-1 text-sm text-muted">Diisi bagi yang sedang bekerja.</p>

            <div v-if="message" class="mt-4 rounded-xl border border-border bg-surface px-4 py-3 text-sm">
                {{ message }}
            </div>

            <form class="mt-6 grid gap-4 sm:grid-cols-2" @submit.prevent="save">
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Nomor NIP / NRP Institusi Asal</span>
                    <input class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                        :class="fieldErrors.institution_user_id ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                        v-model.trim="form.institution_user_id" :disabled="!canEdit" />
                    <span v-if="fieldErrors.institution_user_id" class="text-xs text-red-600">{{ fieldErrors.institution_user_id }}</span>
                </label>

                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Nama Institusi Asal</span>
                    <input class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                        :class="fieldErrors.institution_name ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                        v-model.trim="form.institution_name" :disabled="!canEdit" />
                    <span v-if="fieldErrors.institution_name" class="text-xs text-red-600">{{ fieldErrors.institution_name }}</span>
                </label>

                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Alamat Institusi</span>
                    <textarea rows="3" class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                        :class="fieldErrors.institution_address ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                        v-model.trim="form.institution_address" :disabled="!canEdit"></textarea>
                    <span v-if="fieldErrors.institution_address" class="text-xs text-red-600">{{ fieldErrors.institution_address }}</span>
                </label>

                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Kota Institusi</span>
                    <input class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                        :class="fieldErrors.institution_city ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                        v-model.trim="form.institution_city" :disabled="!canEdit" />
                    <span v-if="fieldErrors.institution_city" class="text-xs text-red-600">{{ fieldErrors.institution_city }}</span>
                </label>

                <div class="sm:col-span-2 mt-2 border-t border-border pt-4">
                    <div class="text-base font-semibold">Izin Pendidikan</div>
                    <p class="mt-1 text-sm text-muted">Izin pendidikan diisi khusus Aparatur Sipil Negara (ASN).</p>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <label class="grid gap-2 text-sm">
                            <span class="text-muted">Izin Pendidikan</span>
                            <input class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                                :class="fieldErrors.education_permit ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                                v-model.trim="form.education_permit" :disabled="!canEdit" />
                            <span v-if="fieldErrors.education_permit" class="text-xs text-red-600">{{ fieldErrors.education_permit }}</span>
                        </label>

                        <label class="grid gap-2 text-sm">
                            <span class="text-muted">Penandatangan Izin Pendidikan</span>
                            <input class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                                :class="fieldErrors.education_permit_signer ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                                v-model.trim="form.education_permit_signer" :disabled="!canEdit" />
                            <span v-if="fieldErrors.education_permit_signer" class="text-xs text-red-600">{{ fieldErrors.education_permit_signer }}</span>
                        </label>

                        <div class="grid gap-2 text-sm sm:col-span-2">
                            <span class="text-muted">Dokumen Pendukung (image/pdf, max 1MB)</span>
                            <input type="file" accept="image/*,.pdf"
                                class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm"
                                :disabled="!canEdit || uploading" @change="uploadPermit" />
                            <div v-if="uploading" class="text-xs text-muted">Uploading...</div>
                            <span v-if="fieldErrors.education_permit_url" class="text-xs text-red-600">{{ fieldErrors.education_permit_url }}</span>
                            <div v-if="form.education_permit_url" class="text-xs">
                                <a class="text-primary underline" :href="form.education_permit_url" target="_blank" rel="noreferrer">
                                    {{ truncate(form.education_permit_url) }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sm:col-span-2 flex justify-end" v-if="canEdit">
                    <button class="rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-white disabled:opacity-60"
                        type="submit" :disabled="loading || uploading">
                        {{ loading ? 'Please wait...' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-border bg-panel p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <div class="font-semibold">Unduh contoh dokumen surat keterangan dan izin</div>
                    <div class="text-sm text-muted"><i>khusus Aparatur Sipil Negara (ASN)</i></div>
                </div>
                <a :href="referenceDocumentUrl" target="_blank" rel="noreferrer"
                    class="inline-flex items-center gap-2 rounded-xl border border-border bg-surface px-4 py-2 text-sm hover:bg-white">
                    <span class="text-primary">Open</span>
                </a>
            </div>
        </div>
    </div>
</template>

<script>
import Repository from '../../repository';
import { uploadFirebaseFile } from '../../upload';

export default {
    name: 'RegistrationInstitution',
    props: { registration: { type: Object, default: null } },
    data() {
        return {
            form: {
                institution_user_id: '',
                institution_name: '',
                institution_address: '',
                institution_city: '',
                education_permit: '',
                education_permit_signer: '',
                education_permit_url: '',
            },
            loading: false,
            uploading: false,
            message: '',
            fieldErrors: {},
            referenceDocumentUrl:
                'https://firebasestorage.googleapis.com/v0/b/unt-dev.firebasestorage.app/o/KardiologiFkkmk%2FAssets%2Ftemplate_surat_keterangan_kerja_dan_izin.docx?alt=media&token=469ad9ff-0f0c-48a2-9564-fba2df2c5b48',
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
                    institution_user_id: value.institution_user_id || '',
                    institution_name: value.institution_name || '',
                    institution_address: value.institution_address || '',
                    institution_city: value.institution_city || '',
                    education_permit: value.education_permit || '',
                    education_permit_signer: value.education_permit_signer || '',
                    education_permit_url: value.education_permit_url || '',
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
            if (!file) return 'File tidak ditemukan.';
            const allowed = ['image/jpeg', 'image/png', 'application/pdf'];
            if (!allowed.includes(file.type)) return 'Format file harus gambar (jpg/png) atau pdf.';
            if (file.size > 1024 * 1024) return 'Ukuran file maksimal 1MB.';
            return '';
        },
        uploadPermit(event) {
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

            this.uploading = true;
            uploadFirebaseFile({ file, prefix: 'Registration/Permit' })
                .then((url) => {
                    if (url) {
                        this.form.education_permit_url = url;
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
        save() {
            this.loading = true;
            this.message = '';
            this.fieldErrors = {};

            return Repository.patch('/api/registration/institution', this.form)
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

