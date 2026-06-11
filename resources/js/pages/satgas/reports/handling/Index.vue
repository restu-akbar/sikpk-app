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
                    Kasus aktif yang sedang ditangani oleh tim Satgas. Tekan
                    kasus untuk membuka detail penanganan, mengisi dokumen, dan
                    memantau progress di setiap tahapan.
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

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6">
            <ReportCard
                v-for="report in reports"
                :key="report.id"
                :report="report"
            />
        </div>
    </div>
</template>
