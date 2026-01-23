<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
        :class="open ? 'pointer-events-auto opacity-100' : 'pointer-events-none opacity-0'"
    >
        <div class="absolute inset-0 bg-slate-900/40" @click="$emit('close')"></div>
        <div
            class="relative w-full rounded-2xl border border-slate-200 bg-white p-6 shadow-xl"
            :class="sizeClass"
        >
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p v-if="eyebrow" class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ eyebrow }}</p>
                    <h2 class="text-lg font-semibold text-slate-900">{{ title }}</h2>
                </div>
                <button
                    class="p-2 text-slate-600 hover:bg-slate-50 cursor-pointer"
                    type="button"
                    aria-label="Close"
                    @click="$emit('close')"
                >
                    <Icon icon="mdi:close" class="h-4 w-4" />
                </button>
            </div>
            <div class="mt-5">
                <slot />
            </div>
            <div v-if="$slots.footer" class="mt-6 flex items-center justify-end gap-2">
                <slot name="footer" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { Icon } from '../icons';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        required: true,
    },
    eyebrow: {
        type: String,
        default: '',
    },
    size: {
        type: String,
        default: 'md',
    },
});

defineEmits(['close']);

const sizeClassMap = {
    sm: 'max-w-md',
    md: 'max-w-lg',
    lg: 'max-w-2xl',
    xl: 'max-w-4xl',
    full: 'max-w-6xl',
};

const sizeClass = sizeClassMap[props.size] ?? sizeClassMap.md;
</script>
