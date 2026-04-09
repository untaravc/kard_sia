<template>
    <div class="rounded-2xl border border-border bg-white">
        <div class="flex flex-wrap items-center gap-2 border-b border-border px-3 py-2">
            <button
                class="rounded-lg border border-border px-2 py-1 text-xs"
                type="button"
                :disabled="!editor"
                aria-label="Bold"
                @click="toggleBold"
            >
                <Icon icon="mdi:format-bold" class="h-4 w-4" />
            </button>
            <button
                class="rounded-lg border border-border px-2 py-1 text-xs"
                type="button"
                :disabled="!editor"
                aria-label="Italic"
                @click="toggleItalic"
            >
                <Icon icon="mdi:format-italic" class="h-4 w-4" />
            </button>
            <button
                class="rounded-lg border border-border px-2 py-1 text-xs"
                type="button"
                :disabled="!editor"
                aria-label="Underline"
                @click="toggleUnderline"
            >
                <Icon icon="mdi:format-underline" class="h-4 w-4" />
            </button>
            <button
                class="rounded-lg border border-border px-2 py-1 text-xs"
                type="button"
                :disabled="!editor"
                aria-label="Bullet list"
                @click="toggleBulletList"
            >
                <Icon icon="mdi:format-list-bulleted" class="h-4 w-4" />
            </button>
            <button
                class="rounded-lg border border-border px-2 py-1 text-xs"
                type="button"
                :disabled="!editor"
                aria-label="Ordered list"
                @click="toggleOrderedList"
            >
                <Icon icon="mdi:format-list-numbered" class="h-4 w-4" />
            </button>
            <button
                class="rounded-lg border border-border px-2 py-1 text-xs"
                type="button"
                :disabled="!editor"
                aria-label="Blockquote"
                @click="toggleBlockquote"
            >
                <Icon icon="mdi:format-quote-close" class="h-4 w-4" />
            </button>
            <button
                class="rounded-lg border border-border px-2 py-1 text-xs"
                type="button"
                :disabled="!editor"
                aria-label="Link"
                @click="setLink"
            >
                <Icon icon="mdi:link-variant" class="h-4 w-4" />
            </button>
            <button
                class="rounded-lg border border-border px-2 py-1 text-xs"
                type="button"
                :disabled="!editor"
                aria-label="Indent"
                @click="indent"
            >
                <Icon icon="mdi:format-indent-increase" class="h-4 w-4" />
            </button>
            <button
                class="rounded-lg border border-border px-2 py-1 text-xs"
                type="button"
                :disabled="!editor"
                aria-label="Outdent"
                @click="outdent"
            >
                <Icon icon="mdi:format-indent-decrease" class="h-4 w-4" />
            </button>
            <div class="mx-1 h-5 w-px bg-border"></div>
            <label class="flex items-center gap-1 text-xs text-muted">
                <span class="sr-only">Table rows</span>
                <input
                    v-model.number="tableRows"
                    type="number"
                    min="1"
                    max="20"
                    class="w-14 rounded-lg border border-border bg-white px-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-primary/30"
                    aria-label="Table rows"
                />
                <span class="text-muted">x</span>
                <span class="sr-only">Table columns</span>
                <input
                    v-model.number="tableCols"
                    type="number"
                    min="1"
                    max="20"
                    class="w-14 rounded-lg border border-border bg-white px-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-primary/30"
                    aria-label="Table columns"
                />
            </label>
            <button
                class="rounded-lg border border-border px-2 py-1 text-xs"
                type="button"
                :disabled="!editor"
                aria-label="Insert table"
                @click="insertTable"
            >
                <Icon icon="mdi:table" class="h-4 w-4" />
            </button>
        </div>

        <div class="px-3 py-2">
            <EditorContent v-if="editor" :editor="editor" class="prose max-w-none" />
        </div>
    </div>
</template>

<script>
import { Extension, mergeAttributes } from '@tiptap/core';
import { Editor, EditorContent } from '@tiptap/vue-2';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import Table from '@tiptap/extension-table';
import TableRow from '@tiptap/extension-table-row';
import TableHeader from '@tiptap/extension-table-header';
import TableCell from '@tiptap/extension-table-cell';
import { Icon } from '../icons';

const IndentExtension = Extension.create({
    name: 'indent',
    addGlobalAttributes() {
        return [
            {
                types: ['paragraph', 'heading'],
                attributes: {
                    indent: {
                        default: 0,
                        parseHTML: (element) => {
                            if (!element || !element.classList) {
                                return 0;
                            }
                            const matched = Array.from(element.classList).find((className) =>
                                /^ql-indent-\d+$/.test(className)
                            );
                            if (!matched) {
                                return 0;
                            }
                            const value = Number(matched.replace('ql-indent-', ''));
                            return Number.isFinite(value) ? value : 0;
                        },
                        renderHTML: (attributes) => {
                            const indent = Number(attributes.indent || 0);
                            if (!indent) {
                                return {};
                            }
                            const capped = Math.max(0, Math.min(8, indent));
                            return mergeAttributes({ class: `ql-indent-${capped}` });
                        },
                    },
                },
            },
        ];
    },
    addCommands() {
        const updateIndent = (delta) => ({ tr, state, dispatch }) => {
            const { from, to } = state.selection;
            let changed = false;

            state.doc.nodesBetween(from, to, (node, pos) => {
                if (!node || !node.type) {
                    return;
                }
                const typeName = node.type.name;
                if (typeName !== 'paragraph' && typeName !== 'heading') {
                    return;
                }

                const current = Number(node.attrs.indent || 0);
                const next = Math.max(0, Math.min(8, current + delta));
                if (next === current) {
                    return;
                }

                tr.setNodeMarkup(pos, undefined, {
                    ...node.attrs,
                    indent: next,
                });
                changed = true;
            });

            if (changed && dispatch) {
                dispatch(tr);
            }
            return changed;
        };

        return {
            indent: () => updateIndent(1),
            outdent: () => updateIndent(-1),
        };
    },
});

