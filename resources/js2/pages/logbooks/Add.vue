<template>
    <div class="grid gap-6">
        <header class="sticky top-0 z-10 flex flex-wrap items-center justify-between gap-3 bg-surface py-2">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Logbook Monitoring</div>
                <h1 class="text-2xl font-semibold text-ink">{{ isEdit ? 'Edit Logbook' : 'Add Logbook' }}</h1>
            </div>
            <div class="flex items-center gap-2">
                <router-link class="rounded-xl border border-border px-4 py-2 text-sm text-muted" to="/blu/logbook-student-daily">
                    Back
                </router-link>
                <button
                    class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                    type="button"
                    :disabled="submitting || loading"
                    @click="submitForm"
                >
                    {{ submitting ? 'Saving...' : 'Save' }}
                </button>
            </div>
        </header>

        <section class="rounded-2xl border border-border bg-panel p-6">
            <form class="grid gap-6 md:grid-cols-2" @submit.prevent="submitForm">
                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Tanggal</span>
                    <input
                        v-model="form.date"
                        type="date"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>

                <label class="grid gap-2 text-sm">
                    <span class="text-muted">No Catatan Medik</span>
                    <input
                        v-model.trim="form.no_catatan_medik"
                        type="text"
                        placeholder="No Catatan Medik"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </label>

                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Rawat Inap</span>
                    <textarea
                        v-model.trim="form.rawat_inap"
                        rows="2"
                        placeholder="Rawat Inap"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    ></textarea>
                </label>

                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Rawat Jalan Poli/UGD</span>
                    <textarea
                        v-model.trim="form.rawat_jalan"
                        rows="2"
                        placeholder="Rawat Jalan Poli/UGD"
                        class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    ></textarea>
                </label>

                <label class="grid gap-2 text-sm">
                    <span class="text-muted">Dosen</span>
                    <v-select
                        v-model="form.lecture_id"
                        :options="lectureOptions"
                        :reduce="(lecture) => lecture.id"
                        :get-option-label="getLectureOptionLabel"
                        :clearable="true"
                        placeholder="Cari dosen..."
                    />
                </label>

                <div
                    v-if="errorMessage"
                    class="md:col-span-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600"
                >
                    {{ errorMessage }}
                </div>
            </form>
        </section>

        <section class="rounded-2xl border border-border bg-panel">
            <button
                type="button"
                class="flex w-full items-center justify-between px-6 py-4 text-left"
                @click="competenceOpen = !competenceOpen"
            >
                <span class="text-sm font-semibold text-ink">Kompetensi</span>
                <Icon :icon="competenceOpen ? 'mdi:chevron-up' : 'mdi:chevron-down'" class="h-5 w-5 text-muted" />
            </button>
            <div v-show="competenceOpen" class="border-t border-border px-6 py-4">
                <div v-if="competenceLoading" class="text-xs text-muted">Loading...</div>
                <div v-else-if="!competenceOptions.length" class="text-xs text-muted">Tidak ada data kompetensi.</div>
                <div v-else class="grid gap-6">
                    <div v-for="(items, category) in groupedCompetence" :key="category">
                        <div class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-muted">
                            {{ category }}
                        </div>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <label
                                v-for="option in items"
                                :key="option.id"
                                class="flex items-center justify-between gap-2 text-sm"
                            >
                                <span class="flex items-center gap-2">
                                    <input
                                        v-model="form.competence_ids"
                                        type="checkbox"
                                        :value="option.id"
                                        class="h-4 w-4 rounded border-border text-primary focus:ring-primary/30"
                                    />
                                    <span>{{ option.name }}</span>
                                </span>
                                <span class="text-xs text-muted">{{ option.count || 0 }}</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import Repository from '../../repository';
import { Icon } from '../../icons';

export default {
    components: {
        Icon,
    },
    data() {
        return {
            baseUrl: '/api/logbook-student-add',
            submitting: false,
            loading: false,
            errorMessage: '',
            lectureOptions: [],
            competenceOpen: true,
            competenceLoading: false,
            competenceOptions: [],
            form: {
                date: '',
                no_catatan_medik: '',
                rawat_inap: '',
                rawat_jalan: '',
                lecture_id: '',
                competence_ids: [],
            },
        };
    },
    computed: {
        isEdit() {
            return Boolean(this.$route.params && this.$route.params.id);
        },
        logbookId() {
            return this.$route.params ? this.$route.params.id : null;
        },
        groupedCompetence() {
            const groups = {};
            this.competenceOptions.forEach((option) => {
                const category = option.desc || 'Lainnya';
                if (!groups[category]) {
                    groups[category] = [];
                }
                groups[category].push(option);
            });
            return groups;
        },
    },
    created() {
        this.fetchLectures();
        this.fetchCompetenceOptions();
        if (this.isEdit) {
            this.fetchLogbook();
        }
    },
    methods: {
        fetchLectures() {
            return Repository.get('/api/lecture-list')
                .then((response) => {
                    const data = response && response.data ? response.data.result : null;
                    this.lectureOptions = Array.isArray(data) ? data : [];
                })
                .catch(() => {
                    this.lectureOptions = [];
                });
        },
        getLectureOptionLabel(lecture) {
            return lecture && lecture.name ? lecture.name : '';
        },
        fetchCompetenceOptions() {
            this.competenceLoading = true;

            return Repository.get('/api/logbook-student-competence')
                .then((response) => {
                    const data = response && response.data ? response.data.result : null;
                    this.competenceOptions = Array.isArray(data) ? data : [];
                })
                .catch(() => {
                    this.competenceOptions = [];
                })
                .finally(() => {
                    this.competenceLoading = false;
                });
        },
        fetchLogbook() {
            this.loading = true;
            this.errorMessage = '';

            return Repository.get(`/api/logbooks/${this.logbookId}`)
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    const logbook = result ? result.logbook : null;
                    const skills = logbook && Array.isArray(logbook.stase_log_skills) ? logbook.stase_log_skills : [];
                    this.form = {
                        date: logbook && logbook.date ? logbook.date : '',
                        no_catatan_medik: logbook && logbook.field_1 ? logbook.field_1 : '',
                        rawat_inap: logbook && logbook.field_2 ? logbook.field_2 : '',
                        rawat_jalan: logbook && logbook.field_3 ? logbook.field_3 : '',
                        lecture_id: logbook && logbook.lecture_id ? logbook.lecture_id : '',
                        competence_ids: skills.map((skill) => skill.form_option_id),
                    };
                })
                .catch(() => {
                    this.errorMessage = 'Failed to load logbook.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        submitForm() {
            if (this.submitting || this.loading) {
                return;
            }

            if (!this.form.date || !this.form.no_catatan_medik) {
                this.errorMessage = 'Tanggal dan No Catatan Medik wajib diisi.';
                return;
            }

            this.submitting = true;
            this.errorMessage = '';

            const request = this.isEdit
                ? Repository.put(`${this.baseUrl}/${this.logbookId}`, this.form)
                : Repository.post(this.baseUrl, this.form);

            return request
                .then(() => {
                    this.$showToast(this.isEdit ? 'Logbook updated successfully.' : 'Logbook created successfully.');
                    this.$router.push('/blu/logbook-student-daily');
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to save logbook.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
    },
};
</script>
