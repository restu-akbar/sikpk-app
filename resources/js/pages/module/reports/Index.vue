<script setup lang="ts">
import DataTable from '@/components/table/DataTable.vue';
import { formatDate } from '@/lib/formatDate';
import { getInitials, getAvatarColor } from '@/composables/useInitials';
import ReportDetailDialog from '@/components/ReportDetail.vue';
import CryptoUnlockDialog from '@/components/CryptoUnlockDialog.vue';
import { ref } from 'vue';
import { useCryptoUnlock } from '@/composables/useCryptoUnlock';
import { ChevronRight } from 'lucide-vue-next';
import FormTimKlarifikasi from '@/components/report/FormTimKlarifikasi.vue';
import axios from 'axios';
import { satgasApi } from '@/lib/axios';

const {
    showUnlockDialog,
    unlockLoading,
    unlockError,
    unlockCrypto,
    cancelUnlock,
} = useCryptoUnlock();

const props = defineProps<{
    rows: any;
}>();
const columns = [
    { key: 'id', label: 'No. Kasus', sortable: true },
    { key: 'created_at', label: 'Tanggal Laporan', sortable: true },
    { key: 'pelapor', label: 'Pelapor' },
    { key: 'jenis_kekerasan', label: 'Jenis Kekerasan' },
    { key: 'progress', label: 'Progress Kasus' },
];

const badgeClass = (status: string) => {
    const map: Record<string, string> = {
        Dibuka: 'bg-blue-50 text-blue-700',
        Klarifikasi: 'bg-yellow-50 text-yellow-700',
        Pemeriksaan: 'bg-green-50 text-green-700',
        Ditutup: 'bg-red-50 text-red-600',
    };
    return map[status] ?? 'bg-gray-100 text-gray-600';
};

const initialsColor = (inisial: string) => {
    const palette: Record<string, string> = {
        AR: 'bg-blue-100 text-blue-700',
        BH: 'bg-orange-100 text-orange-700',
        CM: 'bg-green-100 text-green-700',
        DS: 'bg-indigo-100 text-indigo-700',
        EP: 'bg-pink-100 text-pink-700',
    };
    return palette[inisial] ?? 'bg-gray-100 text-gray-600';
};

const isDetailOpen = ref(false);
const selectedReport = ref<any>(null);

function openDetail(row: any) {
    selectedReport.value = row;
    isDetailOpen.value = true;
}

const isTimKlarifikasiOpen = ref(false);
const satgasMembers = ref({
    data: [],
    current_page: 1,
    last_page: 1,
    next_page_url: null,
    prev_page_url: null,
    total: 0,
});

async function handleAccept(id: number) {
    isDetailOpen.value = false;

    if (!satgasMembers.value.data.length) {
        const { data } = await satgasApi.get('/users');
        satgasMembers.value = {
            ...satgasMembers.value,
            ...data,
        };
    }

    isTimKlarifikasiOpen.value = true;
}

function handleReject(id: number) {
    // logika tolak laporan
}

function handleBack() {
    isDetailOpen.value = true;
    isTimKlarifikasiOpen.value = false;
}

function handleCloseTim() {
    isTimKlarifikasiOpen.value = false;
    isDetailOpen.value = false;
    selectedReport.value = null;
}

function handleTimSubmitted() {
    isTimKlarifikasiOpen.value = false;
    selectedReport.value = null;
}
</script>

<template>
    <div class="space-y-6 p-4 md:p-6">
        <DataTable
            title="Manajemen Kasus"
            description="Pantau setiap laporan yang masuk, kelola tahap penanganan, dan bentuk tim klarifikasi. Sebagai
Ketua, Anda hanya dapat melihat progress, bukan isi dokumen penanganan."
            :columns="columns"
            :rows="rows"
            :searchable="true"
            search-placeholder="Cari nama, email, jurusan..."
            :pagination="true"
            resource-route="laporan"
            @row-click="openDetail"
        >
            <template #id="{ index }">
                <span class="font-mono text-xs text-muted-foreground">
                    {{ index + 1 }}
                </span>
            </template>

            <template #created_at="{ row }">
                {{ formatDate(row.created_at) }}
            </template>

            <template #pelapor="{ row }">
                <div class="flex items-center gap-2">
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                        :class="getAvatarColor(row.nama)"
                    >
                        {{ getInitials(row.nama) }}
                    </div>

                    <div>
                        <p class="text-sm font-medium">{{ row.nama }}</p>

                        <p class="text-xs text-muted-foreground">
                            {{ row.nim }} · {{ row.angkatan }}
                        </p>
                    </div>
                </div>
            </template>

            <!-- Kolom Progress dengan badge -->
            <template #progress="{ row }">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
                    :class="badgeClass(row.progress)"
                >
                    <span
                        class="h-1.5 w-1.5 rounded-full bg-current opacity-80"
                    />
                    {{ row.progress }}
                </span>
            </template>

            <!-- Chevron di akhir baris -->
            <template #aksi="{ row }">
                <button
                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-border hover:bg-muted"
                    @click.stop="openDetail(row)"
                >
                    <ChevronRight class="h-4 w-4 text-muted-foreground" />
                </button>
            </template>
        </DataTable>
        <ReportDetailDialog
            :open="isDetailOpen"
            :report="selectedReport"
            @close="isDetailOpen = false"
            @accept="handleAccept"
            @reject="handleReject"
        />
        <CryptoUnlockDialog
            :open="showUnlockDialog"
            :loading="unlockLoading"
            :error="unlockError"
            @submit="unlockCrypto"
            @cancel="cancelUnlock"
        />
        <FormTimKlarifikasi
            v-show="isTimKlarifikasiOpen"
            :open="isTimKlarifikasiOpen"
            :report="selectedReport"
            :satgas-members="satgasMembers"
            @back="handleBack"
            @close="handleCloseTim"
            @submitted="handleTimSubmitted"
        />
    </div>
</template>
