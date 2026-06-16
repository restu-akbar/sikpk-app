<template>
    <div class="mb-2 flex items-center gap-2">
        <span
            class="font-bold tracking-widest uppercase"
            :class="fontSizeClass"
            style="color: #847b6e"
        >
            {{ title }}
        </span>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    size: {
        type: String,
        default: 'sm',
    },
});

const fontSizeClass = computed(() => {
    switch (props.size) {
        case 'lg':
            return 'text-lg';
        case 'md':
            return 'text-base';
        case 'sm':
        default:
            return 'text-sm';
    }
});
</script>
