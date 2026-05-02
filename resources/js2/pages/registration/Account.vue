<template>
    <div class="rounded-2xl border border-border bg-panel p-6">
        <div class="text-lg font-semibold">Profile</div>
        <p class="mt-1 text-sm text-muted">Lengkapi data profile anda.</p>

        <div v-if="message" class="mt-4 rounded-xl border border-border bg-surface px-4 py-3 text-sm">
            {{ message }}
        </div>

        <form class="mt-6 grid gap-4 sm:grid-cols-2" @submit.prevent="save">
            <label class="grid gap-2 text-sm sm:col-span-2">
                <span class="text-muted">Nama</span>
                <input class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                    :class="fieldErrors.name ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                    v-model.trim="form.name" />
                <span v-if="fieldErrors.name" class="text-xs text-red-600">{{ fieldErrors.name }}</span>
            </label>
            <label class="grid gap-2 text-sm">
                <span class="text-muted">Email</span>
                <input type="email" class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                    :class="fieldErrors.email ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                    v-model.trim="form.email" />
                <span v-if="fieldErrors.email" class="text-xs text-red-600">{{ fieldErrors.email }}</span>
            </label>
            <label class="grid gap-2 text-sm">
                <span class="text-muted">No HP</span>
                <input class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                    :class="fieldErrors.phone ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                    v-model.trim="form.phone" />
                <span v-if="fieldErrors.phone" class="text-xs text-red-600">{{ fieldErrors.phone }}</span>
            </label>

            <div class="grid gap-2 text-sm sm:col-span-2">
                <span class="text-muted">Foto</span>
                <input type="file" accept="image/jpg,image/jpeg,image/png"
                    class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm"
                    :disabled="uploading || !canEdit" @change="uploadPhoto" ref="imageInput" />
                <div class="text-xs text-muted">Bentuk file: jpg, jpeg, png. Ukuran maksimal: 1MB.</div>
                <div class="mt-2 flex items-center gap-4">
                    <div class="flex h-32 w-32 items-center justify-center overflow-hidden rounded-xl border border-border bg-surface">
                        <img v-if="form.image_uri" :src="form.image_uri" class="h-full w-full object-contain" />
                        <div v-else class="text-xs text-muted">No photo</div>
                    </div>
                    <div class="text-xs text-muted" v-if="uploading">Uploading...</div>
                </div>
                <span v-if="fieldErrors.image_uri" class="text-xs text-red-600">{{ fieldErrors.image_uri }}</span>
            </div>

            <div class="sm:col-span-2 flex justify-end" v-if="canEdit">
                <button class="rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-white disabled:opacity-60"
                    type="submit" :disabled="loading || uploading">
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
    name: 'RegistrationAccount',
    props: { registration: { type: Object, default: null } },
    data() {
        return {
            form: { name: '', email: '', phone: '', image_uri: '' },
            loading: false,
            message: '',
            uploading: false,
            fieldErrors: {},
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
                    name: value.name || '',
                    email: value.email || '',
                    phone: value.phone || '',
                    image_uri: value.image_uri || '',
                };
            },
        },
    },
    methods: {
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
        validatePhoto(file) {
            if (!file) {
                return 'File tidak ditemukan.';
            }

            const allowed = ['image/jpeg', 'image/png'];
            if (!allowed.includes(file.type)) {
                return 'Format file harus jpg/jpeg/png.';
            }

            const maxBytes = 1024 * 1024;
            if (file.size > maxBytes) {
                return 'Ukuran file maksimal 1MB.';
            }

            return '';
        },
        uploadPhoto(event) {
            const input = event && event.target ? event.target : null;
            const file = input && input.files && input.files.length ? input.files[0] : null;

            this.message = '';
            this.fieldErrors = {};

            const validationMessage = this.validatePhoto(file);
            if (validationMessage) {
                this.message = validationMessage;
                if (input) {
                    input.value = '';
                }
                return;
            }

            this.uploading = true;
            uploadFirebaseFile({ file, prefix: 'Registration/Account' })
                .then((url) => {
                    if (url) {
                        this.form.image_uri = url;
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
            return Repository.patch('/api/registration/profile', this.form)
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
