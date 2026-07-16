<template>
    <div class="grid gap-6">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Form Builder</div>
                <h1 class="text-2xl font-semibold text-ink">Formulir</h1>
            </div>
            <button
                class="rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white"
                type="button"
                @click="$router.push('/blu/forms/create')"
            >
                Buat Formulir
            </button>
        </header>

        <section class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="rounded-2xl border border-border bg-panel p-4">
                <div class="text-xs text-muted">Draft</div>
                <div class="text-2xl font-semibold text-ink">{{ stats.draft }}</div>
            </div>
            <div class="rounded-2xl border border-border bg-panel p-4">
                <div class="text-xs text-muted">Publish</div>
                <div class="text-2xl font-semibold text-ink">{{ stats.published }}</div>
            </div>
            <div class="rounded-2xl border border-border bg-panel p-4">
                <div class="text-xs text-muted">Ditutup</div>
                <div class="text-2xl font-semibold text-ink">{{ stats.closed }}</div>
            </div>
            <div class="rounded-2xl border border-border bg-panel p-4">
                <div class="text-xs text-muted">Total Tanggapan</div>
                <div class="text-2xl font-semibold text-ink">{{ stats.responses }}</div>
            </div>
        </section>

        <section class="rounded-2xl border border-border bg-panel p-5">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="text-xs text-muted">Kata kunci</label>
                    <input
                        v-model.trim="filters.keyword"
                        type="text"
                        @keyup.enter="applyFilter"
                        placeholder="Cari judul..."
                        class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                    />
                </div>
                <div class="min-w-[160px]">
                    <label class="text-xs text-muted">Visibilitas</label>
                    <select v-model="filters.visibility" @change="applyFilter"
                            class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option value="">Semua</option>
                        <option value="public">Publik</option>
                        <option value="auth">Harus Login</option>
                    </select>
                </div>
                <div class="min-w-[160px]">
                    <label class="text-xs text-muted">Status</label>
                    <select v-model="filters.status" @change="applyFilter"
                            class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option value="">Semua</option>
                        <option value="0">Draft</option>
                        <option value="1">Publish</option>
                        <option value="2">Ditutup</option>
                    </select>
                </div>
            </div>
        </section>

        <section class="relative rounded-2xl border border-border bg-panel">
            <Loading :active="loading" :is-full-page="false" />
            <div class="flex items-center justify-between border-b border-border px-5 py-4">
                <div class="font-semibold">Daftar Formulir</div>
                <div class="text-xs text-muted" v-if="pagination.total">
                    {{ pagination.from }}-{{ pagination.to }} dari {{ pagination.total }}
                </div>
            </div>
            <div class="divide-y divide-border">
                <div v-if="!loading && forms.length === 0" class="px-5 py-6 text-sm text-muted">
                    Belum ada formulir.
                </div>
                <div
                    v-for="(item, index) in forms"
                    :key="item.id"
                    class="flex flex-wrap items-center gap-3 px-5 py-4"
                >
                    <div class="w-8 text-sm font-semibold text-muted">
                        {{ (pagination.from ? pagination.from - 1 : 0) + index + 1 }}
                    </div>
                    <div class="flex-1 min-w-[220px]">
                        <div class="font-semibold text-ink">{{ item.title }}</div>
                        <div class="text-xs text-muted">/form/{{ item.slug }}</div>
                    </div>
                    <span class="rounded-full px-2 py-0.5 text-xs"
                          :class="item.visibility === 'auth' ? 'bg-amber-100 text-amber-700' : 'bg-sky-100 text-sky-700'">
                        {{ item.visibility_label }}
                    </span>
                    <span class="rounded-full px-2 py-0.5 text-xs"
                          :class="statusClass(item.status)">
                        {{ item.status_label }}
                    </span>
                    <div class="text-xs text-muted w-20 text-center">
                        <div class="font-semibold text-ink">{{ item.fields_count }}</div>
                        field
                    </div>
                    <div class="text-xs text-muted w-24 text-center">
                        <div class="font-semibold text-ink">{{ item.responses_count }}</div>
                        tanggapan
                    </div>
                    <div class="flex items-center gap-1">
                        <button class="rounded-lg border border-border px-2 py-1.5 text-xs text-muted"
                                title="Salin tautan" type="button" @click="copyLink(item)">
                            Salin
                        </button>
                        <a class="rounded-lg border border-border px-2 py-1.5 text-xs text-muted"
                           :href="item.public_url" target="_blank">Buka</a>
                        <button class="rounded-lg border border-border px-3 py-1.5 text-xs text-ink"
                                type="button" @click="$router.push(`/blu/forms/${item.id}/responses`)">
                            Tanggapan
                        </button>
                        <button class="rounded-lg bg-primary px-3 py-1.5 text-xs text-white"
                                type="button" @click="$router.push(`/blu/forms/${item.id}`)">
                            Ubah
                        </button>
                        <button class="rounded-lg border border-border px-3 py-1.5 text-xs text-rose-600 hover:bg-rose-50"
                                type="button" @click="deleteForm(item)">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between border-t border-border px-5 py-4 text-xs text-muted">
                <button class="rounded-lg border border-border px-3 py-1.5" type="button"
                        :disabled="pagination.current_page <= 1"
                        @click="changePage(pagination.current_page - 1)">
                    Prev
                </button>
                <div>Halaman {{ pagination.current_page || 1 }} / {{ pagination.last_page || 1 }}</div>
                <button class="rounded-lg border border-border px-3 py-1.5" type="button"
                        :disabled="pagination.current_page >= pagination.last_page"
                        @click="changePage(pagination.current_page + 1)">
                    Next
                </button>
            </div>
        </section>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import Repository from '../../repository';

export default {
    components: { Loading },
    data() {
        return {
            baseUrl: '/api/forms',
            forms: [],
            pagination: {},
            stats: { draft: 0, published: 0, closed: 0, responses: 0 },
            filters: { keyword: '', visibility: '', status: '', page: 1 },
            loading: false,
        };
    },
    created() {
        this.fetchForms();
    },
    methods: {
        fetchForms() {
            this.loading = true;
            return Repository.get(this.baseUrl, { params: this.filters })
                .then((response) => {
                    const body = response && response.data ? response.data : {};
                    const result = body.result || {};
                    this.forms = Array.isArray(result.data) ? result.data : [];
                    this.pagination = result;
                    this.stats = body.stats || this.stats;
                })
                .catch(() => {
                    this.forms = [];
                    this.pagination = {};
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        statusClass(status) {
            if (status === 1) return 'bg-emerald-100 text-emerald-700';
            if (status === 2) return 'bg-rose-100 text-rose-700';
            return 'bg-slate-100 text-slate-600';
        },
        applyFilter() {
            this.filters.page = 1;
            this.fetchForms();
        },
        changePage(page) {
            this.filters.page = page;
            this.fetchForms();
        },
        copyLink(item) {
            const el = document.createElement('textarea');
            el.value = item.public_url;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            this.$showToast('Tautan disalin.');
        },
        deleteForm(item) {
            if (!window.confirm(`Hapus formulir "${item.title}" beserta seluruh tanggapannya?`)) {
                return;
            }
            Repository.delete(`${this.baseUrl}/${item.id}`)
                .then(() => {
                    this.fetchForms();
                    this.$showToast('Formulir dihapus.');
                })
                .catch(() => {
                    this.$showToast('Gagal menghapus formulir.', 'error');
                });
        },
    },
};
</script>
