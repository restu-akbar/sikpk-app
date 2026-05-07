<script setup lang="ts">
const props = defineProps<{
    open: boolean;
    title: string;
    description: string;
}>();

const emit = defineEmits<{
    confirm: [];
    close: [];
}>();
</script>

<template>
    <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
    >
        <div
            class="w-full max-w-md rounded-2xl border border-border bg-background shadow-xl"
        >
            <div class="border-b border-border px-6 py-4">
                <h2 class="text-lg font-semibold text-foreground">
                    {{ title }}
                </h2>
            </div>

            <div class="px-6 py-5">
                <p class="text-sm leading-relaxed text-muted-foreground">
                    {{ description }}
                </p>
            </div>

            <div
                class="flex items-center justify-end gap-3 border-t border-border px-6 py-4"
            >
                <button
                    class="rounded-xl border border-border px-4 py-2 text-sm font-medium text-foreground transition hover:bg-muted"
                    @click="emit('close')"
                >
                    Batal
                </button>

                <button
                    class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:opacity-90"
                    @click="emit('confirm')"
                >
                    Hapus
                </button>
            </div>
        </div>
    </div>
</template>
