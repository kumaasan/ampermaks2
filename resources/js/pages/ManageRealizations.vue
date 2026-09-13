<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ImageIcon, ImagePlus, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
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

interface RealizationSummary {
    id: number;
    title: string;
    description: string;
    image_url: string;
    images_count: number;
    created_at: string;
    edit_url: string;
    delete_url: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedRealizations {
    data: RealizationSummary[];
    current_page: number;
    from: number | null;
    last_page: number;
    links: PaginationLink[];
    to: number | null;
    total: number;
}

defineProps<{
    realizations: PaginatedRealizations;
    createUrl: string;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Realizacje',
                href: '/dashboard/realizations',
            },
        ],
    },
});

const deleteDialogOpen = ref(false);
const deleting = ref(false);
const selectedRealization = ref<RealizationSummary | null>(null);

function openDeleteDialog(realization: RealizationSummary): void {
    selectedRealization.value = realization;
    deleteDialogOpen.value = true;
}

function deleteRealization(): void {
    if (!selectedRealization.value) {
        return;
    }

    router.delete(selectedRealization.value.delete_url, {
        preserveScroll: true,
        onStart: () => {
            deleting.value = true;
        },
        onSuccess: () => {
            deleteDialogOpen.value = false;
            selectedRealization.value = null;
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
    <Head title="Zarządzanie realizacjami" />

    <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 md:p-8">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
        >
            <div>
                <p class="text-sm font-medium text-muted-foreground">
                    Strona główna
                </p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight">
                    Zarządzanie realizacjami
                </h1>
                <p class="mt-2 text-muted-foreground">
                    Dodawaj zdjęcia wykonanych prac i decyduj, co zobaczą
                    klienci.
                </p>
            </div>
            <Button as-child class="w-full sm:w-auto">
                <Link :href="createUrl">
                    <ImagePlus class="size-4" />
                    Nowa realizacja
                </Link>
            </Button>
        </div>

        <div
            v-if="realizations.data.length"
            class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3"
        >
            <Card
                v-for="realization in realizations.data"
                :key="realization.id"
                class="min-w-0 gap-0 overflow-hidden py-0"
            >
                <div class="aspect-[4/3] overflow-hidden bg-muted">
                    <img
                        :src="realization.image_url"
                        :alt="realization.title"
                        class="h-full w-full object-cover"
                        loading="lazy"
                    />
                </div>
                <CardContent class="flex flex-1 flex-col gap-4 p-5">
                    <div class="min-w-0 flex-1">
                        <h2 class="text-lg font-semibold break-words">
                            {{ realization.title }}
                        </h2>
                        <p
                            class="mt-2 text-sm leading-relaxed break-words text-muted-foreground"
                        >
                            {{ realization.description }}
                        </p>
                    </div>
                    <div
                        class="flex flex-col gap-3 border-t pt-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <p class="text-xs text-muted-foreground">
                            Dodano {{ formatDate(realization.created_at) }} ·
                            {{ realization.images_count }}
                            {{
                                realization.images_count === 1
                                    ? 'zdjęcie'
                                    : 'zdjęć'
                            }}
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <Button as-child size="sm" variant="outline">
                                <Link :href="realization.edit_url">
                                    <ImagePlus class="size-4" />
                                    Galeria
                                </Link>
                            </Button>
                            <Button
                                type="button"
                                size="sm"
                                variant="destructive"
                                @click="openDeleteDialog(realization)"
                            >
                                <Trash2 class="size-4" />
                                Usuń
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Card v-else class="items-center gap-4 px-6 py-12 text-center">
            <div class="rounded-full bg-muted p-4">
                <ImageIcon class="size-8 text-muted-foreground" />
            </div>
            <div>
                <h2 class="text-lg font-semibold">Brak realizacji</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Dodaj pierwszą realizację, aby pojawiła się na stronie
                    głównej.
                </p>
            </div>
            <Button as-child>
                <Link :href="createUrl">Dodaj realizację</Link>
            </Button>
        </Card>

        <nav
            v-if="realizations.last_page > 1"
            class="flex flex-wrap items-center justify-center gap-2"
            aria-label="Paginacja realizacji"
        >
            <template v-for="link in realizations.links" :key="link.label">
                <Button
                    v-if="link.url"
                    as-child
                    size="sm"
                    :variant="link.active ? 'default' : 'outline'"
                >
                    <Link
                        :href="link.url"
                        preserve-scroll
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
            <template v-if="realizations.total">
                Wyświetlono {{ realizations.from }}–{{ realizations.to }} z
                {{ realizations.total }} realizacji.
            </template>
            <template v-else>Brak realizacji do wyświetlenia.</template>
        </p>
    </div>

    <Dialog v-model:open="deleteDialogOpen">
        <DialogContent :show-close-button="!deleting">
            <DialogHeader>
                <DialogTitle>Usunąć realizację?</DialogTitle>
                <DialogDescription>
                    Czy na pewno chcesz usunąć realizację
                    <strong class="font-semibold text-foreground">
                        „{{ selectedRealization?.title }}”</strong
                    >? Okładka i wszystkie zdjęcia galerii również zostaną
                    usunięte. Tej operacji nie można cofnąć.
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
                    @click="deleteRealization"
                >
                    <Trash2 class="size-4" />
                    {{ deleting ? 'Usuwanie…' : 'Usuń realizację' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
