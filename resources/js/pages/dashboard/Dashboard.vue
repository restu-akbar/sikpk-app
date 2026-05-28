<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';

import SatgasDashboard from './SatgasDashboard.vue';
import ReporterDashboard from './ReporterDashboard.vue';

import { dashboard } from '@/routes/satgas';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const page = usePage();

const user = page.props.auth.user;
</script>

<template>
    <Head title="Dashboard" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <div class="rounded-xl border p-6 shadow-sm">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                Selamat datang, {{ user.name }}
            </h1>

            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{
                    user.role
                        ? 'Semoga harimu menyenangkan 👋'
                        : 'Anda tidak sendirian, laporan anda kami dengar'
                }}
            </p>
        </div>

        <SatgasDashboard v-if="user.role" />
        <ReporterDashboard v-else />
    </div>
</template>
