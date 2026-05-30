<script setup>
defineProps({
    error: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <p v-if="error" class="mt-1 text-xs text-red-500">
        {{ error }}
    </p>
</template>
