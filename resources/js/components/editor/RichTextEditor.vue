<script setup lang="ts">
import {
    AlignCenter,
    AlignLeft,
    AlignRight,
    Bold,
    Code,
    Heading2,
    Heading3,
    ImagePlus,
    Italic,
    Link2,
    Link2Off,
    List,
    ListOrdered,
    Minus,
    Quote,
    Redo2,
    Strikethrough,
    Trash2,
    Underline as UnderlineIcon,
    Undo2,
} from '@lucide/vue';
import type { Editor, JSONContent } from '@tiptap/core';
import CharacterCount from '@tiptap/extension-character-count';
import FileHandler from '@tiptap/extension-file-handler';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import Underline from '@tiptap/extension-underline';
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { onBeforeUnmount, reactive, ref, watch } from 'vue';
import { BlogImage } from '@/components/editor/BlogImage';

type ImageAlignment = 'left' | 'center' | 'right';

interface UploadedImage {
    id: string;
    url: string;
    width: number;
    height: number;
}

const props = withDefaults(
    defineProps<{
        modelValue: JSONContent;
        uploadUrl: string;
        allowedImageTypes: string[];
        maxSizeMb?: number;
        placeholder?: string;
    }>(),
    {
        maxSizeMb: 8,
        placeholder: 'Zacznij pisać treść artykułu…',
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: JSONContent];
    'uploading-change': [value: boolean];
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const activeUploads = ref(0);
const uploadError = ref<string | null>(null);
const showLinkInput = ref(false);
const linkUrl = ref('');
const selectedImage = reactive({
    visible: false,
    alt: '',
    align: 'center' as ImageAlignment,
});

const toolbarButton =
    'inline-flex size-9 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground disabled:pointer-events-none disabled:opacity-40';
const activeButton = 'bg-accent text-accent-foreground';

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
            defaultProtocol: 'https',
            protocols: ['http', 'https', 'mailto'],
            HTMLAttributes: {
                rel: 'noopener noreferrer nofollow',
                target: '_blank',
            },
        }),
        BlogImage.configure({
            allowBase64: false,
            resize: {
                enabled: true,
                directions: [
                    'top-left',
                    'top-right',
                    'bottom-left',
                    'bottom-right',
                ],
                minWidth: 120,
                minHeight: 68,
                alwaysPreserveAspectRatio: true,
            },
        }),
        FileHandler.configure({
            allowedMimeTypes: props.allowedImageTypes,
            consumePasteEvent: true,
            onDrop: (currentEditor, files, position) => {
                void uploadFiles(currentEditor, files, position);
            },
            onPaste: (currentEditor, files) => {
                void uploadFiles(currentEditor, files);
            },
        }),
        Placeholder.configure({ placeholder: props.placeholder }),
        CharacterCount,
    ],
    editorProps: {
        attributes: {
            class: 'tiptap-content min-h-[360px] px-5 py-4 focus:outline-none',
            'aria-label': 'Treść artykułu',
        },
        transformPastedHTML: (html) => {
            const document = new DOMParser().parseFromString(html, 'text/html');
            document.querySelectorAll('img').forEach((image) => image.remove());

            return document.body.innerHTML;
        },
    },
    onUpdate: ({ editor: currentEditor }) => {
        emit('update:modelValue', currentEditor.getJSON());
        syncSelectedImage(currentEditor);
    },
    onSelectionUpdate: ({ editor: currentEditor }) => {
        syncSelectedImage(currentEditor);
    },
});

watch(
    () => props.modelValue,
    (value) => {
        if (
            editor.value &&
            JSON.stringify(value) !== JSON.stringify(editor.value.getJSON())
        ) {
            editor.value.commands.setContent(value, { emitUpdate: false });
        }
    },
    { deep: true },
);

onBeforeUnmount(() => {
    editor.value?.destroy();
});

function csrfToken(): string {
    return (
        document
            .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? ''
    );
}

function syncSelectedImage(currentEditor: Editor): void {
    selectedImage.visible = currentEditor.isActive('image');

    if (!selectedImage.visible) {
        return;
    }

    const attributes = currentEditor.getAttributes('image');
    selectedImage.alt =
        typeof attributes.alt === 'string' ? attributes.alt : '';
    selectedImage.align = ['left', 'center', 'right'].includes(attributes.align)
        ? (attributes.align as ImageAlignment)
        : 'center';
}

