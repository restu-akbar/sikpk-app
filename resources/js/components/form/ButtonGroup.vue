<script setup lang="ts">
import { toRef } from 'vue';
import FieldLabel from './FieldLabel.vue';
import ErrorField from './ErrorField.vue';
import { useFieldErrorClass } from '@/composables/useFieldErrorClass';

type Option = {
    label: string;
    value: string | number;
};

const props = defineProps<{
    label?: string;
    required?: boolean;
    error?: string;
    modelValue?: string | number;
    options: Option[];
    name?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number): void;
}>();

const buttonClass = useFieldErrorClass(toRef(props, 'error'), 'button');

function select(value: string | number) {
    emit('update:modelValue', value);
}
</script>

<template>
    <div>
        <FieldLabel :required="required">
            {{ label }}
        </FieldLabel>

        <div :id="name" :data-field="name" class="flex flex-wrap gap-2">
            <button
                v-for="item in options"
                :key="item.value"
                type="button"
                @click="select(item.value)"
                :class="[
                    ...buttonClass,
                    modelValue === item.value
                        ? 'border-[#1A5BA6] bg-[#EDF3FB] text-[#1A5BA6]'
                        : 'bg-white text-gray-600 hover:border-gray-400',
                ]"
            >
                {{ item.label }}
            </button>
        </div>

        <ErrorField :error="error" />
    </div>
</template>
