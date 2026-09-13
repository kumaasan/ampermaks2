<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ImageIcon, Save, Upload, X } from '@lucide/vue';
import { computed } from 'vue';
import RealizationImagePicker from '@/components/admin/RealizationImagePicker.vue';
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

const props = defineProps<{
    storeUrl: string;
    indexUrl: string;
    maxUploadSizeMb: number;
    maxImagesPerUpload: number;
    allowedImageTypes: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Nowa realizacja',
                href: '/dashboard/realizations/create',
            },
        ],
    },
});

const form = useForm<{
    title: string;
    description: string;
    images: File[];
}>({
    title: '',
    description: '',
    images: [],
});

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
    form.post(props.storeUrl, {
        forceFormData: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Nowa realizacja" />

    <div class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4 md:p-8">
        <div>
            <p class="text-sm font-medium text-muted-foreground">Realizacje</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight">
                Dodaj realizację
            </h1>
            <p class="mt-2 text-muted-foreground">
                Okładka i opis pojawią się w sekcji „Realizacje”, a pozostałe
                zdjęcia utworzą galerię realizacji.
            </p>
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle>Szczegóły realizacji</CardTitle>
                    <CardDescription>
                        Użyj krótkiego tytułu i opisu najważniejszych wykonanych
                        prac.
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
                            :disabled="form.processing"
                            autocomplete="off"
                            placeholder="Np. Wymiana instalacji elektrycznej"
                            :aria-invalid="Boolean(form.errors.title)"
                        />
                        <div class="flex justify-between gap-3">
                            <InputError :message="form.errors.title" />
                            <span class="ml-auto text-xs text-muted-foreground">
                                {{ form.title.length }}/160
                            </span>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Krótki opis</Label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            maxlength="300"
                            rows="4"
                            required
                            :disabled="form.processing"
                            placeholder="Krótko opisz zakres wykonanych prac."
                            class="w-full resize-y rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50 aria-invalid:border-destructive aria-invalid:ring-destructive/20 dark:bg-input/30 dark:aria-invalid:ring-destructive/40"
                            :aria-invalid="Boolean(form.errors.description)"
                        />
                        <div class="flex justify-between gap-3">
                            <InputError :message="form.errors.description" />
                            <span class="ml-auto text-xs text-muted-foreground">
                                {{ form.description.length }}/300
                            </span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <ImageIcon class="size-5" />
                        Zdjęcia
                    </CardTitle>
                    <CardDescription>
                        Pierwsze wybrane zdjęcie zostanie okładką kafelka.
                        Najlepszy efekt da format 4:3, np. 1600 × 1200 px.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <RealizationImagePicker
                        input-id="images"
                        :model-value="form.images"
                        :allowed-image-types="allowedImageTypes"
                        :max-files="maxImagesPerUpload"
                        :max-upload-size-mb="maxUploadSizeMb"
                        :disabled="form.processing"
                        :error="imageError"
                        first-is-cover
                        @update:model-value="updateImages"
                    />
                </CardContent>
            </Card>

            <div
                class="sticky bottom-4 z-10 flex flex-col-reverse gap-3 rounded-xl border bg-background/95 p-4 shadow-lg backdrop-blur sm:flex-row sm:items-center sm:justify-end"
            >
                <Button
                    type="button"
                    variant="ghost"
                    :disabled="form.processing"
                    as-child
                >
                    <Link :href="indexUrl">
                        <X class="size-4" />
                        Anuluj
                    </Link>
                </Button>
                <Button type="submit" :disabled="form.processing">
                    <Upload v-if="!form.processing" class="size-4" />
                    <Save v-else class="size-4 animate-pulse" />
                    {{ form.processing ? 'Zapisywanie…' : 'Dodaj realizację' }}
                </Button>
            </div>
        </form>
    </div>
</template>
