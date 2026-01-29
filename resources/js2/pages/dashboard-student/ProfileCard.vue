<template>
    <div class="rounded-2xl border border-border bg-panel shadow-sm">
        <div class="relative h-24 rounded-t-2xl bg-gradient-to-br from-cyan-500 to-emerald-400">
            <div class="absolute left-5 top-12">
                <div
                    class="h-16 w-16 rounded-full border-2 border-white bg-cover bg-center"
                    v-if="user.image"
                    :style="'background-image: url(' + user.image + ')'"
                ></div>
                <div
                    class="h-16 w-16 rounded-full border-2 border-white bg-cover bg-center"
                    v-else
                    :style="'background-image: url(/assets/images/dr_default.jpeg)'"
                ></div>
            </div>
        </div>
        <div class="px-5 pb-5 pt-10">
            <div class="min-w-0">
                <div class="text-base font-semibold text-ink break-words">{{ user.name }}</div>
                <div class="text-xs text-muted break-words">{{ user.email }}</div>
            </div>

            <div class="mt-4 rounded-2xl bg-slate-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Profile Details</div>
                <div class="mt-3 grid gap-2 text-xs text-muted">
                    <div class="flex items-center justify-between gap-3">
                        <span>Email</span>
                        <span class="font-medium text-ink">{{ user.email || '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span>Phone</span>
                        <span class="font-medium text-ink">{{ user.phone || '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span>Address</span>
                        <span class="font-medium text-ink text-right">{{ user.address || '-' }}</span>
                    </div>
                </div>
            </div>
            <button
                class="mt-4 w-full rounded-xl border border-border px-3 py-2 text-xs font-semibold text-ink"
                type="button"
                @click="$emit('edit-profile')"
            >
                Edit Profile
            </button>
        </div>
    </div>
</template>

<script>
export default {
    emits: ['edit-profile'],
    props: {
        user: {
            type: Object,
            required: true,
        },
    },
};
</script>
