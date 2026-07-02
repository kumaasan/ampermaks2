<script setup lang="ts"></script>

<template>
    <footer class="border-t border-white/5 bg-[#070A12] pt-20 pb-10 text-white">
        <div class="mx-auto max-w-[1200px] px-5">
            <div class="mb-16 grid grid-cols-1 gap-12 md:grid-cols-4">
                <div class="md:col-span-1">
                    <a href="/" class="mb-6 flex items-center gap-2">
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
                        <span
                            class="text-xl font-bold tracking-tight text-white"
                            >AMPER<span class="text-[#ffd63e]">MAKS</span></span
                        >
                    </a>
                    <p class="mb-6 text-sm leading-relaxed text-gray-400">
                        Profesjonalne usługi elektryczne na Śląsku. Uprawnienia
                        SEP E i D, ponad 10 lat doświadczenia.
                    </p>
                    <div class="flex flex-col gap-2 text-sm text-gray-400">
                        <div class="flex items-center gap-2">
                            <svg
                                class="h-4 w-4 stroke-current"
                                fill="none"
                                stroke-width="2"
                            >
                                <path
                                    d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.02 1.18 2 2 0 012 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 14v2.92z"
                                />
                            </svg>
                            <a href="tel:+48XXXXXXXXX">+48 XXX XXX XXX</a>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-8 md:col-span-3">
                    <div>
                        <h4
                            class="mb-6 text-sm font-bold tracking-widest text-white uppercase"
                        >
                            Usługi
                        </h4>
                        <ul class="space-y-3 text-sm text-gray-400">
                            <li>
                                <a href="#" class="transition hover:text-white"
                                    >Nowe instalacje</a
                                >
                            </li>
                            <li>
                                <a href="#" class="transition hover:text-white"
                                    >Remonty instalacji</a
                                >
                            </li>
                            <li>
                                <a href="#" class="transition hover:text-white"
                                    >Usuwanie awarii</a
                                >
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4
                            class="mb-6 text-sm font-bold tracking-widest text-white uppercase"
                        >
                            Firma
                        </h4>
                        <ul class="space-y-3 text-sm text-gray-400">
                            <li>
                                <a href="#" class="transition hover:text-white"
                                    >O nas</a
                                >
                            </li>
                            <li>
                                <a href="#" class="transition hover:text-white"
                                    >Realizacje</a
                                >
                            </li>
                            <li>
                                <a href="#" class="transition hover:text-white"
                                    >Kontakt</a
                                >
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4
                            class="mb-6 text-sm font-bold tracking-widest text-white uppercase"
                        >
                            Obszar
                        </h4>
                        <ul class="space-y-3 text-sm text-gray-400">
                            <li>
                                <a href="#" class="transition hover:text-white"
                                    >Świętochłowice</a
                                >
                            </li>
                            <li>
                                <a href="#" class="transition hover:text-white"
                                    >Chorzów</a
                                >
                            </li>
                            <li>
                                <a href="#" class="transition hover:text-white"
                                    >Katowice</a
                                >
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div
                class="flex items-center justify-between border-t border-white/5 pt-8 text-xs text-gray-500"
            >
                <p>© 2025 Ampermaks. Wszelkie prawa zastrzeżone.</p>
                <div class="flex gap-4">
                    <a href="#">Polityka prywatności</a>
                    <a href="#">Regulamin</a>
                </div>
            </div>
        </div>
    </footer>
</template>