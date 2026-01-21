<template>
    <header class="flex flex-col gap-4 border-b border-border bg-panel px-9 py-7 lg:flex-row lg:items-center lg:justify-between">
        <div class="space-y-1">
            <div class="text-2xl font-semibold">{{ title }}</div>
            <div class="text-muted">{{ subtitle }}</div>
        </div>
        <div class="flex w-full flex-wrap items-center justify-between gap-3 lg:w-auto lg:flex-nowrap lg:gap-4">
            <div>
                <input
                    class="w-full rounded-xl border border-border bg-panel px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 lg:w-60"
                    type="text"
                    placeholder="Search residents, stase, logs"
                />
            </div>
            <div class="flex items-center gap-2.5">
                <div class="grid h-10 w-10 place-items-center rounded-full bg-accent font-semibold text-ink">SA</div>
                <div class="leading-tight">
                    <div class="font-semibold">Super Admin</div>
                    <div class="text-xs text-muted">Cardio Admin</div>
                </div>
            </div>
        </div>
    </header>
</template>

<script>
export default {
    props: {
        title: {
            type: String,
            default: 'Dashboard',
        },
        subtitle: {
            type: String,
            default: 'Overview for this week',
        },
    },
};
</script>
