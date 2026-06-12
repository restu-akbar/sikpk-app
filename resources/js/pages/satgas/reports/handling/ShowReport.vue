<script setup lang="ts">
import { computed, ref } from 'vue';
import { useGoBack } from '@/composables/useGoBack';
import Button from '@/components/Button.vue';
import { jenisKekerasanOptions } from '@/constants/jenisKekerasanOptions';
import { formatDate } from '@/lib/formatDate';
import { getLabel } from '@/lib/getLabel';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import ClarifyForm from '@/components/ClarifyForm.vue';
import DataTable from '@/components/table/DataTable.vue';
import CryptoUnlockDialog from '@/components/CryptoUnlockDialog.vue';
import { useCryptoUnlock } from '@/composables/useCryptoUnlock';

const {
    showUnlockDialog,
    unlockLoading,
    unlockError,
    unlockCrypto,
    cancelUnlock,
} = useCryptoUnlock();

type StepStatus = 'BERJALAN' | 'MENDATANG' | 'SELESAI';

interface ReportLog {
    id: string;
    progress: string;
    created_at: string;
}

interface Report {
    id: string;
    progress: string;
    report_logs: ReportLog[];
}

const props = defineProps<{
    report: Report;
}>();

const { goBack } = useGoBack('/satgas');

const stepMaster = ['Klarifikasi', 'Pemeriksaan', 'Kesimpulan', 'Pasca'];

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

const isDocumentFormOpen = ref(false);

function openDocumentForm() {
    isDocumentFormOpen.value = true;
}

function onSuccessHandler() {
    isDocumentFormOpen.value = false;
}
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
                    <h2 class="mb-1.5 text-xl font-bold">Progress Kasus</h2>
                    <p class="text-sm leading-relaxed text-[#595959]">
                        Tekan tahap untuk melihat dokumen pada tahap tersebut.
                        Tahap yang sudah selesai dapat diakses untuk meninjau
                        dokumen sebelumnya.
                    </p>
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
                        <p class="text-sm leading-relaxed">
                            Ringkasan laporan dan tim penanganan yang bertugas.
                        </p>
                    </div>

                    <div class="shrink-0">
                        <Button @click="openDocumentForm">
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
                                Tim Penanganan - {{ props.report.no_tim }}
                            </p>

                            <div class="space-y-3">
                                <div
                                    v-for="anggota in props.report.members"
                                    :key="anggota.id"
                                    class="flex items-center gap-3 p-3"
                                >
                                    <Avatar class="h-10 w-10">
                                        <AvatarFallback>
                                            {{ anggota.initials }}
                                        </AvatarFallback>
                                    </Avatar>

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
        </div>
        <ClarifyForm
            :open="isDocumentFormOpen"
            :report="report"
            @close="isDocumentFormOpen = false"
            @success="onSuccessHandler"
        />
        <CryptoUnlockDialog
            :open="showUnlockDialog"
            :loading="unlockLoading"
            :error="unlockError"
            @submit="unlockCrypto"
            @cancel="cancelUnlock"
        />
    </div>
</template>
