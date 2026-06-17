import { getPublicKeys } from '@/lib/crypto/getPublicKeys';
import { useCryptoStore } from '@/lib/crypto/store';
import { encryptFile } from '@/lib/mediaCrypto';

export function useDocumentEncryption() {
    const cryptoStore = useCryptoStore();

    /**
     * Memvalidasi private key dan mengambil public keys dari daftar anggota tim.
     */
    const getTeamPublicKeys = async (
        members: Array<{ id: number | string }>,
    ) => {
        if (!cryptoStore.privateKey) {
            throw new Error(
                'Private key belum tersedia. Selesaikan proses buka kunci (unlock) terlebih dahulu.',
            );
        }

        const memberIds = members.map((m) => m.id);

        return await getPublicKeys(memberIds);
    };

    /**
     * Mengenkripsi file menggunakan public keys dan mengembalikan
     * objek payload yang siap dikirimkan melalui Inertia/Axios.
     */
    const encryptToPayload = async (file: File, publicKeys: string[]) => {
        const encrypted = await encryptFile(file, publicKeys);

        return {
            file: encrypted.encryptedData,
            filename: encrypted.filename,
            mime_type: encrypted.mimeType,
            size: encrypted.size,
            // Backend biasanya menerima edeks dalam bentuk string JSON
            edeks: JSON.stringify(encrypted.edeks),
        };
    };

    return {
        getTeamPublicKeys,
        encryptToPayload,
    };
}
