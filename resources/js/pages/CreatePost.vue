<script setup lang="ts">
/**
 * RichTextEditor.vue
 *
 * Edytor treści posta na blogu oparty o TipTap (headless, MIT, darmowy).
 * Wspiera: pogrubienie/kursywę/podkreślenie/przekreślenie, nagłówki H2-H4,
 * listy, cytaty, blok kodu, linki oraz wgrywanie obrazków (klik, drag&drop, paste).
 *
 * Stylistyka korzysta z tych samych semantycznych tokenów (bg-background,
 * text-foreground, border-input, bg-accent, itd.) co reszta panelu admina,
 * więc dark/light mode działa automatycznie razem z resztą UI.
 *
 * Użycie:
 *   <RichTextEditor v-model="form.content" />
 */
import {
    Bold as BoldIcon,
    Code as CodeIcon,
    Heading2,
    Heading3,
    ImagePlus,
    Italic as ItalicIcon,
    Link2,
    Link2Off,
    List,
    ListOrdered,
    Minus,
    Quote,
    Redo2,
    Strikethrough,
    Underline as UnderlineIcon,
    Undo2,
} from '@lucide/vue';
import CharacterCount from '@tiptap/extension-character-count';
import Image from '@tiptap/extension-image';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import Underline from '@tiptap/extension-underline';
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { onBeforeUnmount, ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue: string;
        placeholder?: string;
        uploadUrl?: string;
        maxSizeMb?: number;
    }>(),
    {
        placeholder: 'Zacznij pisać treść posta…',
        uploadUrl: '/dashboard/posts/upload-image',
        maxSizeMb: 5,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const isUploading = ref(false);
const uploadError = ref<string | null>(null);
const showLinkInput = ref(false);
const linkUrl = ref('');

const toolbarBtnClass =
    'inline-flex h-9 w-9 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground disabled:pointer-events-none disabled:opacity-40';

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit.configure({
            heading: { levels: [2, 3, 4] },
        }),
        Underline,
        Link.configure({
            openOnClick: false,
            autolink: true,
            HTMLAttributes: { rel: 'noopener noreferrer nofollow' },
        }),
        Image.configure({
            HTMLAttributes: { class: 'rounded-lg border border-border' },
        }),
        Placeholder.configure({ placeholder: props.placeholder }),
        CharacterCount,
    ],
    editorProps: {
        attributes: {
            class: 'tiptap-content min-h-[280px] px-4 py-3 focus:outline-none',
        },
        handleDrop: (_view, event, _slice, moved) => {
            if (!moved && event.dataTransfer?.files?.length) {
                const file = event.dataTransfer.files[0];

                if (file.type.startsWith('image/')) {
                    event.preventDefault();
                    uploadAndInsertImage(file);

                    return true;
                }
            }

            return false;
        },
        handlePaste: (_view, event) => {
            const item = Array.from(event.clipboardData?.items ?? []).find((i) =>
                i.type.startsWith('image/'),
            );

            if (item) {
                const file = item.getAsFile();

                if (file) {
                    event.preventDefault();
                    uploadAndInsertImage(file);

                    return true;
                }
            }

            return false;
        },
    },
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML());
    },
});

// synchronizacja z zewnątrz (np. reset formularza po zapisie / edycja innego posta)
watch(
    () => props.modelValue,
    (value) => {
        if (editor.value && value !== editor.value.getHTML()) {
            editor.value.commands.setContent(value, { emitUpdate: false });
        }
    },
);

onBeforeUnmount(() => {
    editor.value?.destroy();
});

function getCsrfToken(): string {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
}

async function uploadAndInsertImage(file: File) {
    if (!file.type.startsWith('image/')) {
        uploadError.value = 'Wybierz plik graficzny.';

        return;
    }

    if (file.size > props.maxSizeMb * 1024 * 1024) {
        uploadError.value = `Plik jest za duży (max ${props.maxSizeMb} MB).`;

        return;
    }

    uploadError.value = null;
    isUploading.value = true;

    try {
        const formData = new FormData();
        formData.append('image', file);

        const response = await fetch(props.uploadUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-XSRF-TOKEN': getCsrfToken(),
                Accept: 'application/json',
            },
            body: formData,
        });

        if (!response.ok) {
            throw new Error('Upload nie powiódł się.');
        }

        const data = await response.json();
        editor.value?.chain().focus().setImage({ src: data.url, alt: file.name }).run();
    } catch (error) {
        uploadError.value = 'Nie udało się wgrać obrazka. Spróbuj ponownie.';
        console.error(error);
    } finally {
        isUploading.value = false;
    }
}

