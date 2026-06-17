<script setup lang="ts">
import { watch, ref } from 'vue';
import { today, formatDate } from '@/lib/formatDate';
import FormSectionTitle from '@/components/form/FormSectionTitle.vue';
import TextareaField from '@/components/form/TextareaField.vue';
import { jenisKekerasanOptions } from '@/constants/jenisKekerasanOptions';
import { useCryptoStore } from '@/lib/crypto/store';
import { generateClarifyReport } from '@/lib/pdf/generateClarifyReport';
import { handleCreate } from '@/lib/handleRequest';
import { store } from '@/routes/satgas/reports/handling/document';
import { useForm } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';
import DialogFooter from './DialogFooter.vue';
import ReportInformationSection from './handling/ReportInformationSection.vue';
import ReporterIdentitySection from './handling/ReporterIdentitySection.vue';
import HandlingTeamSection from './handling/HandlingTeamSection.vue';
import { useDocumentEncryption } from '@/composables/useDocumentEncryption';
import { requiredFields, RequiredFormKeys } from '@/constants/requiredFields';
import BaseModal from './handling/BaseModal.vue';
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
    jenisKekerasan: '',
    nama: '',
    statusPelapor: '',
    statusCivitas: '',
    whatsapp: '',
    jurusan: '',
    prodi: '',
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
            attachment_type: '',
        },
    ],
});
const stepErrors = ref<Record<string, string>>({});
const { getTeamPublicKeys, encryptToPayload } = useDocumentEncryption();
const handleSubmit = async () => {
    try {
        stepErrors.value = {};

        for (const [key, label] of Object.entries(requiredFields)) {
            const formKey = key as RequiredFormKeys;

            if (!form[formKey].trim()) {
                stepErrors.value[formKey] = `${label} wajib diisi.`;
            }
        }

        if (Object.keys(stepErrors.value).length > 0) return;

        const cryptoStore = useCryptoStore();
        if (!cryptoStore.privateKey) {
            throw new Error('Private key belum tersedia');
        }

        const publicKeys = await getTeamPublicKeys(props.report.members);

        const pdf = generateClarifyReport(props.report, form);
        const pdfBlob = pdf.output('blob');
        const pdfFile = new File([pdfBlob], 'laporan-klarifikasi.pdf', {
            type: 'application/pdf',
        });

        const encryptedPayload = await encryptToPayload(pdfFile, publicKeys);

        form.document = [
            {
                ...encryptedPayload,
                type: props.report.progress,
                subtype: 'notulensi',
                attachment_type: 'document',
            },
        ];

        handleCreate(form, store(props.report.id), {
            onSuccess: () => emit('success'),
            onError: () => console.log('error'),
        });
    } catch (error) {
        console.error(error);
        stepErrors.value.general =
            error instanceof Error
                ? error.message
                : 'Terjadi kesalahan tidak terduga.';
    }
};

watch(
    form,
    (newValue) => {
        for (const key in requiredFields) {
            const formKey = key as RequiredFormKeys;

            if (newValue[formKey] && newValue[formKey].trim()) {
                delete stepErrors.value[formKey];
            }
        }
    },
    { deep: true },
);
</script>

<template>
    <BaseModal
        :open="open"
        title="Notulensi Klarifikasi Pelapor"
        description="Dokumentasikan hasil sesi klarifikasi awal dengan pelapor. Sesuaikan informasi pelapor jika yang melapor bukan korban."
        @close="$emit('close')"
    >
        <ReportInformationSection
            v-model:jenis-kekerasan="form.jenisKekerasan"
            :case-number="props.report.case_number"
            :report-date="formatDate(props.report.created_at, false)"
            :clarification-date="today"
            :jenis-kekerasan-options="jenisKekerasanOptions"
            :error="stepErrors.jenisKekerasan"
        />

        <ReporterIdentitySection
            v-model:form="form"
            :step-errors="stepErrors"
        />

        <section>
            <FormSectionTitle title="3. NOTULENSI KLARIFIKASI" />
            <TextareaField
                name="catatanKlarifikasi"
                v-model="form.catatanKlarifikasi"
                label="Catatan hasil sesi klarifikasi "
                placeholder="Tuliskan ringkasan klarifikasi..."
                rows="5"
                :error="stepErrors.catatanKlarifikasi"
                required
            />
            <p class="mt-1.5 text-xs text-gray-400">
                Notulensi akan dijadikan dasar bagi tim untuk menentukan apakah
                laporan dapat dilanjutkan ke tahap pemeriksaan.
            </p>
        </section>

        <HandlingTeamSection
            :team-number="props.report.team_number"
            :members="props.report.members"
        />

        <template #footer>
            <DialogFooter
                back-label="Batal"
                action-label="Simpan & Buat PDF"
                :action-icon="Check"
                :action-disabled="form.processing"
                @back="$emit('close')"
                @action="handleSubmit"
            />
        </template>
    </BaseModal>
</template>
