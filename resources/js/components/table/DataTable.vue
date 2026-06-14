<script setup lang="ts">
import { computed, ref } from 'vue';
import { Pencil, Search, Trash2 } from 'lucide-vue-next';
import FormModal from './FormModal.vue';
import { handleCreate, handleEdit, handleDelete } from '@/lib/utils';
import { router } from '@inertiajs/vue3';

interface Column {
    key: string;
    label: string;
    sortable?: boolean;
}

const emit = defineEmits<{
    rowClick: [row: any];
    edit: [row: any];
    delete: [row: any];
}>();

const props = defineProps<{
    title?: string;
    description?: string;

    columns: Column[];
    rows:
        | any[]
        | {
              data: any[];
              current_page: number;
              last_page: number;
              next_page_url: string | null;
              prev_page_url: string | null;
              total: number;
          };

    searchable?: boolean;
    searchPlaceholder?: string;
    pagination?: boolean;
    perPage?: number;

    createLabel?: string;
    createModalTitle?: string;
    editModalTitle?: string;
    actions?: boolean;
    filterKey?: string;
    filterValue?: string;
    formSchema?: any[];

    resourceRoute?: string;
    validator?: (data: any) => boolean;
}>();

const isCreateOpen = ref(false);
const isEditOpen = ref(false);
const selectedRow = ref<any>(null);

const search = ref('');

const sortKey = ref<string | null>(null);
const sortDirection = ref<'asc' | 'desc'>('asc');

const normalizedRows = computed(() => {
    if (!props.rows) {
        return {
            data: [],
            current_page: 1,
            last_page: 1,
            next_page_url: null,
            prev_page_url: null,
            total: 0,
        };
    }

    if (Array.isArray(props.rows)) {
        return {
            data: props.rows,
            current_page: 1,
            last_page: 1,
            next_page_url: null,
            prev_page_url: null,
            total: props.rows.length,
        };
    }

    return props.rows;
});

