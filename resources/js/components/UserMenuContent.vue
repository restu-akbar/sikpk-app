<script setup lang="ts">
import axios from 'axios';
import { LogOut } from 'lucide-vue-next';
import {
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuItem,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import { logout } from '@/routes';
import type { User } from '@/types';

const props = defineProps<{ user: User }>();

const handleLogout = () => {
    const url = props.user.role ? logout().url : '/logout';
    axios.post(url).finally(() => {
        window.location.href = '/';
    });
};
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>

    <DropdownMenuSeparator />

    <DropdownMenuItem :as-child="true">
        <button
            @click="handleLogout"
            class="flex w-full cursor-pointer items-center"
            data-test="logout-button"
        >
            <LogOut class="mr-2 h-4 w-4" />
            Keluar
        </button>
    </DropdownMenuItem>
</template>
