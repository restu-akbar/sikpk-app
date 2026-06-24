<script setup lang="ts">
import { computed, nextTick, ref } from 'vue';
import {
    X,
    Eye,
    Clock,
    MapPin,
    Mic,
    Play,
    Pause,
    CheckCircle2,
    AlertTriangle,
    FileText,
    FileAudio,
    FileVideo,
    Image as ImageIcon,
    File as FileIcon,
} from 'lucide-vue-next';
import { onClickOutside } from '@vueuse/core';
import DialogHeader from '@/components/DialogHeader.vue';
import DialogFooter from '@/components/DialogFooter.vue';
import { formatDate } from '@/lib/formatDate';
import { getLabel } from '@/lib/getLabel';
import { getReportLabel } from '@/lib/mapping/reportTypeLabelMap';
import {
    statusCivitasOptions,
    statusTerlaporOptions,
} from '@/constants/statusCivitasOptions';
import { disabilityOptions } from '@/constants/disability';
import { nomorIdentitasRules } from '@/constants/nomorIdentitasRules';
import { DEFAULT_REJECTED_REASONS } from '@/types/reports';
import { getInitials, getAvatarColor } from '@/composables/useInitials';
import { useFileViewer } from '@/composables/useFileViewer';

const props = defineProps<{
    open: boolean;
    report: any | null;
}>();

defineEmits<{
    close: [];
}>();

const { audioPreview, viewFile, closeAudioPreview } = useFileViewer();

const isVoiceReport = computed(() => {
    return (props.report?.audio_recordings?.length ?? 0) > 0;
});

const hasTeam = computed(() => {
    return (props.report?.members?.length ?? 0) > 0;
});

const isFinalSelesai = computed(() => props.report?.progress === 'Selesai');

const closedAt = computed(() => {
    const logs = props.report?.report_logs ?? [];
    return logs.length ? logs[logs.length - 1].created_at : props.report?.updated_at;
});

const statusKasus = computed(() => {
    const hasNamaBaik = (props.report?.report_documents ?? []).some(
        (doc: any) => doc.subtype === 'pemulihan_nama_baik',
    );
    return hasNamaBaik ? 'Tidak Terbukti' : 'Terbukti';
});

const rejectedReasonLabel = computed(() => {
    const reason = props.report?.rejected_reason;
    return (
        DEFAULT_REJECTED_REASONS.find((r) => r.value === reason)?.label ??
        reason ??
        'Alasan tidak tercatat'
    );
});

function formatDisabilitasBoolean(value: any): string {
    return value ? 'Ya' : 'Tidak';
}

function formatDisabilitasArray(value: any): string {
    if (!value || (Array.isArray(value) && value.length === 0))
        return 'Tidak ada';
    const items = Array.isArray(value) ? value : [value];
    return items
        .map(
            (v: string) =>
                disabilityOptions.find((o) => o.value === v)?.label ?? v,
        )
        .join(', ');
}

function nomorIdentitasLabel(peranAkademik?: string | null): string {
    return nomorIdentitasRules[peranAkademik ?? '']?.label ?? 'Nomor Identitas';
}

function withoutEmpty(fields: { label: string; value: any }[]) {
    return fields.filter(
        (field) => field.value !== null && field.value !== undefined && field.value !== '',
    );
}

const identitasPelapor = computed(() => {
    const korban = props.report?.korban;

    if (korban) {
        return withoutEmpty([
            { label: 'Nama', value: korban.nama },
            {
                label: 'Peran',
                value: getLabel(statusCivitasOptions, korban.peran_akademik),
            },
            {
                label: nomorIdentitasLabel(korban.peran_akademik),
                value: korban.nomor_identitas,
            },
            { label: 'Jurusan', value: korban.jurusan },
            { label: 'Program Studi', value: korban.prodi },
            { label: 'Angkatan', value: korban.angkatan },
            { label: 'Gender', value: korban.jenis_kelamin },
            {
                label: 'Disabilitas',
                value: formatDisabilitasBoolean(korban.disabilitas),
            },
            { label: 'No Whatsapp', value: korban.nomor_wa },
        ]);
    }

    const reporter = props.report?.reporter;
    return withoutEmpty([
        { label: 'Nama', value: reporter?.name },
        {
            label: 'Peran',
            value: getLabel(statusCivitasOptions, reporter?.status_civitas),
        },
        { label: 'Jurusan', value: reporter?.jurusan },
        { label: 'Program Studi', value: reporter?.prodi },
        {
            label: 'Disabilitas',
            value: formatDisabilitasArray(reporter?.disabilitas),
        },
        { label: 'No Whatsapp', value: reporter?.whatsapp },
    ]);
});

