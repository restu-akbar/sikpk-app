import { ref } from 'vue';
import { useDocumentEncryption } from '@/composables/useDocumentEncryption';
import { satgasApi } from '@/lib/axios';
import { useCryptoStore } from '@/lib/crypto/store';

export function useReportSubmission(reportId: string, members: any[]) {
    const isSubmitting = ref(false);
    const { getTeamPublicKeys, encryptToPayload } = useDocumentEncryption();

    const processAndUploadFiles = async (files: File[], subtype: string) => {
        const cryptoStore = useCryptoStore();
        if (!cryptoStore.privateKey)
            throw new Error('Private key belum tersedia');

        const publicKeys = await getTeamPublicKeys(members);
        const formData = new FormData();

        const encryptedEvidences = await Promise.all(
            files.map((file) => encryptToPayload(file, publicKeys)),
        );

        encryptedEvidences.forEach((enc, index) => {
            Object.entries(enc).forEach(([key, value]) => {
                formData.append(`bukti[${index}][${key}]`, value as any);
            });
            formData.append(
                `bukti[${index}][attachment_type]`,
                'documentation',
            );
        });

        await satgasApi.post(`files/${reportId}`, formData);
    };

    return { isSubmitting, processAndUploadFiles };
}
