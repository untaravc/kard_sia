<template>
    <div class="rounded-2xl border border-border bg-panel shadow-sm">
        <div class="h-20 rounded-t-2xl bg-gradient-to-br from-cyan-500 to-emerald-400"></div>
        <div class="px-5 pb-5">
            <div class="-mt-8 flex items-center gap-3">
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
                <div class="flex-1">
                    <div class="text-base font-semibold text-ink">{{ user.name }}</div>
                    <div class="text-xs text-muted">{{ user.email }}</div>
                </div>
                <button
                    class="rounded-xl border border-border px-3 py-1.5 text-xs font-semibold text-ink"
                    type="button"
                >
                    Edit Profil
                </button>
            </div>

            <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                <div class="rounded-xl bg-slate-50 px-3 py-2">
                    <div class="text-lg font-semibold text-primary">{{ stats.avg_point }}</div>
                    <div class="text-[11px] text-muted">Nilai Rata-rata</div>
                </div>
                <div class="rounded-xl bg-slate-50 px-3 py-2">
                    <div class="text-lg font-semibold text-sky-600">{{ stats.stases }}</div>
                    <div class="text-[11px] text-muted">Stase</div>
                </div>
                <div class="rounded-xl bg-slate-50 px-3 py-2">
                    <div class="text-lg font-semibold text-emerald-600">{{ stats.presences }}</div>
                    <div class="text-[11px] text-muted">Presensi</div>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                <button class="rounded-xl bg-slate-100 px-2 py-3 text-xs font-semibold text-slate-600" type="button">
                    <i class="fa fa-sync text-slate-500"></i>
                    <div class="mt-1">Ganti Stase</div>
                </button>
                <button class="rounded-xl bg-slate-100 px-2 py-3 text-xs font-semibold text-emerald-600" type="button">
                    <i class="fa fa-plus text-emerald-500"></i>
                    <div class="mt-1">Tambah Stase</div>
                </button>
                <button class="rounded-xl bg-slate-100 px-2 py-3 text-xs font-semibold text-amber-600" type="button">
                    <i class="fa fa-book-medical text-amber-500"></i>
                    <div class="mt-1">Log Book</div>
                </button>
            </div>

            <div class="mt-4 rounded-2xl bg-sky-50 p-4">
                <div class="flex items-start gap-3">
                    <div class="grid h-9 w-9 place-items-center rounded-lg bg-sky-100 text-sky-600">
                        <i class="fa fa-calendar-check"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-semibold text-ink">Presensi Hari Ini</div>
                        <div class="text-xs text-muted" v-if="todayPresence.checkin">
                            Masuk pada {{ todayPresence.checkin }}
                            <span v-if="todayPresence.checkout">- {{ todayPresence.checkout }}</span>
                        </div>
                        <div class="text-xs text-muted" v-else>Belum ada presensi hari ini.</div>
                    </div>
                </div>
                <button class="mt-3 w-full rounded-xl bg-primary px-3 py-2 text-xs font-semibold text-white" type="button">
                    Presensi Masuk
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        user: {
            type: Object,
            required: true,
        },
        stats: {
            type: Object,
            required: true,
        },
        todayPresence: {
            type: Object,
            required: true,
        },
    },
};
</script>
