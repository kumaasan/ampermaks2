<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ChevronLeft, ChevronRight, X } from 'lucide-vue-next';
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';
import ClientAppLayout from '@/layouts/ClientAppLayout.vue';

interface GalleryImage {
    id: string;
    url: string;
    is_cover: boolean;
}

interface RealizationDetails {
    id: number;
    title: string;
    description: string;
    images: GalleryImage[];
}

defineOptions({ layout: ClientAppLayout });

const props = defineProps<{
    realization: RealizationDetails;
    backUrl: string;
}>();

const activeIndex = ref<number | null>(null);
const closeButton = ref<HTMLButtonElement>();
let previousBodyOverflow = '';

const isOpen = computed(() => activeIndex.value !== null);
const activeImage = computed(() =>
    activeIndex.value === null
        ? null
        : (props.realization.images[activeIndex.value] ?? null),
);
const activePosition = computed(() => (activeIndex.value ?? 0) + 1);

function openImage(index: number): void {
    activeIndex.value = index;
}

function closeLightbox(): void {
    activeIndex.value = null;
}

function previousImage(imageCount: number): void {
    if (activeIndex.value === null || imageCount < 2) {
        return;
    }

    activeIndex.value = (activeIndex.value - 1 + imageCount) % imageCount;
}

function nextImage(imageCount: number): void {
    if (activeIndex.value === null || imageCount < 2) {
        return;
    }

    activeIndex.value = (activeIndex.value + 1) % imageCount;
}

function handleKeydown(event: KeyboardEvent, imageCount: number): void {
    if (!isOpen.value) {
        return;
    }

    if (event.key === 'Escape') {
        closeLightbox();
    } else if (event.key === 'ArrowLeft') {
        previousImage(imageCount);
    } else if (event.key === 'ArrowRight') {
        nextImage(imageCount);
    }
}

const keydownListener = (event: KeyboardEvent): void =>
    handleKeydown(event, props.realization.images.length);

watch(isOpen, async (open) => {
    if (open) {
        previousBodyOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        await nextTick();
        closeButton.value?.focus();
    } else {
        document.body.style.overflow = previousBodyOverflow;
    }
});

onMounted(() => window.addEventListener('keydown', keydownListener));

onBeforeUnmount(() => {
    window.removeEventListener('keydown', keydownListener);
    document.body.style.overflow = previousBodyOverflow;
});
</script>

<template>
    <Head :title="realization.title" />

    <section class="min-h-[70vh] bg-[#F2F3F4] px-4 py-12 sm:px-6 lg:py-20">
        <div class="mx-auto w-full max-w-[1400px]">
            <Link
                :href="backUrl"
                class="inline-flex items-center gap-2 rounded-md text-sm font-semibold text-[#0B1F3A] outline-none hover:underline focus-visible:ring-2 focus-visible:ring-[#F5A623] focus-visible:ring-offset-4"
            >
                <ArrowLeft class="size-4" />
                Wróć do realizacji
            </Link>

            <div class="mt-8 max-w-3xl">
                <p
                    class="inline-flex items-center gap-3 rounded-full bg-white px-5 py-2.5 text-sm font-medium text-[#0B1F3A]"
                >
                    <span
                        class="size-1.5 rounded-full bg-[#F5A623]"
                        aria-hidden="true"
                    />
                    Realizacja
                </p>
                <h1
                    class="mt-5 text-3xl font-extrabold tracking-tight break-words text-[#0B1F3A] sm:text-4xl lg:text-5xl"
                >
                    {{ realization.title }}
                </h1>
                <p class="mt-4 text-lg leading-relaxed text-slate-600">
                    {{ realization.description }}
                </p>
            </div>

            <div
                class="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 lg:gap-6"
            >
                <button
                    v-for="(image, index) in realization.images"
                    :key="image.id"
                    type="button"
                    class="group relative min-w-0 overflow-hidden rounded-2xl bg-white p-3 text-left shadow-sm transition-shadow outline-none hover:shadow-md focus-visible:ring-4 focus-visible:ring-[#F5A623]/70"
                    :aria-label="`Otwórz zdjęcie ${index + 1} z ${realization.images.length}`"
                    @click="openImage(index)"
                >
                    <span
                        class="block aspect-[4/3] overflow-hidden rounded-xl bg-slate-200"
                    >
                        <img
                            :src="image.url"
                            :alt="`${realization.title} — zdjęcie ${index + 1}`"
                            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.02]"
                            :loading="index === 0 ? 'eager' : 'lazy'"
                            decoding="async"
                            :fetchpriority="index === 0 ? 'high' : 'auto'"
                        />
                    </span>
                    <span
                        v-if="image.is_cover"
                        class="absolute right-5 bottom-5 rounded-md bg-[#0B1F3A]/90 px-2.5 py-1 text-xs font-semibold text-white backdrop-blur"
                    >
                        Okładka
                    </span>
                </button>
            </div>
        </div>
    </section>

    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            leave-active-class="transition-opacity duration-150"
            leave-to-class="opacity-0"
        >
            <div
                v-if="activeImage"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/95 p-3 sm:p-6"
                role="dialog"
                aria-modal="true"
                :aria-label="`Podgląd zdjęcia ${activePosition} z ${realization.images.length}`"
                @click.self="closeLightbox"
            >
                <button
                    ref="closeButton"
                    type="button"
                    class="absolute top-3 right-3 z-10 inline-flex size-11 items-center justify-center rounded-full bg-white/10 text-white outline-none hover:bg-white/20 focus-visible:ring-2 focus-visible:ring-[#F5A623] sm:top-5 sm:right-5"
                    aria-label="Zamknij podgląd"
                    @click="closeLightbox"
                >
                    <X class="size-6" />
                </button>

                <button
                    v-if="realization.images.length > 1"
                    type="button"
                    class="absolute bottom-4 left-4 z-10 inline-flex size-11 items-center justify-center rounded-full bg-white/10 text-white outline-none hover:bg-white/20 focus-visible:ring-2 focus-visible:ring-[#F5A623] sm:top-1/2 sm:bottom-auto sm:left-5 sm:-translate-y-1/2"
                    aria-label="Poprzednie zdjęcie"
                    @click="previousImage(realization.images.length)"
                >
                    <ChevronLeft class="size-7" />
                </button>

                <img
                    :src="activeImage.url"
                    :alt="`${realization.title} — powiększone zdjęcie ${activePosition}`"
                    class="max-h-[calc(100dvh-8rem)] max-w-full object-contain sm:max-h-[calc(100dvh-5rem)]"
                />

                <button
                    v-if="realization.images.length > 1"
                    type="button"
                    class="absolute right-4 bottom-4 z-10 inline-flex size-11 items-center justify-center rounded-full bg-white/10 text-white outline-none hover:bg-white/20 focus-visible:ring-2 focus-visible:ring-[#F5A623] sm:top-1/2 sm:right-5 sm:bottom-auto sm:-translate-y-1/2"
                    aria-label="Następne zdjęcie"
                    @click="nextImage(realization.images.length)"
                >
                    <ChevronRight class="size-7" />
                </button>

                <p
                    class="absolute bottom-5 left-1/2 -translate-x-1/2 text-sm font-medium text-white sm:bottom-6"
                >
                    {{ activePosition }} / {{ realization.images.length }}
                </p>
            </div>
        </Transition>
    </Teleport>
</template>
