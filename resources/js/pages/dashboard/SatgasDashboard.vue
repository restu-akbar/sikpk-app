<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { FileInput, FileX, Loader, CheckCircle2, Accessibility } from 'lucide-vue-next';

import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

import BarChart from './BarChart.vue';
import LineChart from './LineChart.vue';
import PieChart from './PieChart.vue';
import StackedBarChart from './StackedBarChart.vue';
import type { DashboardAnalytics } from './types';

import { dashboard } from '@/routes/satgas';

interface Props {
    analytics: DashboardAnalytics;
}

const props = defineProps<Props>();

const semesterOptions = [
    { value: 'all', label: 'Semua Semester' },
    { value: '1', label: 'Semester Ganjil (Jan - Jun)' },
    { value: '2', label: 'Semester Genap (Jul - Des)' },
];

const yearOptions = computed(() => props.analytics.filter.availableYears);

function applyFilter(year: string | number, semester: string) {
    router.get(
        dashboard().url,
        { year, semester },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

const stats = computed(() => [
    {
        label: 'Laporan Masuk',
        value: props.analytics.laporan.masuk,
        icon: FileInput,
        accent: 'text-blue-500',
        bg: 'bg-blue-500/10',
    },
    {
        label: 'Laporan Dibatalkan',
        value: props.analytics.laporan.dibatalkan,
        icon: FileX,
        accent: 'text-red-500',
        bg: 'bg-red-500/10',
    },
    {
        label: 'Penanganan Berlangsung',
        value: props.analytics.laporan.berlangsung,
        icon: Loader,
        accent: 'text-amber-500',
        bg: 'bg-amber-500/10',
    },
    {
        label: 'Laporan Selesai',
        value: props.analytics.laporan.selesai,
        icon: CheckCircle2,
        accent: 'text-emerald-500',
        bg: 'bg-emerald-500/10',
    },
]);
</script>

<template>
    <div class="space-y-6">
        <!-- Header + Filter -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    Dashboard Laporan Kekerasan
                </h1>

                <p class="text-sm text-muted-foreground">
                    Pantau statistik laporan dan demografi penanganan kasus.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <Select
                    :model-value="String(analytics.filter.year)"
                    @update:model-value="(value) => applyFilter(value as string, analytics.filter.semester)"
                >
                    <SelectTrigger class="w-[140px]">
                        <SelectValue placeholder="Tahun" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Semua Tahun</SelectItem>
                        <SelectItem v-for="year in yearOptions" :key="year" :value="String(year)">
                            {{ year }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <Select
                    :model-value="analytics.filter.semester"
                    @update:model-value="(value) => applyFilter(analytics.filter.year, value as string)"
                >
                    <SelectTrigger class="w-[190px]">
                        <SelectValue placeholder="Semester" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in semesterOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <!-- Tren Laporan -->
        <LineChart
            :title="`Tren Laporan Masuk (${analytics.filter.year === 'all' ? 'Tahun Ini' : analytics.filter.year})`"
            :items="analytics.trend"
        />

        <!-- Data Laporan -->
        <div class="grid gap-4 md:grid-cols-4">
            <div
                v-for="stat in stats"
                :key="stat.label"
                class="rounded-xl border bg-card p-4 shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-muted-foreground">
                        {{ stat.label }}
                    </p>

                    <span :class="['rounded-lg p-2', stat.bg]">
                        <component :is="stat.icon" :class="['size-4', stat.accent]" />
                    </span>
                </div>

                <h2 class="mt-3 text-3xl font-bold">{{ stat.value }}</h2>
            </div>
        </div>

        <!-- Jenis Kekerasan & Disabilitas -->
        <div class="grid gap-4 md:grid-cols-2">
            <PieChart title="Jenis Kekerasan" :items="analytics.jenisKekerasan" />

            <div class="flex flex-col gap-4">
                <div class="flex-1 rounded-xl border bg-card p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-muted-foreground">
                            Korban Disabilitas
                        </p>

                        <span class="rounded-lg bg-violet-500/10 p-2">
                            <Accessibility class="size-4 text-violet-500" />
                        </span>
                    </div>

                    <h2 class="mt-3 text-3xl font-bold">{{ analytics.disabilitas.korban }}</h2>
                </div>

                <div class="flex-1 rounded-xl border bg-card p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-muted-foreground">
                            Terlapor Disabilitas
                        </p>

                        <span class="rounded-lg bg-violet-500/10 p-2">
                            <Accessibility class="size-4 text-violet-500" />
                        </span>
                    </div>

                    <h2 class="mt-3 text-3xl font-bold">{{ analytics.disabilitas.terlapor }}</h2>
                </div>
            </div>
        </div>

        <!-- Demografi -->
        <div class="space-y-6">
            <h2 class="text-lg font-semibold">
                Demografi Laporan
            </h2>

            <div>
                <h3 class="mb-3 text-sm font-semibold text-muted-foreground uppercase">
                    Pelapor
                </h3>

                <div class="grid gap-4 md:grid-cols-2">
                    <PieChart
                        title="Peran Pelapor"
                        :items="analytics.demografi.pelaporPeran"
                    />

                    <BarChart
                        title="Jurusan Pelapor"
                        :items="analytics.demografi.pelaporJurusan"
                    />
                </div>
            </div>

            <div>
                <h3 class="mb-3 text-sm font-semibold text-muted-foreground uppercase">
                    Korban
                </h3>

                <div class="grid gap-4 md:grid-cols-2">
                    <StackedBarChart
                        title="Peran & Gender Korban"
                        :data="analytics.demografi.korbanPeranGender"
                    />

                    <BarChart
                        title="Jurusan Korban"
                        :items="analytics.demografi.korbanJurusan"
                    />
                </div>
            </div>

            <div>
                <h3 class="mb-3 text-sm font-semibold text-muted-foreground uppercase">
                    Terlapor
                </h3>

                <div class="grid gap-4 md:grid-cols-2">
                    <StackedBarChart
                        title="Peran & Gender Terlapor"
                        :data="analytics.demografi.terlaporPeranGender"
                    />

                    <BarChart
                        title="Jurusan Terlapor"
                        :items="analytics.demografi.terlaporJurusan"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
