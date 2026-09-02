<script setup lang="ts">
import { Building2, MapPin, Phone } from 'lucide-vue-next';
import ClientAppLayout from '@/layouts/ClientAppLayout.vue';

defineOptions({ layout: ClientAppLayout });

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

const company = {
    name: 'AmperMaks',
    taxId: 'NIP: 628 103 54 27',
    street: 'ul. Przykładowa 1',
    city: '41-600 Świętochłowice',
};

function handleSubmit() {
    // placeholder na logikę wysyłki formularza
}
</script>

<template>
    <section id="kontakt" class="bg-(--bg)">
        <div class="mx-auto w-full max-w-[1600px] px-6 py-12 lg:py-8">
            <div class="mx-auto max-w-2xl text-center">
                <p
                    class="inline-flex items-center gap-3 rounded-full bg-white px-6 py-3 mb-6 text-lg font-medium text-[#0B1F3A]"
                >
                    <span
                        class="h-1.5 w-1.5 rounded-full bg-[#F5A623]"
                        aria-hidden="true"
                    />
                    Kontakt
                </p>
                <h2
                    class="text-3xl font-extrabold tracking-tight text-[#0B1F3A] sm:text-4xl"
                >
                    Skontaktuj się z nami
                </h2>
                <p class="mt-4 text-lg leading-relaxed text-slate-600">
                    Opisz, czego potrzebujesz — oddzwonimy i przygotujemy wycenę
                    tego samego dnia roboczego.
                </p>
            </div>

            <div class="mt-12 grid rounded-2xl bg-white gap-8 lg:grid-cols-12 lg:gap-1">
                <!-- dodac do formularza CSRF token! -->
                <div
                    class="p-6 sm:p-8 lg:col-span-7 lg:p-10">
                    <form class="space-y-6" @submit.prevent="handleSubmit">
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label
                                    for="firstName"
                                    class="mb-2 block text-sm font-semibold text-[#0B1F3A]"
                                >
                                    Imię
                                </label>
                                <input
                                    id="firstName"
                                    type="text"
                                    name="firstName"
                                    autocomplete="given-name"
                                    required
                                    placeholder="Jan"
                                    class="block w-full rounded-lg border border-slate-300 bg-(--bg) px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#F5A623] focus:ring-2 focus:ring-[#F5A623] focus:outline-none"
                                />
                            </div>
                            <div>
                                <label
                                    for="lastName"
                                    class="mb-2 block text-sm font-semibold text-[#0B1F3A]"
                                >
                                    Nazwisko
                                </label>
                                <input
                                    id="lastName"
                                    type="text"
                                    name="lastName"
                                    autocomplete="family-name"
                                    required
                                    placeholder="Kowalski"
                                    class="block w-full rounded-lg border border-slate-300 bg-(--bg) px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#F5A623] focus:ring-2 focus:ring-[#F5A623] focus:outline-none"
                                />
                            </div>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label
                                    for="email"
                                    class="mb-2 block text-sm font-semibold text-[#0B1F3A]"
                                >
                                    Adres e-mail
                                </label>
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    autocomplete="email"
                                    required
                                    placeholder="jan@email.pl"
                                    class="block w-full rounded-lg border border-slate-300 bg-(--bg) px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#F5A623] focus:ring-2 focus:ring-[#F5A623] focus:outline-none"
                                />
                            </div>
                            <div>
                                <label
                                    for="phoneNumber"
                                    class="mb-2 block text-sm font-semibold text-[#0B1F3A]"
                                >
                                    Numer telefonu
                                </label>
                                <input
                                    id="phoneNumber"
                                    type="tel"
                                    name="phoneNumber"
                                    autocomplete="tel"
                                    required
                                    placeholder="+48 500 100 200"
                                    class="block w-full rounded-lg border border-slate-300 bg-(--bg) px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#F5A623] focus:ring-2 focus:ring-[#F5A623] focus:outline-none"
                                />
                            </div>
                        </div>

                        <div>
                            <label
                                for="message"
                                class="mb-2 block text-sm font-semibold text-[#0B1F3A]"
                            >
                                Twoja wiadomość
                            </label>
                            <textarea
                                id="message"
                                name="message"
                                rows="6"
                                required
                                placeholder="Opisz, czego potrzebujesz..."
                                class="block w-full rounded-lg border border-slate-300 bg-(--bg) px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[#F5A623] focus:ring-2 focus:ring-[#F5A623] focus:outline-none"
                            ></textarea>
                        </div>

                        <p class="text-sm text-slate-500">
                            Wysyłając ten formularz, akceptujesz naszą
                            <a
                                href="#"
                                class="font-semibold text-[#0B1F3A] underline decoration-[#F5A623] decoration-2 underline-offset-4 hover:text-[#D88E12]"
                            >
                                politykę prywatności
                            </a>
                            dotyczącą przetwarzania danych osobowych w celu
                            obsługi zapytania.
                        </p>

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-[#F5A623] px-6 py-3.5 text-sm font-semibold text-[#0B1F3A] shadow-sm transition-colors hover:bg-[#D88E12] focus-visible:ring-2 focus-visible:ring-[#F5A623] focus-visible:ring-offset-2 focus-visible:outline-none sm:w-fit"
                        >
                            Wyślij wiadomość
                        </button>
                    </form>
                </div>

                <!-- Dane kontaktowe -->
                <div class="flex flex-col justify-center gap-10 lg:col-span-5">
                    <div class="flex flex-col items-center text-center">
                        <span
                            class="flex size-11 items-center justify-center rounded-lg bg-white shadow-sm"
                        >
                            <Building2
                                class="size-5 text-[#0B1F3A]"
                                aria-hidden="true"
                            />
                        </span>
                        <p class="mt-4 text-base font-bold text-[#0B1F3A]">
                            Dane firmy
                        </p>
                        <p class="mt-1 text-sm text-slate-600">
                            {{ company.name }}
                        </p>
                        <p class="text-sm text-slate-600">
                            {{ company.taxId }}
                        </p>
                    </div>

                    <div class="flex flex-col items-center text-center">
                        <span
                            class="flex size-11 items-center justify-center rounded-lg bg-white shadow-sm"
                        >
                            <MapPin
                                class="size-5 text-[#0B1F3A]"
                                aria-hidden="true"
                            />
                        </span>
                        <p class="mt-4 text-base font-bold text-[#0B1F3A]">
                            Adres
                        </p>
                        <p class="mt-1 text-sm text-slate-600">
                            {{ company.street }}
                        </p>
                        <p class="text-sm text-slate-600">{{ company.city }}</p>
                    </div>

                    <div class="flex flex-col items-center text-center">
                        <span
                            class="flex size-11 items-center justify-center rounded-lg bg-white shadow-sm"
                        >
                            <Phone
                                class="size-5 text-[#0B1F3A]"
                                aria-hidden="true"
                            />
                        </span>
                        <p class="mt-4 text-base font-bold text-[#0B1F3A]">
                            Zadzwoń do nas
                        </p>
                        <p class="mt-1 max-w-xs text-sm text-slate-600">
                            Chętnie odpowiemy na pytania i pomożemy dobrać
                            zakres usługi.
                        </p>
                        <a
                            :href="phoneHref"
                            class="mt-2 text-base font-bold text-[#0B1F3A] hover:text-[#D88E12] focus-visible:ring-2 focus-visible:ring-[#F5A623] focus-visible:outline-none"
                        >
                            {{ phone }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>