async function uploadFiles(
    currentEditor: Editor,
    files: File[],
    position?: number,
): Promise<void> {
    let insertionPosition = position;

    for (const file of files) {
        const uploaded = await uploadImage(file);

        if (!uploaded) {
            continue;
        }

        const width = Math.min(uploaded.width, 1200);
        const height = Math.max(
            1,
            Math.round(width * (uploaded.height / uploaded.width)),
        );
        const imageNode: JSONContent = {
            type: 'image',
            attrs: {
                src: uploaded.url,
                alt: suggestedAlt(file.name),
                width,
                height,
                mediaId: uploaded.id,
                align: 'center',
            },
        };

        if (insertionPosition === undefined) {
            currentEditor.chain().focus().insertContent(imageNode).run();
        } else {
            currentEditor
                .chain()
                .focus()
                .insertContentAt(insertionPosition, imageNode)
                .run();
            insertionPosition += 1;
        }
    }
}

async function uploadImage(file: File): Promise<UploadedImage | null> {
    if (!props.allowedImageTypes.includes(file.type)) {
        uploadError.value = 'Dozwolone są tylko obrazy JPG, PNG i WebP.';

        return null;
    }

    if (file.size > props.maxSizeMb * 1024 * 1024) {
        uploadError.value = `Obraz może mieć maksymalnie ${props.maxSizeMb} MB.`;

        return null;
    }

    uploadError.value = null;
    activeUploads.value += 1;
    emit('uploading-change', true);

    try {
        const body = new FormData();
        body.append('image', file);

        const response = await fetch(props.uploadUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body,
        });
        const result = (await response.json()) as
            | UploadedImage
            | { message?: string; errors?: { image?: string[] } };

        if (!response.ok || !('id' in result)) {
            let message: string | undefined;

            if ('errors' in result) {
                message = result.errors?.image?.[0];
            } else if ('message' in result) {
                message = result.message;
            }

            throw new Error(message ?? 'Upload obrazu nie powiódł się.');
        }

        return result;
    } catch (error) {
        uploadError.value =
            error instanceof Error
                ? error.message
                : 'Nie udało się przesłać obrazu.';

        return null;
    } finally {
        activeUploads.value -= 1;
        emit('uploading-change', activeUploads.value > 0);
    }
}

function suggestedAlt(fileName: string): string {
    return (
        fileName
            .replace(/\.[^.]+$/, '')
            .replace(/[-_]+/g, ' ')
            .trim() || 'Obraz artykułu'
    );
}

function handleFileSelect(event: Event): void {
    const input = event.target as HTMLInputElement;

    if (editor.value && input.files?.length) {
        void uploadFiles(editor.value, Array.from(input.files));
    }

    input.value = '';
}

function toggleLinkInput(): void {
    if (editor.value?.isActive('link')) {
        editor.value.chain().focus().unsetLink().run();

        return;
    }

    linkUrl.value = '';
    showLinkInput.value = !showLinkInput.value;
}

function applyLink(): void {
    if (!editor.value) {
        return;
    }

    const value = normalizeLink(linkUrl.value);

    if (!value) {
        return;
    }

    editor.value
        .chain()
        .focus()
        .extendMarkRange('link')
        .setLink({ href: value })
        .run();
    showLinkInput.value = false;
    linkUrl.value = '';
}

function normalizeLink(value: string): string | null {
    let normalized = value.trim();

    if (!normalized) {
        uploadError.value = 'Podaj adres linku.';

        return null;
    }

    if (normalized.startsWith('/') || normalized.startsWith('#')) {
        return normalized.startsWith('//') ? null : normalized;
    }

    if (!/^[a-z][a-z\d+.-]*:/i.test(normalized)) {
        normalized = `https://${normalized}`;
    }

    try {
        const protocol = new URL(normalized).protocol;

        if (!['http:', 'https:', 'mailto:'].includes(protocol)) {
            throw new Error();
        }
    } catch {
        uploadError.value = 'Dozwolone są linki HTTP, HTTPS i mailto.';

        return null;
    }

    uploadError.value = null;

    return normalized;
}

