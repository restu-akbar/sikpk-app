<script setup lang="ts">
const props = defineProps<{
    modelValue: boolean;
    title?: string;
    description?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void;
}>();

const toggle = () => {
    emit('update:modelValue', !props.modelValue);
};
</script>

<template>
    <div
        class="cursor-pointer rounded-xl border border-blue-200 bg-blue-50 p-4 mb-4"
        @click="toggle"
    >
        <div class="flex items-start gap-3">
            <div
                class="mt-0.5 flex h-5 w-5 min-w-5 items-center justify-center rounded border-2 transition"
                :class="
                    modelValue
                        ? 'border-blue-600 bg-blue-600'
                        : 'border-gray-300 bg-white'
                "
            >
                <svg
                    v-if="modelValue"
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-3 w-3 text-white"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z"
                        clip-rule="evenodd"
                    />
                </svg>
            </div>

            <p class="text-sm leading-relaxed text-gray-700">
                <span v-if="title" class="font-semibold">
                    {{ title }}
                </span>

                <span v-if="description">
                    {{ description }}
                </span>
            </p>
        </div>
    </div>
</template>
