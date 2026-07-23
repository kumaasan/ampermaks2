<script setup lang="ts">
import {
    ArrowRight,
    Gauge,
    Hammer,
    Home,
    Plug,
    Siren,
    Zap,
} from 'lucide-vue-next';

// Usługi — dodanie kolejnej karty to jeden obiekt w tablicy, bez zmian w template.
// Każdy tytuł to jednocześnie nagłówek H3 pod kątem SEO.
const services = [
    {
        icon: Hammer,
        title: 'Modernizacja instalacji elektrycznej',
        description:
            'Stara instalacja bez uziemienia, przestarzałe bezpieczniki topikowe, za mało gniazdek? Modernizujemy instalacje etapowo, tak żeby dało się w budynku normalnie funkcjonować w trakcie prac.',
    },

    {
        icon: Plug,
        title: 'Nowe instalacje elektryczne',
        description:
            'Kompleksowy montaż instalacji w nowym domu, mieszkaniu lub lokalu usługowym — od projektu rozdzielnicy po ostatni punkt świetlny. Zgodnie z normami i z myślą o latach użytkowania.',
    },
    {
        icon: Siren,
        title: 'Usuwanie awarii elektrycznych',
        description:
            'Zwarcie, brak prądu, iskrzenie w gniazdku? Lokalizujemy usterkę szybko i bez zbędnego rozkuwania ścian. Dostępni również po godzinach, gdy awaria nie może czekać do jutra.',
    },

    {
        icon: Zap,
        title: 'Wymiana rozdzielnic i zabezpieczeń',
        description:
            'Wymiana starej rozdzielnicy na nową, z odpowiednio dobranymi zabezpieczeniami różnicowoprądowymi i nadprądowymi. Realny wzrost bezpieczeństwa instalacji bez ingerencji w resztę domu.',
    },
    {
        icon: Home,
        title: 'Inteligentny dom i montaż urządzeń',
        description:
            'Podłączenie klimatyzacji, ładowarek do samochodów elektrycznych, oświetlenia sterowanego z telefonu i innych urządzeń smart home — zaplanowane tak, żeby instalacja nadążała za technologią.',
    },
    {
        icon: Gauge,
        title: 'Pomiary elektryczne z protokołem',
        description:
            'Pomiary skuteczności ochrony przeciwporażeniowej, rezystancji izolacji i stanu instalacji — z protokołem gotowym do przedłożenia u ubezpieczyciela, zarządcy budynku lub podczas odbioru.',
    },
];
</script>

<template>
    <section id="uslugi" class="bg-white">
        <div
            class="mx-auto w-full max-w-[1600px] px-6 py-16 sm:px-6 lg:px-8 lg:pb-24 lg:pt-16"
        >
            <!-- Nagłówek sekcji -->
            <div class="mx-auto max-w-2xl text-center">
                <p
                    class="inline-flex items-center gap-3 rounded-full bg-[#0B1F3A0D] px-6 py-3 mb-6 text-lg font-medium text-[#0B1F3A]"
                >
                    <span
                        class="h-1.5 w-1.5 rounded-full bg-[#F5A623]"
                        aria-hidden="true"
                    />
                    Nasza oferta
                </p>
                <h2
                    class="text-3xl lg:pb-2 font-extrabold tracking-tight text-[#0B1F3A] sm:text-4xl">
                    Usługi elektryczne dla domu i firmy w jednym miejscu
                </h2>
                <p class="mt-4 text-lg leading-relaxed text-slate-600">
                    Zakres, z którym najczęściej się do nas zgłaszacie — od
                    pojedynczej awarii po pełną instalację elektryczną od
                    podstaw.
                </p>
            </div>

            <!-- Siatka usług -->
            <div
                class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:gap-8"
            >
                <article
                    v-for="service in services"
                    :key="service.title"
                    class="flex flex-col rounded-2xl bg-(--bg) p-6 transition-all duration-300 ease-out hover:ring-2 hover:ring-amber-400 sm:p-7"
                >
                    <span
                        class="flex size-11 shrink-0 items-center justify-center rounded-lg bg-white"
                    >
                        <component
                            :is="service.icon"
                            class="size-5 text-[#0B1F3A]"
                            aria-hidden="true"
                        />
                    </span>
                    <h3 class="mt-5 text-lg font-bold text-[#0B1F3A]">
                        {{ service.title }}
                    </h3>
                    <p
                        class="mt-2 flex-1 text-sm leading-relaxed text-slate-600"
                    >
                        {{ service.description }}
                    </p>
                    <a
                        href="#kontakt"
                        class="mt-5 inline-flex items-center gap-1.5 rounded-sm text-sm font-semibold text-[#0B1F3A] transition-colors hover:text-[#D88E12] focus-visible:ring-2 focus-visible:ring-[#F5A623] focus-visible:outline-none"
                    >
                        Dowiedz się więcej
                        <ArrowRight class="size-4" aria-hidden="true" />
                    </a>
                </article>
            </div>

            <!-- Mikro-CTA zamykające sekcję -->
            <p class="mt-12 text-center text-base text-slate-600">
                Nie widzisz swojej usługi na liście?
                <a
                    href="#kontakt"
                    class="pl-1 font-semibold text-[#0B1F3A] underline decoration-[#F5A623] decoration-2 underline-offset-4 hover:text-[#D88E12] focus-visible:ring-2 focus-visible:ring-[#F5A623] focus-visible:outline-none"
                >
                    Zapytaj — na pewno pomożemy.
                </a>
            </p>
        </div>
    </section>
</template>