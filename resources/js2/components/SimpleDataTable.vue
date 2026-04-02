<template>
    <div>
        <div v-if="!items || items.length === 0" class="text-sm text-muted">No data.</div>
        <div v-else class="overflow-hidden rounded-2xl border border-border">
            <table class="w-full border-collapse text-sm">
                <thead class="bg-panel text-xs text-muted">
                    <tr>
                        <th class="px-3 py-2 text-left w-12">No</th>
                        <th v-for="column in columns" :key="column.key" class="px-3 py-2 text-left">
                            {{ column.label }}
                        </th>
                        <th v-if="withAttachment" class="px-3 py-2 text-left w-28">Lampiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-for="(item, index) in items" :key="item.id || index">
                        <td class="px-3 py-2 text-muted">{{ index + 1 }}</td>
                        <td v-for="column in columns" :key="column.key" class="px-3 py-2">
                            {{ resolveValue(item, column.key) }}
                        </td>
                        <td v-if="withAttachment" class="px-3 py-2">
                            <a
                                v-if="item && item.file_url"
                                class="text-primary underline"
                                :href="item.file_url"
                                target="_blank"
                                rel="noreferrer"
                            >
                                Download
                            </a>
                            <span v-else>-</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        columns: {
            type: Array,
            default: () => [],
        },
        items: {
            type: Array,
            default: () => [],
        },
        withAttachment: {
            type: Boolean,
            default: true,
        },
    },
    methods: {
        resolveValue(item, key) {
            if (!item || !key) {
                return '-';
            }
            const value = item[key];
            if (value === null || value === undefined || value === '') {
                return '-';
            }
            return value;
        },
    },
};
</script>

