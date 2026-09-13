<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ImageIcon, Save, Upload, X } from '@lucide/vue';
import { onBeforeUnmount, ref } from 'vue';
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
    image: File | null;
}>({
    title: '',
    description: '',
    image: null,
});
const previewUrl = ref<string | null>(null);

function setImage(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }

    form.image = file;
    previewUrl.value = file ? URL.createObjectURL(file) : null;
    form.clearErrors('image');
}

function submit(): void {
    form.post(props.storeUrl, {
        forceFormData: true,
        preserveScroll: true,
    });
}

onBeforeUnmount(() => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }
});
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
                Dodane zdjęcie i opis pojawią się w sekcji „Realizacje” na
                stronie głównej.
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
                        Zdjęcie
                    </CardTitle>
                    <CardDescription>
                        Najlepszy efekt da zdjęcie 4:3 o rozmiarze 1600 × 1200
                        px. Inne proporcje zostaną bezpiecznie przycięte w
                        kafelku bez deformowania obrazu.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="image">Wybierz zdjęcie</Label>
                        <input
                            id="image"
                            type="file"
                            required
                            :disabled="form.processing"
                            :accept="allowedImageTypes.join(',')"
                            class="block w-full min-w-0 cursor-pointer rounded-md border border-input bg-transparent text-sm text-muted-foreground shadow-xs file:mr-4 file:border-0 file:border-r file:border-input file:bg-muted file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-foreground hover:file:bg-accent focus-visible:ring-3 focus-visible:ring-ring/50 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-destructive aria-invalid:ring-3 aria-invalid:ring-destructive/20 dark:bg-input/30 dark:aria-invalid:ring-destructive/40"
                            :aria-invalid="Boolean(form.errors.image)"
                            @change="setImage"
                        />
                        <p class="text-xs text-muted-foreground">
                            JPG, PNG lub WebP, maksymalnie
                            {{ maxUploadSizeMb }} MB.
                        </p>
                        <InputError :message="form.errors.image" />
                    </div>

                    <div
                        v-if="previewUrl"
                        class="overflow-hidden rounded-xl border bg-muted"
                    >
                        <img
                            :src="previewUrl"
                            alt="Podgląd wybranego zdjęcia"
                            class="aspect-[4/3] w-full object-cover"
                        />
                    </div>
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
