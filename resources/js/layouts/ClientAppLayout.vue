<script setup lang="ts">
import Footer from '@/components/clientComponents/Footer.vue';
import Navbar from '@/components/clientComponents/Navbar.vue';
</script>

<template>
    <div class="dark:bg-white">
        <Navbar />
        <main>
            <slot />
        </main>

        <Footer />
    </div>
</template>