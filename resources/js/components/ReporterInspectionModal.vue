<script setup lang="ts">
import { computed, watch, ref } from 'vue';
import { today, formatDate } from '@/lib/formatDate';
import FormSectionTitle from '@/components/form/FormSectionTitle.vue';
import MultiSelect from '@/components/form/MultiSelect.vue';
import TextareaField from '@/components/form/TextareaField.vue';
import { jenisKekerasanOptions } from '@/constants/jenisKekerasanOptions';
import { useCryptoStore } from '@/lib/crypto/store';
import { handleCreate } from '@/lib/handleRequest';
import { store } from '@/routes/satgas/reports/handling/document';
import { useForm } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';
import DialogFooter from './DialogFooter.vue';
import ReportInformationSection from './handling/ReportInformationSection.vue';
import HandlingTeamSection from './handling/HandlingTeamSection.vue';
import { useDocumentEncryption } from '@/composables/useDocumentEncryption';
import {
    pelaporRequiredFields,
    rootRequiredFields,
} from '@/constants/requiredFields';
import BaseModal from './handling/BaseModal.vue';
import { Report } from '@/types/reports';
import IdentitySection from './handling/IdentitySection.vue';
import { generateReporterInspection } from '@/lib/pdf/generateReporterInspection';
import { statusTerlaporOptions } from '@/constants/statusCivitasOptions';
import EvidenceSection from './EvidenceSection.vue';
import { useReportSubmission } from '@/composables/useReportSubmission';

const props = defineProps<{
    open: boolean;
    report: Report;
}>();

const emit = defineEmits<{
    close: [];
    success: [];
}>();

const existingKorban = props.report.korban ?? null;
const existingTerlapor = props.report.terlapor ?? null;