function handleFileSelect(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        uploadAndInsertImage(file);
    }

    target.value = '';
}

function applyLink() {
    if (!editor.value) {
    return;
}

    const url = linkUrl.value.trim();

    if (url) {
        editor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
    }

    showLinkInput.value = false;
    linkUrl.value = '';
}

function toggleLinkInput() {
    if (editor.value?.isActive('link')) {
        editor.value.chain().focus().unsetLink().run();

        return;
    }

    linkUrl.value = editor.value?.getAttributes('link').href ?? '';
    showLinkInput.value = !showLinkInput.value;
}
</script>

<template>
    <div
        class="overflow-hidden rounded-xl border border-input bg-background transition-shadow focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2 focus-within:ring-offset-background"
    >
        <!-- Toolbar -->
        <div
            v-if="editor"
            class="flex flex-wrap items-center gap-1 border-b border-input bg-muted/40 p-2 dark:bg-muted/10"
        >
            <button type="button" title="Cofnij" :disabled="!editor.can().undo()" :class="toolbarBtnClass" @click="editor.chain().focus().undo().run()">
                <Undo2 class="size-4" />
            </button>
            <button type="button" title="Ponów" :disabled="!editor.can().redo()" :class="toolbarBtnClass" @click="editor.chain().focus().redo().run()">
                <Redo2 class="size-4" />
            </button>

            <span class="mx-1 h-5 w-px bg-border" />

            <button type="button" title="Pogrubienie" :class="[toolbarBtnClass, editor.isActive('bold') && 'bg-accent text-accent-foreground']" @click="editor.chain().focus().toggleBold().run()">
                <BoldIcon class="size-4" />
            </button>
            <button type="button" title="Kursywa" :class="[toolbarBtnClass, editor.isActive('italic') && 'bg-accent text-accent-foreground']" @click="editor.chain().focus().toggleItalic().run()">
                <ItalicIcon class="size-4" />
            </button>
            <button type="button" title="Podkreślenie" :class="[toolbarBtnClass, editor.isActive('underline') && 'bg-accent text-accent-foreground']" @click="editor.chain().focus().toggleUnderline().run()">
                <UnderlineIcon class="size-4" />
            </button>
            <button type="button" title="Przekreślenie" :class="[toolbarBtnClass, editor.isActive('strike') && 'bg-accent text-accent-foreground']" @click="editor.chain().focus().toggleStrike().run()">
                <Strikethrough class="size-4" />
            </button>

            <span class="mx-1 h-5 w-px bg-border" />

            <button type="button" title="Nagłówek 2" :class="[toolbarBtnClass, editor.isActive('heading', { level: 2 }) && 'bg-accent text-accent-foreground']" @click="editor.chain().focus().toggleHeading({ level: 2 }).run()">
                <Heading2 class="size-4" />
            </button>
            <button type="button" title="Nagłówek 3" :class="[toolbarBtnClass, editor.isActive('heading', { level: 3 }) && 'bg-accent text-accent-foreground']" @click="editor.chain().focus().toggleHeading({ level: 3 }).run()">
                <Heading3 class="size-4" />
            </button>

            <span class="mx-1 h-5 w-px bg-border" />

            <button type="button" title="Lista punktowana" :class="[toolbarBtnClass, editor.isActive('bulletList') && 'bg-accent text-accent-foreground']" @click="editor.chain().focus().toggleBulletList().run()">
                <List class="size-4" />
            </button>
            <button type="button" title="Lista numerowana" :class="[toolbarBtnClass, editor.isActive('orderedList') && 'bg-accent text-accent-foreground']" @click="editor.chain().focus().toggleOrderedList().run()">
                <ListOrdered class="size-4" />
            </button>
            <button type="button" title="Cytat" :class="[toolbarBtnClass, editor.isActive('blockquote') && 'bg-accent text-accent-foreground']" @click="editor.chain().focus().toggleBlockquote().run()">
                <Quote class="size-4" />
            </button>
            <button type="button" title="Blok kodu" :class="[toolbarBtnClass, editor.isActive('codeBlock') && 'bg-accent text-accent-foreground']" @click="editor.chain().focus().toggleCodeBlock().run()">
                <CodeIcon class="size-4" />
            </button>
            <button type="button" title="Linia pozioma" :class="toolbarBtnClass" @click="editor.chain().focus().setHorizontalRule().run()">
                <Minus class="size-4" />
            </button>

            <span class="mx-1 h-5 w-px bg-border" />

            <button type="button" :title="editor.isActive('link') ? 'Usuń link' : 'Wstaw link'" :class="[toolbarBtnClass, editor.isActive('link') && 'bg-accent text-accent-foreground']" @click="toggleLinkInput">
                <component :is="editor.isActive('link') ? Link2Off : Link2" class="size-4" />
            </button>
            <button type="button" title="Wstaw obrazek" :disabled="isUploading" :class="toolbarBtnClass" @click="fileInput?.click()">
                <ImagePlus class="size-4" />
            </button>
            <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="handleFileSelect" />
        </div>

        <!-- Wiersz wklejania linku -->
        <div v-if="showLinkInput" class="flex items-center gap-2 border-b border-input bg-muted/20 p-2 dark:bg-muted/10">
            <input
                v-model="linkUrl"
                type="url"
                placeholder="https://…"
                class="h-8 flex-1 rounded-md border border-input bg-background px-2 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring"
                @keydown.enter.prevent="applyLink"
                @keydown.escape="showLinkInput = false"
            />
            <button type="button" class="text-sm font-medium text-primary" @click="applyLink">Dodaj</button>
            <button type="button" class="text-sm text-muted-foreground" @click="showLinkInput = false">Anuluj</button>
        </div>

        <p v-if="uploadError" class="border-b border-destructive/30 bg-destructive/10 px-4 py-2 text-sm text-destructive">
            {{ uploadError }}
        </p>
        <p v-if="isUploading" class="border-b border-input bg-muted/20 px-4 py-2 text-sm text-muted-foreground dark:bg-muted/10">
            Wgrywanie obrazka…
        </p>

        <!-- Treść edytora -->
        <EditorContent :editor="editor" />

        <!-- Licznik znaków (spójny z Waszym wzorcem SEO counterów) -->
        <div v-if="editor" class="flex items-center justify-end border-t border-input bg-muted/20 px-4 py-1.5 text-xs text-muted-foreground dark:bg-muted/10">
            {{ editor.storage.characterCount.characters() }} znaków · {{ editor.storage.characterCount.words() }} słów
        </div>
    </div>
