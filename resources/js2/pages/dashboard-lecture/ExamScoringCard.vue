<template>
    <div class="relative rounded-2xl border border-border bg-panel">
        <Loading :active="loading" :is-full-page="false" />
        <div class="border-b border-border px-5 py-4">
            <div class="text-sm font-semibold text-ink">
                Penilaian Ujian Umum
                <span class="text-xs font-normal text-muted">(Data muncul di semua dashboard dosen)</span>
            </div>
        </div>
        <div class="divide-y divide-border">
            <div
                v-for="(item, index) in items"
                :key="index"
                class="grid gap-3 px-5 py-4 text-sm lg:grid-cols-[40px_1fr_1fr_140px]"
            >
                <div class="text-xs font-semibold text-muted">{{ index + 1 }}</div>
                <div>
                    <div class="font-semibold text-ink" v-if="item.student">{{ item.student.name }}</div>
                    <div class="text-xs text-muted">
                        <span v-if="item.data">Nilai: </span>
                        <span v-if="item.data" class="rounded-full bg-emerald-100 px-2 py-0.5 text-emerald-700">
                            {{ item.data.point_average }}
                        </span>
                    </div>
                    <div class="text-xs text-muted" v-if="item.stase_task">
                        <span v-if="item.stase_task.stase" class="italic">#{{ item.stase_task.stase.name }}</span>
                        <span v-if="item.stase_task.name"> • {{ item.stase_task.name }}</span>
                        <span v-if="item.title"> • {{ item.title }}</span>
                    </div>
                </div>
                <div class="text-xs text-muted">
                    <ol v-if="item.files && item.files.length" class="list-decimal pl-4">
                        <li v-for="(file, fileIndex) in item.files" :key="fileIndex">
                            <button
                                class="text-primary underline"
                                type="button"
                                @click="$emit('open-file', file)"
                            >
                                lampiran
                            </button>
                        </li>
                    </ol>
                    <span v-else>no files</span>
                </div>
                <div class="flex items-center gap-2">
                    <router-link
                        v-if="showScoring(item) && !item.data"
                        :to="`/dosen/task/scoring/${item.id}`"
                        class="rounded-lg bg-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600"
                    >
                        Nilai
                    </router-link>
                    <router-link
                        v-if="showScoring(item) && item.data"
                        :to="`/dosen/task/scoring/${item.id}`"
                        class="rounded-lg bg-sky-500 px-3 py-1.5 text-xs font-semibold text-white"
                    >
                        Perbarui
                    </router-link>

                    <router-link
                        v-if="isTesis(item) && !item.data"
                        :to="`/dosen/task/nilai-tesis/${item.id}`"
                        class="rounded-lg bg-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600"
                    >
                        Nilai
                    </router-link>
                    <router-link
                        v-if="isTesis(item) && item.data"
                        :to="`/dosen/task/nilai-tesis/${item.id}`"
                        class="rounded-lg bg-sky-500 px-3 py-1.5 text-xs font-semibold text-white"
                    >
                        Perbarui
                    </router-link>

                    <router-link
                        v-if="isProposal(item) && !item.data"
                        :to="`/dosen/task/nilai-proposal/${item.id}`"
                        class="rounded-lg bg-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600"
                    >
                        Nilai
                    </router-link>
                    <router-link
                        v-if="isProposal(item) && item.data"
                        :to="`/dosen/task/nilai-proposal/${item.id}`"
                        class="rounded-lg bg-sky-500 px-3 py-1.5 text-xs font-semibold text-white"
                    >
                        Perbarui
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
    components: {
        Loading,
    },
    props: {
        items: {
            type: Array,
            default: () => [],
        },
        loading: {
            type: Boolean,
            default: false,
        },
    },
    methods: {
        taskDesc(item) {
            if (!item || !item.stase_task || !item.stase_task.task) {
                return null;
            }
            return item.stase_task.task.desc;
        },
        showScoring(item) {
            const desc = this.taskDesc(item);
            return desc !== 'nilai-tesis' && desc !== 'nilai-proposal';
        },
        isTesis(item) {
            return this.taskDesc(item) === 'nilai-tesis';
        },
        isProposal(item) {
            return this.taskDesc(item) === 'nilai-proposal';
        },
    },
};
</script>
