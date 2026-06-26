<script setup lang="ts">
import { Link, usePage, useForm } from '@inertiajs/vue3';
import {
    Archive,
    BarChart3,
    Briefcase,
    FileText,
    ChevronsUpDown,
    KeyRound,
    LayoutGrid,
    LogOut,
    Users,
} from 'lucide-vue-next';
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import ChangePasswordForm from '@/components/ChangePasswordForm.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { logout } from '@/routes';
import { dashboard } from '@/routes/satgas';
import { index } from '@/routes/satgas/reports/handling/';
import type { NavItem } from '@/types';
import { getInitials, getAvatarColor } from '@/composables/useInitials';
import { generateEncryption } from '@/lib/crypto';
import { setTemporaryError } from '@/lib/utils';
import { update } from '@/routes/satgas/settings/security';

const page = usePage();
const user = page.props.auth.user;
const { isCurrentUrl } = useCurrentUrl();

interface NavItem {
    title: string;
    href: string;
    icon: any;
}

const dashboardUrl = dashboard.url();

const penangananKasusNavItem: NavItem = {
    href: index(),
    title: 'Penanganan Kasus',
    icon: Briefcase,
};

const ketuaNavItems: NavItem[] = [
    { title: 'Dashboard', href: dashboardUrl, icon: LayoutGrid },
    { title: 'Manajemen Kasus', href: '/satgas/reports', icon: FileText },
    penangananKasusNavItem,
    { title: 'Arsip Kasus', href: '/satgas/archive', icon: Archive },
    { title: 'Manajemen Anggota', href: '/satgas/master/users', icon: Users },
];

const anggotaNavItems: NavItem[] = [
    { title: 'Dashboard', href: dashboardUrl, icon: LayoutGrid },
    penangananKasusNavItem,
];

const navItems = user.role === 'ketua' ? ketuaNavItems : anggotaNavItems;

const panelLabel =
    user.role === 'ketua' ? 'PANEL KETUA SATGAS' : 'PANEL ANGGOTA SATGAS';

const handleLogout = () => {
    const url = user.role ? logout().url : '/logout';
    axios.post(url).finally(() => {
        window.location.href = '/';
    });
};

// Dropdown menu
const showMenu = ref(false);

const closeMenu = () => {
    showMenu.value = false;
};
onMounted(() => document.addEventListener('click', closeMenu));
onUnmounted(() => document.removeEventListener('click', closeMenu));

// Modal ganti kata sandi
const showPasswordModal = ref(false);

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
    emek_password: '',
    emek_password_salt: '',
});

const submitChangePassword = async () => {
    try {
        passwordForm.clearErrors();

        if (!passwordForm.current_password) {
            setTemporaryError(
                passwordForm,
                'current_password',
                'Kata sandi lama wajib diisi.',
            );
            return;
        }
        if (!passwordForm.password) {
            setTemporaryError(
                passwordForm,
                'password',
                'Kata sandi baru wajib diisi.',
            );
            return;
        }
        if (!passwordForm.password_confirmation) {
            setTemporaryError(
                passwordForm,
                'password_confirmation',
                'Konfirmasi kata sandi wajib diisi.',
            );
            return;
        }
        if (passwordForm.current_password === passwordForm.password) {
            passwordForm.setError(
                'password',
                'Kata sandi baru tidak boleh sama dengan kata sandi lama.',
            );
            return;
        }
        if (passwordForm.password !== passwordForm.password_confirmation) {
            passwordForm.setError(
                'password_confirmation',
                'Konfirmasi kata sandi harus sama dengan kata sandi baru.',
            );
            return;
        }

        const encryption = await generateEncryption({
            mode: 'change',
            oldPassword: passwordForm.current_password,
            newPassword: passwordForm.password,
            emek_password: user.emek_password,
            emek_password_salt: user.emek_password_salt,
        });
        console.log(encryption);

        passwordForm.emek_password = encryption.emek_password;
        passwordForm.emek_password_salt = encryption.emek_password_salt;

        passwordForm.submit(update(), {
            preserveScroll: true,
            onSuccess: () => {
                passwordForm.reset();
                showPasswordModal.value = false;
            },
            onError: () => {
                passwordForm.reset(
                    'current_password',
                    'password',
                    'password_confirmation',
                );
            },
        });
    } catch (error) {
        console.error(error);
    }
};
</script>

