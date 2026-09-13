<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    CheckCircle2,
    Clock3,
    FilePlus2,
    FileText,
    ImageIcon,
    ImagePlus,
    ShieldAlert,
    Users,
} from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';

interface DashboardStats {
    posts: number;
    publishedPosts: number;
    realizations: number;
    users: number;
}

interface RecentPost {
    id: number;
    title: string;
    status: 'draft' | 'published';
    updated_at: string;
    edit_url: string;
}

interface RecentRealization {
    id: number;
    title: string;
    image_url: string;
    created_at: string;
}

interface DashboardLinks {
    createPost: string;
    managePosts: string;
    createRealization: string;
    manageRealizations: string;
}

withDefaults(
    defineProps<{
        canManageContent: boolean;
        stats?: DashboardStats;
        recentPosts?: RecentPost[];
        recentRealizations?: RecentRealization[];
        links?: DashboardLinks;
    }>(),
    {
        recentPosts: () => [],
        recentRealizations: () => [],
    },
);

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

function formatDate(value: string): string {
    return new Intl.DateTimeFormat('pl-PL', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}
</script>

<template>
    <Head title="AmperMaks panel administratora" />

    <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 md:p-8">
        <div
            class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"
        >
            <div>
                <p class="text-sm font-medium text-muted-foreground">
                    Panel administratora
                </p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight">
                    Dashboard
                </h1>
                <p class="mt-2 max-w-2xl text-muted-foreground">
                    Najważniejsze informacje i ostatnie zmiany na stronie
                    AmperMaks.
                </p>
            </div>

            <div
                v-if="canManageContent && links"
                class="grid grid-cols-1 gap-2 sm:grid-cols-2"
            >
                <Button as-child>
                    <Link :href="links.createPost">
                        <FilePlus2 class="size-4" />
                        Dodaj artykuł
                    </Link>
                </Button>
                <Button as-child variant="outline">
                    <Link :href="links.createRealization">
                        <ImagePlus class="size-4" />
                        Dodaj realizację
                    </Link>
                </Button>
            </div>
        </div>

        <Card
            v-if="!canManageContent"
            class="items-center px-6 py-12 text-center"
        >
            <div class="rounded-full bg-muted p-4">
                <ShieldAlert class="size-8 text-muted-foreground" />
            </div>
            <div>
                <CardTitle>Brak uprawnień administratora</CardTitle>
                <CardDescription class="mt-2">
                    To konto nie ma dostępu do zarządzania treścią strony.
                </CardDescription>
            </div>
        </Card>

        <template v-else-if="stats && links">
            <section
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"
                aria-label="Statystyki strony"
            >
                <Card class="gap-4 py-5">
                    <CardContent
                        class="flex items-center justify-between gap-4"
                    >
                        <div>
                            <p
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Wszystkie posty
                            </p>
                            <p class="mt-1 text-3xl font-bold">
                                {{ stats.posts }}
                            </p>
                        </div>
                        <div class="rounded-xl bg-muted p-3">
                            <FileText class="size-6 text-muted-foreground" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="gap-4 py-5">
                    <CardContent
                        class="flex items-center justify-between gap-4"
                    >
                        <div>
                            <p
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Opublikowane
                            </p>
                            <p class="mt-1 text-3xl font-bold">
                                {{ stats.publishedPosts }}
                            </p>
                        </div>
                        <div class="rounded-xl bg-emerald-500/10 p-3">
                            <CheckCircle2
                                class="size-6 text-emerald-700 dark:text-emerald-300"
                            />
                        </div>
                    </CardContent>
                </Card>

                <Card class="gap-4 py-5">
                    <CardContent
                        class="flex items-center justify-between gap-4"
                    >
                        <div>
                            <p
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Realizacje
                            </p>
                            <p class="mt-1 text-3xl font-bold">
                                {{ stats.realizations }}
                            </p>
                        </div>
                        <div class="rounded-xl bg-muted p-3">
                            <ImageIcon class="size-6 text-muted-foreground" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="gap-4 py-5">
                    <CardContent
                        class="flex items-center justify-between gap-4"
                    >
                        <div>
                            <p
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Użytkownicy
                            </p>
                            <p class="mt-1 text-3xl font-bold">
                                {{ stats.users }}
                            </p>
                        </div>
                        <div class="rounded-xl bg-muted p-3">
                            <Users class="size-6 text-muted-foreground" />
                        </div>
                    </CardContent>
                </Card>
            </section>

            <div class="grid min-w-0 gap-6 xl:grid-cols-2">
                <Card class="min-w-0 gap-0 overflow-hidden py-0">
                    <CardHeader class="border-b py-5">
                        <div
                            class="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <CardTitle>Ostatnie posty</CardTitle>
                                <CardDescription class="mt-1">
                                    Ostatnio dodane lub zmienione artykuły.
                                </CardDescription>
                            </div>
                            <Button
                                as-child
                                size="sm"
                                variant="ghost"
                                class="w-full sm:w-auto"
                            >
                                <Link :href="links.managePosts">
                                    Zarządzaj
                                    <ArrowRight class="size-4" />
                                </Link>
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div
                            v-if="recentPosts.length"
                            class="divide-y divide-border"
                        >
                            <Link
                                v-for="post in recentPosts"
                                :key="post.id"
                                :href="post.edit_url"
                                class="flex min-w-0 flex-col gap-2 px-6 py-4 transition-colors hover:bg-muted/40 focus-visible:bg-muted/40 focus-visible:outline-none sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div class="min-w-0">
                                    <p class="truncate font-medium">
                                        {{ post.title }}
                                    </p>
                                    <p
                                        class="mt-1 flex items-center gap-1.5 text-xs text-muted-foreground"
                                    >
                                        <Clock3 class="size-3.5" />
                                        {{ formatDate(post.updated_at) }}
                                    </p>
                                </div>
                                <Badge
                                    variant="outline"
                                    :class="
                                        post.status === 'published'
                                            ? 'w-fit border-emerald-600/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300'
                                            : 'w-fit border-amber-600/30 bg-amber-500/10 text-amber-700 dark:text-amber-300'
                                    "
                                >
                                    {{
                                        post.status === 'published'
                                            ? 'Opublikowany'
                                            : 'Szkic'
                                    }}
                                </Badge>
                            </Link>
                        </div>
                        <div v-else class="px-6 py-12 text-center">
                            <FileText
                                class="mx-auto size-8 text-muted-foreground"
                            />
                            <p class="mt-3 font-medium">Brak postów</p>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Dodaj pierwszy artykuł, aby pojawił się tutaj.
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card class="min-w-0 gap-0 overflow-hidden py-0">
                    <CardHeader class="border-b py-5">
                        <div
                            class="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <CardTitle>Ostatnie realizacje</CardTitle>
                                <CardDescription class="mt-1">
                                    Najnowsze zdjęcia widoczne na stronie
                                    głównej.
                                </CardDescription>
                            </div>
                            <Button
                                as-child
                                size="sm"
                                variant="ghost"
                                class="w-full sm:w-auto"
                            >
                                <Link :href="links.manageRealizations">
                                    Zarządzaj
                                    <ArrowRight class="size-4" />
                                </Link>
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="p-4 sm:p-6">
                        <div
                            v-if="recentRealizations.length"
                            class="grid grid-cols-1 gap-4 sm:grid-cols-2"
                        >
                            <article
                                v-for="realization in recentRealizations"
                                :key="realization.id"
                                class="min-w-0 overflow-hidden rounded-xl border bg-card"
                            >
                                <div
                                    class="aspect-[4/3] overflow-hidden bg-muted"
                                >
                                    <img
                                        :src="realization.image_url"
                                        :alt="realization.title"
                                        class="h-full w-full object-cover"
                                        loading="lazy"
                                    />
                                </div>
                                <div class="p-3">
                                    <h3 class="truncate text-sm font-semibold">
                                        {{ realization.title }}
                                    </h3>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        {{ formatDate(realization.created_at) }}
                                    </p>
                                </div>
                            </article>
                        </div>
                        <div v-else class="py-8 text-center">
                            <ImageIcon
                                class="mx-auto size-8 text-muted-foreground"
                            />
                            <p class="mt-3 font-medium">Brak realizacji</p>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Dodaj pierwsze zdjęcie wykonanej pracy.
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </template>
    </div>
</template>
