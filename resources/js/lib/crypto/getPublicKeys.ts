import { importPublicKey } from '@/lib/crypto/rsa-oaep';

export async function getPublicKeys(
    owners?: string[],
): Promise<Record<string, CryptoKey>> {
    console.log(owners)
    const params = new URLSearchParams();

    owners?.forEach((owner) => {
        params.append('owner[]', owner);
    });

    const url = params.size
        ? `/api/public-key?${params.toString()}`
        : '/api/public-key';

    const res = await fetch(url);

    const publicKeys: Record<string, string> = await res.json();

    const entries = await Promise.all(
        Object.entries(publicKeys).map(
            async ([owner, publicKey]) =>
                [owner, await importPublicKey(publicKey)] as const,
        ),
    );

    return Object.fromEntries(entries);
}
