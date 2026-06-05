<script setup lang="ts">
import PublicNavbar from '@/components/public/PublicNavbar.vue';
import PublicFooter from '@/components/public/PublicFooter.vue';

interface Props {
    currentPage?: string;
}

withDefaults(defineProps<Props>(), {
    currentPage: 'Beranda',
});
</script>

<template>
    <div class="flex min-h-svh flex-col bg-white">
        <PublicNavbar :current-page="currentPage" />
        <main class="flex-1">
            <slot />
        </main>
        <PublicFooter />
    </div>
</template>
