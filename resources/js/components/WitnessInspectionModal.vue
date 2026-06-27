<script setup lang="ts">
import { watch, ref } from 'vue';
import { today, formatDate } from '@/lib/formatDate';
import FormSectionTitle from '@/components/form/FormSectionTitle.vue';
import TextareaField from '@/components/form/TextareaField.vue';
import { jenisKekerasanOptions } from '@/constants/jenisKekerasanOptions';
import { useCryptoStore } from '@/lib/crypto/store';
import { getPublicKeys } from '@/lib/crypto/getPublicKeys';
import { encryptFile } from '@/lib/mediaCrypto';
import { generateWitnessReport } from '@/lib/pdf/generateWitnessInspection';
import { handleCreate } from '@/lib/handleRequest';
import { store } from '@/routes/satgas/reports/handling/document';
import { useForm } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';
import DialogFooter from './DialogFooter.vue';
import ModalHeaderSection from './ModalHeaderSection.vue';
import ReportInformationSection from './handling/ReportInformationSection.vue';
import HandlingTeamSection from './handling/HandlingTeamSection.vue';
import FormField from './form/FormField.vue';
import ErrorField from './form/ErrorField.vue';
import FieldLabel from './form/FieldLabel.vue';
import { Report } from '@/types/reports';

const props = defineProps<{
    open: boolean;
    report: Report;
}>();

const emit = defineEmits<{
    close: [];
    success: [];
}>();

const form = useForm({
    jenisKekerasan: props.report.jenis_kekerasan ?? '',
    nama: '',
    statusPelapor: '',
    whatsapp: '',
    relasi: '',
    catatanKlarifikasi: '',

    document: [
        {
            file: null as File | null,
            filename: '',
            mimeType: '',
            size: 0,
            edeks: {},
            type: '',
            subtype: '',
        },
    ],
});
const stepErrors = ref<Record<string, string>>({});
const handleSubmit = async () => {
    try {
        stepErrors.value = {};

        if (!form.jenisKekerasan.trim()) {
            stepErrors.value.jenisKekerasan = 'Jenis kekerasan wajib dipilih.';
        }

        if (!form.nama.trim()) {
            stepErrors.value.nama = 'Nama wajib diisi.';
        }

        if (!form.statusPelapor.trim()) {
            stepErrors.value.statusPelapor = 'Relasi saksi wajib diisi.';
        }

        if (!form.relasi.trim()) {
            stepErrors.value.relasi =
                'Relasi akademik / profesional wajib diisi.';
        }

        if (!form.whatsapp.trim()) {
            stepErrors.value.whatsapp = 'Nomor WhatsApp wajib diisi.';
        }

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

        const pdf = generateWitnessReport(props.report, form);
        const pdfBlob = pdf.output('blob');

        const pdfFile = new File([pdfBlob], 'pemeriksaan-saksi.pdf', {
            type: 'application/pdf',
        });

        const encryptedPDF = await encryptFile(pdfFile, publicKeys);

        form.document = [
            {
                file: encryptedPDF.encryptedData,
                filename: encryptedPDF.filename,
                mime_type: encryptedPDF.mimeType,
                size: encryptedPDF.size,
                edeks: JSON.stringify(encryptedPDF.edeks),
                type: props.report.progress,
                subtype: 'periksa_saksi',
                attachment_type: 'document',
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

watch(
    () => props.open,
    (value) => {
        if (value) document.body.style.overflow = 'hidden';
        else document.body.style.overflow = '';
    },
);
watch(
    form,
    (newValue) => {
        if (newValue.jenisKekerasan.trim())
            delete stepErrors.value.jenisKekerasan;
        if (newValue.nama.trim()) delete stepErrors.value.nama;
        if (newValue.statusPelapor.trim())
            delete stepErrors.value.statusPelapor;
        if (newValue.relasi.trim()) delete stepErrors.value.statusCivitas;
        if (newValue.whatsapp.trim()) delete stepErrors.value.whatsapp;
        if (newValue.catatanKlarifikasi.trim())
            delete stepErrors.value.catatanKlarifikasi;
    },
    { deep: true }, // Wajib menggunakan deep: true karena memantau object (form)
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
                    <ModalHeaderSection
                        title="Notulensi Klarifikasi Saksi"
                        description="Dokumentasikan hasil sesi klarifikasi awal dengan pelapor. Sesuaikan informasi pelapor jika yang melapor bukan korban."
                        @close="emit('close')"
                    />

                    <!-- Body -->
                    <div class="flex-1 space-y-6 overflow-y-auto px-6 py-4">
                        <!-- Seksi 1: Informasi Laporan -->
                        <ReportInformationSection
                            v-model:jenis-kekerasan="form.jenisKekerasan"
                            :case-number="props.report.case_number"
                            :report-date="
                                formatDate(props.report.created_at, false)
                            "
                            :clarification-date="today"
                            :jenis-kekerasan-options="jenisKekerasanOptions"
                            :error="stepErrors.jenisKekerasan"
                        />
                        <!-- Seksi 2: Identitas Pelapor -->
                        <Section>
                            <FormSectionTitle title="2. Identitas Saksi" />
                            <div class="mb-4 grid grid-cols-2 gap-4">
                                <FormField
                                    name="nama"
                                    v-model="form.nama"
                                    label="Nama lengkap saksi"
                                    :error="stepErrors.nama"
                                    required
                                />
                                <FormField
                                    name="whatsapp"
                                    v-model="form.whatsapp"
                                    label="No. whatsapp aktif"
                                    :error="stepErrors.whatsapp"
                                    required
                                />
                            </div>
                            <div class="mb-4 grid grid-cols-2 gap-4">
                                <div>
                                    <FieldLabel required>
                                        Relasi saksi
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

                                    <ErrorField
                                        :error="stepErrors.statusPelapor"
                                    />
                                </div>
                                <FormField
                                    name="relasi"
                                    v-model="form.relasi"
                                    label="Relasi Akademik / Profesional"
                                    :error="stepErrors.relasi"
                                    required
                                />
                            </div>
                        </Section>
                        <!-- Seksi 3: Notulensi -->
                        <section>
                            <FormSectionTitle title="3. Kesaksian Saksi" />
                            <TextareaField
                                name="catatanKlarifikasi"
                                v-model="form.catatanKlarifikasi"
                                label="Catatan hasil kesaksian saksi"
                                placeholder="Tuliskan keterangan yang diberikan saksi terkait peristiwa…"
                                rows="5"
                                :error="stepErrors.catatanKlarifikasi"
                                required
                            />
                        </section>

                        <!-- Tim Penanganan -->
                        <HandlingTeamSection
                            :team-number="props.report.team_number"
                            :members="props.report.members"
                        />
                    </div>

                    <!-- Footer -->
                    <DialogFooter
                        back-label="Batal"
                        action-label="Simpan & Buat PDF"
                        :action-icon="Check"
                        :action-disabled="form.processing"
                        @back="$emit('close')"
                        @action="handleSubmit"
                    />
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
