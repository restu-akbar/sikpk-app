<script setup lang="ts">
import ReportCard from '@/components/report/ReportCard.vue';

defineProps<{
    reports: Report[];
}>();
</script>

<template>
    <div class="space-y-6 p-4 md:p-6">
        <div class="flex flex-col gap-4">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-foreground">
                    Penanganan Kasus
                </h2>
                <p class="text-sm text-muted-foreground">
                    Kasus Aktif yang sedang anda tangani, klik untuk melakukan penanganan kasus
                </p>
            </div>

            <div class="flex justify-end">
                <p class="flex items-baseline gap-2">
                    <span class="text-lg font-semibold text-foreground">
                        {{ reports.length }}
                    </span>
                    <span class="text-xs text-muted-foreground">
                        Kasus aktif
                    </span>
                </p>
            </div>
        </div>

        <!-- Empty state -->
        <div
            v-if="reports.length === 0"
            class="flex flex-col items-center justify-center rounded-lg border border-dashed border-border py-16 text-center"
        >
            <div class="mb-4 rounded-full bg-muted p-4">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-8 w-8 text-muted-foreground"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                    />
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-foreground">Tidak ada kasus aktif</h3>
            <p class="mt-1 text-xs text-muted-foreground">
                Saat ini tidak ada kasus yang perlu ditangani.
            </p>
        </div>

        <!-- Report grid -->
        <div
            v-else
            class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6"
        >
            <ReportCard
                v-for="report in reports"
                :key="report.id"
                :report="report"
            />
        </div>
    </div>
</template>