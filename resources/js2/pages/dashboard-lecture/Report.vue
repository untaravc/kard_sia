<template>
    <section class="grid gap-4 sm:grid-cols-2">
        <router-link
            to="/blu/presences/daily"
            class="group rounded-2xl border border-border bg-panel p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-sky-200 hover:bg-sky-50/60"
        >
            <div class="text-xs uppercase tracking-[0.2em] text-sky-600">Presences</div>
            <div class="mt-2 text-lg font-semibold text-ink">Daily</div>
            <div class="mt-1 text-xs text-muted">View daily presence report</div>
        </router-link>
        <router-link
            to="/blu/presences/monthly"
            class="group rounded-2xl border border-border bg-panel p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50/60"
        >
            <div class="text-xs uppercase tracking-[0.2em] text-emerald-600">Presences</div>
            <div class="mt-2 text-lg font-semibold text-ink">Monthly</div>
            <div class="mt-1 text-xs text-muted">View monthly presence report</div>
        </router-link>
    </section>
</template>
