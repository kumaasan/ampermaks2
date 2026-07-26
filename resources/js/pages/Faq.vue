<script setup lang="ts">
import { ChevronDown } from 'lucide-vue-next';
import { ref } from 'vue';
import ClientAppLayout from '@/layouts/ClientAppLayout.vue';

// Treść inspirowana obecnym FAQ na ampermaks.pl, przepisana i rozbudowana
// (naturalny język, bez upychania słów kluczowych) + kilka dodatkowych pytań
// istotnych dla konwersji (faktura, godziny pracy poza standardowymi).
//
// Uwaga SEO: FAQPage JSON-LD celowo nie jest tu dodane — lepiej wstrzyknąć je
// w Home.vue przez <Head> z @inertiajs/vue3, żeby trafiło realnie do <head> strony,
// zamiast wklejać <script type="application/ld+json"> w środku sekcji.
defineOptions({ layout: ClientAppLayout });

const faqs = [
    {
        question: 'Jakie usługi elektryczne wykonujecie?',
        answer: 'Zajmujemy się pełnym zakresem prac elektrycznych — od usuwania awarii, przez montaż nowych instalacji i ich modernizację, po pomiary ochronne i wymianę rozdzielnic. Obsługujemy zarówno domy i mieszkania, jak i firmy oraz lokale usługowe.',
    },
    {
        question: 'Czy przygotowanie wyceny jest bezpłatne?',
        answer: 'Tak. Przy prostszych zleceniach wycenę możemy przedstawić już telefonicznie, a przy większym zakresie prac umawiamy się na oględziny na miejscu — w obu przypadkach nic to nie kosztuje.',
    },
    {
        question:
            'Zajmujecie się awariami elektrycznymi? Jak szybko można liczyć na pomoc?',
        answer: 'Tak, usuwanie awarii to jedna z naszych podstawowych usług. Najlepiej zadzwonić od razu — na podstawie opisu usterki ocenimy priorytet zgłoszenia i ustalimy najbliższy możliwy termin dojazdu.',
    },
    {
        question:
            'Czy doradzacie przy modernizacji starszej instalacji elektrycznej?',
        answer: 'Oczywiście. Sprawdzamy stan obecnej instalacji, wskazujemy, co wymaga wymiany ze względów bezpieczeństwa, i planujemy prace etapami, tak aby dało się normalnie korzystać z mieszkania lub budynku w trakcie modernizacji.',
    },
    {
        question: 'Czy wykonujecie pomiary elektryczne z protokołem?',
        answer: 'Tak — wykonujemy pomiary skuteczności ochrony przeciwporażeniowej oraz rezystancji izolacji, a wyniki przekazujemy w formie protokołu, który można przedłożyć np. ubezpieczycielowi lub zarządcy budynku.',
    },
    {
        question: 'Czy udzielacie gwarancji na wykonane prace?',
        answer: 'Tak, każda wykonana przez nas usługa objęta jest gwarancją. Jej dokładny zakres i czas trwania zależą od rodzaju zlecenia i ustalamy go jeszcze przed rozpoczęciem prac.',
    },
    {
        question: 'Czy wystawiacie faktury za wykonane usługi?',
        answer: 'Tak, do każdego zlecenia — zarówno dla klientów indywidualnych, jak i firm — wystawiamy fakturę lub rachunek.',
    },
    {
        question: 'Czy można umówić wizytę poza standardowymi godzinami pracy?',
        answer: 'W miarę możliwości staramy się dopasować do grafiku klienta, szczególnie firm, które potrzebują prac poza swoimi godzinami działania. Wystarczy to ustalić podczas kontaktu.',
    },
];

// Pierwsze pytanie otwarte domyślnie — zachęca do rozwinięcia kolejnych.
// number | null zadeklarowane jawnie: ref(0) samo zawęziłoby typ do "number",
// a toggle() poniżej musi też móc przypisać null przy zwijaniu.
const openIndex = ref<number | null>(0);

function toggle(index: number) {
    openIndex.value = openIndex.value === index ? null : index;
}
</script>

<template>
    <section id="faq" class="bg-(--bg)">
        <div class="mx-auto w-full max-w-[1600px] px-6 py-16 lg:py-8">
            <div class="mx-auto max-w-2xl text-center">
                <p
                    class="mb-6 inline-flex items-center gap-3 rounded-full bg-white px-6 py-3 text-lg font-medium text-[#0B1F3A]"
                >
                    <span
                        class="h-1.5 w-1.5 rounded-full bg-[#F5A623]"
                        aria-hidden="true"
                    />
                    Faq
                </p>
                <h2
                    class="text-3xl font-extrabold tracking-tight text-[#0B1F3A] sm:text-4xl"
                >
                    Najczęściej zadawane pytania
                </h2>
                <p class="mt-4 text-lg leading-relaxed text-slate-600">
                    Nie znalazłeś odpowiedzi na swoje pytanie? Zadzwoń — chętnie
                    wyjaśnimy wszystkie wątpliwości.
                </p>
            </div>

            <div
                class="mx-auto mt-12 max-w-3xl rounded-2xl bg-white p-5 sm:p-8 lg:p-10"
            >
                <div class="divide-y divide-slate-200">
                    <div v-for="(faq, index) in faqs" :key="faq.question">
                        <h3>
                            <button
                                type="button"
                                class="flex w-full items-center justify-between gap-4 py-5 text-left text-base font-semibold text-[#0B1F3A] focus-visible:ring-2 focus-visible:ring-[#F5A623] focus-visible:outline-none"
                                :aria-expanded="openIndex === index"
                                :aria-controls="`faq-panel-${index}`"
                                @click="toggle(index)"
                            >
                                {{ faq.question }}
                                <ChevronDown
                                    class="size-5 shrink-0 text-slate-400 transition-transform duration-200"
                                    :class="
                                        openIndex === index ? 'rotate-180' : ''
                                    "
                                    aria-hidden="true"
                                />
                            </button>
                        </h3>
                        <div
                            v-show="openIndex === index"
                            :id="`faq-panel-${index}`"
                            role="region"
                            class="pr-10 pb-5 text-sm leading-relaxed text-slate-600"
                        >
                            {{ faq.answer }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>