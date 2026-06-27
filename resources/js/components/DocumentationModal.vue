<script setup lang="ts">
import { ref } from 'vue';
import FormSectionTitle from '@/components/form/FormSectionTitle.vue';
import { useCryptoStore } from '@/lib/crypto/store';
import { handleCreate } from '@/lib/handleRequest';
import { store } from '@/routes/satgas/reports/handling/document';
import { useForm } from '@inertiajs/vue3';
import { Report } from '@/types/reports';
import FileUploadField from './form/FileUploadField.vue';
import DialogFooter from './DialogFooter.vue';
import { useDocumentEncryption } from '@/composables/useDocumentEncryption';
import BaseModal from './handling/BaseModal.vue';

type AttachmentField = {
    label: string;
    accept: string;
};

const props = defineProps<{
    open: boolean;
    report: Report;
    attachmentFields: AttachmentField[];
    subtype: string;
    documentId: string;
}>();

const emit = defineEmits<{
    close: [];
    success: [];
}>();

const form = useForm({
    document: [] as File[][],
});

const generalError = ref('');
const { getTeamPublicKeys, encryptToPayload } = useDocumentEncryption();
const handleSubmit = async () => {
    try {
        generalError.value = '';

        const hasAnyFile = props.attachmentFields.some(
            (_, index) => (form.document[index] ?? []).length > 0,
        );

        if (!hasAnyFile) {
            generalError.value = 'Pilih minimal satu berkas untuk diunggah.';
            return;
        }

        const cryptoStore = useCryptoStore();

        if (!cryptoStore.privateKey) {
            throw new Error('Private key belum tersedia');
        }

        const publicKeys = await getTeamPublicKeys(props.report.members);

        const encryptedAttachments = [];

        for (const attachmentGroup of form.document) {
            if (!attachmentGroup?.length) continue;

            for (const file of attachmentGroup) {
                const encryptedPayload = await encryptToPayload(
                    file,
                    publicKeys,
                );

                encryptedAttachments.push({
                    ...encryptedPayload,
                    document_id: props.documentId,
                    type: props.report.progress,
                    subtype: props.subtype,
                    attachment_type: 'documentation',
                });
            }
        }

        form.transform((data) => ({
            ...data,
            document: encryptedAttachments,
        }));

        handleCreate(form, store(props.report.id), {
            onSuccess: () => emit('success'),
            onError: () => console.error('error'),
        });
    } catch (error) {
        console.error(error);
        generalError.value =
            error instanceof Error
                ? error.message
                : 'Terjadi kesalahan tidak terduga.';
    }
};

function getAcceptHint(accept: string) {
    if (accept.includes('image/*')) {
        return 'Format: JPG, JPEG, PNG, WEBP';
    }

    if (accept.includes('audio/*')) {
        return 'Format: MP3, WAV, M4A, OGG';
    }

    if (accept.includes('video/*')) {
        return 'Format: MP4, MOV, AVI, MKV';
    }

    if (accept.includes('.pdf')) {
        return 'Format: PDF';
    }

    return '';
}
</script>

<template>
    <BaseModal
        :open="open"
        title="Formulir Dokumentasi"
        description="Unggah berkas pendukung untuk melengkapi dokumen penanganan."
        @close="$emit('close')"
    >
        <FormSectionTitle title="Berkas yang masih perlu diunggah" size="md" />
        <p class="mb-3 text-sm text-muted-foreground">
            Lengkapi Dokumen Pemeriksaan Untuk melanjutkan Ke Tahap Berikutnya
        </p>

        <section
            v-for="(field, index) in attachmentFields"
            :key="index"
            class="rounded-3xl border p-4"
        >
            <FormSectionTitle :title="field.label" />

            <FileUploadField
                v-model="form.document[index]"
                :label="field.description"
                :hint="getAcceptHint(field.accept)"
                :accept="field.accept"
            />
        </section>

        <p v-if="generalError" class="mt-2 text-sm text-red-600">
            {{ generalError }}
        </p>

        <template #footer>
            <DialogFooter
                back-label="Batal"
                action-label="Simpan Dokumentasi"
                :action-disabled="form.processing"
                @back="$emit('close')"
                @action="handleSubmit"
            />
        </template>
    </BaseModal>
</template>
