<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Archive,
    BarChart3,
    Briefcase,
    FileText,
    LayoutGrid,
    LogOut,
    Users,
} from 'lucide-vue-next';
import axios from 'axios';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { logout } from '@/routes';
import { dashboard } from '@/routes/satgas';
import { dashboard as reporterDashboard} from '@/routes';
import type { NavItem } from '@/types';
import { getInitials, getAvatarColor } from '@/composables/useInitials';

const page = usePage();
const user = page.props.auth.user;
const { isCurrentUrl } = useCurrentUrl();

interface NavItem {
    title: string;
    href: string;
    icon: any;
}

const dashboardUrl = dashboard.url();

const ketuaNavItems: NavItem[] = [
    { title: 'Dashboard', href: dashboardUrl, icon: LayoutGrid },
    { title: 'Manajemen Kasus', href: '/satgas/cases', icon: FileText },
    { title: 'Penanganan Kasus', href: '/satgas/handling', icon: Briefcase },
    { title: 'Arsip Kasus', href: '/satgas/archive', icon: Archive },
    { title: 'Manajemen Anggota', href: '/satgas/master/users', icon: Users },
    { title: 'Grafik Demografi', href: '/satgas/demographics', icon: BarChart3 },
];

const anggotaNavItems: NavItem[] = [
    { title: 'Dashboard', href: dashboardUrl, icon: LayoutGrid },
];

const navItems = user.role === 'ketua' ? ketuaNavItems : anggotaNavItems;

const panelLabel = user.role === 'ketua' ? 'PANEL KETUA SATGAS' : 'PANEL ANGGOTA SATGAS';

const handleLogout = () => {
    const url = user.role ? logout().url : '/logout';
    axios.post(url).finally(() => {
        window.location.href = '/';
    });
};
</script>

<template>
    <Sidebar
        collapsible="icon"
        variant="sidebar"
        class="border-r-0 bg-nav-bg"
    >
        <!-- ── HEADER ── sejajar dengan AppSidebarHeader (h-16, border-b) -->
        <SidebarHeader
            class="flex h-16 items-center border-b border-sidebar-border/70 px-4"
        >
            <Link
                :href="dashboardUrl"
                class="flex w-full items-center gap-3 group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:pt-3"
            >
                <span class="flex h-7 w-7 shrink-0 items-center justify-center overflow-hidden rounded-sm">
                    <AppLogoIcon class="h-full w-full object-contain" />
                </span>
                <div class="flex flex-col leading-tight group-data-[collapsible=icon]:hidden">
                    <span class="text-base font-bold text-nav-name tracking-wide">
                        SIKPK
                    </span>
                    <span class="text-[11px] font-medium text-nav-muted uppercase tracking-wider">
                        SATGAS PPK POLBAN
                    </span>
                </div>
            </Link>
        </SidebarHeader>

        <!-- ── CONTENT ── -->
        <SidebarContent class="px-4 pt-4 group-data-[collapsible=icon]:px-2">
            <!-- Section label -->
            <p class="mb-3 px-1 text-[11px] font-semibold uppercase tracking-widest text-nav-muted group-data-[collapsible=icon]:hidden">
                {{ panelLabel }}
            </p>

            <!-- Nav items -->
            <nav class="flex flex-col gap-1">
                <template v-for="item in navItems" :key="item.href">
                    <!-- ACTIVE item -->
                    <Link
                        v-if="isCurrentUrl(item.href)"
                        :href="item.href"
                        class="relative flex items-center gap-3 overflow-hidden rounded-lg bg-nav-active-bg px-3 py-2.5 text-sm font-semibold text-nav-active-text transition-colors group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:px-2 group-data-[collapsible=icon]:py-2.5"
                        :title="item.title"
                    >
                        <!-- orange indicator bar -->
                        <span class="absolute left-0 top-0 h-full w-[3px] rounded-r bg-nav-indicator group-data-[collapsible=icon]:hidden" />
                        <component :is="item.icon" class="h-4 w-4 shrink-0" />
                        <span class="group-data-[collapsible=icon]:hidden">{{ item.title }}</span>
                    </Link>

                    <!-- INACTIVE item -->
                    <Link
                        v-else
                        :href="item.href"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-nav-text transition-colors hover:bg-black/5 group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:px-2 group-data-[collapsible=icon]:py-2.5"
                        :title="item.title"
                    >
                        <component :is="item.icon" class="h-4 w-4 shrink-0" />
                        <span class="group-data-[collapsible=icon]:hidden">{{ item.title }}</span>
                    </Link>
                </template>
            </nav>
        </SidebarContent>

        <!-- ── FOOTER ── -->
        <SidebarFooter class="px-4 pb-4 group-data-[collapsible=icon]:px-2 group-data-[collapsible=icon]:pb-3">
            <!-- Expanded: full card with name/role/logout -->
            <div
                class="mt-2 flex items-center gap-3 rounded-xl bg-nav-footer-bg px-3 py-3
                       group-data-[collapsible=icon]:mt-0 group-data-[collapsible=icon]:rounded-none
                       group-data-[collapsible=icon]:bg-transparent group-data-[collapsible=icon]:p-0
                       group-data-[collapsible=icon]:justify-center"
            >
                <!-- Avatar -->
                <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold group-data-[collapsible=icon]:h-7 group-data-[collapsible=icon]:w-7"
                    :class="getAvatarColor(user.name)"
                >
                    {{ getInitials(user.name) }}
                </div>

                <!-- Name + role (hidden when collapsed) -->
                <div class="flex min-w-0 flex-1 flex-col leading-tight group-data-[collapsible=icon]:hidden">
                    <span class="truncate text-sm font-semibold text-nav-name">
                        {{ user.name }}
                    </span>
                    <span class="truncate text-[11px] text-nav-muted capitalize">
                        {{ user.role === 'ketua' ? 'Ketua Satgas' : 'Anggota Satgas' }}
                    </span>
                </div>

                <!-- Logout (hidden when collapsed) -->
                <button
                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-nav-muted transition-colors hover:bg-black/10 hover:text-nav-text group-data-[collapsible=icon]:hidden"
                    title="Keluar"
                    @click="handleLogout"
                >
                    <LogOut class="h-4 w-4" />
                </button>
            </div>
        </SidebarFooter>
    </Sidebar>
</template>
