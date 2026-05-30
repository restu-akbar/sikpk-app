<script setup>
defineProps({
    required: Boolean,
});
</script>

<template>
    <label
        class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-200"
    >
        <slot />
        <span v-if="required" class="text-red-500">*</span>
    </label>
</template>
