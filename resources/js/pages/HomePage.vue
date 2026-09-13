<script setup lang="ts">
import AwardsSection from '@/components/clientComponents/AwardsSection.vue';
import Hero from '@/components/clientComponents/Hero.vue';
import ProcessSteps from '@/components/clientComponents/ProcessSteps.vue';
import ServiceCard from '@/components/clientComponents/ServiceCards.vue';
import ClientAppLayout from '@/layouts/ClientAppLayout.vue';

defineOptions({ layout: ClientAppLayout });

interface Realization {
    id: number;
    title: string;
    description: string;
    image: string;
    show_url: string;
}

defineProps<{
    realizations: Realization[];
}>();

const awards = [
    {
        image: '/awards/logo_gold_pl_400.png',
        title: 'Złota nagroda konkursu Orły Elektryki',
        description:
            'Złota nagroda przyznana naszej firmie za 2026 rok oznacza, że znaleźliśmy się w gronie 3,5% najlepszych firm w tej branży w Polsce.',
    },
    {
        image: 'awards/logo_pl_400.png',
        title: 'Laureat konkursu Orły Elektryki',
        description:
            'W 2026 roku zostaliśmy laureatami lokalnej edycji konkursu Orły Elektryki w Świętochłowicach.',
    },
];
</script>

<template>
    <div>
        <Hero />
        <ServiceCard />
        <ProcessSteps :projects="realizations" />
        <AwardsSection :awards="awards" />
    </div>
</template>
