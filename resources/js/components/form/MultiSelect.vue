<script setup lang="ts">
import ErrorField from './ErrorField.vue';
import FormField from './FormField.vue';

interface Option {
    label: string;
    value: string | number;
}

const props = withDefaults(
    defineProps<{
        label?: string;
        options: Option[];
        error?: string;
        helperText?: string;
        otherOptionValue?: string | number;
        otherInputLabel?: string;
        otherInputError?: string;
    }>(),
    {
        otherOptionValue: 'lainnya',
        otherInputLabel: 'Sebutkan lainnya...',
    },
);

const selectedValues = defineModel<Array<string | number>>({
    required: true,
    default: () => [],
});

const otherText = defineModel<string>('otherText', {
    default: '',
});

const toggleOption = (val: string | number) => {
    let updatedValues = [...(selectedValues.value || [])];
    const index = updatedValues.indexOf(val);

    if (index === -1) {
        updatedValues.push(val);
    } else {
        updatedValues.splice(index, 1);

        if (val === props.otherOptionValue) {
            otherText.value = '';
        }
    }

    selectedValues.value = updatedValues;
};
</script>

<template>
    <div>
        <p v-if="props.label" class="mb-2 text-sm font-medium text-gray-700">
            {{ props.label }}
        </p>

        <div class="flex flex-wrap gap-2.5">
            <button
                type="button"
                v-for="opt in props.options"
                :key="opt.value"
                @click="toggleOption(opt.value)"
                :class="[
                    'rounded-lg border px-4 py-2 text-sm font-medium transition-all',
                    selectedValues.includes(opt.value)
                        ? 'border-[#1A5BA6] bg-[#EDF3FB] text-[#1A5BA6]'
                        : 'border-gray-300 text-gray-600 hover:border-gray-400',
                ]"
            >
                {{ opt.label }}
            </button>
        </div>

        <ErrorField :error="props.error" />

        <div
            v-if="selectedValues.includes(props.otherOptionValue)"
            class="mt-4"
        >
            <FormField
                name="lainnyaInput"
                v-model="otherText"
                :label="props.otherInputLabel"
                :error="props.otherInputError"
            />
        </div>

        <p v-if="props.helperText" class="mt-3 text-[11px] text-gray-400">
            {{ props.helperText }}
        </p>
    </div>
</template>
