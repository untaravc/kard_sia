<template>
    <div class="grid gap-5">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <button class="rounded-xl border border-border px-3 py-2 text-sm text-muted"
                        type="button" @click="$router.push('/blu/forms')">
                    &larr; Kembali
                </button>
                <div>
                    <div class="text-xs uppercase tracking-[0.2em] text-muted">Tanggapan</div>
                    <h1 class="text-2xl font-semibold text-ink">{{ form.title || 'Formulir' }}</h1>
                </div>
            </div>
            <button class="rounded-xl border border-border px-4 py-2 text-sm text-ink"
                    type="button" @click="$router.push(`/blu/forms/${$route.params.id}`)">
                Ubah Formulir
            </button>
        </header>

        <section class="relative rounded-2xl border border-border bg-panel">
            <Loading :active="loading" :is-full-page="false" />
            <div class="flex items-center justify-between border-b border-border px-5 py-4">
                <div class="font-semibold">Daftar Tanggapan</div>
                <div class="text-xs text-muted" v-if="pagination.total">
                    {{ pagination.from }}-{{ pagination.to }} dari {{ pagination.total }}
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border text-left text-xs uppercase tracking-wide text-muted">
                            <th class="px-4 py-3 w-10">#</th>
                            <th class="px-4 py-3">Responden</th>
                            <th class="px-4 py-3" v-for="field in form.fields" :key="field.id">{{ field.label }}</th>
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!loading && responses.length === 0">
                            <td class="px-4 py-6 text-muted" :colspan="(form.fields ? form.fields.length : 0) + 4">
                                Belum ada tanggapan.
                            </td>
                        </tr>
                        <tr v-for="(resp, index) in responses" :key="resp.id" class="border-b border-border align-top">
                            <td class="px-4 py-3 text-muted">
                                {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-ink">{{ resp.respondent_name || 'Anonim' }}</div>
                                <div class="text-xs text-muted">{{ resp.respondent_email }}</div>
                                <span class="text-[10px] uppercase text-muted">{{ resp.respondent_type }}</span>
                            </td>
                            <td class="px-4 py-3" v-for="field in form.fields" :key="field.id">
                                {{ answerFor(resp, field.id) }}
                            </td>
                            <td class="px-4 py-3 text-xs text-muted">{{ formatDate(resp.created_at) }}</td>
                            <td class="px-4 py-3 text-right">
                                <button class="rounded-lg border border-border px-2 py-1 text-xs text-ink"
                                        type="button" @click="openDetail(resp)">Detail</button>
                                <button class="rounded-lg border border-border px-2 py-1 text-xs text-rose-600 hover:bg-rose-50"
                                        type="button" @click="deleteResponse(resp)">Hapus</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex items-center justify-between border-t border-border px-5 py-4 text-xs text-muted">
                <button class="rounded-lg border border-border px-3 py-1.5" type="button"
                        :disabled="pagination.current_page <= 1"
                        @click="changePage(pagination.current_page - 1)">Prev</button>
                <div>Halaman {{ pagination.current_page || 1 }} / {{ pagination.last_page || 1 }}</div>
                <button class="rounded-lg border border-border px-3 py-1.5" type="button"
                        :disabled="pagination.current_page >= pagination.last_page"
                        @click="changePage(pagination.current_page + 1)">Next</button>
            </div>
        </section>

        <Modal :open="detailOpen" title="Detail Tanggapan" eyebrow="Tanggapan" size="md" @close="detailOpen = false">
            <div v-if="detail" class="grid gap-3">
                <div class="rounded-xl bg-slate-50 px-3 py-2 text-sm">
                    <div class="font-medium text-ink">{{ detail.respondent_name || 'Anonim' }}</div>
                    <div class="text-xs text-muted">{{ detail.respondent_email }} &middot; {{ detail.respondent_type }}</div>
                    <div class="text-xs text-muted">{{ formatDate(detail.created_at) }}</div>
                </div>
                <div v-for="field in form.fields" :key="field.id" class="grid gap-1">
                    <div class="text-xs font-semibold text-muted">{{ field.label }}</div>
                    <div class="text-sm text-ink">{{ answerFor(detail, field.id) || '—' }}</div>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Modal from '../../components/Modal.vue';
import Repository from '../../repository';

export default {
    components: { Loading, Modal },
    data() {
        return {
            form: { title: '', fields: [] },
            responses: [],
            pagination: {},
            loading: false,
            page: 1,
            detailOpen: false,
            detail: null,
        };
    },
    created() {
        this.fetchResponses();
    },
    methods: {
        fetchResponses() {
            this.loading = true;
            const id = this.$route.params.id;
            Repository.get(`/api/forms/${id}/responses`, { params: { page: this.page } })
                .then((response) => {
                    const body = response && response.data ? response.data : {};
                    const result = body.result || {};
                    this.responses = Array.isArray(result.data) ? result.data : [];
                    this.pagination = result;
                    if (body.form) this.form = body.form;
                })
                .catch(() => {
                    this.responses = [];
                    this.pagination = {};
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        answerFor(resp, fieldId) {
            const answers = resp && Array.isArray(resp.answers) ? resp.answers : [];
            const answer = answers.find((a) => a.form_field_id === fieldId);
            if (!answer) return '';
            // display_value is appended server-side (checkbox arrays flattened).
            if (answer.display_value !== undefined && answer.display_value !== null) {
                return answer.display_value;
            }
            return answer.value;
        },
        formatDate(value) {
            if (!value) return '';
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return value;
            return date.toLocaleString('id-ID', {
                day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
            });
        },
        changePage(page) {
            this.page = page;
            this.fetchResponses();
        },
        openDetail(resp) {
            this.detail = resp;
            this.detailOpen = true;
        },
        deleteResponse(resp) {
            if (!window.confirm('Hapus tanggapan ini?')) return;
            Repository.delete(`/api/form-responses/${resp.id}`)
                .then(() => {
                    this.fetchResponses();
                    this.$showToast('Tanggapan dihapus.');
                })
                .catch(() => {
                    this.$showToast('Gagal menghapus tanggapan.', 'error');
                });
        },
    },
};
</script>
