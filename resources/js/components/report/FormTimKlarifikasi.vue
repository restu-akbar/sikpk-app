<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DialogFooter from '@/components/DialogFooter.vue';
import DialogHeader from '@/components/DialogHeader.vue';
import {
    X,
    ChevronRight,
    ChevronLeft,
    UserRound,
    Search,
    Minus,
    Plus,
    Play,
    Pause,
    Mic,
} from 'lucide-vue-next';
import { getLabel } from '@/lib/getLabel';
import { jenisKekerasanOptions } from '@/constants/jenisKekerasanOptions';
import { formatDate } from '@/lib/formatDate';
import { getInitials, getAvatarColor } from '@/composables/useInitials';
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

const isVoiceReport = computed(() => {
    return props.report?.audio_recordings?.length > 0;
});

const ringkasanFields = computed(() => {
    const fields = [
        {
            label: 'Pelapor',
            value: props.report?.reporter?.name,
            semibold: true,
        },
        {
            label: 'Jenis',
            value: getLabel(jenisKekerasanOptions, props.report?.jenis_kekerasan),
            semibold: true,
        },
    ];

    if (!isVoiceReport.value) {
        fields.push(
            {
                label: 'Tempat',
                value: props.report?.tempat_kejadian,
                semibold: false,
            },
            {
                label: 'Waktu Kejadian',
                value: formatDate(props.report?.waktu_kejadian),
                semibold: false,
            },
        );
    }

    fields.push(
        {
            label: 'Jurusan',
            value: props.report?.reporter?.jurusan,
            semibold: false,
        },
        {
            label: 'Prodi',
            value: props.report?.reporter?.prodi,
            semibold: false,
        },
    );

    return fields;
});

const playingAudioId = ref<string | null>(null);

function toggleAudio(audioId: string) {
    const el = document.getElementById(`audio-tim-${audioId}`) as HTMLAudioElement;
    if (!el) return;

    if (playingAudioId.value === audioId) {
        el.pause();
        playingAudioId.value = null;
    } else {
        if (playingAudioId.value) {
            const prev = document.getElementById(`audio-tim-${playingAudioId.value}`) as HTMLAudioElement;
            prev?.pause();
        }
        el.play();
        playingAudioId.value = audioId;
    }
}

function onAudioEnded() {
    playingAudioId.value = null;
}

function formatDuration(seconds: number) {
    const m = Math.floor(seconds / 60);
    const s = Math.floor(seconds % 60);
    return `${m}:${s.toString().padStart(2, '0')}`;
}

const selected = ref<string[]>([]);
const activeTab = ref('');
const searchQ = ref('');

const tabOptions = [
    { label: 'Semua', value: '' },
    { label: 'Dosen', value: 'dosen' },
    { label: 'Mahasiswa', value: 'mahasiswa' },
];

const filteredSatgas = computed(() => {
    const data = props.satgasMembers?.data || [];

    return data.filter((m: any) => {
        const tabOk =
            activeTab.value === '' || m.academic_role === activeTab.value;

        const q = searchQ.value.toLowerCase();
        const searchOk =
            !q ||
            m.name?.toLowerCase().includes(q) ||
            m.department?.toLowerCase().includes(q);

        return tabOk && searchOk;
    });
});

function toggleMember(id: string) {
    const idx = selected.value.indexOf(id);
    if (idx > -1) {
        selected.value.splice(idx, 1);
    } else if (selected.value.length < 3) {
        selected.value.push(id);
    }
}

