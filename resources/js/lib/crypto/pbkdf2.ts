export async function derivePbkdf2Key(params: {
    password: string;
    salt: Uint8Array;
    iterations: number;
    hash?: string;
    keyUsage?: KeyUsage[];
}) {
    const {
        password,
        salt,
        iterations,
        hash = 'SHA-256',
        keyUsage = ['encrypt', 'decrypt'],
    } = params;

    const keyMaterial = await crypto.subtle.importKey(
        'raw',
        new TextEncoder().encode(password),
        'PBKDF2',
        false,
        ['deriveKey'],
    );

    return crypto.subtle.deriveKey(
        {
            name: 'PBKDF2',
            salt,
            iterations,
            hash,
        },
        keyMaterial,
        {
            name: 'AES-GCM',
            length: 256,
        },
        false,
        keyUsage,
    );
}
