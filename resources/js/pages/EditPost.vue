<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import type { JSONContent } from '@tiptap/core';
import PostForm from '@/components/admin/PostForm.vue';

interface EditablePost {
    title: string;
    slug: string;
    excerpt: string;
    content: JSONContent;
    status: 'draft' | 'published';
}

defineProps<{
    post: EditablePost;
    updateUrl: string;
    indexUrl: string;
    uploadUrl: string;
    maxUploadSizeMb: number;
    allowedImageTypes: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Posty',
                href: '/dashboard/posts',
            },
            {
                title: 'Edycja',
                href: '/dashboard/posts',
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Edycja: ${post.title}`" />

    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4 md:p-8">
        <div>
            <p class="text-sm font-medium text-muted-foreground">Blog</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight">
                Edytuj artykuł
            </h1>
            <p class="mt-2 text-muted-foreground">
                Zmień treść, adres lub status istniejącego wpisu.
            </p>
        </div>

        <PostForm
            mode="edit"
            :submit-url="updateUrl"
            :cancel-url="indexUrl"
            :upload-url="uploadUrl"
            :allowed-image-types="allowedImageTypes"
            :max-upload-size-mb="maxUploadSizeMb"
            :initial-post="post"
        />
    </div>
</template>
