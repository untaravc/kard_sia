<template>
    <div class="grid gap-4">
        <div class="rounded-2xl border border-border bg-panel p-6">
            <div class="text-lg font-semibold">Keluarga</div>
            <p class="mt-1 text-sm text-muted">Lengkapi data keluarga anda.</p>

            <div v-if="message" class="mt-4 rounded-xl border border-border bg-surface px-4 py-3 text-sm">
                {{ message }}
            </div>

            <form class="mt-6 grid gap-4 sm:grid-cols-2" @submit.prevent="save">
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Nama Ayah</span>
                    <input class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                        :class="fieldErrors.father_name ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                        v-model.trim="form.father_name" :disabled="!canEdit" />
                    <span v-if="fieldErrors.father_name" class="text-xs text-red-600">{{ fieldErrors.father_name }}</span>
                </label>

                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Pekerjaan Ayah</span>
                    <input class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                        :class="fieldErrors.father_job ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                        v-model.trim="form.father_job" :disabled="!canEdit" />
                    <span v-if="fieldErrors.father_job" class="text-xs text-red-600">{{ fieldErrors.father_job }}</span>
                </label>

                <label class="grid gap-2 text-sm sm:col-span-2">
                    <span class="text-muted">Alamat Ayah</span>
                    <textarea rows="3" class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                        :class="fieldErrors.father_address ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                        v-model.trim="form.father_address" :disabled="!canEdit"></textarea>
                    <span v-if="fieldErrors.father_address" class="text-xs text-red-600">{{ fieldErrors.father_address }}</span>
                </label>

                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Nama Ibu</span>
                    <input class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                        :class="fieldErrors.mother_name ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                        v-model.trim="form.mother_name" :disabled="!canEdit" />
                    <span v-if="fieldErrors.mother_name" class="text-xs text-red-600">{{ fieldErrors.mother_name }}</span>
                </label>

                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Pekerjaan Ibu</span>
                    <input class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                        :class="fieldErrors.mother_job ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                        v-model.trim="form.mother_job" :disabled="!canEdit" />
                    <span v-if="fieldErrors.mother_job" class="text-xs text-red-600">{{ fieldErrors.mother_job }}</span>
                </label>

                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Nama Pasangan</span>
                    <input class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                        :class="fieldErrors.spouse_name ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                        v-model.trim="form.spouse_name" :disabled="!canEdit" />
                    <span v-if="fieldErrors.spouse_name" class="text-xs text-red-600">{{ fieldErrors.spouse_name }}</span>
                </label>

                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Pekerjaan Pasangan</span>
                    <input class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                        :class="fieldErrors.spouse_job ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                        v-model.trim="form.spouse_job" :disabled="!canEdit" />
                    <span v-if="fieldErrors.spouse_job" class="text-xs text-red-600">{{ fieldErrors.spouse_job }}</span>
                </label>

                <div class="sm:col-span-2 flex justify-end" v-if="canEdit">
                    <button class="rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-white disabled:opacity-60"
                        type="submit" :disabled="loading">
                        {{ loading ? 'Please wait...' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-border bg-panel p-6">
            <div class="flex items-center justify-between gap-4">
                <div class="text-base font-semibold">Data Anak</div>
                <button v-if="canEdit" type="button" class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    @click="showAddModal">
                    Tambah
                </button>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-xs uppercase text-muted">
                        <tr class="border-b border-border">
                            <th class="px-2 py-2 w-12">No</th>
                            <th class="px-2 py-2">Nama</th>
                            <th class="px-2 py-2">Tahun Lahir</th>
                            <th class="px-2 py-2 w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(detail, index) in children" :key="detail.id" class="border-b border-border">
                            <td class="px-2 py-2">{{ index + 1 }}</td>
                            <td class="px-2 py-2">{{ detail.name }}</td>
                            <td class="px-2 py-2">{{ detail.year }}</td>
                            <td class="px-2 py-2">
                                <div class="flex gap-2">
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
                        <tr v-if="children.length === 0">
                            <td colspan="4" class="px-2 py-4 text-center text-sm text-muted">Belum ada data.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Modal :open="modalOpen" title="Data Anak" :closeOnBackdrop="false" @close="closeModal">
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Nama</span>
                    <input class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                        :class="detailErrors.name ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                        v-model.trim="detailForm.name" />
                    <span v-if="detailErrors.name" class="text-xs text-red-600">{{ detailErrors.name }}</span>
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Tahun Lahir</span>
                    <input class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                        :class="detailErrors.year ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                        v-model.trim="detailForm.year" />
                    <span v-if="detailErrors.year" class="text-xs text-red-600">{{ detailErrors.year }}</span>
                </label>
            </div>
            <template #footer>
                <button type="button"
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white disabled:opacity-60"
                    :disabled="detailLoading" @click="submitDetail">
                    {{ detailLoading ? 'Please wait...' : (detailEditMode ? 'Simpan' : 'Tambah') }}
                </button>
            </template>
        </Modal>
    </div>
</template>

<script>
import Repository from '../../repository';
import Modal from '../../components/Modal.vue';

export default {
    name: 'RegistrationFamily',
    components: { Modal },
    props: { registration: { type: Object, default: null } },
    data() {
        return {
            form: {
                father_name: '',
                father_job: '',
                father_address: '',
                mother_name: '',
                mother_job: '',
                spouse_name: '',
                spouse_job: '',
            },
            loading: false,
            message: '',
            fieldErrors: {},
            modalOpen: false,
            detailEditMode: false,
            detailLoading: false,
            detailErrors: {},
            detailForm: {
                id: null,
                label: 'child',
                name: '',
                year: '',
            },
        };
    },
    computed: {
        canEdit() {
            return this.registration && this.registration.status === 100;
        },
        children() {
            const details = this.registration && Array.isArray(this.registration.details) ? this.registration.details : [];
            return details.filter((item) => item && item.label === 'child');
        },
    },
    watch: {
        registration: {
            immediate: true,
            handler(value) {
                if (!value) return;
                this.form = {
                    father_name: value.father_name || '',
                    father_job: value.father_job || '',
                    father_address: value.father_address || '',
                    mother_name: value.mother_name || '',
                    mother_job: value.mother_job || '',
                    spouse_name: value.spouse_name || '',
                    spouse_job: value.spouse_job || '',
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
        save() {
            this.loading = true;
            this.message = '';
            this.fieldErrors = {};
            return Repository.patch('/api/registration/family', this.form)
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
        showAddModal() {
            this.detailEditMode = false;
            this.detailErrors = {};
            this.detailForm = { id: null, label: 'child', name: '', year: '' };
            this.modalOpen = true;
        },
        showEditModal(detail) {
            this.detailEditMode = true;
            this.detailErrors = {};
            this.detailForm = {
                id: detail.id,
                label: detail.label || 'child',
                name: detail.name || '',
                year: detail.year || '',
            };
            this.modalOpen = true;
        },
        closeModal() {
            this.modalOpen = false;
        },
        submitDetail() {
            this.detailLoading = true;
            this.detailErrors = {};

            const payload = {
                label: this.detailForm.label,
                name: this.detailForm.name,
                year: this.detailForm.year,
            };

            const request = this.detailEditMode
                ? Repository.patch(`/api/registration-details/${this.detailForm.id}`, payload)
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
                    this.detailLoading = false;
                });
        },
        removeDetail(id) {
            if (!confirm('Hapus data?')) {
                return;
            }
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

