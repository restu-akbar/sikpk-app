<script setup lang="ts">
import { watch, ref, nextTick } from 'vue';
import { today, formatDate } from '@/lib/formatDate';
import FormSectionTitle from '@/components/form/FormSectionTitle.vue';
import FieldReadonly from '@/components/form/FieldReadonly.vue';
import DropdownField from '@/components/form/DropdownField.vue';
import FormField from '@/components/form/FormField.vue';
import FieldLabel from '@/components/form/FieldLabel.vue';
import TextareaField from '@/components/form/TextareaField.vue';
import { jenisKekerasanOptions } from '@/constants/jenisKekerasanOptions';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useCryptoStore } from '@/lib/crypto/store';
import { getPublicKeys } from '@/lib/crypto/getPublicKeys';
import { reEncryptEdeks } from '@/lib/crypto/re-encrypt-edeks';
import { encryptFile } from '@/lib/mediaCrypto';
import { generateClarifyReport } from '@/lib/pdf/generateClarifyReport';
import { handleCreate } from '@/lib/handleRequest';
import { store } from '@/routes/satgas/reports/evidence';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
    open: boolean;
    report: any;
}>();

const emit = defineEmits<{
    close: [];
}>();

const form = useForm({
    jenisKekerasan: props.report.jenis_kekerasan,
    nama: props.report.reporter.name,
    statusPelapor: props.report.status_pelapor,
    statusCivitas: props.report.reporter.status_civitas,
    whatsapp: props.report.reporter.whatsapp,
    jurusan: props.report.reporter.jurusan,
    prodi: props.report.reporter.prodi,
    catatanKlarifikasi: '',

    bukti: {
        file: null,
        filename: '',
        mimeType: '',
        size: 0,
        edeks: {},
    },
});
const stepErrors = ref<Record<string, string>>({});
const handleSubmit = async () => {
    try {
        stepErrors.value = {};

        if (!form.catatanKlarifikasi.trim()) {
            stepErrors.value.catatanKlarifikasi =
                'Catatan klarifikasi wajib diisi.';
        }

        if (Object.keys(stepErrors.value).length > 0) return;

        const cryptoStore = useCryptoStore();
        if (!cryptoStore.privateKey) {
            throw new Error('Private key belum tersedia');
        }

        const memberIds = props.report.members.map((m: any) => m.id);
        const publicKeys = await getPublicKeys(memberIds);

        const pdf = generateClarifyReport(props.report, form);
        const pdfBlob = pdf.output('blob');

        const pdfFile = new File([pdfBlob], 'laporan-klarifikasi.pdf', {
            type: 'application/pdf',
        });

        const encryptedPDF = await encryptFile(pdfFile, publicKeys);

        form.bukti = [
            {
                file: encryptedPDF.encryptedData,
                filename: encryptedPDF.filename,
                mime_type: encryptedPDF.mimeType,
                size: encryptedPDF.size,
                edeks: JSON.stringify(encryptedPDF.edeks),
            },
        ];

        handleCreate(form, store(props.report.id), {
            onSuccess: async () => {
                emit('success');
            },
            onError: () => {
                console.log('error');
            },
        });
    } catch (error) {
        console.error(error);
        stepErrors.value.general = 'Terjadi kesalahan tidak terduga.';
    }
};

function handleClose() {
    emit('close');
}

