import { ref } from 'vue';
import { satgasApi } from '@/lib/axios';
import { useCryptoStore } from '@/lib/crypto/store';
import { decryptFile } from '@/lib/mediaCrypto';

const AUDIO_EXTENSIONS =
    /\.(mp3|wav|ogg|m4a|aac|flac|wma|mpga|opus)(\.[a-z0-9]+)?$/i;

export function useFileViewer() {
    const cryptoStore = useCryptoStore();
    const audioPreview = ref<{ url: string; filename: string } | null>(null);

    function isAudioFile(file: File): boolean {
        return (
            file.type.startsWith('audio/') || AUDIO_EXTENSIONS.test(file.name)
        );
    }

    function openDecryptedFile(decryptedFile: File) {
        const url = URL.createObjectURL(decryptedFile);

        if (isAudioFile(decryptedFile)) {
            audioPreview.value = { url, filename: decryptedFile.name };
            return;
        }

        window.open(url, '_blank');
    }

    function closeAudioPreview() {
        if (audioPreview.value) {
            URL.revokeObjectURL(audioPreview.value.url);
        }
        audioPreview.value = null;
    }

    async function viewFile(
        attachment: any,
        type: 'document' | 'evidence' = 'document',
    ) {
        try {
            const { data } = await satgasApi.get(`files/${attachment.id}`, {
                responseType: 'blob',
                params: { type },
            });

            const edek = cryptoStore.userId
                ? attachment.edeks?.[cryptoStore.userId]
                : undefined;

            const decryptedFile = await decryptFile({
                encryptedFile: data,
                edek,
                privateKey: cryptoStore.privateKey!,
                filename: attachment.original_filename,
                mimeType: attachment.mime_type,
            });

            openDecryptedFile(decryptedFile);
        } catch (error) {
            console.error('Gagal membuka berkas:', error);
        }
    }

    return {
        audioPreview,
        viewFile,
        closeAudioPreview,
    };
}
