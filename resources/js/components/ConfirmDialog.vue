<script setup lang="ts">
import { computed } from 'vue';
import { X } from 'lucide-vue-next';
import Option from '@/type/Option';
import DropdownField from '@/components/form/DropdownField.vue';
import TextareaField from '@/components/form/TextareaField.vue';
import DialogFooter from '@/components/DialogFooter.vue';

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

const selectModel = computed({
    get: () => props.selectValue,
    set: (val) => emit('update:selectValue', val as any),
});

const textareaModel = computed({
    get: () => props.textareaValue,
    set: (val) => emit('update:textareaValue', val ?? ''),
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
        <div
            class="w-full max-w-xl overflow-hidden rounded-2xl border border-nav-stroke bg-background shadow-xl"
        >
            <!-- HEADER -->
            <div class="relative border-b border-nav-stroke p-6">
                <button
                    class="absolute top-4 right-4 flex h-7 w-7 items-center justify-center rounded-lg hover:bg-muted"
                    @click="emit('close')"
                >
                    <X class="h-4 w-4 text-muted-foreground" />
                </button>

                <div
                    class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-[#F8E8E3]"
                >
                    <svg
                        class="h-6 w-6 text-[#C8442B]"
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

                <h2 class="text-xl font-semibold text-[#1B1A18]">
                    {{ title }}
                    <span v-if="rowName">{{ rowName }}</span>
                </h2>
                <p class="mt-1 text-sm text-[#6B6862]">
                    {{ description }}
                </p>
            </div>

            <!-- BODY -->
            <div
                v-if="showSelect || showTextarea"
                class="space-y-4 px-6 py-5"
            >
                <DropdownField
                    v-if="showSelect"
                    :label="selectLabel"
                    :options="selectOptions"
                    v-model="selectModel"
                    required
                />

                <TextareaField
                    v-if="showTextarea"
                    :label="textareaLabel"
                    v-model="textareaModel"
                    required
                />
            </div>

            <!-- FOOTER -->
            <DialogFooter
                back-label="Batal"
                :reject-label="actionLabel"
                :reject-icon="X"
                @back="emit('close')"
                @reject="handleConfirm"
            />
        </div>
    </div>
</template>
