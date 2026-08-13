<script setup lang="ts">
import { Search } from 'lucide-vue-next';
import ClientAppLayout from '@/layouts/ClientAppLayout.vue';

defineOptions({ layout: ClientAppLayout });
</script>

<template>
    <section class="relative bg-[#0B1F3A0D]">
        <div
            class="mx-auto w-full max-w-[1600px] px-6 pt-16 pb-24 lg:pt-24 lg:pb-32">
            <div class="mx-auto max-w-4xl text-center">
                <p class="inline-flex items-center gap-3 rounded-full bg-white px-6 py-3 mb-6 text-lg font-medium text-[#0B1F3A]">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#F5A623]" aria-hidden="true" />
                    Blog AmperMaks
                </p>

                <h1
                    class="text-4xl font-extrabold tracking-tight text-[#0B1F3A] sm:text-5xl lg:text-6xl"
                >
                    Wiedza od praktyków,
                    <span class="text-[#F5A623]">bez lania wody.</span>
                </h1>

                <p
                    class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-slate-600"
                >
                    Szukasz odpowiedzi na pytanie związane z instalacjami
                    elektrycznymi, pomiarami lub awariami? Znajdź interesujący
                    Cię artykuł.
                </p>

                <!-- Search -->

                <div class="mx-auto mt-12 max-w-3xl">
                    <form
                        class="flex flex-col gap-3 rounded-2xl bg-white p-3 shadow-xl ring-1 ring-slate-200 transition-shadow focus-within:ring-2 focus-within:ring-[#F5A623] md:flex-row"

                    >
                        <div class="flex flex-1 items-center gap-3 rounded-xl px-5">
                            <Search class="size-5 shrink-0 text-slate-400" aria-hidden="true" />
                            <input
                                type="text"
                                placeholder="Szukaj artykułów..."
                                aria-label="Szukaj artykułów na blogu"
                                class="h-14 w-full border-none bg-transparent px-0 text-base text-slate-900 placeholder:text-slate-400 focus:border-none! focus:outline-none! focus:ring-0!"
                            />
                        </div>

                        <button
                            type="submit"
                            class="h-14 shrink-0 rounded-xl bg-[#F5A623] px-8 font-semibold text-[#0B1F3A] transition-colors hover:bg-[#D88E12] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#F5A623] focus-visible:ring-offset-2"
                        >
                            Szukaj
                        </button>
                    </form>
                </div>

                <!-- Popular searches -->

                <div class="mt-6 flex flex-wrap justify-center gap-3 text-sm">
                    <button
                        class="rounded-full bg-white px-4 py-2 text-slate-600 transition hover:bg-[#F5A623] hover:text-[#0B1F3A]"
                    >
                        Najnowsze
                    </button>

                    <button
                        class="rounded-full bg-white px-4 py-2 text-slate-600 transition hover:bg-[#F5A623] hover:text-[#0B1F3A]"
                    >
                        Najstarsze
                    </button>
                    <button
                        class="rounded-full bg-white px-4 py-2 text-slate-600 transition hover:bg-[#F5A623] hover:text-[#0B1F3A]"
                    >
                        Ten tydzień
                    </button>

                    <button
                        class="rounded-full bg-white px-4 py-2 text-slate-600 transition hover:bg-[#F5A623] hover:text-[#0B1F3A]"
                    >
                        Ostatni miesiąc
                    </button>
                    <button
                        class="rounded-full bg-white px-4 py-2 text-slate-600 transition hover:bg-[#F5A623] hover:text-[#0B1F3A]"
                    >
                        Ten rok
                    </button>

                </div>
            </div>
        </div>

        <!-- Wave -->

        <div class="absolute inset-x-0 bottom-0 z-30 translate-y-2">
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

    <!-- Placeholder pod przyszłe wpisy -->

    <section class="bg-white py-20">
        <div class="mx-auto w-full max-w-[1600px] px-6">
            <div class="mb-10">
                <h2
                    class="text-3xl font-extrabold tracking-tight text-[#0B1F3A]"
                >
                    Najnowsze artykuły
                </h2>

                <p class="mt-3 text-slate-600">
                    Ostatnio opublikowane poradniki i artykuły.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 xl:grid-cols-3">
                <div
                    v-for="i in 6"
                    :key="i"
                    class="h-80 rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50"
                />
            </div>
        </div>
    </section>
</template>