<script setup lang="ts">
import { ref } from 'vue';
import DataTable from '@/components/table/DataTable.vue';
import type { User } from '@/types/auth';
import { getInitials, getAvatarColor } from '@/composables/useInitials';
import { toast } from 'vue-sonner';
import { jurusanList, prodiList } from '@/constants/jurusanProdi';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { handleDelete } from '@/lib/handleRequest';
import { destroy } from '@/routes/satgas/master/users';

const props = defineProps<{
    users: { data: User[] };
}>();

const rows = ref<User[]>([...props.users.data]);

const unsurFilter = ref('');
const unsurOptions = [
    { label: 'Semua', value: '' },
    { label: 'Dosen', value: 'dosen' },
    { label: 'Mahasiswa', value: 'mahasiswa' },
    { label: 'Tenaga Kependidikan', value: 'tendik' },
];

const unsurLabels: Record<string, string> = {
    dosen: 'Dosen',
    mahasiswa: 'Mahasiswa',
    tendik: 'Tenaga Kependidikan',
};

const columns = [
    { key: 'name', label: 'Nama', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'academic_role', label: 'Unsur', sortable: true },
    { key: 'role', label: 'Jabatan', sortable: true },
    { key: 'department', label: 'Jurusan/Unit', sortable: false },
];

const roleLabels: Record<string, string> = {
    ketua: 'Ketua',
    wakil_ketua: 'Wakil Ketua',
    sekretaris: 'Sekretaris',
    anggota: 'Anggota',
};

const formSchema = [
    {
        key: 'name',
        label: 'Nama Lengkap',
        type: 'text',
        span: 'full',
        required: true,
        placeholder: 'Contoh: Dr. Hendra Wijaya, M.T.',
    },
    {
        key: 'email',
        label: 'Email',
        type: 'email',
        span: 'full',
        required: true,
        placeholder: 'nama@polban.ac.id',
    },
    {
        key: 'academic_role',
        label: 'Unsur',
        type: 'select',
        span: 'half',
        required: true,
        placeholder: 'Pilih unsur...',
        options: [
            { label: 'Dosen', value: 'dosen' },
            { label: 'Mahasiswa', value: 'mahasiswa' },
            { label: 'Tenaga Kependidikan', value: 'tendik' },
        ],
    },
    {
        key: 'role',
        label: 'Jabatan Satgas',
        type: 'select',
        span: 'half',
        required: true,
        placeholder: 'Pilih jabatan...',
        disabled: (form: Record<string, any>) => form.role === 'ketua',
        options: (form: Record<string, any>) => [
            ...(form.role === 'ketua' ? [{ label: 'Ketua', value: 'ketua' }] : []),
            { label: 'Anggota', value: 'anggota' },
            { label: 'Sekretaris', value: 'sekretaris' },
            { label: 'Wakil Ketua', value: 'wakil_ketua' },
        ],
    },
    {
        key: 'department',
        label: (form: Record<string, any>) =>
            form.academic_role === 'tendik' ? 'Unit' : 'Jurusan',
        type: (form: Record<string, any>) =>
            form.academic_role === 'tendik' ? 'text' : 'select',
        span: 'half',
        required: true,
        resetOn: ['academic_role'],
        placeholder: (form: Record<string, any>) =>
            form.academic_role === 'tendik'
                ? 'Tuliskan unit...'
                : 'Pilih jurusan...',
        options: jurusanList.map((j) => ({ label: j.name, value: j.name })),
    },
    {
        key: 'study_program',
        label: 'Program Studi',
        type: 'select',
        span: 'half',
        required: false,
        placeholder: 'Pilih program studi...',
        filteredBy: 'department',
        hidden: (form: Record<string, any>) => form.academic_role !== 'mahasiswa',
        options: prodiList.map((p) => ({
            label: `${p.degreeLevel} ${p.name}`,
            value: `${p.degreeLevel} ${p.name}`,
            department: p.jurusanName,
        })),
    },
    {
        key: 'entry_year',
        label: 'Angkatan',
        type: 'number',
        span: 'half',
        required: false,
        placeholder: new Date().getFullYear().toString(),
        hidden: (form: Record<string, any>) => form.academic_role !== 'mahasiswa',
    },
];