const filteredRows = computed(() => {
    let data = [...(normalizedRows.value?.data ?? [])]; // tambah optional chaining + fallback

    // FILTER by key/value
    if (props.filterKey && props.filterValue) {
        data = data.filter(
            (row) => String(row[props.filterKey!] ?? '') === props.filterValue,
        );
    }

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
            if (aValue < bValue) return sortDirection.value === 'asc' ? -1 : 1;
            if (aValue > bValue) return sortDirection.value === 'asc' ? 1 : -1;
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
    if (props.validator && !props.validator(data)) return;
    handleCreate(props.resourceRoute!, data);
    isCreateOpen.value = false;
}

function submitEdit(data: any) {
    if (props.validator && !props.validator(data)) return;
    handleEdit(`${props.resourceRoute}/${data.id}`, data);
    isEditOpen.value = false;
}
</script>

<template>
    <div>
        <!-- OUTSIDE CARD: title, description, create button -->
        <div
            v-if="title || description || createLabel"
            class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
        >
            <div class="space-y-2">
                <h2
                    v-if="title"
                    class="text-2xl font-bold tracking-tight text-foreground"
                >
                    {{ title }}
                </h2>

                <p v-if="description" class="text-sm text-muted-foreground">
                    {{ description }}
                </p>
            </div>

            <button
                v-if="createLabel"
                class="inline-flex h-10 shrink-0 items-center justify-center rounded-lg bg-brand-accent px-5 text-sm font-medium text-white transition hover:opacity-90"
                @click="isCreateOpen = true"
            >
                + {{ createLabel }}
            </button>
        </div>

        <!-- TABLE CARD -->
        <div
            class="overflow-hidden rounded-2xl border border-border bg-background shadow-sm"
        >
            <!-- TOOLBAR: filter slot + search -->
            <div
                v-if="searchable || $slots.filter"
                class="flex flex-col gap-2 border-b border-border px-4 py-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <!-- FILTER — diisi oleh halaman parent via slot -->
                <div v-if="$slots.filter">
                    <slot name="filter" />
                </div>

                <div v-else />

                <!-- SEARCH -->
                <div v-if="searchable" class="relative w-full sm:w-64">
                    <Search
                        class="absolute top-1/2 left-3 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground"
                    />
                    <input
                        v-model="search"
                        type="text"
                        :placeholder="searchPlaceholder || 'Cari data...'"
                        class="h-9 w-full rounded-lg border border-border bg-background pr-3 pl-8 text-sm transition outline-none focus:ring-2 focus:ring-primary"
                    />
                </div>
            </div>

            <!-- DESKTOP TABLE -->
            <div class="hidden w-full overflow-x-auto md:block">
                <table class="w-full min-w-[700px] text-sm">
                    <!-- HEAD -->
                    <thead class="bg-surface">
                        <tr>
                            <th
                                v-for="column in columns"
                                :key="column.key"
                                class="h-12 px-4 text-left text-xs font-semibold text-muted-foreground"
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
                                        {{
                                            sortDirection === 'asc' ? '↑' : '↓'
                                        }}
                                    </span>
                                </button>

                                <span v-else>{{ column.label }}</span>
                            </th>

                            <th
                                v-if="actions"
                                class="w-[100px] px-4 text-right text-xs font-semibold text-muted-foreground"
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
                            class="cursor-pointer border-t border-border transition-colors hover:bg-muted/40"
                            @click="emit('rowClick', row)"
                        >
                            <td
                                v-for="column in columns"
                                :key="column.key"
                                class="max-w-[240px] truncate p-4 align-middle text-foreground"
                            >
                                <slot
                                    :name="column.key"
                                    :row="row"
                                    :index="index"
                                >
                                    {{ row[column.key] }}
                                </slot>
                            </td>

                            <!-- ACTION -->
                            <td v-if="actions" class="p-4">
                                <div
                                    class="flex items-center justify-end gap-1"
                                >
                                    <button
                                        class="flex h-8 w-8 items-center justify-center rounded-lg border border-border text-muted-foreground transition hover:bg-muted hover:text-foreground"
                                        title="Edit"
                                        @click="
                                            selectedRow = row;
                                            isEditOpen = true;
                                        "
                                    >
                                        <Pencil class="h-3.5 w-3.5" />
                                    </button>

                                    <button
                                        class="flex h-8 w-8 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-500 transition hover:bg-red-100"
                                        title="Hapus"
                                        @click.stop="emit('delete', row)"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- EMPTY -->
                        <tr v-if="filteredRows.length === 0">
                            <td
                                :colspan="columns.length + (actions ? 1 : 0)"
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
                    class="cursor-pointer rounded-xl border border-border bg-background p-4 shadow-sm"
                    @click="emit('rowClick', row)"
                >
                    <div class="space-y-3">
                        <div v-for="column in columns" :key="column.key">
                            <p
                                class="mb-1 text-xs font-medium tracking-wide text-muted-foreground uppercase"
                            >
                                {{ column.label }}
                            </p>

                            <div class="text-sm text-foreground">
                                <slot
                                    :name="column.key"
                                    :row="row"
                                    :index="index"
                                >
                                    {{ row[column.key] }}
                                </slot>
                            </div>
                        </div>
                    </div>

                    <!-- ACTION -->
                    <div
                        v-if="actions"
                        class="mt-4 flex items-center gap-2 border-t border-border pt-4"
                    >
                        <button
                            class="flex h-9 flex-1 items-center justify-center gap-1.5 rounded-lg border border-border text-sm text-muted-foreground transition hover:bg-muted"
                            @click="
                                selectedRow = row;
                                isEditOpen = true;
                            "
                        >
                            <Pencil class="h-3.5 w-3.5" />
                            Edit
                        </button>

                        <button
                            class="flex h-9 flex-1 items-center justify-center gap-1.5 rounded-lg border border-red-200 bg-red-50 text-sm text-red-600 transition hover:bg-red-100"
                            @click.stop="emit('delete', row)"
                        >
                            <Trash2 class="h-3.5 w-3.5" />
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
                class="flex flex-col gap-3 border-t border-border px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6"
            >
                <p class="text-sm text-muted-foreground">
                    Total {{ normalizedRows.total }} data
                </p>

                <div v-if="pagination" class="flex items-center gap-2">
                    <button
                        class="rounded-lg border border-border px-3 py-2 text-sm transition disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="normalizedRows.prev_page_url"
                        @click="
                            normalizedRows.prev_page_url &&
                            router.visit(normalizedRows.prev_page_url)
                        "
                    >
                        Sebelumnya
                    </button>

                    <div class="text-sm text-muted-foreground">
                        {{ normalizedRows.current_page }} /
                        {{ normalizedRows.last_page }}
                    </div>

                    <button
                        class="rounded-lg border border-border px-3 py-2 text-sm transition disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="normalizedRows.next_page_url"
                        @click="
                            normalizedRows.next_page_url &&
                            router.visit(normalizedRows.next_page_url)
                        "
                    >
                        Selanjutnya
                    </button>
                </div>
            </div>
        </div>
    </div>

    <FormModal
        :open="isCreateOpen"
        :title="createModalTitle || createLabel || 'Tambah Data'"
        :schema="formSchema || []"
        :submit-label="createLabel || 'Simpan'"
        @close="isCreateOpen = false"
        @submit="submitCreate"
    />

    <FormModal
        :open="isEditOpen"
        :title="
            editModalTitle ||
            (createModalTitle
                ? createModalTitle.replace(/^Tambah/, 'Edit')
                : 'Edit Data')
        "
        :schema="formSchema || []"
        submit-label="Simpan Perubahan"
        :data="selectedRow"
        @close="isEditOpen = false"
        @submit="submitEdit"
    />
</template>
