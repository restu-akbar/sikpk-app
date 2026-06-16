<script setup lang="ts">
import { watch, ref } from 'vue';
import FormSectionTitle from '@/components/form/FormSectionTitle.vue';
import { useCryptoStore } from '@/lib/crypto/store';
import { getPublicKeys } from '@/lib/crypto/getPublicKeys';
import { encryptFile } from '@/lib/mediaCrypto';
import { handleCreate } from '@/lib/handleRequest';
import { store } from '@/routes/satgas/reports/handling/document';
import { useForm } from '@inertiajs/vue3';
import { Report } from '@/types/reports';
import FileUploadField from './form/FileUploadField.vue';
import DialogFooter from './DialogFooter.vue';

type AttachmentField = {
    label: string;
    accept: string;
};

const props = defineProps<{
    open: boolean;
    report: Report;
    attachmentFields: AttachmentField[];
}>();

const emit = defineEmits<{
    close: [];
    success: [];
}>();

const form = useForm({
    document: [] as File[][],
});

const stepErrors = ref<Record<number, string>>({});
const handleSubmit = async () => {
    try {
        stepErrors.value = {};

        props.attachmentFields.forEach((_, index) => {
            const files = form.document[index] ?? [];

            if (!files.length) {
                stepErrors.value[index] = 'Berkas wajib diunggah';
            }
        });

        if (Object.keys(stepErrors.value).length > 0) {
            return;
        }

        const cryptoStore = useCryptoStore();

        if (!cryptoStore.privateKey) {
            throw new Error('Private key belum tersedia');
        }

        const memberIds = props.report.members.map((m) => m.id);
        const publicKeys = await getPublicKeys(memberIds);

        const encryptedAttachments = [];

        for (const attachmentGroup of form.document) {
            if (!attachmentGroup?.length) continue;

            for (const file of attachmentGroup) {
                const encryptedFile = await encryptFile(file, publicKeys);

                encryptedAttachments.push({
                    file: encryptedFile.encryptedData,
                    filename: encryptedFile.filename,
                    mime_type: encryptedFile.mimeType,
                    size: encryptedFile.size,
                    edeks: JSON.stringify(encryptedFile.edeks),

                    type: props.report.progress,
                    subtype: 'documentation',
                });
            }
        }
        form.document = encryptedAttachments as any;
        handleCreate(form, store(props.report.id), {
            onSuccess: () => {
                emit('success');
            },
            onError: () => {
                console.error('error');
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
                                Formulir Dokumentasi
                            </h2>
                            <p class="mt-0.5 text-sm text-gray-500">
                                Unggah berkas pendukung untuk melengkapi dokumen
                                penanganan.
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
                        <FormSectionTitle
                            title="Berkas yang harus diunggah"
                            size="md"
                        />
                        <!-- Seksi 1: Informasi Laporan -->
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
                                :required="true"
                                :error="stepErrors[index]"
                            />
                        </section>

                        <!-- Tim Penanganan -->
                    </div>

                    <!-- Footer -->
                    <DialogFooter
                        back-label="Batal"
                        action-label="Simpan Dokumentasi"
                        :action-disabled="form.processing"
                        @back="$emit('close')"
                        @action="handleSubmit"
                    />
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
