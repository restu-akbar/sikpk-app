<script setup lang="ts">
import { watch, ref } from 'vue';
import { today, formatDate } from '@/lib/formatDate';
import FormSectionTitle from '@/components/form/FormSectionTitle.vue';
import TextareaField from '@/components/form/TextareaField.vue';
import { useCryptoStore } from '@/lib/crypto/store';
import { handleCreate } from '@/lib/handleRequest';
import { store } from '@/routes/satgas/reports/handling/document';
import { useForm } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';
import DialogFooter from './DialogFooter.vue';
import ReportInformationSection from './handling/ReportInformationSection.vue';
import HandlingTeamSection from './handling/HandlingTeamSection.vue';
import { useDocumentEncryption } from '@/composables/useDocumentEncryption';
import BaseModal from './handling/BaseModal.vue';
import { Report } from '@/types/reports';
import { RequiredFormKeys } from '@/constants/requiredFields';
import FieldLabel from '@/components/form/FieldLabel.vue';
import { generateVindication } from '@/lib/pdf/generateVindication';

const props = defineProps<{
    open: boolean;
    report: Report;
}>();

const emit = defineEmits<{
    close: [];
    success: [];
}>();

const form = useForm({
    status: '',
    pemulihanAkan: '',
    hasilPemulihan: '',

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

const requiredFields = {
    status: 'Status kasus',
    pemulihanAkan: 'Pemulihan yang akan dilakukan',
    hasilPemulihan: 'Hasil pemulihan',
};

const textareaSections: { name: RequiredFormKeys; title: string }[] = [
    {
        name: 'pemulihanAkan',
        title: '2. PEMULIHAN NAMA BAIK YANG AKAN DILAKUKAN',
    },
    { name: 'hasilPemulihan', title: '3. HASIL PEMULIHAN NAMA BAIK' },
];

const stepErrors = ref<Record<string, string>>({});
const { getTeamPublicKeys, encryptToPayload } = useDocumentEncryption();

const handleSubmit = async () => {
    try {
        stepErrors.value = {};

        for (const [key, label] of Object.entries(requiredFields)) {
            const formKey = key as RequiredFormKeys;
            if (!form[formKey] || !form[formKey].trim()) {
                stepErrors.value[formKey] = `${label} wajib diisi.`;
            }
        }

        if (Object.keys(stepErrors.value).length > 0) return;

        const cryptoStore = useCryptoStore();
        if (!cryptoStore.privateKey) {
            throw new Error('Private key belum tersedia');
        }

        const publicKeys = await getTeamPublicKeys(props.report.members);

        const pdf = generateVindication(props.report, form);
        const pdfBlob = pdf.output('blob');
        const pdfFile = new File(
            [pdfBlob],
            'formulir-pemulihan-nama-baik.pdf',
            {
                type: 'application/pdf',
            },
        );

        const encryptedPayload = await encryptToPayload(pdfFile, publicKeys);

        form.document = [
            {
                ...encryptedPayload,
                type: props.report.progress,
                subtype: 'pemulihan_nama_baik',
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
        title="Formulir Pemulihan Nama Baik"
        description="Catatan tindakan pemulihan nama baik"
        @close="$emit('close')"
    >
        <ReportInformationSection
            :case-number="props.report.case_number"
            :report-date="formatDate(props.report.created_at, false)"
            :clarification-date="today"
            :show-jenis-kekerasan="false"
        />

        <div class="mt-4">
            <FieldLabel> Status Kasus </FieldLabel>
            <div class="mt-2 flex flex-row items-center gap-6">
                <label class="flex cursor-pointer items-center gap-2">
                    <input
                        v-model="form.status"
                        type="radio"
                        value="terbukti"
                        class="h-4 w-4"
                    />
                    <span class="text-sm">Terbukti</span>
                </label>
                <label class="flex cursor-pointer items-center gap-2">
                    <input
                        v-model="form.status"
                        type="radio"
                        value="tidakTerbukti"
                        class="h-4 w-4"
                    />
                    <span class="text-sm">Tidak Terbukti</span>
                </label>
            </div>
            <p v-if="stepErrors.status" class="mt-1 text-xs text-red-500">
                {{ stepErrors.status }}
            </p>
        </div>

        <section v-for="section in textareaSections" :key="section.name">
            <FormSectionTitle :title="section.title" />
            <TextareaField
                :name="section.name"
                v-model="form[section.name]"
                rows="5"
                :error="stepErrors[section.name]"
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
