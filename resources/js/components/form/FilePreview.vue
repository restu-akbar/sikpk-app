<script setup lang="ts">
import { ref } from 'vue';
import { Trash2, X } from 'lucide-vue-next';
import { computed } from 'vue';

export interface PreviewFile {
    file?: File;
    name: string;
    type: string;
    url: string;
}

const props = withDefaults(
    defineProps<{
        file: PreviewFile;
        removable?: boolean;
    }>(),
    {
        removable: true,
    },
);

const emit = defineEmits<{
    remove: [];
}>();

const openPreview = ref(false);

const isImage = computed(() => props.file?.type?.startsWith('image/') ?? false);
const isVideo = computed(() => props.file?.type?.startsWith('video/') ?? false);
const isAudio = computed(() => props.file?.type?.startsWith('audio/') ?? false);
</script>

<template>
    <div
        class="relative min-w-0 overflow-hidden rounded-xl border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900"
    >
        <button
            v-if="removable"
            type="button"
            class="absolute top-2 right-2 z-10 rounded-lg border border-gray-200 bg-white p-2 shadow-sm hover:text-red-500"
            @click="$emit('remove')"
        >
            <Trash2 class="h-4 w-4" />
        </button>

        <div
            class="cursor-pointer overflow-hidden rounded-lg"
            @click="openPreview = true"
        >
            <img
                v-if="isImage"
                :src="file.url"
                class="h-40 w-full object-cover transition hover:scale-105"
            />

            <video v-else-if="isVideo" class="h-40 w-full object-cover">
                <source :src="file.url" :type="file.type" />
            </video>

            <audio v-else-if="isAudio" controls class="mt-12 w-full">
                <source :src="file.url" :type="file.type" />
            </audio>

            <div
                v-else
                class="flex h-40 items-center justify-center overflow-hidden rounded-lg border border-dashed"
            >
                <span class="block w-full truncate px-4 text-center text-sm">
                    {{ file.name }}
                </span>
            </div>
        </div>

        <p class="mt-2 truncate text-xs text-gray-500">
            {{ file.name }}
        </p>
    </div>

    <!-- Preview Modal -->
    <Teleport to="body">
        <div
            v-if="openPreview"
            class="fixed inset-0 z-[999] flex items-center justify-center bg-black/80 p-6"
            @click.self="openPreview = false"
        >
            <button
                class="absolute top-4 right-4 text-white"
                @click="openPreview = false"
            >
                <X class="h-8 w-8" />
            </button>

            <img
                v-if="isImage"
                :src="file.url"
                class="max-h-[90vh] max-w-[90vw] object-contain"
            />

            <video
                v-else-if="isVideo"
                controls
                autoplay
                class="max-h-[90vh] max-w-[90vw]"
            >
                <source :src="file.url" :type="file.type" />
            </video>

            <audio
                v-else-if="isAudio"
                controls
                autoplay
                class="w-full max-w-xl"
            >
                <source :src="file.url" :type="file.type" />
            </audio>

            <iframe
                v-else
                :src="file.url"
                class="h-[90vh] w-[90vw] rounded-lg bg-white"
            />
        </div>
    </Teleport>
</template>
