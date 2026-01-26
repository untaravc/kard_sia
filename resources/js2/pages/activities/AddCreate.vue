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
                <router-link
                    class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                    to="/cblu/activities"
                >
                    Back
                </router-link>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="button"
                    :disabled="submitting"
                    @click="submitForm"
                >
                    {{ submitting ? 'Saving...' : 'Save Activity' }}
                </button>
            </div>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-6">
            <div class="grid gap-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Nama</span>
                        <input
                            v-model.trim="form.name"
                            type="text"
                            placeholder="ex: Seminar Hasil"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Pembicara</span>
                        <input
                            v-model.trim="form.speaker"
                            type="text"
                            placeholder="dr. Don Joe"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Judul</span>
                        <textarea
                            v-model.trim="form.title"
                            placeholder="ex: Perbedaan Derajat Aliran Koroner TIMI 3 Antara..."
                            class="min-h-[90px] w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        ></textarea>
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Stase</span>
                        <select
                            v-model="form.stase_id"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        >
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
                        <input
                            v-model="form.start_date"
                            type="datetime-local"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Selesai</span>
                        <input
                            v-model="form.end_date"
                            type="datetime-local"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
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
                            <input
                                v-model.trim="form.place"
                                type="text"
                                placeholder="ex: Zoom id 123123, Ruang Konferensi..."
                                class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                            />
                        </label>
                        <label class="grid gap-2 text-sm">
                            <span class="text-muted">Kategori</span>
                            <select
                                v-model="form.category"
                                class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                            >
                                <option :value="null">Pilih Kategori</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.name }}
                                </option>
                            </select>
                        </label>
                        <label class="grid gap-2 text-sm">
                            <span class="text-muted">Status</span>
                            <select
                                v-model="form.status"
                                class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                            >
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
                        <input
                            v-model.trim="form.link"
                            type="text"
                            placeholder="https://meet.google.com/..."
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Passcode</span>
                        <input
                            v-model.trim="form.passcode"
                            type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                    </label>
                </div>

                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Catatan</span>
                    <textarea
                        v-model.trim="form.note"
                        class="min-h-[90px] w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    ></textarea>
                </label>
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Deskripsi</span>
                    <textarea
                        v-model.trim="form.desc"
                        class="min-h-[120px] w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    ></textarea>
                </label>

                <div v-if="errorMessage" class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ errorMessage }}
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import Repository from '../../repository';
import Swal from 'sweetalert2';

export default {
    data() {
        return {
            baseUrl: '/api/activities',
            submitting: false,
            loading: false,
            errorMessage: '',
            toast: null,
            stases: [],
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
            },
        };
    },
    computed: {
        isEdit() {
            return this.$route.params && this.$route.params.id && this.$route.params.id !== 'create';
        },
    },
    created() {
        this.initToast();
        this.fetchStases();
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
        fetchActivity() {
            this.loading = true;
            return Repository.get(`${this.baseUrl}/${this.$route.params.id}`)
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    if (!result) {
                        return;
                    }
                    this.form = {
                        ...this.form,
                        ...result,
                    };
                })
                .catch(() => {
                    this.errorMessage = 'Failed to load activity.';
                })
                .finally(() => {
                    this.loading = false;
                });
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
