<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { Monitor, Moon, Sun } from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const { appearance, updateAppearance } = useAppearance();

const options = [
    { value: 'light', icon: Sun },
    { value: 'system', icon: Monitor },
    { value: 'dark', icon: Moon },
] as const;
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 bg-[#FDFCFB] px-4 transition-[width,height] ease-linear"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <div class="ml-auto flex items-center">
            <div
                class="relative flex items-center rounded-full bg-neutral-100 p-0.5 dark:bg-neutral-800"
            >
                <div
                    class="absolute top-0.5 left-0.5 h-7 w-7 rounded-full bg-white shadow-md transition-transform duration-300 ease-out dark:bg-neutral-600"
                    :style="{
                        transform: `translateX(${options.findIndex((o) => o.value === appearance) * 100}%)`,
                    }"
                />

                <button
                    v-for="{ value, icon } in options"
                    :key="value"
                    @click="updateAppearance(value)"
                    class="relative z-10 flex h-7 w-7 items-center justify-center rounded-full transition-colors duration-200"
                    :class="
                        appearance === value
                            ? 'text-neutral-900 dark:text-neutral-100'
                            : 'text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300'
                    "
                >
                    <component :is="icon" class="h-3.5 w-3.5" />
                </button>
            </div>
        </div>
    </header>
</template>