function getMember(id: string) {
    return props.satgasMembers.data?.find((m: any) => m.id === id);
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
        evidences: props.report.report_evidences,
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
                    <DialogHeader
                        :report="report"
                        @close="handleClose"
                    />

                    <!-- Body -->
                    <div class="flex-1 overflow-y-auto px-6">
                        <!-- Ringkasan Kasus -->
                        <div class="pt-5 pb-3">
                            <p class="mb-3 text-sm font-bold text-[#3B3A37]">
                                RINGKASAN KASUS
                            </p>

                            <div
                                class="mb-4 grid grid-cols-2 gap-x-8 gap-y-3"
                            >
                                <div
                                    v-for="(item, i) in ringkasanFields"
                                    :key="i"
                                >
                                    <p
                                        class="text-xs font-bold text-[#6B6862] uppercase"
                                    >
                                        {{ item.label }}
                                    </p>
                                    <p
                                        class="text-sm text-[#1B1A18]"
                                        :class="
                                            item.semibold
                                                ? 'font-semibold'
                                                : ''
                                        "
                                    >
                                        {{ item.value || '—' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Voice report: Rekaman Suara -->
                            <template v-if="isVoiceReport">
                                <p class="mb-3 text-sm font-bold text-[#3B3A37]">
                                    REKAMAN SUARA
                                </p>
                                <div class="flex flex-col gap-2">
                                    <div
                                        v-for="(audio, index) in report.audio_recordings"
                                        :key="audio.id"
                                        class="flex items-center gap-3 rounded-lg bg-[#F6F2EE] p-3"
                                    >
                                        <button
                                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#F5821F] text-white transition-colors hover:bg-[#e0741a]"
                                            @click="toggleAudio(audio.id)"
                                        >
                                            <Pause
                                                v-if="playingAudioId === audio.id"
                                                class="h-4 w-4"
                                            />
                                            <Play
                                                v-else
                                                class="h-4 w-4"
                                            />
                                        </button>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-[#3B3A37]">
                                                Rekaman {{ index + 1 }}
                                            </p>
                                            <p class="text-xs text-[#6B6862]">
                                                {{ audio.duration ? formatDuration(audio.duration) : '—' }}
                                            </p>
                                        </div>
                                        <Mic class="h-4 w-4 shrink-0 text-[#908C84]" />
                                        <audio
                                            :id="`audio-tim-${audio.id}`"
                                            :src="`/satgas/audio-recordings/${audio.id}`"
                                            preload="none"
                                            @ended="onAudioEnded"
                                        />
                                    </div>
                                </div>
                            </template>

                            <!-- Form report: Kronologi -->
                            <div
                                v-else-if="report?.kronologi"
                                class="rounded-lg bg-[#F6F2EE] p-4 text-sm leading-relaxed text-[#3B3A37]"
                            >
                                {{ report.kronologi }}
                            </div>
                        </div>

                        <!-- Tim Terpilih -->
                        <div class="py-3">
                            <p class="mb-1 text-sm font-bold text-[#3B3A37]">
                                TIM TERPILIH ({{ selected.length }}/3)
                            </p>
                            <p class="mb-3 text-xs text-[#6B6862]">
                                Anggota yang dipilih pertama akan ditetapkan
                                sebagai Ketua Tim.
                            </p>
                            <div
                                class="rounded-xl border border-[#E5E1D9] bg-surface p-3"
                            >
                                <div class="grid grid-cols-3 gap-3">
                                    <template v-for="i in 3" :key="i">
                                        <!-- Filled slot -->
                                        <div
                                            v-if="selected[i - 1]"
                                            class="flex items-center gap-2.5 rounded-lg border border-nav-stroke bg-white px-3 py-3"
                                        >
                                            <div
                                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                                                :class="
                                                    getAvatarColor(
                                                        getMember(
                                                            selected[i - 1],
                                                        )?.name,
                                                    )
                                                "
                                            >
                                                {{
                                                    getInitials(
                                                        getMember(
                                                            selected[i - 1],
                                                        )?.name,
                                                    )
                                                }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p
                                                    class="truncate text-xs font-medium text-[#1B1A18]"
                                                >
                                                    {{
                                                        getMember(
                                                            selected[i - 1],
                                                        )?.name
                                                    }}
                                                </p>
                                                <p
                                                    class="truncate text-[10px] text-[#6B6862]"
                                                >
                                                    {{
                                                        getMember(
                                                            selected[i - 1],
                                                        )?.academic_role ===
                                                        'dosen'
                                                            ? 'Dosen'
                                                            : 'Mahasiswa'
                                                    }}
                                                </p>
                                            </div>
                                            <button
                                                class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full hover:bg-red-100 hover:text-red-600"
                                                @click="
                                                    toggleMember(
                                                        selected[i - 1],
                                                    )
                                                "
                                            >
                                                <X class="h-3 w-3" />
                                            </button>
                                        </div>

                                        <!-- Empty slot -->
                                        <div
                                            v-else
                                            class="flex flex-col items-center justify-center gap-1.5 rounded-lg border border-dashed border-[#BAB6AE] bg-white py-5"
                                        >
                                            <UserRound
                                                class="h-6 w-6 text-[#BAB6AE]"
                                            />
                                            <span
                                                class="text-xs text-[#BAB6AE]"
                                                >Slot {{ i }} kosong</span
                                            >
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Daftar Anggota Satgas -->
                        <div class="pt-3 pb-5">
                            <!-- Title + Filter + Search -->
                            <div
                                class="mb-3 flex items-center justify-between"
                            >
                                <p
                                    class="text-sm font-bold text-[#3B3A37]"
                                >
                                    DAFTAR ANGGOTA SATGAS
                                </p>
                                <div class="flex items-center gap-3">
                                    <!-- Tab filter -->
                                    <div
                                        class="inline-flex h-9 items-center gap-0.5 rounded-lg border border-border bg-surface p-1"
                                    >
                                        <button
                                            v-for="tab in tabOptions"
                                            :key="tab.value"
                                            class="relative h-full rounded-md px-4 text-sm transition-all"
                                            :class="
                                                activeTab === tab.value
                                                    ? 'bg-background text-foreground shadow-sm'
                                                    : 'text-nav-muted hover:text-foreground'
                                            "
                                            @click="activeTab = tab.value"
                                        >
                                            <span
                                                class="invisible font-bold"
                                                >{{ tab.label }}</span
                                            >
                                            <span
                                                class="absolute inset-0 flex items-center justify-center"
                                                :class="
                                                    activeTab === tab.value
                                                        ? 'font-bold'
                                                        : 'font-normal'
                                                "
                                                >{{ tab.label }}</span
                                            >
                                        </button>
                                    </div>

                                    <!-- Search -->
                                    <div class="relative w-56">
                                        <Search
                                            class="absolute top-1/2 left-3 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground"
                                        />
                                        <input
                                            v-model="searchQ"
                                            type="text"
                                            placeholder="Cari nama/jurusan..."
                                            class="h-9 w-full rounded-lg border border-border bg-background pr-3 pl-8 text-sm outline-none transition focus:ring-2 focus:ring-primary"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Table -->
                            <div
                                class="overflow-hidden rounded-lg border border-nav-stroke"
                            >
                                <table class="w-full text-sm">
                                    <thead class="bg-surface">
                                        <tr>
                                            <th
                                                class="px-4 py-2.5 text-left text-xs font-semibold text-muted-foreground uppercase"
                                            >
                                                Nama
                                            </th>
                                            <th
                                                class="px-4 py-2.5 text-left text-xs font-semibold text-muted-foreground uppercase"
                                            >
                                                Unsur
                                            </th>
                                            <th
                                                class="px-4 py-2.5 text-left text-xs font-semibold text-muted-foreground uppercase"
                                            >
                                                Jurusan
                                            </th>
                                            <th
                                                class="px-4 py-2.5 text-left text-xs font-semibold text-muted-foreground uppercase"
                                            >
                                                Angkatan
                                            </th>
                                            <th
                                                class="px-4 py-2.5 text-left text-xs font-semibold text-muted-foreground uppercase"
                                            >
                                                Menangani
                                            </th>
                                            <th
                                                class="px-4 py-2.5 text-center text-xs font-semibold text-muted-foreground uppercase"
                                            >
                                                Aksi
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="member in filteredSatgas"
                                            :key="member.id"
                                            class="border-t border-nav-stroke transition-colors hover:bg-muted/30"
                                        >
                                            <!-- Nama -->
                                            <td class="px-4 py-3">
                                                <div
                                                    class="flex items-center gap-2.5"
                                                >
                                                    <div
                                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                                                        :class="
                                                            getAvatarColor(
                                                                member.name,
                                                            )
                                                        "
                                                    >
                                                        {{
                                                            getInitials(
                                                                member.name,
                                                            )
                                                        }}
                                                    </div>
                                                    <div>
                                                        <p
                                                            class="font-medium text-[#1B1A18]"
                                                        >
                                                            {{ member.name }}
                                                        </p>
                                                        <p
                                                            class="text-xs text-[#6B6862]"
                                                        >
                                                            {{
                                                                member.role ===
                                                                'ketua'
                                                                    ? 'Ketua'
                                                                    : member.role ===
                                                                        'wakil_ketua'
                                                                      ? 'Wakil Ketua'
                                                                      : member.role ===
                                                                          'sekretaris'
                                                                        ? 'Sekretaris'
                                                                        : 'Anggota'
                                                            }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Unsur -->
                                            <td class="px-4 py-3">
                                                <span
                                                    class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium"
                                                    :class="
                                                        member.academic_role ===
                                                        'dosen'
                                                            ? 'bg-blue-500/10 text-blue-600'
                                                            : 'bg-orange-500/10 text-orange-600'
                                                    "
                                                >
                                                    {{
                                                        member.academic_role ===
                                                        'dosen'
                                                            ? 'Dosen'
                                                            : 'Mahasiswa'
                                                    }}
                                                </span>
                                            </td>

                                            <!-- Jurusan -->
                                            <td
                                                class="px-4 py-3 text-[#1B1A18]"
                                            >
                                                {{
                                                    member.department || '—'
                                                }}
                                            </td>

                                            <!-- Angkatan -->
                                            <td
                                                class="px-4 py-3 text-[#1B1A18]"
                                            >
                                                {{
                                                    member.entry_year || '—'
                                                }}
                                            </td>

                                            <!-- Menangani -->
                                            <td class="px-4 py-3">
                                                <span
                                                    class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium"
                                                    :class="
                                                        (member.handled_reports_count ??
                                                            0) > 0
                                                            ? 'border-orange-200 bg-orange-50 text-orange-700'
                                                            : 'border-green-200 bg-green-50 text-green-700'
                                                    "
                                                >
                                                    {{
                                                        member.handled_reports_count ??
                                                        0
                                                    }}
                                                    Kasus
                                                </span>
                                            </td>

                                            <!-- Aksi -->
                                            <td class="px-4 py-3 text-center">
                                                <button
                                                    class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-nav-stroke transition-colors"
                                                    :class="
                                                        selected.includes(
                                                            member.id,
                                                        )
                                                            ? 'border-blue-200 bg-blue-50 text-blue-600'
                                                            : 'hover:bg-muted'
                                                    "
                                                    :disabled="
                                                        selected.length >= 3 &&
                                                        !selected.includes(
                                                            member.id,
                                                        )
                                                    "
                                                    @click.stop="
                                                        toggleMember(member.id)
                                                    "
                                                >
                                                    <Minus
                                                        v-if="
                                                            selected.includes(
                                                                member.id,
                                                            )
                                                        "
                                                        class="h-3.5 w-3.5"
                                                    />
                                                    <Plus
                                                        v-else
                                                        class="h-3.5 w-3.5 text-muted-foreground"
                                                    />
                                                </button>
                                            </td>
                                        </tr>

                                        <tr
                                            v-if="filteredSatgas.length === 0"
                                        >
                                            <td
                                                colspan="6"
                                                class="px-4 py-8 text-center text-sm text-muted-foreground"
                                            >
                                                Tidak ada anggota ditemukan
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <DialogFooter
                        back-label="Batal"
                        :back-icon="ChevronLeft"
                        action-label="Bentuk Tim"
                        :action-icon="ChevronRight"
                        :action-disabled="selected.length < 3"
                        :info-text="
                            selected.length > 0
                                ? `${selected.length} anggota terpilih`
                                : 'Belum ada anggota terpilih'
                        "
                        @back="handleBack"
                        @action="submit"
                    />
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
