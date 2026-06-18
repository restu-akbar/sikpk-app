<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useStep } from '@/composables/useStep';
import Agreement from '@/components/Agreement.vue';
import StepIndicator from '@/components/StepIndicator.vue';
import axios from 'axios';
import { jenisKekerasanOptions } from '@/constants/jenisKekerasanOptions';
import { progressStatusMap } from '@/constants/progressMap';
import ProgressBadge from '@/components/ProgressBadge.vue';
import { getLabel } from '@/lib/getLabel';
import { formatDate } from '@/lib/formatDate';

const jenisKekerasanMap = Object.fromEntries(
    jenisKekerasanOptions.map((item) => [item.value, item.label]),
);

const currentStatus = computed(() => {
    return (
        progressStatusMap[selectedReport.value?.progress] ??
        progressStatusMap['Laporan Baru']
    );
});
const statusColorMap = {
    blue: {
        container: 'border-blue-100 bg-blue-50',
        icon: 'text-[#1A5BA6]',
        title: 'text-[#1A5BA6]',
    },

    orange: {
        container: 'border-orange-100 bg-orange-50',
        icon: 'text-orange-600',
        title: 'text-orange-700',
    },

    green: {
        container: 'border-green-100 bg-green-50',
        icon: 'text-green-600',
        title: 'text-green-700',
    },

    red: {
        container: 'border-red-100 bg-red-50',
        icon: 'text-red-600',
        title: 'text-red-700',
    },

    gray: {
        container: 'border-gray-200 bg-gray-50',
        icon: 'text-gray-600',
        title: 'text-gray-700',
    },
};

const currentStatusColor = computed(() => {
    return statusColorMap[currentStatus.value.color] ?? statusColorMap.blue;
});
const stepsData = [
    { title: 'Pernyataan Kerahasiaan', desc: 'Persetujuan menjaga informasi' },
    { title: 'Pilih Laporan', desc: 'Memilih laporan yang akan ditinjau' },
    { title: 'Lihat Progress', desc: 'Alur Penanganan Oleh Satgas' },
];

const { currentStep, steps, nextStep, prevStep } = useStep(stepsData, 3);

const agreed = ref(false);
const agreeError = ref('');

watch(agreed, (val) => {
    if (val) agreeError.value = '';
});

const reports = ref([]);
const loadingReports = ref(false);
const selectedReport = ref(null);
const reportDetail = ref(null);

const selectReport = async (report) => {
    selectedReport.value = report;

    try {
        loadingReports.value = true;

        const { data } = await axios.get(`/api/reports/${report.id}/logs`);

        reportDetail.value = data;
        console.log('LOG RESPONSE:', reportDetail.value);
        nextStep();
    } finally {
        loadingReports.value = false;
    }
};
const handleNext = async () => {
    if (!agreed.value) {
        agreeError.value = 'Pernyataan persetujuan harus diceklis';
        return;
    }

    agreeError.value = '';

    try {
        loadingReports.value = true;

        const { data } = await axios.get('/api/reports');

        reports.value = data;

        nextStep();
    } catch (error) {
        console.error(error);
    } finally {
        loadingReports.value = false;
    }
};

const progressSteps = [
    'Laporan Baru',
    'Klarifikasi',
    'Pemeriksaan',
    'Kesimpulan',
    'Pasca',
    'Selesai',
];

const progressLabels = {
    'Laporan Baru': 'Laporan Diterima',
    Klarifikasi: 'Tahap Klarifikasi',
    Pemeriksaan: 'Tahap Pemeriksaan',
    Kesimpulan: 'Penyusunan Kesimpulan',
    Pasca: 'Pendampingan Pasca Penanganan',
    Selesai: 'Kasus Selesai',
};

const timelineItems = computed(() => {
    const logs = reportDetail.value?.report_logs ?? [];
    console.log(logs);

    const currentIndex = progressSteps.indexOf(selectedReport.value?.progress);

    return progressSteps.map((step, index) => {
        const log = logs.find((item) => item.progress === step);

        return {
            title: progressLabels[step] ?? step,
            done: index <= currentIndex,
            date: log ? formatDate(log.created_at) : null,
        };
    });
});
</script>

