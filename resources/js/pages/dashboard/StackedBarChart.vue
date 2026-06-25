<script setup lang="ts">
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Tooltip,
    Legend,
} from 'chart.js';
import ChartDataLabels from 'chartjs-plugin-datalabels';

import type { CrosstabData } from './types';

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend, ChartDataLabels);

interface Props {
    title: string;
    data: CrosstabData;
}

const props = defineProps<Props>();

const colors: Record<string, string> = {
    'Laki-laki': '#3b82f6',
    Perempuan: '#ec4899',
};

const fallbackColors = ['#3b82f6', '#ec4899', '#f59e0b', '#10b981'];

const hasData = computed(() => props.data.labels.length > 0);

const chartData = computed(() => ({
    labels: props.data.labels,
    datasets: props.data.series.map((series, index) => ({
        label: series.label,
        data: series.data,
        backgroundColor: colors[series.label] ?? fallbackColors[index % fallbackColors.length],
        borderRadius: 4,
        maxBarThickness: 32,
    })),
}));

const chartOptions = {
    indexAxis: 'y' as const,
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom' as const,
            labels: { boxWidth: 12, font: { size: 11 } },
        },
        datalabels: {
            color: '#fff',
            font: { size: 10, weight: 'bold' as const },
            formatter: (value: number) => (value > 0 ? value : ''),
        },
    },
    scales: {
        x: {
            stacked: true,
            beginAtZero: true,
            ticks: { precision: 0 },
        },
        y: {
            stacked: true,
        },
    },
};
</script>

<template>
    <div class="rounded-xl border bg-card p-4 shadow-sm">
        <h3 class="text-sm font-semibold text-foreground">{{ title }}</h3>

        <div v-if="!hasData" class="mt-6 text-sm text-muted-foreground">
            Belum ada data.
        </div>

        <div v-else class="mt-4 h-64">
            <Bar :data="chartData" :options="chartOptions" />
        </div>
    </div>
</template>
