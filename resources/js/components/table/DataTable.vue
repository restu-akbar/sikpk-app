<script setup lang="ts">
import { computed, ref } from 'vue';
import FormModal from './FormModal.vue';
import ConfirmDialog from '../ConfirmDialog.vue';
import { handleCreate, handleEdit, handleDelete } from '@/lib/utils';
import ErrorAlert from '@/components/ErrorAlert.vue';
import { router } from '@inertiajs/vue3';
interface Column {
    key: string;
    label: string;
    sortable?: boolean;
}

const props = defineProps<{
    title?: string;
    description?: string;

    columns: Column[];
    rows: {
        data: any[];
        current_page: number;
        last_page: number;
        next_page_url: string | null;
        prev_page_url: string | null;
        total: number;
    };

    searchable?: boolean;
    pagination?: boolean;
    perPage?: number;

    createLabel?: string;
    actions?: boolean;
    formSchema?: any[];

    resourceRoute?: string;
    validator?: (data: any) => boolean;
}>();

const isCreateOpen = ref(false);
const isEditOpen = ref(false);
const isDeleteOpen = ref(false);
const selectedRow = ref<any>(null);

const search = ref('');

const sortKey = ref<string | null>(null);
const sortDirection = ref<'asc' | 'desc'>('asc');

const filteredRows = computed(() => {
    let data = [...props.rows.data];

    // SEARCH
    if (search.value) {
        const keyword = search.value.toLowerCase();

        data = data.filter((row) =>
            props.columns.some((column) =>
                String(row[column.key] ?? '')
                    .toLowerCase()
                    .includes(keyword),
            ),
        );
    }

    // SORT
    if (sortKey.value) {
        data.sort((a, b) => {
            const aValue = a[sortKey.value!];
            const bValue = b[sortKey.value!];

            if (aValue < bValue) {
                return sortDirection.value === 'asc' ? -1 : 1;
            }

            if (aValue > bValue) {
                return sortDirection.value === 'asc' ? 1 : -1;
            }

            return 0;
        });
    }

    return data;
});

function toggleSort(column: Column) {
    if (!column.sortable) return;

    if (sortKey.value === column.key) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = column.key;
        sortDirection.value = 'asc';
    }
}
function submitCreate(data: any) {
    if (props.validator) {
        const isValid = props.validator(data);

        if (!isValid) return;
    }

    handleCreate(props.resourceRoute!, data);

    isCreateOpen.value = false;
}

function submitEdit(data: any) {
    if (props.validator) {
        const isValid = props.validator(data);

        if (!isValid) return;
    }

    handleEdit(props.resourceRoute!, data);

    isEditOpen.value = false;
}

function submitDelete() {
    handleDelete(props.resourceRoute!, selectedRow.value);

    isDeleteOpen.value = false;
}
</script>

