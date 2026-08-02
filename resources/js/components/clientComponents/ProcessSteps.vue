<script setup lang="ts">
import { Image as ImageIcon } from 'lucide-vue-next';
import { computed } from 'vue';

interface Project {
    title: string;
    description: string;
    image: string;
}

// Bez wartości domyślnej defineProps<T>() dałoby props.projects === undefined,
// gdyby ktoś zapomniał je przekazać w Home.vue — computed niżej by wtedy wyleciał.
// withDefaults zabezpiecza to i utrzymuje działający fallback na skeleton.
const props = withDefaults(defineProps<{ projects: Project[] }>(), {
    projects: () => [],
});

const hasProjects = computed(() => props.projects.length > 0);

// 6, nie 7 — żeby siatka 3-kolumnowa (i 2-kolumnowa na tablecie) zawsze
// zamykała się pełnymi rzędami, zarówno w skeletonie, jak i z realnymi zdjęciami.
const skeletonCount = 6;
</script>

<template>
    <section id="realizacje" class="relative bg-[#0B1F3A0D]">
        <!-- Fala na górze sekcji — inna amplituda niż dolna, dla lekkiej asymetrii -->
        <div class="absolute inset-x-0 top-0 z-30 lg:-translate-y-2">
            <svg
                viewBox="0 0 1440 120"
                class="block h-10 w-full lg:h-24"
                preserveAspectRatio="none"
            >
                <path
                    d="M0,120 C260,10 480,10 760,70 C980,116 1160,132 1440,52 L1440,0 L0,0 Z"
                    fill="#ffffff"
                />
            </svg>
        </div>

        <div class="mx-auto w-full max-w-[1600px] px-6 py-20 lg:py-32">
            <div class="mx-auto max-w-2xl text-center">
                <p
                    class="mb-6 inline-flex items-center gap-3 rounded-full bg-white px-6 py-3 text-lg font-medium text-[#0B1F3A]"
                >
                    <span
                        class="h-1.5 w-1.5 rounded-full bg-[#F5A623]"
                        aria-hidden="true"
                    />
                    Realizacje
                </p>
                <h2
                    class="text-3xl font-extrabold tracking-tight text-[#0B1F3A] sm:text-4xl"
                >
                    Zobacz, jak wyglądają nasze realizacje
                </h2>
                <p class="mt-4 text-lg leading-relaxed text-slate-600">
                    {{
                        hasProjects
                            ? 'Kilka przykładów prac, które zrealizowaliśmy ostatnio — zobacz efekt.'
                            : 'Zdjęcia z ostatnich realizacji pojawią się tutaj wkrótce — bezpośrednio z placu budowy, bez podkolorowanych zdjęć stockowych.'
                    }}
                </p>
            </div>

            <!-- Realna galeria — karta 1:1 jak w skeletonie poniżej (ten sam p-4, h-48, odstępy) -->
            <div
                v-if="hasProjects"
                class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:gap-8"
            >
                <figure
                    v-for="project in projects"
                    :key="project.title"
                    class="overflow-hidden rounded-2xl bg-white p-4"
                >
                    <div
                        class="h-64 w-full overflow-hidden rounded-xl bg-slate-100"
                    >
                        <img
                            :src="project.image"
                            :alt="project.title"
                            class="h-full w-full object-cover"
                            loading="lazy"
                        />
                    </div>
                    <figcaption>
                        <p class="mt-4 text-md font-bold text-[#0B1F3A]">
                            {{ project.title }}
                        </p>
                        <p class="mt-2.5 text-sm text-slate-600">
                            {{ project.description }}
                        </p>
                    </figcaption>
                </figure>
            </div>

            <!-- Skeleton dopóki brak realnych zdjęć -->
            <div
                v-else
                class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:gap-8"
                role="status"
            >
                <div
                    v-for="n in skeletonCount"
                    :key="n"
                    class="animate-pulse overflow-hidden rounded-2xl bg-white p-4"
                >
                    <div
                        class="flex h-48 w-full items-center justify-center rounded-xl bg-slate-200"
                    >
                        <ImageIcon
                            class="size-8 text-slate-400"
                            aria-hidden="true"
                        />
                    </div>
                    <div class="mt-4 h-3 w-3/5 rounded-full bg-slate-200" />
                    <div class="mt-2.5 h-2.5 w-2/5 rounded-full bg-slate-200" />
                </div>
                <span class="sr-only">Ładowanie zdjęć realizacji...</span>
            </div>
        </div>
    </section>
</template>