export default {
    name: 'RichTextEditor',
    components: {
        EditorContent,
        Icon,
    },
    props: {
        value: {
            type: String,
            default: '',
        },
        placeholder: {
            type: String,
            default: '',
        },
    },
    data() {
        return {
            editor: null,
            tableRows: 3,
            tableCols: 3,
        };
    },
    watch: {
        value(next) {
            if (!this.editor) {
                return;
            }
            const current = this.editor.getHTML();
            if ((next || '') !== (current || '')) {
                this.editor.commands.setContent(next || '', false);
            }
        },
    },
    mounted() {
        this.editor = new Editor({
            content: this.value || '',
            extensions: [
                StarterKit,
                Underline,
                Link.configure({
                    openOnClick: false,
                }),
                Placeholder.configure({
                    placeholder: this.placeholder || '',
                }),
                IndentExtension,
                Table.configure({
                    resizable: true,
                }),
                TableRow,
                TableHeader,
                TableCell,
            ],
            onUpdate: ({ editor }) => {
                this.$emit('input', editor.getHTML());
            },
        });
    },
    beforeDestroy() {
        if (this.editor) {
            this.editor.destroy();
            this.editor = null;
        }
    },
    methods: {
        toggleBold() {
            this.editor?.chain().focus().toggleBold().run();
        },
        toggleItalic() {
            this.editor?.chain().focus().toggleItalic().run();
        },
        toggleUnderline() {
            this.editor?.chain().focus().toggleUnderline().run();
        },
        toggleBulletList() {
            this.editor?.chain().focus().toggleBulletList().run();
        },
        toggleOrderedList() {
            this.editor?.chain().focus().toggleOrderedList().run();
        },
        toggleBlockquote() {
            this.editor?.chain().focus().toggleBlockquote().run();
        },
        setLink() {
            if (!this.editor) {
                return;
            }
            const previousUrl = this.editor.getAttributes('link').href || '';
            const url = window.prompt('URL', previousUrl);
            if (url === null) {
                return;
            }
            if (!url) {
                this.editor.chain().focus().extendMarkRange('link').unsetLink().run();
                return;
            }
            this.editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
        },
        insertTable() {
            const rows = Number(this.tableRows);
            const cols = Number(this.tableCols);
            const safeRows = Number.isFinite(rows) ? Math.max(1, Math.min(20, rows)) : 3;
            const safeCols = Number.isFinite(cols) ? Math.max(1, Math.min(20, cols)) : 3;

            this.tableRows = safeRows;
            this.tableCols = safeCols;

            this.editor?.chain().focus().insertTable({ rows: safeRows, cols: safeCols, withHeaderRow: false }).run();
        },
        indent() {
            this.editor?.chain().focus().indent().run();
        },
        outdent() {
            this.editor?.chain().focus().outdent().run();
        },
    },
};
</script>

<style scoped>
.prose :deep(.ProseMirror) {
    min-height: 140px;
    outline: none;
    font-size: 14px;
    line-height: 1.6;
}

.prose :deep(.ProseMirror ul) {
    list-style-type: disc;
    padding-left: 40px;
}

.prose :deep(.ProseMirror ol) {
    list-style-type: decimal;
    padding-left: 40px;
}

.prose :deep(.ProseMirror li > p) {
    margin: 0;
}

.prose :deep(.ProseMirror table) {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}
.prose :deep(.ProseMirror th),
.prose :deep(.ProseMirror td) {
    border: 1px solid #e5e7eb;
    padding: 6px 8px;
    vertical-align: top;
}

.prose :deep(.ProseMirror .ql-indent-1) { padding-left: 3em; }
.prose :deep(.ProseMirror .ql-indent-2) { padding-left: 6em; }
.prose :deep(.ProseMirror .ql-indent-3) { padding-left: 9em; }
.prose :deep(.ProseMirror .ql-indent-4) { padding-left: 12em; }
.prose :deep(.ProseMirror .ql-indent-5) { padding-left: 15em; }
.prose :deep(.ProseMirror .ql-indent-6) { padding-left: 18em; }
.prose :deep(.ProseMirror .ql-indent-7) { padding-left: 21em; }
.prose :deep(.ProseMirror .ql-indent-8) { padding-left: 24em; }
</style>
