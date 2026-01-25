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

        <section class="rounded-2xl border border-border bg-panel p-5">
            <form class="grid gap-4" @submit.prevent="submitBulk">
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Stase ID</span>
                        <select v-model="bulkForm.stase_id"
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
                        <input v-model.trim="bulkForm.type" type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
                    </label>
                    <label class="grid gap-2 text-sm">
                        <span class="text-muted">Category</span>
                        <input v-model.trim="bulkForm.category" type="text"
                            class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
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
                    <div class="mt-4 grid gap-4">
                        <div class="grid gap-3 rounded-xl border border-border/60 bg-white p-3 md:grid-cols-2"
                            v-for="(item, index) in bulkForm.data" :key="index">
                            <label class="grid gap-1 text-xs text-muted">
                                Field 1
                                <input v-model.trim="item.field_1" type="text"
                                    class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
                            </label>
                            <label class="grid gap-1 text-xs text-muted">
                                Field 2
                                <input v-model.trim="item.field_2" type="text"
                                    class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
                            </label>
                            <label class="grid gap-1 text-xs text-muted">
                                Field 3
                                <input v-model.trim="item.field_3" type="text"
                                    class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
                            </label>
                            <label class="grid gap-1 text-xs text-muted">
                                Field 4
                                <input v-model.trim="item.field_4" type="text"
                                    class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
                            </label>
                            <label class="grid gap-1 text-xs text-muted">
                                Field 5
                                <input v-model.trim="item.field_5" type="text"
                                    class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
                            </label>
                            <label class="grid gap-1 text-xs text-muted">
                                Field 6
                                <input v-model.trim="item.field_6" type="text"
                                    class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
                            </label>
                            <div class="flex items-center justify-end md:col-span-2">
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
import Repository from '../../repository';
import Swal from 'sweetalert2';

export default {
    data() {
        return {
            baseUrl: '/api/logbooks/bulk',
            submitting: false,
            errorMessage: '',
            toast: null,
            lectureOptions: [],
            staseOptions: [],
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
        this.initToast();
        this.fetchLectures();
        this.fetchStases();
        this.resetForm();
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
        emptyBulkRow() {
            return {
                field_1: '',
                field_2: '',
                field_3: '',
                field_4: '',
                field_5: '',
                field_6: '',
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
                    this.showToast('Bulk logbook created successfully.');
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
    },
};
</script>
