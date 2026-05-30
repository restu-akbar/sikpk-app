<script setup lang="ts">
import { ref } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

/* Falls back to icon + text layout if the full logo image is missing. */
const imgError = ref(false);
</script>

<template>
    <!-- Full logo image (logo-satgas-full.png): used when available -->
    <img
        v-if="!imgError"
        src="/images/logo-satgas-full.png"
        alt="Satgas PPK Politeknik Negeri Bandung"
        class="h-8 w-auto object-contain"
        @error="imgError = true"
    />

    <!-- Fallback: icon + text layout for sidebar -->
    <template v-else>
        <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary">
            <AppLogoIcon class="size-5" />
        </div>
        <div class="ml-1 grid flex-1 text-left text-sm leading-tight">
            <span class="truncate font-semibold">SIKPK POLBAN</span>
        </div>
    </template>
</template>
