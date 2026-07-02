<script setup lang="ts"></script>

<template>
    <section class="bg-[#f8f9fa] px-5 py-20 md:px-10 lg:px-20">
        <div class="mx-auto max-w-[1200px]">
            <div class="grid grid-cols-1 items-start gap-16 lg:grid-cols-2">
                <div>
                    <span
                        class="mb-4 flex items-center gap-2.5 text-[12px] font-bold tracking-[0.14em] text-[#ffd63e] uppercase"
                    >
                        <span
                            class="h-[2px] w-7 rounded-sm bg-[#ffd63e]"
                        ></span>
                        Dlaczego Ampermaks
                    </span>
                    <h2
                        class="mb-8 text-4xl font-black tracking-tighter text-slate-900 uppercase md:text-6xl"
                    >
                        Co Nas<br /><span class="text-[#ffd63e]">Wyróżnia</span>
                    </h2>
                    <div class="space-y-8">
                        <div class="flex gap-6">
                            <div class="text-3xl font-black text-gray-200">
                                01
                            </div>
                            <div>
                                <h3 class="mb-2 text-xl font-bold">
                                    Uprawnienia SEP E i D
                                </h3>
                                <p class="text-gray-600">
                                    Posiadamy aktualne świadectwa kwalifikacyjne
                                    SEP do 1kV i powyżej 1kV – gwarancja
                                    legalnej i bezpiecznej pracy.
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-6">
                            <div class="text-3xl font-black text-gray-200">
                                02
                            </div>
                            <div>
                                <h3 class="mb-2 text-xl font-bold">
                                    Gwarancja na wykonane prace
                                </h3>
                                <p class="text-gray-600">
                                    Każda nasza realizacja objęta jest pełną
                                    gwarancją. Wystawiamy protokoły pomiarów i
                                    dokumentację powykonawczą.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-gray-100 bg-white p-8 shadow-sm"
                >
                    <div class="mb-6 border-b pb-4 text-xl font-bold">
                        Uprawnienia i certyfikaty
                    </div>
                    <div class="space-y-4">
                        <div
                            class="flex items-center justify-between rounded-lg bg-gray-50 p-3"
                        >
                            <div>
                                <div class="font-bold">Świadectwo SEP – E</div>
                                <div class="text-sm text-gray-500">
                                    Eksploatacja do 1kV
                                </div>
                            </div>
                            <span
                                class="rounded bg-green-500 px-2 py-1 text-[10px] font-bold text-white"
                                >Aktywne</span
                            >
                        </div>
                        <!-- Add more cert rows here -->
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>