<template>
    <div
        class="overflow-hidden rounded-2xl border border-border bg-background shadow-sm"
    >
        <!-- HEADER -->
        <div
            v-if="title || description"
            class="border-b border-border px-4 py-5 sm:px-6"
        >
            <div
                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <div class="space-y-1">
                    <h2
                        v-if="title"
                        class="text-lg font-semibold tracking-tight text-foreground"
                    >
                        {{ title }}
                    </h2>

                    <p v-if="description" class="text-sm text-muted-foreground">
                        {{ description }}
                    </p>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <!-- SEARCH -->
                    <input
                        v-if="searchable"
                        v-model="search"
                        type="text"
                        placeholder="Cari data..."
                        class="h-10 w-full rounded-lg border border-border bg-background px-3 text-sm transition outline-none focus:ring-2 focus:ring-primary sm:w-64"
                    />

                    <!-- CREATE -->
                    <button
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-primary px-4 text-sm font-medium text-primary-foreground transition hover:opacity-90"
                        @click="isCreateOpen = true"
                    >
                        + {{ createLabel || 'Tambah Data' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- DESKTOP TABLE -->
        <div class="hidden w-full overflow-x-auto md:block">
            <table class="w-full min-w-[700px] text-sm">
                <!-- HEAD -->
                <thead class="bg-muted/50">
                    <tr>
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            class="h-12 px-4 text-left font-medium text-muted-foreground"
                        >
                            <button
                                v-if="column.sortable"
                                class="inline-flex items-center gap-1 transition hover:text-foreground"
                                @click="toggleSort(column)"
                            >
                                {{ column.label }}

                                <span
                                    v-if="sortKey === column.key"
                                    class="text-xs"
                                >
                                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                </span>
                            </button>

                            <span v-else>
                                {{ column.label }}
                            </span>
                        </th>

                        <th
                            v-if="actions"
                            class="w-[180px] px-4 text-right font-medium text-muted-foreground"
                        >
                            Aksi
                        </th>
                    </tr>
                </thead>

                <!-- BODY -->
                <tbody>
                    <tr
                        v-for="(row, index) in filteredRows"
                        :key="row.id || index"
                        class="border-t border-border transition-colors hover:bg-muted/40"
                    >
                        <td
                            v-for="column in columns"
                            :key="column.key"
                            class="max-w-[240px] truncate p-4 align-middle text-foreground"
                        >
                            <slot :name="column.key" :row="row" :index="index">
                                {{ row[column.key] }}
                            </slot>
                        </td>

                        <!-- ACTION -->
                        <td class="p-4">
                            <div
                                class="flex items-center justify-end gap-2 whitespace-nowrap"
                            >
                                <button
                                    class="rounded-lg border border-border px-3 py-1.5 text-xs font-medium transition hover:bg-muted"
                                    @click="
                                        selectedRow = row;
                                        isEditOpen = true;
                                    "
                                >
                                    Edit
                                </button>

                                <button
                                    class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-100"
                                    @click="
                                        selectedRow = row;
                                        isDeleteOpen = true;
                                    "
                                >
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- EMPTY -->
                    <tr v-if="filteredRows.length === 0">
                        <td
                            :colspan="columns.length + 1"
                            class="p-12 text-center"
                        >
                            <div class="space-y-2">
                                <p class="font-medium text-foreground">
                                    Tidak ada data
                                </p>

                                <p class="text-sm text-muted-foreground">
                                    Data akan muncul di sini
                                </p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- MOBILE CARD -->
        <div class="space-y-4 p-4 md:hidden">
            <div
                v-for="(row, index) in filteredRows"
                :key="row.id || index"
                class="rounded-xl border border-border bg-background p-4 shadow-sm"
            >
                <div class="space-y-3">
                    <div v-for="column in columns" :key="column.key">
                        <p
                            class="mb-1 text-xs font-medium tracking-wide text-muted-foreground uppercase"
                        >
                            {{ column.label }}
                        </p>

                        <div class="text-sm text-foreground">
                            <slot :name="column.key" :row="row" :index="index">
                                {{ row[column.key] }}
                            </slot>
                        </div>
                    </div>
                </div>

                <!-- ACTION -->
                <div
                    class="mt-4 flex flex-wrap items-center gap-2 border-t border-border pt-4"
                >
                    <button
                        class="flex-1 rounded-lg border border-border px-3 py-2 text-sm font-medium transition hover:bg-muted"
                        @click="
                            selectedRow = row;
                            isEditOpen = true;
                        "
                    >
                        Edit
                    </button>

                    <button
                        class="flex-1 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-100"
                        @click="
                            selectedRow = row;
                            isDeleteOpen = true;
                        "
                    >
                        Hapus
                    </button>
                </div>
            </div>

            <!-- EMPTY -->
            <div
                v-if="filteredRows.length === 0"
                class="rounded-xl border border-border p-10 text-center"
            >
                <p class="font-medium text-foreground">Tidak ada data</p>

                <p class="mt-1 text-sm text-muted-foreground">
                    Data akan muncul di sini
                </p>
            </div>
        </div>

        <!-- FOOTER -->
        <div
            v-if="pagination"
            class="flex flex-col gap-3 border-t border-border px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6"
        >
            <p class="text-sm text-muted-foreground">
                Total {{ rows.total }} data
            </p>

            <div class="flex items-center gap-2">
                <button
                    class="rounded-lg border border-border px-3 py-2 text-sm transition disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="!rows.prev_page_url"
                    @click="
                        rows.prev_page_url && router.visit(rows.prev_page_url)
                    "
                >
                    Sebelumnya
                </button>

                <div class="text-sm text-muted-foreground">
                    {{ rows.current_page }} / {{ rows.last_page }}
                </div>

                <button
                    class="rounded-lg border border-border px-3 py-2 text-sm transition disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="!rows.next_page_url"
                    @click="
                        rows.next_page_url && router.visit(rows.next_page_url)
                    "
                >
                    Selanjutnya
                </button>
            </div>
        </div>
        <FormModal
            :open="isCreateOpen"
            title="Tambah Data"
            :schema="formSchema || []"
            @close="isCreateOpen = false"
            @submit="submitCreate"
        />

        <FormModal
            :open="isEditOpen"
            title="Edit Data"
            :schema="formSchema || []"
            :data="selectedRow"
            @close="isEditOpen = false"
            @submit="submitEdit"
        />

        <ConfirmDialog
            :open="isDeleteOpen"
            title="Hapus Data"
            description="Yakin ingin menghapus data ini?"
            @close="isDeleteOpen = false"
            @confirm="submitDelete"
        />
    </div>
</template>
