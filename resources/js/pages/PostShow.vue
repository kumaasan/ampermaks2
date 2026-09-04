<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import ClientAppLayout from '@/layouts/ClientAppLayout.vue';

interface PublicPost {
    title: string;
    slug: string;
    excerpt: string | null;
    content_html: string;
    author: string;
    published_at: string;
}

const props = defineProps<{ post: PublicPost }>();

defineOptions({ layout: ClientAppLayout });

const formattedDate = new Intl.DateTimeFormat('pl-PL', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
}).format(new Date(props.post.published_at));
</script>

<template>
    <Head :title="post.title">
        <meta
            v-if="post.excerpt"
            head-key="description"
            name="description"
            :content="post.excerpt"
        />
    </Head>

    <article class="bg-white py-16 lg:py-24">
        <div class="mx-auto max-w-4xl px-6">
            <Link
                href="/blog"
                class="inline-flex items-center gap-2 font-medium text-slate-600 hover:text-[#B77805]"
            >
                <ArrowLeft class="size-4" />
                Wróć do bloga
            </Link>

            <header class="mt-10 border-b border-slate-200 pb-10">
                <p class="font-medium text-[#B77805]">
                    {{ formattedDate }} · {{ post.author }}
                </p>
                <h1
                    class="mt-4 text-4xl font-extrabold tracking-tight text-[#0B1F3A] sm:text-5xl"
                >
                    {{ post.title }}
                </h1>
                <p
                    v-if="post.excerpt"
                    class="mt-6 text-xl leading-relaxed text-slate-600"
                >
                    {{ post.excerpt }}
                </p>
            </header>

            <!-- content_html powstaje z walidowanego JSON-u w PostContentRenderer. -->
            <div class="blog-content mt-12" v-html="post.content_html" />
        </div>
    </article>
</template>
