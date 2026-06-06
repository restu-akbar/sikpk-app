<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
    X,
    Check,
    ChevronRight,
    ChevronLeft,
    UserIcon,
    Search,
    Minus,
    Plus,
} from 'lucide-vue-next';
import DataTable from '@/components/table/DataTable.vue';
import { getLabel } from '@/lib/getLabel';
import { jenisKekerasanOptions } from '@/constants/jenisKekerasanOptions';
import axios from 'axios';
import { formatDate } from '@/lib/formatDate';
import { satgasApi } from '@/lib/axios';
import { handleCreate } from '@/lib/handleRequest';
import { assign } from '@/routes/satgas/reports';
import { useCryptoStore } from '@/lib/crypto/store';
import { getPublicKeys } from '@/lib/crypto/getPublicKeys';
import { reEncryptEdeks } from '@/lib/crypto/re-encrypt-edeks';

const props = defineProps<{
    open: boolean;
    report: any | null;
    satgasMembers: {
        data: any[];
        current_page: number;
        last_page: number;
        next_page_url: string | null;
        prev_page_url: string | null;
        total: number;
    };
}>();

const emit = defineEmits<{
    close: [];
    submitted: [];
    back: [];
}>();

const ringkasanFields = computed(() => [
    {
        label: 'Pelapor',
        value: props.report?.reporter?.name,
        bold: true,
    },
    {
        label: 'Jenis',
        value: getLabel(jenisKekerasanOptions, props.report?.jenis_kekerasan),
        bold: true,
    },
    {
        label: 'Tempat',
        value: props.report?.tempat_kejadian,
    },
    {
        label: 'Waktu Kejadian',
        value: formatDate(props.report?.waktu_kejadian),
    },
    {
        label: 'Jurusan',
        value: props.report?.reporter?.jurusan,
    },
    {
        label: 'Prodi',
        value: props.report?.reporter?.prodi,
    },
]);

const selected = ref<number[]>([]);
const activeTab = ref('Semua');
const searchQ = ref('');

const members = computed(() => {
    return Array.isArray(props.satgasMembers?.data)
        ? props.satgasMembers.data
        : [];
});

const columns = [
    { key: 'name', label: 'Nama' },
    { key: 'unsur', label: 'Unsur' },
    { key: 'jurusan', label: 'Jurusan' },
    { key: 'angkatan', label: 'Angkatan' },
    { key: 'status', label: 'Status' },
    { key: 'aksi', label: 'Aksi' },
];

const filteredSatgas = computed(() => {
    const data = props.satgasMembers.data || [];

    return data.filter((m) => {
        const tabOk =
            activeTab.value === 'Semua' || m.unsur === activeTab.value;

        const q = searchQ.value.toLowerCase();

        const searchOk =
            !q ||
            m.name.toLowerCase().includes(q) ||
            m.jurusan?.toLowerCase().includes(q);

        return tabOk && searchOk;
    });
});

const satgasTable = computed(() => ({
    data: filteredSatgas.value,
    current_page: props.satgasMembers.current_page,
    last_page: props.satgasMembers.last_page,
    next_page_url: props.satgasMembers.next_page_url,
    prev_page_url: props.satgasMembers.prev_page_url,
    total: props.satgasMembers.total,
}));

function toggleMember(id: string) {
    const idx = selected.value.indexOf(id);
    if (idx > -1) {
        selected.value.splice(idx, 1);
    } else if (selected.value.length < 3) {
        selected.value.push(id);
    }
}

function getMember(id: string) {
    return props.satgasMembers.data?.find((m) => m.id === id);
}

const form = useForm({
    anggota: [] as string[],
});

async function submit() {
    if (selected.value.length === 0) return;

    const cryptoStore = useCryptoStore();

    if (!cryptoStore.privateKey) {
        throw new Error('Private key belum tersedia');
    }

    const publicKeys = await getPublicKeys(selected.value);

    const edekUpdates = await reEncryptEdeks({
        evidences: props.report.evidences,
        currentUserId: cryptoStore.userId,
        privateKey: cryptoStore.privateKey,
        targetPublicKeys: publicKeys,
    });

    form.anggota = selected.value;

    handleCreate(
        form.transform((data) => ({
            ...data,
            edek_updates: edekUpdates,
        })),
        assign(props.report.id),
        {
            onSuccess: () => {
                selected.value = [];
                emit('submitted');
            },
        },
    );
}