function updateImageAlt(): void {
    editor.value
        ?.chain()
        .focus()
        .updateAttributes('image', { alt: selectedImage.alt.trim() })
        .run();
}

function setImageAlignment(align: ImageAlignment): void {
    editor.value?.chain().focus().updateAttributes('image', { align }).run();
    selectedImage.align = align;
}

function deleteSelectedImage(): void {
    editor.value?.chain().focus().deleteSelection().run();
}
</script>

<template>
    <div
        class="overflow-hidden rounded-xl border border-input bg-background transition-shadow focus-within:ring-2 focus-within:ring-ring"
    >
        <div
            v-if="editor"
            class="flex flex-wrap items-center gap-1 border-b border-input bg-muted/40 p-2"
        >
            <button
                type="button"
                title="Cofnij"
                :disabled="!editor.can().undo()"
                :class="toolbarButton"
                @click="editor.chain().focus().undo().run()"
            >
                <Undo2 class="size-4" />
            </button>
            <button
                type="button"
                title="Ponów"
                :disabled="!editor.can().redo()"
                :class="toolbarButton"
                @click="editor.chain().focus().redo().run()"
            >
                <Redo2 class="size-4" />
            </button>
            <span class="mx-1 h-5 w-px bg-border" />
            <button
                type="button"
                title="Pogrubienie"
                :class="[
                    toolbarButton,
                    editor.isActive('bold') && activeButton,
                ]"
                @click="editor.chain().focus().toggleBold().run()"
            >
                <Bold class="size-4" />
            </button>
            <button
                type="button"
                title="Kursywa"
                :class="[
                    toolbarButton,
                    editor.isActive('italic') && activeButton,
                ]"
                @click="editor.chain().focus().toggleItalic().run()"
            >
                <Italic class="size-4" />
            </button>
            <button
                type="button"
                title="Podkreślenie"
                :class="[
                    toolbarButton,
                    editor.isActive('underline') && activeButton,
                ]"
                @click="editor.chain().focus().toggleUnderline().run()"
            >
                <UnderlineIcon class="size-4" />
            </button>
            <button
                type="button"
                title="Przekreślenie"
                :class="[
                    toolbarButton,
                    editor.isActive('strike') && activeButton,
                ]"
                @click="editor.chain().focus().toggleStrike().run()"
            >
                <Strikethrough class="size-4" />
            </button>
            <span class="mx-1 h-5 w-px bg-border" />
            <button
                type="button"
                title="Nagłówek H2"
                :class="[
                    toolbarButton,
                    editor.isActive('heading', { level: 2 }) && activeButton,
                ]"
                @click="
                    editor.chain().focus().toggleHeading({ level: 2 }).run()
                "
            >
                <Heading2 class="size-4" />
            </button>
            <button
                type="button"
                title="Nagłówek H3"
                :class="[
                    toolbarButton,
                    editor.isActive('heading', { level: 3 }) && activeButton,
                ]"
                @click="
                    editor.chain().focus().toggleHeading({ level: 3 }).run()
                "
            >
                <Heading3 class="size-4" />
            </button>
            <span class="mx-1 h-5 w-px bg-border" />
            <button
                type="button"
                title="Lista punktowana"
                :class="[
                    toolbarButton,
                    editor.isActive('bulletList') && activeButton,
                ]"
                @click="editor.chain().focus().toggleBulletList().run()"
            >
                <List class="size-4" />
            </button>
            <button
                type="button"
                title="Lista numerowana"
                :class="[
                    toolbarButton,
                    editor.isActive('orderedList') && activeButton,
                ]"
                @click="editor.chain().focus().toggleOrderedList().run()"
            >
                <ListOrdered class="size-4" />
            </button>
            <button
                type="button"
                title="Cytat"
                :class="[
                    toolbarButton,
                    editor.isActive('blockquote') && activeButton,
                ]"
                @click="editor.chain().focus().toggleBlockquote().run()"
            >
                <Quote class="size-4" />
            </button>
            <button
                type="button"
                title="Blok kodu"
                :class="[
                    toolbarButton,
                    editor.isActive('codeBlock') && activeButton,
                ]"
                @click="editor.chain().focus().toggleCodeBlock().run()"
            >
                <Code class="size-4" />
            </button>
            <button
                type="button"
                title="Linia pozioma"
                :class="toolbarButton"
                @click="editor.chain().focus().setHorizontalRule().run()"
            >
                <Minus class="size-4" />
            </button>
            <span class="mx-1 h-5 w-px bg-border" />
            <button
                type="button"
                :title="editor.isActive('link') ? 'Usuń link' : 'Dodaj link'"
                :class="[
                    toolbarButton,
                    editor.isActive('link') && activeButton,
                ]"
                @click="toggleLinkInput"
            >
                <component
                    :is="editor.isActive('link') ? Link2Off : Link2"
                    class="size-4"
                />
            </button>
            <button
                type="button"
                title="Dodaj obraz"
                :disabled="activeUploads > 0"
                :class="toolbarButton"
                @click="fileInput?.click()"
            >
                <ImagePlus class="size-4" />
            </button>
            <input
                ref="fileInput"
                type="file"
                multiple
                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                class="hidden"
                @change="handleFileSelect"
            />
        </div>

        <div
            v-if="showLinkInput"
            class="flex items-center gap-2 border-b border-input bg-muted/20 p-2"
        >
            <input
                v-model="linkUrl"
                type="text"
                inputmode="url"
                placeholder="https://example.com"
                class="h-9 flex-1 rounded-md border border-input bg-background px-3 text-sm outline-none focus:ring-1 focus:ring-ring"
                @keydown.enter.prevent="applyLink"
                @keydown.escape="showLinkInput = false"
            />
            <button
                type="button"
                class="text-sm font-medium"
                @click="applyLink"
            >
                Dodaj
            </button>
            <button
                type="button"
                class="text-sm text-muted-foreground"
                @click="showLinkInput = false"
            >
                Anuluj
            </button>
        </div>

        <div
            v-if="selectedImage.visible"
            class="flex flex-wrap items-end gap-3 border-b border-input bg-muted/20 p-3"
        >
            <label class="min-w-60 flex-1 text-xs font-medium">
                Tekst alternatywny obrazu
                <input
                    v-model="selectedImage.alt"
                    maxlength="250"
                    class="mt-1 h-9 w-full rounded-md border border-input bg-background px-3 text-sm outline-none focus:ring-1 focus:ring-ring"
                    @change="updateImageAlt"
                />
            </label>
            <div class="flex gap-1" aria-label="Wyrównanie obrazu">
                <button
                    v-for="alignment in ['left', 'center', 'right'] as const"
                    :key="alignment"
                    type="button"
                    :title="`Wyrównaj: ${alignment}`"
                    :class="[
                        toolbarButton,
                        selectedImage.align === alignment && activeButton,
                    ]"
                    @click="setImageAlignment(alignment)"
                >
                    <AlignLeft v-if="alignment === 'left'" class="size-4" />
                    <AlignCenter
                        v-else-if="alignment === 'center'"
                        class="size-4"
                    />
                    <AlignRight v-else class="size-4" />
                </button>
                <button
                    type="button"
                    title="Usuń obraz"
                    :class="[toolbarButton, 'text-destructive']"
                    @click="deleteSelectedImage"
                >
                    <Trash2 class="size-4" />
                </button>
            </div>
            <p class="w-full text-xs text-muted-foreground">
                Zmień rozmiar, przeciągając jeden z uchwytów w narożnikach
                obrazu. Proporcje są zachowywane automatycznie.
            </p>
        </div>

        <p
            v-if="uploadError"
            class="border-b border-destructive/30 bg-destructive/10 px-4 py-2 text-sm text-destructive"
        >
            {{ uploadError }}
        </p>
        <p
            v-if="activeUploads > 0"
            class="border-b border-input bg-muted/20 px-4 py-2 text-sm text-muted-foreground"
        >
            Przesyłanie obrazów: {{ activeUploads }}…
        </p>

        <EditorContent :editor="editor" />

        <div
            v-if="editor"
            class="flex justify-between border-t border-input bg-muted/20 px-4 py-2 text-xs text-muted-foreground"
        >
            <span
                >Upuść lub wklej JPG, PNG albo WebP (maks.
                {{ maxSizeMb }} MB).</span
            >
            <span>
                {{ editor.storage.characterCount.characters() }} znaków ·
                {{ editor.storage.characterCount.words() }} słów
            </span>
        </div>
    </div>
