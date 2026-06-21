<script setup lang="ts">
import { computed, watch, ref } from 'vue';
import { today, formatDate } from '@/lib/formatDate';
import FormSectionTitle from '@/components/form/FormSectionTitle.vue';
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
import { pelaporRequiredFields } from '@/constants/requiredFields';
import BaseModal from './handling/BaseModal.vue';
import { Report } from '@/types/reports';
import IdentitySection from './handling/IdentitySection.vue';
import { generateReporterInspection } from '@/lib/pdf/generateReporterInspection';
import { generateSuspectInspection } from '@/lib/pdf/generateSuspectInspection';
import { statusTerlaporOptions } from '@/constants/statusCivitasOptions';

const props = defineProps<{
    open: boolean;
    report: Report;
}>();

const emit = defineEmits<{
    close: [];
    success: [];
}>();

const existingTerlapor = props.report.terlapor ?? null;

const form = useForm({
    terlapor: {
        jenisKekerasan: props.report.jenis_kekerasan ?? '',
        nama: existingTerlapor?.nama ?? props.report.nama_terlapor ?? '',
        status: existingTerlapor?.jenis_kelamin ?? '',
        civitas: existingTerlapor?.peran_akademik ?? props.report.status_terlapor ?? '',
        nomorIdentitas: existingTerlapor?.nomor_identitas ?? '',
        whatsapp: existingTerlapor?.nomor_wa ?? '',
        jurusan: existingTerlapor?.jurusan ?? '',
        prodi: existingTerlapor?.prodi ?? '',
        angkatan: existingTerlapor?.angkatan ?? '',
        domisili: '',
        kontakLain: '',
    },

    kronologi: '',
    pihakDihubungi: '',
    pihakLain: '',
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
const terlaporErrors = computed(() => getErrorsFor('terlapor.'));
const stepErrors = ref<Record<string, string>>({});

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

        const showJurusanTerlapor = ['mahasiswa', 'dosen'].includes(
            form.terlapor.civitas,
        );
        const showProdiTerlapor = form.terlapor.civitas === 'mahasiswa';

        for (const [key, label] of Object.entries(pelaporRequiredFields)) {
            if (key === 'jurusan' && !showJurusanTerlapor) continue;
            if (key === 'prodi' && !showProdiTerlapor) continue;

            const val = form.terlapor[key as keyof typeof form.terlapor];
            if (checkIsEmpty(val)) {
                stepErrors.value[`terlapor.${key}`] = `${label} wajib diisi.`;
            }
        }

        if (Object.keys(stepErrors.value).length > 0) return;

        const cryptoStore = useCryptoStore();
        if (!cryptoStore.privateKey)
            throw new Error('Private key belum tersedia');

        const publicKeys = await getTeamPublicKeys(props.report.members);
        const pdf = generateSuspectInspection(props.report, form);
        const pdfBlob = pdf.output('blob');
        const pdfFile = new File([pdfBlob], 'laporan-penanganan-terlapor.pdf', {
            type: 'application/pdf',
        });

        const encryptedPayload = await encryptToPayload(pdfFile, publicKeys);

        form.document = [
            {
                ...encryptedPayload,
                type: props.report.progress,
                subtype: 'periksa_terlapor',
                attachment_type: 'document',
            },
        ];
        console.log(form);

        handleCreate(form, store(props.report.id), {
            onSuccess: () => emit('success'),
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

        for (const key of Object.keys(pelaporRequiredFields)) {
            const val =
                newValue.terlapor[key as keyof typeof newValue.terlapor];
            if (checkIsValid(val)) delete stepErrors.value[`terlapor.${key}`];
        }
    },
    { deep: true },
);
</script>

<template>
    <BaseModal
        :open="open"
        title="Formulir Pemeriksaan Terlapor"
        description="Pemeriksaan formal pihak yang dilaporkan."
        @close="$emit('close')"
    >
        <ReportInformationSection
            v-model:jenis-kekerasan="form.terlapor.jenisKekerasan"
            :case-number="props.report.case_number"
            :report-date="formatDate(props.report.created_at, false)"
            :clarification-date="today"
            :jenis-kekerasan-options="jenisKekerasanOptions"
            :error="stepErrors.jenisKekerasan"
        />

        <IdentitySection
            v-model:form="form.terlapor"
            :step-errors="terlaporErrors"
            :is-additional="true"
            :is-gender="true"
            :show-nomor-identitas="true"
            :show-angkatan="true"
            :step="2"
            subject="Terlapor"
            :civitas-options="statusTerlaporOptions"
        />
        <section>
            <FormSectionTitle title="3. KRONOLOGI PERISTIWA VERSI TERLAPOR" />
            <TextareaField
                v-model="form.kronologi"
                label="Catatan hasil kronologi versi terlapor"
                placeholder="Tuliskan kronologi peristiwa dari sudut pandang terlapor.."
                rows="5"
                :error="stepErrors.kronologi"
                required
            />
        </section>
        <section>
            <FormSectionTitle title="4. PIHAK YANG TELAH DIHUBUNGI" />
            <TextareaField
                v-model="form.pihakDihubungi"
                label="Tuliskan pihak lain yang sudah dihubungi"
                placeholder="Sebutkan pihak yang telah dihubungi sebelum sesi ini.."
                rows="5"
                :error="stepErrors.pihakDihubungi"
                required
            />
        </section>
        <section>
            <FormSectionTitle
                title="5. KEMUNGKINAN KERJA SAMA DENGAN PIHAK LAIN"
            />
            <TextareaField
                v-model="form.pihakLain"
                label="Tuliskan kemungkinan kerja sama dengan pihak lain"
                placeholder="Sebutkan kemungkinan kerjasama dengan.."
                rows="5"
                :error="stepErrors.pihakLain"
                required
            />
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
