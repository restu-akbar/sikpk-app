<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import FieldLabel from './FieldLabel.vue';
import ErrorField from './ErrorField.vue';
import FilePreview from './FilePreview.vue';
import { Upload } from 'lucide-vue-next';

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
        required?: boolean;
    }>(),
    {
        modelValue: () => [],
        multiple: true,
        accept: 'image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt',
        removable: true,
        required: false,
    },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: File[]): void;
}>();

const previewFiles = ref<PreviewFile[]>([]);
const touched = ref(false);

const buildPreview = (files: File[]) => {
    previewFiles.value.forEach((f) => URL.revokeObjectURL(f.url));

    previewFiles.value = files.map((file) => ({
        file,
        name: file.name,
        type: file.type,
        url: URL.createObjectURL(file),
    }));
};

const internalError = computed(() => {
    if (!touched.value) return undefined;

    if (props.required && props.modelValue.length === 0) {
        return 'Berkas wajib diunggah';
    }

    return undefined;
});

const finalError = computed(() => props.error || internalError.value);

watch(
    () => props.modelValue,
    (files) => {
        buildPreview(files ?? []);
    },
    { immediate: true },
);
const isFileAccepted = (file: File, accept: string) => {
    const rules = accept.split(',').map((r) => r.trim());

    return rules.some((rule) => {
        if (rule.endsWith('/*')) {
            const category = rule.replace('/*', '');
            return file.type.startsWith(`${category}/`);
        }

        if (rule.startsWith('.')) {
            return file.name.toLowerCase().endsWith(rule.toLowerCase());
        }

        return file.type === rule;
    });
};
const uploadError = ref('');
const handleFiles = (event: Event) => {
    touched.value = true;

    const target = event.target as HTMLInputElement;

    if (!target.files) return;

    const selectedFiles = Array.from(target.files);

    const validFiles = selectedFiles.filter((file) =>
        isFileAccepted(file, props.accept),
    );

    if (validFiles.length !== selectedFiles.length) {
        uploadError.value = `Format file tidak sesuai`;
        return;
    }

    uploadError.value = '';

    const files = props.multiple
        ? [...props.modelValue, ...validFiles]
        : validFiles.slice(0, 1);

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
    <div class="min-w-0">
        <FieldLabel>
            {{ label }}
        </FieldLabel>

        <label
            class="flex h-36 w-full cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-white px-4 text-center transition-all hover:border-blue-500 hover:bg-blue-50 sm:h-48 dark:border-gray-700 dark:bg-gray-900 dark:hover:border-blue-400 dark:hover:bg-gray-800"
        >
            <Upload class="mb-2 h-8 w-8 text-gray-400 sm:mb-3 sm:h-10 sm:w-10" />

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

        <ErrorField :error="uploadError || finalError" />

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
