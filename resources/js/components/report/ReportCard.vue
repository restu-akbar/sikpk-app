<script setup lang="ts">
import { getLabel } from '@/lib/getLabel';
import { jenisKekerasanOptions } from '@/constants/jenisKekerasanOptions';
import { formatDate } from '@/lib/formatDate';
import { Link } from '@inertiajs/vue3';
import { show } from '@/routes/satgas/reports/handling/';

type Member = {
    name: string;
    initials: string;
};

type Report = {
    id: string;
    caseNumber: string;
    title: string;
    status: string;
    statusColor?: string;

    reportDate: string;
    reporter: string;
    progress: string;
    teamNumber: string;

    members: Member[];
};

defineProps<{
    report: Report;
}>();
</script>

<template>
    <div
        class="w-full rounded-2xl border border-gray-200 font-sans shadow-sm sm:max-w-2xl"
        :class="`border-l-4 border-l-${report.statusColor ?? 'orange-500'}`"
    >
        <div class="p-4 sm:p-6">
            <!-- HEADER -->
            <div
                class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
            >
                <div>
                    <p class="font-mono text-xs text-gray-500 sm:text-sm">
                        {{ report.caseNumber }}
                    </p>

                    <h2
                        class="mt-1 text-lg font-bold text-gray-900 sm:mt-2 sm:text-[22px]"
                    >
                        {{ getLabel(jenisKekerasanOptions, report.title) }}
                    </h2>
                </div>

                <div
                    class="flex w-fit items-center rounded-full px-3 py-1 text-xs font-semibold sm:text-sm"
                    :class="
                        report.statusColor
                            ? `bg-${report.statusColor}-100 text-${report.statusColor}-700`
                            : 'bg-purple-100 text-purple-700'
                    "
                >
                    <span
                        class="mr-2 h-2 w-2 rounded-full"
                        :class="
                            report.statusColor
                                ? `bg-${report.statusColor}-600`
                                : 'bg-purple-600'
                        "
                    ></span>
                    {{ report.status }}
                </div>
            </div>

            <hr class="my-4 border-gray-200 sm:my-5" />

            <!-- INFO -->
            <div
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-x-8 sm:gap-y-6"
            >
                <div>
                    <p
                        class="mb-1 text-xs font-bold tracking-widest text-gray-500 uppercase"
                    >
                        Tanggal Laporan
                    </p>
                    <p class="text-sm font-medium text-gray-900 sm:text-base">
                        {{ formatDate(report.reportDate, false) }}
                    </p>
                </div>

                <div>
                    <p
                        class="mb-1 text-xs font-bold tracking-widest text-gray-500 uppercase"
                    >
                        Pelapor
                    </p>
                    <p class="text-sm font-medium text-gray-900 sm:text-base">
                        {{ report.reporter }}
                    </p>
                </div>

                <div>
                    <p
                        class="mb-1 text-xs font-bold tracking-widest text-gray-500 uppercase"
                    >
                        Progress Saat Ini
                    </p>
                    <p class="text-sm font-medium text-gray-900 sm:text-base">
                        {{ report.progress }}
                    </p>
                </div>

                <div>
                    <p
                        class="mb-1 text-xs font-bold tracking-widest text-gray-500 uppercase"
                    >
                        No. Tim Penanganan
                    </p>
                    <p
                        class="font-mono text-sm font-medium text-gray-900 sm:text-base"
                    >
                        {{ report.teamNumber }}
                    </p>
                </div>
            </div>

            <hr class="my-4 border-gray-200 sm:my-5" />

            <!-- FOOTER -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-center">
                    <div class="flex -space-x-2 sm:-space-x-1.5">
                        <div
                            v-for="(m, i) in report.members"
                            :key="i"
                            class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-white bg-[#ebf0f9] text-[10px] font-bold text-[#1e3a8a] sm:h-8 sm:w-8 sm:text-xs"
                        >
                            {{ m.initials }}
                        </div>
                    </div>

                    <span
                        class="ml-3 font-mono text-xs text-gray-600 sm:ml-4 sm:text-sm"
                    >
                        {{ report.members.length }} anggota
                    </span>
                </div>

                <Link
                    :href="show.url(report.id)"
                    class="group flex items-center rounded-md text-xs font-semibold text-[#2563eb] transition-all hover:text-blue-800 focus:ring-2 focus:ring-blue-300 focus:outline-none sm:text-sm"
                >
                    <span class="group-hover:underline"> Buka penanganan </span>

                    <svg
                        class="ml-1 h-3 w-3 transition-transform group-hover:translate-x-1 sm:h-4 sm:w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5l7 7-7 7"
                        />
                    </svg>
                </Link>
            </div>
        </div>
    </div>
</template>
