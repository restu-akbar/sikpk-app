<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';
</script>

<template>
    <div class="min-h-svh lg:grid lg:grid-cols-2">

        <!-- Left panel: brand identity (lg+ only) -->
        <div class="relative hidden h-full flex-col justify-between bg-brand p-10 text-white lg:flex">

            <!-- Logo area: icon 40px + institution name -->
            <Link :href="home()" class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl">
                    <AppLogoIcon class="size-full object-contain" />
                </div>
                <div class="flex flex-col gap-0.5">
                    <span class="font-display text-sm font-bold leading-none text-white">
                        Satgas PPK
                    </span>
                    <span class="font-display text-xs leading-none text-white/70">
                        Politeknik Negeri Bandung
                    </span>
                </div>
            </Link>

            <!-- App title: text-4xl = 36px, natural word-wrap -->
            <div>
                <h1 class="font-display text-4xl font-extrabold leading-tight text-white">
                    Aplikasi Pengelolaan dan Pelaporan Kekerasan Politeknik Negeri Bandung
                </h1>
            </div>

            <!-- Pakta Integritas quote block -->
            <div class="rounded-r-lg border-l-4 border-brand-accent bg-white/10 px-5 py-4">
                <p class="font-display text-base italic leading-relaxed text-white/90">
                    "Kerahasiaan adalah komitmen utama. Setiap data
                    yang Anda akses tunduk pada Pakta Integritas Satgas."
                </p>
                <p class="mt-3 font-display text-[11px] font-semibold uppercase tracking-widest text-white/55">
                    Pakta Integritas &middot; Satgas SIKPK
                </p>
            </div>

        </div>

        <!-- Right panel: form content -->
        <!-- bg-[#F6F7F9] matches the Figma right-panel background -->
        <div class="flex min-h-svh items-center justify-center bg-auth-bg px-8 py-12">
            <div class="w-full max-w-md">
                <slot />
            </div>
        </div>

    </div>
</template>
