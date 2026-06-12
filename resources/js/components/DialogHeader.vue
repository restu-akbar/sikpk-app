<script setup lang="ts">
import { X } from 'lucide-vue-next';
import ProgressBadge from '@/components/ProgressBadge.vue';
import { formatDate } from '@/lib/formatDate';
import { getLabel } from '@/lib/getLabel';
import { jenisKekerasanOptions } from '@/constants/jenisKekerasanOptions';

defineProps<{
    report: any;
}>();

defineEmits<{
    close: [];
}>();
</script>

<template>
    <div class="relative shrink-0 border-b border-nav-stroke px-6 py-5">
        <div class="mb-2">
            <ProgressBadge :status="report.progress" />
        </div>
        <h2 class="text-lg font-semibold">
            {{ report.case_number }}
        </h2>
        <p class="mt-0.5 text-xs text-muted-foreground">
            {{ getLabel(jenisKekerasanOptions, report.jenis_kekerasan) }}
            - Dilaporkan {{ formatDate(report.created_at, false) }}
        </p>
        <button
            class="absolute top-5 right-5 flex h-7 w-7 items-center justify-center rounded-lg hover:bg-muted"
            @click="$emit('close')"
        >
            <X class="h-4 w-4 text-muted-foreground" />
        </button>
    </div>
</template>
