<template>
    <div class="rounded-2xl border border-border bg-panel p-4 shadow-sm">
        <div class="flex items-baseline justify-between">
            <div class="text-sm font-semibold text-ink">{{ staseName }}</div>
            <div class="text-xs text-muted">Active assignments and reviews</div>
        </div>
        <div class="mt-3 grid gap-3">
            <div class="rounded-xl border border-border/60 bg-white p-3" v-for="task in tasks" :key="task.id">
                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div class="flex-1">
                        <div v-if="task.plan" class="text-xs text-muted">Plan: {{ task.plan }}</div>
                        <div class="mt-1 flex items-center gap-2">
                            <span
                                class="rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                :class="task.open ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'"
                            >
                                {{ task.open ? 'open' : 'closed' }}
                            </span>
                            <span class="text-sm font-semibold text-ink">{{ task.name }}</span>
                        </div>
                        <div class="mt-1 text-xs text-muted" v-if="task.title">
                            <i>{{ task.title }}</i>
                        </div>
                        <div class="mt-2 text-xs text-muted" v-if="task.reviewers && task.reviewers.length">
                            <div v-for="reviewer in task.reviewers" :key="reviewer.name">
                                <i class="fa fa-marker"></i> {{ reviewer.name }}
                                <span
                                    class="ml-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700"
                                    v-if="reviewer.score > 0"
                                >
                                    {{ reviewer.score }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <button
                            class="rounded-lg border border-border px-3 py-1.5 text-xs font-semibold text-ink"
                            type="button"
                            v-if="task.open"
                        >
                            Upload Assignment File
                        </button>
                        <button
                            class="rounded-lg bg-emerald-500 px-3 py-1.5 text-xs font-semibold text-white"
                            type="button"
                            v-else
                        >
                            Open
                        </button>
                    </div>
                </div>
            </div>
            <div v-if="tasks.length === 0" class="text-xs text-muted">
                No active assignments.
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        staseName: {
            type: String,
            required: true,
        },
        tasks: {
            type: Array,
            required: true,
        },
    },
};
</script>
