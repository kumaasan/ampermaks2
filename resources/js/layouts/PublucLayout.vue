<template>
    <div>
        <!-- Twoja własna nawigacja -->
        <header>
            <nav>
                <Link href="/">Strona główna</Link>
                <Link href="/blog">Blog</Link>
            </nav>
        </header>

        <!-- Tu renderują się podstrony -->
        <main>
            <slot />
        </main>

        <footer>
            <p>© 2026 Moja firma</p>
        </footer>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>
