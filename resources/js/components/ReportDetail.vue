<script setup lang="ts">
import { X, Check, Clock, MapPin } from 'lucide-vue-next';
import { formatDate } from '@/lib/formatDate';
import { getLabel } from '@/lib/getLabel';
import {
    statusCivitasOptions,
    statusOptions,
} from '@/constants/statusCivitasOptions';
import { disabilityOptions } from '@/constants/disability';
import { jenisKekerasanOptions } from '@/constants/jenisKekerasanOptions';
import FilePreview from '@/components/form/FilePreview.vue';
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
defineProps<{
    open: boolean;
    report: any | null;
}>();

defineEmits<{
    close: [];
    accept: [id: string];
    reject: [id: string];
}>();

function identitasFields(report: any) {
    return [
        { label: 'Nama', value: report.reporter?.name },
        {
            label: 'Status Pelapor',
            value: getLabel(statusOptions, report.status_pelapor),
        },
        {
            label: 'Peran Pelapor',
            value: getLabel(statusCivitasOptions, report.status_civitas),
        },
        { label: 'No. Whatsapp', value: report.whatsapp },
        { label: 'Email', value: report.reporter?.email },
        {
            label: 'Disabilitas',
            value: getLabel(disabilityOptions, report.disabilitas),
        },
        { label: 'Jurusan', value: report.reporter?.jurusan },
        { label: 'Prodi', value: report.reporter?.prodi },
    ];
}
function fileIcon(name: string) {
    if (/\.(png|jpg|jpeg|webp)$/i.test(name)) return 'image';
    if (/\.pdf$/i.test(name)) return 'file-type-pdf';
    if (/\.(m4a|mp3|wav)$/i.test(name)) return 'music';
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
                    <!-- Header -->
                    <div
                        class="relative shrink-0 border-b border-border px-6 py-5"
                    >
                        <div class="mb-2 flex items-center gap-2">
                            <span class="text-xs text-muted-foreground">{{
                                report.id
                            }}</span>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700"
                            >
                                <span
                                    class="h-1.5 w-1.5 rounded-full bg-blue-600"
                                />
                                {{ report.progress }}
                            </span>
                        </div>
                        <h2 class="text-lg font-medium">
                            {{
                                getLabel(
                                    jenisKekerasanOptions,
                                    report.jenis_kekerasan,
                                )
                            }}
                        </h2>
                        <p class="mt-0.5 text-xs text-muted-foreground">
                            Dilaporkan {{ formatDate(report.waktu_kejadian) }}
                        </p>
                        <button
                            class="absolute top-5 right-5 flex h-7 w-7 items-center justify-center rounded-lg hover:bg-muted"
                            @click="$emit('close')"
                        >
                            <X class="h-4 w-4 text-muted-foreground" />
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="flex-1 overflow-y-auto px-6">
                        <!-- Identitas Pelapor -->
                        <div class="border-b border-border py-5">
                            <p
                                class="mb-3 text-[11px] font-medium tracking-wider text-muted-foreground uppercase"
                            >
                                Identitas Pelapor
                            </p>
                            <div
                                v-for="field in identitasFields(report)"
                                :key="field.label"
                                class="flex border-b border-dashed border-border py-2 last:border-0"
                            >
                                <span
                                    class="w-36 shrink-0 text-sm text-muted-foreground"
                                    >{{ field.label }}</span
                                >
                                <span
                                    class="text-sm"
                                    :class="
                                        field.label === 'Email'
                                            ? 'text-blue-600'
                                            : ''
                                    "
                                >
                                    {{ field.value }}
                                </span>
                            </div>
                        </div>

                        <!-- Tempat & Waktu -->
                        <div class="border-b border-border py-5">
                            <p
                                class="mb-3 text-[11px] font-medium tracking-wider text-muted-foreground uppercase"
                            >
                                Tempat & Waktu Kejadian
                            </p>
                            <div
                                class="flex items-start border-b border-dashed border-border py-2"
                            >
                                <span
                                    class="flex w-36 shrink-0 items-center gap-2 text-sm text-muted-foreground"
                                >
                                    <MapPin class="h-3.5 w-3.5" /> Tempat
                                </span>
                                <span class="text-sm">{{
                                    report.tempat_kejadian
                                }}</span>
                            </div>
                            <div class="flex items-start py-2">
                                <span
                                    class="flex w-36 shrink-0 items-center gap-2 text-sm text-muted-foreground"
                                >
                                    <Clock class="h-3.5 w-3.5" /> Waktu
                                </span>
                                <span class="text-sm">{{
                                    formatDate(report.waktu_kejadian)
                                }}</span>
                            </div>
                        </div>

                        <!-- Kronologi -->
                        <div class="border-b border-border py-5">
                            <p
                                class="mb-3 text-[11px] font-medium tracking-wider text-muted-foreground uppercase"
                            >
                                Kronologi Kejadian
                            </p>
                            <div
                                class="rounded-lg bg-muted/50 p-4 text-sm leading-relaxed"
                            >
                                {{ report.kronologi }}
                            </div>
                        </div>

                        <!-- Bukti -->
                        <div class="py-5">
                            <p
                                class="mb-3 text-[11px] font-medium tracking-wider text-muted-foreground uppercase"
                            >
                                Bukti Pendukung ({{
                                    report.evidences?.length ?? 0
                                }})
                            </p>
                            <div class="grid grid-cols-2 gap-2">
                                <div
                                    v-for="file in report.evidences"
                                    :key="file.id"
                                    @click="openEvidence(file)"
                                >
                                    <FilePreview
                                        :file="{
                                            name: file.original_filename,
                                            type: file.mime_type,
                                            url: '',
                                        }"
                                        :removable="false"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="flex shrink-0 items-center justify-between border-t border-border px-6 py-4"
                    >
                        <button
                            class="h-9 rounded-lg border border-border px-4 text-sm hover:bg-muted"
                            @click="$emit('close')"
                        >
                            Tutup
                        </button>
                        <div class="flex gap-2">
                            <button
                                class="inline-flex h-9 items-center gap-1.5 rounded-lg border px-4 text-sm font-medium transition-colors"
                                :class="
                                    report.progress === 'Laporan Baru'
                                        ? 'border-red-300 bg-red-50 text-red-700 hover:bg-red-100'
                                        : 'cursor-not-allowed border-gray-200 bg-gray-50 text-gray-400'
                                "
                                :disabled="report.progress !== 'Laporan Baru'"
                                @click="$emit('reject', report.id)"
                            >
                                <X class="h-3.5 w-3.5" /> Tolak Laporan
                            </button>
                            <button
                                class="inline-flex h-9 items-center gap-1.5 rounded-lg px-4 text-sm font-medium text-white transition-colors"
                                :class="
                                    report.progress === 'Laporan Baru'
                                        ? 'bg-orange-500 hover:bg-orange-600'
                                        : 'cursor-not-allowed bg-gray-300 text-gray-500'
                                "
                                :disabled="report.progress !== 'Laporan Baru'"
                                @click="$emit('accept', report.id)"
                            >
                                <Check class="h-3.5 w-3.5" /> Terima Laporan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
