<template>
    <div class="rounded-2xl border border-border bg-panel p-6">
        <div class="text-lg font-semibold">Riwayat Pendidikan</div>
        <p class="mt-1 text-sm text-muted">Lengkapi riwayat pendidikan anda.</p>

        <div v-if="message" class="mt-4 rounded-xl border border-border bg-surface px-4 py-3 text-sm">
            {{ message }}
        </div>

        <form class="mt-6 grid gap-4 sm:grid-cols-2" @submit.prevent="save">
            <label class="grid gap-2 text-sm sm:col-span-2">
                <span class="text-muted">Universitas Asal</span>
                <input class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                    :class="fieldErrors.origin_university ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                    v-model.trim="form.origin_university" :disabled="!canEdit" />
                <span v-if="fieldErrors.origin_university" class="text-xs text-red-600">{{ fieldErrors.origin_university }}</span>
            </label>

            <label class="grid gap-2 text-sm">
                <span class="text-muted">Akreditasi Universitas Asal Saat Lulus</span>
                <select class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                    :class="fieldErrors.origin_university_accreditation ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                    v-model="form.origin_university_accreditation" :disabled="!canEdit">
                    <option value="unggul">Unggul</option>
                    <option value="baik-sekali">Baik Sekali</option>
                    <option value="baik">Baik</option>
                    <option value="tidak-terakreditasi">Tidak Terakreditasi</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                </select>
                <span v-if="fieldErrors.origin_university_accreditation" class="text-xs text-red-600">{{ fieldErrors.origin_university_accreditation }}</span>
            </label>

            <label class="grid gap-2 text-sm">
                <span class="text-muted">Alamat Universitas Asal</span>
                <input class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                    :class="fieldErrors.origin_university_address ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                    v-model.trim="form.origin_university_address" :disabled="!canEdit" />
                <span v-if="fieldErrors.origin_university_address" class="text-xs text-red-600">{{ fieldErrors.origin_university_address }}</span>
            </label>

            <label class="grid gap-2 text-sm">
                <span class="text-muted">Index Prestasi S1</span>
                <input type="number" max="4" step="0.01"
                    class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                    :class="fieldErrors.ip_s1 ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                    v-model="form.ip_s1" :disabled="!canEdit" />
                <span v-if="fieldErrors.ip_s1" class="text-xs text-red-600">{{ fieldErrors.ip_s1 }}</span>
            </label>

            <label class="grid gap-2 text-sm">
                <span class="text-muted">Index Prestasi Profesi</span>
                <input type="number" max="4" step="0.01"
                    class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                    :class="fieldErrors.ip_profession ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                    v-model="form.ip_profession" :disabled="!canEdit" />
                <span v-if="fieldErrors.ip_profession" class="text-xs text-red-600">{{ fieldErrors.ip_profession }}</span>
            </label>

            <label class="grid gap-2 text-sm">
                <span class="text-muted">Index Prestasi Komulatif</span>
                <input type="number" max="4" step="0.01"
                    class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                    :class="fieldErrors.ip_commulative ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                    v-model="form.ip_commulative" :disabled="!canEdit" />
                <span v-if="fieldErrors.ip_commulative" class="text-xs text-red-600">{{ fieldErrors.ip_commulative }}</span>
            </label>

            <label class="grid gap-2 text-sm">
                <span class="text-muted">Tahun Masuk S1</span>
                <input type="number" class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                    :class="fieldErrors.s1_init_year ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                    v-model="form.s1_init_year" :disabled="!canEdit" />
                <span v-if="fieldErrors.s1_init_year" class="text-xs text-red-600">{{ fieldErrors.s1_init_year }}</span>
            </label>

            <label class="grid gap-2 text-sm">
                <span class="text-muted">Tahun Lulus S1</span>
                <input type="number" class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                    :class="fieldErrors.s1_finish_year ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                    v-model="form.s1_finish_year" :disabled="!canEdit" />
                <span v-if="fieldErrors.s1_finish_year" class="text-xs text-red-600">{{ fieldErrors.s1_finish_year }}</span>
            </label>

            <label class="grid gap-2 text-sm">
                <span class="text-muted">Tahun Masuk Profesi</span>
                <input type="number" class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                    :class="fieldErrors.profession_init_year ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                    v-model="form.profession_init_year" :disabled="!canEdit" />
                <span v-if="fieldErrors.profession_init_year" class="text-xs text-red-600">{{ fieldErrors.profession_init_year }}</span>
            </label>

            <label class="grid gap-2 text-sm">
                <span class="text-muted">Tahun Lulus Profesi</span>
                <input type="number" class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                    :class="fieldErrors.profession_finish_year ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                    v-model="form.profession_finish_year" :disabled="!canEdit" />
                <span v-if="fieldErrors.profession_finish_year" class="text-xs text-red-600">{{ fieldErrors.profession_finish_year }}</span>
            </label>

            <div class="sm:col-span-2 mt-2 border-t border-border pt-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <div class="text-sm font-semibold">STR</div>
                        <div class="mt-2 grid gap-2">
                            <label class="grid gap-2 text-sm">
                                <span class="text-muted">Berlaku Sampai</span>
                                <input type="date"
                                    class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                                    :class="fieldErrors.str_end_date ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                                    v-model="form.str_end_date" :disabled="!canEdit || form.str_forever" />
                                <span v-if="fieldErrors.str_end_date" class="text-xs text-red-600">{{ fieldErrors.str_end_date }}</span>
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-muted">
                                <input type="checkbox" v-model="form.str_forever" :disabled="!canEdit" @change="onToggleStrForever" />
                                STR berlaku seumur hidup
                            </label>
                            <div class="text-xs text-muted">Upload dokumen pendukung (image/pdf, max 1MB)</div>
                            <input type="file" accept="image/*,.pdf"
                                class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm"
                                :disabled="!canEdit || strUploading" @change="uploadStr" />
                            <div v-if="strUploading" class="text-xs text-muted">Uploading...</div>
                            <span v-if="fieldErrors.str_url" class="text-xs text-red-600">{{ fieldErrors.str_url }}</span>
                            <div v-if="form.str_url" class="text-xs">
                                <a class="text-primary underline" :href="form.str_url" target="_blank" rel="noreferrer">{{ truncate(form.str_url) }}</a>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="text-sm font-semibold">ACLS</div>
                        <div class="mt-2 grid gap-2">
                            <label class="grid gap-2 text-sm">
                                <span class="text-muted">Berlaku Sampai</span>
                                <input type="date"
                                    class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                                    :class="fieldErrors.acls_end_date ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                                    v-model="form.acls_end_date" :disabled="!canEdit" />
                                <span v-if="fieldErrors.acls_end_date" class="text-xs text-red-600">{{ fieldErrors.acls_end_date }}</span>
                            </label>
                            <div class="text-xs text-muted">Upload dokumen pendukung (image/pdf, max 1MB)</div>
                            <input type="file" accept="image/*,.pdf"
                                class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm"
                                :disabled="!canEdit || aclsUploading" @change="uploadAcls" />
                            <div v-if="aclsUploading" class="text-xs text-muted">Uploading...</div>
                            <span v-if="fieldErrors.acls_url" class="text-xs text-red-600">{{ fieldErrors.acls_url }}</span>
                            <div v-if="form.acls_url" class="text-xs">
                                <a class="text-primary underline" :href="form.acls_url" target="_blank" rel="noreferrer">{{ truncate(form.acls_url) }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sm:col-span-2 flex justify-end" v-if="canEdit">
                <button class="rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-white disabled:opacity-60"
                    type="submit" :disabled="loading || strUploading || aclsUploading">
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
    name: 'RegistrationEducationBg',
    props: { registration: { type: Object, default: null } },
    data() {
        return {
            form: {
                origin_university: '',
                origin_university_accreditation: '',
                origin_university_address: '',
                ip_s1: '',
                ip_profession: '',
                ip_commulative: '',
                s1_init_year: '',
                s1_finish_year: '',
                profession_init_year: '',
                profession_finish_year: '',
                str_end_date: '',
                str_url: '',
                str_forever: false,
                acls_end_date: '',
                acls_url: '',
            },
            loading: false,
            message: '',
            fieldErrors: {},
            strUploading: false,
            aclsUploading: false,
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
                const strForever = value.str_end_date === '2100-01-01';
                this.form = {
                    origin_university: value.origin_university || '',
                    origin_university_accreditation: value.origin_university_accreditation || '',
                    origin_university_address: value.origin_university_address || '',
                    ip_s1: value.ip_s1 || '',
                    ip_profession: value.ip_profession || '',
                    ip_commulative: value.ip_commulative || '',
                    s1_init_year: value.s1_init_year || '',
                    s1_finish_year: value.s1_finish_year || '',
                    profession_init_year: value.profession_init_year || '',
                    profession_finish_year: value.profession_finish_year || '',
                    str_end_date: strForever ? '' : (value.str_end_date || ''),
                    str_url: value.str_url || '',
                    str_forever: Boolean(strForever),
                    acls_end_date: value.acls_end_date || '',
                    acls_url: value.acls_url || '',
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
        onToggleStrForever() {
            if (this.form.str_forever) {
                this.form.str_end_date = '';
            }
        },
        uploadStr(event) {
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

            this.strUploading = true;
            uploadFirebaseFile({ file, prefix: 'Registration/Str' })
                .then((url) => {
                    if (url) {
                        this.form.str_url = url;
                    } else {
                        this.message = 'Upload failed';
                    }
                })
                .catch(() => {
                    this.message = 'Upload failed';
                })
                .finally(() => {
                    this.strUploading = false;
                });
        },
        uploadAcls(event) {
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

            this.aclsUploading = true;
            uploadFirebaseFile({ file, prefix: 'Registration/Acls' })
                .then((url) => {
                    if (url) {
                        this.form.acls_url = url;
                    } else {
                        this.message = 'Upload failed';
                    }
                })
                .catch(() => {
                    this.message = 'Upload failed';
                })
                .finally(() => {
                    this.aclsUploading = false;
                });
        },
        save() {
            this.loading = true;
            this.message = '';
            this.fieldErrors = {};

            const payload = { ...this.form };
            if (payload.str_forever) {
                payload.str_end_date = '2100-01-01';
            }
            delete payload.str_forever;

            return Repository.patch('/api/registration/education-bg', payload)
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

