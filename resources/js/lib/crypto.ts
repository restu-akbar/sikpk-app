import { aesEncrypt, aesDecrypt } from '@/lib/crypto/aes-gcm';
import { bufferToBase64 } from '@/lib/crypto/base64';
import { base64ToBuffer } from '@/lib/crypto/base64';
import { derivePbkdf2Key } from '@/lib/crypto/pbkdf2';
import {
    generateRsaKeyPair,
    exportPublicKey,
    exportPrivateKey,
} from '@/lib/crypto/rsa-oaep';

import { generateRecoveryCode } from '@/lib/crypto/recovery';

export async function generateEncryption(password: string) {
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
    const kek = await derivePbkdf2Key({
        password,
        salt: passwordSalt,
        iterations: 600000,
    });

    /*
    |--------------------------------------------------------------------------
    | Encrypt MEK using Password KEK
    |--------------------------------------------------------------------------
    */
    const emekEncrypted = await aesEncrypt({
        key: kek,
        data: mek,
    });

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
    const recoveryKek = await derivePbkdf2Key({
        password: recoveryCode,
        salt: recoverySalt,
        iterations: 600000,
    });

    /*
    |--------------------------------------------------------------------------
    | Encrypt MEK using Recovery KEK
    |--------------------------------------------------------------------------
    */
    const recoveryEncrypted = await aesEncrypt({
        key: recoveryKek,
        data: mek,
    });

    /*
    |--------------------------------------------------------------------------
    | Generate RSA Keypair
    |--------------------------------------------------------------------------
    */
    const keyPair = await generateRsaKeyPair();

    const publicKey = await exportPublicKey(keyPair.publicKey);

    const privateKeyPkcs8 = await exportPrivateKey(keyPair.privateKey);

    /*
    |--------------------------------------------------------------------------
    | Encrypt Private Key using MEK
    |--------------------------------------------------------------------------
    */
    const mekKey = await crypto.subtle.importKey('raw', mek, 'AES-GCM', false, [
        'encrypt',
    ]);

    const encryptedPrivate = await aesEncrypt({
        key: mekKey,
        data: privateKeyPkcs8,
    });

    /*
    |--------------------------------------------------------------------------
    | Return payload
    |--------------------------------------------------------------------------
    */
    return {
        public_key: publicKey,

        encrypted_private_key: JSON.stringify(encryptedPrivate),

        emek_password: JSON.stringify(emekEncrypted),

        emek_password_salt: bufferToBase64(passwordSalt),

        emek_recovery: JSON.stringify(recoveryEncrypted),

        emek_recovery_salt: bufferToBase64(recoverySalt),

        recovery_code: recoveryCode,
    };
}

export async function generateDecryption({
    password,
    emek_password,
    emek_password_salt,
    encrypted_private_key,
}: {
    password: string;
    emek_password: string;
    emek_password_salt: string;
    encrypted_private_key: string;
}) {
    /*
    |--------------------------------------------------------------------------
    | Parse payloads
    |--------------------------------------------------------------------------
    */
    const emekPayload = JSON.parse(emek_password);
    const privatePayload = JSON.parse(encrypted_private_key);
    const salt = base64ToBuffer(emek_password_salt);

    /*
    |--------------------------------------------------------------------------
    | Derive KEK from password
    |--------------------------------------------------------------------------
    */
    const kek = await derivePbkdf2Key({
        password,
        salt,
        iterations: 600000,
    });

    /*
    |--------------------------------------------------------------------------
    | Decrypt MEK
    |--------------------------------------------------------------------------
    */
    const mekRaw = await aesDecrypt({
        key: kek,
        iv: emekPayload.iv,
        data: emekPayload.data,
    });

    /*
    |--------------------------------------------------------------------------
    | Import MEK as AES key
    |--------------------------------------------------------------------------
    */
    const mekKey = await crypto.subtle.importKey(
        'raw',
        mekRaw,
        'AES-GCM',
        false,
        ['decrypt'],
    );

    /*
    |--------------------------------------------------------------------------
    | Decrypt private key
    |--------------------------------------------------------------------------
    */
    const privateKeyPkcs8 = await aesDecrypt({
        key: mekKey,
        iv: privatePayload.iv,
        data: privatePayload.data,
    });

    /*
    |--------------------------------------------------------------------------
    | Import RSA private key
    |--------------------------------------------------------------------------
    */
    const privateKey = await crypto.subtle.importKey(
        'pkcs8',
        privateKeyPkcs8,
        {
            name: 'RSA-OAEP',
            hash: 'SHA-256',
        },
        false,
        ['decrypt'],
    );

    return privateKey;
}
