<script setup lang="ts">
import { toRef } from 'vue';
import FieldLabel from './FieldLabel.vue';
import ErrorField from './ErrorField.vue';
import { useFieldErrorClass } from '@/composables/useFieldErrorClass';

const props = defineProps<{
    label?: string;
    error?: string;
    required?: boolean;
    hint?: string;
    modelValue?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const textareaClass = useFieldErrorClass(toRef(props, 'error'), 'textarea');
</script>

<template>
    <div>
        <FieldLabel :required="required">
            {{ label }}
        </FieldLabel>

        <textarea
            v-bind="$attrs"
            :value="modelValue"
            @input="
                emit(
                    'update:modelValue',
                    ($event.target as HTMLTextAreaElement).value,
                )
            "
            :class="textareaClass"
        />

        <ErrorField :error="error" />

        <p
            v-if="hint"
            class="mt-1.5 text-[11px] text-gray-500 dark:text-gray-400"
        >
            {{ hint }}
        </p>
    </div>
</template>
