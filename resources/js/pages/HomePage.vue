<script setup lang="ts">
import ServiceCard from '@/components/clientComponents/ServiceCards.vue'
import WhyUs from '@/components/clientComponents/WhyUs.vue'
import ClientAppLayout from '@/layouts/ClientAppLayout.vue';

defineOptions({layout: ClientAppLayout});

</script>

<template>
    <div>
        <WhyUs/>
        <ServiceCard/>
    </div>
</template>


