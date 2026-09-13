<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { MapPin, Phone } from 'lucide-vue-next';

const navigationLinks = [
    { label: 'Strona główna', href: '/' },
    { label: 'Usługi', href: '/#uslugi' },
    { label: 'Realizacje', href: '/#realizacje' },
    { label: 'Nagrody', href: '/#nagrody' },
    { label: 'Kontakt', href: '/kontakt' },
    { label: 'FAQ', href: '/faq' },
    { label: 'Blog', href: '/blog' },
];

const socialLinks = [
    {
        label: 'Facebook',
        href: 'https://www.facebook.com/ampermaks/',
    },
    {
        label: 'Instagram',
        href: 'https://www.instagram.com/ampermaks9',
    },
];

const currentYear = new Date().getFullYear();
</script>

<template>
    <footer class="bg-[#0B1F3A] text-white">
        <div class="mx-auto w-full max-w-[1600px] px-6 py-10 sm:py-12 lg:py-14">
            <div
                class="grid gap-10 border-b border-white/15 pb-10 md:grid-cols-2 md:gap-x-12 lg:grid-cols-12 lg:gap-x-10 lg:pb-12"
            >
                <div class="md:col-span-2 lg:col-span-5">
                    <Link
                        href="/"
                        class="inline-flex min-h-11 items-center rounded-sm text-2xl font-bold tracking-tight text-white transition-colors hover:text-[#F5A623] focus-visible:ring-2 focus-visible:ring-[#F5A623] focus-visible:ring-offset-4 focus-visible:ring-offset-[#0B1F3A] focus-visible:outline-none"
                        aria-label="AmperMaks — strona główna"
                    >
                        AmperMaks
                    </Link>
                    <span
                        class="mt-2 block h-0.5 w-10 bg-[#F5A623]"
                        aria-hidden="true"
                    />

                    <p
                        class="mt-5 max-w-xl text-sm leading-6 text-slate-300 sm:text-base sm:leading-7"
                    >
                        Kompleksowe usługi elektryczne dla domów i firm:
                        instalacje, modernizacje, awarie oraz pomiary.
                    </p>

                    <a
                        href="tel:510186483"
                        class="mt-5 inline-flex min-h-11 items-center gap-3 rounded-sm text-base font-semibold text-white transition-colors hover:text-[#F5A623] focus-visible:ring-2 focus-visible:ring-[#F5A623] focus-visible:ring-offset-4 focus-visible:ring-offset-[#0B1F3A] focus-visible:outline-none"
                    >
                        <Phone
                            class="size-5 shrink-0 text-[#F5A623]"
                            aria-hidden="true"
                        />
                        510 186 483
                    </a>
                </div>

                <nav class="lg:col-span-3" aria-label="Nawigacja w stopce">
                    <h2
                        class="text-xs font-bold tracking-[0.16em] text-slate-400 uppercase"
                    >
                        Na skróty
                    </h2>
                    <ul class="mt-4 grid grid-cols-2 gap-x-5">
                        <li
                            v-for="link in navigationLinks"
                            :key="link.href"
                            class="min-w-0"
                        >
                            <Link
                                :href="link.href"
                                class="inline-flex min-h-11 items-center rounded-sm text-sm font-medium text-slate-200 transition-colors hover:text-[#F5A623] focus-visible:ring-2 focus-visible:ring-[#F5A623] focus-visible:ring-offset-2 focus-visible:ring-offset-[#0B1F3A] focus-visible:outline-none"
                            >
                                {{ link.label }}
                            </Link>
                        </li>
                    </ul>
                </nav>

                <div class="lg:col-span-4">
                    <h2
                        class="text-xs font-bold tracking-[0.16em] text-slate-400 uppercase"
                    >
                        Obszar działania
                    </h2>
                    <p
                        class="mt-4 flex min-h-11 items-center gap-3 text-sm leading-6 text-slate-200"
                    >
                        <MapPin
                            class="size-5 shrink-0 text-[#F5A623]"
                            aria-hidden="true"
                        />
                        Świętochłowice i okolice
                    </p>

                    <h2
                        class="mt-7 text-xs font-bold tracking-[0.16em] text-slate-400 uppercase"
                    >
                        Obserwuj nas
                    </h2>
                    <ul class="mt-3 flex flex-wrap gap-x-6 gap-y-1">
                        <li v-for="social in socialLinks" :key="social.href">
                            <a
                                :href="social.href"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex min-h-11 items-center border-b border-white/20 text-sm font-semibold text-slate-200 transition-colors hover:border-[#F5A623] hover:text-[#F5A623] focus-visible:ring-2 focus-visible:ring-[#F5A623] focus-visible:ring-offset-2 focus-visible:ring-offset-[#0B1F3A] focus-visible:outline-none"
                            >
                                {{ social.label }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="flex flex-col gap-2 pt-6 text-sm text-slate-400 sm:flex-row sm:items-center sm:justify-between"
            >
                <p>
                    © {{ currentYear }} AmperMaks. Wszelkie prawa zastrzeżone.
                </p>
                <p>Usługi elektryczne dla domów i firm.</p>
            </div>
        </div>
    </footer>
</template>