function handleClose() {
    selected.value = [];
    emit('close');
}

function handleBack() {
    selected.value = [];
    emit('back');
}
</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="open"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/45 p-4"
                @click.self="handleClose"
            >
                <div
                    class="flex h-[90vh] w-[75vw] flex-col overflow-hidden rounded-xl border border-border bg-background"
                >
                    <!-- Header -->
                    <div
                        class="relative shrink-0 border-b border-border px-6 py-5"
                    >
                        <div class="mb-2 flex items-center gap-2">
                            <span
                                class="font-mono text-xs text-muted-foreground"
                            >
                                {{ report.id }}
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700"
                            >
                                <span
                                    class="h-1.5 w-1.5 rounded-full bg-blue-600"
                                />
                                Laporan Baru
                            </span>
                        </div>
                        <h2 class="text-base font-medium">
                            Bentuk Tim Klarifikasi
                        </h2>
                        <p class="mt-0.5 text-xs text-muted-foreground">
                            Pilih hingga 3 anggota satgas yang akan menangani
                            laporan ini.
                        </p>
                        <button
                            class="absolute top-5 right-5 flex h-7 w-7 items-center justify-center rounded-lg hover:bg-muted"
                            @click="handleClose"
                        >
                            <X class="h-4 w-4 text-muted-foreground" />
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="flex-1 overflow-y-auto px-6">
                        <!-- Ringkasan Kasus -->
                        <div class="border-b border-border py-4">
                            <p
                                class="mb-3 text-[10px] font-medium tracking-wider text-muted-foreground uppercase"
                            >
                                Ringkasan Kasus
                            </p>

                            <div
                                class="mb-3 grid grid-cols-2 gap-x-6 gap-y-2.5"
                            >
                                <div
                                    v-for="(item, i) in ringkasanFields"
                                    :key="i"
                                >
                                    <p
                                        class="text-[10px] tracking-wider text-muted-foreground uppercase"
                                    >
                                        {{ item.label }}
                                    </p>

                                    <p
                                        class="text-sm"
                                        :class="item.bold ? 'font-medium' : ''"
                                    >
                                        {{ item.value }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="rounded-lg bg-muted/50 p-3 text-xs leading-relaxed text-muted-foreground"
                            >
                                {{ report.kronologi }}
                            </div>
                        </div>

                        <!-- Slot Tim Terpilih -->
                        <div class="border-b border-border py-4">
                            <p
                                class="mb-3 text-[10px] font-medium tracking-wider text-muted-foreground uppercase"
                            >
                                Tim Terpilih ({{ selected.length }}/3)
                            </p>
                            <div class="grid grid-cols-3 gap-2">
                                <template v-for="i in 3" :key="i">
                                    <div
                                        v-if="selected[i - 1]"
                                        class="flex items-center gap-2 rounded-lg border border-border bg-muted/40 px-3 py-2.5"
                                    >
                                        <div
                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700"
                                        >
                                            {{
                                                getMember(selected[i - 1])
                                                    ?.initials
                                            }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p
                                                class="truncate text-xs font-medium"
                                            >
                                                {{
                                                    getMember(selected[i - 1])
                                                        ?.name
                                                }}
                                            </p>
                                            <p
                                                class="truncate text-[10px] text-muted-foreground"
                                            >
                                                {{
                                                    getMember(selected[i - 1])
                                                        ?.unsur
                                                }}
                                            </p>
                                        </div>
                                        <button
                                            class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full hover:bg-red-100 hover:text-red-600"
                                            @click="
                                                toggleMember(selected[i - 1])
                                            "
                                        >
                                            <X class="h-3 w-3" />
                                        </button>
                                    </div>
                                    <div
                                        v-else
                                        class="flex flex-col items-center justify-center gap-1.5 rounded-lg border border-dashed border-border py-4 text-muted-foreground"
                                    >
                                        <UserIcon class="h-5 w-5 opacity-40" />
                                        <span class="text-xs"
                                            >Slot {{ i }} kosong</span
                                        >
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Daftar Anggota Satgas -->
                        <DataTable
                            title="Daftar Anggota Satgas"
                            :columns="columns"
                            :rows="satgasTable"
                            :searchable="true"
                            search-placeholder="Cari nama/jurusan..."
                            :pagination="true"
                            :actions="false"
                        >
                            <!-- FILTER TAB -->
                            <template #filter>
                                <div class="flex items-center gap-2">
                                    <div
                                        class="flex rounded-lg border border-border"
                                    >
                                        <button
                                            v-for="tab in [
                                                'Semua',
                                                'Dosen',
                                                'Mahasiswa',
                                            ]"
                                            :key="tab"
                                            class="px-3 py-1.5 text-xs transition-colors first:rounded-l-lg last:rounded-r-lg"
                                            :class="
                                                activeTab === tab
                                                    ? 'bg-foreground font-medium text-background'
                                                    : 'text-muted-foreground hover:text-foreground'
                                            "
                                            @click="activeTab = tab"
                                        >
                                            {{ tab }}
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <!-- NAMA -->
                            <template #name="{ row }">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold"
                                        :class="row.avatarClass"
                                    >
                                        {{ row.initials }}
                                    </div>

                                    <div>
                                        <p class="font-medium">
                                            {{ row.name }}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ row.role }}
                                        </p>
                                    </div>
                                </div>
                            </template>

                            <!-- UNSUR -->
                            <template #unsur="{ row }">
                                <span
                                    class="rounded-full bg-orange-50 px-2 py-0.5 text-[11px] font-medium text-orange-700"
                                >
                                    {{ row.unsur }}
                                </span>
                            </template>

                            <!-- STATUS -->
                            <template #status="{ row }">
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-medium"
                                    :class="
                                        row.status === 'available'
                                            ? 'bg-green-50 text-green-700'
                                            : 'bg-yellow-50 text-yellow-700'
                                    "
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-current"
                                    />
                                    {{
                                        row.status === 'available'
                                            ? 'Tersedia'
                                            : 'Sedang menangani'
                                    }}
                                </span>
                            </template>

                            <!-- AKSI (SELECT MEMBER) -->
                            <template #aksi="{ row }">
                                <button
                                    class="flex h-7 w-7 items-center justify-center rounded-lg border border-border transition-colors"
                                    :class="
                                        selected.includes(row.id)
                                            ? 'border-blue-200 bg-blue-50 text-blue-600'
                                            : 'hover:bg-muted'
                                    "
                                    :disabled="
                                        selected.length >= 3 &&
                                        !selected.includes(row.id)
                                    "
                                    @click.stop="toggleMember(row.id)"
                                >
                                    <Minus
                                        v-if="selected.includes(row.id)"
                                        class="h-3.5 w-3.5"
                                    />
                                    <Plus
                                        v-else
                                        class="h-3.5 w-3.5 text-muted-foreground"
                                    />
                                </button>
                            </template>
                        </DataTable>
                    </div>

                    <!-- Footer -->
                    <div
                        class="flex shrink-0 items-center justify-between border-t border-border px-6 py-4"
                    >
                        <button
                            class="inline-flex h-9 items-center gap-2 rounded-lg border border-border px-4 text-sm hover:bg-muted"
                            @click="handleBack"
                        >
                            <ChevronLeft class="h-4 w-4" />
                            <span>Kembali</span>
                        </button>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-muted-foreground">
                                {{
                                    selected.length > 0
                                        ? `${selected.length} anggota terpilih`
                                        : 'Belum ada anggota terpilih'
                                }}
                            </span>
                            <button
                                class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-orange-500 px-4 text-sm font-medium text-white transition-opacity hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-40"
                                :disabled="selected.length < 3"
                                @click="submit"
                            >
                                Bentuk Tim <ChevronRight class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
