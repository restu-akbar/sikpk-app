import { base64ToBuffer, bufferToBase64 } from './base64';

export async function aesEncrypt(params: {
    key: CryptoKey;
    data: ArrayBuffer;
    iv?: Uint8Array;
}) {
    const iv = params.iv ?? crypto.getRandomValues(new Uint8Array(12));

    const encrypted = await crypto.subtle.encrypt(
        {
            name: 'AES-GCM',
            iv,
        },
        params.key,
        params.data,
    );

    return {
        iv: bufferToBase64(iv),
        data: bufferToBase64(encrypted),
    };
}

export async function aesDecrypt(params: {
    key: CryptoKey;
    iv: string;
    data: string;
}) {
    const decrypted = await crypto.subtle.decrypt(
        {
            name: 'AES-GCM',
            iv: base64ToBuffer(params.iv),
        },
        params.key,
        base64ToBuffer(params.data),
    );

    return decrypted;
}
