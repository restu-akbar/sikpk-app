<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Mail, Phone, Shield } from 'lucide-vue-next';
import type { Auth } from '@/types';
import { getInitials } from '@/composables/useInitials';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { home } from '@/routes';
import { logout } from '@/routes/reporter';

interface Props {
    currentPage?: string;
}

withDefaults(defineProps<Props>(), {
    currentPage: 'Beranda',
});

const page = usePage<{ auth: Auth }>();
const user = computed(() => page.props.auth?.user ?? null);

const navItems = ['Beranda', 'Lacak Kasus', 'Informasi', 'Lokasi Satgas'] as const;

const dropdownOpen = ref(false);
let closeTimer: ReturnType<typeof setTimeout> | null = null;

function openDropdown() {
    if (closeTimer) clearTimeout(closeTimer);
    dropdownOpen.value = true;
}

function scheduleClose() {
    closeTimer = setTimeout(() => {
        dropdownOpen.value = false;
    }, 150);
}

function handleLogout() {
    router.post(logout().url);
}

function avatarColor(name: string): string {
    const colors = [
        'bg-violet-600',
        'bg-blue-600',
        'bg-emerald-600',
        'bg-rose-600',
        'bg-amber-600',
        'bg-indigo-600',
    ];
    const index = (name.charCodeAt(0) ?? 0) % colors.length;
    return colors[index];
}
</script>

<template>
    <header class="sticky top-0 z-50">

        <!-- Emergency strip -->
        <div class="bg-brand-dark py-2.5">
            <div class="mx-auto flex max-w-screen-xl items-center justify-center gap-6 px-8">
                <span class="flex items-center gap-1.5 text-xs font-semibold text-white">
                    <Mail class="size-3.5 shrink-0" />
                    Butuh Bantuan Segera?
                </span>

                <span class="h-3 w-px bg-white/25" aria-hidden="true" />

                <span class="flex items-center gap-1.5 text-xs font-medium text-white/90">
                    <Phone class="size-3.5 shrink-0" />
                    Satpam Polban:
                    <strong class="ml-0.5 font-bold">(022) 2013789</strong>
                </span>

                <span class="h-3 w-px bg-white/25" aria-hidden="true" />

                <span class="flex items-center gap-1.5 text-xs font-medium text-white/90">
                    <Shield class="size-3.5 shrink-0" />
                    Polisi:
                    <strong class="ml-0.5 font-bold">110</strong>
                </span>
            </div>
        </div>

        <!-- Main navbar -->
        <nav class="border-b border-border bg-white">
            <div class="mx-auto flex max-w-screen-xl items-center justify-between px-8 py-3">

                <!-- Logo -->
                <Link :href="home()" class="flex shrink-0 items-center gap-2">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-lg">
                        <AppLogoIcon class="size-8" />
                    </div>
                    <div class="flex flex-col leading-none">
                        <span class="text-sm font-bold tracking-tight text-foreground">SIKPK</span>
                        <span class="text-[10px] font-medium tracking-widest text-muted-foreground">POLBAN</span>
                    </div>
                </Link>

                <!-- Nav items -->
                <div class="flex items-center gap-1">
                    <span
                        v-for="item in navItems"
                        :key="item"
                        :class="[
                            'cursor-default rounded px-4 py-1.5 text-sm font-semibold transition-colors',
                            item === currentPage
                                ? 'bg-[#E7EEF7] text-[#0F3A6C]'
                                : 'text-[#403B34] hover:bg-accent hover:text-foreground',
                        ]"
                    >
                        {{ item }}
                    </span>
                </div>

                <!-- Right side actions -->
                <div class="flex shrink-0 items-center gap-3">

                    <!-- Not logged in -->
                    <template v-if="!user">
                        <Button as-child variant="outline" size="default">
                            <Link href="/login">Masuk</Link>
                        </Button>
                        <Button variant="brand-accent" size="default">
                            Buat Laporan
                        </Button>
                    </template>

                    <!-- Logged in -->
                    <template v-else>
                        <Button variant="brand-accent" size="default">
                            Buat Laporan
                        </Button>

                        <!-- User avatar + dropdown -->
                        <div class="relative">
                            <div
                                :class="[
                                    'flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-full text-sm font-semibold text-white select-none',
                                    avatarColor(user.name),
                                ]"
                                @mouseenter="openDropdown"
                                @mouseleave="scheduleClose"
                            >
                                {{ getInitials(user.name) }}
                            </div>

                            <!-- Dropdown card -->
                            <div
                                v-if="dropdownOpen"
                                class="absolute right-0 top-full mt-2 w-60 rounded-xl border border-border bg-white shadow-lg"
                                @mouseenter="openDropdown"
                                @mouseleave="scheduleClose"
                            >
                                <!-- User info -->
                                <div class="px-4 py-3">
                                    <p class="text-sm font-semibold text-foreground">{{ user.name }}</p>
                                    <p class="mt-0.5 text-xs text-muted-foreground">{{ user.email }}</p>
                                </div>

                                <div class="border-t border-border px-3 py-2.5 flex items-center justify-between gap-2">
                                    <Button variant="ghost" size="sm" class="text-xs text-muted-foreground">
                                        Lacak Kasus Saya
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="text-xs"
                                        @click="handleLogout"
                                    >
                                        Keluar
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </template>

                </div>
            </div>
        </nav>

    </header>
</template>
