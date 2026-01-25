<template>
    <div class="rounded-2xl border border-border bg-panel p-4 shadow-sm">
        <a
            target="_blank"
            :href="linkUrl"
            class="flex items-center gap-3 text-decoration-none"
        >
            <div class="grid h-10 w-10 place-items-center rounded-xl bg-sky-100 text-sky-600">
                <i class="fa fa-folder-open"></i>
            </div>
            <div>
                <div class="text-sm font-semibold text-ink">{{ title }}</div>
                <div class="text-xs text-muted">{{ subtitle }}</div>
            </div>
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
