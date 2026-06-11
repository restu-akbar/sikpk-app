<template>
    <div class="mb-5 flex items-center gap-2">
        <span class="text-sm font-bold tracking-widest uppercase" style="color: #847B6E">
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
