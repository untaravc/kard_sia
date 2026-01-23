<template>
    <div class="mt-6 flex flex-col gap-3 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between">
        <p>
            Showing
            <span class="font-semibold text-slate-900">{{ from }}</span>
            -
            <span class="font-semibold text-slate-900">{{ to }}</span>
            of
            <span class="font-semibold text-slate-900">{{ total }}</span>
        </p>
        <div class="flex flex-wrap items-center gap-2">
            <button
                class="cursor-pointer rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                type="button"
                :disabled="currentPage <= 1 || loading"
                @click="$emit('page-change', currentPage - 1)"
            >
                Prev
            </button>
            <button
                v-for="page in lastPage"
                :key="page"
                class="cursor-pointer rounded-full border px-3 py-1 text-xs font-semibold"
                :class="
                    page === currentPage
                        ? 'border-primary bg-primary text-white'
                        : 'border-slate-200 text-slate-600 hover:bg-slate-50'
                "
                type="button"
                :disabled="loading"
                @click="$emit('page-change', page)"
            >
                {{ page }}
            </button>
            <button
                class="cursor-pointer rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                type="button"
                :disabled="currentPage >= lastPage || loading"
                @click="$emit('page-change', currentPage + 1)"
            >
                Next
            </button>
        </div>
    </div>
</template>

<script setup>
defineProps({
    currentPage: {
        type: Number,
        required: true,
    },
    lastPage: {
        type: Number,
        required: true,
    },
    total: {
        type: Number,
        required: true,
    },
    from: {
        type: Number,
        required: true,
    },
    to: {
        type: Number,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['page-change']);
</script>
