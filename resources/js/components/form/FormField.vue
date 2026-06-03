<script setup lang="ts">
import { computed, toRef, ref } from 'vue';

defineOptions({ inheritAttrs: false });

import FieldLabel from './FieldLabel.vue';
import ErrorField from './ErrorField.vue';
import { useFieldErrorClass } from '@/composables/useFieldErrorClass';

const props = defineProps<{
    label?: string;
    error?: string;
    required?: boolean;
    modelValue?: string;
    hint?: string;

    minLength?: number;
    maxLength?: number;
    pattern?: RegExp | string;
    validator?: (value: string) => string | undefined;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const touched = ref(false);

const internalError = computed(() => {
    if (!touched.value) return undefined;

    const value = props.modelValue ?? '';
    if (props.minLength && value.length < props.minLength) {
        return `Minimal ${props.minLength} karakter`;
    }
    if (props.maxLength && value.length > props.maxLength) {
        return `Maksimal ${props.maxLength} karakter`;
    }
    if (props.pattern && value) {
        const regex =
            props.pattern instanceof RegExp
                ? props.pattern
                : new RegExp(props.pattern);
        if (!regex.test(value)) {
            return 'Format tidak valid';
        }
    }
    if (props.validator) {
        return props.validator(value);
    }
    return undefined;
});

const finalError = computed(() => props.error || internalError.value);

const inputClass = useFieldErrorClass(toRef(props, 'error'), 'input');
</script>

<template>
    <div>
        <FieldLabel :required="required">
            {{ label }}
        </FieldLabel>

        <input
            v-bind="$attrs"
            :value="modelValue"
            @input="
                emit(
                    'update:modelValue',
                    ($event.target as HTMLInputElement).value,
                )
            "
            @blur="touched = true"
            :class="inputClass"
        />

        <ErrorField :error="finalError" />

        <p
            v-if="hint"
            class="mt-1.5 text-[11px] text-gray-500 dark:text-gray-400"
        >
            {{ hint }}
        </p>
    </div>
</template>
