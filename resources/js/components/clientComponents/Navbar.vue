<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Menu, X, Phone } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted } from 'vue';

// Numer telefonu i etykieta CTA jako props — łatwo podmienić bez grzebania w kodzie
defineProps({
    phone: {
        type: String,
        default: '510 186 483',
    },
    phoneHref: {
        type: String,
        default: 'tel:510186483',
    },
    ctaLabel: {
        type: String,
        default: 'Bezpłatna wycena',
    },
});

// Linki nawigacji — dodanie nowej sekcji = dodanie jednego wpisu tutaj
const navLinks = [
    { label: 'Home', href: '/', mobileOnly: true },
    { label: 'Usługi', href: '/#uslugi' },
    { label: 'Realizacje', href: '/#realizacje' },
    { label: 'Nagrody', href: '/#nagrody' },
    { label: 'Kontakt', href: '/kontakt' },
    { label: 'FAQ', href: '/faq' },
    { label: 'Blog', href: '/blog' },
];

const isScrolled = ref(false);
const isMobileMenuOpen = ref(false);

function handleScroll() {
    isScrolled.value = window.scrollY > 12;
}

function closeMobileMenu() {
    isMobileMenuOpen.value = false;
}

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

// Paleta AmperMaks (literalne HEX, niezależne od globalnego motywu projektu):
// granat #0B1F3A, granat-jasny #12345D, bursztyn #F5A623, bursztyn-hover #D88E12
</script>

<template>
    <header
        class="fixed inset-x-0 top-0 z-50 transition-colors duration-300"
        :class="
            isScrolled
                ? 'border-b border-slate-200 bg-[#F2F3F4]/90 shadow-sm backdrop-blur-md'
                : 'border-b border-transparent bg-white/70 backdrop-blur-sm'
        "
    >
        <div class="mx-auto grid w-full max-w-[1600px] gap-10 px-6">
            <div class="flex h-16 items-center justify-between lg:h-20">
                <!-- Logo -->
                <Link
                    href="/"
                    class="flex shrink-0 items-center gap-2"
                    aria-label="AmperMaks - strona główna"
                >
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-lg"
                    >
                        <img alt="AmperMaks logo" src="/logo2.png" />
                    </span>
                    <span
                        class="text-lg font-bold tracking-tight text-[#0B1F3A]"
                        >AmperMaks</span
                    >
                </Link>

                <!-- Nawigacja desktop -->
                <nav
                    class="hidden items-center gap-8 lg:flex"
                    aria-label="Nawigacja główna"
                >
                    <Link
                        v-for="link in navLinks"
                        :key="link.href"
                        :href="link.href"
                        :class="[
                            'rounded-sm text-sm font-medium text-slate-600 transition-colors hover:text-[#0B1F3A] focus-visible:ring-2 focus-visible:ring-[#F5A623] focus-visible:outline-none',
                            link.mobileOnly ? 'lg:hidden' : '',
                        ]"
                    >
                        {{ link.label }}
                    </Link>
                </nav>

                <!-- Telefon + CTA (desktop) -->
                <div class="hidden items-center gap-5 lg:flex">
                    <a
                        :href="phoneHref"
                        class="flex items-center gap-2 rounded-sm text-sm font-semibold text-[#0B1F3A] transition-colors hover:text-[#D88E12] focus-visible:ring-2 focus-visible:ring-[#F5A623] focus-visible:outline-none"
                    >
                        <Phone class="h-4 w-4" aria-hidden="true" />
                        {{ phone }}
                    </a>
                    <Link
                        href="/kontakt"
                        class="inline-flex items-center justify-center rounded-lg bg-[#F5A623] px-5 py-2.5 text-sm font-semibold text-[#0B1F3A] shadow-sm transition-colors hover:bg-[#D88E12] focus-visible:ring-2 focus-visible:ring-[#F5A623] focus-visible:ring-offset-2 focus-visible:outline-none"
                    >
                        {{ ctaLabel }}
                    </Link>
                </div>

                <!-- Przycisk menu mobilnego -->
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg p-2 text-[#0B1F3A] hover:bg-slate-100 focus-visible:ring-2 focus-visible:ring-[#F5A623] focus-visible:outline-none lg:hidden"
                    :aria-expanded="isMobileMenuOpen"
                    aria-controls="mobile-menu"
                    aria-label="Otwórz menu nawigacyjne"
                    @click="isMobileMenuOpen = !isMobileMenuOpen"
                >
                    <Menu
                        v-if="!isMobileMenuOpen"
                        class="h-6 w-6"
                        aria-hidden="true"
                    />
                    <X v-else class="h-6 w-6" aria-hidden="true" />
                </button>
            </div>
        </div>

        <!-- Panel menu mobilnego -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div
                v-if="isMobileMenuOpen"
                id="mobile-menu"
                class="border-t border-slate-200 bg-white px-4 pt-4 pb-6 shadow-lg lg:hidden"
            >
                <nav class="flex flex-col gap-1" aria-label="Nawigacja mobilna">
                    <a
                        v-for="link in navLinks"
                        :key="link.href"
                        :href="link.href"
                        class="rounded-lg px-3 py-2.5 text-base font-medium text-slate-700 hover:bg-slate-50 hover:text-[#0B1F3A] focus-visible:ring-2 focus-visible:ring-[#F5A623] focus-visible:outline-none"
                        @click="closeMobileMenu"
                    >
                        {{ link.label }}
                    </a>
                </nav>

                <div
                    class="mt-4 flex flex-col gap-3 border-t border-slate-200 pt-4"
                >
                    <a
                        :href="phoneHref"
                        class="flex items-center justify-center gap-2 rounded-lg border border-slate-200 px-4 py-3 text-base font-semibold text-[#0B1F3A] focus-visible:ring-2 focus-visible:ring-[#F5A623] focus-visible:outline-none"
                    >
                        <Phone class="h-5 w-5" aria-hidden="true" />
                        {{ phone }}
                    </a>
                    <a
                        href="#kontakt"
                        class="flex items-center justify-center rounded-lg bg-[#F5A623] px-4 py-3 text-base font-semibold text-[#0B1F3A] focus-visible:ring-2 focus-visible:ring-[#F5A623] focus-visible:ring-offset-2 focus-visible:outline-none"
                        @click="closeMobileMenu"
                    >
                        {{ ctaLabel }}
                    </a>
                </div>
            </div>
        </Transition>
    </header>

    <!-- Odstęp kompensujący fixed header, żeby Hero nie chował się pod nim -->
    <div class="h-16 lg:h-20" aria-hidden="true" />
</template>
