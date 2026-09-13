<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ImageIcon, Save, Trash2, Upload } from '@lucide/vue';
import { computed, ref } from 'vue';
import RealizationImagePicker from '@/components/admin/RealizationImagePicker.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

interface GalleryImage {
    id: number;
    url: string;
    sort_order: number;
    delete_url: string;
}

interface DeletableImage {
    delete_url: string;
    is_cover: boolean;
}

interface RealizationDetails {
    id: number;
    title: string;
    description: string;
    cover: {
        url: string;
        delete_url: string | null;
    };
    images: GalleryImage[];
}

const props = defineProps<{
    realization: RealizationDetails;
    storeImagesUrl: string;
    indexUrl: string;
    maxUploadSizeMb: number;
    maxImagesPerUpload: number;
    allowedImageTypes: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Galeria realizacji',
                href: '/dashboard/realizations',
            },
        ],
    },
});

const form = useForm<{ images: File[] }>({
    images: [],
});
const deleteDialogOpen = ref(false);
const deleting = ref(false);
const selectedImage = ref<DeletableImage | null>(null);

const imageError = computed(
    () =>
        form.errors.images ??
        Object.entries(form.errors).find(([key]) =>
            key.startsWith('images.'),
        )?.[1],
);

function updateImages(images: File[]): void {
    form.images = images;
    const imageErrorKeys = Object.keys(form.errors).filter((key) =>
        key.startsWith('images'),
    ) as ('images' | `images.${number}`)[];

    form.clearErrors(...imageErrorKeys);
}

function submit(): void {
    form.post(props.storeImagesUrl, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

function confirmImageDeletion(deleteUrl: string, isCover = false): void {
    selectedImage.value = {
        delete_url: deleteUrl,
        is_cover: isCover,
    };
    deleteDialogOpen.value = true;
}

function deleteImage(): void {
    if (!selectedImage.value) {
        return;
    }

    router.delete(selectedImage.value.delete_url, {
        preserveScroll: true,
        onStart: () => {
            deleting.value = true;
        },
        onSuccess: () => {
            deleteDialogOpen.value = false;
            selectedImage.value = null;
        },
        onFinish: () => {
            deleting.value = false;
        },
    });
}
</script>

<template>
    <Head :title="`Galeria: ${realization.title}`" />

    <div class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 md:p-8">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <div class="min-w-0">
                <p class="text-sm font-medium text-muted-foreground">
                    Galeria realizacji
                </p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight break-words">
                    {{ realization.title }}
                </h1>
                <p class="mt-2 max-w-3xl text-muted-foreground">
                    {{ realization.description }}
                </p>
            </div>
            <Button
                as-child
                variant="outline"
                class="w-full shrink-0 sm:w-auto"
            >
                <Link :href="indexUrl">
                    <ArrowLeft class="size-4" />
                    Wróć do listy
                </Link>
            </Button>
        </div>

        <Card>
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <ImageIcon class="size-5" />
                    Obecne zdjęcia
                </CardTitle>
                <CardDescription>
                    Okładka pozostaje pierwszym zdjęciem publicznej galerii.
                    Każde zdjęcie możesz usunąć osobno; okładkę dopiero wtedy,
                    gdy istnieje zdjęcie, które może ją zastąpić.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div
                    class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4"
                >
                    <div
                        class="relative overflow-hidden rounded-xl border bg-muted"
                    >
                        <img
                            :src="realization.cover.url"
                            :alt="`Okładka: ${realization.title}`"
                            class="aspect-[4/3] w-full object-cover"
                            decoding="async"
                        />
                        <span
                            class="absolute bottom-2 left-2 rounded-md bg-background/90 px-2 py-1 text-xs font-semibold text-foreground shadow-sm backdrop-blur"
                        >
                            Okładka
                        </span>
                        <Button
                            v-if="realization.cover.delete_url"
                            type="button"
                            size="icon-sm"
                            variant="destructive"
                            class="absolute top-2 right-2 shadow-md"
                            aria-label="Usuń okładkę i wybierz kolejne zdjęcie"
                            @click="
                                confirmImageDeletion(
                                    realization.cover.delete_url,
                                    true,
                                )
                            "
                        >
                            <Trash2 class="size-4" />
                        </Button>
                    </div>

                    <div
                        v-for="(image, index) in realization.images"
                        :key="image.id"
                        class="group relative overflow-hidden rounded-xl border bg-muted"
                    >
                        <img
                            :src="image.url"
                            :alt="`${realization.title} — zdjęcie ${index + 2}`"
                            class="aspect-[4/3] w-full object-cover"
                            loading="lazy"
                            decoding="async"
                        />
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="destructive"
                            class="absolute top-2 right-2 shadow-md"
                            :aria-label="`Usuń zdjęcie ${index + 2}`"
                            @click="confirmImageDeletion(image.delete_url)"
                        >
                            <Trash2 class="size-4" />
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>

        <form @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle>Dodaj zdjęcia do galerii</CardTitle>
                    <CardDescription>
                        Nowe zdjęcia zostaną dopisane na końcu galerii. Okładka
                        nie zostanie zmieniona.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-5">
                    <RealizationImagePicker
                        input-id="gallery-images"
                        :model-value="form.images"
                        :allowed-image-types="allowedImageTypes"
                        :max-files="maxImagesPerUpload"
                        :max-upload-size-mb="maxUploadSizeMb"
                        :disabled="form.processing"
                        :error="imageError"
                        @update:model-value="updateImages"
                    />

                    <div class="flex justify-end border-t pt-5">
                        <Button
                            type="submit"
                            class="w-full sm:w-auto"
                            :disabled="
                                form.processing || form.images.length === 0
                            "
                        >
                            <Upload v-if="!form.processing" class="size-4" />
                            <Save v-else class="size-4 animate-pulse" />
                            {{
                                form.processing
                                    ? 'Zapisywanie…'
                                    : 'Dodaj zdjęcia'
                            }}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </form>
    </div>

    <Dialog v-model:open="deleteDialogOpen">
        <DialogContent :show-close-button="!deleting">
            <DialogHeader>
                <DialogTitle>Usunąć zdjęcie?</DialogTitle>
                <DialogDescription>
                    <template v-if="selectedImage?.is_cover">
                        Czy na pewno chcesz usunąć okładkę? Pierwsze dodatkowe
                        zdjęcie zostanie nową okładką realizacji. Tej operacji
                        nie można cofnąć.
                    </template>
                    <template v-else>
                        Czy na pewno chcesz usunąć to zdjęcie z galerii?
                        Realizacja i pozostałe zdjęcia pozostaną bez zmian. Tej
                        operacji nie można cofnąć.
                    </template>
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
                    @click="deleteImage"
                >
                    <Trash2 class="size-4" />
                    {{ deleting ? 'Usuwanie…' : 'Usuń zdjęcie' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