</template>

<style scoped>
:deep(.tiptap-content) {
    color: var(--foreground);
    font-size: 0.9375rem;
    line-height: 1.75;
}

:deep(.tiptap-content p) {
    margin: 0.75em 0;
}

:deep(.tiptap-content h2) {
    margin: 1.4em 0 0.5em;
    font-size: 1.5rem;
    font-weight: 700;
}

:deep(.tiptap-content h3) {
    margin: 1.2em 0 0.5em;
    font-size: 1.25rem;
    font-weight: 650;
}

:deep(.tiptap-content h4) {
    margin: 1em 0 0.4em;
    font-size: 1.1rem;
    font-weight: 650;
}

:deep(.tiptap-content ul),
:deep(.tiptap-content ol) {
    margin: 0.75em 0;
    padding-left: 1.75em;
}

:deep(.tiptap-content ul) {
    list-style: disc;
}

:deep(.tiptap-content ol) {
    list-style: decimal;
}

:deep(.tiptap-content blockquote) {
    margin: 1em 0;
    border-left: 3px solid var(--border);
    padding-left: 1em;
    color: var(--muted-foreground);
    font-style: italic;
}

:deep(.tiptap-content pre) {
    margin: 1em 0;
    overflow-x: auto;
    border-radius: 0.5rem;
    background: var(--muted);
    padding: 0.9em 1em;
}

