<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
        :class="open ? 'pointer-events-auto opacity-100' : 'pointer-events-none opacity-0'"
    >
        <div class="absolute inset-0 bg-slate-900/40" @click="handleBackdropClick"></div>
        <div
            class="relative flex max-h-[85vh] w-full flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-xl"
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
            <div class="mt-5 flex-1 overflow-y-auto">
                <slot />
            </div>
            <div v-if="$slots.footer" class="mt-6 flex items-center justify-end gap-2">
                <slot name="footer" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { onBeforeUnmount, watch } from 'vue';
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
    closeOnBackdrop: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close']);

const sizeClassMap = {
    sm: 'max-w-md',
    md: 'max-w-lg',
    lg: 'max-w-2xl',
    xl: 'max-w-4xl',
    xxl: 'max-w-5xl',
    xxxl: 'max-w-7xl',
    full: 'max-w-6xl',
};

const sizeClass = sizeClassMap[props.size] ?? sizeClassMap.md;

const handleBackdropClick = () => {
    if (props.closeOnBackdrop) {
        emit('close');
    }
};

let previousOverflow = '';

const setBodyScrollLock = (locked) => {
    if (typeof document === 'undefined') {
        return;
    }
    if (locked) {
        previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = previousOverflow || '';
        previousOverflow = '';
    }
};

watch(
    () => props.open,
    (isOpen) => {
        setBodyScrollLock(isOpen);
    },
    { immediate: true }
);

onBeforeUnmount(() => {
    setBodyScrollLock(false);
});
</script>
