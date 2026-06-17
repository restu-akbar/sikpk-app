<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        status: string;
        size?: 'normal' | 'large';
    }>(),
    { size: 'normal' },
);

const colorMap: Record<string, string> = {
    'Laporan Baru': 'bg-progress-baru-bg text-progress-baru',
    Klarifikasi: 'bg-progress-klarifikasi-bg text-progress-klarifikasi',
    Pemeriksaan: 'bg-progress-pemeriksaan-bg text-progress-pemeriksaan',
    Kesimpulan: 'bg-progress-kesimpulan-bg text-progress-kesimpulan',
    Pasca: 'bg-progress-pasca-bg text-progress-pasca',
};

const colorClasses = colorMap[props.status] ?? 'bg-gray-100 text-gray-600';

const sizeClasses = computed(() =>
    props.size === 'large'
        ? 'px-3 py-1 text-xs sm:text-sm gap-2'
        : 'px-2.5 py-0.5 text-xs gap-1.5',
);

const dotSize = computed(() =>
    props.size === 'large' ? 'h-2 w-2' : 'h-1.5 w-1.5',
);
</script>

<template>
    <span
        class="inline-flex items-center rounded-full font-semibold"
        :class="[colorClasses, sizeClasses]"
    >
        <span
            class="rounded-full bg-current opacity-80"
            :class="dotSize"
        />
        {{ status }}
    </span>
</template>