:deep(.tiptap-content code) {
    border-radius: 0.25rem;
    background: var(--muted);
    padding: 0.15em 0.35em;
    font-size: 0.88em;
}

:deep(.tiptap-content pre code) {
    background: transparent;
    padding: 0;
}

:deep(.tiptap-content a) {
    color: #b77805;
    text-decoration: underline;
    text-underline-offset: 2px;
}

:deep(.tiptap-content img) {
    display: block;
    max-width: 100%;
    height: auto;
    border-radius: 0.75rem;
}

:deep(.tiptap-content img[data-align='left']) {
    margin-right: auto;
}

:deep(.tiptap-content img[data-align='center']) {
    margin-right: auto;
    margin-left: auto;
}

:deep(.tiptap-content img[data-align='right']) {
    margin-left: auto;
}

:deep([data-resize-container]) {
    max-width: 100%;
    padding-block: 0.75rem;
}

:deep([data-resize-container]:has(img[data-align='left'])) {
    justify-content: flex-start;
}

:deep([data-resize-container]:has(img[data-align='center'])) {
    justify-content: center;
}

:deep([data-resize-container]:has(img[data-align='right'])) {
    justify-content: flex-end;
}

:deep([data-resize-wrapper]) {
    max-width: 100%;
}

:deep([data-resize-handle]) {
    z-index: 10;
    width: 12px;
    height: 12px;
    border: 2px solid var(--background);
    border-radius: 999px;
    background: var(--foreground);
    opacity: 0;
}

:deep(.ProseMirror-selectednode [data-resize-handle]) {
    opacity: 1;
}

:deep([data-resize-handle='top-left']),
:deep([data-resize-handle='bottom-right']) {
    cursor: nwse-resize;
}

:deep([data-resize-handle='top-right']),
:deep([data-resize-handle='bottom-left']) {
    cursor: nesw-resize;
}

:deep([data-resize-handle='top-left']) {
    transform: translate(-50%, -50%);
}

:deep([data-resize-handle='top-right']) {
    transform: translate(50%, -50%);
}

:deep([data-resize-handle='bottom-left']) {
    transform: translate(-50%, 50%);
}

:deep([data-resize-handle='bottom-right']) {
    transform: translate(50%, 50%);
}

:deep(.tiptap-content p.is-editor-empty:first-child::before) {
    float: left;
    height: 0;
    color: var(--muted-foreground);
    content: attr(data-placeholder);
    pointer-events: none;
}
</style>
