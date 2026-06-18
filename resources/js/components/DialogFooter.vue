<script setup lang="ts">
import { computed, type Component } from 'vue';

const props = defineProps<{
    backLabel?: string;
    backIcon?: Component;

    rejectLabel?: string;
    rejectIcon?: Component;
    rejectDisabled?: boolean;
    rejectVariant?: 'danger' | 'default';

    actionLabel?: string;
    actionIcon?: Component;
    actionDisabled?: boolean;
    actionVariant?: 'primary' | 'success' | 'danger';

    infoText?: string;
}>();

defineEmits<{
    back: [];
    reject: [];
    action: [];
}>();

const rejectClass = computed(() => {
    switch (props.rejectVariant) {
        case 'danger':
            return 'border-[#C8442B] text-[#C8442B] bg-white hover:bg-red-50';

        default:
            return 'border-[#C8442B] text-[#C8442B] bg-white hover:bg-red-50';
    }
});

const actionClass = computed(() => {
    switch (props.actionVariant) {
        case 'success':
            return 'border-green-600 bg-green-600 text-white hover:bg-green-700';

        case 'danger':
            return 'border-[#C8442B] bg-[#C8442B] text-white hover:opacity-90';

        default:
            return 'border-brand-accent bg-brand-accent text-white hover:opacity-90';
    }
});
</script>

<template>
    <div class="flex items-center justify-between border-t bg-[#FBF9F5] px-6 py-4">
        <!-- BACK -->
        <button
            v-if="backLabel"
            class="inline-flex h-9 items-center gap-2 rounded-lg border px-4 text-sm hover:bg-gray-50"
            @click="$emit('back')"
        >
            <component :is="backIcon" v-if="backIcon" class="h-4 w-4" />
            {{ backLabel }}
        </button>

        <div v-else />

        <!-- ACTIONS -->
        <div class="flex items-center gap-3">
            <!-- REJECT -->
            <button
                v-if="rejectLabel"
                class="inline-flex h-9 items-center gap-1.5 rounded-lg border px-4 text-sm font-medium transition"
                :class="[
                    rejectClass,
                    rejectDisabled && 'cursor-not-allowed opacity-50',
                ]"
                :disabled="rejectDisabled"
                @click="$emit('reject')"
            >
                <component :is="rejectIcon" v-if="rejectIcon" class="h-4 w-4" />
                {{ rejectLabel }}
            </button>

            <!-- ACTION -->
            <button
                v-if="actionLabel"
                class="inline-flex h-9 items-center gap-1.5 rounded-lg border px-4 text-sm font-medium transition"
                :class="[
                    actionClass,
                    actionDisabled && 'cursor-not-allowed opacity-50',
                ]"
                :disabled="actionDisabled"
                @click="$emit('action')"
            >
                <component :is="actionIcon" v-if="actionIcon" class="h-4 w-4" />
                {{ actionLabel }}
            </button>
        </div>
    </div>
</template>
