<template>
    <div class="rounded-2xl border border-border bg-panel p-5">
        <div class="flex items-start gap-3">
            <div class="grid h-10 w-10 place-items-center rounded-xl bg-sky-100 text-sky-600">
                <i class="fa fa-folder-open"></i>
            </div>
            <div class="flex-1">
                <div class="text-sm font-semibold text-ink">{{ title }}</div>
                <div class="text-xs text-muted">{{ subtitle }}</div>
            </div>
        </div>
        <a
            class="mt-4 inline-flex items-center gap-2 rounded-xl border border-border px-3 py-2 text-xs font-semibold text-ink"
            :href="linkUrl"
            target="_blank"
            rel="noopener"
        >
            Buka Dokumen
            <i class="fa fa-arrow-right text-slate-500"></i>
        </a>
    </div>
</template>

<script>
export default {
    props: {
        title: {
            type: String,
            required: true,
        },
        subtitle: {
            type: String,
            required: true,
        },
        linkUrl: {
            type: String,
            required: true,
        },
    },
};
</script>
