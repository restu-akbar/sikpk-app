<script setup lang="ts">
defineProps<{
    title: string;
    description: string;
}>();

const emit = defineEmits<{
    close: [];
}>();
</script>

<template>
    <div
        class="flex items-start justify-between border-b border-gray-200 px-6 py-5"
    >
        <div>
            <h2 class="text-base font-semibold text-gray-900">
                {{ title }}
            </h2>

            <p class="mt-0.5 text-sm text-gray-500">
                {{ description }}
            </p>
        </div>

        <button
            type="button"
            class="mt-0.5 ml-4 text-gray-400 hover:text-gray-600"
            aria-label="Tutup"
            @click="emit('close')"
        >
            <svg
                class="h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                />
            </svg>
        </button>
    </div>
</template>
