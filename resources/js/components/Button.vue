<script setup lang="ts">
defineEmits<{
    click: [];
}>();
</script>

<template>
    <button
        type="button"
        class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-lg bg-brand-accent px-5 text-sm font-medium text-white transition hover:opacity-90"
        @click="$emit('click')"
    >
        <slot name="left-icon" />

        <slot />

        <slot name="right-icon" />
    </button>
</template>
