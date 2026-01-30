<template>
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
            <div class="flex w-full flex-col gap-6 lg:max-w-[360px] lg:flex-none">
                <QuickLinkCard
                    title="Kurikulum & Standar Operasional"
                    subtitle="Akses dokumen resmi"
                    link-url="https://drive.google.com/drive/folders/1LoCN_taJgB37eHhdc9HY1IplL8N5jjBV?usp=sharing"
                />
            </div>

        </div>
    </div>
</template>

<script>
import QuickLinkCard from './QuickLinkCard.vue';

export default {
    components: {
        QuickLinkCard,
    },
};
</script>
