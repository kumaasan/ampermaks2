<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { FileText, Send } from '@lucide/vue';
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

interface Props {
    storeUrl: string;
    uploadUrl: string;
    maxUploadSizeMb: number;
    allowedImageTypes: string[];
}

interface PostForm {
    title: string;
    excerpt: string;
    content: JSONContent;
    status: 'draft' | 'published';
}

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Nowy artykuł',
                href: '/dashboard/posts/create',
            },
        ],
    },
});

const emptyDocument: JSONContent = {
    type: 'doc',
    content: [{ type: 'paragraph' }],
};
const editorUploading = ref(false);
const form = useForm<PostForm>({
    title: '',
    excerpt: '',
    content: emptyDocument,
    status: 'draft',
});

const submitDisabled = computed(() => form.processing || editorUploading.value);

function submit(status: PostForm['status']): void {
    form.status = status;
    form.post(props.storeUrl, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Nowy artykuł" />

    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4 md:p-8">
        <div>
            <p class="text-sm font-medium text-muted-foreground">Blog</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight">Nowy artykuł</h1>
            <p class="mt-2 text-muted-foreground">
                Zapisz szkic albo opublikuj gotowy wpis na stronie bloga.
            </p>
        </div>

        <form class="space-y-6" @submit.prevent="submit('published')">
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <FileText class="size-5" />
                        Podstawowe informacje
                    </CardTitle>
                    <CardDescription>
                        Tytuł będzie nagłówkiem H1 strony. W treści używaj
                        nagłówków od H2.
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
                        />
                        <div class="flex justify-between gap-3">
                            <InputError :message="form.errors.title" />
                            <span class="ml-auto text-xs text-muted-foreground">
                                {{ form.title.length }}/160
                            </span>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="excerpt">Krótki opis</Label>
                        <textarea
                            id="excerpt"
                            v-model="form.excerpt"
                            maxlength="320"
                            rows="3"
                            placeholder="Opis widoczny na liście artykułów i użyteczny dla SEO."
                            class="w-full resize-y rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring"
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
                        Obrazy są przesyłane do Laravel Storage. Nie zapisujemy
                        ich w treści jako Base64.
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
                class="sticky bottom-4 flex flex-col-reverse gap-3 rounded-xl border bg-background/95 p-4 shadow-lg backdrop-blur sm:flex-row sm:justify-end"
            >
                <p
                    v-if="editorUploading"
                    class="mr-auto self-center text-sm text-muted-foreground"
                >
                    Poczekaj na zakończenie przesyłania obrazów.
                </p>
                <Button
                    type="button"
                    variant="outline"
                    :disabled="submitDisabled"
                    @click="submit('draft')"
                >
                    Zapisz szkic
                </Button>
                <Button type="submit" :disabled="submitDisabled">
                    <Send class="size-4" />
                    {{ form.processing ? 'Zapisywanie…' : 'Opublikuj' }}
                </Button>
            </div>
        </form>
    </div>
</template>