const identitasTerlapor = computed(() => {
    const terlapor = props.report?.terlapor;

    if (terlapor) {
        return withoutEmpty([
            { label: 'Nama', value: terlapor.nama },
            {
                label: 'Peran',
                value: getLabel(statusTerlaporOptions, terlapor.peran_akademik),
            },
            {
                label: nomorIdentitasLabel(terlapor.peran_akademik),
                value: terlapor.nomor_identitas,
            },
            { label: 'Jurusan', value: terlapor.jurusan },
            { label: 'Program Studi', value: terlapor.prodi },
            { label: 'Angkatan', value: terlapor.angkatan },
            { label: 'Gender', value: terlapor.jenis_kelamin },
            {
                label: 'Disabilitas',
                value: formatDisabilitasBoolean(terlapor.disabilitas),
            },
            { label: 'No Whatsapp', value: terlapor.nomor_wa },
        ]);
    }

    if (!props.report?.nama_terlapor) {
        return null;
    }

    return withoutEmpty([
        { label: 'Nama', value: props.report.nama_terlapor },
        {
            label: 'Peran',
            value: getLabel(statusTerlaporOptions, props.report.status_terlapor),
        },
    ]);
});

const stepOrder = ['Klarifikasi', 'Pemeriksaan', 'Kesimpulan', 'Pasca'];

const documentGroups = computed(() => {
    const docs = props.report?.report_documents ?? [];

    return stepOrder
        .map((step) => ({
            step,
            docs: docs.filter((d: any) => d.type === step),
        }))
        .filter((group) => group.docs.length > 0);
});

const totalDocuments = computed(() =>
    documentGroups.value.reduce((sum, g) => sum + g.docs.length, 0),
);

const activeViewDocMenuId = ref<string | null>(null);
const eyeMenuPosition = ref({ top: '0px', left: '0px' });
const eyeMenuPopupRef = ref<HTMLElement | null>(null);

onClickOutside(
    eyeMenuPopupRef,
    () => {
        activeViewDocMenuId.value = null;
    },
    { ignore: ['.eye-menu-btn'] },
);

function getAttachmentIconMeta(mimeType?: string | null) {
    if (mimeType?.startsWith('audio/')) {
        return { icon: FileAudio, bg: 'bg-[#F1E9FB]', color: 'text-purple-600' };
    }
    if (mimeType?.startsWith('video/')) {
        return { icon: FileVideo, bg: 'bg-[#FCE9EC]', color: 'text-pink-600' };
    }
    if (mimeType?.startsWith('image/')) {
        return { icon: ImageIcon, bg: 'bg-[#E8F5EA]', color: 'text-green-600' };
    }
    if (mimeType === 'application/pdf') {
        return { icon: FileText, bg: 'bg-[#FBEEE3]', color: 'text-orange-600' };
    }
    return { icon: FileIcon, bg: 'bg-[#F1EFEA]', color: 'text-gray-500' };
}

async function toggleViewDocMenu(event: MouseEvent, doc: any) {
    if (activeViewDocMenuId.value === doc.id) {
        activeViewDocMenuId.value = null;
        return;
    }

    activeViewDocMenuId.value = doc.id;

    const button = event.currentTarget as HTMLElement;
    const rect = button.getBoundingClientRect();

    eyeMenuPosition.value = {
        top: `${rect.top}px`,
        left: `${rect.left - 232}px`,
    };

    await nextTick();

    const popup = document.querySelector('.eye-menu-popup') as HTMLElement;
    if (popup) {
        const popupRect = popup.getBoundingClientRect();

        if (popupRect.bottom > window.innerHeight) {
            let newTop = rect.bottom - popupRect.height;
            if (newTop < 16) newTop = 16;
            eyeMenuPosition.value.top = `${newTop}px`;
        }

        if (popupRect.left < 0) {
            eyeMenuPosition.value.left = `${rect.right + 8}px`;
        }
    }
}

function openAttachment(att: any) {
    activeViewDocMenuId.value = null;
    viewFile(att, 'document');
}

function openEvidence(file: any) {
    viewFile(file, 'evidence');
}

