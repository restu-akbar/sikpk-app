<script setup lang="ts">
import PublicNavbar from '@/components/public/PublicNavbar.vue';
import PublicFooter from '@/components/public/PublicFooter.vue';
</script>

<template>
    <div class="flex min-h-svh flex-col bg-[#FDFCFB]">
        <PublicNavbar />
        <main class="flex-1">
            <slot />
        </main>
        <PublicFooter />
    </div>
</template>
