<script setup lang="ts">
import { CheckCircle2, MapPinCheck, Phone } from 'lucide-vue-next';

// Telefon jako prop — spójny z SiteHeader, łatwo nadpisać w Home.vue
defineProps({
    phone: {
        type: String,
        default: '510 186 483',
    },
    phoneHref: {
        type: String,
        default: 'tel:510186483',
    },
});

const trustPoints = [
    'Uprawnienia SEP E i D',
    'Wieloletnie doświadczenia',
    '500+ zrealizowanych zleceń',
];

// Paleta AmperMaks (literalne HEX, niezależne od globalnego motywu projektu):
// granat #0B1F3A, granat-jasny #12345D, bursztyn #F5A623, sukces #16A34A
</script>

<template>
    <section class="bg-(--bg)">
        <div
            class="mx-auto grid w-full max-w-[1600px] gap-10 px-6 py-8 lg:grid-cols-12 lg:gap-12 lg:py-24"
        >
            <!-- Kolumna tekstowa -->
            <div class="flex flex-col justify-center lg:col-span-7">
                <p
                    class="mb-4 inline-flex w-fit items-center gap-2 rounded-full bg-[#0B1F3A0D] px-3 py-1 text-sm font-medium text-[#0B1F3A]"
                >
                    <span
                        class="h-1.5 w-1.5 rounded-full bg-[#F5A623] font-extrabold"
                        aria-hidden="true"
                    />
                    Elektryk Świętochłowice i okolice
                </p>

                <h1
                    class="text-4xl font-extrabold tracking-tight text-[#0B1F3A] sm:text-5xl lg:text-6xl"
                >
                    Elektryk, na którego
                    <span class="text-[#F5A623]">możesz liczyć</span> — dziś i
                    za 10 lat
                </h1>

                <p
                    class="mt-6 max-w-xl text-lg leading-relaxed font-medium text-slate-600"
                >
                    Kompleksowe usługi elektryczne dla domów i firm na Śląsku —
                    instalacje, modernizacje, awarie i pomiary. Uprawnienia SEP
                    E i D, szybka reakcja i pełna gwarancja na wykonaną pracę.
                </p>

                <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                    <a
                        :href="phoneHref"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#F5A623] px-6 py-3.5 text-base font-semibold text-[#0B1F3A] shadow-sm transition-colors hover:bg-[#D88E12] focus-visible:ring-2 focus-visible:ring-[#F5A623] focus-visible:ring-offset-2 focus-visible:outline-none"
                    >
                        <Phone class="h-5 w-5" aria-hidden="true" />
                        Zadzwoń teraz: {{ phone }}
                    </a>
                    <a
                        href="#kontakt"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-6 py-3.5 text-base font-semibold text-[#0B1F3A] transition-colors hover:border-[#0B1F3A] focus-visible:ring-2 focus-visible:ring-[#F5A623] focus-visible:outline-none"
                    >
                        Wyceń zlecenie
                    </a>
                </div>

                <ul class="mt-8 flex flex-wrap gap-x-6 gap-y-3">
                    <li
                        v-for="point in trustPoints"
                        :key="point"
                        class="flex items-center gap-2 text-sm font-medium text-slate-600"
                    >
                        <CheckCircle2
                            strokeWidth="3"
                            class="size-5 shrink-0 text-[#16A34A]"
                            aria-hidden="true"
                        />
                        {{ point }}
                    </li>
                </ul>
            </div>

            <!-- Kolumna wizualna -->
            <div class="relative lg:col-span-5">
                <div
                    class="relative aspect-[4/5] overflow-hidden rounded-2xl bg-[#0B1F3A] lg:aspect-auto lg:h-full"
                >
                    <!-- Abstrakcyjna grafika obwodu elektrycznego - do podmiany na realne zdjęcie realizacji -->
                    <svg
                        class="absolute inset-0 h-full w-full opacity-40"
                        viewBox="0 0 400 500"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true"
                    >
                        <path
                            d="M40 60 H180 V140 H320 V220"
                            stroke="#F5A623"
                            stroke-width="2"
                        />
                        <path
                            d="M40 220 H140 V320 H280 V420"
                            stroke="#12345D"
                            stroke-width="2"
                        />
                        <circle cx="180" cy="140" r="5" fill="#F5A623" />
                        <circle cx="320" cy="220" r="5" fill="#F5A623" />
                        <circle cx="140" cy="320" r="5" fill="#12345D" />
                    </svg>

                    <div class="absolute inset-0 flex items-end p-6">
                        <div class="w-full rounded-xl bg-white p-5 shadow-lg">
                            <div class="flex items-center gap-3">
                                <span
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-[#16A34A1A]"
                                >
                                    <MapPinCheck
                                        class="h-5 w-5 text-[#16A34A]"
                                        aria-hidden="true"
                                    />
                                </span>
                                <div>
                                    <p
                                        class="text-sm font-semibold text-[#0B1F3A]"
                                    >
                                        Znajdujemy się w Świętochłowicach
                                    </p>
                                    <p class="text-sm text-slate-600">
                                        Dojazd na terenie Śląska
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>