<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { FileText, Save, Send, X } from '@lucide/vue';
import type { JSONContent } from '@tiptap/core';
import { computed, ref } from 'vue';
import RichTextEditor from '@/components/editor/RichTextEditor.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface EditablePost {
    title: string;
    slug: string;
    excerpt: string;
    content: JSONContent;
    status: 'draft' | 'published';
}

interface PostForm {
    title: string;
    slug: string;
    excerpt: string;
    content: JSONContent;
    status: 'draft' | 'published';
}

const props = defineProps<{
    mode: 'create' | 'edit';
    submitUrl: string;
    cancelUrl: string;
    uploadUrl: string;
    maxUploadSizeMb: number;
    allowedImageTypes: string[];
    initialPost?: EditablePost;
}>();

const emptyDocument: JSONContent = {
    type: 'doc',
    content: [{ type: 'paragraph' }],
};
const isEditing = computed(() => props.mode === 'edit');
const editorUploading = ref(false);
const form = useForm<PostForm>({
    title: props.initialPost?.title ?? '',
    slug: props.initialPost?.slug ?? '',
    excerpt: props.initialPost?.excerpt ?? '',
    content: cloneContent(props.initialPost?.content ?? emptyDocument),
    status: props.initialPost?.status ?? 'draft',
});

function cloneContent(content: JSONContent): JSONContent {
    return JSON.parse(JSON.stringify(content)) as JSONContent;
}

const submitDisabled = computed(() => form.processing || editorUploading.value);
const primaryLabel = computed(() => {
    if (form.processing) {
        return 'Zapisywanie…';
    }

    if (isEditing.value && form.status === 'published') {
        return 'Zapisz zmiany';
    }

    return 'Opublikuj';
});
const draftLabel = computed(() =>
    isEditing.value && form.status === 'draft'
        ? 'Zapisz zmiany'
        : 'Zapisz jako szkic',
);

function submit(status: PostForm['status']): void {
    form.status = status;

    const options = {
        preserveScroll: true,
    };

    if (isEditing.value) {
        form.put(props.submitUrl, options);

        return;
    }

    form.post(props.submitUrl, options);
}
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit('published')">
        <Card>
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <FileText class="size-5" />
                    Podstawowe informacje
                </CardTitle>
                <CardDescription>
                    Tytuł będzie nagłówkiem H1 strony. W treści używaj nagłówków
                    od H2.
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-5">
                <div class="grid gap-2">
                    <Label for="title">Tytuł</Label>
                    <Input
                        id="title"
                        v-model="form.title"
                        maxlength="160"
                        required
                        autocomplete="off"
                        placeholder="Np. Jak przygotować instalację pod fotowoltaikę?"
                        :aria-invalid="Boolean(form.errors.title)"
                    />
                    <div class="flex justify-between gap-3">
                        <InputError :message="form.errors.title" />
                        <span class="ml-auto text-xs text-muted-foreground">
                            {{ form.title.length }}/160
                        </span>
                    </div>
                </div>

                <div v-if="isEditing" class="grid gap-2">
                    <Label for="slug">Adres artykułu</Label>
                    <div
                        class="flex rounded-md border border-input bg-transparent shadow-xs focus-within:border-ring focus-within:ring-3 focus-within:ring-ring/50 dark:bg-input/30"
                        :class="
                            form.errors.slug &&
                            'border-destructive ring-destructive/20'
                        "
                    >
                        <span
                            class="flex items-center border-r border-input px-3 text-sm text-muted-foreground"
                        >
                            /blog/
                        </span>
                        <input
                            id="slug"
                            v-model="form.slug"
                            maxlength="190"
                            required
                            autocomplete="off"
                            spellcheck="false"
                            class="h-9 min-w-0 flex-1 bg-transparent px-3 py-1 text-base outline-none placeholder:text-muted-foreground md:text-sm"
                            :aria-invalid="Boolean(form.errors.slug)"
                        />
                    </div>
                    <p class="text-xs text-muted-foreground">
                        Zmiana adresu zmieni publiczny link do artykułu.
                    </p>
                    <InputError :message="form.errors.slug" />
                </div>

                <div class="grid gap-2">
                    <Label for="excerpt">Krótki opis</Label>
                    <textarea
                        id="excerpt"
                        v-model="form.excerpt"
                        maxlength="320"
                        rows="3"
                        placeholder="Opis widoczny na liście artykułów i użyteczny dla SEO."
                        class="w-full resize-y rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50 aria-invalid:border-destructive aria-invalid:ring-destructive/20 dark:bg-input/30 dark:aria-invalid:ring-destructive/40"
                        :aria-invalid="Boolean(form.errors.excerpt)"
                    />
                    <div class="flex justify-between gap-3">
                        <InputError :message="form.errors.excerpt" />
                        <span class="ml-auto text-xs text-muted-foreground">
                            {{ form.excerpt.length }}/320
                        </span>
                    </div>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Treść artykułu</CardTitle>
                <CardDescription>
                    Obrazy są przesyłane do Laravel Storage. Nie zapisujemy ich
                    w treści jako Base64.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <RichTextEditor
                    v-model="form.content"
                    :upload-url="uploadUrl"
                    :allowed-image-types="allowedImageTypes"
                    :max-size-mb="maxUploadSizeMb"
                    @uploading-change="editorUploading = $event"
                />
                <InputError class="mt-2" :message="form.errors.content" />
            </CardContent>
        </Card>

        <div
            class="sticky bottom-4 z-10 flex flex-col-reverse gap-3 rounded-xl border bg-background/95 p-4 shadow-lg backdrop-blur sm:flex-row sm:items-center sm:justify-end"
        >
            <p
                v-if="editorUploading"
                class="mr-auto self-center text-sm text-muted-foreground"
            >
                Poczekaj na zakończenie przesyłania obrazów.
            </p>
            <Button
                v-else
                type="button"
                variant="ghost"
                :disabled="form.processing"
                as-child
            >
                <Link :href="cancelUrl">
                    <X class="size-4" />
                    Anuluj
                </Link>
            </Button>
            <Button
                type="button"
                variant="outline"
                :disabled="submitDisabled"
                @click="submit('draft')"
            >
                <Save class="size-4" />
                {{ draftLabel }}
            </Button>
            <Button type="submit" :disabled="submitDisabled">
                <Send class="size-4" />
                {{ primaryLabel }}
            </Button>
        </div>
    </form>
</template>
