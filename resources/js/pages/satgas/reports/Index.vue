<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import DataTable from '@/components/table/DataTable.vue';
import { formatDate } from '@/lib/formatDate';
import { getInitials, getAvatarColor } from '@/composables/useInitials';
import { jenisKekerasanOptions } from '@/constants/jenisKekerasanOptions';
import ReportDetailDialog from '@/components/ReportDetail.vue';
import CryptoUnlockDialog from '@/components/CryptoUnlockDialog.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ProgressBadge from '@/components/ProgressBadge.vue';
import { computed, ref } from 'vue';
import { useCryptoUnlock } from '@/composables/useCryptoUnlock';
import { ChevronRight, EllipsisVertical } from 'lucide-vue-next';
import FormTimKlarifikasi from '@/components/report/FormTimKlarifikasi.vue';
import { reject } from '@/routes/satgas/reports';
import { satgasApi } from '@/lib/axios';
import { REJECTED_REASON_MAPPING } from '@/types/reports';
import { handleEdit } from '@/lib/handleRequest';

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
    { key: 'case_number', label: 'No. Kasus', sortable: true },
    { key: 'created_at', label: 'Tanggal Laporan', sortable: true },
    { key: 'pelapor', label: 'Pelapor' },
    { key: 'jenis_kekerasan', label: 'Jenis Kekerasan' },
    { key: 'progress', label: 'Progress Kasus' },
    { key: 'arrow', label: '' },
];

const progressFilter = ref('');
const moreFilterOpen = ref(false);
const defaultOption = { label: 'Laporan Baru', value: 'Laporan Baru' };
const moreOptions = [
    { label: 'Laporan Baru', value: 'Laporan Baru' },
    { label: 'Klarifikasi', value: 'Klarifikasi' },
    { label: 'Pemeriksaan', value: 'Pemeriksaan' },
    { label: 'Kesimpulan', value: 'Kesimpulan' },
    { label: 'Pasca', value: 'Pasca' },
];

const activeOption = computed(() => {
    if (progressFilter.value === '') return defaultOption;
    return (
        moreOptions.find((o) => o.value === progressFilter.value) ??
        defaultOption
    );
});

function selectFilter(value: string) {
    progressFilter.value = value;
    moreFilterOpen.value = false;
}

function toggleMore() {
    moreFilterOpen.value = !moreFilterOpen.value;
}

function closeMore() {
    moreFilterOpen.value = false;
}

const jenisKekerasanLabel = (value: string) =>
    jenisKekerasanOptions.find((o) => o.value === value)?.label ?? value;

const isDetailOpen = ref(false);
const selectedReport = ref<any>(null);
const rejectReason = ref('');
const rejectNote = ref('');
const isRejectOpen = ref(false);
const rejectOptions = computed(() => {
    const progress = selectedReport.value?.progress;

    const options =
        REJECTED_REASON_MAPPING[
            progress as keyof typeof REJECTED_REASON_MAPPING
        ] ?? [];

    return options;
});

const form = useForm({
    progress: '',
    reason: '',
    note: '',
});

async function submitReject() {
    if (!selectedReport.value) return;

    form.progress = 'reject';
    form.reason = rejectReason.value;
    form.note = rejectNote.value;

    handleEdit(form, reject(selectedReport.value.id));
    isRejectOpen.value = false;
}
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

async function handleAccept() {
    isDetailOpen.value = false;

    if (!satgasMembers.value.data.length) {
        const { data } = await satgasApi.get('users');
        satgasMembers.value = {
            ...satgasMembers.value,
            ...data,
        };
    }

    isTimKlarifikasiOpen.value = true;
}

