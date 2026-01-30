<template>
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
            <div class="flex w-full flex-col gap-6 lg:max-w-[320px] lg:flex-none">
                <div class="rounded-2xl border border-border bg-panel p-4 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="grid h-9 w-9 place-items-center rounded-lg bg-sky-100 text-sky-600">
                            <i class="fa fa-calendar-check"></i>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-semibold text-ink">Today's Presence</div>
                            <div class="text-xs text-muted" v-if="todayPresence.checkin">
                                Checked in at {{ todayPresence.checkin }}
                                <span v-if="todayPresence.checkout">- {{ todayPresence.checkout }}</span>
                            </div>
                            <div class="text-xs text-muted" v-else>No presence recorded today.</div>
                        </div>
                    </div>
                    <button class="mt-3 w-full rounded-xl bg-primary px-3 py-2 text-xs font-semibold text-white" type="button">
                        Check In
                    </button>
                </div>
                <QuickLinkCard
                    title="Curriculum & SOP"
                    subtitle="Access official documents"
                    link-url="https://drive.google.com/drive/folders/1LoCN_taJgB37eHhdc9HY1IplL8N5jjBV?usp=sharing"
                />
            </div>

            <div class="flex min-w-0 flex-1 flex-col gap-6">
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
    data() {
        return {
            todayPresence: {
                checkin: '08:12',
                checkout: '16:45',
            },
        };
    },
};
</script>