function validateUser(data: Partial<User>) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (data.email && !emailRegex.test(data.email)) {
        toast.error('Format email tidak valid');
        return false;
    }

    const duplicate = rows.value.find(
        (u) => u.email === data.email && u.id !== data.id,
    );
    if (duplicate) {
        toast.error('Email sudah digunakan');
        return false;
    }

    return true;
}

const selectedRow = ref(null);
const isDeleteOpen = ref(false);

function openDeleteDialog(row: any) {
    selectedRow.value = row;
    isDeleteOpen.value = true;
}

function submitDelete() {
    handleDelete(destroy(selectedRow.value), {
        onSuccess: () => {
            isDeleteOpen.value = false;
        },
    });
}
</script>

<template>
    <div class="space-y-6 p-4 md:p-6">
        <DataTable
            title="Manajemen Anggota Satgas"
            description="Kelola data anggota Satuan Tugas PPK Polban. Tambah, perbarui, atau hapus anggota beserta jabatan dan unsurnya."
            :columns="columns"
            :rows="users"
            :form-schema="formSchema"
            resource-route="/satgas/master/users"
            :validator="validateUser"
            actions
            searchable
            search-placeholder="Cari nama, email, jurusan..."
            pagination
            :per-page="10"
            create-label="Tambah Anggota"
            filter-key="academic_role"
            :filter-value="unsurFilter"
            create-modal-title="Tambah Anggota Satgas"
            edit-modal-title="Edit Anggota Satgas"
            @delete="openDeleteDialog"
        >
            <!-- Filter unsur -->
            <template #filter>
                <div
                    class="inline-flex h-9 items-center gap-0.5 rounded-lg border border-border bg-surface p-1"
                >
                    <button
                        v-for="opt in unsurOptions"
                        :key="opt.value"
                        class="relative h-full rounded-md px-4 text-sm transition-all"
                        :class="
                            unsurFilter === opt.value
                                ? 'bg-background text-foreground shadow-sm'
                                : 'text-nav-muted hover:text-foreground'
                        "
                        @click="unsurFilter = opt.value"
                    >
                        <span class="invisible font-bold">{{ opt.label }}</span>
                        <span
                            class="absolute inset-0 flex items-center justify-center"
                            :class="
                                unsurFilter === opt.value
                                    ? 'font-bold'
                                    : 'font-normal'
                            "
                            >{{ opt.label }}</span
                        >
                    </button>
                </div>
            </template>

            <!-- Nama dengan avatar -->
            <template #name="{ row }">
                <div class="flex items-center gap-2.5">
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                        :class="getAvatarColor(row.name)"
                    >
                        {{ getInitials(row.name) }}
                    </div>
                    <span class="font-medium">{{ row.name }}</span>
                </div>
            </template>

            <!-- Unsur badge -->
            <template #academic_role="{ row }">
                <span
                    v-if="row.academic_role"
                    class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium"
                    :class="{
                        'bg-blue-500/10 text-blue-600': row.academic_role === 'dosen',
                        'bg-orange-500/10 text-orange-600': row.academic_role === 'mahasiswa',
                        'bg-emerald-500/10 text-emerald-600': row.academic_role === 'tendik',
                    }"
                >
                    {{ unsurLabels[row.academic_role] ?? row.academic_role }}
                </span>
                <span v-else class="text-muted-foreground">—</span>
            </template>

            <!-- Jabatan -->
            <template #role="{ row }">
                {{ roleLabels[row.role] ?? row.role }}
            </template>

            <!-- Jurusan -->
            <template #department="{ row }">
                <span v-if="row.department">{{ row.department }}</span>
                <span v-else class="text-muted-foreground">—</span>
            </template>

            <!-- Angkatan -->
            <template #entry_year="{ row }">
                <span v-if="row.entry_year">{{ row.entry_year }}</span>
                <span v-else class="text-muted-foreground">—</span>
            </template>
        </DataTable>
        <ConfirmDialog
            :open="isDeleteOpen"
            title="Hapus anggota?"
            description="akan dihapus dari daftar anggota satgas."
            :row-name="selectedRow?.name"
            action-label=""
            reject-label="Hapus"
            reject-variant="danger"
            @close="isDeleteOpen = false"
            @confirm="submitDelete"
        />
    </div>
</template>
