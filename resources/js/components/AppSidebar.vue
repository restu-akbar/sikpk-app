<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Users } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';

import { dashboard } from '@/routes/satgas';
import { dashboard as reporterDashboard} from '@/routes';
import type { NavItem } from '@/types';

const page = usePage();
const user = page.props.auth.user;

const { state } = useSidebar();

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: user.role !== null ? dashboard() : reporterDashboard(),
        icon: LayoutGrid,
    },

    ...(user.role === null
        ? [
              {
                  title: 'Laporan',
                  href: '/reports',
                  icon: Users,
              },
          ]
        : []),
];

const masterNavItems: NavItem[] =
    user.role === 'ketua'
        ? [
              {
                  title: 'Anggota',
                  href: '/satgas/master/users',
                  icon: Users,
              },
          ]
        : [];

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="group">
        <!-- HEADER -->
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <div
                v-if="mainNavItems.length"
                v-show="state !== 'collapsed'"
                class="px-2 text-xs font-semibold text-muted-foreground"
            >
                MODUL
            </div>

            <NavMain :items="mainNavItems" />

            <div v-if="masterNavItems.length" class="mx-2 border-t" />

            <!-- MASTER -->
            <div
                v-if="masterNavItems.length"
                v-show="state !== 'collapsed'"
                class="px-2 text-xs font-semibold text-muted-foreground"
            >
                MASTER
            </div>

            <NavMain v-if="masterNavItems.length" :items="masterNavItems" />
        </SidebarContent>

        <!-- FOOTER -->
        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>
