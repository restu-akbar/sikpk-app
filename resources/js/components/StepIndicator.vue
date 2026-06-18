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
    <div class="border-b border-gray-200 bg-white px-4 py-4 sm:px-6 sm:py-5 md:px-10">
        <div class="mx-auto flex max-w-3xl items-start justify-between">
            <template v-for="(step, index) in steps" :key="index">
                <div
                    class="flex flex-1 flex-col items-center gap-1 select-none sm:gap-1.5"
                >
                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold transition-all duration-300 sm:h-9 sm:w-9 sm:text-sm"
                        :class="[
                            currentStep >= index + 1
                                ? 'bg-[#1A5BA6] text-white shadow-sm'
                                : 'border border-gray-300 bg-white text-gray-400',
                        ]"
                    >
                        <svg
                            v-if="currentStep > index + 1"
                            class="h-3.5 w-3.5 text-white sm:h-4 sm:w-4"
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
                        class="mt-1 text-center text-[10px] font-bold transition-colors duration-300 sm:text-xs"
                        :class="
                            currentStep >= index + 1
                                ? 'text-gray-900'
                                : 'text-gray-400'
                        "
                    >
                        {{ step.title }}
                    </span>

                    <span
                        class="hidden max-w-[140px] text-center text-[10px] leading-tight text-gray-400 sm:block"
                    >
                        {{ step.desc }}
                    </span>
                </div>

                <div
                    v-if="index < steps.length - 1"
                    class="mt-3.5 h-[2px] flex-1 transition-all duration-500 sm:mt-4.5"
                    :class="
                        currentStep > index + 1 ? 'bg-[#1A5BA6]' : 'bg-gray-200'
                    "
                />
            </template>
        </div>
    </div>
</template>
