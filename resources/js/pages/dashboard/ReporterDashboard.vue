<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="space-y-6">
        <!-- Header + Quick Action -->
        <div class="rounded-xl border p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-lg font-semibold">Dashboard Pelapor</h1>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Laporkan kejadian dan pantau status laporanmu dengan
                        mudah.
                    </p>
                </div>

                <Link
                    href="/reports/create"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700"
                >
                    + Buat Laporan
                </Link>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border p-4">
                <p class="text-sm text-muted-foreground">Total Laporan</p>
                <h2 class="mt-2 text-2xl font-bold">5</h2>
            </div>

            <div class="rounded-xl border p-4">
                <p class="text-sm text-muted-foreground">Diproses</p>
                <h2 class="mt-2 text-2xl font-bold">2</h2>
            </div>

            <div class="rounded-xl border p-4">
                <p class="text-sm text-muted-foreground">Selesai</p>
                <h2 class="mt-2 text-2xl font-bold">3</h2>
            </div>
        </div>

        <!-- Aktivitas -->
        <div class="rounded-xl border p-6">
            <h2 class="font-semibold">Aktivitas Terbaru</h2>

            <div class="mt-4 space-y-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-medium">Laporan dikirim</p>
                        <p class="text-sm text-muted-foreground">
                            Kekerasan verbal di lingkungan kerja
                        </p>
                    </div>
                    <span class="text-xs text-muted-foreground">
                        Baru saja
                    </span>
                </div>

                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-medium">Status diperbarui</p>
                        <p class="text-sm text-muted-foreground">
                            Dalam proses penanganan
                        </p>
                    </div>
                    <span class="text-xs text-muted-foreground">
                        1 hari lalu
                    </span>
                </div>
            </div>
        </div>

        <!-- Tips -->
        <div class="rounded-xl border p-4">
            <p class="text-sm text-muted-foreground">
                💡 Pastikan laporan berisi kronologi, waktu, dan lokasi agar
                lebih cepat diproses.
            </p>
        </div>
    </div>
</template>
