<script setup lang="ts">
import PublicNavbar from '@/components/public/PublicNavbar.vue';
</script>

<template>
    <!-- Public-facing auth layout: navbar at top, centered card on light gray background -->
    <div class="flex min-h-svh flex-col bg-auth-bg">

        <PublicNavbar />

        <!-- Content area: vertically and horizontally centered -->
        <div class="flex flex-1 items-center justify-center px-4 py-12">
            <div class="w-full max-w-[448px] rounded-2xl bg-auth-card-bg px-10 py-10 shadow-md">
                <slot />
            </div>
        </div>

    </div>
</template>