<template>
    <div
        class="flex min-h-screen flex-col overflow-x-hidden overflow-y-auto"
        style="font-family: 'Plus Jakarta Sans', sans-serif"
    >
        <main class="mx-auto w-full max-w-4xl flex-1 px-4 py-6 sm:py-10">
            <!-- Page Header -->
            <div class="mb-6 text-center">
                <h1 class="mb-2 text-2xl font-bold sm:text-3xl">Cek Status Laporan</h1>
                <p class="mx-auto max-w-md text-sm leading-relaxed">
                    <span class="font-bold text-gray-900">
                        Pantau perkembangan laporan Anda.
                    </span>
                    <span class="text-gray-500">
                        Informasi progres hanya dapat diakses oleh pelapor yang
                        sah.
                    </span>
                </p>
            </div>

            <!-- Divider full-width -->
            <div
                class="mb-6 border-b border-gray-200"
                style="width: 100vw; margin-left: calc(50% - 50vw)"
            ></div>

            <div
                class="my-6 overflow-hidden rounded-2xl border border-gray-200 shadow-sm"
            >
                <!-- Step Indicator -->
                <StepIndicator :steps="steps" :current-step="currentStep" />

                <!-- Step 1: Pernyataan Kerahasiaan -->
                <div v-if="currentStep === 1">
                    <!-- Hero Banner -->
                    <div
                        class="bg-[linear-gradient(126.92deg,_#1A5BA6_0%,_#0B2A4F_100%)] px-4 py-6 sm:px-8 sm:py-8"
                    >
                        <div class="flex items-start gap-3 sm:gap-4">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-white/30 sm:h-11 sm:w-11"
                            >
                                <svg
                                    class="h-5 w-5 text-white sm:h-6 sm:w-6"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-white sm:text-xl">
                                    Pernyataan Kerahasiaan
                                </h2>
                                <p
                                    class="mt-1.5 text-sm leading-relaxed text-white/80"
                                >
                                    Sebelum melihat progres kasus, Anda perlu
                                    memahami dan menyetujui pernyataan
                                    kerahasiaan berikut. Pernyataan ini
                                    melindungi privasi Anda dan pihak-pihak yang
                                    terlibat.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-4 sm:p-6">
                        <!-- Declaration Card -->
                        <div class="mb-6 rounded-xl border border-gray-200 bg-[#FDFCFB] p-4 sm:p-6">
                            <p class="mb-4 font-bold text-gray-800">
                                Saya menyatakan bahwa:
                            </p>
                            <ol class="flex flex-col gap-4">
                                <li
                                    class="flex gap-3 text-sm leading-relaxed text-gray-600"
                                >
                                    <span
                                        class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#FDFCFB] text-[11px] font-bold text-gray-700"
                                    >
                                        1
                                    </span>
                                    <span>
                                        <strong
                                            class="font-semibold text-gray-800"
                                        >
                                            Informasi yang saya akses pada
                                            halaman ini bersifat rahasia.
                                        </strong>
                                        Saya tidak akan mengambil tangkapan
                                        layar, menyalin, atau menyebarluaskan
                                        informasi progres kasus kepada pihak
                                        yang tidak berkepentingan.
                                    </span>
                                </li>
                                <li
                                    class="flex gap-3 text-sm leading-relaxed text-gray-600"
                                >
                                    <span
                                        class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#FDFCFB] text-[11px] font-bold text-gray-700"
                                    >
                                        2
                                    </span>
                                    <span>
                                        <strong
                                            class="font-semibold text-gray-800"
                                        >
                                            Saya adalah pelapor yang sah
                                        </strong>
                                        atas kasus dengan nomor laporan
                                        tersebut. Saya bertanggung jawab penuh
                                        atas penggunaan akses ini.
                                    </span>
                                </li>
                                <li
                                    class="flex gap-3 text-sm leading-relaxed text-gray-600"
                                >
                                    <span
                                        class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#FDFCFB] text-[11px] font-bold text-gray-700"
                                    >
                                        3
                                    </span>
                                    <span>
                                        <strong
                                            class="font-semibold text-gray-800"
                                        >
                                            Pelanggaran pernyataan ini
                                        </strong>
                                        dapat menyebabkan dikenakan sanksi
                                        sesuai peraturan Polban yang berlaku
                                        dan/atau tindak lanjut berdasarkan
                                        ketentuan peraturan perundang-undangan,
                                        termasuk UU ITE.
                                    </span>
                                </li>
                            </ol>
                        </div>

                        <Agreement
                            v-model="agreed"
                            :error="agreeError"
                            title="Saya telah membaca, memahami, dan menyetujui"
                            description=" Pernyataan Kerahasiaan SIKPK di atas. Saya bersedia menjaga seluruh informasi progres kasus yang akan ditampilkan."
                        />

                        <!-- Error message -->
                        <p
                            v-if="agreeError"
                            class="mt-1.5 text-xs text-red-500"
                        >
                            {{ agreeError }}
                        </p>

                        <!-- Action -->
                        <div class="mt-6 flex justify-end">
                            <button
                                class="flex w-full items-center justify-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold text-white transition-all duration-200 sm:w-auto"
                                :class="
                                    agreed
                                        ? 'bg-[#F5821F] hover:bg-[#e0741a] active:scale-[0.98]'
                                        : 'cursor-not-allowed bg-[#F5821F]/50'
                                "
                                @click="handleNext"
                            >
                                Setuju &amp; Lihat Progres
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Pilih Laporan -->
                <div v-else-if="currentStep === 2">
                    <div class="bg-white p-4 sm:p-6">
                        <div class="mb-6">
                            <h1
                                class="mb-1 text-lg font-semibold text-gray-900 sm:text-xl"
                            >
                                Pilih kasus yang ingin Anda pantau
                            </h1>

                            <p
                                class="max-w-2xl text-sm leading-relaxed text-gray-600"
                            >
                                Daftar berikut menampilkan laporan yang Anda
                                kirim dari perangkat ini. Pilih satu kasus untuk
                                melihat progres penanganannya.
                            </p>
                        </div>
                        <div class="space-y-4">
                            <button
                                v-for="report in reports"
                                :key="report.id"
                                type="button"
                                class="w-full rounded-2xl border bg-white p-4 text-left transition-all hover:border-[#1A5BA6] hover:shadow-sm sm:p-6"
                                :class="
                                    selectedReport === report.id
                                        ? 'border-[#1A5BA6] ring-2 ring-blue-100'
                                        : 'border-gray-200'
                                "
                                @click="selectReport(report)"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                            <span
                                                class="font-mono text-xs font-semibold text-[#1A5BA6] sm:text-sm"
                                            >
                                                {{ report.case_number }}
                                            </span>

                                            <ProgressBadge :status="report.progress" />
                                        </div>

                                        <h3
                                            class="mt-2 text-base font-semibold text-gray-900 sm:mt-3 sm:text-xl"
                                        >
                                            {{
                                                getLabel(
                                                    jenisKekerasanOptions,
                                                    report.jenis_kekerasan,
                                                )
                                            }}
                                        </h3>

                                        <div
                                            class="mt-2 flex flex-col gap-1 text-xs text-gray-500 sm:mt-3 sm:flex-row sm:flex-wrap sm:gap-x-8 sm:gap-y-2 sm:text-sm"
                                        >
                                            <span>
                                                Dilaporkan
                                                <strong
                                                    class="font-semibold text-gray-700"
                                                >
                                                    {{
                                                        formatDate(
                                                            report.created_at,
                                                        )
                                                    }}
                                                </strong>
                                            </span>

                                            <span>
                                                Pembaruan terakhir
                                                <strong
                                                    class="font-semibold text-gray-700"
                                                >
                                                    {{
                                                        formatDate(
                                                            report.updated_at,
                                                        )
                                                    }}
                                                </strong>
                                            </span>
                                        </div>
                                    </div>

                                    <svg
                                        class="hidden h-6 w-6 shrink-0 text-gray-400 sm:block"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M9 5l7 7-7 7"
                                        />
                                    </svg>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Lihat Progress -->
                <div v-else-if="currentStep === 3" class="bg-white">
                    <div class="px-4 py-5 sm:px-6 sm:py-6">
                        <!-- Header Laporan -->
                        <div
                            class="mb-6 flex flex-col gap-3 border-b border-gray-100 pb-5 sm:flex-row sm:items-start sm:justify-between"
                        >
                            <!-- Kiri -->
                            <div class="min-w-0">
                                <p class="mb-1 text-xs text-gray-400">
                                    {{ selectedReport.case_number }}
                                </p>

                                <h2 class="text-xl font-bold text-gray-900 sm:text-2xl">
                                    {{
                                        getLabel(
                                            jenisKekerasanOptions,
                                            selectedReport.jenis_kekerasan,
                                        )
                                    }}
                                </h2>

                                <div
                                    class="mt-2 flex flex-col gap-1 text-xs text-gray-500 sm:flex-row sm:items-center sm:gap-4"
                                >
                                    <span>
                                        Dilaporkan
                                        <strong class="text-gray-700">
                                            {{
                                                formatDate(
                                                    selectedReport.created_at,
                                                    false,
                                                )
                                            }}
                                        </strong>
                                    </span>

                                    <span>
                                        Pembaruan
                                        <strong class="text-gray-700">
                                            {{
                                                formatDate(
                                                    selectedReport.updated_at,
                                                    false,
                                                )
                                            }}
                                        </strong>
                                    </span>
                                </div>
                            </div>

                            <!-- Kanan -->
                            <ProgressBadge :status="selectedReport.progress" size="large" />
                        </div>

                        <!-- Status Banner -->
                        <div
                            class="mb-6 flex items-start gap-3 rounded-xl border px-3 py-3 sm:px-4 sm:py-4"
                            :class="currentStatusColor.container"
                        >
                            <svg
                                class="mt-0.5 h-5 w-5 shrink-0"
                                :class="currentStatusColor.icon"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>

                            <div>
                                <p
                                    class="text-sm font-bold"
                                    :class="currentStatusColor.title"
                                >
                                    {{ currentStatus.title }}
                                </p>

                                <p
                                    class="mt-0.5 text-sm leading-relaxed text-gray-600"
                                >
                                    {{ currentStatus.description }}
                                </p>
                            </div>
                        </div>

                        <!-- Alur Penanganan -->
                        <p
                            class="mb-4 text-xs font-bold tracking-widest text-gray-400 uppercase"
                        >
                            Alur Penanganan
                        </p>

                        <div class="flex flex-col">
                            <template
                                v-for="(item, index) in timelineItems"
                                :key="index"
                            >
                                <div class="flex gap-3 sm:gap-4">
                                    <!-- Circle & Line -->
                                    <div class="flex flex-col items-center">
                                        <div
                                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold sm:h-8 sm:w-8"
                                            :class="
                                                item.done
                                                    ? 'bg-[#1A5BA6] text-white'
                                                    : 'border border-gray-300 bg-white text-gray-400'
                                            "
                                        >
                                            <svg
                                                v-if="item.done"
                                                class="h-3.5 w-3.5 sm:h-4 sm:w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2.5"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M5 13l4 4L19 7"
                                                />
                                            </svg>
                                            <span v-else>{{ index + 1 }}</span>
                                        </div>
                                        <div
                                            v-if="
                                                index < timelineItems.length - 1
                                            "
                                            class="my-1 w-px flex-1"
                                            :class="
                                                item.done
                                                    ? 'bg-[#1A5BA6]'
                                                    : 'bg-gray-200'
                                            "
                                        ></div>
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-1 pb-5 sm:pb-6">
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            <span
                                                class="text-sm font-semibold"
                                                :class="
                                                    item.done
                                                        ? 'text-gray-700'
                                                        : 'text-gray-400'
                                                "
                                                >{{ item.title }}</span
                                            >
                                            <span
                                                v-if="item.date"
                                                class="text-xs text-gray-400"
                                                >{{ item.date }}</span
                                            >
                                            <span
                                                v-if="item.badge"
                                                class="rounded-full border border-gray-200 px-2 py-0.5 text-xs text-gray-400"
                                                >{{ item.badge }}</span
                                            >
                                        </div>
                                        <p
                                            v-if="item.desc"
                                            class="mt-1 text-sm leading-relaxed text-gray-500"
                                        >
                                            {{ item.desc }}
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div
                        class="flex flex-col gap-3 border-t border-gray-200 bg-white px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                    >
                        <button
                            class="flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 active:scale-[0.98]"
                            @click="prevStep"
                        >
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"
                                />
                            </svg>
                            Daftar Kasus
                        </button>
                        <a
                            href="https://wa.me/6285171047293"
                            target="_blank"
                            class="flex items-center justify-center gap-2 rounded-lg bg-orange-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-orange-600 active:scale-[0.98]"
                        >
                            Hubungi Sekretariat Satgas
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