const playingAudioId = ref<string | null>(null);

function toggleAudio(audioId: string) {
    const el = document.getElementById(
        `archive-audio-${audioId}`,
    ) as HTMLAudioElement;
    if (!el) return;

    if (playingAudioId.value === audioId) {
        el.pause();
        playingAudioId.value = null;
    } else {
        if (playingAudioId.value) {
            const prev = document.getElementById(
                `archive-audio-${playingAudioId.value}`,
            ) as HTMLAudioElement;
            prev?.pause();
        }
        el.play();
        playingAudioId.value = audioId;
    }
}

function onAudioEnded() {
    playingAudioId.value = null;
}

function formatFileSize(bytes: number) {
    if (!bytes) return '—';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
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
                        :closed-at="closedAt"
                        @close="$emit('close')"
                    />

                    <div class="flex-1 overflow-y-auto px-6 py-5">
                        <!-- Status banner -->
                        <div
                            v-if="isFinalSelesai"
                            class="mb-5 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 p-4"
                        >
                            <CheckCircle2 class="h-5 w-5 shrink-0 text-green-600" />
                            <p class="text-sm font-semibold text-green-700">
                                Penanganan Selesai · {{ statusKasus }}
                            </p>
                        </div>
                        <div
                            v-else
                            class="mb-5 flex items-start gap-3 rounded-xl border border-orange-200 bg-orange-50 p-4"
                        >
                            <AlertTriangle
                                class="mt-0.5 h-5 w-5 shrink-0 text-orange-600"
                            />
                            <div>
                                <p class="text-sm font-semibold text-orange-700">
                                    {{ rejectedReasonLabel }}
                                </p>
                                <p
                                    v-if="report.note_reason"
                                    class="mt-0.5 text-sm text-orange-700/80"
                                >
                                    {{ report.note_reason }}
                                </p>
                            </div>
                        </div>


                        <!-- Identitas -->
                        <div class="grid grid-cols-2 gap-x-8">
                            <div class="border-b border-nav-stroke py-5">
                                <p class="mb-3 text-sm font-bold text-[#3B3A37]">
                                    IDENTITAS PELAPOR
                                </p>
                                <div
                                    v-for="field in identitasPelapor"
                                    :key="field.label"
                                    class="flex border-b border-dashed border-nav-stroke py-2 last:border-0"
                                >
                                    <span class="w-36 shrink-0 text-sm text-[#6B6862]">
                                        {{ field.label }}
                                    </span>
                                    <span class="text-sm text-[#1B1A18]">
                                        {{ field.value ?? '—' }}
                                    </span>
                                </div>
                            </div>

                            <div class="border-b border-nav-stroke py-5">
                                <p class="mb-3 text-sm font-bold text-[#3B3A37]">
                                    IDENTITAS TERLAPOR
                                </p>
                                <template v-if="identitasTerlapor">
                                    <div
                                        v-for="field in identitasTerlapor"
                                        :key="field.label"
                                        class="flex border-b border-dashed border-nav-stroke py-2 last:border-0"
                                    >
                                        <span class="w-36 shrink-0 text-sm text-[#6B6862]">
                                            {{ field.label }}
                                        </span>
                                        <span class="text-sm text-[#1B1A18]">
                                            {{ field.value ?? '—' }}
                                        </span>
                                    </div>
                                </template>
                                <p v-else class="text-sm text-muted-foreground italic">
                                    Identitas terlapor tidak tercatat.
                                </p>
                            </div>
                        </div>

                        <!-- Voice report: Rekaman Suara -->
                        <template v-if="isVoiceReport">
                            <div class="border-b border-nav-stroke py-5">
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
                                            <Play v-else class="h-4 w-4" />
                                        </button>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-[#3B3A37]">
                                                Rekaman {{ index + 1 }}
                                            </p>
                                            <p class="text-xs text-[#6B6862]">
                                                {{ audio.duration ? `${Math.floor(audio.duration / 60)}:${String(Math.floor(audio.duration % 60)).padStart(2, '0')}` : '—' }}
                                            </p>
                                        </div>
                                        <Mic class="h-4 w-4 shrink-0 text-[#908C84]" />
                                        <audio
                                            :id="`archive-audio-${audio.id}`"
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
                                <p class="mb-3 text-sm font-bold text-[#3B3A37]">
                                    TEMPAT & WAKTU KEJADIAN
                                </p>
                                <div class="flex border-b border-dashed border-nav-stroke py-2">
                                    <span class="flex w-36 shrink-0 items-center gap-2 text-sm text-[#6B6862]">
                                        <MapPin class="h-3.5 w-3.5" />
                                        Tempat
                                    </span>
                                    <span class="text-sm text-[#1B1A18]">
                                        {{ report.tempat_kejadian }}
                                    </span>
                                </div>
                                <div class="flex py-2">
                                    <span class="flex w-36 shrink-0 items-center gap-2 text-sm text-[#6B6862]">
                                        <Clock class="h-3.5 w-3.5" />
                                        Waktu
                                    </span>
                                    <span class="text-sm text-[#1B1A18]">
                                        {{ formatDate(report.waktu_kejadian) }}
                                    </span>
                                </div>
                            </div>

                            <div class="border-b border-nav-stroke py-5">
                                <p class="mb-3 text-sm font-bold text-[#3B3A37]">
                                    KRONOLOGI KEJADIAN
                                </p>
                                <div class="rounded-lg bg-[#F6F2EE] p-4 text-sm leading-relaxed text-[#3B3A37]">
                                    {{ report.kronologi }}
                                </div>
                            </div>
                        </template>

                        <!-- Bukti Digital -->
                        <div class="border-b border-nav-stroke py-5">
                            <p class="mb-3 text-sm font-bold text-[#3B3A37]">
                                BUKTI DIGITAL ({{ report.report_evidences?.length ?? 0 }})
                            </p>
                            <div class="grid grid-cols-2 gap-3">
                                <div
                                    v-for="file in report.report_evidences"
                                    :key="file.id"
                                    class="flex items-center gap-3 rounded-lg border border-nav-stroke p-3"
                                >
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-[#1B1A18]">
                                            {{ file.original_filename }}
                                        </p>
                                        <p class="text-xs text-[#6B6862]">
                                            {{ formatFileSize(file.size) }}
                                        </p>
                                    </div>
                                    <button
                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-nav-stroke transition-colors hover:bg-surface"
                                        @click="openEvidence(file)"
                                    >
                                        <Eye class="h-4 w-4 text-[#6B6862]" />
                                    </button>
                                </div>
                                <p
                                    v-if="!report.report_evidences || report.report_evidences.length === 0"
                                    class="col-span-2 py-3 text-center text-sm text-[#908C84] italic"
                                >
                                    Tidak ada bukti digital
                                </p>
                            </div>
                        </div>

                        <!-- Tim Penanganan + Dokumen Penanganan -->
                        <template v-if="hasTeam">
                            <div class="border-b border-nav-stroke py-5">
                                <p class="mb-3 text-sm font-bold text-[#3B3A37]">
                                    TIM PENANGANAN
                                </p>
                                <div class="flex flex-col gap-3">
                                    <div
                                        v-for="(anggota, index) in report.members"
                                        :key="anggota.id"
                                        class="flex items-center gap-3"
                                    >
                                        <div
                                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                                            :class="getAvatarColor(anggota.name)"
                                        >
                                            {{ getInitials(anggota.name) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2">
                                                <p class="text-sm font-medium text-[#1B1A18]">
                                                    {{ anggota.name }}
                                                </p>
                                                <span
                                                    v-if="index === 0"
                                                    class="rounded-full bg-[#FEF3E7] px-2 py-0.5 text-[10px] font-semibold text-[#C9651A]"
                                                >
                                                    Ketua Tim
                                                </span>
                                            </div>
                                            <p class="text-xs text-[#6B6862]">
                                                {{ anggota.academic_role === 'dosen' ? 'Dosen' : 'Mahasiswa' }}
                                                · {{ anggota.department }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="py-5">
                                <p class="mb-3 text-sm font-bold text-[#3B3A37]">
                                    DOKUMEN PENANGANAN ({{ totalDocuments }} dokumen)
                                </p>
                                <div
                                    v-for="group in documentGroups"
                                    :key="group.step"
                                    class="mb-3 overflow-hidden rounded-xl border border-nav-stroke"
                                >
                                    <div class="flex items-center justify-between bg-surface px-4 py-2.5">
                                        <p class="text-sm font-semibold text-[#3B3A37]">
                                            {{ group.step }}
                                        </p>
                                        <span class="text-xs text-[#6B6862]">
                                            {{ group.docs.length }} dokumen
                                        </span>
                                    </div>
                                    <div
                                        v-for="doc in group.docs"
                                        :key="doc.id"
                                        class="flex items-center justify-between border-t border-nav-stroke px-4 py-3"
                                    >
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-[#1B1A18]">
                                                {{ getReportLabel(doc.type, doc.subtype) }}
                                            </p>
                                            <p class="text-xs text-[#6B6862]">
                                                Dibuat {{ formatDate(doc.created_at, false) }}
                                            </p>
                                        </div>
                                        <button
                                            type="button"
                                            class="eye-menu-btn flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-nav-stroke text-muted-foreground transition-colors hover:bg-surface"
                                            @click.stop="toggleViewDocMenu($event, doc)"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <DialogFooter back-label="Tutup" @back="$emit('close')" />
                </div>
            </div>
        </Transition>
    </Teleport>

    <Teleport to="body">
        <Transition name="fade-scale">
            <div
                v-if="activeViewDocMenuId"
                ref="eyeMenuPopupRef"
                class="eye-menu-popup fixed z-[100] w-64 overflow-hidden rounded-xl border bg-white shadow-xl ring-1 ring-black/5"
                :style="{
                    top: eyeMenuPosition.top,
                    left: eyeMenuPosition.left,
                }"
                @click.stop
            >
                <div class="border-b bg-gray-50 px-3 py-2 text-xs font-semibold tracking-wider text-gray-500 uppercase">
                    Buka Berkas Kasus
                </div>

                <div class="max-h-64 overflow-y-auto py-1">
                    <template
                        v-for="group in documentGroups"
                        :key="group.step"
                    >
                        <template v-for="doc in group.docs" :key="doc.id">
                            <template v-if="doc.id === activeViewDocMenuId">
                                <template
                                    v-for="att in doc.attachments || []"
                                    :key="att.id"
                                >
                                    <button
                                        v-if="att.attachment_type === 'document'"
                                        type="button"
                                        class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm transition hover:bg-gray-100"
                                        @click.stop="openAttachment(att)"
                                    >
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#E7EEF7]">
                                            <FileText class="h-4 w-4 text-[#1A5BA6]" />
                                        </div>
                                        <span class="truncate font-medium text-[#1B1A18]">
                                            {{ getReportLabel(doc.type, doc.subtype) }}
                                        </span>
                                    </button>
                                </template>

                                <template
                                    v-for="(att, idx) in doc.attachments || []"
                                    :key="`documentation-${att.id}`"
                                >
                                    <button
                                        v-if="att.attachment_type === 'documentation'"
                                        type="button"
                                        class="flex w-full items-center gap-3 border-t border-gray-100 px-4 py-2.5 text-left text-sm transition hover:bg-gray-100"
                                        @click.stop="openAttachment(att)"
                                    >
                                        <div
                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg"
                                            :class="getAttachmentIconMeta(att.mime_type).bg"
                                        >
                                            <component
                                                :is="getAttachmentIconMeta(att.mime_type).icon"
                                                class="h-4 w-4"
                                                :class="getAttachmentIconMeta(att.mime_type).color"
                                            />
                                        </div>
                                        <span
                                            class="truncate font-medium text-[#1B1A18]"
                                            :title="att.original_filename"
                                        >
                                            {{ att.original_filename || `Lampiran ${idx + 1}` }}
                                        </span>
                                    </button>
                                </template>

                                <div
                                    v-if="!doc.attachments || doc.attachments.length === 0"
                                    class="px-4 py-2.5 text-center text-sm text-gray-400 italic"
                                >
                                    Belum ada lampiran
                                </div>
                            </template>
                        </template>
                    </template>
                </div>
            </div>
        </Transition>
    </Teleport>

    <Teleport to="body">
        <div
            v-if="audioPreview"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
            @click.self="closeAudioPreview"
        >
            <div class="w-full max-w-md overflow-hidden rounded-xl border border-border bg-background shadow-2xl">
                <div class="flex items-center justify-between border-b border-border px-6 py-4">
                    <p class="truncate text-sm font-semibold">
                        {{ audioPreview.filename }}
                    </p>
                    <button
                        class="shrink-0 text-muted-foreground hover:text-foreground"
                        @click="closeAudioPreview"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <div class="px-6 py-5">
                    <audio :src="audioPreview.url" controls autoplay class="w-full" />
                </div>
            </div>
        </div>
    </Teleport>
</template>
