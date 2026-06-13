<script setup lang="ts">
import { computed, ref } from 'vue';
import { X, Check, Clock, MapPin, Eye, ShieldCheck, Play, Pause, Mic } from 'lucide-vue-next';
import DialogFooter from '@/components/DialogFooter.vue';
import DialogHeader from '@/components/DialogHeader.vue';
import { formatDate } from '@/lib/formatDate';
import { getLabel } from '@/lib/getLabel';
import {
    statusCivitasOptions,
    statusOptions,
} from '@/constants/statusCivitasOptions';
import { disabilityOptions } from '@/constants/disability';
import { getInitials, getAvatarColor } from '@/composables/useInitials';
import axios from 'axios';
import { useCryptoStore } from '@/lib/crypto/store';
import { decryptFile } from '@/lib/mediaCrypto';

const cryptoStore = useCryptoStore();

async function openEvidence(file: any) {
    try {
        const { data } = await axios.get(`/satgas/evidences/${file.id}`, {
            responseType: 'blob',
        });
        const edek = file.edeks[cryptoStore.userId];
        const decryptedFile = await decryptFile({
            encryptedFile: data,
            edek,
            privateKey: cryptoStore.privateKey!,
            filename: file.original_filename,
            mimeType: file.mime_type,
        });

        const url = URL.createObjectURL(decryptedFile);
        window.open(url, '_blank');
    } catch (error) {
        console.error(error);
    }
}

const props = defineProps<{
    open: boolean;
    report: any | null;
}>();

defineEmits<{
    close: [];
    accept: [id: string];
    reject: [id: string];
}>();

const progressSteps = [
    { label: 'Laporan Dibuka', key: 'Laporan Baru' },
    { label: 'Klarifikasi', key: 'Klarifikasi' },
    { label: 'Pemeriksaan', key: 'Pemeriksaan' },
    { label: 'Kesimpulan', key: 'Kesimpulan' },
    { label: 'Pasca', key: 'Pasca' },
    { label: 'Selesai', key: 'Selesai' },
];

const currentStepIndex = computed(() => {
    if (!props.report) return 0;
    const idx = progressSteps.findIndex((s) => s.key === props.report.progress);
    return idx === -1 ? 0 : idx;
});

const hasTeam = computed(() => {
    return props.report?.handlers?.length > 0;
});

const isLaporanBaru = computed(() => {
    return props.report?.progress === 'Laporan Baru';
});

const isVoiceReport = computed(() => {
    return props.report?.audio_recordings?.length > 0;
});

const playingAudioId = ref<string | null>(null);

