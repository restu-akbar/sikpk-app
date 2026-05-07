<script setup lang="ts">
import { ref } from 'vue';
import DataTable from '@/components/table/DataTable.vue';
import User from '@/type/auth';
import { toast } from 'vue-sonner';

const props = defineProps<{
    users: {
        data: User[];
    };
}>();

const rows = ref<User[]>([...props.users.data]);

const columns = [
    {
        key: 'no',
        label: 'No',
        sortable: true,
    },
    {
        key: 'name',
        label: 'Nama',
        sortable: true,
    },
    {
        key: 'email',
        label: 'Email',
        sortable: true,
    },
    {
        key: 'must_change_password',
        label: 'Status Aktivasi',
        sortable: true,
    },
];
const formSchema = [
    {
        key: 'name',
        label: 'Nama',
        type: 'text',
    },
    {
        key: 'email',
        label: 'Email',
        type: 'email',
    },
];

function validateUser(data: Partial<User>) {
    const validationErrors: string[] = [];

    if (!data.name || data.name.trim() === '') {
        validationErrors.push('Nama wajib diisi');
    }

    if (!data.email || data.email.trim() === '') {
        validationErrors.push('Email wajib diisi');
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (data.email && !emailRegex.test(data.email)) {
        validationErrors.push('Format email tidak valid');
    }

    const duplicateEmail = rows.value.find(
        (user) => user.email === data.email && user.id !== data.id,
    );

    if (duplicateEmail) {
        validationErrors.push('Email sudah digunakan');
    }

    if (validationErrors.length) {
        validationErrors.forEach((error) => {
            toast.error(error);
        });
    }
    return validationErrors.length === 0;
}
</script>

<template>
    <div class="space-y-6 p-4 md:p-6">
        <DataTable
            title="Master User"
            description="Kelola seluruh data anggota."
            :columns="columns"
            :rows="users"
            :form-schema="formSchema"
            resource-route="/master/users"
            :validator="validateUser"
            actions
            searchable
            pagination
            :per-page="10"
            create-label="Tambah User"
        >
            <template #no="{ index }">
                {{ index + 1 }}
            </template>

            <template #must_change_password="{ row }">
                <span
                    class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium"
                    :class="
                        row.must_change_password
                            ? 'bg-yellow-500/10 text-yellow-600 dark:text-yellow-400'
                            : 'bg-green-500/10 text-green-600 dark:text-green-400'
                    "
                >
                    {{
                        row.must_change_password ? 'Belum Aktif' : 'Sudah Aktif'
                    }}
                </span>
            </template>
        </DataTable>
    </div>
</template>
