<script setup lang="ts">
interface Props {
    isKetua?: boolean;
}

defineProps<Props>();
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-bold tracking-tight">
                Dashboard Laporan Kekerasan
            </h1>

            <p class="text-sm text-muted-foreground">
                Pantau laporan, tindak lanjut, dan aktivitas penanganan kasus.
            </p>
        </div>

        <!-- Statistik -->
        <div class="grid gap-4 md:grid-cols-4">
            <div class="rounded-xl border p-4">
                <p class="text-sm text-muted-foreground">Total Laporan</p>

                <h2 class="mt-2 text-3xl font-bold">128</h2>
            </div>

            <div class="rounded-xl border p-4">
                <p class="text-sm text-muted-foreground">Diproses</p>

                <h2 class="mt-2 text-3xl font-bold text-yellow-500">32</h2>
            </div>

            <div class="rounded-xl border p-4">
                <p class="text-sm text-muted-foreground">Selesai</p>

                <h2 class="mt-2 text-3xl font-bold text-green-500">84</h2>
            </div>

            <div v-if="isKetua" class="rounded-xl border p-4">
                <p class="text-sm text-muted-foreground">Total Satgas</p>

                <h2 class="mt-2 text-3xl font-bold">12</h2>
            </div>
        </div>

        <!-- Aktivitas -->
        <div class="rounded-xl border p-4">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="font-semibold">Aktivitas Terbaru</h2>

                <span class="text-xs text-muted-foreground"> Hari ini </span>
            </div>

            <div class="space-y-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-medium">Laporan baru masuk</p>

                        <p class="text-sm text-muted-foreground">
                            Kasus kekerasan verbal dilaporkan.
                        </p>
                    </div>

                    <span class="text-xs text-muted-foreground">
                        10 menit lalu
                    </span>
                </div>

                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-medium">Status laporan diperbarui</p>

                        <p class="text-sm text-muted-foreground">
                            Laporan dipindahkan ke tahap investigasi.
                        </p>
                    </div>

                    <span class="text-xs text-muted-foreground">
                        1 jam lalu
                    </span>
                </div>

                <div v-if="isKetua" class="flex items-start justify-between">
                    <div>
                        <p class="font-medium">Penugasan Satgas</p>

                        <p class="text-sm text-muted-foreground">
                            Ketua menugaskan anggota untuk tindak lanjut kasus.
                        </p>
                    </div>

                    <span class="text-xs text-muted-foreground">
                        3 jam lalu
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
