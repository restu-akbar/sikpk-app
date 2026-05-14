import { base64ToBuffer, bufferToBase64 } from './base64';

export async function generateRsaKeyPair() {
    return crypto.subtle.generateKey(
        {
            name: 'RSA-OAEP',
            modulusLength: 2048,
            publicExponent: new Uint8Array([1, 0, 1]),
            hash: 'SHA-256',
        },
        true,
        ['encrypt', 'decrypt'],
    );
}

export async function exportPublicKey(key: CryptoKey): Promise<string> {
    const raw = await crypto.subtle.exportKey('spki', key);

    return bufferToBase64(raw);
}

export async function exportPrivateKey(key: CryptoKey): Promise<ArrayBuffer> {
    return crypto.subtle.exportKey('pkcs8', key);
}

export async function importPublicKey(base64: string): Promise<CryptoKey> {
    return crypto.subtle.importKey(
        'spki',
        base64ToBuffer(base64),
        {
            name: 'RSA-OAEP',
            hash: 'SHA-256',
        },
        true,
        ['encrypt'],
    );
}

export async function importPrivateKey(pkcs8: ArrayBuffer): Promise<CryptoKey> {
    return crypto.subtle.importKey(
        'pkcs8',
        pkcs8,
        {
            name: 'RSA-OAEP',
            hash: 'SHA-256',
        },
        false,
        ['decrypt'],
    );
}

export async function rsaEncrypt(key: CryptoKey, data: ArrayBuffer) {
    return crypto.subtle.encrypt({ name: 'RSA-OAEP' }, key, data);
}

export async function rsaDecrypt(key: CryptoKey, data: ArrayBuffer) {
    return crypto.subtle.decrypt({ name: 'RSA-OAEP' }, key, data);
}