const form = useForm({
    jenisKekerasan: props.report.jenis_kekerasan ?? '',

    terlapor: {
        nama: existingTerlapor?.nama ?? props.report.nama_terlapor ?? '',
        status: existingTerlapor?.jenis_kelamin ?? '',
        civitas:
            existingTerlapor?.peran_akademik ??
            props.report.status_terlapor ??
            '',
        jurusan: existingTerlapor?.jurusan ?? '',
        prodi: existingTerlapor?.prodi ?? '',
    },

    korban: {
        nama: existingKorban?.nama ?? '',
        status: existingKorban?.jenis_kelamin ?? '',
        civitas: existingKorban?.peran_akademik ?? '',
        nomorIdentitas: existingKorban?.nomor_identitas ?? '',
        whatsapp: existingKorban?.nomor_wa ?? '',
        jurusan: existingKorban?.jurusan ?? '',
        prodi: existingKorban?.prodi ?? '',
        angkatan: existingKorban?.angkatan ?? '',
        domisili: '',
        kontakLain: '',
    },
    kronologi: '',
    ciriFisik: '',

    alasan: [] as string[],
    alasanLainnya: '',

    kebutuhanKorban: [] as string[],
    kebutuhanKorbanLainnya: '',

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
const bukti = ref<File[]>([]);
const { processAndUploadFiles } = useReportSubmission(
    props.report.id,
    props.report.members,
);
const terlaporErrors = computed(() => getErrorsFor('terlapor.'));
const alasanOptions = [
    { label: 'Mencari keadilan', value: 'keadilan' },
    { label: 'Mencegah keberulangan', value: 'keberulangan' },
    { label: 'Pemulihan trauma', value: 'trauma' },
    { label: 'Sanksi terhadap pelaku', value: 'sanksi' },
    { label: 'Pengembalian nama baik', value: 'nama_baik' },
    { label: '+Lainnya', value: 'lainnya' },
];
const kebutuhanKorbanOptions = [
    { label: 'Pendampingan psikologis', value: 'keadilan' },
    { label: 'Pendampingan hukum', value: 'keberulangan' },
    { label: 'Pemulihan akademik', value: 'trauma' },
    { label: 'Perlindungan keamanan', value: 'sanksi' },
    { label: 'Pemulihan nama baik', value: 'nama_baik' },
    { label: 'Mediasi', value: 'nama_baik' },
    { label: '+Lainnya', value: 'lainnya' },
];

const stepErrors = ref<Record<string, string>>({});
const korbanErrors = computed(() => getErrorsFor('korban.'));
const getErrorsFor = (prefix: string): Record<string, string> => {
    const sectionErrors: Record<string, string> = {};
    for (const key in stepErrors.value) {
        if (key.startsWith(prefix)) {
            const rawKey = key.slice(prefix.length);
            sectionErrors[rawKey] = stepErrors.value[key];
        }
    }
    return sectionErrors;
};
const { getTeamPublicKeys, encryptToPayload } = useDocumentEncryption();
const handleSubmit = async () => {
    try {
        stepErrors.value = {};

        const checkIsEmpty = (val: any) => {
            if (typeof val === 'string') return val.trim() === '';
            if (Array.isArray(val)) return val.length === 0;
            return !val;
        };

        for (const [key, label] of Object.entries(rootRequiredFields)) {
            const val = form[key];
            if (checkIsEmpty(val)) {
                stepErrors.value[key] = `${label} wajib diisi.`;
            }
        }

        const showJurusanKorban = ['mahasiswa', 'dosen'].includes(
            form.korban.civitas,
        );
        const showProdiKorban = form.korban.civitas === 'mahasiswa';

        for (const [key, label] of Object.entries(pelaporRequiredFields)) {
            if (key === 'jurusan' && !showJurusanKorban) continue;
            if (key === 'prodi' && !showProdiKorban) continue;

            const val = form.korban[key as keyof typeof form.korban];
            if (checkIsEmpty(val)) {
                stepErrors.value[`korban.${key}`] = `${label} wajib diisi.`;
            }
        }

        if (Object.keys(stepErrors.value).length > 0) return;

        const cryptoStore = useCryptoStore();
        if (!cryptoStore.privateKey)
            throw new Error('Private key belum tersedia');

        const publicKeys = await getTeamPublicKeys(props.report.members);
        const pdf = generateReporterInspection(props.report, form);
        const pdfBlob = pdf.output('blob');
        const pdfFile = new File([pdfBlob], 'laporan-klarifikasi.pdf', {
            type: 'application/pdf',
        });

        const encryptedPayload = await encryptToPayload(pdfFile, publicKeys);

        form.document = [
            {
                ...encryptedPayload,
                type: props.report.progress,
                subtype: 'periksa_pelapor',
                attachment_type: 'document',
            },
        ];
        console.log(form);

        handleCreate(form, store(props.report.id), {
            onSuccess: async () => {
                if (bukti.value.length > 0) {
                    await processAndUploadFiles(
                        bukti.value,
                        'periksa_pelapor',
                    );
                }

                emit('success');
            },
            onError: () => console.error('error'),
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
        const checkIsValid = (val: any) => {
            if (typeof val === 'string') return val.trim() !== '';
            if (Array.isArray(val)) return val.length > 0;
            return !!val;
        };

        for (const key of Object.keys(rootRequiredFields)) {
            const val = newValue[key];
            if (checkIsValid(val)) delete stepErrors.value[key];
        }

        for (const key of Object.keys(pelaporRequiredFields)) {
            const val = newValue.korban[key as keyof typeof newValue.korban];
            if (checkIsValid(val)) delete stepErrors.value[`korban.${key}`];
        }
    },
    { deep: true },
);
</script>

<template>
    <BaseModal
        :open="open"
        title="Formulir Pemeriksaan Pelapor"
        description="Dokumentasikan hasil pemeriksaan terhadap pelapor, termasuk identitas korban dan terlapor."
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

        <IdentitySection
            v-model:form="form.korban"
            :step-errors="korbanErrors"
            :is-additional="true"
            :is-gender="true"
            :show-nomor-identitas="true"
            :show-angkatan="true"
            :step="2"
            subject="Korban"
        />

        <IdentitySection
            v-model:form="form.terlapor"
            :step-errors="terlaporErrors"
            :show-whatsapp="false"
            :is-gender="true"
            :step="3"
            subject="Terlapor"
            :is-optional="true"
            :civitas-options="statusTerlaporOptions"
        />

        <section>
            <FormSectionTitle title="4. KRONOLOGI PERISTIWA" />
            <TextareaField
                v-model="form.kronologi"
                label="Catatan hasil kronologi kejadian"
                placeholder="Tuliskan kronologi kejadian..."
                rows="5"
                :error="stepErrors.kronologi"
                required
            />
        </section>
        <section>
            <FormSectionTitle title="5. CIRI FISIK PADA SAAT KEJADIAN" />
            <TextareaField
                v-model="form.ciriFisik"
                label="Catatan ciri fisik pelaku"
                placeholder="Pakaian, ciri tubuh, aksesoris, dll yang dapat membantu identifikasi pelaku…"
                rows="5"
                :error="stepErrors.ciriFisik"
                required
            />
        </section>
        <section>
            <FormSectionTitle title="6. ALASAN PENGADUAN" />

            <MultiSelect
                v-model="form.alasan"
                v-model:otherText="form.alasanLainnya"
                :options="alasanOptions"
                :error="stepErrors.alasan"
                :otherInputError="stepErrors.alasanLainnya"
                otherOptionValue="lainnya"
                otherInputLabel="Tuliskan alasan lainnya"
            />
        </section>
        <section>
            <FormSectionTitle title="7. KEBUTUHAN KORBAN" />

            <MultiSelect
                v-model="form.kebutuhanKorban"
                v-model:otherText="form.kebutuhanKorbanLainnya"
                :options="kebutuhanKorbanOptions"
                :error="stepErrors.kebutuhanKorban"
                :otherInputError="stepErrors.kebutuhanKorbanLainnya"
                otherOptionValue="lainnya"
                otherInputLabel="Tuliskan kebutuhan korban lainnya"
            />
        </section>
        <section>
            <EvidenceSection v-model="bukti" />
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
