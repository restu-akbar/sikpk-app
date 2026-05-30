<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Mail, Phone, Shield } from 'lucide-vue-next';
import type { Auth } from '@/types';
import { getInitials } from '@/composables/useInitials';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { home } from '@/routes';

interface Props {
    /* Which nav item is shown as active. Pass the exact label string. */
    currentPage?: string;
}

withDefaults(defineProps<Props>(), {
    currentPage: 'Beranda',
});

const page = usePage<{ auth: Auth }>();
const user = computed(() => page.props.auth?.user ?? null);

const navItems = ['Beranda', 'Lacak Kasus', 'Informasi', 'Lokasi Satgas'] as const;

/* Generate a deterministic background color for the user avatar
   based on the first character of the user's name. */
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
                <span class="flex items-center gap-1.5 text-xs text-white/85">
                    <Mail class="size-3.5 shrink-0" />
                    Butuh Bantuan Segera?
                </span>

                <span class="h-3 w-px bg-white/25" aria-hidden="true" />

                <span class="flex items-center gap-1.5 text-xs text-white/85">
                    <Phone class="size-3.5 shrink-0" />
                    Satpam Polban:
                    <strong class="ml-0.5 font-semibold">(022) 2013789</strong>
                </span>

                <span class="h-3 w-px bg-white/25" aria-hidden="true" />

                <span class="flex items-center gap-1.5 text-xs text-white/85">
                    <Shield class="size-3.5 shrink-0" />
                    Polisi:
                    <strong class="ml-0.5 font-semibold">110</strong>
                </span>
            </div>
        </div>

        <!-- Main navbar -->
        <nav class="border-b border-border bg-white">
            <div class="mx-auto flex max-w-screen-xl items-center justify-between px-8 py-3">

                <!-- Logo: links to home (only required functionality) -->
                <Link :href="home()" class="flex items-center gap-2 shrink-0">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-lg">
                        <AppLogoIcon class="size-8" />
                    </div>
                    <div class="flex flex-col leading-none">
                        <span class="text-sm font-bold tracking-tight text-foreground">
                            SIKPK
                        </span>
                        <span class="text-[10px] font-medium tracking-widest text-muted-foreground">
                            POLBAN
                        </span>
                    </div>
                </Link>

                <!-- Nav items -->
                <div class="flex items-center gap-1">
                    <span
                        v-for="item in navItems"
                        :key="item"
                        :class="[
                            'cursor-default rounded-full px-4 py-1.5 text-sm font-medium transition-colors',
                            item === currentPage
                                ? 'bg-secondary text-foreground'
                                : 'text-muted-foreground hover:bg-accent hover:text-foreground',
                        ]"
                    >
                        {{ item }}
                    </span>
                </div>

                <!-- Right side actions -->
                <div class="flex shrink-0 items-center gap-3">

                    <!-- Not logged in -->
                    <template v-if="!user">
                        <Button variant="outline" size="default">
                            Masuk
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

                        <!-- User avatar with initials -->
                        <div
                            :class="[
                                'flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-full text-sm font-semibold text-white',
                                avatarColor(user.name),
                            ]"
                            :title="user.name"
                        >
                            {{ getInitials(user.name) }}
                        </div>
                    </template>

                </div>
            </div>
        </nav>

    </header>
</template>
