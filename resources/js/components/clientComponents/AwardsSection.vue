<script setup lang="ts">
import { computed } from 'vue';
import type { PropType } from 'vue';

// Nagrody przekazywane przez props. Dopóki lista jest pusta, sekcja renderuje
// skeleton zamiast podstawiać fałszywe zdjęcia trofeów/certyfikatów.
// Docelowo: <AwardsSection :awards="[{ image: '/img/orly-elektryki-2024.jpg', title: 'Orły Elektryki 2024' }]" />
// Zdjęcia mogą mieć różne proporcje — kafelek używa object-contain, więc żadne nie jest przycinane.

interface Award {
    image: string;
    title: string;
    description: string;
}
const props = defineProps({
    awards: {
        type: Array as PropType<Award[]>,
        default: () => [],
    },
});

const hasAwards = computed(() => props.awards.length > 0);
</script>

<template>
    <section id="nagrody" class="relative overflow-hidden bg-white">
        <!-- Fala na górze sekcji — inna amplituda niż dolna, dla lekkiej asymetrii -->
        <div class="absolute inset-x-0 top-0 z-30 lg:-translate-y-2">
            <svg
                viewBox="0 0 1440 120"
                class="block h-10 w-full lg:h-24"
                preserveAspectRatio="none"
            >
                <path
                    d="M0,120 C180,35 420,-5 720,45 C980,90 1200,15 1440,25 L1440,0 L0,0 Z"
                    fill="#F2F3F4"
                />
            </svg>
        </div>

        <div
            class="relative z-10 mx-auto w-full max-w-400 px-6 pt-16 pb-20 lg:pt-28 lg:pb-32"
        >
            <div class="mx-auto max-w-2xl text-center">
                <p
                    class="mb-6 inline-flex items-center gap-3 rounded-full bg-[#0B1F3A0D] px-6 py-3 text-lg font-medium text-[#0B1F3A]"
                >
                    <span
                        class="h-1.5 w-1.5 rounded-full bg-[#F5A623]"
                        aria-hidden="true"
                    />
                    Nagrody i wyróżnienia
                </p>
                <h2
                    class="text-3xl font-extrabold tracking-tight text-[#0B1F3A] sm:text-4xl"
                >
                    Nagrodzeni w konkursie Orły Elektryki
                </h2>
                <p class="mt-4 text-lg leading-relaxed text-slate-600">
                    Nasza praca została doceniona przez branżę elektryczną w
                    ogólnopolskim konkursie Orły Elektryki — potwierdzenie
                    jakości, którą realizujemy na co dzień.
                </p>
            </div>

            <!-- zdjęcia nagród, gdy przekazano awards -->
            <div
                v-if="hasAwards"
                class="mt-12 flex flex-wrap justify-center gap-6"
            >
                <figure
                    v-for="award in props.awards"
                    :key="award.title"
                    class="w-full max-w-xs rounded-2xl bg-(--bg) p-5 transition-all duration-300 ease-out hover:ring-2 hover:ring-amber-400 sm:w-[320px] lg:w-[340px]"
                >
                    <div class="flex h-40 items-center justify-center">
                        <img
                            :src="award.image"
                            :alt="award.title"
                            class="max-h-full max-w-full object-contain"
                            loading="lazy"
                        />
                    </div>

                    <div class="mt-4 text-center">
                        <h3 class="text-xl font-extrabold text-[#0B1F3A]">
                            {{ award.title }}
                        </h3>

                        <p
                            v-if="award.description"
                            class="mt-2 text-sm leading-relaxed font-medium text-slate-600"
                        >
                            {{ award.description }}
                        </p>
                    </div>
                </figure>
            </div>
        </div>
    </section>
</template>
