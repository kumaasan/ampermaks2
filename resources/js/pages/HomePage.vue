<script setup lang="ts">
import AwardsSection from '@/components/clientComponents/AwardsSection.vue';
import Hero from '@/components/clientComponents/Hero.vue';
import ProcessSteps from '@/components/clientComponents/ProcessSteps.vue';
import ServiceCard from '@/components/clientComponents/ServiceCards.vue';
import ClientAppLayout from '@/layouts/ClientAppLayout.vue';

defineOptions({ layout: ClientAppLayout });

const awards = [
    {
        image: '/awards/logo_gold_pl_400.png',
        title: 'Złota nagroda konkursu Orły Elektryki',
        description:
            'Złota nagroda dla naszej firmy na rok 2026 co srawia, że jesteśmy w 3,5% najlepszych firm w tej branży w Polsce.',
    },
    {
        image: 'awards/logo_pl_400.png',
        title: 'Laureat konkursu Orły Elektryki',
        description:
            'Jesteśmy laureatami lokalnego konkursu Orły Elektryki - Świętochłowice w roku 2026',
    },
];
</script>

<template>
    <div>
        <Hero />
        <ServiceCard />
        <ProcessSteps :projects="[]" />
        <AwardsSection :awards="awards" />
    </div>
</template>
