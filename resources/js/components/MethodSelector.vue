<script setup lang="ts">
defineProps<{
    modelValue: string;
    options: {
        value: string;
        title: string;
        description: string;
        badge?: string;
    }[];
}>();

const emit = defineEmits(['update:modelValue']);

const select = (value: string) => {
    emit('update:modelValue', value);
};
</script>

<template>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <button
            v-for="opt in options"
            :key="opt.value"
            type="button"
            @click="select(opt.value)"
            :class="[
                'rounded-xl border-2 p-5 text-left transition-all',
                modelValue === opt.value
                    ? 'border-[#2563EB] shadow-sm'
                    : 'hover:border-gray-300',
            ]"
        >
            <div class="mb-2 flex items-center justify-between gap-2">
                <span
                    class="text-[10px] font-semibold tracking-widest uppercase"
                >
                    {{ opt.value === 'form' ? 'Metode 01' : 'Metode 02' }}
                </span>

                <span
                    v-if="opt.badge"
                    class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                    :class="
                        modelValue === opt.value
                            ? 'bg-blue-500 text-white'
                            : 'text-gray-400'
                    "
                >
                    {{ opt.badge }}
                </span>
            </div>

            <h3 class="mb-1 text-base font-bold">
                {{ opt.title }}
            </h3>

            <p class="text-xs leading-relaxed text-gray-600">
                {{ opt.description }}
            </p>
        </button>
    </div>
</template>
