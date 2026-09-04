<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowRight, Search } from '@lucide/vue';
import { ref } from 'vue';
import ClientAppLayout from '@/layouts/ClientAppLayout.vue';

interface PostSummary {
    title: string;
    slug: string;
    excerpt: string | null;
    author: string;
    published_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedPosts {
    data: PostSummary[];
    links: PaginationLink[];
    total: number;
}

const props = defineProps<{
    posts: PaginatedPosts;
    filters: {
        search: string;
        sort: 'newest' | 'oldest';
    };
}>();

defineOptions({ layout: ClientAppLayout });

const search = ref(props.filters.search);

function applyFilters(sort = props.filters.sort): void {
    router.get(
        '/blog',
        { search: search.value || undefined, sort },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function formatDate(value: string): string {
    return new Intl.DateTimeFormat('pl-PL', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(value));
}

function paginationLabel(label: string): string {
    return label.replaceAll('&laquo;', '‹').replaceAll('&raquo;', '›');
}
</script>

<template>
    <Head title="Blog" />

    <section class="relative bg-[#0B1F3A0D]">
        <div
            class="mx-auto w-full max-w-[1600px] px-6 pt-16 pb-24 lg:pt-24 lg:pb-32"
        >
            <div class="mx-auto max-w-4xl text-center">
                <p
                    class="mb-6 inline-flex items-center gap-3 rounded-full bg-white px-6 py-3 text-lg font-medium text-[#0B1F3A]"
                >
                    <span class="h-1.5 w-1.5 rounded-full bg-[#F5A623]" />
                    Blog AmperMaks
                </p>
                <h1
                    class="text-4xl font-extrabold tracking-tight text-[#0B1F3A] sm:text-5xl lg:text-6xl"
                >
                    Wiedza od praktyków,
                    <span class="text-[#F5A623]">bez lania wody.</span>
                </h1>
                <p
                    class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-slate-600"
                >
                    Poradniki dotyczące instalacji elektrycznych, pomiarów i
                    bezpiecznego użytkowania energii.
                </p>

                <form
                    class="mx-auto mt-12 flex max-w-3xl flex-col gap-3 rounded-2xl bg-white p-3 shadow-xl ring-1 ring-slate-200 focus-within:ring-2 focus-within:ring-[#F5A623] md:flex-row"
                    @submit.prevent="applyFilters()"
                >
                    <div class="flex flex-1 items-center gap-3 rounded-xl px-5">
                        <Search class="size-5 shrink-0 text-slate-400" />
                        <input
                            v-model="search"
                            type="search"
                            placeholder="Szukaj artykułów..."
                            aria-label="Szukaj artykułów na blogu"
                            class="h-14 w-full border-none bg-transparent px-0 text-base text-slate-900 placeholder:text-slate-400 focus:outline-none"
                        />
                    </div>
                    <button
                        type="submit"
                        class="h-14 shrink-0 rounded-xl bg-[#F5A623] px-8 font-semibold text-[#0B1F3A] transition-colors hover:bg-[#D88E12]"
                    >
                        Szukaj
                    </button>
                </form>

                <div class="mt-6 flex justify-center gap-3 text-sm">
                    <button
                        type="button"
                        :class="[
                            'rounded-full px-4 py-2 transition',
                            filters.sort === 'newest'
                                ? 'bg-[#F5A623] text-[#0B1F3A]'
                                : 'bg-white text-slate-600 hover:bg-amber-100',
                        ]"
                        @click="applyFilters('newest')"
                    >
                        Najnowsze
                    </button>
                    <button
                        type="button"
                        :class="[
                            'rounded-full px-4 py-2 transition',
                            filters.sort === 'oldest'
                                ? 'bg-[#F5A623] text-[#0B1F3A]'
                                : 'bg-white text-slate-600 hover:bg-amber-100',
                        ]"
                        @click="applyFilters('oldest')"
                    >
                        Najstarsze
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white py-20">
        <div class="mx-auto w-full max-w-[1600px] px-6">
            <div class="mb-10">
                <h2
                    class="text-3xl font-extrabold tracking-tight text-[#0B1F3A]"
                >
                    Wszystkie artykuły
                </h2>
                <p class="mt-3 text-slate-600">
                    {{ posts.total }} opublikowanych wpisów.
                </p>
            </div>

            <div
                v-if="posts.data.length"
                class="grid grid-cols-1 gap-8 md:grid-cols-2 xl:grid-cols-3"
            >
                <article
                    v-for="post in posts.data"
                    :key="post.slug"
                    class="group flex min-h-72 flex-col rounded-2xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                >
                    <p class="text-sm font-medium text-[#B77805]">
                        {{ formatDate(post.published_at) }} · {{ post.author }}
                    </p>
                    <h3
                        class="mt-4 text-2xl leading-tight font-bold text-[#0B1F3A]"
                    >
                        {{ post.title }}
                    </h3>
                    <p class="mt-4 line-clamp-4 leading-relaxed text-slate-600">
                        {{
                            post.excerpt ||
                            'Przeczytaj najnowszy artykuł na blogu AmperMaks.'
                        }}
                    </p>
                    <Link
                        :href="`/blog/${post.slug}`"
                        class="mt-auto inline-flex items-center gap-2 pt-6 font-semibold text-[#0B1F3A] group-hover:text-[#B77805]"
                    >
                        Czytaj artykuł
                        <ArrowRight class="size-4" />
                    </Link>
                </article>
            </div>

            <div
                v-else
                class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-16 text-center text-slate-600"
            >
                Nie znaleziono artykułów spełniających podane kryteria.
            </div>

            <nav
                v-if="posts.links.length > 3"
                class="mt-12 flex flex-wrap justify-center gap-2"
                aria-label="Paginacja"
            >
                <component
                    :is="link.url ? Link : 'span'"
                    v-for="link in posts.links"
                    :key="link.label"
                    :href="link.url ?? undefined"
                    :class="[
                        'rounded-lg border px-4 py-2 text-sm',
                        link.active
                            ? 'border-[#F5A623] bg-[#F5A623] text-[#0B1F3A]'
                            : link.url
                              ? 'border-slate-200 text-slate-700 hover:bg-slate-50'
                              : 'border-slate-100 text-slate-300',
                    ]"
                >
                    {{ paginationLabel(link.label) }}
                </component>
            </nav>
        </div>
    </section>
</template>
