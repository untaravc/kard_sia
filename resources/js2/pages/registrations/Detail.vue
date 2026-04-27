<template>
    <div class="relative grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-3">
                    <span class="h-2 w-2 rounded-full bg-primary/70"></span>
                    <div class="text-xs uppercase tracking-[0.2em] text-muted">Registration Detail</div>
                </div>
                <h1 class="text-2xl font-semibold text-ink">
                    {{ registration.name || 'Registration' }}
                </h1>
                <div class="mt-1 text-xs text-muted" v-if="registration.email">
                    {{ registration.email }}
                </div>
            </div>
            <router-link
                class="rounded-xl border border-border bg-white/80 px-4 py-2 text-sm text-muted shadow-sm backdrop-blur"
                to="/blu/registrations"
            >
                Back
            </router-link>
        </header>

        <section class="relative rounded-2xl border border-border bg-panel p-5">
            <Loading :active="loading" :is-full-page="false" />
            <div
                v-if="errorMessage"
                class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600"
            >
                {{ errorMessage }}
            </div>

            <div v-if="!loading" class="grid gap-6">
                <div class="flex flex-wrap items-center gap-3 rounded-xl border border-border bg-white px-4 py-3">
                    <div class="text-xs uppercase tracking-[0.2em] text-muted">Status</div>
                    <div class="text-sm font-semibold text-ink">
                        {{ registration.status_label || '-' }}
                    </div>
                    <div class="ml-auto flex items-center gap-2">
                        <select
                            v-model.number="statusForm.status"
                            class="rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                            :disabled="statusSubmitting"
                            @change="updateStatus"
                        >
                            <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                        <div v-if="statusSubmitting" class="text-xs text-muted">Saving...</div>
                    </div>
                </div>

                <section class="grid gap-6 lg:grid-cols-12">
                    <div class="lg:col-span-9">
                        <div class="rounded-2xl border border-border bg-white p-5">
                            <div class="text-xs uppercase tracking-[0.2em] text-muted">I. Identitas</div>
                            <div class="mt-4 grid gap-5 md:grid-cols-2">
                                <div class="grid gap-2 text-sm">
                                    <div class="grid grid-cols-[160px_1fr] gap-3">
                                        <div class="text-muted">Nama</div>
                                        <div class="font-medium text-ink">{{ registration.name || '-' }}</div>
                                    </div>
                                    <div class="grid grid-cols-[160px_1fr] gap-3">
                                        <div class="text-muted">Email</div>
                                        <div class="font-medium text-ink">{{ registration.email || '-' }}</div>
                                    </div>
                                    <div class="grid grid-cols-[160px_1fr] gap-3">
                                        <div class="text-muted">No HP</div>
                                        <div class="font-medium text-ink">{{ registration.phone || '-' }}</div>
                                    </div>
                                    <div class="grid grid-cols-[160px_1fr] gap-3">
                                        <div class="text-muted">Jenis Kelamin</div>
                                        <div class="font-medium text-ink">{{ genderLabel }}</div>
                                    </div>
                                    <div class="grid grid-cols-[160px_1fr] gap-3">
                                        <div class="text-muted">Tempat / Tgl Lahir</div>
                                        <div class="font-medium text-ink">
                                            {{ registration.birth_place || '-' }}
                                            <span v-if="registration.birth_date"> • {{ registration.birth_date }}</span>
                                            <span v-if="registration.age !== null && registration.age !== undefined">
                                                • {{ registration.age }} tahun
                                            </span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-[160px_1fr] gap-3">
                                        <div class="text-muted">Agama</div>
                                        <div class="font-medium text-ink">{{ registration.religion || '-' }}</div>
                                    </div>
                                    <div class="grid grid-cols-[160px_1fr] gap-3">
                                        <div class="text-muted">Alamat</div>
                                        <div class="font-medium text-ink">{{ registration.origin_address || '-' }}</div>
                                    </div>
                                    <div class="grid grid-cols-[160px_1fr] gap-3">
                                        <div class="text-muted">NIK</div>
                                        <div class="font-medium text-ink">{{ registration.nik || '-' }}</div>
                                    </div>
                                    <div class="grid grid-cols-[160px_1fr] gap-3">
                                        <div class="text-muted">Status Perkawinan</div>
                                        <div class="font-medium text-ink">{{ maritalStatusLabel }}</div>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-muted text-sm">Foto</div>
                                    <div class="mt-2 overflow-hidden rounded-2xl border border-border bg-slate-50">
                                        <img
                                            v-if="registration.image_uri"
                                            :src="registration.image_uri"
                                            alt="Registration"
                                            class="h-72 w-full object-contain"
                                        />
                                        <div v-else class="grid h-72 place-items-center text-sm text-muted">
                                            No image
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 rounded-2xl border border-border bg-white p-5">
                            <div class="text-xs uppercase tracking-[0.2em] text-muted">Keluarga</div>
                            <div class="mt-4 grid gap-4 text-sm">
                                <div>
                                    <div class="font-semibold text-ink">Ayah</div>
                                    <div class="mt-2 grid gap-1">
                                        <div class="flex justify-between gap-4">
                                            <span class="text-muted">Nama</span>
                                            <span class="text-ink">{{ registration.father_name || '-' }}</span>
                                        </div>
                                        <div class="flex justify-between gap-4">
                                            <span class="text-muted">Pekerjaan</span>
                                            <span class="text-ink">{{ registration.father_job || '-' }}</span>
                                        </div>
                                        <div class="flex justify-between gap-4">
                                            <span class="text-muted">Alamat</span>
                                            <span class="text-ink text-right">{{ registration.father_address || '-' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="font-semibold text-ink">Ibu</div>
                                    <div class="mt-2 grid gap-1">
                                        <div class="flex justify-between gap-4">
                                            <span class="text-muted">Nama</span>
                                            <span class="text-ink">{{ registration.mother_name || '-' }}</span>
                                        </div>
                                        <div class="flex justify-between gap-4">
                                            <span class="text-muted">Pekerjaan</span>
                                            <span class="text-ink">{{ registration.mother_job || '-' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="font-semibold text-ink">Pasangan</div>
                                    <div class="mt-2 grid gap-1">
                                        <div class="flex justify-between gap-4">
                                            <span class="text-muted">Nama</span>
                                            <span class="text-ink">{{ registration.spouse_name || '-' }}</span>
                                        </div>
                                        <div class="flex justify-between gap-4">
                                            <span class="text-muted">Pekerjaan</span>
                                            <span class="text-ink">{{ registration.spouse_job || '-' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="font-semibold text-ink">Anak</div>
                                    <div v-if="children.length === 0" class="mt-2 text-sm text-muted">No children.</div>
                                    <div v-else class="mt-3 grid gap-2">
                                        <div
                                            v-for="(child, i) in children"
                                            :key="`child-${i}`"
                                            class="rounded-xl border border-border bg-panel px-3 py-2"
                                        >
                                            <div class="text-sm font-medium text-ink">{{ child.name || '-' }}</div>
                                            <div class="text-xs text-muted">
                                                <span v-if="child.year">Tahun lahir: {{ child.year }}</span>
                                                <span v-else-if="child.desc">Tahun lahir: {{ child.desc }}</span>
                                                <span v-else>-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 rounded-2xl border border-border bg-white p-5">
                            <div class="text-xs uppercase tracking-[0.2em] text-muted">II. Pendaftaran</div>
                            <div class="mt-4 grid gap-4 text-sm">
                                <div class="grid grid-cols-[240px_1fr] gap-3">
                                    <div class="text-muted">Tes ke-</div>
                                    <div class="font-medium text-ink">{{ registration.test_order || '-' }}</div>
                                </div>
                                <div class="grid grid-cols-[240px_1fr] gap-3">
                                    <div class="text-muted">Jalur Seleksi</div>
                                    <div class="font-medium text-ink">{{ registration.selection_path || '-' }}</div>
                                </div>
                                <div class="grid grid-cols-[240px_1fr] gap-3">
                                    <div class="text-muted">Status Kerja</div>
                                    <div class="font-medium text-ink">{{ registration.working_status || '-' }}</div>
                                </div>
                                <div class="grid grid-cols-[240px_1fr] gap-3">
                                    <div class="text-muted">Surat Pernyataan</div>
                                    <div class="font-medium text-ink">
                                        <a
                                            v-if="registration.statement_letter_url"
                                            class="text-primary underline"
                                            :href="registration.statement_letter_url"
                                            target="_blank"
                                            rel="noreferrer"
                                        >
                                            Buka Lampiran
                                        </a>
                                        <span v-else>-</span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-[240px_1fr] gap-3">
                                    <div class="text-muted">Tujuan Kerja Setelah Lulus</div>
                                    <div class="font-medium text-ink">{{ registration.graduate_place || '-' }}</div>
                                </div>
                                <div class="grid grid-cols-[240px_1fr] gap-3">
                                    <div class="text-muted">Alasan Tujuan</div>
                                    <div class="font-medium text-ink">{{ registration.graduate_reason || '-' }}</div>
                                </div>
                                <div class="grid grid-cols-[240px_1fr] gap-3">
                                    <div class="text-muted">Lampiran Kelulusan</div>
                                    <div class="font-medium text-ink">
                                        <a
                                            v-if="registration.graduate_url"
                                            class="text-primary underline"
                                            :href="registration.graduate_url"
                                            target="_blank"
                                            rel="noreferrer"
                                        >
                                            Buka Lampiran
                                        </a>
                                        <span v-else>-</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 text-xs uppercase tracking-[0.2em] text-muted">Institusi</div>
                            <div class="mt-4 grid gap-3 text-sm">
                                <div class="grid grid-cols-[240px_1fr] gap-3">
                                    <div class="text-muted">NIP / NRP</div>
                                    <div class="font-medium text-ink">{{ registration.institution_user_id || '-' }}</div>
                                </div>
                                <div class="grid grid-cols-[240px_1fr] gap-3">
                                    <div class="text-muted">Nama</div>
                                    <div class="font-medium text-ink">{{ registration.institution_name || '-' }}</div>
                                </div>
                                <div class="grid grid-cols-[240px_1fr] gap-3">
                                    <div class="text-muted">Alamat</div>
                                    <div class="font-medium text-ink">{{ registration.institution_address || '-' }}</div>
                                </div>
                                <div class="grid grid-cols-[240px_1fr] gap-3">
                                    <div class="text-muted">Kota</div>
                                    <div class="font-medium text-ink">{{ registration.institution_city || '-' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 rounded-2xl border border-border bg-white p-5">
                            <div class="text-xs uppercase tracking-[0.2em] text-muted">III. Asal Pendidikan</div>
                            <div class="mt-4 grid gap-6 md:grid-cols-2">
                                <div class="rounded-2xl border border-border bg-panel p-4">
                                    <div class="text-sm font-semibold text-ink">S1</div>
                                    <div class="mt-3 grid gap-2 text-sm">
                                        <div class="grid grid-cols-[160px_1fr] gap-3">
                                            <div class="text-muted">Universitas</div>
                                            <div class="font-medium text-ink">{{ registration.origin_university || '-' }}</div>
                                        </div>
                                        <div class="grid grid-cols-[160px_1fr] gap-3">
                                            <div class="text-muted">Akreditasi</div>
                                            <div class="font-medium text-ink">{{ registration.origin_university_accreditation || '-' }}</div>
                                        </div>
                                        <div class="grid grid-cols-[160px_1fr] gap-3">
                                            <div class="text-muted">Alamat</div>
                                            <div class="font-medium text-ink">{{ registration.origin_university_address || '-' }}</div>
                                        </div>
                                        <div class="grid grid-cols-[160px_1fr] gap-3">
                                            <div class="text-muted">Tahun Masuk</div>
                                            <div class="font-medium text-ink">{{ registration.s1_init_year || '-' }}</div>
                                        </div>
                                        <div class="grid grid-cols-[160px_1fr] gap-3">
                                            <div class="text-muted">Tahun Lulus</div>
                                            <div class="font-medium text-ink">{{ registration.s1_finish_year || '-' }}</div>
                                        </div>
                                        <div class="grid grid-cols-[160px_1fr] gap-3">
                                            <div class="text-muted">IP S1</div>
                                            <div class="font-medium text-ink">{{ registration.ip_s1 || '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="rounded-2xl border border-border bg-panel p-4">
                                    <div class="text-sm font-semibold text-ink">Profesi</div>
                                    <div class="mt-3 grid gap-2 text-sm">
                                        <div class="grid grid-cols-[180px_1fr] gap-3">
                                            <div class="text-muted">Tahun Masuk</div>
                                            <div class="font-medium text-ink">{{ registration.profession_init_year || '-' }}</div>
                                        </div>
                                        <div class="grid grid-cols-[180px_1fr] gap-3">
                                            <div class="text-muted">Tahun Lulus</div>
                                            <div class="font-medium text-ink">{{ registration.profession_finish_year || '-' }}</div>
                                        </div>
                                        <div class="grid grid-cols-[180px_1fr] gap-3">
                                            <div class="text-muted">IP Profesi</div>
                                            <div class="font-medium text-ink">{{ registration.ip_profession || '-' }}</div>
                                        </div>
                                        <div class="grid grid-cols-[180px_1fr] gap-3">
                                            <div class="text-muted">IP Komulatif</div>
                                            <div class="font-medium text-ink">{{ registration.ip_commulative || '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6">
                                <div class="text-sm font-semibold text-ink">Nilai Tambahan</div>
                                <div v-if="scores.length === 0" class="mt-2 text-sm text-muted">No scores.</div>
                                <div v-else class="mt-3 overflow-hidden rounded-2xl border border-border">
                                    <table class="w-full border-collapse text-sm">
                                        <thead class="bg-panel text-xs text-muted">
                                            <tr>
                                                <th class="px-3 py-2 text-left">Nama</th>
                                                <th class="px-3 py-2 text-left">Nilai</th>
                                                <th class="px-3 py-2 text-left">Lampiran</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-border">
                                            <tr v-for="(score, i) in scores" :key="`score-${i}`">
                                                <td class="px-3 py-2">{{ score.name || '-' }}</td>
                                                <td class="px-3 py-2">{{ score.desc || '-' }}</td>
                                                <td class="px-3 py-2">
                                                    <a
                                                        v-if="score.file_url"
                                                        class="text-primary underline"
                                                        :href="score.file_url"
                                                        target="_blank"
                                                        rel="noreferrer"
                                                    >
                                                        Download
                                                    </a>
                                                    <span v-else>-</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 rounded-2xl border border-border bg-white p-5">
                            <div class="text-xs uppercase tracking-[0.2em] text-muted">IV. Pendidikan Tambahan</div>
                            <SimpleDataTable
                                class="mt-4"
                                :items="educations"
                                :columns="[
                                    { key: 'name', label: 'Nama' },
                                    { key: 'place', label: 'Tempat' },
                                    { key: 'desc', label: 'Bidang' },
                                    { key: 'year', label: 'Tahun' },
                                    { key: 'duration', label: 'Durasi' },
                                ]"
                            />
                        </div>

                        <div class="mt-6 rounded-2xl border border-border bg-white p-5">
                            <div class="text-xs uppercase tracking-[0.2em] text-muted">V. Riwayat Pekerjaan</div>
                            <SimpleDataTable
                                class="mt-4"
                                :items="jobs"
                                :columns="[
                                    { key: 'name', label: 'Tempat Kerja' },
                                    { key: 'duration', label: 'Durasi' },
                                    { key: 'desc', label: 'Jabatan' },
                                    { key: 'year', label: 'Tahun' },
                                ]"
                            />
                        </div>

                        <div class="mt-6 rounded-2xl border border-border bg-white p-5">
                            <div class="text-xs uppercase tracking-[0.2em] text-muted">VI. Kegiatan Ilmiah</div>
                            <SimpleDataTable
                                class="mt-4"
                                :items="scientifics"
                                :columns="[
                                    { key: 'name', label: 'Judul' },
                                    { key: 'desc', label: 'Kegiatan' },
                                    { key: 'year', label: 'Tahun' },
                                ]"
                            />
                        </div>

                        <div class="mt-6 rounded-2xl border border-border bg-white p-5">
                            <div class="text-xs uppercase tracking-[0.2em] text-muted">VII. Organisasi</div>
                            <SimpleDataTable
                                class="mt-4"
                                :items="organisations"
                                :columns="[
                                    { key: 'name', label: 'Organisasi' },
                                    { key: 'desc', label: 'Kegiatan' },
                                    { key: 'year', label: 'Tahun' },
                                ]"
                            />
                        </div>

                        <div class="mt-6 rounded-2xl border border-border bg-white p-5">
                            <div class="text-xs uppercase tracking-[0.2em] text-muted">VIII. Penghargaan</div>
                            <SimpleDataTable
                                class="mt-4"
                                :items="achievements"
                                :columns="[
                                    { key: 'name', label: 'Jenis' },
                                    { key: 'desc', label: 'Judul Kegiatan' },
                                    { key: 'year', label: 'Tahun' },
                                ]"
                            />
                        </div>

                        <div class="mt-6 rounded-2xl border border-border bg-white p-5">
                            <div class="text-xs uppercase tracking-[0.2em] text-muted">IX. Narasumber</div>
                            <SimpleDataTable
                                class="mt-4"
                                :items="recomendations"
                                :columns="[
                                    { key: 'name', label: 'Nama' },
                                    { key: 'desc', label: 'Jabatan' },
                                    { key: 'desc_1', label: 'Hubungan' },
                                    { key: 'contact', label: 'Kontak' },
                                ]"
                            />
                        </div>

                        <div class="mt-6 rounded-2xl border border-border bg-white p-5">
                            <div class="text-xs uppercase tracking-[0.2em] text-muted">X. Logbook</div>
                            <SimpleDataTable
                                class="mt-4"
                                :items="logbooks"
                                :columns="[
                                    { key: 'name', label: 'Nama Tempat' },
                                ]"
                            />
                        </div>
                    </div>

                    <div class="lg:col-span-3">
                        <div class="rounded-2xl border border-border bg-white p-5" v-if="registration.score">
                            <div class="text-xs uppercase tracking-[0.2em] text-muted">Score</div>
                            <div class="mt-4 grid gap-2 text-sm">
                                <div class="flex justify-between gap-4">
                                    <span class="text-muted">Subtotal</span>
                                    <span class="text-ink">{{ registration.score.subtotal_score ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <span class="text-muted">Total</span>
                                    <span class="text-ink">{{ registration.score.total_score ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <span class="text-muted">Lulus</span>
                                    <span class="text-ink">{{ registration.score.is_pass ? 'Ya' : 'Tidak' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </section>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Repository from '../../repository';
import SimpleDataTable from '../../components/SimpleDataTable.vue';

export default {
    components: {
        Loading,
        SimpleDataTable,
    },
    data() {
        return {
            loading: false,
            errorMessage: '',
            registration: {},
            details: [],
            statusSubmitting: false,
            statusForm: {
                status: 100,
            },
            statusOptions: [
                { value: 100, label: 'Pengisian Pendaftaran' },
                { value: 101, label: 'Pengisian Pendaftaran Selesai' },
                { value: 200, label: 'Lolos Administrasi' },
                { value: 201, label: 'Tidak Lolos Administrasi' },
                { value: 300, label: 'Lolos Ujian Tulis - Jurnal' },
                { value: 301, label: 'Tidak Lolos Ujian Tulis - Jurnal' },
                { value: 400, label: 'Diterima' },
                { value: 401, label: 'Tidak Lolos Ujian Wawancara' },
                { value: 500, label: 'Dibatalkan' },
            ],
        };
    },
    computed: {
        genderLabel() {
            if (this.registration.gender === 'M') {
                return 'Laki-laki';
            }
            if (this.registration.gender === 'F') {
                return 'Perempuan';
            }
            return this.registration.gender || '-';
        },
        maritalStatusLabel() {
            if (this.registration.marital_status === 'M') {
                return 'Menikah';
            }
            if (this.registration.marital_status === 'S') {
                return 'Belum Menikah';
            }
            return this.registration.marital_status || '-';
        },
        children() {
            return this.filterDetails('child');
        },
        scores() {
            return this.filterDetails('score');
        },
        jobs() {
            return this.filterDetails('job');
        },
        scientifics() {
            return this.filterDetails('scientific');
        },
        educations() {
            return this.filterDetails('education');
        },
        organisations() {
            return this.filterDetails('organisation');
        },
        achievements() {
            return this.filterDetails('achievement');
        },
        recomendations() {
            return this.filterDetails('recomendation');
        },
        logbooks() {
            return this.filterDetails('logbook');
        },
    },
    created() {
        this.fetchRegistration();
    },
    methods: {
        filterDetails(label) {
            if (!Array.isArray(this.details)) {
                return [];
            }
            return this.details.filter((item) => item && item.label === label);
        },
        fetchRegistration() {
            const id = this.$route && this.$route.params ? this.$route.params.id : null;
            if (!id) {
                this.errorMessage = 'Missing registration id.';
                return Promise.resolve();
            }

            this.loading = true;
            this.errorMessage = '';

            return Repository.get(`/api/registrations/${id}`)
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.registration = result || {};
                    this.details = result && Array.isArray(result.details) ? result.details : [];
                    this.statusForm.status = this.registration.status ?? 100;
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to load registration.';
                    this.errorMessage = message;
                    this.registration = {};
                    this.details = [];
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        updateStatus() {
            const id = this.$route && this.$route.params ? this.$route.params.id : null;
            if (!id) {
                return;
            }

            this.statusSubmitting = true;

            Repository.patch(`/api/registrations/${id}/status`, {
                status: this.statusForm.status,
            })
                .then(() => {
                    this.fetchRegistration();
                    this.$showToast('Status updated successfully.');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to update status.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.statusSubmitting = false;
                });
        },
    },
};
</script>
