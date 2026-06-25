<script setup lang="ts">
import { computed } from 'vue';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Filler,
    Tooltip,
} from 'chart.js';

import type { DistributionItem } from './types';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Filler, Tooltip);

interface Props {
    title: string;
    items: DistributionItem[];
}

const props = defineProps<Props>();

const chartData = computed(() => ({
    labels: props.items.map((item) => item.label),
    datasets: [
        {
            label: 'Laporan',
            data: props.items.map((item) => item.total),
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            fill: true,
            tension: 0.3,
            pointBackgroundColor: '#3b82f6',
            pointRadius: 4,
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
            <Line :data="chartData" :options="chartOptions" />
        </div>
    </div>
</template>
