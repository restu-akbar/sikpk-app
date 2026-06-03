export function bufferToBase64(buffer: ArrayBuffer | Uint8Array): string {
    const bytes =
        buffer instanceof Uint8Array ? buffer : new Uint8Array(buffer);
    let binary = '';
    const chunkSize = 8192;

    for (let i = 0; i < bytes.length; i += chunkSize) {
        binary += String.fromCharCode(...bytes.subarray(i, i + chunkSize));
    }

    return btoa(binary);
}

export function base64ToBuffer(base64: string): Uint8Array {
    if (!base64 || typeof base64 !== 'string') {
        return new Uint8Array(0);
    }

    const sanitized = base64
        .trim()
        .replace(/\s+/g, '')
        .replace(/-/g, '+')
        .replace(/_/g, '/');

    const padded = sanitized.padEnd(
        sanitized.length + ((4 - (sanitized.length % 4)) % 4),
        '=',
    );

    return Uint8Array.from(atob(padded), (c) => c.charCodeAt(0));
}
