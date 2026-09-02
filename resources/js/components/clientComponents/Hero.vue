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
    'Wieloletnie doświadczenie',
    'W gronie Złotych Orłów Elektryki 2026',
];

// Paleta AmperMaks (literalne HEX, niezależne od globalnego motywu projektu):
// granat #0B1F3A, granat-jasny #12345D, bursztyn #F5A623, sukces #16A34A
</script>

<template>
    <section class="relative z-10 bg-(--bg) pb-5">
        <div
            class="mx-auto grid w-full max-w-[1600px] gap-10 px-6 py-8 lg:grid-cols-12 lg:gap-12 lg:pt-24 lg:pb-32"
        >
            <!-- Kolumna tekstowa -->
            <div class="flex flex-col justify-center lg:col-span-7">
                <p
                    class="mb-4 inline-flex w-fit items-center gap-2 rounded-full bg-white px-3 py-1 text-sm font-medium text-[#0B1F3A]"
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
                    <span class="text-[#F5A623]">możesz liczyć</span> - dziś i
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
                    class="relative aspect-4/5 overflow-hidden rounded-xl lg:aspect-auto lg:h-full"
                >
                    <!-- Mapa dojazdu. absolute + h-full/w-full zamiast sztywnych width/height
                         z Google - inaczej iframe wymusza min. 620px szerokości i rozwala layout na mobile -->
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1298.8600123160954!2d18.91648637142907!3d50.29804279210744!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x69196ec4f68eda0f%3A0xc66439357798ad74!2sMTK%20Krzysztof%20G%C5%82ombik%20-%20AmperMaks!5e0!3m2!1sen!2spl!4v1785688387653!5m2!1sen!2spl"
                        title="Mapa dojazdu do AmperMaks w Świętochłowicach"
                        class="absolute inset-0 h-full w-full border-0"
                        loading="eager"
                        referrerpolicy="strict-origin-when-cross-origin"
                    ></iframe>

                    <div
                        class="pointer-events-none absolute inset-0 flex items-end justify-center p-6"
                    >
                        <div
                            class="pointer-events-auto w-fit rounded-xl bg-white/10 px-5 py-4 shadow-lg backdrop-blur-2xl"
                        >
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
        <div class="absolute inset-x-0 bottom-0 z-30 lg:translate-y-2">
            <svg
                viewBox="0 0 1440 120"
                class="block h-10 w-full lg:h-24"
                preserveAspectRatio="none"
            >
                <path
                    d="M0,0 C220,118 420,118 720,42 C940,2 1120,-20 1440,74 L1440,120 L0,120 Z"
                    fill="#ffffff"
                />
            </svg>
        </div>
    </section>
</template>
