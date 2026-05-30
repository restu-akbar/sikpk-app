<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';
</script>

<template>
    <div class="min-h-svh lg:grid lg:grid-cols-2">

        <!-- Left panel: brand identity (lg+ only) -->
        <div class="relative hidden h-full flex-col justify-between bg-brand p-10 text-white lg:flex">

            <!-- Logo area: icon 56px + institution name -->
            <Link :href="home()" class="flex items-center gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white/15">
                    <AppLogoIcon class="size-12" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <span class="font-display text-xl font-bold leading-none text-white">
                        Satgas PPK
                    </span>
                    <span class="font-display text-base leading-none text-white/70">
                        Politeknik Negeri Bandung
                    </span>
                </div>
            </Link>

            <!-- App title: text-6xl = 60px, natural word-wrap creates 3 lines -->
            <div>
                <h1 class="font-display text-5xl font-extrabold leading-tight text-white">
                    Aplikasi Pengelolaan dan Pelaporan Kekerasan Politeknik Negeri Bandung
                </h1>
            </div>

            <!-- Pakta Integritas quote block
                 text-xl (20px) wraps italic text to 2 lines.
                 Attribution pada baris ke-3. -->
            <div class="rounded-r-lg border-l-4 border-brand-accent bg-white/10 px-6 py-5">
                <p class="font-display text-l italic leading-relaxed text-white/90">
                    "Kerahasiaan adalah komitmen utama. Setiap data
                    yang Anda akses tunduk pada Pakta Integritas Satgas."
                </p>
                <p class="mt-3 font-display text-m font-semibold uppercase tracking-widest text-white/55">
                    Pakta Integritas &middot; Satgas SIKPK
                </p>
            </div>

        </div>

        <!-- Right panel: form content -->
        <!-- bg-[#F6F7F9] matches the Figma right-panel background -->
        <div class="flex min-h-svh items-center justify-center bg-[#F6F7F9] px-8 py-12">
            <div class="w-full max-w-md">
                <slot />
            </div>
        </div>

    </div>
</template>
