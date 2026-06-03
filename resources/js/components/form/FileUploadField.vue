<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Trash2, Upload } from 'lucide-vue-next';
import FieldLabel from './FieldLabel.vue';
import ErrorField from './ErrorField.vue';
import FilePreview from './FilePreview.vue';

type PreviewFile = {
    file: File;
    name: string;
    type: string;
    url: string;
};

const props = withDefaults(
    defineProps<{
        label?: string;
        hint?: string;
        error?: string;
        modelValue?: File[];
        multiple?: boolean;
        accept?: string;
        removable?: boolean;
    }>(),
    {
        modelValue: () => [],
        multiple: true,
        accept: 'image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt',
        removable: true,
    },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: File[]): void;
}>();

const previewFiles = ref<PreviewFile[]>([]);

const buildPreview = (files: File[]) => {
    previewFiles.value.forEach((f) => URL.revokeObjectURL(f.url));

    previewFiles.value = files.map((file) => ({
        file,
        name: file.name,
        type: file.type,
        url: URL.createObjectURL(file),
    }));
};

watch(
    () => props.modelValue,
    (files) => {
        buildPreview(files ?? []);
    },
    { immediate: true },
);

const handleFiles = (event: Event) => {
    const target = event.target as HTMLInputElement;

    if (!target.files) return;

    const selectedFiles = Array.from(target.files);

    const files = props.multiple
        ? [...props.modelValue, ...selectedFiles]
        : [selectedFiles[0]];

    emit('update:modelValue', files);

    target.value = '';
};

const removeFile = (index: number) => {
    const files = [...props.modelValue];

    files.splice(index, 1);

    emit('update:modelValue', files);
};

const hasFiles = computed(() => previewFiles.value.length > 0);

onBeforeUnmount(() => {
    previewFiles.value.forEach((f) => URL.revokeObjectURL(f.url));
});
</script>

<template>
    <div>
        <FieldLabel>
            {{ label }}
        </FieldLabel>

        <label
            class="flex h-48 w-full cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-white text-center transition-all hover:border-blue-500 hover:bg-blue-50 dark:border-gray-700 dark:bg-gray-900 dark:hover:border-blue-400 dark:hover:bg-gray-800"
        >
            <Upload class="mb-3 h-10 w-10 text-gray-400" />

            <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                Klik untuk unggah berkas
            </p>

            <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">
                atau seret file ke sini
            </p>

            <input
                type="file"
                class="hidden"
                :multiple="multiple"
                :accept="accept"
                @change="handleFiles"
            />
        </label>

        <ErrorField :error="error" />

        <p
            v-if="hint"
            class="mt-2 text-[11px] text-gray-500 dark:text-gray-400"
        >
            {{ hint }}
        </p>

        <div
            v-if="hasFiles"
            class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
        >
            <FilePreview
                v-for="(file, index) in previewFiles"
                :key="file.url"
                :file="file"
                :removable="removable"
                @remove="removeFile(index)"
            />
        </div>
    </div>
</template>
