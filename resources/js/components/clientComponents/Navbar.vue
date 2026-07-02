<script setup lang="ts"></script>

<template>
    <nav class="border-b border-white/5 bg-[#070A12] py-4">
        <div
            class="mx-auto flex max-w-[1200px] items-center justify-between px-5"
        >
            <a href="/" class="flex items-center gap-2">
                <div class="h-8 w-8">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="#0B0F1A"
                        stroke-width="2.5"
                        stroke-linecap="round"
                    >
                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                    </svg>
                </div>
                <span class="text-xl font-bold tracking-tight text-white"
                    >AMPER<span class="text-[#ffd63e]">MAKS</span></span
                >
            </a>

            <div class="flex items-center gap-8">
                <div class="hidden gap-8 md:flex">
                    <a
                        href="#"
                        class="text-[13px] font-medium tracking-[0.08em] text-gray-400 uppercase transition-colors hover:text-[#ffd63e]"
                        >Home</a
                    >
                    <a
                        href="#"
                        class="text-[13px] font-medium tracking-[0.08em] text-gray-400 uppercase transition-colors hover:text-[#ffd63e]"
                        >Usługi</a
                    >
                    <a
                        href="#"
                        class="text-[13px] font-medium tracking-[0.08em] text-gray-400 uppercase transition-colors hover:text-[#ffd63e]"
                        >O nas</a
                    >
                    <a
                        href="#"
                        class="text-[13px] font-medium tracking-[0.08em] text-gray-400 uppercase transition-colors hover:text-[#ffd63e]"
                        >Kontakt</a
                    >
                </div>
                <a
                    href="tel:+48XXXXXXXXX"
                    class="rounded-md bg-[#ffd63e] px-5 py-2.5 text-[13px] font-bold tracking-[0.06em] text-[#070A12] transition-all hover:-translate-y-0.5 hover:bg-[#ffd63e]"
                >
                    Zadzwoń teraz
                </a>
            </div>
        </div>
    </nav>
</template>