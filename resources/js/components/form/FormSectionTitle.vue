<template>
    <div class="mb-5 flex items-center gap-2">
        <span class="text-xs font-semibold tracking-widest uppercase">
            {{ title }}
        </span>
    </div>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
});
</script>
