import { aesEncrypt } from '@/lib/crypto/aes-gcm';
import { rsaEncrypt } from '@/lib/crypto/rsa-oaep';
import { bufferToBase64 } from '@/lib/crypto/base64';

export async function encryptFile(
    file: File,
    publicKey: CryptoKey,
): Promise<EncryptedFile> {
    const fileBuffer = await file.arrayBuffer();

    const dekRaw = crypto.getRandomValues(new Uint8Array(32));

    const dekKey = await crypto.subtle.importKey(
        'raw',
        dekRaw,
        { name: 'AES-GCM' },
        false,
        ['encrypt'],
    );

    const encryptedFile = await aesEncrypt({
        key: dekKey,
        data: new Uint8Array(fileBuffer),
    });

    const encryptedDek = await rsaEncrypt(publicKey, dekRaw);

    return {
        filename: file.name,
        mimeType: file.type,
        size: file.size,
        encryptedData: new File(
            [JSON.stringify(encryptedFile)],
            `${file.name}.enc`,
            { type: 'application/octet-stream' },
        ),
        edek: bufferToBase64(encryptedDek),
    };
}
