<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Logbook</div>
                <h1 class="text-2xl font-semibold text-ink">Create Bulk Logbook</h1>
            </div>
            <router-link class="rounded-xl border border-border px-4 py-2 text-sm text-muted" :to="'/cblu/logbooks'">
                Back to Logbooks
            </router-link>
        </header>

        <section class="relative rounded-2xl border border-border bg-panel p-5">
            <Loading :active="loadingOptions" :is-full-page="false" />
            <form class="flex flex-col gap-4" @submit.prevent="submitBulk">
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Stase ID</span>
                        <select
                            v-model="bulkForm.stase_id"
                            @change="handleStaseChange"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                            <option :value="null">Select stase</option>
                            <option v-for="staseLog in staseOptions" :key="staseLog.id" :value="staseLog.stase_id">
                                {{ staseLog.stase ? staseLog.stase.name : `Stase ${staseLog.stase_id}` }}
                            </option>
                        </select>
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Date</span>
                        <input v-model="bulkForm.date" type="date"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Type</span>
                        <select
                            v-model="bulkForm.type"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                            @change="handleTypeChange"
                        >
                            <option value="">Select type</option>
                            <option v-for="option in staseTypes" :key="option.id" :value="option.value">
                                {{ option.name }}
                            </option>
                        </select>
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Category</span>
                        <select
                            v-model="bulkForm.category"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                        >
                            <option value="">Select category</option>
                            <option v-for="skill in staseSkills" :key="skill.id" :value="skill.value">
                                {{ skill.name }}
                            </option>
                        </select>
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Lecture ID</span>
                        <select v-model="bulkForm.lecture_id"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                            <option :value="null">Select lecture</option>
                            <option v-for="lecture in lectureOptions" :key="lecture.id" :value="lecture.id">
                                {{ lecture.name }}
                            </option>
                        </select>
                    </label>
                </div>

                <div class="rounded-xl border border-dashed border-border bg-white p-4">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-semibold text-ink">Bulk Items</div>
                        <button class="rounded-lg border border-border px-3 py-1 text-xs text-muted" type="button"
                            @click="addBulkRow">
                            Add Row
                        </button>
                    </div>
                    <div class="mt-4 flex flex-col gap-4">
                        <div
                            class="flex flex-col items-start gap-3 rounded-xl border border-border/60 bg-white p-3 lg:flex-row"
                            v-for="(item, index) in bulkForm.data"
                            :key="index"
                        >
                            <div class="flex flex-1 flex-col gap-3 md:flex-row md:flex-wrap">
                                <label
                                    v-for="(fieldLabel, fieldIndex) in availableFields"
                                    :key="`${index}-${fieldIndex}`"
                                    class="grid gap-1 text-xs text-muted"
                                >
                                    {{ fieldLabel }}
                                    <textarea
                                        v-model.trim="item[`field_${fieldIndex + 1}`]"
                                        rows="2"
                                        class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                                    ></textarea>
                                </label>
                            </div>
                            <div class="self-start rounded-xl border border-dashed border-border/60 bg-slate-50 p-3 lg:w-64 lg:flex-none">
                                <div class="text-xs font-semibold text-ink">Skills</div>
                                <div v-if="staseSkills.length" class="mt-3 grid gap-2 text-xs text-muted">
                                    <label
                                        v-for="skill in staseSkills"
                                        :key="`${index}-${skill.id}`"
                                        class="flex items-start gap-2"
                                    >
                                        <input
                                            v-model="item.skills[skill.id]"
                                            type="checkbox"
                                            class="mt-0.5 h-4 w-4 rounded border-border text-primary focus:ring-primary/30"
                                        />
                                        <span class="leading-snug">{{ skill.name }}</span>
                                    </label>
                                </div>
                                <div v-else class="mt-2 text-xs text-muted">Select a stase to load skills.</div>
                            </div>
                            <div class="ml-auto flex items-center justify-end">
                                <button
                                    class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1 text-xs text-rose-600"
                                    type="button" @click="removeBulkRow(index)">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="errorMessage"
                    class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600">
                    {{ errorMessage }}
                </div>
                <button class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white" type="submit"
                    :disabled="submitting">
                    {{ submitting ? 'Saving...' : 'Create Bulk Logbook' }}
                </button>
            </form>
        </section>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Repository from '../../repository';

