<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Trash2, Upload } from 'lucide-vue-next';
import FieldLabel from './FieldLabel.vue';
import ErrorField from './ErrorField.vue';

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
    }>(),
    {
        modelValue: () => [],
        multiple: true,
        accept: 'image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt',
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
            <div
                v-for="(file, index) in previewFiles"
                :key="file.url"
                class="relative rounded-xl border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900"
            >
                <button
                    type="button"
                    @click="removeFile(index)"
                    class="absolute top-2 right-2 rounded-lg border border-gray-200 bg-white p-2 text-gray-500 shadow-sm transition-all hover:border-red-200 hover:text-red-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:border-red-800 dark:hover:text-red-400"
                    aria-label="Hapus file"
                >
                    <Trash2 class="h-4 w-4" />
                </button>

                <!-- Image -->
                <img
                    v-if="file.type.startsWith('image/')"
                    :src="file.url"
                    class="h-40 w-full rounded-lg object-cover"
                />

                <!-- Video -->
                <video
                    v-else-if="file.type.startsWith('video/')"
                    controls
                    class="h-40 w-full rounded-lg object-cover"
                >
                    <source :src="file.url" :type="file.type" />
                </video>

                <!-- Audio -->
                <audio
                    v-else-if="file.type.startsWith('audio/')"
                    controls
                    class="mt-12 w-full"
                >
                    <source :src="file.url" :type="file.type" />
                </audio>

                <!-- Other document -->
                <div
                    v-else
                    class="flex h-40 flex-col items-center justify-center rounded-lg border border-dashed border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                >
                    <p
                        class="max-w-[180px] truncate text-sm font-medium text-gray-700 dark:text-gray-300"
                    >
                        {{ file.name }}
                    </p>
                </div>

                <p class="mt-2 truncate text-xs text-gray-500">
                    {{ file.name }}
                </p>
            </div>
        </div>
    </div>
</template>
