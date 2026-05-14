export function generateRecoveryCode(): string {
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
