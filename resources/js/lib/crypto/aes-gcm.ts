import { bufferToBase64, base64ToBuffer } from './base64';

export async function aesEncrypt(params: {
    key: CryptoKey;
    data: ArrayBuffer;
    iv?: Uint8Array;
    encoding?: 'base64' | 'binary';
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

    if (params.encoding === 'binary') {
        return {
            iv,
            data: new Uint8Array(encrypted),
        };
    }

    return {
        iv: bufferToBase64(iv),
        data: bufferToBase64(encrypted),
    };
}

export async function aesDecrypt(params: {
    key: CryptoKey;
    iv: string | Uint8Array;
    data: string | Uint8Array;
    encoding?: 'base64' | 'binary';
}) {
    const iv =
        params.encoding === 'binary'
            ? params.iv
            : base64ToBuffer(params.iv as string);

    const data =
        params.encoding === 'binary'
            ? params.data
            : base64ToBuffer(params.data as string);

    return crypto.subtle.decrypt(
        {
            name: 'AES-GCM',
            iv,
        },
        params.key,
        data,
    );
}
