<script setup lang="ts">
import Hero from '@/components/clientComponents/Hero.vue'
import ServiceCard from '@/components/clientComponents/ServiceCards.vue'
import WhyUs from '@/components/clientComponents/WhyUs.vue'
import ClientAppLayout from '@/layouts/ClientAppLayout.vue';

defineOptions({layout: ClientAppLayout});

</script>

<template>
    <div>
        <Hero/>
        <WhyUs/>
        <ServiceCard/>
    </div>
</template>


