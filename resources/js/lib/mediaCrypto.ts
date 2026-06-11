import { aesEncrypt, aesDecrypt } from '@/lib/crypto/aes-gcm';
import { bufferToBase64, base64ToBuffer } from '@/lib/crypto/base64';
import { rsaEncrypt, rsaDecrypt } from '@/lib/crypto/rsa-oaep';

export async function encryptFile(
    file: File,
    publicKeys: Record<string, CryptoKey> | CryptoKey[] | CryptoKey,
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
        data: fileBuffer,
        encoding: 'binary',
    });

    let normalizedKeys: Record<string, CryptoKey> = {};

    if (
        !Array.isArray(publicKeys) &&
        typeof publicKeys === 'object' &&
        publicKeys !== null
    ) {
        normalizedKeys = publicKeys as Record<string, CryptoKey>;
    } else if (Array.isArray(publicKeys)) {
        normalizedKeys = Object.fromEntries(
            publicKeys.map((key, index) => [`key_${index}`, key]),
        );
    } else {
        normalizedKeys = {
            primary: publicKeys as CryptoKey,
        };
    }

    const edeksEntries = await Promise.all(
        Object.entries(normalizedKeys).map(async ([userId, publicKey]) => {
            const encryptedDek = await rsaEncrypt(publicKey, dekRaw);

            return [userId, bufferToBase64(encryptedDek)] as const;
        }),
    );

    const edeks = Object.fromEntries(edeksEntries);

    const payload = new Uint8Array(
        encryptedFile.iv.length + encryptedFile.data.length,
    );

    payload.set(encryptedFile.iv, 0);
    payload.set(encryptedFile.data, encryptedFile.iv.length);

    const encryptedBlob = new File([payload], `${file.name}.enc`, {
        type: 'application/octet-stream',
    });

    /**
     * =========================
     * RETURN PAYLOAD
     * =========================
     */
    return {
        filename: file.name,
        mimeType: file.type,
        size: file.size,
        encryptedData: encryptedBlob,
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

    const payload = new Uint8Array(await encryptedFile.arrayBuffer());

    const iv = payload.slice(0, 12);
    const ciphertext = payload.slice(12);

    const decrypted = await aesDecrypt({
        key: dekKey,
        iv,
        data: ciphertext,
        encoding: 'binary',
    });

    return new File([decrypted], filename, { type: mimeType });
}