function toggleAudio(audioId: string) {
    const el = document.getElementById(`audio-${audioId}`) as HTMLAudioElement;
    if (!el) return;

    if (playingAudioId.value === audioId) {
        el.pause();
        playingAudioId.value = null;
    } else {
        if (playingAudioId.value) {
            const prev = document.getElementById(`audio-${playingAudioId.value}`) as HTMLAudioElement;
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

function identitasFields(report: any) {
    const fields = [
        { label: 'Nama', value: report.reporter?.name },
        { label: 'Peran', value: getLabel(statusCivitasOptions, report.reporter?.status_civitas) },
        { label: 'Status Pelapor', value: getLabel(statusOptions, report.status_pelapor) },
        { label: 'Jurusan', value: report.reporter?.jurusan },
        { label: 'Program Studi', value: report.reporter?.prodi },
        { label: 'Disabilitas', value: formatDisabilitas(report.reporter?.disabilitas) },
        { label: 'Email', value: report.reporter?.email },
        { label: 'Telepon', value: report.reporter?.whatsapp },
    ];

    if (!isLaporanBaru.value) {
        fields.splice(5, 0, {
            label: 'Angkatan',
            value: report.reporter?.angkatan,
        });
    }

    return fields;
}

function formatDisabilitas(value: any): string {
    if (!value || (Array.isArray(value) && value.length === 0)) return 'Tidak ada';
    const items = Array.isArray(value) ? value : [value];
    return items
        .map((v: string) => disabilityOptions.find((o) => o.value === v)?.label ?? v)
        .join(', ');
}

function formatFileSize(bytes: number) {
  if (bytes < 1024) return bytes + ' B';

  if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';

  return (bytes / 1048576).toFixed(1) + ' MB';
}

function fileIconType(name: string) {
    if (/\.(png|jpg|jpeg|webp)$/i.test(name)) return 'image';
    if (/\.pdf$/i.test(name)) return 'pdf';
    if (/\.(m4a|mp3|wav)$/i.test(name)) return 'audio';
    return 'file';
}
</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="open && report"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/45 p-4"
                @click.self="$emit('close')"
            >
                <div
                    class="flex h-[90vh] w-[75vw] flex-col overflow-hidden rounded-xl border border-border bg-background"
                >
                    <DialogHeader
                        :report="report"
                        @close="$emit('close')"
                    />

                    <!-- Scrollable body (progress + content as one unit) -->
                    <div class="flex-1 overflow-y-auto">
                        <!-- Progress Bar -->
                        <div class="bg-white px-6 py-4">
                            <div class="flex items-start justify-between">
                                <div
                                    v-for="(step, index) in progressSteps"
                                    :key="step.key"
                                    class="flex flex-1 flex-col items-center gap-1.5"
                                >
                                    <div
                                        class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold"
                                        :class="
                                            index < currentStepIndex
                                                ? 'bg-[#2E8B57] text-white'
                                                : index === currentStepIndex
                                                  ? 'bg-[#F5821F] text-white'
                                                  : 'border border-[#E5E1D9] bg-white text-[#908C84]'
                                        "
                                    >
                                        <svg
                                            v-if="index < currentStepIndex"
                                            class="h-3.5 w-3.5"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="3"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M5 13l4 4L19 7"
                                            />
                                        </svg>
                                        <span v-else>{{ index + 1 }}</span>
                                    </div>
                                    <span
                                        class="text-[11px] font-medium whitespace-nowrap"
                                        :class="
                                            index <= currentStepIndex
                                                ? 'text-[#3B3A37]'
                                                : 'text-[#908C84]'
                                        "
                                    >
                                        {{ step.label }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Content: 60/40 layout -->
                        <div class="flex">
                            <!-- Left 60% -->
                            <div class="w-[60%] px-6">
                                <!-- Identitas Pelapor -->
                                <div class="border-b border-nav-stroke py-5">
                                    <p
                                        class="mb-3 text-sm font-bold text-[#3B3A37]"
                                    >
                                        IDENTITAS PELAPOR
                                    </p>
                                    <div
                                        v-for="field in identitasFields(report)"
                                        :key="field.label"
                                        class="flex border-b border-dashed border-nav-stroke py-2 last:border-0"
                                    >
                                        <span
                                            class="w-36 shrink-0 text-sm text-[#6B6862]"
                                        >
                                            {{ field.label }}
                                        </span>
                                        <span class="text-sm text-[#1B1A18]">
                                            {{ field.value ?? '—' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Voice report: Rekaman Suara -->
                                <template v-if="isVoiceReport">
                                    <div class="py-5">
                                        <p
                                            class="mb-3 text-sm font-bold text-[#3B3A37]"
                                        >
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
                                                    :id="`audio-${audio.id}`"
                                                    :src="`/satgas/audio-recordings/${audio.id}`"
                                                    preload="none"
                                                    @ended="onAudioEnded"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Form report: Tempat/Waktu + Kronologi -->
                                <template v-else>
                                    <div class="border-b border-nav-stroke py-5">
                                        <p
                                            class="mb-3 text-sm font-bold text-[#3B3A37]"
                                        >
                                            TEMPAT & WAKTU KEJADIAN
                                        </p>
                                        <div
                                            class="flex border-b border-dashed border-nav-stroke py-2"
                                        >
                                            <span
                                                class="flex w-36 shrink-0 items-center gap-2 text-sm text-[#6B6862]"
                                            >
                                                <MapPin class="h-3.5 w-3.5" />
                                                Tempat
                                            </span>
                                            <span class="text-sm text-[#1B1A18]">
                                                {{ report.tempat_kejadian }}
                                            </span>
                                        </div>
                                        <div class="flex py-2">
                                            <span
                                                class="flex w-36 shrink-0 items-center gap-2 text-sm text-[#6B6862]"
                                            >
                                                <Clock class="h-3.5 w-3.5" />
                                                Waktu
                                            </span>
                                            <span class="text-sm text-[#1B1A18]">
                                                {{
                                                    formatDate(
                                                        report.waktu_kejadian,
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="py-5">
                                        <p
                                            class="mb-3 text-sm font-bold text-[#3B3A37]"
                                        >
                                            KRONOLOGI KEJADIAN
                                        </p>
                                        <div
                                            class="rounded-lg bg-[#F6F2EE] p-4 text-sm leading-relaxed text-[#3B3A37]"
                                        >
                                            {{ report.kronologi }}
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Right 40% -->
                            <div class="w-[40%] px-5 py-5">
                                <div class="flex flex-col gap-4">
                                    <!-- Tim Penanganan -->
                                    <div
                                        v-if="hasTeam"
                                        class="rounded-xl border border-nav-stroke bg-surface p-4"
                                    >
                                        <p
                                            class="mb-3 text-sm font-bold text-[#3B3A37]"
                                        >
                                            TIM PENANGANAN
                                        </p>
                                        <div class="flex flex-col gap-3">
                                            <div
                                                v-for="handler in report.handlers"
                                                :key="handler.id"
                                                class="flex items-center gap-3"
                                            >
                                                <div
                                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                                                    :class="
                                                        getAvatarColor(
                                                            handler.name,
                                                        )
                                                    "
                                                >
                                                    {{
                                                        getInitials(
                                                            handler.name,
                                                        )
                                                    }}
                                                </div>
                                                <div>
                                                    <p
                                                        class="text-sm font-medium text-[#1B1A18]"
                                                    >
                                                        {{ handler.name }}
                                                    </p>
                                                    <p
                                                        class="text-xs text-[#6B6862]"
                                                    >
                                                        {{
                                                            handler.academic_role ===
                                                            'dosen'
                                                                ? 'Dosen'
                                                                : 'Mahasiswa'
                                                        }}
                                                        ·
                                                        {{
                                                            handler.department
                                                        }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Privasi -->
                                    <div
                                        v-if="hasTeam"
                                        class="rounded-xl border border-nav-stroke bg-white p-4"
                                    >
                                        <div
                                            class="flex items-start gap-2.5 rounded-lg bg-[#E7EEF7] p-3"
                                        >
                                            <ShieldCheck
                                                class="mt-0.5 h-4 w-4 shrink-0 text-[#134881]"
                                            />
                                            <p
                                                class="text-xs leading-relaxed text-[#134881]"
                                            >
                                                Demi privasi pelapor, isi
                                                dokumen penanganan hanya dapat
                                                diakses oleh tim penangan.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Bukti Pendukung -->
                                    <div
                                        class="rounded-xl border border-nav-stroke bg-white p-4"
                                    >
                                        <p
                                            class="mb-3 text-sm font-bold text-[#3B3A37]"
                                        >
                                            BUKTI PENDUKUNG ({{
                                                report.report_evidences?.length ?? 0
                                            }})
                                        </p>
                                        <div class="flex flex-col gap-2">
                                            <div
                                                v-for="file in report.report_evidences"
                                                :key="file.id"
                                                class="flex items-center gap-3 rounded-lg border border-nav-stroke p-3"
                                            >
                                                <div
                                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#F6F2EE]"
                                                >
                                                    <svg
                                                        v-if="
                                                            fileIconType(
                                                                file.original_filename,
                                                            ) === 'image'
                                                        "
                                                        class="h-4 w-4 text-[#6B6862]"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.5"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <rect
                                                            x="3"
                                                            y="3"
                                                            width="18"
                                                            height="18"
                                                            rx="2"
                                                        />
                                                        <circle
                                                            cx="8.5"
                                                            cy="8.5"
                                                            r="1.5"
                                                        />
                                                        <path
                                                            d="m21 15-5-5L5 21"
                                                        />
                                                    </svg>
                                                    <svg
                                                        v-else-if="
                                                            fileIconType(
                                                                file.original_filename,
                                                            ) === 'pdf'
                                                        "
                                                        class="h-4 w-4 text-red-500"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.5"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"
                                                        />
                                                        <path
                                                            d="M14 2v6h6"
                                                        />
                                                    </svg>
                                                    <svg
                                                        v-else
                                                        class="h-4 w-4 text-[#6B6862]"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.5"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"
                                                        />
                                                        <path
                                                            d="M14 2v6h6"
                                                        />
                                                    </svg>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <p
                                                        class="truncate text-sm font-medium text-[#1B1A18]"
                                                    >
                                                        {{
                                                            file.original_filename
                                                        }}
                                                    </p>
                                                    <p
                                                        class="text-xs text-[#6B6862]"
                                                    >
                                                        {{
                                                            formatFileSize(
                                                                file.size,
                                                            )
                                                        }}
                                                    </p>
                                                </div>
                                                <button
                                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-nav-stroke transition-colors hover:bg-surface"
                                                    @click="openEvidence(file)"
                                                >
                                                    <Eye
                                                        class="h-4 w-4 text-[#6B6862]"
                                                    />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <DialogFooter
                        back-label="Tutup"
                        reject-label="Tolak Laporan"
                        :reject-icon="X"
                        :reject-disabled="report.progress !== 'Laporan Baru'"
                        action-label="Terima Laporan"
                        :action-icon="Check"
                        :action-disabled="report.progress !== 'Laporan Baru'"
                        @back="$emit('close')"
                        @reject="$emit('reject', report.id)"
                        @action="$emit('accept', report.id)"
                    />
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
