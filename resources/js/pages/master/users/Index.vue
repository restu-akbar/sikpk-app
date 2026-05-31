<script setup lang="ts">
import { ref } from 'vue';
import DataTable from '@/components/table/DataTable.vue';
import type { User, Department, StudyProgram } from '@/types/auth';
import { getInitials, getAvatarColor } from '@/composables/useInitials';
import { toast } from 'vue-sonner';

const props = defineProps<{
    users: { data: User[] };
    departments: Department[];
    study_programs: StudyProgram[];
}>();

const rows = ref<User[]>([...props.users.data]);

const unsurFilter = ref('');
const unsurOptions = [
    { label: 'Semua', value: '' },
    { label: 'Dosen', value: 'dosen' },
    { label: 'Mahasiswa', value: 'mahasiswa' },
];

const columns = [
    { key: 'name',          label: 'Nama',    sortable: true  },
    { key: 'email',         label: 'Email',   sortable: true  },
    { key: 'academic_role', label: 'Unsur',   sortable: true  },
    { key: 'role',          label: 'Jabatan', sortable: true  },
    { key: 'department',    label: 'Jurusan', sortable: false },
];

const roleLabels: Record<string, string> = {
    ketua:       'Ketua',
    wakil_ketua: 'Wakil Ketua',
    sekretaris:  'Sekretaris',
    anggota:     'Anggota',
};

const formSchema = [
    {
        key: 'name', label: 'Nama Lengkap', type: 'text', span: 'full',
        required: true, placeholder: 'Contoh: Dr. Hendra Wijaya, M.T.',
    },
    {
        key: 'email', label: 'Email', type: 'email', span: 'full',
        required: true, placeholder: 'nama@polban.ac.id',
    },
    {
        key: 'academic_role', label: 'Unsur', type: 'select', span: 'half',
        required: true,
        options: [
            { label: 'Pilih unsur...', value: ''          },
            { label: 'Dosen',          value: 'dosen'     },
            { label: 'Mahasiswa',      value: 'mahasiswa' },
        ],
    },
    {
        key: 'role', label: 'Jabatan Satgas', type: 'select', span: 'half',
        required: true,
        options: [
            { label: 'Pilih jabatan...', value: ''           },
            { label: 'Anggota',          value: 'anggota'    },
            { label: 'Sekretaris',       value: 'sekretaris' },
            { label: 'Wakil Ketua',      value: 'wakil_ketua'},
        ],
    },
    {
        key: 'department_id', label: 'Jurusan', type: 'select', span: 'half',
        required: true,
        options: [
            { label: 'Pilih jurusan...', value: '' },
            ...props.departments.map(d => ({ label: d.name, value: d.id })),
        ],
    },
    {
        key: 'study_program_id', label: 'Program Studi', type: 'select', span: 'half',
        required: false,
        filteredBy: 'department_id',
        options: [
            { label: 'Pilih program studi...', value: '', department_id: null },
            ...props.study_programs.map(sp => ({
                label: `${sp.degree_level} ${sp.name}`,
                value: sp.id,
                department_id: sp.department_id,
            })),
        ],
    },
    {
        key: 'entry_year', label: 'Angkatan', type: 'number', span: 'half',
        required: false, placeholder: new Date().getFullYear().toString(),
    },
];

function validateUser(data: Partial<User>) {
    const errors: string[] = [];

    if (!data.name?.trim())  errors.push('Nama wajib diisi');
    if (!data.email?.trim()) errors.push('Email wajib diisi');

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (data.email && !emailRegex.test(data.email))
        errors.push('Format email tidak valid');

    const duplicate = rows.value.find(
        u => u.email === data.email && u.id !== data.id,
    );
    if (duplicate) errors.push('Email sudah digunakan');

    errors.forEach(e => toast.error(e));
    return errors.length === 0;
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
        >
            <!-- Filter unsur -->
            <template #filter>
                <div class="inline-flex h-9 items-center gap-0.5 rounded-lg border border-border bg-[#FBF9F5] p-1">
                    <button
                        v-for="opt in unsurOptions"
                        :key="opt.value"
                        class="relative h-full rounded-md px-4 text-sm transition-all"
                        :class="
                            unsurFilter === opt.value
                                ? 'bg-white text-foreground shadow-sm'
                                : 'text-[#6B6862] hover:text-foreground'
                        "
                        @click="unsurFilter = opt.value"
                    >
                        <span class="invisible font-bold">{{ opt.label }}</span>
                        <span
                            class="absolute inset-0 flex items-center justify-center"
                            :class="unsurFilter === opt.value ? 'font-bold' : 'font-normal'"
                        >{{ opt.label }}</span>
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
                    :class="
                        row.academic_role === 'dosen'
                            ? 'bg-blue-500/10 text-blue-600'
                            : 'bg-orange-500/10 text-orange-600'
                    "
                >
                    {{ row.academic_role === 'dosen' ? 'Dosen' : 'Mahasiswa' }}
                </span>
                <span v-else class="text-muted-foreground">—</span>
            </template>

            <!-- Jabatan -->
            <template #role="{ row }">
                {{ roleLabels[row.role] ?? row.role }}
            </template>

            <!-- Jurusan -->
            <template #department="{ row }">
                <span v-if="row.department">{{ row.department.name }}</span>
                <span v-else class="text-muted-foreground">—</span>
            </template>

            <!-- Prodi -->
            <template #study_program="{ row }">
                <span v-if="row.study_program">
                    {{ row.study_program.degree_level }} {{ row.study_program.name }}
                </span>
                <span v-else class="text-muted-foreground">—</span>
            </template>

            <!-- Angkatan -->
            <template #entry_year="{ row }">
                <span v-if="row.entry_year">{{ row.entry_year }}</span>
                <span v-else class="text-muted-foreground">—</span>
            </template>
        </DataTable>
    </div>
</template>
