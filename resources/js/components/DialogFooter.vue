<script setup lang="ts">
import type { Component } from 'vue';

defineProps<{
    backLabel?: string;
    backIcon?: Component;
    rejectLabel?: string;
    rejectIcon?: Component;
    rejectDisabled?: boolean;
    actionLabel?: string;
    actionIcon?: Component;
    actionDisabled?: boolean;
    infoText?: string;
}>();

defineEmits<{
    back: [];
    reject: [];
    action: [];
}>();
</script>

<template>
    <div
        class="flex shrink-0 items-center justify-between border-t border-nav-stroke bg-surface px-6 py-4"
    >
        <!-- Left: back/close button -->
        <button
            v-if="backLabel"
            class="inline-flex h-9 items-center gap-2 rounded-lg border border-nav-stroke bg-white px-4 text-sm text-nav-text transition-colors hover:bg-gray-50"
            @click="$emit('back')"
        >
            <component :is="backIcon" v-if="backIcon" class="h-4 w-4" />
            {{ backLabel }}
        </button>
        <div v-else />

        <!-- Right: info + reject + action -->
        <div class="flex items-center gap-3">
            <span v-if="infoText" class="text-xs text-muted-foreground">
                {{ infoText }}
            </span>

            <button
                v-if="rejectLabel"
                class="inline-flex h-9 items-center gap-1.5 rounded-lg border px-4 text-sm font-medium transition-colors"
                :class="
                    rejectDisabled
                        ? 'cursor-not-allowed border-gray-200 bg-gray-50 text-gray-400'
                        : 'border-dialog-reject bg-white text-dialog-reject hover:bg-red-50'
                "
                :disabled="rejectDisabled"
                @click="$emit('reject')"
            >
                <component
                    :is="rejectIcon"
                    v-if="rejectIcon"
                    class="h-3.5 w-3.5"
                />
                {{ rejectLabel }}
            </button>

            <button
                v-if="actionLabel"
                class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-brand-accent px-4 text-sm font-medium text-white transition-colors"
                :class="
                    actionDisabled
                        ? 'cursor-not-allowed bg-brand-accent/55'
                        : 'bg-brand-accent hover:brightness-95'
                "
                :disabled="actionDisabled"
                @click="$emit('action')"
            >
                {{ actionLabel }}
                <component
                    :is="actionIcon"
                    v-if="actionIcon"
                    class="h-4 w-4"
                />
            </button>
        </div>
    </div>
</template>
