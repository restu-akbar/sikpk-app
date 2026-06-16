<script setup lang="ts">
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';
import { useGoBack } from '@/composables/useGoBack';
import Button from '@/components/Button.vue';
import { jenisKekerasanOptions } from '@/constants/jenisKekerasanOptions';
import { formatDate } from '@/lib/formatDate';
import { getLabel } from '@/lib/getLabel';
import ClarifyForm from '@/components/ClarifyForm.vue';
import DataTable from '@/components/table/DataTable.vue';
import CryptoUnlockDialog from '@/components/CryptoUnlockDialog.vue';
import { useCryptoUnlock } from '@/composables/useCryptoUnlock';
import { getReportLabel } from '@/lib/mapping/reportTypeLabelMap';
import { ArrowRight, Ban, ChevronRight, Eye } from 'lucide-vue-next';
import { getAvatarColor, getInitials } from '@/composables/useInitials';
import { satgasApi } from '@/lib/axios';
import { useCryptoStore } from '@/lib/crypto/store';
import { decryptFile } from '@/lib/mediaCrypto';
import DocumentationDialog from '@/components/DocumentationDialog.vue';
import {
    DEFAULT_REJECTED_REASONS,
    REJECTED_REASON_MAPPING,
    Report,
} from '@/types/reports';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { useForm } from '@inertiajs/vue3';
import { handleEdit } from '@/lib/handleRequest';
import { update } from '@/routes/satgas/reports';
import { progressColor } from '@/constants/progressColor';

const dialogRegistry: Record<string, any> = {
    notulensi: ClarifyForm,
    documentation: DocumentationDialog,
};
const activeDialog = ref<null | {
    name: string;
    props?: any;
}>(null);
const props = defineProps<{
    report: Report;
}>();
const activeDialogComponent = computed(() => {
    if (!activeDialog.value) return null;
    return dialogRegistry[activeDialog.value.name] ?? null;
});
function openDialog(name: string, props: any = {}) {
    activeDialog.value = { name, props };
}

function closeDialog() {
    activeDialog.value = null;
}

const cryptoStore = useCryptoStore();

const { goBack } = useGoBack('/satgas');

const showDocMenu = ref(false);
const docMenuRef = ref<HTMLElement | null>(null);
const {
    showUnlockDialog,
    unlockLoading,
    unlockError,
    unlockCrypto,
    cancelUnlock,
} = useCryptoUnlock();

const stepMaster = ['Klarifikasi', 'Pemeriksaan', 'Kesimpulan', 'Pasca'];

function handleSelectOption(opt: any) {
    openDialog(opt.subtype, {
        report: props.report,
    });

    showDocMenu.value = false;
}

function handleClickOutside(event: MouseEvent) {
    if (docMenuRef.value && !docMenuRef.value.contains(event.target as Node)) {
        showDocMenu.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});

type StepStatus = 'BERJALAN' | 'MENDATANG' | 'SELESAI';

const steps = computed(() => {
    const currentIndex = stepMaster.findIndex(
        (step) => step === props.report.progress,
    );

    return stepMaster.map((title, index) => {
        const log = props.report.report_logs.find(
            (item) => item.progress === title,
        );

        let status: StepStatus = 'MENDATANG';

        if (index < currentIndex) {
            status = 'SELESAI';
        } else if (index === currentIndex) {
            status = 'BERJALAN';
        }

        return {
            id: index + 1,
            title,
            date: log?.created_at ? formatDate(log.created_at, false) : null,
            status,
        };
    });
});
const documentationFieldsMap = {
    Klarifikasi: [
        {
            label: 'Dokumentasi BAP',
            description:
                'Foto / scan dokumen BAP klarifikasi. Bisa unggah beberapa gambar.',
            accept: 'image/*',
        },
        {
            label: 'Rekaman Klarifikasi',
            description: 'File audio sesi klarifikasi.',
            accept: 'audio/*',
        },
    ],

    Pemeriksaan: [
        {
            label: 'Dokumentasi Pemeriksaan',
            description:
                'Foto / scan dokumen BAP pemeriksaan. Bisa unggah beberapa gambar.',
            accept: 'image/*',
        },
        {
            label: 'Rekaman Pemeriksaan',
            description: 'File audio sesi pemeriksaan.',
            accept: 'audio/*',
        },
        {
            label: 'Berita Acara Pemeriksaan',
            description: 'Hasil scan dokumen yang sudah ditandatangani',
            accept: '.pdf',
        },
    ],
};

