<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Mail, Phone, Shield, Menu, X } from 'lucide-vue-next';
import type { Auth } from '@/types';
import { getInitials } from '@/composables/useInitials';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { logout } from '@/routes/reporter';
import { create } from '@/routes/reports';
import { login as loginGoogle } from '@/routes/google';
import { track } from '@/routes/reports';

const page = usePage<{ auth: Auth }>();

const activeNav = computed(() => {
    const url = page.url;
    if (url.startsWith('/reports/track')) return 'Lacak Kasus';
    if (url === '/landing' || url === '/') return 'Beranda';
    return '';
});
const user = computed(() => page.props.auth?.user ?? null);

const navItems = [
    { label: 'Beranda', href: '/landing' },
    { label: 'Informasi', href: '/landing#informasi' },
    { label: 'Lokasi Satgas', href: '/landing#lokasi-satgas' },
    { label: 'Lacak Kasus', href: '/reports/track' },
] as const;

const mobileMenuOpen = ref(false);
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
        <div class="bg-brand-dark py-2 md:py-2.5">
            <div
                class="mx-auto flex max-w-screen-xl items-center justify-center gap-3 px-4 md:gap-6 md:px-8"
            >
                <span
                    class="hidden items-center gap-1.5 text-xs font-semibold text-white sm:flex"
                >
                    <Mail class="size-3.5 shrink-0" />
                    Butuh Bantuan Segera?
                </span>

                <span class="hidden h-3 w-px bg-white/25 sm:block" aria-hidden="true" />

                <span
                    class="flex items-center gap-1.5 text-[11px] font-medium text-white/90 sm:text-xs"
                >
                    <Phone class="size-3.5 shrink-0" />
                    <span class="hidden sm:inline">Satpam Polban:</span>
                    <strong class="ml-0.5 font-bold">+62 859-2436-2219</strong>
                </span>

                <span class="h-3 w-px bg-white/25" aria-hidden="true" />

                <span
                    class="flex items-center gap-1.5 text-[11px] font-medium text-white/90 sm:text-xs"
                >
                    <Shield class="size-3.5 shrink-0" />
                    Polisi:
                    <strong class="ml-0.5 font-bold">110</strong>
                </span>
            </div>
        </div>

        <!-- Main navbar -->
        <nav class="border-b border-border bg-white">
            <div
                class="mx-auto flex max-w-screen-xl items-center justify-between px-4 py-3 md:px-8"
            >
                <!-- Logo -->
                <Link href="/landing" class="flex shrink-0 items-center gap-2">
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-lg"
                    >
                        <AppLogoIcon class="size-8" />
                    </div>
                    <div class="flex flex-col leading-none">
                        <span
                            class="text-sm font-bold tracking-tight text-foreground"
                            >SIKPK</span
                        >
                        <span
                            class="text-[10px] font-medium tracking-widest text-muted-foreground"
                            >POLBAN</span
                        >
                    </div>
                </Link>

                <!-- Nav items (desktop) -->
                <div class="hidden items-center gap-1 md:flex">
                    <Link
                        v-for="item in navItems"
                        :key="item.label"
                        :href="item.href"
                        :class="[
                            'rounded px-4 py-1.5 text-sm font-semibold transition-colors',
                            item.href ? 'cursor-pointer' : 'cursor-default',
                            item.label === activeNav
                                ? 'bg-[#E7EEF7] text-[#0F3A6C]'
                                : 'text-[#403B34] hover:bg-accent hover:text-foreground',
                        ]"
                    >
                        {{ item.label }}
                    </Link>
                </div>

                <!-- Right side actions (desktop) -->
                <div class="hidden shrink-0 items-center gap-3 md:flex">
                    <template v-if="!user">
                        <Button as-child variant="brand-accent" size="default">
                            <Link :href="loginGoogle()">Masuk</Link>
                        </Button>
                    </template>

                    <!-- Logged in -->
                    <template v-else>
                        <Button as-child variant="brand-accent" size="default">
                            <Link :href="create().url">Buat Laporan</Link>
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

                            <!-- Dropdown wrapper -->
                            <div
                                v-if="dropdownOpen"
                                class="absolute top-full right-0 z-50 mt-2 w-60"
                                @mouseenter="openDropdown"
                                @mouseleave="scheduleClose"
                            >
                                <div
                                    class="absolute -top-1 right-3 h-3 w-3 rotate-45 border-t border-l border-border bg-white"
                                />
                                <div
                                    class="overflow-hidden rounded-xl border border-border bg-white shadow-lg"
                                >
                                    <div class="px-4 py-3">
                                        <p
                                            class="text-sm font-semibold text-foreground"
                                        >
                                            {{ user.name }}
                                        </p>
                                        <p
                                            class="mt-0.5 text-xs text-muted-foreground"
                                        >
                                            {{ user.email }}
                                        </p>
                                    </div>
                                    <div
                                        class="flex items-center justify-between gap-2 border-t border-border px-3 py-2.5"
                                    >
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="text-xs text-muted-foreground"
                                        >
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
                        </div>
                    </template>
                </div>

                <!-- Mobile hamburger -->
                <button
                    class="flex h-9 w-9 items-center justify-center rounded-lg hover:bg-accent md:hidden"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    aria-label="Toggle menu"
                >
                    <X v-if="mobileMenuOpen" class="size-5" />
                    <Menu v-else class="size-5" />
                </button>
            </div>

            <!-- Mobile menu -->
            <div
                v-if="mobileMenuOpen"
                class="border-t border-border px-4 pb-4 md:hidden"
            >
                <div class="flex flex-col gap-1 py-3">
                    <Link
                        v-for="item in navItems"
                        :key="item.label"
                        :href="item.href"
                        :class="[
                            'rounded-lg px-4 py-2.5 text-sm font-semibold transition-colors',
                            item.label === activeNav
                                ? 'bg-[#E7EEF7] text-[#0F3A6C]'
                                : 'text-[#403B34] hover:bg-accent',
                        ]"
                        @click="mobileMenuOpen = false"
                    >
                        {{ item.label }}
                    </Link>
                </div>

                <div class="border-t border-border pt-3">
                    <template v-if="!user">
                        <Button as-child variant="brand-accent" size="default" class="w-full">
                            <Link :href="loginGoogle()">Buat Laporan</Link>
                        </Button>
                    </template>
                    <template v-else>
                        <div class="mb-3 flex items-center gap-3">
                            <div
                                :class="[
                                    'flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-semibold text-white select-none',
                                    avatarColor(user.name),
                                ]"
                            >
                                {{ getInitials(user.name) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-foreground">{{ user.name }}</p>
                                <p class="text-xs text-muted-foreground">{{ user.email }}</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <Button as-child variant="brand-accent" size="sm" class="flex-1">
                                <Link :href="create().url">Buat Laporan</Link>
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                @click="handleLogout"
                            >
                                Keluar
                            </Button>
                        </div>
                    </template>
                </div>
            </div>
        </nav>
    </header>
</template>
