<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { FilePenLine, FilePlus2, Search, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';

interface PostSummary {
    id: number;
    title: string;
    slug: string;
    status: 'draft' | 'published';
    created_at: string;
    updated_at: string;
    published_at: string | null;
    edit_url: string;
    delete_url: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedPosts {
    data: PostSummary[];
    current_page: number;
    from: number | null;
    last_page: number;
    links: PaginationLink[];
    to: number | null;
    total: number;
}

const props = defineProps<{
    posts: PaginatedPosts;
    filters: { search: string };
    indexUrl: string;
    createUrl: string;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Posty',
                href: '/dashboard/posts',
            },
        ],
    },
});

const search = ref(props.filters.search);
const deleteDialogOpen = ref(false);
const deleting = ref(false);
const selectedPost = ref<PostSummary | null>(null);

function applySearch(): void {
    router.get(
        props.indexUrl,
        { search: search.value.trim() || undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function clearSearch(): void {
    search.value = '';
    applySearch();
}

function openDeleteDialog(post: PostSummary): void {
    selectedPost.value = post;
    deleteDialogOpen.value = true;
}

function deletePost(): void {
    if (!selectedPost.value) {
        return;
    }

    router.delete(selectedPost.value.delete_url, {
        preserveScroll: true,
        onStart: () => {
            deleting.value = true;
        },
        onSuccess: () => {
            deleteDialogOpen.value = false;
            selectedPost.value = null;
        },
        onFinish: () => {
            deleting.value = false;
        },
    });
}

function formatDate(value: string): string {
    return new Intl.DateTimeFormat('pl-PL', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function paginationLabel(label: string): string {
    return label
        .replaceAll('&laquo;', '‹')
        .replaceAll('&raquo;', '›')
        .replace('Previous', 'Poprzednia')
        .replace('Next', 'Następna');
}
</script>

<template>
    <Head title="Zarządzanie postami" />

    <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 md:p-8">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
        >
            <div>
                <p class="text-sm font-medium text-muted-foreground">Blog</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight">
                    Zarządzanie postami
                </h1>
                <p class="mt-2 text-muted-foreground">
                    Edytuj, publikuj i usuwaj artykuły z jednego miejsca.
                </p>
            </div>
            <Button as-child class="w-full sm:w-auto">
                <Link :href="createUrl">
                    <FilePlus2 class="size-4" />
                    Nowy artykuł
                </Link>
            </Button>
        </div>

        <Card class="gap-0 py-0">
            <CardContent class="p-4 sm:p-5">
                <form
                    class="flex flex-col gap-3 sm:flex-row"
                    role="search"
                    @submit.prevent="applySearch"
                >
                    <div class="relative flex-1">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            v-model="search"
                            type="search"
                            maxlength="160"
                            class="pl-9"
                            placeholder="Szukaj po tytule…"
                            aria-label="Szukaj postów po tytule"
                        />
                    </div>
                    <Button type="submit">Szukaj</Button>
                    <Button
                        v-if="filters.search"
                        type="button"
                        variant="ghost"
                        @click="clearSearch"
                    >
                        Wyczyść
                    </Button>
                </form>
            </CardContent>
        </Card>

        <Card v-if="posts.data.length" class="gap-0 overflow-hidden py-0">
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full text-left text-sm">
                    <thead class="border-b bg-muted/50 text-muted-foreground">
                        <tr>
                            <th class="px-5 py-3 font-medium">Artykuł</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Utworzono</th>
                            <th class="px-5 py-3 font-medium">
                                Ostatnia zmiana
                            </th>
                            <th class="px-5 py-3 text-right font-medium">
                                Akcje
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr
                            v-for="post in posts.data"
                            :key="post.id"
                            class="transition-colors hover:bg-muted/30"
                        >
                            <td class="max-w-md px-5 py-4">
                                <p class="truncate font-semibold">
                                    {{ post.title }}
                                </p>
                                <p
                                    class="mt-1 truncate text-xs text-muted-foreground"
                                >
                                    /blog/{{ post.slug }}
                                </p>
                            </td>
                            <td class="px-5 py-4">
                                <Badge
                                    variant="outline"
                                    :class="
                                        post.status === 'published'
                                            ? 'border-emerald-600/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300'
                                            : 'border-amber-600/30 bg-amber-500/10 text-amber-700 dark:text-amber-300'
                                    "
                                >
                                    {{
                                        post.status === 'published'
                                            ? 'Opublikowany'
                                            : 'Szkic'
                                    }}
                                </Badge>
                            </td>
                            <td
                                class="px-5 py-4 whitespace-nowrap text-muted-foreground"
                            >
                                {{ formatDate(post.created_at) }}
                            </td>
                            <td
                                class="px-5 py-4 whitespace-nowrap text-muted-foreground"
                            >
                                {{ formatDate(post.updated_at) }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <Button
                                        as-child
                                        size="sm"
                                        variant="outline"
                                    >
                                        <Link :href="post.edit_url">
                                            <FilePenLine class="size-4" />
                                            Edytuj
                                        </Link>
                                    </Button>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="destructive"
                                        @click="openDeleteDialog(post)"
                                    >
                                        <Trash2 class="size-4" />
                                        Usuń
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-border md:hidden">
                <article
                    v-for="post in posts.data"
                    :key="post.id"
                    class="space-y-4 p-4"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="font-semibold break-words">
                                {{ post.title }}
                            </h2>
                            <p
                                class="mt-1 truncate text-xs text-muted-foreground"
                            >
                                /blog/{{ post.slug }}
                            </p>
                        </div>
                        <Badge
                            variant="outline"
                            :class="
                                post.status === 'published'
                                    ? 'shrink-0 border-emerald-600/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300'
                                    : 'shrink-0 border-amber-600/30 bg-amber-500/10 text-amber-700 dark:text-amber-300'
                            "
                        >
                            {{
                                post.status === 'published'
                                    ? 'Opublikowany'
                                    : 'Szkic'
                            }}
                        </Badge>
                    </div>

                    <dl class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <dt class="text-muted-foreground">Utworzono</dt>
                            <dd class="mt-1 font-medium">
                                {{ formatDate(post.created_at) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">
                                Ostatnia zmiana
                            </dt>
                            <dd class="mt-1 font-medium">
                                {{ formatDate(post.updated_at) }}
                            </dd>
                        </div>
                    </dl>

                    <div class="grid grid-cols-2 gap-2">
                        <Button as-child variant="outline">
                            <Link :href="post.edit_url">
                                <FilePenLine class="size-4" />
                                Edytuj
                            </Link>
                        </Button>
                        <Button
                            type="button"
                            variant="destructive"
                            @click="openDeleteDialog(post)"
                        >
                            <Trash2 class="size-4" />
                            Usuń
                        </Button>
                    </div>
                </article>
            </div>
        </Card>

        <Card v-else class="items-center gap-4 px-6 py-12 text-center">
            <div class="rounded-full bg-muted p-4">
                <FilePenLine class="size-8 text-muted-foreground" />
            </div>
            <div>
                <h2 class="text-lg font-semibold">
                    {{
                        filters.search
                            ? 'Nie znaleziono postów'
                            : 'Nie masz jeszcze postów'
                    }}
                </h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{
                        filters.search
                            ? 'Zmień wyszukiwaną frazę i spróbuj ponownie.'
                            : 'Utwórz pierwszy artykuł, aby pojawił się na tej liście.'
                    }}
                </p>
            </div>
            <Button v-if="!filters.search" as-child>
                <Link :href="createUrl">Nowy artykuł</Link>
            </Button>
            <Button v-else type="button" variant="outline" @click="clearSearch">
                Wyczyść wyszukiwanie
            </Button>
        </Card>

        <nav
            v-if="posts.last_page > 1"
            class="flex flex-wrap items-center justify-center gap-2"
            aria-label="Paginacja postów"
        >
            <template v-for="link in posts.links" :key="link.label">
                <Button
                    v-if="link.url"
                    as-child
                    size="sm"
                    :variant="link.active ? 'default' : 'outline'"
                >
                    <Link
                        :href="link.url"
                        preserve-scroll
                        preserve-state
                        :aria-current="link.active ? 'page' : undefined"
                    >
                        {{ paginationLabel(link.label) }}
                    </Link>
                </Button>
                <Button v-else size="sm" variant="outline" disabled>
                    {{ paginationLabel(link.label) }}
                </Button>
            </template>
        </nav>

        <p class="text-center text-sm text-muted-foreground">
            <template v-if="posts.total">
                Wyświetlono {{ posts.from }}–{{ posts.to }} z
                {{ posts.total }} postów.
            </template>
            <template v-else>Brak postów do wyświetlenia.</template>
        </p>
    </div>

    <Dialog v-model:open="deleteDialogOpen">
        <DialogContent :show-close-button="!deleting">
            <DialogHeader>
                <DialogTitle>Usunąć artykuł?</DialogTitle>
                <DialogDescription>
                    Czy na pewno chcesz usunąć post
                    <strong class="font-semibold text-foreground">
                        „{{ selectedPost?.title }}”</strong
                    >? Tej operacji nie można cofnąć.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button
                    type="button"
                    variant="outline"
                    :disabled="deleting"
                    @click="deleteDialogOpen = false"
                >
                    Anuluj
                </Button>
                <Button
                    type="button"
                    variant="destructive"
                    :disabled="deleting"
                    @click="deletePost"
                >
                    <Trash2 class="size-4" />
                    {{ deleting ? 'Usuwanie…' : 'Usuń post' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