const documentationFields = computed(() => {
    return documentationFieldsMap[props.report.progress] ?? [];
});
const handleStepClick = (step: (typeof steps.value)[number]) => {
    if (step.status === 'MENDATANG') {
        return;
    }

    console.log('Buka dokumen tahap:', step.title);
};
const reportSections = computed(() => [
    {
        title: 'Ringkasan Laporan',
        items: [
            { label: 'No. Laporan', value: props.report.case_number },
            {
                label: 'Jenis Kekerasan',
                value: getLabel(
                    jenisKekerasanOptions,
                    props.report.jenis_kekerasan,
                ),
            },
            {
                label: 'Tanggal Lapor',
                value: formatDate(props.report.created_at, false),
            },
            { label: 'Tempat Kejadian', value: props.report.tempat_kejadian },
            {
                label: 'Waktu Kejadian',
                value: formatDate(props.report.waktu_kejadian),
            },
        ],
    },
    {
        title: 'Data Pelapor',
        items: [
            { label: 'Nama', value: props.report.reporter.name },
            { label: 'NIM', value: props.report.reporter.nim },
            { label: 'Jurusan', value: props.report.reporter.jurusan },
            { label: 'Program Studi', value: props.report.reporter.prodi },
            { label: 'WhatsApp', value: props.report.reporter.whatsapp },
        ],
    },
]);

const documentOptionsMap = {
    Klarifikasi: ['notulensi', 'documentation'],
    Pemeriksaan: ['periksa_saksi', 'periksa_pelapor', 'periksa_terlapor'],
    Kesimpulan: [
        'kesimpulan_rekomendasi',
        'penyampaian_hasil',
        'pernyataan_pelaku',
    ],
    Pasca: ['pemulihan_korban', 'pemulihan_nama_baik'],
};

const availableDocumentOptions = computed(() => {
    const progress = props.report.progress;
    const options = documentOptionsMap[progress] ?? [];

    return options.map((subtype) => ({
        subtype,
        label: getReportLabel(progress, subtype),
    }));
});

const isAllDocumentsComplete = computed(() => {
    const progress = props.report.progress;
    if (!progress) return false;

    const requiredTypes = documentOptionsMap[progress] ?? [];
    const existingTypes =
        props.report.report_documents?.map((d: any) => d.subtype) ?? [];

    return requiredTypes.every((type) => existingTypes.includes(type));
});

const filteredReportDocuments = computed(() => {
    if (props.report.progress === 'Laporan Dihentikan') {
        return props.report.report_documents;
    }

    return props.report.report_documents.filter(
        (doc) => doc.type === props.report.progress,
    );
});
const columns = [
    { key: 'no', label: 'No.' },
    { key: 'jenis_dokumen', label: 'Jenis Dokumen' },
    { key: 'created_at', label: 'Tanggal Dibuat', sortable: true },
    { key: 'status', label: 'Status' },
    { key: 'file', label: 'File' },
    { key: 'arrow', label: '' },
];
function handleRowClick() {
    if (!isAllDocumentsComplete) return;

    openDialog('documentation', {
        report: props.report,
        attachmentFields: documentationFields.value,
    });
}
const viewFile = async (row: any) => {
    try {
        const { data } = await satgasApi.get(`files/${row.id}`, {
            responseType: 'blob',
            params: {
                type: 'document',
            },
        });

        const edek = row.edeks[cryptoStore.userId];

        const decryptedFile = await decryptFile({
            encryptedFile: data,
            edek,
            privateKey: cryptoStore.privateKey!,
            filename: row.original_filename,
            mimeType: row.mime_type,
        });
        const url = URL.createObjectURL(decryptedFile);
        window.open(url, '_blank');
    } catch (error) {
        console.error('Download error:', error);
    }
};
const isDialogOpen = ref(false);

const dialogMode = ref<'continue' | 'stop'>('continue');

function openConfirmDialog(mode: 'continue' | 'stop') {
    dialogMode.value = mode;
    isDialogOpen.value = true;
}

function closeConfirmDialog() {
    isDialogOpen.value = false;
}
const dialogConfig = computed(() => {
    if (dialogMode.value === 'stop') {
        return {
            title: 'Hentikan Penanganan',
            description: 'Apakah Anda yakin?',
            actionLabel: 'Hentikan',
            actionIcon: 'x',
            actionVariant: 'danger',
            showSelect: true,
            showTextarea: true,
        };
    }

    return {
        title: 'Lanjutkan Penanganan',
        description: 'Apakah Anda yakin?',
        actionLabel: 'Lanjutkan',
        actionIcon: 'check',
        actionVariant: 'success',
        showSelect: false,
        showTextarea: false,
    };
});

