<template>
    <div class="relative rounded-2xl border border-border bg-panel">
        <Loading :active="loading" :is-full-page="false" />
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-border px-5 py-4">
            <div class="text-sm font-semibold text-ink">Penilaian</div>
            <div class="flex flex-1 flex-wrap items-center justify-end gap-3">
                <input
                    :value="filterValue"
                    type="text"
                    placeholder="Filter name..."
                    class="w-full max-w-[220px] rounded-lg border border-border bg-white px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-primary/30"
                    @input="$emit('filter-change', $event.target.value)"
                />
                <router-link to="/blu/scores" class="text-xs text-primary underline">Riwayat penilaian</router-link>
            </div>
        </div>
        <div class="divide-y divide-border">
            <div
                v-for="(item, index) in items"
                :key="index"
                class="flex flex-wrap items-start justify-between gap-4 px-5 py-4 text-sm"
            >
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span
                            v-if="item.stase_task && item.stase_task.stase"
                            class="rounded-full bg-sky-100 px-2 py-0.5 font-semibold text-sky-600"
                        >
                            #{{ item.stase_task.stase.name }}
                        </span>
                    </div>
                    <div class="mt-1 font-semibold text-ink" v-if="item.student">
                        {{ item.student.name }}
                    </div>
                    <div class="text-xs text-muted">
                        <span v-if="item.stase_task">{{ item.stase_task.name }}</span>
                        <span v-if="item.title">: {{ item.title }}</span>
                    </div>
                    <div v-if="item.plan" class="mt-1 text-xs text-muted">
                        Plan: {{ item.plan }}
                    </div>
                    <div v-if="item.files && item.files.length" class="mt-2 flex flex-wrap gap-2">
                        <button
                            v-for="(file, fileIndex) in item.files"
                            :key="fileIndex"
                            class="rounded-full border border-border px-3 py-1 text-[11px] text-slate-600"
                            type="button"
                            @click="$emit('open-file', file)"
                        >
                            {{ truncate(file.title, 12) }}
                        </button>
                    </div>
                </div>
                <div class="flex min-w-[120px] flex-col items-end gap-2 text-right">
                    <div v-if="item.data" class="text-2xl font-semibold text-emerald-600">
                        {{ item.data.point_average }}
                    </div>
                    <router-link
                        v-if="showScoring(item) && !item.data"
                        :to="`/blu/task-scoring/${item.id}`"
                        class="rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white"
                    >
                        Nilai
                    </router-link>
                    <button
                        v-if="showScoring(item) && !item.data"
                        type="button"
                        class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                        @click="$emit('open-direct-score', item)"
                    >
                        Nilai Langsung
                    </button>
                    <router-link
                        v-if="showScoring(item) && item.data && !item.data.admin"
                        :to="`/blu/task-scoring/${item.id}`"
                        class="rounded-lg bg-sky-500 px-3 py-1.5 text-xs font-semibold text-white"
                    >
                        Perbarui
                    </router-link>
                    <button
                        v-if="showScoring(item) && item.data && item.data.admin"
                        type="button"
                        class="rounded-lg border border-border px-3 py-1.5 text-xs text-muted"
                        @click="$emit('open-direct-score', item)"
                    >
                        Perbarui Langsung
                    </button>

                    <router-link
                        v-if="isTesis(item) && !item.data"
                        :to="`/blu/task-scoring-thesis/${item.id}`"
                        class="rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white"
                    >
                        Nilai Thesis
                    </router-link>
                    <router-link
                        v-if="isTesis(item) && item.data"
                        :to="`/blu/task-scoring-thesis/${item.id}`"
                        class="rounded-lg bg-sky-500 px-3 py-1.5 text-xs font-semibold text-white"
                    >
                        Perbarui Nilai Thesis
                    </router-link>

                    <router-link
                        v-if="isProposal(item) && !item.data"
                        :to="`/blu/task-scoring-proposal/${item.id}`"
                        class="rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white"
                    >
                        Nilai Proposal
                    </router-link>
                    <router-link
                        v-if="isProposal(item) && item.data"
                        :to="`/blu/task-scoring-proposal/${item.id}`"
                        class="rounded-lg bg-sky-500 px-3 py-1.5 text-xs font-semibold text-white"
                    >
                        Perbarui Nilai Proposal
                    </router-link>
                </div>
            </div>
            <div v-if="!items.length" class="px-5 py-6 text-sm text-muted">
                Tidak ada jadwal penilaian ujian.
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
        filterValue: {
            type: String,
            default: '',
        },
    },
    methods: {
        truncate(text, length) {
            if (!text) {
                return '';
            }
            if (text.length <= length) {
                return text;
            }
            return `${text.slice(0, length)}...`;
        },
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
