import { aesEncrypt, aesDecrypt } from '@/lib/crypto/aes-gcm';
import { bufferToBase64 } from '@/lib/crypto/base64';
import { base64ToBuffer } from '@/lib/crypto/base64';
import { derivePbkdf2Key } from '@/lib/crypto/pbkdf2';
import { generateRecoveryCode } from '@/lib/crypto/recovery';
import {
    generateRsaKeyPair,
    exportPublicKey,
    exportPrivateKey,
} from '@/lib/crypto/rsa-oaep';

// ============================================================
// generateEncryption
// ============================================================
// Mode 1 — initial (getting-started)
//   Full generate: RSA keypair + MEK + enkripsi password & recovery
//
// Mode 2 — change (ganti password)
//   Decrypt MEK pakai password lama, encrypt ulang pakai password baru
//   Return: emek_password + emek_password_salt
//
// Mode 3 — recovery (pakai recovery file)
//   Decrypt MEK pakai recovery code, encrypt ulang pakai password baru
//   Return: emek_password + emek_password_salt
// ============================================================

type InitialParams = {
    mode: 'initial';
    password: string;
};

type ChangeParams = {
    mode: 'change';
    oldPassword: string;
    newPassword: string;
    emek_password: string;
    emek_password_salt: string;
};

type RecoveryParams = {
    mode: 'recovery';
    recoveryCode: string;
    newPassword: string;
    emek_recovery: string;
    emek_recovery_salt: string;
};

type EncryptionParams = InitialParams | ChangeParams | RecoveryParams;

type InitialResult = {
    public_key: string;
    encrypted_private_key: string;
    emek_password: string;
    emek_password_salt: string;
    emek_recovery: string;
    emek_recovery_salt: string;
    recovery_code: string;
};

type ReEncryptResult = {
    emek_password: string;
    emek_password_salt: string;
};

export async function generateEncryption(
    params: InitialParams,
): Promise<InitialResult>;
export async function generateEncryption(
    params: ChangeParams,
): Promise<ReEncryptResult>;
export async function generateEncryption(
    params: RecoveryParams,
): Promise<ReEncryptResult>;
export async function generateEncryption(
    params: EncryptionParams,
): Promise<InitialResult | ReEncryptResult> {
    // ----------------------------------------------------------
    // Mode: change — decrypt MEK pakai password lama,
    //               encrypt ulang pakai password baru
    // ----------------------------------------------------------
    if (params.mode === 'change') {
        const { oldPassword, newPassword, emek_password, emek_password_salt } =
            params;

        try {
            const mekRaw = await decryptMekWithPassword({
                password: oldPassword,
                emek_password,
                emek_password_salt,
            });
            return encryptMekWithPassword({ mekRaw, password: newPassword });
        } catch (err) {
            throw err;
        }
    }

    // ----------------------------------------------------------
    // Mode: recovery — decrypt MEK pakai recovery code,
    //                  encrypt ulang pakai password baru
    // ----------------------------------------------------------
    if (params.mode === 'recovery') {
        const { recoveryCode, newPassword, emek_recovery, emek_recovery_salt } =
            params;

        const emekPayload = JSON.parse(emek_recovery);
        const recoverySalt = base64ToBuffer(emek_recovery_salt);

        const recoveryKek = await derivePbkdf2Key({
            password: recoveryCode,
            salt: recoverySalt,
            iterations: 600000,
        });

        const mekRaw = await aesDecrypt({
            key: recoveryKek,
            iv: emekPayload.iv,
            data: emekPayload.data,
        });

        return encryptMekWithPassword({ mekRaw, password: newPassword });
    }

    // ----------------------------------------------------------
    // Mode: initial — full generate (getting-started)
    // ----------------------------------------------------------
    const { password } = params;

    const passwordSalt = crypto.getRandomValues(new Uint8Array(16));
    const mek = crypto.getRandomValues(new Uint8Array(32));

    const kek = await derivePbkdf2Key({
        password,
        salt: passwordSalt,
        iterations: 600000,
    });

    const emekEncrypted = await aesEncrypt({ key: kek, data: mek });

    const recoveryCode = generateRecoveryCode();
    const recoverySalt = crypto.getRandomValues(new Uint8Array(16));

    const recoveryKek = await derivePbkdf2Key({
        password: recoveryCode,
        salt: recoverySalt,
        iterations: 600000,
    });

    const recoveryEncrypted = await aesEncrypt({ key: recoveryKek, data: mek });

    const keyPair = await generateRsaKeyPair();
    const publicKey = await exportPublicKey(keyPair.publicKey);
    const privateKeyPkcs8 = await exportPrivateKey(keyPair.privateKey);

    const mekKey = await crypto.subtle.importKey('raw', mek, 'AES-GCM', false, [
        'encrypt',
    ]);
    const encryptedPrivate = await aesEncrypt({
        key: mekKey,
        data: privateKeyPkcs8,
    });

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
    const emekPayload = JSON.parse(emek_password);
    const privatePayload = JSON.parse(encrypted_private_key);
    const salt = base64ToBuffer(emek_password_salt);

    const kek = await derivePbkdf2Key({ password, salt, iterations: 600000 });

    const mekRaw = await aesDecrypt({
        key: kek,
        iv: emekPayload.iv,
        data: emekPayload.data,
    });

    const mekKey = await crypto.subtle.importKey(
        'raw',
        mekRaw,
        'AES-GCM',
        false,
        ['decrypt'],
    );

    const privateKeyPkcs8 = await aesDecrypt({
        key: mekKey,
        iv: privatePayload.iv,
        data: privatePayload.data,
    });

    const privateKey = await crypto.subtle.importKey(
        'pkcs8',
        privateKeyPkcs8,
        { name: 'RSA-OAEP', hash: 'SHA-256' },
        false,
        ['decrypt'],
    );

    return privateKey;
}

async function decryptMekWithPassword({
    password,
    emek_password,
    emek_password_salt,
}: {
    password: string;
    emek_password: string;
    emek_password_salt: string;
}): Promise<ArrayBuffer> {
    const emekPayload = JSON.parse(emek_password);
    const salt = base64ToBuffer(emek_password_salt);

    const kek = await derivePbkdf2Key({ password, salt, iterations: 600000 });

    return aesDecrypt({ key: kek, iv: emekPayload.iv, data: emekPayload.data });
}

async function encryptMekWithPassword({
    mekRaw,
    password,
}: {
    mekRaw: ArrayBuffer;
    password: string;
}): Promise<ReEncryptResult> {
    const newSalt = crypto.getRandomValues(new Uint8Array(16));

    const newKek = await derivePbkdf2Key({
        password,
        salt: newSalt,
        iterations: 600000,
    });

    const newEmek = await aesEncrypt({
        key: newKek,
        data: new Uint8Array(mekRaw),
    });

    return {
        emek_password: JSON.stringify(newEmek),
        emek_password_salt: bufferToBase64(newSalt),
    };
}