</template>

<style scoped>
:deep(.tiptap-content) {
    color: var(--foreground);
    font-size: 0.9375rem;
    line-height: 1.7;
}
:deep(.tiptap-content p) {
    margin: 0.75em 0;
}
:deep(.tiptap-content h2) {
    margin: 1.4em 0 0.5em;
    font-size: 1.375rem;
    font-weight: 700;
}
:deep(.tiptap-content h3) {
    margin: 1.2em 0 0.5em;
    font-size: 1.15rem;
    font-weight: 600;
}
:deep(.tiptap-content h4) {
    margin: 1em 0 0.4em;
    font-size: 1rem;
    font-weight: 600;
}
:deep(.tiptap-content ul),
:deep(.tiptap-content ol) {
    margin: 0.75em 0;
    padding-left: 1.5em;
}
:deep(.tiptap-content ul) {
    list-style: disc;
}
:deep(.tiptap-content ol) {
    list-style: decimal;
}
:deep(.tiptap-content li) {
    margin: 0.25em 0;
}
:deep(.tiptap-content blockquote) {
    margin: 1em 0;
    border-left: 3px solid var(--border);
    padding-left: 1em;
    color: var(--muted-foreground);
    font-style: italic;
}
:deep(.tiptap-content code) {
    border-radius: 0.25rem;
    background-color: var(--muted);
    padding: 0.15em 0.4em;
    font-size: 0.85em;
}
:deep(.tiptap-content pre) {
    margin: 1em 0;
    overflow-x: auto;
    border-radius: 0.5rem;
    background-color: var(--muted);
    padding: 0.75em 1em;
}
:deep(.tiptap-content pre code) {
    background: none;
    padding: 0;
}
:deep(.tiptap-content a) {
    color: var(--primary);
    text-decoration: underline;
    text-underline-offset: 2px;
}
:deep(.tiptap-content img) {
    margin: 1em 0;
    max-width: 100%;
    height: auto;
}
:deep(.tiptap-content hr) {
    margin: 1.5em 0;
    border: none;
    border-top: 1px solid var(--border);
}
:deep(.tiptap-content p.is-editor-empty:first-child::before) {
    float: left;
    height: 0;
    color: var(--muted-foreground);
    content: attr(data-placeholder);
    pointer-events: none;
}
</style>