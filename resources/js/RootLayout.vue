<script setup lang="ts">
import { Toaster } from 'vue-sonner';
import { useFlashToast } from '@/composables/useFlashToast';

useFlashToast();
</script>

<template>
    <Toaster position="top-right" richColors />
    <slot />
</template>
