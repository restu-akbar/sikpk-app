import { base64ToBuffer, bufferToBase64 } from './base64';
import { rsaDecrypt, rsaEncrypt } from './rsa-oaep';

interface ReEncryptEdeksParams {
    evidences: {
        id: string;
        edeks: Record<string, string>;
    }[];
    currentUserId: string;
    privateKey: CryptoKey;
    targetPublicKeys: Record<string, CryptoKey>;
}

export async function reEncryptEdeks({
    evidences,
    currentUserId,
    privateKey,
    targetPublicKeys,
}: ReEncryptEdeksParams) {
    return Promise.all(
        evidences.map(async (evidence) => {
            const myEdek = evidence.edeks[currentUserId];

            if (!myEdek) {
                throw new Error(
                    `Missing EDEK for current user on evidence ${evidence.id}`,
                );
            }

            const dekRaw = await rsaDecrypt(privateKey, base64ToBuffer(myEdek));

            const newEdeks: Record<string, string> = {};

            await Promise.all(
                Object.entries(targetPublicKeys).map(
                    async ([userId, publicKey]) => {
                        const encryptedDek = await rsaEncrypt(
                            publicKey,
                            dekRaw,
                        );

                        newEdeks[userId] = bufferToBase64(encryptedDek);
                    },
                ),
            );

            return {
                evidence_id: evidence.id,
                edeks: newEdeks,
            };
        }),
    );
}
