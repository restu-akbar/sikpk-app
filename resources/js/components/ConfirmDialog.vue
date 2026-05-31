<script setup lang="ts">
const props = defineProps<{
    open: boolean;
    title: string;
    description: string;
    rowName?: string;
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
        @click.self="emit('close')"
    >
        <div class="w-full max-w-sm rounded-2xl border border-nav-stroke bg-background shadow-xl">

            <!-- BODY -->
            <div class="flex flex-col items-center px-8 py-8 text-center">
                <!-- Icon -->
                <div class="mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-red-50">
                    <svg
                        class="h-6 w-6 text-red-500"
                        fill="none" stroke="currentColor" stroke-width="1.8"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" />
                    </svg>
                </div>

                <!-- Title -->
                <h2 class="mb-3 text-lg font-semibold text-foreground">
                    {{ title }}
                </h2>

                <!-- Description -->
                <p class="text-sm leading-relaxed text-muted-foreground">
                    <template v-if="rowName">
                        <strong class="font-semibold text-foreground">{{ rowName }}</strong>
                        {{ ' ' + description }}
                    </template>
                    <template v-else>
                        {{ description }}
                    </template>
                </p>
            </div>

            <!-- FOOTER -->
            <div class="flex items-center justify-between rounded-b-2xl border-t border-nav-stroke bg-[#FBF9F5] px-6 py-4">
                <!-- Batal -->
                <button
                    class="rounded-xl border border-nav-stroke bg-white px-5 py-2 text-sm font-medium text-foreground transition hover:bg-muted"
                    @click="emit('close')"
                >
                    Batal
                </button>

                <!-- Hapus -->
                <button
                    class="rounded-xl border border-red-300 bg-white px-5 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50"
                    @click="emit('confirm')"
                >
                    Hapus
                </button>
            </div>
        </div>
    </div>
</template>