<template>
    <Sidebar collapsible="icon" variant="sidebar" class="border-r-0 bg-nav-bg">
        <!-- ── HEADER ── sejajar dengan AppSidebarHeader (h-16, border-b) -->
        <SidebarHeader
            class="flex h-16 items-center border-b border-sidebar-border/70 px-4"
        >
            <Link
                :href="dashboardUrl"
                class="flex w-full items-center gap-3 group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:pt-3"
            >
                <span
                    class="flex h-7 w-7 shrink-0 items-center justify-center overflow-hidden rounded-sm"
                >
                    <AppLogoIcon class="h-full w-full object-contain" />
                </span>
                <div
                    class="flex flex-col leading-tight group-data-[collapsible=icon]:hidden"
                >
                    <span
                        class="text-base font-bold tracking-wide text-nav-name"
                    >
                        SIKPK
                    </span>
                    <span
                        class="text-[11px] font-medium tracking-wider text-nav-muted uppercase"
                    >
                        SATGAS PPK POLBAN
                    </span>
                </div>
            </Link>
        </SidebarHeader>

        <!-- ── CONTENT ── -->
        <SidebarContent class="px-4 pt-4 group-data-[collapsible=icon]:px-2">
            <!-- Section label -->
            <p
                class="mb-3 px-1 text-[11px] font-semibold tracking-widest text-nav-muted uppercase group-data-[collapsible=icon]:hidden"
            >
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
                        <span
                            class="absolute top-0 left-0 h-full w-[3px] rounded-r bg-nav-indicator group-data-[collapsible=icon]:hidden"
                        />
                        <component :is="item.icon" class="h-4 w-4 shrink-0" />
                        <span class="group-data-[collapsible=icon]:hidden">{{
                            item.title
                        }}</span>
                    </Link>

                    <!-- INACTIVE item -->
                    <Link
                        v-else
                        :href="item.href"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-nav-text transition-colors group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:px-2 group-data-[collapsible=icon]:py-2.5 hover:bg-black/5"
                        :title="item.title"
                    >
                        <component :is="item.icon" class="h-4 w-4 shrink-0" />
                        <span class="group-data-[collapsible=icon]:hidden">{{
                            item.title
                        }}</span>
                    </Link>
                </template>
            </nav>
        </SidebarContent>

        <!-- ── FOOTER ── -->
        <SidebarFooter
            class="px-4 pb-4 group-data-[collapsible=icon]:px-2 group-data-[collapsible=icon]:pb-3"
        >
            <!-- Expanded: full card with name/role/logout -->
            <div
                class="mt-2 flex items-center gap-3 rounded-xl bg-nav-footer-bg px-3 py-3 group-data-[collapsible=icon]:mt-0 group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:rounded-none group-data-[collapsible=icon]:bg-transparent group-data-[collapsible=icon]:p-0"
            >
                <!-- Avatar -->
                <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold group-data-[collapsible=icon]:h-7 group-data-[collapsible=icon]:w-7"
                    :class="getAvatarColor(user.name)"
                >
                    {{ getInitials(user.name) }}
                </div>

                <!-- Name + role (hidden when collapsed) -->
                <div
                    class="flex min-w-0 flex-1 flex-col leading-tight group-data-[collapsible=icon]:hidden"
                >
                    <span class="truncate text-sm font-semibold text-nav-name">
                        {{ user.name }}
                    </span>
                    <span
                        class="truncate text-[11px] text-nav-muted capitalize"
                    >
                        {{
                            user.role === 'ketua'
                                ? 'Ketua Satgas'
                                : 'Anggota Satgas'
                        }}
                    </span>
                </div>

                <!-- Menu trigger (hidden when collapsed) -->
                <div
                    class="relative shrink-0 group-data-[collapsible=icon]:hidden"
                >
                    <button
                        class="flex h-7 w-7 items-center justify-center rounded-md text-nav-muted transition-colors hover:bg-black/10 hover:text-nav-text"
                        @click.stop="showMenu = !showMenu"
                    >
                        <ChevronsUpDown class="h-4 w-4" />
                    </button>

                    <!-- Dropdown -->
                    <div
                        v-if="showMenu"
                        class="absolute right-0 bottom-full mb-2 w-48 rounded-xl border border-border bg-background shadow-lg"
                        @click.stop
                    >
                        <button
                            class="flex w-full items-center gap-2.5 rounded-t-xl px-4 py-2.5 text-sm text-foreground transition hover:bg-muted"
                            @click="
                                showMenu = false;
                                showPasswordModal = true;
                            "
                        >
                            <KeyRound class="h-4 w-4 text-muted-foreground" />
                            Ubah Kata Sandi
                        </button>
                        <div class="border-t border-border" />
                        <button
                            class="flex w-full items-center gap-2.5 rounded-b-xl px-4 py-2.5 text-sm text-red-500 transition hover:bg-red-50 dark:hover:bg-red-950/30"
                            @click="
                                showMenu = false;
                                handleLogout();
                            "
                        >
                            <LogOut class="h-4 w-4" />
                            Keluar
                        </button>
                    </div>
                </div>
            </div>
        </SidebarFooter>
    </Sidebar>

    <!-- Modal Ganti Kata Sandi -->
    <div
        v-if="showPasswordModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
        @click.self="showPasswordModal = false"
    >
        <div
            class="w-full max-w-md rounded-2xl border border-border bg-background shadow-2xl"
        >
            <!-- Header -->
            <div
                class="flex items-center justify-between border-b border-border px-6 py-4"
            >
                <h2 class="text-lg font-semibold text-foreground">
                    Ganti Kata Sandi
                </h2>
                <button
                    class="rounded-lg p-2 text-muted-foreground transition hover:bg-muted hover:text-foreground"
                    @click="showPasswordModal = false"
                >
                    ✕
                </button>
            </div>

            <!-- Body -->
            <div class="p-6">
                <ChangePasswordForm
                    :form="passwordForm"
                    :on-submit="submitChangePassword"
                    submit-label="Simpan Kata Sandi"
                    processing-label="Menyimpan..."
                />
            </div>
        </div>
    </div>
</template>
