<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div>
        <header>
            <nav>
                <Link href="/">Strona główna</Link>
                <Link href="/blog">Blog</Link>
            </nav>
        </header>
        <main>
            <slot />
        </main>

        <footer>
            <p>© 2026 Moja firma</p>
        </footer>
    </div>
</template>

