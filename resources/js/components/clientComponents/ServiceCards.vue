<script setup lang="ts"></script>
<template>
    <section class="bg-white px-5 py-20 md:px-10 lg:px-20">
        <div class="mx-auto max-w-[1200px]">
            <div class="mb-12">
                <span
                    class="mb-4 flex items-center gap-2.5 text-[12px] font-bold tracking-[0.14em] text-[#ffd63e] uppercase"
                >
                    <span class="h-[2px] w-7 rounded-sm bg-[#ffd63e]"></span>
                    Nasze Usługi
                </span>
                <h2
                    class="text-4xl font-black tracking-tighter text-slate-900 uppercase md:text-6xl"
                >
                    Profesjonalne<br /><span class="text-[#ffd63e]"
                        >Rozwiązania</span
                    >
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                <!-- Example Card (Repeat for others) -->
                <article
                    class="rounded-xl border border-gray-100 bg-gray-50 p-8 transition-shadow hover:shadow-xl"
                >
                    <div class="mb-6 text-[#ffd63e]">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke-width="2"
                            stroke-linecap="round"
                        >
                            <rect x="2" y="7" width="20" height="14" rx="2" />
                            <path
                                d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"
                            />
                        </svg>
                    </div>
                    <h3 class="mb-4 text-xl font-bold">Remonty instalacji</h3>
                    <p class="mb-6 leading-relaxed text-gray-600">
                        Wymiana starej instalacji aluminiowej lub miedzianej,
                        modernizacja rozdzielnic, wymiana gniazdek i łączników.
                    </p>
                    <div class="mb-6 flex flex-wrap gap-2">
                        <span
                            class="rounded bg-gray-200 px-2 py-1 text-[10px] font-bold"
                            >Stare budownictwo</span
                        >
                        <span
                            class="rounded bg-gray-200 px-2 py-1 text-[10px] font-bold"
                            >Bloki</span
                        >
                    </div>
                    <div
                        class="flex items-center justify-between border-t pt-4"
                    >
                        <span class="font-bold">Zapytaj o wycenę</span>
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke-width="2.5"
                            stroke-linecap="round"
                            class="h-5 w-5"
                        >
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                    </div>
                </article>
                <!-- Repeat cards here -->
            </div>
        </div>
    </section>
</template>