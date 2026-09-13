<script setup lang="ts">
import { ImageIcon, Plus, X } from '@lucide/vue';
import { onBeforeUnmount, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';

interface Preview {
    file: File;
    url: string;
}

const props = withDefaults(
    defineProps<{
        modelValue: File[];
        inputId: string;
        allowedImageTypes: string[];
        maxFiles: number;
        maxUploadSizeMb: number;
        disabled?: boolean;
        error?: string;
        firstIsCover?: boolean;
    }>(),
    {
        disabled: false,
        error: undefined,
        firstIsCover: false,
    },
);

const emit = defineEmits<{
    'update:modelValue': [files: File[]];
}>();

const previews = ref<Preview[]>([]);
const selectionError = ref<string>();

function releasePreviews(): void {
    previews.value.forEach((preview) => URL.revokeObjectURL(preview.url));
}

watch(
    () => props.modelValue,
    (files) => {
        releasePreviews();
        previews.value = files.map((file) => ({
            file,
            url: URL.createObjectURL(file),
        }));
    },
    { immediate: true },
);

function addFiles(event: Event): void {
    const input = event.target as HTMLInputElement;
    const selectedFiles = Array.from(input.files ?? []);
    const remainingSlots = Math.max(
        props.maxFiles - props.modelValue.length,
        0,
    );

    selectionError.value = undefined;

    if (selectedFiles.length > remainingSlots) {
        selectionError.value = `Jednorazowo możesz wybrać maksymalnie ${props.maxFiles} zdjęć.`;
    }

    const knownFiles = new Set(
        props.modelValue.map(
            (file) => `${file.name}:${file.size}:${file.lastModified}`,
        ),
    );
    const newFiles = selectedFiles
        .filter(
            (file) =>
                !knownFiles.has(
                    `${file.name}:${file.size}:${file.lastModified}`,
                ),
        )
        .slice(0, remainingSlots);

    emit('update:modelValue', [...props.modelValue, ...newFiles]);
    input.value = '';
}

function removeFile(index: number): void {
    selectionError.value = undefined;
    emit(
        'update:modelValue',
        props.modelValue.filter((_, fileIndex) => fileIndex !== index),
    );
}

onBeforeUnmount(releasePreviews);
</script>

<template>
    <div class="space-y-4">
        <div class="grid gap-2">
            <Label :for="inputId">Wybierz zdjęcia</Label>
            <input
                :id="inputId"
                type="file"
                multiple
                :disabled="disabled || modelValue.length >= maxFiles"
                :accept="allowedImageTypes.join(',')"
                class="block w-full min-w-0 cursor-pointer rounded-md border border-input bg-transparent text-sm text-muted-foreground shadow-xs file:mr-4 file:border-0 file:border-r file:border-input file:bg-muted file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-foreground hover:file:bg-accent focus-visible:ring-3 focus-visible:ring-ring/50 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-destructive aria-invalid:ring-3 aria-invalid:ring-destructive/20 dark:bg-input/30 dark:aria-invalid:ring-destructive/40"
                :aria-invalid="Boolean(error || selectionError)"
                @change="addFiles"
            />
            <p class="text-xs text-muted-foreground">
                JPG, PNG lub WebP, maksymalnie {{ maxUploadSizeMb }} MB na plik
                i {{ maxFiles }} zdjęć jednorazowo.
            </p>
            <InputError :message="selectionError ?? error" />
        </div>

        <div
            v-if="previews.length"
            class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4"
        >
            <div
                v-for="(preview, index) in previews"
                :key="`${preview.file.name}-${preview.file.size}-${preview.file.lastModified}`"
                class="group relative min-w-0 overflow-hidden rounded-xl border bg-muted"
            >
                <img
                    :src="preview.url"
                    :alt="`Podgląd: ${preview.file.name}`"
                    class="aspect-[4/3] w-full object-cover"
                />
                <div
                    class="absolute inset-x-0 bottom-0 flex items-end justify-between gap-2 bg-linear-to-t from-black/80 to-transparent p-2 pt-8"
                >
                    <span class="truncate text-xs font-medium text-white">
                        {{
                            firstIsCover && index === 0
                                ? 'Okładka'
                                : `Zdjęcie ${index + 1}`
                        }}
                    </span>
                    <Button
                        type="button"
                        size="icon-sm"
                        variant="secondary"
                        :disabled="disabled"
                        :aria-label="`Usuń ${preview.file.name} z wyboru`"
                        @click="removeFile(index)"
                    >
                        <X class="size-4" />
                    </Button>
                </div>
            </div>
        </div>

        <div
            v-else
            class="flex min-h-36 flex-col items-center justify-center gap-2 rounded-xl border border-dashed bg-muted/40 p-6 text-center text-muted-foreground"
        >
            <div class="rounded-full bg-muted p-3">
                <ImageIcon class="size-6" />
            </div>
            <p class="text-sm font-medium">Nie wybrano jeszcze zdjęć</p>
            <p class="text-xs">Możesz zaznaczyć kilka plików w jednym oknie.</p>
        </div>

        <p
            v-if="modelValue.length"
            class="flex items-center gap-2 text-xs text-muted-foreground"
        >
            <Plus class="size-3.5" />
            Wybrano {{ modelValue.length }} z {{ maxFiles }} zdjęć.
        </p>
    </div>
</template>
