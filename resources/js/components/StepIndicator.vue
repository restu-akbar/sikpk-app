<script setup lang="ts">
interface Step {
    title: string;
    desc: string;
}

defineProps<{
    steps: Step[];
    currentStep: number;
}>();
</script>

<template>
    <div class="border-b border-gray-200 bg-white px-6 py-5 md:px-10">
        <div class="mx-auto flex max-w-3xl items-start justify-between">
            <template v-for="(step, index) in steps" :key="index">
                <div
                    class="flex flex-1 flex-col items-center gap-1.5 select-none"
                >
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold transition-all duration-300"
                        :class="[
                            currentStep >= index + 1
                                ? 'bg-[#1A5BA6] text-white shadow-sm'
                                : 'border border-gray-300 bg-white text-gray-400',
                        ]"
                    >
                        <svg
                            v-if="currentStep > index + 1"
                            class="h-4 w-4 text-white"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="3"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                        <span v-else>{{ index + 1 }}</span>
                    </div>

                    <span
                        class="mt-1 text-center text-xs font-bold transition-colors duration-300"
                        :class="
                            currentStep >= index + 1
                                ? 'text-gray-900'
                                : 'text-gray-400'
                        "
                    >
                        {{ step.title }}
                    </span>

                    <span
                        class="max-w-[140px] text-center text-[10px] leading-tight text-gray-400"
                    >
                        {{ step.desc }}
                    </span>
                </div>

                <div
                    v-if="index < steps.length - 1"
                    class="mt-4.5 h-[2px] flex-1 transition-all duration-500"
                    :class="
                        currentStep > index + 1 ? 'bg-[#1A5BA6]' : 'bg-gray-200'
                    "
                />
            </template>
        </div>
    </div>
</template>
