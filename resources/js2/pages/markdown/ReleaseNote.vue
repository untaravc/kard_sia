<template>
    <section class="rounded-2xl border border-border bg-panel p-6 shadow-sm">
        <Loading :active="loading" :is-full-page="false" />
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <div class="text-xs uppercase tracking-[0.2em] text-muted">Markdown</div>
                <h1 class="text-lg font-semibold text-ink">Release Notes</h1>
            </div>
            <button
                class="rounded-xl border border-border px-4 py-2 text-sm text-muted"
                type="button"
                @click="goBack"
            >
                Back
            </button>
        </div>
        <div
            v-if="errorMessage"
            class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-600"
        >
            {{ errorMessage }}
        </div>
        <div class="mt-5 grid gap-4">
            <div
                v-for="note in notes"
                :key="note.filename"
                class="rounded-2xl border border-border/60 bg-white p-5"
            >
                <div class="flex items-center justify-between gap-3">
                    <div class="text-sm font-semibold text-ink"></div>
                    <div class="text-[11px] text-muted">{{ note.filename }}</div>
                </div>
                <div class="mt-3 release-note" v-html="note.html"></div>
            </div>
            <div v-if="!loading && notes.length === 0" class="text-sm text-muted">
                No release notes available.
            </div>
        </div>
    </section>
</template>

<script>
import Repository from '../../repository';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
    components: {
        Loading,
    },
    data() {
        return {
            notes: [],
            loading: false,
            errorMessage: '',
        };
    },
    created() {
        this.fetchReleaseNote();
    },
    methods: {
        fetchReleaseNote() {
            this.loading = true;
            this.errorMessage = '';
            return Repository.get('/api/release-note')
                .then((response) => {
                    const result = response && response.data ? response.data.result : [];
                    const items = Array.isArray(result) ? result : [];
                    this.notes = items.map((note) => ({
                        ...note,
                        html: this.renderMarkdown(note.content || ''),
                    }));
                })
                .catch(() => {
                    this.errorMessage = 'Failed to load release notes.';
                    this.notes = [];
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        renderMarkdown(value) {
            if (!value) {
                return '';
            }
            const parts = value.split(/```/);
            return parts
                .map((part, index) => {
                    if (index % 2 === 1) {
                        return `<pre class="mt-3 rounded-xl bg-slate-900 px-4 py-3 text-xs text-slate-100"><code>${this.escapeHtml(part.trim())}</code></pre>`;
                    }
                    return this.renderBlocks(part);
                })
                .join('');
        },
        renderBlocks(text) {
            const lines = text.split(/\r?\n/);
            let html = '';
            let inList = false;

            const closeList = () => {
                if (inList) {
                    html += '</ul>';
                    inList = false;
                }
            };

            lines.forEach((line) => {
                const trimmed = line.trim();

                if (trimmed.startsWith('- ') || trimmed.startsWith('* ')) {
                    if (!inList) {
                        html += '<ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-ink">';
                        inList = true;
                    }
                    html += `<li>${this.renderInline(trimmed.slice(2))}</li>`;
                    return;
                }

                closeList();

                if (trimmed === '') {
                    html += '<div class="h-3"></div>';
                    return;
                }

                const headingMatch = line.match(/^(#{1,6})\s+(.*)$/);
                if (headingMatch) {
                    const level = headingMatch[1].length;
                    const sizeClass = level === 1 ? 'text-xl' : level === 2 ? 'text-lg' : 'text-base';
                    html += `<h${level} class="mt-3 font-semibold text-ink ${sizeClass}">${this.renderInline(headingMatch[2])}</h${level}>`;
                    return;
                }

                html += `<p class="text-sm text-ink">${this.renderInline(line)}</p>`;
            });

            closeList();
            return html;
        },
        renderInline(text) {
            let output = this.escapeHtml(text);
            output = output.replace(/`([^`]+)`/g, '<code class="rounded bg-slate-100 px-1 py-0.5 text-xs">$1</code>');
            output = output.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
            output = output.replace(/\*([^*]+)\*/g, '<em>$1</em>');
            output = output.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a class="text-primary underline" href="$2" target="_blank" rel="noopener">$1</a>');
            return output;
        },
        escapeHtml(value) {
            return value
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        },
        goBack() {
            this.$router.back();
        },
    },
};
</script>

<style scoped>
.release-note :deep(p) {
    margin-top: 0.25rem;
}
</style>
