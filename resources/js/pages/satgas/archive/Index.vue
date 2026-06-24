<script setup lang="ts">
import DataTable from '@/components/table/DataTable.vue';
import { formatDate } from '@/lib/formatDate';
import { getInitials, getAvatarColor } from '@/composables/useInitials';
import { jenisKekerasanOptions } from '@/constants/jenisKekerasanOptions';
import ReportDetailDialog from '@/components/ArchiveReportDetail.vue';
import ProgressBadge from '@/components/ProgressBadge.vue';
import { ref } from 'vue';
import { ChevronRight } from 'lucide-vue-next';

defineProps<{
    rows: any;
}>();

const columns = [
    { key: 'case_number', label: 'No. Kasus', sortable: true },
    { key: 'created_at', label: 'Tanggal Laporan', sortable: true },
    { key: 'pelapor', label: 'Pelapor' },
    { key: 'jenis_kekerasan', label: 'Jenis Kekerasan' },
    { key: 'progress', label: 'Progress Kasus' },
    { key: 'arrow', label: '' },
];

const progressFilter = ref('');
const progressOptions = [
    { label: 'Semua', value: '' },
    { label: 'Selesai', value: 'Selesai' },
    { label: 'Laporan Dihentikan', value: 'Laporan Dihentikan' },
    { label: 'Laporan Ditolak', value: 'Laporan Ditolak' },
];

const jenisKekerasanLabel = (value: string) =>
    jenisKekerasanOptions.find((o) => o.value === value)?.label ?? value;

const isDetailOpen = ref(false);
const selectedReport = ref<any>(null);

function openDetail(row: any) {
    selectedReport.value = row;
    isDetailOpen.value = true;
}
</script>

<template>
    <div class="space-y-6 p-4 md:p-6">
        <DataTable
            title="Arsip Kasus"
            description="Daftar laporan yang penanganannya sudah final — selesai, dihentikan, atau ditolak."
            :columns="columns"
            :rows="rows"
            :searchable="true"
            search-placeholder="Cari nama, email, jurusan..."
            :pagination="true"
            filter-key="progress"
            :filter-value="progressFilter"
            @row-click="openDetail"
        >
            <template #filter>
                <div
                    class="inline-flex h-9 items-center gap-0.5 rounded-lg border border-border bg-surface p-1"
                >
                    <button
                        v-for="opt in progressOptions"
                        :key="opt.value"
                        class="relative h-full rounded-md px-4 text-sm transition-all"
                        :class="
                            progressFilter === opt.value
                                ? 'bg-background text-foreground shadow-sm'
                                : 'text-nav-muted hover:text-foreground'
                        "
                        @click="progressFilter = opt.value"
                    >
                        <span class="invisible font-bold">{{
                            opt.label
                        }}</span>
                        <span
                            class="absolute inset-0 flex items-center justify-center"
                            :class="
                                progressFilter === opt.value
                                    ? 'font-bold'
                                    : 'font-normal'
                            "
                            >{{ opt.label }}</span
                        >
                    </button>
                </div>
            </template>

            <template #case_number="{ row }">
                <span class="font-mono text-xs text-muted-foreground">
                    {{ row.case_number }}
                </span>
            </template>

            <template #created_at="{ row }">
                {{ formatDate(row.created_at, false) }}
            </template>

            <template #pelapor="{ row }">
                <div class="flex items-center gap-2.5">
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                        :class="getAvatarColor(row.reporter?.name)"
                    >
                        {{ getInitials(row.reporter?.name) }}
                    </div>
                    <span class="font-medium">{{ row.reporter?.name }}</span>
                </div>
            </template>

            <template #jenis_kekerasan="{ row }">
                {{ jenisKekerasanLabel(row.jenis_kekerasan) }}
            </template>

            <template #arrow>
                <ChevronRight class="h-4 w-4 text-muted-foreground" />
            </template>

            <template #progress="{ row }">
                <ProgressBadge :status="row.progress" />
            </template>
        </DataTable>
        <ReportDetailDialog
            :open="isDetailOpen"
            :report="selectedReport"
            @close="isDetailOpen = false"
        />
    </div>
</template>
