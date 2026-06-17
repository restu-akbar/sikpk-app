<script setup lang="ts">
import { watch, ref } from 'vue';
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
import BaseModal from './handling/BaseModal.vue';
import { Report } from '@/types/reports';
import { generateConclusion } from '@/lib/pdf/generateConclusion';
import { RequiredFormKeys } from '@/constants/requiredFields';

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
    status: '',
    hasil: '',
    rekomendasiSanksi: '',
    pemulihanKorban: '',
    pemulihanNamaBaik: '',
    pencegahanKeberulangan: '',

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
    jenisKekerasan: 'Jenis kekerasan',
    status: 'Status kasus',
    hasil: 'Hasil pemeriksaan',
    rekomendasiSanksi: 'Rekomendasi Sanksi',
    pemulihanKorban: 'Pemulihan korban',
    pemulihanNamaBaik: 'Pemulihan nama baik',
    pencegahanKeberulangan: 'Pencegahan keberulangan',
};

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

        const pdf = generateConclusion(props.report, form);
        const pdfBlob = pdf.output('blob');
        const pdfFile = new File(
            [pdfBlob],
            'laporan-kesimpulan-rekomendasi.pdf',
            {
                type: 'application/pdf',
            },
        );

        const encryptedPayload = await encryptToPayload(pdfFile, publicKeys);

        form.document = [
            {
                ...encryptedPayload,
                type: props.report.progress,
                subtype: 'kesimpulan_rekomendasi',
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
        title="Formulir Kesimpulan & Rekomendasi"
        description="Ringkasan hasil pemeriksaan beserta rekomendasi sanksi dan tindak lanjut."
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

        <section>
            <FormSectionTitle title="2. Status Kasus" />
            <div class="mt-4 flex flex-row items-center gap-6">
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
        </section>

        <section>
            <FormSectionTitle title="3. HASIL PEMERIKSAAN" />
            <TextareaField
                name="hasilPemeriksaan"
                v-model="form.hasil"
                label="Catatan hasil pemeriksaan"
                placeholder="Ringkasan temuan dari proses klarifikasi dan pemeriksaan..."
                rows="5"
                :error="stepErrors.hasil"
                required
            />
        </section>

        <section>
            <FormSectionTitle title="4. REKOMENDASI SANKSI" />
            <TextareaField
                name="rekomendasiSanksi"
                v-model="form.rekomendasiSanksi"
                placeholder="Sanksi yang direkomendasikan sesuai pedoman..."
                label="Tulis rekomendasi sanksi yang diberikan"
                rows="5"
                :error="stepErrors.rekomendasiSanksi"
                required
            />
        </section>
        <section>
            <FormSectionTitle title="5. REKOMENDASI TINDAKLANJUT" />
            <div class="mt-4 space-y-6">
                <TextareaField
                    name="pemulihanKorban"
                    v-model="form.pemulihanKorban"
                    label="Pemulihan Korban"
                    rows="5"
                    :error="stepErrors.hasil"
                    required
                />
                <TextareaField
                    name="pemulihanNamaBaik"
                    v-model="form.pemulihanNamaBaik"
                    label="Pemulihan Nama Baik Terlapor"
                    rows="5"
                    :error="stepErrors.pemulihanNamaBaik"
                    required
                />
                <TextareaField
                    name="pencegahanKeberulangan"
                    v-model="form.pencegahanKeberulangan"
                    label="Pencegahan Keberulangan"
                    rows="5"
                    :error="stepErrors.pencegahanKeberulangan"
                    required
                />
            </div>
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
