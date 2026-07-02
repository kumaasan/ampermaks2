<script setup lang="ts"></script>

<template>
    <section class="bg-white px-5 py-20 md:px-10 lg:px-20">
        <div class="mx-auto max-w-[1200px]">
            <span
                class="mb-4 flex items-center gap-2.5 text-[12px] font-bold tracking-[0.14em] text-[#ffd63e] uppercase"
            >
                <span class="h-[2px] w-7 rounded-sm bg-[#ffd63e]"></span> Jak
                pracujemy
            </span>
            <h2
                class="mb-4 text-4xl font-black tracking-tighter text-slate-900 uppercase md:text-6xl"
            >
                Prosty<br /><span class="text-[#ffd63e]">Proces Zlecenia</span>
            </h2>
            <p class="mb-16 max-w-xl text-gray-500">
                Od pierwszego kontaktu do odbioru gotowej instalacji – bez
                komplikacji.
            </p>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
                <div class="space-y-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-[#ffd63e]"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke-width="2"
                            stroke-linecap="round"
                            class="h-6 w-6"
                        >
                            <path
                                d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.02 1.18 2 2 0 012 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 14v2.92z"
                            />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold">Kontakt i opis</h3>
                    <p class="text-sm text-gray-600">
                        Zadzwoń lub napisz – opisz problem lub zakres pracy.
                        Odpiszemy w ciągu godziny.
                    </p>
                </div>
                <!-- Repeat for 3 more steps -->
            </div>
        </div>
    </section>
</template>