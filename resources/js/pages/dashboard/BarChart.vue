<script setup lang="ts">
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Tooltip,
} from 'chart.js';

import type { DistributionItem } from './types';

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip);

interface Props {
    title: string;
    items: DistributionItem[];
}

const props = defineProps<Props>();

const colors = [
    '#ef4444',
    '#f59e0b',
    '#3b82f6',
    '#8b5cf6',
    '#10b981',
    '#ec4899',
    '#06b6d4',
    '#f97316',
];

const chartData = computed(() => ({
    labels: props.items.map((item) => item.label),
    datasets: [
        {
            data: props.items.map((item) => item.total),
            backgroundColor: props.items.map((_, index) => colors[index % colors.length]),
            borderRadius: 4,
            maxBarThickness: 40,
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        datalabels: { display: false },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: { precision: 0 },
        },
    },
};
</script>

<template>
    <div class="rounded-xl border bg-card p-4 shadow-sm">
        <h3 class="text-sm font-semibold text-foreground">{{ title }}</h3>

        <div v-if="items.length === 0" class="mt-6 text-sm text-muted-foreground">
            Belum ada data.
        </div>

        <div v-else class="mt-4 h-64">
            <Bar :data="chartData" :options="chartOptions" />
        </div>
    </div>
</template>
