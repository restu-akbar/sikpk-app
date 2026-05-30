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
    placeholder?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number): void;
}>();

const selectClass = useFieldErrorClass(toRef(props, 'error'), 'select');
</script>

<template>
    <div>
        <FieldLabel :required="required">
            {{ label }}
        </FieldLabel>

        <div class="relative">
            <select
                v-bind="$attrs"
                :value="modelValue ?? ''"
                @change="
                    emit(
                        'update:modelValue',
                        ($event.target as HTMLSelectElement).value,
                    )
                "
                :required="required"
                :class="selectClass"
            >
                <option value="" disabled>
                    {{ placeholder ?? 'Pilih opsi...' }}
                </option>

                <option
                    v-for="option in options"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>

            <div
                class="pointer-events-none absolute inset-y-0 right-3 flex items-center"
            >
                <svg
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 9l-7 7-7-7"
                    />
                </svg>
            </div>
        </div>

        <ErrorField :error="error" />
    </div>
</template>
