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
    <!-- Outer card pembungkus -->
    <div class="flex gap-3 rounded-xl border border-gray-200 bg-white p-3 shadow-sm">
        <!-- Inner card per metode -->
        <button
            v-for="(opt, index) in options"
            :key="opt.value"
            type="button"
            @click="select(opt.value)"
            class="flex-1 rounded-xl border-2 border-transparent p-4 text-left transition-all"
            :class="
                modelValue === opt.value
                    ? 'bg-[#EDF3FB]'
                    : 'hover:bg-gray-50'
            "
        >
            <div class="mb-3 flex items-center justify-between gap-2">
                <span
                    class="text-[10px] font-semibold tracking-widest uppercase"
                    :class="modelValue === opt.value ? 'text-[#0F3A6C]' : 'text-[#847B6E]'"
                >
                    {{ `Metode 0${index + 1}` }}
                </span>

                <span
                    v-if="opt.badge"
                    class="rounded-full px-2.5 py-0.5 text-[10px] font-semibold"
                    :class="
                        modelValue === opt.value
                            ? 'bg-[#D5E2F4] text-[#0F3A6C]'
                            : 'bg-[#ECE8E2] text-[#847B6E]'
                    "
                >
                    {{ opt.badge }}
                </span>
            </div>

            <h3 class="mb-1.5 text-base font-bold" style="color: #181613">
                {{ opt.title }}
            </h3>

            <p class="text-xs leading-relaxed" style="color: #0F3A6C">
                {{ opt.description }}
            </p>
        </button>
    </div>
</template>
