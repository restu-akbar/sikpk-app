<script setup lang="ts">
import { Trash2 } from 'lucide-vue-next';
type PreviewFile = {
    name: string;
    type: string;
    url: string;
};
const emit = defineEmits<{
    (e: 'remove', index: number): void;
}>();
defineProps<{
    files: PreviewFile[];
}>();
</script>

<template>
    <div
        v-if="files.length"
        class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
    >
        <div
            v-for="(file, index) in files"
            :key="index"
            class="relative rounded-xl border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900"
        >
            <button
                type="button"
                @click="emit('remove', index)"
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
                class="w-full"
            >
                <source :src="file.url" :type="file.type" />
            </audio>

            <!-- Document -->
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
</template>
