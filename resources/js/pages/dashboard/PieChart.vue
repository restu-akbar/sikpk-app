<script setup lang="ts">
import { computed } from 'vue';
import { Pie } from 'vue-chartjs';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import ChartDataLabels from 'chartjs-plugin-datalabels';

import type { DistributionItem } from './types';

ChartJS.register(ArcElement, Tooltip, Legend, ChartDataLabels);

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
            borderWidth: 0,
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'right' as const,
            labels: { boxWidth: 12, font: { size: 11 } },
        },
        datalabels: {
            color: '#fff',
            font: { size: 11, weight: 'bold' as const },
            formatter: (value: number, context: any) => {
                const data: number[] = context.dataset.data;
                const total = data.reduce((sum, item) => sum + item, 0);
                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;

                return `${value} (${percentage}%)`;
            },
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
            <Pie :data="chartData" :options="chartOptions" />
        </div>
    </div>
</template>