export default {
    components: {
        Loading,
    },
    data() {
        return {
            baseUrl: '/api/logbooks/bulk',
            submitting: false,
            loadingOptions: false,
            errorMessage: '',
            lectureOptions: [],
            staseOptions: [],
            staseTypes: [],
            staseSkills: [],
            availableFields: ['Field 1', 'Field 2', 'Field 3', 'Field 4', 'Field 5', 'Field 6'],
            bulkForm: {
                stase_id: null,
                type: '',
                category: '',
                lecture_id: null,
                date: '',
                data: [],
            },
        };
    },
    created() {
        this.fetchLectures();
        this.fetchStases();
        this.resetForm();
    },
    methods: {
        emptyBulkRow() {
            return {
                field_1: '',
                field_2: '',
                field_3: '',
                field_4: '',
                field_5: '',
                field_6: '',
                skills: {},
            };
        },
        resetForm() {
            this.bulkForm = {
                stase_id: null,
                type: '',
                category: '',
                lecture_id: null,
                date: '',
                data: [this.emptyBulkRow()],
            };
            this.staseTypes = [];
            this.staseSkills = [];
            this.availableFields = ['Field 1', 'Field 2', 'Field 3', 'Field 4', 'Field 5', 'Field 6'];
        },
        handleStaseChange() {
            const staseId = this.bulkForm.stase_id;
            if (!staseId) {
                this.staseTypes = [];
                this.staseSkills = [];
                this.bulkForm.type = '';
                this.bulkForm.category = '';
                this.availableFields = ['Field 1', 'Field 2', 'Field 3', 'Field 4', 'Field 5', 'Field 6'];
                this.bulkForm.data = this.bulkForm.data.map((item) => ({
                    ...item,
                    skills: {},
                }));
                return;
            }
            this.fetchStaseOptions(staseId);
        },
        handleTypeChange() {
            const selected = this.staseTypes.find((option) => option.value === this.bulkForm.type);
            const parsed = selected ? this.normalizeParseDesc(selected.parse_desc) : [];
            if (parsed.length) {
                this.availableFields = parsed;
            } else {
                this.availableFields = ['Field 1', 'Field 2', 'Field 3', 'Field 4', 'Field 5', 'Field 6'];
            }
        },
        normalizeParseDesc(parseDesc) {
            if (!parseDesc) {
                return [];
            }
            if (Array.isArray(parseDesc)) {
                return parseDesc;
            }
            if (typeof parseDesc === 'object') {
                return ['field_1', 'field_2', 'field_3', 'field_4', 'field_5', 'field_6']
                    .map((key) => parseDesc[key])
                    .filter(Boolean);
            }
            return [];
        },
        addBulkRow() {
            this.bulkForm.data.push(this.emptyBulkRow());
        },
        removeBulkRow(index) {
            this.bulkForm.data.splice(index, 1);
        },
        submitBulk() {
            this.submitting = true;
            this.errorMessage = '';

            return Repository.post(this.baseUrl, this.bulkForm)
                .then(() => {
                    this.$showToast('Bulk logbook created successfully.');
                    this.resetForm();
                })
                .catch((error) => {
                    const message = error && error.response && error.response.data
                        ? error.response.data.text
                        : 'Failed to create bulk logbook.';
                    this.errorMessage = message;
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
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
        fetchStases() {
            return Repository.get('/api/stase-list')
                .then((response) => {
                    const data = response && response.data ? response.data.result : null;
                    this.staseOptions = Array.isArray(data) ? data : [];
                })
                .catch(() => {
                    this.staseOptions = [];
                });
        },
        fetchStaseOptions(staseId) {
            this.loadingOptions = true;
            return Repository.get(`/api/stase-option/${staseId}`)
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    this.staseTypes = result && Array.isArray(result.types) ? result.types : [];
                    this.staseSkills = result && Array.isArray(result.skills) ? result.skills : [];
                    if (this.staseTypes.length && !this.bulkForm.type) {
                        this.bulkForm.type = this.staseTypes[0].value;
                    }
                    if (this.staseSkills.length && !this.bulkForm.category) {
                        this.bulkForm.category = this.staseSkills[0].value;
                    }
                    this.handleTypeChange();
                    this.bulkForm.data = this.bulkForm.data.map((item) => ({
                        ...item,
                        skills: item.skills || {},
                    }));
                })
                .catch(() => {
                    this.staseTypes = [];
                    this.staseSkills = [];
                    this.availableFields = ['Field 1', 'Field 2', 'Field 3', 'Field 4', 'Field 5', 'Field 6'];
                })
                .finally(() => {
                    this.loadingOptions = false;
                });
        },
    },
};
</script>
