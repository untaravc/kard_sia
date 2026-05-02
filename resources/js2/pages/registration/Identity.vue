<template>
    <div class="rounded-2xl border border-border bg-panel p-6">
        <div class="text-lg font-semibold">Identitas</div>
        <p class="mt-1 text-sm text-muted">Lengkapi data identitas anda.</p>

        <div v-if="message" class="mt-4 rounded-xl border border-border bg-surface px-4 py-3 text-sm">
            {{ message }}
        </div>

        <form class="mt-6 grid gap-4 sm:grid-cols-2" @submit.prevent="save">
            <label class="grid gap-2 text-sm">
                <span class="text-muted">NIK</span>
                <input class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                    :class="fieldErrors.nik ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                    v-model.trim="form.nik" />
                <span v-if="fieldErrors.nik" class="text-xs text-red-600">{{ fieldErrors.nik }}</span>
            </label>

            <label class="grid gap-2 text-sm">
                <span class="text-muted">Jenis Kelamin</span>
                <select class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                    :class="fieldErrors.gender ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                    v-model="form.gender">
                    <option value="M">Laki-laki</option>
                    <option value="F">Perempuan</option>
                </select>
                <span v-if="fieldErrors.gender" class="text-xs text-red-600">{{ fieldErrors.gender }}</span>
            </label>

            <label class="grid gap-2 text-sm">
                <span class="text-muted">Tempat Lahir</span>
                <input class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                    :class="fieldErrors.birth_place ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                    v-model.trim="form.birth_place" />
                <span v-if="fieldErrors.birth_place" class="text-xs text-red-600">{{ fieldErrors.birth_place }}</span>
            </label>

            <label class="grid gap-2 text-sm">
                <span class="text-muted">Tanggal Lahir</span>
                <input type="date" max="2005-01-01"
                    class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                    :class="fieldErrors.birth_date ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                    v-model="form.birth_date" />
                <span v-if="fieldErrors.birth_date" class="text-xs text-red-600">{{ fieldErrors.birth_date }}</span>
                <div v-if="ageMessage" class="text-xs text-muted">{{ ageMessage }}</div>
            </label>

            <label class="grid gap-2 text-sm sm:col-span-2">
                <span class="text-muted">Alamat Asal</span>
                <textarea rows="3"
                    class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                    :class="fieldErrors.origin_address ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                    v-model.trim="form.origin_address"></textarea>
                <span v-if="fieldErrors.origin_address" class="text-xs text-red-600">{{ fieldErrors.origin_address }}</span>
            </label>

            <label class="grid gap-2 text-sm">
                <span class="text-muted">Status Perkawinan</span>
                <select class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                    :class="fieldErrors.marital_status ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                    v-model="form.marital_status">
                    <option value="M">Kawin</option>
                    <option value="S">Tidak Kawin</option>
                </select>
                <span v-if="fieldErrors.marital_status" class="text-xs text-red-600">{{ fieldErrors.marital_status }}</span>
            </label>

            <label class="grid gap-2 text-sm">
                <span class="text-muted">Agama</span>
                <select class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                    :class="fieldErrors.religion ? 'border-red-300 focus:ring-red-500/20' : 'border-border focus:ring-primary/30'"
                    v-model="form.religion">
                    <option value="islam">Islam</option>
                    <option value="kristen">Kristen</option>
                    <option value="katolik">Katolik</option>
                    <option value="hindu">Hindu</option>
                    <option value="buddha">Buddha</option>
                    <option value="konghucu">Konghucu</option>
                </select>
                <span v-if="fieldErrors.religion" class="text-xs text-red-600">{{ fieldErrors.religion }}</span>
            </label>

            <div class="sm:col-span-2 flex justify-end" v-if="canEdit">
                <button class="rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-white disabled:opacity-60"
                    type="submit" :disabled="loading">
                    {{ loading ? 'Please wait...' : 'Simpan' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script>
import Repository from '../../repository';

export default {
    name: 'RegistrationIdentity',
    props: { registration: { type: Object, default: null } },
    data() {
        return {
            form: {
                nik: '',
                gender: 'M',
                birth_place: '',
                birth_date: '',
                origin_address: '',
                marital_status: 'S',
                religion: 'islam',
            },
            loading: false,
            message: '',
            fieldErrors: {},
        };
    },
    computed: {
        canEdit() {
            return this.registration && this.registration.status === 100;
        },
        ageMessage() {
            if (!this.form.birth_date) {
                return '';
            }

            const referenceDate = new Date('2026-01-01T00:00:00');
            const birthDate = new Date(`${this.form.birth_date}T00:00:00`);
            if (Number.isNaN(referenceDate.getTime()) || Number.isNaN(birthDate.getTime())) {
                return '';
            }

            const diffMs = referenceDate.getTime() - birthDate.getTime();
            if (diffMs <= 0) {
                return '';
            }

            const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
            const years = Math.floor(diffDays / 365);
            const months = Math.floor((diffDays % 365) / 30);

            let msg = 'Anda berusia ';
            msg += years > 0 ? `${years} tahun ` : '0 tahun ';
            msg += `${months} bulan `;
            msg += 'pada 1 Januari 2026.';
            return msg;
        },
    },
    watch: {
        registration: {
            immediate: true,
            handler(value) {
                if (!value) return;
                this.form = {
                    nik: value.nik || '',
                    gender: value.gender || 'M',
                    birth_place: value.birth_place || '',
                    birth_date: value.birth_date || '',
                    origin_address: value.origin_address || '',
                    marital_status: value.marital_status || 'S',
                    religion: value.religion || 'islam',
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
            return Repository.patch('/api/registration/identity', this.form)
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