watch(
    () => props.open,
    (value) => {
        if (value) document.body.style.overflow = 'hidden';
        else document.body.style.overflow = '';
    },
);
</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="open"
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/40 p-6"
                @click.self="$emit('close')"
            >
                <div
                    class="flex h-[95vh] w-[75vw] flex-col overflow-hidden rounded-xl border border-border bg-background"
                >
                    <!-- Header -->
                    <div
                        class="flex items-start justify-between border-b border-gray-200 px-6 py-5"
                    >
                        <div>
                            <h2 class="text-base font-semibold text-gray-900">
                                Notulensi Klarifikasi Pelapor
                            </h2>
                            <p class="mt-0.5 text-sm text-gray-500">
                                Dokumentasikan hasil sesi klarifikasi awal
                                dengan pelapor. Sesuaikan informasi pelapor jika
                                yang melapor bukan korban.
                            </p>
                        </div>
                        <button
                            class="mt-0.5 ml-4 text-gray-400 hover:text-gray-600"
                            @click="$emit('close')"
                            aria-label="Tutup"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="flex-1 space-y-6 overflow-y-auto px-6 py-4">
                        <!-- Seksi 1: Informasi Laporan -->
                        <section>
                            <FormSectionTitle title="1. INFORMASI LAPORAN" />
                            <div class="mb-4 grid grid-cols-3 gap-4">
                                <FieldReadonly
                                    label="No. Laporan"
                                    :value="props.report.case_number"
                                />

                                <FieldReadonly
                                    label="Tanggal Melapor"
                                    :value="
                                        formatDate(
                                            props.report.created_at,
                                            false,
                                        )
                                    "
                                />

                                <FieldReadonly
                                    label="Tanggal Klarifikasi (Hari Ini)"
                                    :value="today"
                                />
                            </div>
                            <DropdownField
                                name="jenisKekerasan"
                                v-model="form.jenisKekerasan"
                                label="Jenis dugaan kekerasan"
                                placeholder="Pilih jenis dugaan kekerasan..."
                                :options="jenisKekerasanOptions"
                                required
                            />
                        </section>

                        <!-- Seksi 2: Identitas Pelapor -->
                        <section>
                            <FormSectionTitle title="2. IDENTITAS PELAPOR" />
                            <div class="mb-4 grid grid-cols-2 gap-4">
                                <FormField
                                    name="nama"
                                    v-model="form.nama"
                                    label="Nama lengkap pelapor"
                                    :error="stepErrors.nama"
                                    required
                                />
                                <div>
                                    <FieldLabel required>
                                        Status Pelapor
                                    </FieldLabel>

                                    <div
                                        class="mt-2 flex h-10 items-center gap-6 rounded-md"
                                    >
                                        <label
                                            class="flex cursor-pointer items-center gap-2"
                                        >
                                            <input
                                                v-model="form.statusPelapor"
                                                type="radio"
                                                value="korban"
                                                class="h-4 w-4"
                                            />
                                            <span class="text-sm">Korban</span>
                                        </label>

                                        <label
                                            class="flex cursor-pointer items-center gap-2"
                                        >
                                            <input
                                                v-model="form.statusPelapor"
                                                type="radio"
                                                value="saksi"
                                                class="h-4 w-4"
                                            />
                                            <span class="text-sm">Saksi</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 grid grid-cols-2 gap-4">
                                <FormField
                                    name="civitas"
                                    v-model="form.statusCivitas"
                                    label="Peran Pelapor"
                                    required
                                />
                                <FormField
                                    name="whatsapp"
                                    v-model="form.whatsapp"
                                    label="Nomor WhatsApp Aktif"
                                    :error="stepErrors.whatsapp"
                                    required
                                />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <FormField
                                    name="jurusan"
                                    v-model="form.jurusan"
                                    label="Jurusan"
                                    :error="stepErrors.jurusan"
                                    required
                                />
                                <FormField
                                    name="prodi"
                                    v-model="form.prodi"
                                    label="Prodi"
                                    :error="stepErrors.prodi"
                                    required
                                />
                            </div>
                        </section>

                        <!-- Seksi 3: Notulensi -->
                        <section>
                            <FormSectionTitle
                                title="3. NOTULENSI KLARIFIKASI"
                            />
                            <TextareaField
                                name="catatanKlarifikasi"
                                v-model="form.catatanKlarifikasi"
                                label="Catatan hasil sesi klarifikasi "
                                placeholder="Tuliskan ringkasan klarifikasi: poin yang disampaikan pelapor, klarifikasi terkait kronologi, hal yang perlu ditindaklanjuti…"
                                rows="5"
                                :error="stepErrors.catatanKlarifikasi"
                                required
                            />
                            <p class="mt-1.5 text-xs text-gray-400">
                                Notulensi akan dijadikan dasar bagi tim untuk
                                menentukan apakah laporan dapat dilanjutkan ke
                                tahap pemeriksaan.
                            </p>
                        </section>

                        <!-- Tim Penanganan -->
                        <section>
                            <FormSectionTitle title="TIM PENANGANAN" />
                            <div
                                class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3"
                            >
                                <p class="mb-3 text-xs text-gray-400">
                                    {{ props.report.no_tim }}
                                </p>

                                <div class="flex flex-wrap gap-3">
                                    <div
                                        v-for="(anggota, i) in props.report
                                            .members"
                                        :key="i"
                                        class="flex items-center gap-2 rounded-full bg-white px-3 py-2"
                                    >
                                        <Avatar class="h-8 w-8">
                                            <AvatarFallback>
                                                {{ anggota.initials }}
                                            </AvatarFallback>
                                        </Avatar>

                                        <span class="text-sm font-medium">
                                            {{ anggota.name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <!-- Footer -->
                    <div
                        class="flex items-center justify-between border-t border-gray-200 px-6 py-2.5"
                    >
                        <button
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm transition hover:bg-gray-50"
                            @click="$emit('close')"
                        >
                            Batal
                        </button>
                        <button
                            class="flex items-center gap-2 rounded-lg bg-orange-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-orange-600"
                            @click="handleSubmit"
                        >
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                            Simpan & Buat PDF
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
