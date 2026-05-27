<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';

import KetuaDashboard from './KetuaDashboard.vue';
import AnggotaDashboard from './AnggotaDashboard.vue';

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

const greeting =
    user.role === 'ketua'
        ? `Selamat datang Ketua, ${user.name}!`
        : `Selamat datang, ${user.name}!`;
</script>

<template>
    <Head title="Dashboard" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <!-- Greeting -->
        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-bold text-gray-800">
                {{ greeting }}
            </h1>

            <p class="mt-2 text-sm text-gray-500">
                Semoga harimu menyenangkan 👋
            </p>
        </div>

        <!-- Dashboard by role -->
        <KetuaDashboard v-if="user.role === 'ketua'" />

        <AnggotaDashboard v-else />
    </div>
</template>
