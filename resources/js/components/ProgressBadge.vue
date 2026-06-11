<script setup lang="ts">
const props = defineProps<{
    status: string;
}>();

const colorMap: Record<string, string> = {
    'Laporan Baru': 'bg-progress-baru-bg text-progress-baru',
    Klarifikasi: 'bg-progress-klarifikasi-bg text-progress-klarifikasi',
    Pemeriksaan: 'bg-progress-pemeriksaan-bg text-progress-pemeriksaan',
    Kesimpulan: 'bg-progress-kesimpulan-bg text-progress-kesimpulan',
    Pasca: 'bg-progress-pasca-bg text-progress-pasca',
};

const classes = colorMap[props.status] ?? 'bg-gray-100 text-gray-600';
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
        :class="classes"
    >
        <span class="h-1.5 w-1.5 rounded-full bg-current opacity-80" />
        {{ status }}
    </span>
</template>
