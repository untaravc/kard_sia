<template>
    <div class="hidden rounded-2xl border border-border bg-panel p-4 shadow-sm lg:block">
        <div class="text-sm font-semibold text-ink">Stase Terambil</div>
        <div class="mt-3 grid gap-3">
            <div class="rounded-xl border border-border/60 bg-white px-3 py-2" v-for="item in stases" :key="item.id">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span
                            class="rounded-full px-2 py-0.5 text-[11px] font-semibold"
                            :class="item.status === 'ongoing' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'"
                        >
                            {{ item.status }}
                        </span>
                        <span class="text-sm font-semibold text-ink">{{ item.name }}</span>
                    </div>
                    <span class="text-[11px] text-muted">{{ item.period }}</span>
                </div>
                <div class="mt-1 text-xs text-muted" v-if="item.evaluation_link">
                    <i class="fa fa-check-circle text-emerald-500" v-if="item.evaluated"></i>
                    Form Evaluasi
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        stases: {
            type: Array,
            required: true,
        },
    },
};
</script>
