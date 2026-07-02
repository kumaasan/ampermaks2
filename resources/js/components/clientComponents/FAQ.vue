<script setup lang="ts"></script>

<template>
    <section class="bg-gray-50 px-5 py-20 md:px-10 lg:px-20">
        <div class="mx-auto max-w-[1200px]">
            <div class="grid grid-cols-1 gap-12 lg:grid-cols-2">
                <div>
                    <span
                        class="mb-4 text-[12px] font-bold tracking-[0.14em] text-[#ffd63e] uppercase"
                        >FAQ</span
                    >
                    <h2
                        class="mb-4 text-4xl font-black tracking-tighter text-slate-900 uppercase md:text-6xl"
                    >
                        Często<br /><span class="text-[#ffd63e]"
                            >Zadawane<br />Pytania</span
                        >
                    </h2>
                    <p class="mt-4 text-sm text-gray-500">
                        Masz inne pytanie? Zadzwoń lub napisz – chętnie
                        doradzimy.
                    </p>
                </div>

                <div class="space-y-6">
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <details class="group">
                            <summary
                                class="flex cursor-pointer list-none items-center justify-between font-bold"
                            >
                                Ile kosztuje wizyta elektryka na Śląsku?
                                <span
                                    class="transition-transform group-open:rotate-180"
                                >
                                    <svg
                                        class="h-5 w-5"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        viewBox="0 0 24 24"
                                    >
                                        <path d="M6 9l6 6 6-6" />
                                    </svg>
                                </span>
                            </summary>
                            <div
                                class="mt-4 text-sm leading-relaxed text-gray-600"
                            >
                                Wycena jest bezpłatna i bez zobowiązań. Koszt
                                robocizny zależy od zakresu prac i jest ustalany
                                indywidualnie po oględzinach...
                            </div>
                        </details>
                        <!-- Add more details here -->
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>