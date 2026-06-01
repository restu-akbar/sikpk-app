import { aesEncrypt } from '@/lib/crypto/aes-gcm';
import { rsaEncrypt } from '@/lib/crypto/rsa-oaep';

export async function encryptFile(
    file: File,
    publicKey: CryptoKey,
): Promise<EncryptedFile> {
    const fileBuffer = await file.arrayBuffer();

    const dek = crypto.getRandomValues(new Uint8Array(32));

    const encryptedFile = await aesEncrypt({
        key: dek,
        data: new Uint8Array(fileBuffer),
    });

    const edek = await rsaEncrypt(publicKey, dek.buffer);

    return {
        filename: file.name,
        mimeType: file.type,
        size: file.size,

        encryptedData: new Blob([JSON.stringify(encryptedFile)]),

        edek: bufferToBase64(edek),
    };
}
