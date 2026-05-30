<script setup>
import FieldLabel from './FieldLabel.vue';

const props = defineProps({
    label: String,
    required: {
        type: Boolean,
        default: false,
    },
    modelValue: {
        type: String,
        default: '',
    },
    options: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue']);

function select(value) {
    emit('update:modelValue', value);
}

function buttonClass(value) {
    return [
        'rounded-lg border px-4 py-2 text-sm font-medium transition-all',
        props.modelValue === value
            ? 'border-blue-500 bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300'
            : 'border-gray-300 bg-white text-gray-600 hover:border-gray-400 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300',
    ];
}
</script>

<template>
    <FieldLabel :required="required">
        {{ label }}
    </FieldLabel>

    <div class="flex flex-wrap gap-2">
        <button
            v-for="item in options"
            :key="item.value"
            type="button"
            @click="select(item.value)"
            :class="buttonClass(item.value)"
        >
            {{ item.label }}
        </button>
    </div>
</template>
