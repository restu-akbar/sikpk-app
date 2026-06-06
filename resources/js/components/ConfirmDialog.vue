<script setup lang="ts">
import { computed } from 'vue';
import Option from '@/type/Option';
import DropdownField from '@/components/form/DropdownField.vue';
import TextareaField from '@/components/form/TextareaField.vue';

const props = withDefaults(
    defineProps<{
        open: boolean;
        title: string;
        description: string;
        rowName?: string;

        actionLabel?: string;

        icon?: 'warning' | 'danger' | 'info' | 'success';

        showSelect?: boolean;
        selectLabel?: string;
        selectOptions?: Option[];
        selectValue?: string | number;

        showTextarea?: boolean;
        textareaLabel?: string;
        textareaValue?: string;
    }>(),
    {
        actionLabel: 'Konfirmasi',
        icon: 'warning',
        showSelect: false,
        showTextarea: false,
        selectOptions: () => [],
    },
);

const emit = defineEmits<{
    close: [];
    confirm: [
        {
            selectValue?: string | number;
            textareaValue?: string;
        },
    ];
    'update:selectValue': [string | number];
    'update:textareaValue': [string];
}>();

/**
 * 🔥 FIX: computed wrapper supaya v-model tetap sinkron
 */
const selectModel = computed({
    get: () => props.selectValue,
    set: (val) => emit('update:selectValue', val as any),
});

const textareaModel = computed({
    get: () => props.textareaValue,
    set: (val) => emit('update:textareaValue', val ?? ''),
});

/**
 * icon class logic
 */
const iconClasses = computed(() => {
    switch (props.icon) {
        case 'danger':
            return {
                wrapper: 'bg-red-100',
                icon: 'text-red-600',
            };
        case 'success':
            return {
                wrapper: 'bg-green-100',
                icon: 'text-green-600',
            };
        case 'info':
            return {
                wrapper: 'bg-blue-100',
                icon: 'text-blue-600',
            };
        default:
            return {
                wrapper: 'bg-orange-100',
                icon: 'text-orange-600',
            };
    }
});

function handleConfirm() {
    emit('confirm', {
        selectValue: props.selectValue,
        textareaValue: props.textareaValue,
    });
}
</script>

<template>
    <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
        @click.self="emit('close')"
    >
        <div class="w-full max-w-xl rounded-2xl border bg-background shadow-xl">
            <!-- HEADER -->
            <div class="relative p-6">
                <button class="absolute top-4 right-4" @click="emit('close')">
                    ✕
                </button>

                <div class="flex items-start gap-4">
                    <div
                        :class="[
                            'flex h-14 w-14 items-center justify-center rounded-full',
                            iconClasses.wrapper,
                        ]"
                    >
                        <svg
                            :class="['h-6 w-6', iconClasses.icon]"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"
                            />
                        </svg>
                    </div>

                    <div>
                        <h2 class="text-xl font-semibold">
                            {{ title }}
                            <span v-if="rowName">{{ rowName }}</span>
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- BODY -->
            <div class="space-y-4 px-6 pb-4">
                <!-- SELECT -->
                <DropdownField
                    v-if="showSelect"
                    :label="selectLabel"
                    :options="selectOptions"
                    v-model="selectModel"
                />

                <!-- TEXTAREA -->
                <TextareaField
                    v-if="showTextarea"
                    :label="textareaLabel"
                    v-model="textareaModel"
                />
            </div>

            <!-- FOOTER -->
            <div class="flex justify-between border-t px-6 py-5">
                <button
                    class="rounded-xl border px-5 py-2.5 text-sm"
                    @click="emit('close')"
                >
                    Batal
                </button>

                <button
                    class="rounded-xl border border-red-300 px-5 py-2.5 text-sm text-red-600 hover:bg-red-50"
                    @click="handleConfirm"
                >
                    {{ actionLabel }}
                </button>
            </div>
        </div>
    </div>
</template>
