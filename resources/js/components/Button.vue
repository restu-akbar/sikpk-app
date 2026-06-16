<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        variant?: 'primary' | 'secondary';
        class?: string;
    }>(),
    {
        variant: 'primary',
        class: '',
    },
);

defineEmits<{
    click: [];
}>();

const buttonClass = computed(() => {
    const variants = {
        primary: 'bg-brand-accent text-white hover:opacity-90',
        secondary:
            'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50',
    };

    return [
        'inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-lg px-5 text-sm font-medium transition',
        variants[props.variant],
        props.class,
    ];
});
</script>

<template>
    <button type="button" :class="buttonClass" @click="$emit('click')">
        <slot name="left-icon" />

        <slot />

        <slot name="right-icon" />
    </button>
</template>