function handleReject() {
    isDetailOpen.value = false;

    rejectReason.value = '';
    rejectNote.value = '';

    isRejectOpen.value = true;
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
            filter-key="progress"
            :filter-value="progressFilter"
            @row-click="openDetail"
        >
            <template #filter>
                <div
                    class="inline-flex h-9 items-center gap-0.5 rounded-lg border border-border bg-surface p-1"
                >
                    <!-- Semua -->
                    <button
                        class="relative h-full rounded-md px-4 text-sm transition-all"
                        :class="
                            progressFilter === ''
                                ? 'bg-background text-foreground shadow-sm'
                                : 'text-nav-muted hover:text-foreground'
                        "
                        @click="selectFilter('')"
                    >
                        <span class="invisible font-bold">Semua</span>
                        <span
                            class="absolute inset-0 flex items-center justify-center"
                            :class="
                                progressFilter === ''
                                    ? 'font-bold'
                                    : 'font-normal'
                            "
                            >Semua</span
                        >
                    </button>

                    <!-- Active filter option -->
                    <button
                        class="relative h-full rounded-md px-4 text-sm transition-all"
                        :class="
                            progressFilter !== ''
                                ? 'bg-background text-foreground shadow-sm'
                                : 'text-nav-muted hover:text-foreground'
                        "
                        @click="selectFilter(activeOption.value)"
                    >
                        <span class="invisible font-bold">{{
                            activeOption.label
                        }}</span>
                        <span
                            class="absolute inset-0 flex items-center justify-center"
                            :class="
                                progressFilter !== ''
                                    ? 'font-bold'
                                    : 'font-normal'
                            "
                            >{{ activeOption.label }}</span
                        >
                    </button>

                    <!-- More menu -->
                    <div class="relative">
                        <button
                            class="flex h-full items-center rounded-md px-2 text-nav-muted transition-all hover:text-foreground"
                            @click="toggleMore"
                        >
                            <EllipsisVertical class="h-4 w-4" />
                        </button>

                        <Transition
                            enter-active-class="transition duration-100 ease-out"
                            enter-from-class="scale-95 opacity-0"
                            enter-to-class="scale-100 opacity-100"
                            leave-active-class="transition duration-75 ease-in"
                            leave-from-class="scale-100 opacity-100"
                            leave-to-class="scale-95 opacity-0"
                        >
                            <div
                                v-if="moreFilterOpen"
                                class="absolute right-0 z-50 mt-2 min-w-[160px] rounded-lg border border-border bg-background p-1 shadow-lg"
                            >
                                <button
                                    v-for="opt in moreOptions"
                                    :key="opt.value"
                                    class="flex w-full items-center rounded-md px-3 py-2 text-left text-sm transition-colors"
                                    :class="
                                        progressFilter === opt.value
                                            ? 'bg-primary/10 font-medium text-primary'
                                            : 'text-foreground hover:bg-surface'
                                    "
                                    @click="selectFilter(opt.value)"
                                >
                                    {{ opt.label }}
                                </button>
                            </div>
                        </Transition>
                    </div>
                </div>

                <!-- Click outside to close -->
                <div
                    v-if="moreFilterOpen"
                    class="fixed inset-0 z-40"
                    @click="closeMore"
                />
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
            :open="isTimKlarifikasiOpen"
            :report="selectedReport"
            :satgas-members="satgasMembers"
            @back="handleBack"
            @close="handleCloseTim"
            @submitted="handleTimSubmitted"
        />
        <ConfirmDialog
            :open="isRejectOpen"
            icon="warning"
            title="Tolak laporan"
            :row-name="selectedReport?.case_number"
            description="Pelapor akan diberitahu alasan penolakan. Tindakan ini tidak dapat dibatalkan."
            action-label="Tolak Laporan"
            :show-select="true"
            select-label="Kategori Penolakan"
            :select-options="rejectOptions"
            v-model:select-value="rejectReason"
            :show-textarea="true"
            textarea-label="Alasan Penolakan"
            v-model:textarea-value="rejectNote"
            @close="isRejectOpen = false"
            @confirm="submitReject"
        />
    </div>
</template>
