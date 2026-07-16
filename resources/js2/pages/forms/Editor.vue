<template>
    <div class="grid gap-5 pb-16">
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <button class="rounded-xl border border-border px-3 py-2 text-sm text-muted"
                        type="button" @click="$router.push('/blu/forms')">
                    &larr; Kembali
                </button>
                <div>
                    <div class="text-xs uppercase tracking-[0.2em] text-muted">Form Builder</div>
                    <h1 class="text-2xl font-semibold text-ink">{{ form.id ? 'Ubah Formulir' : 'Buat Formulir' }}</h1>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a v-if="form.id" class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                   :href="publicUrl" target="_blank">Pratinjau</a>
                <button class="rounded-xl bg-primary px-5 py-2 text-sm font-medium text-white"
                        type="button" :disabled="saving" @click="save">
                    {{ saving ? 'Menyimpan...' : 'Simpan' }}
                </button>
            </div>
        </header>

        <div v-if="errorMessage" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-600">
            {{ errorMessage }}
        </div>

        <div class="relative">
            <Loading :active="loading" :is-full-page="false" />

            <!-- Form settings -->
            <section class="overflow-hidden rounded-2xl border border-border bg-panel">
                <div class="h-2 bg-primary"></div>
                <div class="grid gap-4 p-6">
                    <input v-model="form.title" type="text" placeholder="Judul formulir"
                           class="w-full border-0 border-b border-border bg-transparent px-1 py-2 text-2xl font-semibold text-ink focus:border-primary focus:outline-none" />
                    <textarea v-model="form.description" rows="2" placeholder="Deskripsi (opsional)"
                              class="w-full rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"></textarea>

                    <div class="grid gap-4 md:grid-cols-3">
                        <label class="grid gap-1 text-sm">
                            <span class="text-xs text-muted">Visibilitas</span>
                            <select v-model="form.visibility"
                                    class="rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                                <option value="public">Publik (siapa saja)</option>
                                <option value="auth">Harus Login</option>
                            </select>
                        </label>
                        <label class="grid gap-1 text-sm">
                            <span class="text-xs text-muted">Status</span>
                            <select v-model.number="form.status"
                                    class="rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                                <option :value="0">Draft</option>
                                <option :value="1">Publish</option>
                                <option :value="2">Ditutup</option>
                            </select>
                        </label>
                        <div class="grid gap-1 text-sm">
                            <span class="text-xs text-muted">Opsi</span>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.allow_multiple" />
                                <span>Boleh isi berkali-kali</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.collect_email" />
                                <span>Minta email (tamu)</span>
                            </label>
                        </div>
                    </div>

                    <div v-if="form.id" class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 text-xs text-muted">
                        <span>Tautan publik:</span>
                        <code class="text-primary">{{ publicUrl }}</code>
                        <button class="text-primary underline" type="button" @click="copyLink">salin</button>
                    </div>
                </div>
            </section>

            <!-- Fields -->
            <section v-for="(field, i) in form.fields" :key="i"
                     class="mt-4 rounded-2xl border border-border bg-panel p-5">
                <div class="flex flex-wrap gap-3">
                    <input v-model="field.label" type="text" placeholder="Pertanyaan"
                           class="flex-1 min-w-[220px] rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
                    <select v-model="field.type"
                            class="w-56 rounded-xl border border-border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                        <option v-for="t in fieldTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                    </select>
                </div>

                <input v-model="field.description" type="text" placeholder="Keterangan / bantuan (opsional)"
                       class="mt-3 w-full rounded-xl border border-border bg-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-primary/30" />

                <input v-if="['text','textarea','number','email'].includes(field.type)"
                       v-model="field.placeholder" type="text" placeholder="Placeholder (opsional)"
                       class="mt-2 w-full rounded-xl border border-border bg-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-primary/30" />

                <!-- Options editor -->
                <div v-if="isChoice(field.type)" class="mt-3 grid gap-2">
                    <div v-for="(opt, oi) in field.options" :key="oi" class="flex items-center gap-2">
                        <span class="text-muted">
                            <span v-if="field.type === 'checkbox'">&#9744;</span>
                            <span v-else-if="field.type === 'radio'">&#9711;</span>
                            <span v-else>&#9662;</span>
                        </span>
                        <input v-model="field.options[oi]" type="text" placeholder="Opsi"
                               class="flex-1 rounded-lg border border-border bg-white px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
                        <button class="text-rose-500" type="button" @click="removeOption(field, oi)">&times;</button>
                    </div>
                    <button class="justify-self-start text-sm text-primary" type="button" @click="addOption(field)">
                        + Tambah opsi
                    </button>
                </div>

                <!-- Linear scale config -->
                <div v-if="field.type === 'rating'" class="mt-3 grid gap-2">
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-xs text-muted">Skala 1 sampai</span>
                        <input v-model.number="field.max_rating" type="number" min="2" max="10"
                               class="w-20 rounded-lg border border-border bg-white px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30" />
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        <span v-for="n in scalePreview(field.max_rating)" :key="n"
                              class="flex h-8 min-w-[2rem] items-center justify-center rounded-md border border-border px-1 text-xs text-muted">
                            {{ n }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between border-t border-border pt-3">
                    <label class="flex items-center gap-2 text-sm text-muted">
                        <input type="checkbox" v-model="field.is_required" />
                        <span>Wajib diisi</span>
                    </label>
                    <div class="flex items-center gap-1">
                        <button class="rounded-lg border border-border px-2 py-1 text-xs text-muted"
                                type="button" :disabled="i === 0" @click="move(i, -1)">&uarr;</button>
                        <button class="rounded-lg border border-border px-2 py-1 text-xs text-muted"
                                type="button" :disabled="i === form.fields.length - 1" @click="move(i, 1)">&darr;</button>
                        <button class="rounded-lg border border-border px-2 py-1 text-xs text-rose-600 hover:bg-rose-50"
                                type="button" @click="removeField(i)">Hapus</button>
                    </div>
                </div>
            </section>

            <button class="mt-4 w-full rounded-2xl border border-dashed border-primary/50 bg-primary/5 px-4 py-3 text-sm font-medium text-primary"
                    type="button" @click="addField">
                + Tambah Pertanyaan
            </button>
        </div>
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
            loading: false,
            saving: false,
            errorMessage: '',
            fieldTypes: [
                { value: 'text', label: 'Jawaban Singkat' },
                { value: 'textarea', label: 'Paragraf' },
                { value: 'radio', label: 'Pilihan Ganda' },
                { value: 'checkbox', label: 'Kotak Centang' },
                { value: 'select', label: 'Dropdown' },
                { value: 'number', label: 'Angka' },
                { value: 'email', label: 'Email' },
                { value: 'date', label: 'Tanggal' },
                { value: 'rating', label: 'Skala Linier' },
            ],
            form: {
                id: null,
                title: '',
                slug: '',
                description: '',
                visibility: 'public',
                status: 1,
                allow_multiple: true,
                collect_email: false,
                fields: [],
            },
        };
    },
    computed: {
        publicUrl() {
            return `${window.location.origin}/form/${this.form.slug}`;
        },
    },
    created() {
        if (this.$route.params.id) {
            this.loadForm(this.$route.params.id);
        } else {
            this.addField();
        }
    },
    methods: {
        isChoice(type) {
            return ['select', 'radio', 'checkbox'].includes(type);
        },
        scalePreview(max) {
            const n = Math.min(Math.max(parseInt(max, 10) || 5, 2), 10);
            return Array.from({ length: n }, (_, i) => i + 1);
        },
        loadForm(id) {
            this.loading = true;
            Repository.get(`${this.baseUrl}/${id}`)
                .then((response) => {
                    const result = response && response.data ? response.data.result : null;
                    if (!result) return;
                    this.form = {
                        id: result.id,
                        title: result.title || '',
                        slug: result.slug || '',
                        description: result.description || '',
                        visibility: result.visibility || 'public',
                        status: Number(result.status),
                        allow_multiple: !!result.allow_multiple,
                        collect_email: !!result.collect_email,
                        fields: (result.fields || []).map((f) => ({
                            id: f.id,
                            type: f.type,
                            label: f.label || '',
                            description: f.description || '',
                            placeholder: f.placeholder || '',
                            options: Array.isArray(f.options) ? f.options : [],
                            is_required: !!f.is_required,
                            max_rating: f.max_rating || 5,
                        })),
                    };
                })
                .catch(() => {
                    this.errorMessage = 'Gagal memuat formulir.';
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        addField() {
            this.form.fields.push({
                id: null,
                type: 'text',
                label: '',
                description: '',
                placeholder: '',
                options: [],
                is_required: false,
                max_rating: 5,
            });
        },
        removeField(i) {
            this.form.fields.splice(i, 1);
        },
        move(i, dir) {
            const target = i + dir;
            if (target < 0 || target >= this.form.fields.length) return;
            const arr = this.form.fields;
            arr.splice(target, 0, arr.splice(i, 1)[0]);
        },
        addOption(field) {
            if (!Array.isArray(field.options)) {
                this.$set(field, 'options', []);
            }
            field.options.push('');
        },
        removeOption(field, oi) {
            field.options.splice(oi, 1);
        },
        save() {
            this.saving = true;
            this.errorMessage = '';

            const request = this.form.id
                ? Repository.put(`${this.baseUrl}/${this.form.id}`, this.form)
                : Repository.post(this.baseUrl, this.form);

            request
                .then((response) => {
                    const saved = response && response.data ? response.data.result : null;
                    this.$showToast('Formulir disimpan.');
                    if (!this.form.id && saved && saved.id) {
                        this.$router.push(`/blu/forms/${saved.id}`);
                    } else if (saved) {
                        this.form.slug = saved.slug;
                    }
                })
                .catch((error) => {
                    this.errorMessage = error && error.response && error.response.data
                        ? error.response.data.text || 'Gagal menyimpan formulir.'
                        : 'Gagal menyimpan formulir.';
                })
                .finally(() => {
                    this.saving = false;
                });
        },
        copyLink() {
            const el = document.createElement('textarea');
            el.value = this.publicUrl;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            this.$showToast('Tautan disalin.');
        },
    },
};
</script>
