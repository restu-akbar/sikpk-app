function base64ToBuffer(base64: string): ArrayBuffer {
    return Uint8Array.from(atob(base64), (c) => c.charCodeAt(0));
}

export async function importPublicKey(base64: string) {
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

export async function encryptText(text: string, publicKey: CryptoKey) {
    const encoded = new TextEncoder().encode(text);

    const encrypted = await crypto.subtle.encrypt(
        {
            name: 'RSA-OAEP',
        },
        publicKey,
        encoded,
    );

    return encrypted;
}

export async function decryptText(
    encrypted: ArrayBuffer,
    privateKey: CryptoKey,
) {
    const decrypted = await crypto.subtle.decrypt(
        {
            name: 'RSA-OAEP',
        },
        privateKey,
        encrypted,
    );

    return new TextDecoder().decode(decrypted);
}
