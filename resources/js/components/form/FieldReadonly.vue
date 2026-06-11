<script setup lang="ts">
import FieldLabel from './FieldLabel.vue';

defineProps<{
    label: string;
    value?: string | number | null;
    hint?: string;
}>();
</script>

<template>
    <div>
        <FieldLabel>
            {{ label }}
        </FieldLabel>

        <div
            class="mt-1 flex min-h-10 items-center rounded-md border-[#E8E3DA] border-input bg-[#F6F2EE] px-3 py-2 text-sm text-foreground"
        >
            {{ value || '-' }}
        </div>

        <p
            v-if="hint"
            class="mt-1.5 text-[11px] text-gray-500 dark:text-gray-400"
        >
            {{ hint }}
        </p>
    </div>
</template>
