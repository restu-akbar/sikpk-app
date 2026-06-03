import { aesEncrypt, aesDecrypt } from '@/lib/crypto/aes-gcm';
import { bufferToBase64, base64ToBuffer } from '@/lib/crypto/base64';
import { rsaEncrypt, rsaDecrypt } from '@/lib/crypto/rsa-oaep';

export async function encryptFile(
    file: File,
    publicKeys: Record<string, CryptoKey>,
) {
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
    const edeksEntries = await Promise.all(
        Object.entries(publicKeys).map(async ([userId, publicKey]) => {
            const encryptedDek = await rsaEncrypt(publicKey, dekRaw);

            return [userId, bufferToBase64(encryptedDek)] as const;
        }),
    );

    const edeks = Object.fromEntries(edeksEntries);

    return {
        filename: file.name,
        mimeType: file.type,
        size: file.size,
        encryptedData: new File(
            [JSON.stringify(encryptedFile)],
            `${file.name}.enc`,
            { type: 'application/octet-stream' },
        ),
        edeks,
    };
}

export async function decryptFile({
    encryptedFile,
    edek,
    privateKey,
    filename,
    mimeType,
}: {
    encryptedFile: Blob;
    edek: string;
    privateKey: CryptoKey;
    filename: string;
    mimeType: string;
}) {
    const encryptedDek = base64ToBuffer(edek);

    const dekRaw = await rsaDecrypt(privateKey, encryptedDek);

    const dekKey = await crypto.subtle.importKey(
        'raw',
        dekRaw,
        { name: 'AES-GCM' },
        false,
        ['decrypt'],
    );

    const encryptedPayload = JSON.parse(await encryptedFile.text());

    const decrypted = await aesDecrypt({
        key: dekKey,
        iv: encryptedPayload.iv,
        data: encryptedPayload.data,
    });

    return new File([decrypted], filename, { type: mimeType });
}
