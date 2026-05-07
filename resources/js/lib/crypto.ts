export async function generateEncryptionContext(password: string) {
    /*
    |--------------------------------------------------------------------------
    | Password Salt
    |--------------------------------------------------------------------------
    */
    const passwordSalt = crypto.getRandomValues(new Uint8Array(16));

    /*
    |--------------------------------------------------------------------------
    | Generate MEK
    |--------------------------------------------------------------------------
    */
    const mek = crypto.getRandomValues(new Uint8Array(32));

    /*
    |--------------------------------------------------------------------------
    | Derive KEK from Password
    |--------------------------------------------------------------------------
    */
    const passwordKey = await crypto.subtle.importKey(
        'raw',
        new TextEncoder().encode(password),
        'PBKDF2',
        false,
        ['deriveKey'],
    );

    const kek = await crypto.subtle.deriveKey(
        {
            name: 'PBKDF2',
            salt: passwordSalt,
            iterations: 600000,
            hash: 'SHA-256',
        },
        passwordKey,
        {
            name: 'AES-GCM',
            length: 256,
        },
        true,
        ['encrypt', 'decrypt'],
    );

    /*
    |--------------------------------------------------------------------------
    | Encrypt MEK using Password KEK
    |--------------------------------------------------------------------------
    */
    const emekIv = crypto.getRandomValues(new Uint8Array(12));

    const encryptedMek = await crypto.subtle.encrypt(
        {
            name: 'AES-GCM',
            iv: emekIv,
        },
        kek,
        mek,
    );

    /*
    |--------------------------------------------------------------------------
    | Generate Recovery Code
    |--------------------------------------------------------------------------
    */
    const recoveryCode = generateRecoveryCode();

    const recoverySalt = crypto.getRandomValues(new Uint8Array(16));

    /*
    |--------------------------------------------------------------------------
    | Derive Recovery KEK
    |--------------------------------------------------------------------------
    */
    const recoveryKey = await crypto.subtle.importKey(
        'raw',
        new TextEncoder().encode(recoveryCode),
        'PBKDF2',
        false,
        ['deriveKey'],
    );

    const recoveryKek = await crypto.subtle.deriveKey(
        {
            name: 'PBKDF2',
            salt: recoverySalt,
            iterations: 600000,
            hash: 'SHA-256',
        },
        recoveryKey,
        {
            name: 'AES-GCM',
            length: 256,
        },
        true,
        ['encrypt', 'decrypt'],
    );

    /*
    |--------------------------------------------------------------------------
    | Encrypt MEK using Recovery KEK
    |--------------------------------------------------------------------------
    */
    const recoveryIv = crypto.getRandomValues(new Uint8Array(12));

    const encryptedRecoveryMek = await crypto.subtle.encrypt(
        {
            name: 'AES-GCM',
            iv: recoveryIv,
        },
        recoveryKek,
        mek,
    );

    /*
    |--------------------------------------------------------------------------
    | Generate RSA Keypair
    |--------------------------------------------------------------------------
    */
    const keyPair = await crypto.subtle.generateKey(
        {
            name: 'RSA-OAEP',
            modulusLength: 2048,
            publicExponent: new Uint8Array([1, 0, 1]),
            hash: 'SHA-256',
        },
        true,
        ['encrypt', 'decrypt'],
    );

    /*
    |--------------------------------------------------------------------------
    | Export Keys
    |--------------------------------------------------------------------------
    */
    const publicKey = await crypto.subtle.exportKey('spki', keyPair.publicKey);

    const privateKey = await crypto.subtle.exportKey(
        'pkcs8',
        keyPair.privateKey,
    );

    /*
    |--------------------------------------------------------------------------
    | Encrypt Private Key using MEK
    |--------------------------------------------------------------------------
    */
    const mekKey = await crypto.subtle.importKey('raw', mek, 'AES-GCM', false, [
        'encrypt',
    ]);

    const privateIv = crypto.getRandomValues(new Uint8Array(12));

    const encryptedPrivateKey = await crypto.subtle.encrypt(
        {
            name: 'AES-GCM',
            iv: privateIv,
        },
        mekKey,
        privateKey,
    );

    return {
        /*
        |--------------------------------------------------------------------------
        | Keys
        |--------------------------------------------------------------------------
        */
        public_key: bufferToBase64(publicKey),

        encrypted_private_key: JSON.stringify({
            iv: bufferToBase64(privateIv),
            data: bufferToBase64(encryptedPrivateKey),
        }),

        /*
        |--------------------------------------------------------------------------
        | Password EMEK
        |--------------------------------------------------------------------------
        */
        emek_password: JSON.stringify({
            iv: bufferToBase64(emekIv),
            data: bufferToBase64(encryptedMek),
        }),

        emek_password_salt: bufferToBase64(passwordSalt),

        /*
        |--------------------------------------------------------------------------
        | Recovery EMEK
        |--------------------------------------------------------------------------
        */
        emek_recovery: JSON.stringify({
            iv: bufferToBase64(recoveryIv),
            data: bufferToBase64(encryptedRecoveryMek),
        }),

        emek_recovery_salt: bufferToBase64(recoverySalt),

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        | Show once to user
        |--------------------------------------------------------------------------
        */
        recovery_code: recoveryCode,
    };
}

function generateRecoveryCode(): string {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    const parts = [];

    for (let g = 0; g < 4; g++) {
        let part = '';

        for (let i = 0; i < 5; i++) {
            part += chars[Math.floor(Math.random() * chars.length)];
        }

        parts.push(part);
    }

    return parts.join('-');
}

function bufferToBase64(buffer: ArrayBuffer | Uint8Array) {
    return btoa(String.fromCharCode(...new Uint8Array(buffer)));
}