const form = useForm({
    progress: '',
    rejected_reason: '',
    note_reason: '',
});
function handleConfirm() {
    if (dialogMode.value === 'stop') {
        stopHandling();
    } else {
        continueHandling();
    }

    closeConfirmDialog();
}
function stopHandling() {
    form.progress = 'Laporan Dihentikan';
    handleEdit(form, update(props.report.id));
}

const progressFlow = [
    'Laporan Baru',
    'Klarifikasi',
    'Pemeriksaan',
    'Kesimpulan',
    'Pasca',
];

function continueHandling() {
    const currentIndex = progressFlow.indexOf(props.report.progress);

    if (currentIndex === -1 || currentIndex >= progressFlow.length - 1) {
        return;
    }

    form.progress = progressFlow[currentIndex + 1];

    handleEdit(form, update(props.report.id));
}
const nextProgress = computed(() => {
    const currentIndex = progressFlow.indexOf(props.report.progress);

    if (currentIndex === -1 || currentIndex >= progressFlow.length - 1) {
        return props.report.progress;
    }

    return progressFlow[currentIndex + 1];
});
</script>

<template>
    <div class="min-h-screen w-full">
        <div class="max-w-jxl mx-auto w-full px-4 py-5">
            <button
                type="button"
                @click="goBack"
                class="group inline-flex items-center text-base font-medium focus:outline-none"
            >
                <svg
                    class="mr-1 h-4 w-4 transition-transform group-hover:-translate-x-1"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 19l-7-7 7-7"
                    />
                </svg>

                <span class="group-hover:underline">
                    Kembali ke daftar penanganan
                </span>
            </button>

            <div
                class="my-5 w-full overflow-hidden rounded-xl border border-[#EBE5DA] shadow-sm"
            >
                <div class="border-b border-[#EBE5DA] bg-[#FBF9F5] px-6 py-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="mb-1.5 text-xl font-bold">
                                Progress Kasus
                            </h2>
                            <p class="text-sm leading-tight text-[#595959]">
                                Tekan tahap untuk melihat dokumen pada tahap
                                tersebut. Tahap yang sudah selesai dapat diakses
                                untuk meninjau dokumen sebelumnya.
                            </p>
                        </div>

                        <div class="ml-4 shrink-0">
                            <span
                                :class="[
                                    'inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold',
                                    progressColor(props.report.progress).badge,
                                ]"
                            >
                                {{ props.report.progress }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div
                            v-for="step in steps"
                            :key="step.id"
                            @click="handleStepClick(step)"
                            :class="[
                                'relative flex cursor-pointer items-center justify-between rounded-xl border p-5 transition-all duration-200 select-none',

                                step.status === 'BERJALAN'
                                    ? 'border-[#104887] bg-[#F9EFE3] shadow-[0_4px_12px_rgba(16,72,135,0.1)] ring-1 ring-[#104887]'
                                    : step.status === 'SELESAI'
                                      ? 'border-[#22C55E] bg-[#F0FDF4]'
                                      : 'border-dashed border-[#E5DEC1] bg-[#F6F2EE] opacity-80',
                            ]"
                        >
                            <div class="flex items-center space-x-4">
                                <div
                                    :class="[
                                        'flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold',

                                        step.status === 'BERJALAN'
                                            ? 'bg-[#E36D13] text-white'
                                            : step.status === 'SELESAI'
                                              ? 'bg-[#22C55E] text-white'
                                              : 'border border-[#DDD7CD] bg-white text-[#8C8C8C]',
                                    ]"
                                >
                                    {{ step.id }}
                                </div>

                                <div>
                                    <h3
                                        class="text-base font-bold text-[#262626]"
                                    >
                                        {{ step.title }}
                                    </h3>

                                    <p class="text-xs text-[#737373]">
                                        {{
                                            step.date
                                                ? `Mulai ${step.date}`
                                                : 'Belum dimulai'
                                        }}
                                    </p>
                                </div>
                            </div>

                            <span
                                :class="[
                                    'rounded-full px-3 py-1 text-xs font-bold uppercase',

                                    step.status === 'BERJALAN'
                                        ? 'border border-[#F5D5BA] bg-white text-[#E36D13]'
                                        : step.status === 'SELESAI'
                                          ? 'border border-[#BBF7D0] bg-white text-[#16A34A]'
                                          : 'border border-[#E0DBCF] bg-[#EFEBE3] text-[#8C8C8C]',
                                ]"
                            >
                                {{ step.status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="my-5 w-full overflow-hidden rounded-xl border border-[#EBE5DA] shadow-sm"
            >
                <div
                    class="flex items-center justify-between border-b border-[#EBE5DA] bg-[#FBF9F5] px-6 py-6"
                >
                    <div>
                        <h2 class="mb-1.5 text-xl font-bold">
                            Informasi Kasus
                        </h2>
                        <p class="text-sm leading-tight">
                            Ringkasan laporan dan tim penanganan yang bertugas.
                        </p>
                    </div>

                    <div
                        ref="docMenuRef"
                        class="relative inline-block shrink-0"
                    >
                        <Button
                            @click="showDocMenu = !showDocMenu"
                            class="bg-blue-600 text-white shadow-sm transition hover:brightness-90"
                        >
                            <template #left-icon>
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 4v16m8-8H4"
                                    />
                                </svg>
                            </template>
                            Tambah Dokumen
                        </Button>

                        <Transition name="fade-scale">
                            <div
                                v-if="showDocMenu"
                                class="absolute left-0 z-50 mt-2 w-72 origin-top-left overflow-hidden rounded-xl border bg-white shadow-xl ring-1 ring-black/5"
                            >
                                <div
                                    class="border-b bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-500"
                                >
                                    Pilih Jenis Dokumen
                                </div>

                                <button
                                    v-for="opt in availableDocumentOptions"
                                    :key="opt.subtype"
                                    class="flex w-full items-center justify-between px-4 py-2.5 text-left text-sm transition hover:bg-gray-100"
                                    @click="handleSelectOption(opt)"
                                >
                                    <span>{{ opt.label }}</span>
                                </button>

                                <div
                                    v-if="availableDocumentOptions.length === 0"
                                    class="px-4 py-3 text-sm text-gray-400"
                                >
                                    Semua dokumen sudah lengkap
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>

                <!-- Content -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div class="p-5">
                        <div
                            v-for="section in reportSections"
                            :key="section.title"
                            class="mb-8 last:mb-0"
                        >
                            <p
                                class="mb-3 text-sm font-bold tracking-wider text-muted-foreground uppercase"
                            >
                                {{ section.title }}
                            </p>

                            <div class="space-y-3">
                                <div
                                    v-for="item in section.items"
                                    :key="item.label"
                                    class="grid grid-cols-[180px_1fr] gap-4 border-b"
                                >
                                    <p class="text-sm text-gray-500">
                                        {{ item.label }}
                                    </p>

                                    <p class="text-sm text-gray-900">
                                        {{ item.value }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan -->
                    <div class="p-5">
                        <div class="mb-5">
                            <p
                                class="mb-2 text-sm font-bold tracking-wider text-muted-foreground uppercase"
                            >
                                Kronologi
                            </p>

                            <div class="rounded-lg bg-[#F6F2EE] p-3">
                                <p>{{ props.report.kronologi }}</p>
                            </div>
                        </div>

                        <div>
                            <p
                                class="mb-4 text-sm font-bold tracking-wider text-muted-foreground uppercase"
                            >
                                Tim Penanganan - {{ props.report.team_number }}
                            </p>

                            <div class="space-y-3">
                                <div
                                    v-for="anggota in props.report.members"
                                    :key="anggota.id"
                                    class="flex items-center gap-3 p-3"
                                >
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold group-data-[collapsible=icon]:h-7 group-data-[collapsible=icon]:w-7"
                                        :class="getAvatarColor(anggota.name)"
                                    >
                                        {{ getInitials(anggota.name) }}
                                    </div>

                                    <div class="min-w-0">
                                        <p class="font-medium text-foreground">
                                            {{ anggota.name }}
                                        </p>

                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{ anggota.academic_role }} -
                                            {{ anggota.department }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <DataTable
                :columns="columns"
                :rows="filteredReportDocuments"
                :searchable="false"
                :pagination="false"
                @row-click="
                    !isAllDocumentsComplete &&
                    ['Klarifikasi', 'Pemeriksaan'].includes(
                        props.report.progress,
                    )
                        ? handleRowClick
                        : undefined
                "
                resource-route="/documents"
            >
                <!-- Filter slot: badge tahap -->
                <template #filter>
                    <div class="flex flex-col gap-1">
                        <!-- header + tahap satu baris -->
                        <div class="flex items-center justify-between gap-2">
                            <h1 class="text-xl font-semibold text-gray-900">
                                Dokumen Penanganan
                            </h1>

                            <span
                                class="shrink-0 rounded-full bg-orange-100 px-3 py-1 text-xs font-medium text-orange-600"
                            >
                                Tahap {{ report.progress }}
                            </span>
                        </div>

                        <!-- keterangan -->
                        <p class="text-sm leading-tight text-gray-500">
                            {{ props.report.report_documents.length }} dokumen
                            tercatat
                        </p>
                    </div>
                </template>

                <!-- No. urut -->
                <template #no="{ index }">
                    <span class="text-sm text-muted-foreground">
                        {{ String(index + 1) }}
                    </span>
                </template>

                <!-- Jenis Dokumen bold -->
                <template #jenis_dokumen="{ row }">
                    <span class="font-semibold text-foreground">{{
                        getReportLabel(row.type, row.subtype)
                    }}</span>
                </template>

                <!-- Tanggal -->
                <template #created_at="{ row }">
                    {{ formatDate(row.created_at, false) }}
                </template>

                <!-- Status badge -->
                <template #status="{}">
                    <span
                        :class="[
                            'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium',
                            isAllDocumentsComplete
                                ? 'bg-green-50 text-green-600'
                                : 'bg-orange-50 text-orange-600',
                        ]"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-current" />
                        {{ isAllDocumentsComplete ? 'Selesai' : 'Menunggu' }}
                    </span>
                </template>

                <template #file="{ row }">
                    <button
                        type="button"
                        @click.stop="viewFile(row)"
                        class="flex h-8 w-8 items-center justify-center rounded-lg border border-border text-muted-foreground transition hover:bg-muted hover:text-foreground"
                        title="Lihat File"
                    >
                        <Eye class="h-4 w-4" />
                    </button>
                </template>
                <template #arrow>
                    <ChevronRight
                        v-if="
                            !isAllDocumentsComplete &&
                            ['Klarifikasi', 'Pemeriksaan'].includes(
                                props.report.progress,
                            )
                        "
                        class="h-4 w-4 text-muted-foreground"
                    />
                </template>
            </DataTable>
            <div class="mt-5 flex justify-end gap-3">
                <Button
                    v-if="
                        ['Klarifikasi', 'Pemeriksaan'].includes(
                            props.report.progress,
                        )
                    "
                    variant="secondary"
                    :disabled="!isAllDocumentsComplete"
                    @click="openConfirmDialog('stop')"
                >
                    <Ban class="h-4 w-4" />
                    Hentikan Penanganan
                </Button>

                <Button
                    :disabled="
                        !isAllDocumentsComplete ||
                        props.report.progress == 'Laporan Dihentikan'
                    "
                    @click="openConfirmDialog('continue')"
                >
                    <ArrowRight class="h-4 w-4" />
                    Lanjutkan
                </Button>
            </div>
        </div>

        <component
            :is="activeDialogComponent"
            v-if="activeDialogComponent"
            v-bind="activeDialog?.props"
            :open="true"
            @close="closeDialog"
        />
        <CryptoUnlockDialog
            :open="showUnlockDialog"
            :loading="unlockLoading"
            :error="unlockError"
            @submit="unlockCrypto"
            @cancel="cancelUnlock"
        />
        <ConfirmDialog
            :open="isDialogOpen"
            :title="dialogConfig.title"
            :description="dialogConfig.description"
            :action-label="dialogConfig.actionLabel"
            :show-select="dialogConfig.showSelect"
            :show-textarea="dialogConfig.showTextarea"
            select-label="Kategori Penolakan"
            :select-options="
                REJECTED_REASON_MAPPING[
                    props.report
                        .progress as keyof typeof REJECTED_REASON_MAPPING
                ] ?? DEFAULT_REJECTED_REASONS
            "
            textarea-label="Alasan Penolakan"
            v-model:select-value="form.rejected_reason"
            v-model:textarea-value="form.note_reason"
            :reject-label="dialogConfig.rejectLabel"
            :reject-variant="dialogConfig.rejectVariant"
            :action-variant="dialogConfig.actionVariant"
            :action-icon="dialogConfig.actionIcon"
            :row-name="props.report.case_number"
            @close="closeConfirmDialog"
            @confirm="handleConfirm"
        />
    </div>
</template>
