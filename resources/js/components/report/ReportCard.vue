<script setup lang="ts">
import { getLabel } from '@/lib/getLabel';
import { jenisKekerasanOptions } from '@/constants/jenisKekerasanOptions';
import { formatDate } from '@/lib/formatDate';
import { Link } from '@inertiajs/vue3';
import { show } from '@/routes/satgas/reports/handling/';
import { getAvatarColor, getInitials } from '@/composables/useInitials';
import ProgressBadge from '@/components/ProgressBadge.vue';

const props = defineProps<{
    report: any;
}>();
</script>

<template>
    <div
        class="w-full overflow-hidden rounded-2xl border border-gray-200 border-l-[#F5821F] border-l-4 bg-white font-sans shadow-sm sm:max-w-2xl"
    >
        <div class="p-4 sm:p-6">
            <!-- HEADER -->
            <div
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <h2
                    class="text-lg font-bold text-gray-900 sm:text-[22px]"
                >
                    {{ getLabel(jenisKekerasanOptions, report.title) }}
                </h2>
                <ProgressBadge :status="report.progress" size="large" />
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
                        Nomor Laporan
                    </p>
                    <p class="font-mono text-sm font-medium text-gray-900 sm:text-base">
                        {{ report.caseNumber }}
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
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold group-data-[collapsible=icon]:h-7 group-data-[collapsible=icon]:w-7"
                            :class="getAvatarColor(m.name)"
                        >
                            {{ getInitials(m.name) }}
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
                    class="group flex items-center rounded-md text-xs font-semibold text-[#1A5BA6] transition-all hover:text-[#14497F] focus:ring-2 focus:ring-blue-300 focus:outline-none sm:text-sm"
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
