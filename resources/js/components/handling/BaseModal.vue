<script setup lang="ts">
import { watch, onUnmounted } from 'vue';
import ModalHeaderSection from '../ModalHeaderSection.vue';

const props = defineProps<{
    open: boolean;
    title: string;
    description?: string;
}>();

const emit = defineEmits<{
    close: [];
}>();
watch(
    () => props.open,
    (value) => {
        if (value) document.body.style.overflow = 'hidden';
        else document.body.style.overflow = '';
    },
);

onUnmounted(() => {
    document.body.style.overflow = '';
});
</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="open"
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/40 p-6"
                @click.self="$emit('close')"
            >
                <div
                    class="flex h-[95vh] w-[75vw] flex-col overflow-hidden rounded-xl border border-border bg-background"
                >
                    <ModalHeaderSection
                        :title="title"
                        :description="description"
                        @close="$emit('close')"
                    />

                    <div class="flex-1 space-y-6 overflow-y-auto px-6 py-4">
                        <slot />
                    </div>

                    <slot name="footer" />
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
