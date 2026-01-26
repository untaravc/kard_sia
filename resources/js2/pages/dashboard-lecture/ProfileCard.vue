<template>
    <div class="rounded-2xl border border-border bg-panel shadow-sm">
        <div class="h-16 rounded-t-2xl bg-gradient-to-br from-indigo-500 via-sky-500 to-emerald-400"></div>
        <div class="px-5 pb-5">
            <div class="-mt-8 flex items-center gap-3">
                <div
                    class="h-16 w-16 rounded-xl border-2 border-white bg-cover bg-center"
                    :style="profileStyle"
                ></div>
                <div class="flex-1">
                    <div class="text-base font-semibold text-ink">{{ user.name }}</div>
                    <div class="text-xs text-muted">{{ user.email }}</div>
                </div>
            </div>

            <div class="mt-4 grid gap-2">
                <button
                    class="rounded-xl border border-border px-3 py-2 text-xs font-semibold text-ink"
                    type="button"
                    @click="$emit('edit-profile')"
                >
                    <i class="fa fa-user-edit text-slate-500"></i>
                    Perbarui Profil
                </button>
                <router-link
                    to="/dosen/ksm-schedules"
                    class="rounded-xl bg-primary px-3 py-2 text-center text-xs font-semibold text-white"
                >
                    <i class="fa fa-table"></i>
                    Jadwal Tindakan
                </router-link>
            </div>

            <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                <div class="rounded-xl bg-slate-50 px-3 py-2">
                    <div class="text-lg font-semibold text-primary">{{ infoCards.avg_scoring || 0 }}</div>
                    <div class="text-[11px] text-muted">Jumlah Menilai</div>
                </div>
                <div class="rounded-xl bg-slate-50 px-3 py-2">
                    <div class="text-lg font-semibold text-sky-600">{{ infoCards.scoring || 0 }}</div>
                    <div class="text-[11px] text-muted">Rata-rata Menilai</div>
                </div>
                <div class="rounded-xl bg-slate-50 px-3 py-2">
                    <div class="text-lg font-semibold text-emerald-600">{{ infoCards.act_lect || 0 }}</div>
                    <div class="text-[11px] text-muted">Presensi Agenda</div>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                <div class="rounded-xl bg-slate-100 px-2 py-3 text-xs font-semibold text-slate-600">
                    <div class="relative mx-auto h-10 w-10">
                        <i class="fa fa-calendar-check text-primary text-2xl"></i>
                        <span class="absolute -bottom-2 -right-2 rounded-full bg-slate-700 px-2 py-0.5 text-[10px] text-white">
                            {{ schedulesCount }}
                        </span>
                    </div>
                    <div class="mt-2">Agenda Hari Ini</div>
                </div>
                <div class="rounded-xl bg-slate-100 px-2 py-3 text-xs font-semibold text-slate-600">
                    <div class="relative mx-auto h-10 w-10">
                        <i class="fa fa-edit text-2xl text-slate-500"></i>
                        <span class="absolute -bottom-2 -right-2 rounded-full bg-rose-500 px-2 py-0.5 text-[10px] text-white">
                            {{ scorePending }}
                        </span>
                    </div>
                    <div class="mt-2">Penilaian</div>
                </div>
                <router-link
                    to="/dosen/resident-logs"
                    class="rounded-xl bg-slate-100 px-2 py-3 text-xs font-semibold text-slate-600"
                >
                    <div class="relative mx-auto h-10 w-10">
                        <i class="fa fa-book-medical text-2xl text-amber-500"></i>
                        <span class="absolute -bottom-2 -right-2 rounded-full bg-rose-500 px-2 py-0.5 text-[10px] text-white">
                            {{ infoCards.log_pending || 0 }}
                        </span>
                    </div>
                    <div class="mt-2">Logbook Approval</div>
                </router-link>
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
        infoCards: {
            type: Object,
            required: true,
        },
        schedulesCount: {
            type: Number,
            default: 0,
        },
        scorePending: {
            type: Number,
            default: 0,
        },
    },
    computed: {
        profileStyle() {
            if (this.user && this.user.image) {
                if (this.user.image.startsWith('http')) {
                    return `background-image: url(${this.user.image})`;
                }
                return `background-image: url(/storage/${this.user.image})`;
            }
            return 'background-image: url(/assets/images/dr_default.jpeg)';
        },
    },
};
</script>
